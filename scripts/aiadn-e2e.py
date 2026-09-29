#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 1.

Drives the real pages over HTTP and reads the emails from Mailpit, like a person would.
Needs the local stack running (docker compose up) with Mailpit on :8025.

    python3 scripts/aiadn-e2e.py
"""
import http.cookiejar
import json
import random
import re
import string
import subprocess
import sys
import urllib.error
import urllib.parse
import urllib.request

SITE = "http://localhost:8888"
MAIL = "http://localhost:8025/api/v1"
PASSED, FAILED = [], []


def check(name, ok, detail=""):
    (PASSED if ok else FAILED).append(name)
    print(("  PASS  " if ok else "  FAIL  ") + name + (f"   [{detail}]" if detail and not ok else ""))


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *a, **k):
        return None


class Browser:
    """One person's browser: its own cookies."""

    def __init__(self):
        self.jar = http.cookiejar.CookieJar()
        self.follow = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar))
        self.stay = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(self.jar), NoRedirect)

    def get(self, url, follow=True):
        return self._do(urllib.request.Request(url), follow)

    def post(self, url, data, follow=True):
        body = urllib.parse.urlencode(data, doseq=True).encode()
        return self._do(urllib.request.Request(url, data=body), follow)

    def _do(self, req, follow):
        try:
            r = (self.follow if follow else self.stay).open(req, timeout=30)
            return r.status, r.read().decode("utf-8", "replace"), dict(r.headers)
        except urllib.error.HTTPError as e:
            return e.code, e.read().decode("utf-8", "replace"), dict(e.headers)


def mail_clear():
    urllib.request.urlopen(urllib.request.Request(MAIL + "/messages", method="DELETE"), timeout=10).read()


def mails(to=None):
    data = json.load(urllib.request.urlopen(MAIL + "/messages?limit=300", timeout=10))
    out = []
    for m in data.get("messages", []):
        addrs = [a["Address"] for a in m.get("To", [])]
        if to and to not in addrs:
            continue
        full = json.load(urllib.request.urlopen(f"{MAIL}/message/{m['ID']}", timeout=10))
        out.append({"to": addrs, "subject": m["Subject"], "text": full.get("Text", "")})
    return out


def code_for(to):
    for m in mails(to):
        hit = re.search(r"(?m)^\s+(\d{6})\s*$", m["text"])
        if hit:
            return hit.group(1)
    return None


def link_for(to, marker):
    for m in mails(to):
        hit = re.search(r"(http\S*" + marker + r"\S*)", m["text"])
        if hit:
            return hit.group(1)
    return None


def loc(headers):
    return headers.get("Location") or headers.get("location") or ""


def field(html, name):
    hit = re.search(r'name="' + name + r'" value="([^"]*)"', html)
    return hit.group(1) if hit else ""


def clear_rate_limits():
    sql = "DELETE FROM wp_options WHERE option_name LIKE '%aiadn_rl_%'"
    subprocess.run(["docker", "compose", "exec", "-T", "db", "mysql", "-uwordpress", "-pwordpress", "wordpress", "-e", sql],
                   capture_output=True, cwd=__file__.rsplit("/scripts/", 1)[0])


def sign_in(browser, school_code, email, role="teacher"):
    """Front door -> emailed code -> dashboard. Returns the dashboard HTML, or None."""
    status, _, headers = browser.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": school_code, "role": role, "email": email}, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(headers))
    if not ref:
        return None
    code = code_for(email)
    if not code:
        return None
    status, html, _ = browser.post(f"{SITE}/conversation/join/", {"aiadn_action": "verify", "ref": ref.group(1), "code": code})
    return html if "aiadn__band" in html and "<h1>" in html else None


def main():
    clear_rate_limits()
    mail_clear()
    run = "".join(random.choices(string.ascii_lowercase, k=5))
    domain = f"oakfield{run}.sch.uk"
    teacher = f"s.patel@{domain}"
    slt = f"head@{domain}"
    colleague = f"l.brown@{domain}"
    outsider = f"someone{run}@gmail.com"
    school_name = f"Oakfield Primary {run.upper()}"
    postcode = "LS6 2AB"

    print("\n1. Front door and pages")
    b = Browser()
    s, html, h = b.get(f"{SITE}/conversation/join/")
    check("front door loads", s == 200 and "Enter your school code" in html)
    check("front door is not cached", "no-cache" in h.get("Cache-Control", "") or "no-store" in h.get("Cache-Control", ""))
    check("front door is not indexed", "noindex" in h.get("X-Robots-Tag", ""))
    s, html, _ = b.get(f"{SITE}/conversation/register/")
    check("register page loads", s == 200 and "Register your school" in html)
    s, html, h = b.get(f"{SITE}/conversation/school/", follow=False)
    check("dashboard needs sign-in", s == 302 and "/conversation/join/" in loc(h))

    print("\n2. Register a school")
    good = {"aiadn_action": "register", "school_name": school_name, "postcode": postcode, "age_phases[]": ["primary"], "mat_name": "Northern Lights Trust",
            "teacher_name": "Sam Patel", "job_title": "Class teacher", "email": teacher, "slt_email": slt, "partner_ref": "Apps for Good", "agree": "1"}
    s, html, _ = b.post(f"{SITE}/conversation/register/", {**good, "school_name": ""})
    check("missing school name is rejected", "Enter your school name" in html)
    check("form keeps what was typed", teacher in html)
    s, html, _ = b.post(f"{SITE}/conversation/register/", {**good, "slt_email": teacher})
    check("SLT email must differ from teacher's", "someone else" in html)
    s, html, _ = b.post(f"{SITE}/conversation/register/", {**good, "agree": ""})
    check("Code of Conduct must be agreed", "Code of Conduct" in html and "agree" in html.lower())
    s, html, _ = b.post(f"{SITE}/conversation/register/", {**good, "website": "spam"})
    check("hidden field catches bots", "Something went wrong" in html)

    s, _, h = b.post(f"{SITE}/conversation/register/", good, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h))
    check("valid registration goes to the code screen", s == 302 and bool(ref), loc(h))
    ref = ref.group(1)
    code = code_for(teacher)
    check("teacher receives a 6-digit code by email", bool(code))
    check("no school code is shown before verification", "SCH-" not in " ".join(m["text"] for m in mails(teacher)))
    s, html, _ = b.get(f"{SITE}/conversation/register/?step=code&ref={ref}")
    check("code screen masks the email", "s.p***@" in html and teacher not in html)

    print("\n3. Codes: wrong, right, single use")
    wrong = "000000" if code != "000000" else "111111"
    s, html, _ = b.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref, "code": wrong})
    check("wrong code is refused", "didn't work" in html or "didn&#039;t work" in html)
    s, html, _ = b.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref, "code": code})
    check("right code signs the lead in", "Waiting for SLT approval" in html)
    m = re.search(r"SCH-[2-9A-HJ-NP-Z]{5}", html)
    check("school code is created and shown", bool(m))
    school_code = m.group(0) if m else ""
    b2 = Browser()
    s, html, _ = b2.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref, "code": code})
    check("a code cannot be used twice", "Waiting for SLT" not in html and "didn't work" in html.replace("&#039;", "'"))

    print("\n4. Five wrong guesses kill a code")
    other = f"t2.{run}@{domain}"
    # A second, separate registration to test lockout without disturbing the first school.
    r2 = {**good, "school_name": school_name + " Two", "postcode": "M1 1AA", "email": other, "slt_email": f"head2@{domain}"}
    s, _, h = Browser().post(f"{SITE}/conversation/register/", r2, follow=False)
    ref2 = re.search(r"ref=([a-f0-9]{32})", loc(h)).group(1)
    real2 = code_for(other)
    bb = Browser()
    for _ in range(5):
        bb.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref2, "code": "999999" if real2 != "999999" else "888888"})
    s, html, _ = bb.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref2, "code": real2})
    check("after 5 wrong guesses even the right code fails", "Waiting for SLT" not in html)

    print("\n5. SLT approval")
    link = link_for(slt, "approve")
    check("SLT receives an approval link", bool(link))
    slt_browser = Browser()
    for _ in range(3):
        s, html, _ = slt_browser.get(link)
    check("opening the link shows the school and an Approve button", school_name in html and "Approve" in html)
    s, dash, _ = b.get(f"{SITE}/conversation/school/")
    check("opening the link changed nothing (mail scanners are safe)", "Waiting for SLT approval" in dash)
    s, html, _ = slt_browser.post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    check("pressing Approve approves and shows the code", "<h1>Approved</h1>" in html and school_code in html)
    s, html, _ = Browser().post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    check("the link cannot be used twice", "no longer valid" in html)
    check("teacher is told the school is approved", any("approved" in m["subject"] for m in mails(teacher)))
    s, dash, _ = b.get(f"{SITE}/conversation/school/")
    check("dashboard now shows approved state", "Class PIN for students" in dash and school_code in dash)
    check("lead sees the team", "Team" in dash and teacher in dash)

    print("\n6. Front door: teacher, same message for everyone")
    t = Browser()
    dash = sign_in(t, school_code, teacher)
    check("teacher signs in with school code + email + code", bool(dash) and "Class PIN" in dash)
    fd = f"{SITE}/conversation/join/"
    _, _, h_ok = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "teacher", "email": teacher}, follow=False)
    _, _, h_bad_code = Browser().post(fd, {"aiadn_action": "front_door", "school_code": "SCH-22222", "role": "teacher", "email": teacher}, follow=False)
    _, _, h_bad_email = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "teacher", "email": f"nobody@{'other.example'}"}, follow=False)
    shape = lambda hh: re.sub(r"[a-f0-9]{32}", "REF", loc(hh))
    check("right details, wrong school code, unknown email all look identical", shape(h_ok) == shape(h_bad_code) == shape(h_bad_email), f"{shape(h_ok)} / {shape(h_bad_code)} / {shape(h_bad_email)}")
    mail_clear()
    Browser().post(fd, {"aiadn_action": "front_door", "school_code": "SCH-22222", "role": "teacher", "email": teacher}, follow=False)
    Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "teacher", "email": f"nobody@{'other.example'}"}, follow=False)
    check("no email is sent for a wrong code or an unknown person", len(mails()) == 0)
    _, _, h = Browser().post(fd, {"aiadn_action": "front_door", "school_code": "not a code", "role": "teacher", "email": teacher}, follow=False)
    check("a malformed school code looks the same as any other non-match", shape(h) == shape(h_ok), loc(h))

    print("\n7. Colleagues on the same email domain")
    mail_clear()
    c = Browser()
    dash = sign_in(c, school_code, colleague)
    check("colleague on the school's domain joins straight away", bool(dash))
    check("the lead is told", any("joined" in m["subject"] for m in mails(teacher)))
    mail_clear()
    _, _, h = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "teacher", "email": outsider}, follow=False)
    check("a personal email address does not join", len(mails(outsider)) == 0 and "step=code" in loc(h))

    print("\n8. Class PIN and students")
    s, dash, _ = t.get(f"{SITE}/conversation/school/")
    token = field(dash, "csrf")
    check("dashboard forms carry a CSRF token", bool(token))
    t.post(f"{SITE}/conversation/school/", {"aiadn_action": "new_pin"})
    s, dash, _ = t.get(f"{SITE}/conversation/school/")
    check("a request without the CSRF token does not start a PIN", "No PIN is running" in dash)
    t.post(f"{SITE}/conversation/school/", {"aiadn_action": "new_pin", "csrf": token})
    s, dash, _ = t.get(f"{SITE}/conversation/school/")
    pin = re.search(r'aiadn__bigcode--pin">(\d{4})<', dash)
    check("teacher starts a class PIN", bool(pin))
    pin = pin.group(1) if pin else "0000"
    st = Browser()
    s, html, _ = st.post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "student", "pin": pin})
    check("student gets in with school code + PIN and lands on the survey", "Student Voice" in html and school_name in html)
    s, html, h = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "student", "pin": "0000" if pin != "0000" else "1111"}, follow=False)
    check("wrong PIN is refused", "msg=nomatch" in loc(h))
    s, html, h = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "student"}, follow=False)
    check("school code alone gets a student nothing", "msg=nomatch" in loc(h))
    s, html, _ = st.get(f"{SITE}/conversation/school/", follow=False)
    check("a student session cannot open the teacher dashboard", s == 302 and "/conversation/school/" not in loc(_))
    t.post(f"{SITE}/conversation/school/", {"aiadn_action": "new_pin", "csrf": token})
    s, html, h = Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "student", "pin": pin}, follow=False)
    check("starting a new PIN stops the old one", "msg=nomatch" in loc(h))

    print("\n9. SLT and judge at the front door")
    sl = Browser()
    dash = sign_in(sl, school_code, slt, role="slt")
    check("SLT signs in with the school code and their email", bool(dash) and "senior leader" in dash)
    check("SLT cannot start a class PIN", "Start a class PIN" not in dash and "Start a new PIN" not in dash)
    mail_clear()
    Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "judge", "email": "judge@example.org"}, follow=False)
    check("judges cannot sign in yet and nothing is sent", len(mails()) == 0)
    mail_clear()
    Browser().post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "slt", "email": teacher}, follow=False)
    check("a teacher's email cannot sign in as SLT", len(mails(teacher)) == 0)

    print("\n10. Sign out and rate limits")
    _, html, h = t.post(f"{SITE}/conversation/school/", {"aiadn_action": "logout", "csrf": token})
    check("sign out works", "You are signed out" in html)
    s, _, h = t.get(f"{SITE}/conversation/school/", follow=False)
    check("dashboard is closed after sign out", s == 302)
    limiter = Browser()
    hit = False
    for _ in range(20):
        _, _, h = limiter.post(fd, {"aiadn_action": "front_door", "school_code": school_code, "role": "student", "pin": "1234"}, follow=False)
        if "msg=slow" in loc(h):
            hit = True
            break
    check("repeated PIN guesses are slowed down", hit)

    print(f"\n{len(PASSED)} passed, {len(FAILED)} failed")
    if FAILED:
        print("Failed: " + "; ".join(FAILED))
        sys.exit(1)


if __name__ == "__main__":
    main()
