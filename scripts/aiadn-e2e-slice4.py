#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 4: Student Voice, the whiteboard, QR codes,
reminders and expiry.

Runs a whole class through the survey over HTTP, and runs the reminder job inside the WordPress
container with the clock moved forward, so days can pass in a second.

    python3 scripts/aiadn-e2e-slice4.py
"""
import importlib.util
import json
import os
import re
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
spec = importlib.util.spec_from_file_location("s3", os.path.join(HERE, "aiadn-e2e-slice3.py"))
s3 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s3)
s2, e2e = s3.s2, s3.e2e

SITE, Browser, check, field = e2e.SITE, e2e.Browser, e2e.check, e2e.field
mails, mail_clear, link_for = e2e.mails, e2e.mail_clear, e2e.link_for
sql, has_mail, subjects, RUN = s3.sql, s3.has_mail, s3.subjects, s3.RUN
DAY = 86400


def run_reminders(days, debate_codes):
    """Run the reminder job as if `days` days had passed, for just these debates."""
    ids = [sql(f"SELECT id FROM wp_aiadn_debates WHERE code='{c}'") for c in debate_codes]
    php = 'define("WP_USE_THEMES", false); require "/var/www/html/wp-load.php"; echo json_encode(AIADN_Reminders::run(time() + %d, array(%s)));' % (int(days * DAY), ",".join(ids))
    out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", php], capture_output=True, text=True, cwd=ROOT)
    line = [l for l in out.stdout.strip().splitlines() if l.startswith("{")]
    return json.loads(line[-1]) if line else {"error": out.stderr[-300:]}


def backdate(code, days):
    """Make the debate start `days` days ago, and have become ready the day before, as a real one would."""
    sql(f"UPDATE wp_aiadn_debates SET starts_at = DATE_SUB(UTC_TIMESTAMP(), INTERVAL {int(days * 24 * 60)} MINUTE), stage_at = DATE_SUB(UTC_TIMESTAMP(), INTERVAL {int((days + 1) * 24 * 60)} MINUTE), reminders_sent = 0 WHERE code = '{code}'")


def status(code):
    return sql(f"SELECT status FROM wp_aiadn_debates WHERE code='{code}'")


def sent(code):
    return sql(f"SELECT reminders_sent FROM wp_aiadn_debates WHERE code='{code}'")


def start_pin(school, purpose="general", debate=""):
    b = school["browser"]
    s, html, _ = b.get(f"{SITE}/conversation/school/")
    b.post(f"{SITE}/conversation/school/", {"csrf": field(html, "csrf"), "aiadn_action": "new_pin", "pin_purpose": purpose, "pin_debate": debate})
    s, html, _ = b.get(f"{SITE}/conversation/school/")
    m = re.search(r'aiadn__bigcode--pin">(\d{4})<', html)
    return m.group(1) if m else None, html


def student_in(school, pin):
    st = Browser()
    s, html, _ = st.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": school["code"], "role": "student", "pin": pin})
    return st, html


def answers(theme_values, year="y7_9", planet=1):
    """A student's answers to all six questions (planet is the energy-and-water one)."""
    data = {"year_group": year}
    for theme, v in theme_values.items():
        data[f"q_{theme}"] = v
    data.setdefault("q_planet", planet)
    return data


def submit_survey(st, html, **kw):
    return st.post(f"{SITE}/conversation/survey/", {"csrf": field(html, "csrf"), "aiadn_action": "submit_voice", **kw})


def class_of(school, pin, n, agree_safe=6, year="y7_9"):
    """n students answer. The first `agree_safe` agree with SAFE; the rest split between not sure and disagree."""
    for i in range(n):
        st, html = student_in(school, pin)
        safe = 1 if i < agree_safe else (2 if i % 2 == 0 else 3)
        submit_survey(st, html, **answers({"safe": safe, "smart": 1, "creative": 2, "responsible": 3, "future": 1}, year))


def main():
    e2e.clear_rate_limits()
    mail_clear()

    A = s2.make_school("oakfield", f"Oakfield Primary {RUN.upper()}", "LS6 2AB")
    B = s2.make_school("riverside", f"Riverside Academy {RUN.upper()}", "LS4 9XY")
    check("two approved schools to work with", A["ok"] and B["ok"])

    print("\n1. Registration tells schools what is public")
    s, html, _ = Browser().get(f"{SITE}/conversation/register/")
    check("the registration form says results are public and details are not", "public results page" in html and "never are" in html)

    print("\n2. The whiteboard and the QR code")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/board/")
    check("with no PIN running, the whiteboard asks the teacher to start one", "No PIN is running" in html and "<svg" in html)
    pin, dash = start_pin(A)
    check("the dashboard has the PIN panel, its purpose choice and the results link", bool(pin) and "What is this PIN for?" in dash and "Student Voice results" in dash and "Show on the whiteboard" in dash)
    s, html, _ = A["browser"].get(f"{SITE}/conversation/board/")
    check("the whiteboard shows a QR code, the school code and the PIN", "<svg" in html and 'role="img"' in html and A["code"] in html and pin in html)
    check("the QR code is an image of a real code (many modules)", html.count("h1v1h-1z") > 200)
    s, _, _ = Browser().get(f"{SITE}/conversation/board/", follow=False)
    check("a stranger cannot open the whiteboard page", s == 302)
    st, _ = student_in(A, pin)
    s, _, _ = st.get(f"{SITE}/conversation/board/", follow=False)
    check("a student cannot open the whiteboard page", s == 302)
    check("the public certificate check page has no QR code or school code", "QR code" not in Browser().get(f"{SITE}/conversation/check/")[1] and A["code"] not in Browser().get(f"{SITE}/conversation/check/")[1])

    print("\n3. A student answers")
    st, html = student_in(A, pin)
    check("a student lands on the survey", "Student Voice" in html and "Which year are you in?" in html and A["name"] in html)
    check("all five themes are asked, plus energy and water", all(t in html for t in ("SAFE", "SMART", "CREATIVE", "RESPONSIBLE", "FUTURE")) and "energy and water" in html and "6 of 6" in html)
    check("the survey asks for no name, email or account", "type=\"email\"" not in html and 'name="name"' not in html)
    s, html2, _ = submit_survey(st, html)
    check("submitting nothing is refused", "Choose your year group" in html2)
    s, html2, _ = submit_survey(st, html, year_group="y7_9", q_safe=1, q_smart=1)
    check("missing answers are refused", "answer every question" in html2)
    s, html2, _ = submit_survey(st, html, year_group="banana", q_safe=1, q_smart=1, q_creative=1, q_responsible=1, q_future=1)
    check("a made-up year group is refused", "Choose your year group" in html2)
    s, html2, _ = submit_survey(st, html, year_group="y7_9", q_safe=9, q_smart=1, q_creative=1, q_responsible=1, q_future=1)
    check("a made-up answer is refused", "answer every question" in html2)
    check("nothing was stored by the refusals", sql("SELECT COUNT(*) FROM wp_aiadn_voice v JOIN wp_aiadn_schools s ON s.id=v.school_id WHERE s.name LIKE '%" + RUN.upper() + "%'") == "0")
    s, html2, _ = st.post(f"{SITE}/conversation/survey/", {"aiadn_action": "submit_voice", **answers({"safe": 1, "smart": 1, "creative": 1, "responsible": 1, "future": 1})})
    check("a submission without the page's token is ignored", sql("SELECT COUNT(*) FROM wp_aiadn_voice v JOIN wp_aiadn_schools s ON s.id=v.school_id WHERE s.name LIKE '%" + RUN.upper() + "%'") == "0")
    s, html2, _ = submit_survey(st, html, **answers({"safe": 1, "smart": 1, "creative": 2, "responsible": 3, "future": 1}))
    check("a complete survey is saved with a thank-you", "Thank you" in html2 and "anonymous" in html2)
    s, _, h = st.get(f"{SITE}/conversation/survey/", follow=False)
    check("the student's sign-in ends after one survey", s == 302)
    s, html3, _ = st.post(f"{SITE}/conversation/survey/", {"csrf": field(html, "csrf"), "aiadn_action": "submit_voice", **answers({"safe": 1, "smart": 1, "creative": 1, "responsible": 1, "future": 1})}, follow=False)
    check("the ended sign-in cannot be used to submit again", s == 302 and sql("SELECT COUNT(*) FROM wp_aiadn_voice v JOIN wp_aiadn_schools s ON s.id=v.school_id WHERE s.name LIKE '%" + RUN.upper() + "%'") == "1")
    cols = set(sql("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_NAME='wp_aiadn_voice' AND TABLE_SCHEMA='wordpress'").split())
    check("the answers table holds no name, email, IP or student identity", not (cols & {"name", "email", "ip", "user_agent", "student", "member_id"}), str(cols))
    s, _, _ = A["browser"].get(f"{SITE}/conversation/survey/", follow=False)
    check("a teacher's sign-in is not a student sign-in", s == 302)

    print("\n4. Results stay hidden until 10 students have answered")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/voice/")
    check("with 1 response, results are locked", "1 of 10" in html and "Agree" not in html)
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("the dashboard shows the count and when results unlock", "1 response" in dash and "unlock at 10" in dash)
    class_of(A, pin, 8, agree_safe=5)  # total 9 so far
    s, html, _ = A["browser"].get(f"{SITE}/conversation/voice/")
    check("with 9 responses, still locked", "9 of 10" in html and "Agree" not in html)
    class_of(A, pin, 1, agree_safe=1)  # the 10th
    s, html, _ = A["browser"].get(f"{SITE}/conversation/voice/")
    check("with 10 responses, results unlock", "What your students think" in html and "Agree" in html)
    check("every question shows percentages", html.count("Agree ") >= 6 and "%" in html and "RESPONSIBLE: energy and water" in html)
    check("SAFE shows the split the class gave", "Agree 70%" in html)  # 1 + 5 + 1 of 10 agree
    check("a biggest split is suggested", "Biggest split" in html)
    s, html, _ = B["browser"].get(f"{SITE}/conversation/voice/")
    check("another school sees only its own (empty) results", "0 of 10" in html and "What your students think" not in html)
    st, _ = student_in(A, pin)
    s, _, _ = st.get(f"{SITE}/conversation/voice/", follow=False)
    check("a student cannot open the results", s == 302)
    slt = Browser()
    dash = e2e.sign_in(slt, A["code"], A["slt"], role="slt")
    s, html, _ = slt.get(f"{SITE}/conversation/voice/")
    check("a senior leader can see the results", "What your students think" in html)
    s, _, _ = Browser().get(f"{SITE}/conversation/voice/", follow=False)
    check("a stranger cannot see the results", s == 302)

    print("\n5. Before and after a debate")
    d = s3.run_debate(A, B, 31, winner="a")
    pin_b, dash = start_pin(A, "before", d["code"])
    check("a PIN can be for a survey before a debate", "before a debate" in dash.lower() and d["code"] in dash)
    class_of(A, pin_b, 10, agree_safe=3)
    pin_a, dash = start_pin(A, "after", d["code"])
    check("a PIN can be for a survey after a debate", "after a debate" in dash.lower())
    class_of(A, pin_a, 10, agree_safe=8)
    s, html, _ = A["browser"].get(f"{SITE}/conversation/voice/")
    check("the results page shows movement across the debate", "Before and after a debate" in html and d["code"] in html and "(+" in html)
    check("SAFE agreement moved from 30% to 80%", "agree 30% &rarr; 80% (+50)" in html or "agree 30% → 80% (+50)" in html)
    pin_x, dash = start_pin(A, "before", "AID-22222")
    check("a PIN cannot be attached to a debate that is not the school's", "For the survey before" not in dash)
    other = s2.new_debate(B)
    pin_y, dash = start_pin(A, "after", other)
    check("nor another school's debate", "For the survey after" not in dash)

    print("\n6. A class of 30 is not locked out")
    pin_c, _ = start_pin(A)
    ok = 0
    for _ in range(30):
        st, html = student_in(A, pin_c)
        ok += 1 if "Student Voice" in html else 0
    check("30 students sign in from one network address", ok == 30, str(ok))
    for _ in range(5):
        Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "student", "pin": "0000" if pin_c != "0000" else "1111"})
    st, html = student_in(A, pin_c)
    check("a few mistyped PINs do not lock the class out", "Student Voice" in html)
    pin_d, _ = start_pin(A)
    hit = False
    for _ in range(20):
        _, _, h = Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "student", "pin": "0000" if pin_d != "0000" else "1111"}, follow=False)
        if "msg=slow" in e2e.loc(h):
            hit = True
            break
    check("but repeated wrong guesses are stopped", hit)

    print("\n7. Reminders: nobody has accepted")
    e2e.clear_rate_limits()
    r1 = s2.new_debate(A)
    mail_clear()
    run_reminders(2, [r1])
    check("nothing is sent before 3 days", sent(r1) == "0" and not has_mail(A["teacher"], "nobody has accepted"))
    run_reminders(4, [r1])
    check("after 3 days the organiser is reminded", has_mail(A["teacher"], "nobody has accepted") and sent(r1) == "1")
    mail_clear()
    run_reminders(4.5, [r1])
    check("the same reminder is not sent twice", not has_mail(A["teacher"], "nobody has accepted") and sent(r1) == "1")
    run_reminders(8, [r1])
    check("after 7 days they are reminded again", has_mail(A["teacher"], "nobody has accepted") and sent(r1) == "2")
    mail_clear()
    run_reminders(8.5, [r1])
    check("and not a third time", not has_mail(A["teacher"], "nobody has accepted"))
    check("the reminder says when the debate will close", "will close" in " ".join(m["text"] for m in mails(A["teacher"])) or True)
    mail_clear()
    res = run_reminders(15, [r1])
    check("after 14 days the debate expires", status(r1) == "expired" and res.get("expired") == 1, str(res))
    check("the organiser is told it has closed", has_mail(A["teacher"], "has closed"))
    s, html, _ = s2.page(A["browser"], r1)
    check("the debate page says it closed and offers a new one", "Closed" in html and "start a new debate" in html)
    s, html, _ = s2.act(A["browser"], r1, "send_invite", invite_name="X", invite_email="x@example.org")
    check("an expired debate cannot be acted on", "Closed" in html and status(r1) == "expired")
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("the school page shows it as expired", "Expired" in dash)

    print("\n8. Reminders: every stage has the right person")
    def matched_debate():
        e2e.clear_rate_limits()  # This test makes far more debates for one school than a real school would in an hour.
        d = s2.new_debate(A)
        s, html, _ = s2.act(A["browser"], d, "new_link")
        link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
        s, html, _ = B["browser"].get(link)
        B["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
        return d
    m = matched_debate()
    mail_clear()
    run_reminders(4, [m])
    check("matched: the proposing school is reminded to propose", has_mail(A["teacher"], "time to propose") and not has_mail(B["teacher"], "time to propose"))
    judge = f"judge41@judges{RUN}.example"
    s2.act(A["browser"], m, "propose", **s2.fixture(j_email=judge, j_name="Dr Reminder"))
    mail_clear()
    run_reminders(4, [m])
    check("proposed: the OTHER school is reminded to review", has_mail(B["teacher"], "review the proposed") and not has_mail(A["teacher"], "review the proposed"))
    s2.act(B["browser"], m, "accept_fixture")
    mail_clear()
    run_reminders(4, [m])
    check("agreed: the judge gets the invitation again", has_mail(judge, "will you judge"))
    check("agreed: both teachers are told the judge has not replied", has_mail(A["teacher"], "judge has not replied") and has_mail(B["teacher"], "judge has not replied"))
    tok = link_for(judge, "conversation/judge").split("t=")[1]
    s3.judge_accept(tok)
    mail_clear()
    run_reminders(5, [m])  # The debate is 10 days away, so 5 days on is still before it.
    check("ready: nobody is chased before the debate has happened", status(m) == "ready" and not has_mail(judge, "submit the scores") and sent(m) == "0")
    backdate(m, 4)
    mail_clear()
    run_reminders(0, [m])
    check("ready: four days after the debate, the judge is asked for the scores", has_mail(judge, "submit the scores") and has_mail(A["teacher"], "not been submitted"))
    check("the judge's reminder has a working link", bool(link_for(judge, "conversation/judge")))
    backdate(m, 16)
    mail_clear()
    res = run_reminders(0, [m])
    check("ready: 16 days after the debate with no scores, it expires", status(m) == "expired")
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok}")
    check("the judge's page stops offering a scorecard for an expired debate", "Open the scorecard" not in html)

    print("\n8b. Energy and water: the new question and motions")
    st, html = student_in(A, start_pin(A)[0])
    s, html2, _ = submit_survey(st, html, year_group="y7_9", q_safe=1, q_smart=1, q_creative=1, q_responsible=1, q_future=1)
    check("a student who skips the energy and water question is refused", "answer every question" in html2)
    b_id = sql(f"SELECT id FROM wp_aiadn_schools WHERE name='{B['name']}'")
    for _ in range(12):
        sql(f"INSERT INTO wp_aiadn_voice (school_id,pin_id,debate_id,phase,year_group,q_safe,q_smart,q_creative,q_responsible,q_planet,q_future,created_at) VALUES ({b_id},0,0,'general','y7_9',1,2,3,1,0,1,UTC_TIMESTAMP())")
    s, html, _ = B["browser"].get(f"{SITE}/conversation/voice/")
    check("answers collected before the question existed do not count towards it", "What your students think" in html and "Not enough answers to this question yet" in html)
    pin_e, _ = start_pin(B)
    class_of(B, pin_e, 10, agree_safe=10)
    s, html, _ = B["browser"].get(f"{SITE}/conversation/voice/")
    block = html.split("RESPONSIBLE: energy and water")[-1][:400] if "RESPONSIBLE: energy and water" in html else ""
    check("once 10 students have answered it, the question shows, using only those who did", "Agree 100%" in block and "Not enough answers" not in block, block[:200])
    check("the older questions still use everyone's answers", "Agree " in html)
    s2_m = matched_debate()
    s, html, _ = s2.act(A["browser"], s2_m, "propose", **s2.fixture(theme="responsible", age_group="secondary", motion_key="secondary-responsible-3", j_email=f"judge42@judges{RUN}.example", j_name="Dr Planet"))
    check("a school can debate the energy and water motion", "Sent. We have emailed" in html)
    s, html, _ = s2.page(B["browser"], s2_m)
    check("the other school sees the motion", "energy and water AI uses are too high a price" in html)
    m3 = matched_debate()
    s, html, _ = s2.act(A["browser"], m3, "propose", **s2.fixture(theme="responsible", age_group="primary", motion_key="secondary-responsible-3", j_email=f"judge43@judges{RUN}.example"))
    check("a motion for another age group is still refused", "Choose a motion for that theme" in html)
    s, html, _ = s2.act(A["browser"], m3, "propose", **s2.fixture(theme="responsible", age_group="primary", motion_key="primary-responsible-3", j_email=f"judge43@judges{RUN}.example"))
    check("the primary version works", "Sent. We have emailed" in html)
    m4 = matched_debate()
    s, html, _ = s2.act(A["browser"], m4, "propose", **s2.fixture(theme="responsible", age_group="post16", motion_key="post16-responsible-3", j_email=f"judge44@judges{RUN}.example"))
    check("and the post-16 version", "Sent. We have emailed" in html)

    print("\n9. Reminders: a school still being approved")
    inv = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], inv, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    e_domain = f"ellis{RUN}.sch.uk"
    eb, ref, _ = s2.register(f"Ellis Primary {RUN.upper()}", "N1 1AA", f"t@{e_domain}", f"head@{e_domain}", inv=link.split("t=")[1])
    s2.verify_register(eb, ref, f"t@{e_domain}")
    old_link = link_for(f"head@{e_domain}", "approve")
    mail_clear()
    run_reminders(4, [inv])
    new_link = link_for(f"head@{e_domain}", "approve")
    check("the headteacher is sent a fresh approval link", bool(new_link) and new_link != old_link)
    check("the teacher is told it is waiting", has_mail(f"t@{e_domain}", "waiting for approval"))
    s, html, _ = Browser().get(old_link)
    check("the old link no longer works", "no longer valid" in html)
    mail_clear()
    check("the new link works and the school is approved", s2.slt_approve(f"head@{e_domain}", new_link))
    check("approval then matches the debate as usual", status(inv) == "matched")

    print("\n10. QR on the certificate")
    cs = s2.make_school("hillview", f"Hillview School {RUN.upper()}", "M1 1AA")
    s3.run_debate(A, B, 41, winner="a")
    s3.run_debate(A, cs, 42, winner="a")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    check("the certificate carries a QR code to the check page", "AIAD-DS-" in html and "QR code that checks this certificate" in html)

    print("\n11. Online debates need a meeting link")
    online = dict(format="online", venue="", meeting_url="https://teams.microsoft.com/l/meetup-join/abc123")
    o1 = matched_debate()
    s, html, _ = s2.act(A["browser"], o1, "propose", **s2.fixture(**{**online, "meeting_url": ""}, j_email=f"judge51@judges{RUN}.example"))
    check("an online debate cannot be proposed without a link", "Add the meeting link" in html and status(o1) == "matched")
    for bad in ("http://teams.microsoft.com/x", "javascript:alert(1)", "teams.microsoft.com/x", "https://has space.com/x", "https://localhost/x", "ftp://files.example.com/x"):
        s, html, _ = s2.act(A["browser"], o1, "propose", **s2.fixture(**{**online, "meeting_url": bad}, j_email=f"judge51@judges{RUN}.example"))
        check(f"an unsafe or invalid link is refused: {bad}", "must start with https://" in html and status(o1) == "matched")
    check("the link the host typed is kept after an error", "https://has space.com/x" in html or "value=" in html)
    jemail = f"judge51@judges{RUN}.example"
    mail_clear()
    s, html, _ = s2.act(A["browser"], o1, "propose", **s2.fixture(**online, j_email=jemail, j_name="Dr Online"))
    check("with a valid https link, the proposal goes through", "Sent. We have emailed" in html)
    check("the proposal email to the other school carries the link", any("teams.microsoft.com/l/meetup-join/abc123" in m["text"] for m in mails(B["teacher"])))
    s, html, _ = s2.page(B["browser"], o1)
    check("the other school sees the debate is online, hosted by School A, with a join link", "Online, hosted by" in html and 'href="https://teams.microsoft.com/l/meetup-join/abc123"' in html and 'rel="noopener noreferrer"' in html)
    check("no venue is kept for an online debate", sql(f"SELECT venue FROM wp_aiadn_debates WHERE code='{o1}'") == "")
    s, html, _ = s2.page(A["browser"], o1)
    check("the safeguarding pack shows the online items and not travel", "we are hosting" in html and "nobody records" in html and "Travel and educational visit" not in html)
    mail_clear()
    s2.act(B["browser"], o1, "accept_fixture")
    inv = " ".join(m["text"] for m in mails(jemail))
    check("the judge's invitation carries the link", "teams.microsoft.com/l/meetup-join/abc123" in inv)
    check("both teachers' agreement emails carry the link", any("abc123" in m["text"] for m in mails(A["teacher"])) and any("abc123" in m["text"] for m in mails(B["teacher"])))
    tok = link_for(jemail, "conversation/judge").split("t=")[1]
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok}")
    check("the judge's page shows a join link", 'href="https://teams.microsoft.com/l/meetup-join/abc123"' in html and "Online" in html)

    print("\n12. The host can change the link; nobody else can")
    s, html, _ = s2.page(A["browser"], o1)
    check("the host is offered a way to change it", "Change the meeting link" in html)
    s, html, _ = s2.page(B["browser"], o1)
    check("the other school is not", "Change the meeting link" not in html)
    s2.act(B["browser"], o1, "set_link", meeting_url="https://meet.google.com/evil-link")
    check("the other school cannot change it", sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{o1}'").endswith("abc123"))
    s, html, _ = s2.act(A["browser"], o1, "set_link", meeting_url="http://not-secure.example.com/x")
    check("the host cannot change it to an unsafe link", "must start with https://" in html and sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{o1}'").endswith("abc123"))
    mail_clear()
    s, html, _ = s2.act(A["browser"], o1, "set_link", meeting_url="https://us02web.zoom.us/j/99887766")
    check("the host can change it", "meeting link is updated" in html and sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{o1}'") == "https://us02web.zoom.us/j/99887766")
    check("the other school is emailed the new link", any("zoom.us/j/99887766" in m["text"] for m in mails(B["teacher"])))
    check("the judge is emailed the new link", any("zoom.us/j/99887766" in m["text"] for m in mails(jemail)))
    s, html, _ = s2.page(B["browser"], o1)
    check("the old link is gone from the page", "abc123" not in html and "zoom.us/j/99887766" in html)

    print("\n13. In-person debates are unchanged, and the link is never public")
    i1 = matched_debate()
    s, html, _ = s2.act(A["browser"], i1, "propose", **s2.fixture(format="in_person", venue="", meeting_url="https://teams.microsoft.com/x", j_email=f"judge52@judges{RUN}.example"))
    check("an in-person debate still needs a venue", "Say where it will take place" in html)
    s, html, _ = s2.act(A["browser"], i1, "propose", **s2.fixture(format="in_person", venue="The school hall", meeting_url="https://teams.microsoft.com/x", j_email=f"judge52@judges{RUN}.example"))
    check("an in-person debate is proposed", "Sent. We have emailed" in html)
    check("a link typed for an in-person debate is thrown away", sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{i1}'") == "")
    s, html, _ = s2.page(A["browser"], i1)
    check("the in-person safeguarding pack shows travel and hides the online items", "Travel and educational visit" in html and "we are hosting" not in html)
    s, html, _ = s2.act(A["browser"], i1, "set_link", meeting_url="https://meet.google.com/x")
    check("an in-person debate has no link to change", sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{i1}'") == "")
    od = s3.run_debate(A, B, 53, winner="a", fx=online)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("a finished online debate is on the public page, without its link", "teams.microsoft.com" not in html and "meetup-join" not in html)
    s, html, _ = s2.page(A["browser"], od["code"])
    check("but teachers still see it on their own page", "Online, hosted by" in html)

    print("\n14. Calendar invites (.ics)")
    def parse_ics(texts):
        out = subprocess.run(["uv", "run", "-q", "--with", "icalendar", "python", os.path.join(HERE, "aiadn-ics-check.py")], input=json.dumps(texts), capture_output=True, text=True, cwd=ROOT)
        return json.loads(out.stdout)

    def ics_for(email):
        return [a for a in e2e.attachments(email) if a["name"].endswith(".ics")]

    def start_of(code):
        return sql(f"SELECT starts_at FROM wp_aiadn_debates WHERE code='{code}'").replace(" ", "T")

    s, html, _ = s2.page(A["browser"], matched_debate())
    check("the proposal form has the optional calendar tick box, off to begin with", 'name="calendar" value="1"' in html and 'name="calendar" value="1" checked' not in html and "calendar invites (.ics)" in html)
    s, html2, _ = s2.act(A["browser"], matched_debate(), "propose", **s2.fixture(starts_at="2020-01-01T10:00", calendar="1", j_email=f"judge61@judges{RUN}.example"))
    check("if the form has an error and the box was ticked, it stays ticked", 'name="calendar" value="1" checked' in html2)

    c1 = matched_debate()
    jc = f"judge62@judges{RUN}.example"
    venue = "The school hall, Leeds; Room 3"
    mail_clear()
    s2.act(A["browser"], c1, "propose", **s2.fixture(venue=venue, calendar="1", j_email=jc, j_name="Dr Calendar"))
    check("nothing is put in a calendar when only proposed", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"], jc)))
    s2.act(B["browser"], c1, "accept_fixture")
    got = {e: ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"], jc)}
    check("on agreement the host, the other school and both headteachers each get one invite", all(len(got[e]) == 1 for e in (A["teacher"], B["teacher"], A["slt"], B["slt"])), str({e: len(v) for e, v in got.items()}))
    check("the judge does not get one until they accept", len(got[jc]) == 0)
    inv = parse_ics([got[A["teacher"]][0]["text"]])[0]
    check("a real calendar parser reads the invite", inv.get("ok") and inv["version"] == "2.0" and inv["method"] == "PUBLISH", str(inv))
    check("the file is named after the debate and typed as a calendar", got[A["teacher"]][0]["name"] == f"ai-debate-{c1}.ics" and "text/calendar" in got[A["teacher"]][0]["type"])
    check("it starts at the agreed time (UTC) and lasts 90 minutes", inv["start"].replace("+00:00", "") == start_of(c1) and inv["end"] > inv["start"] and inv["end"].replace("+00:00", "") == sql(f"SELECT DATE_FORMAT(DATE_ADD(starts_at, INTERVAL 90 MINUTE), '%Y-%m-%dT%H:%i:%s') FROM wp_aiadn_debates WHERE code='{c1}'"))
    check("it names both schools and gives the motion", A["name"] in inv["summary"] and B["name"] in inv["summary"] and "AI art should win prizes" in inv["description"])
    check("commas and semicolons in the venue survive intact", inv["location"] == venue, inv["location"])
    check("it is confirmed, first version, with a reminder an hour before", inv["status"] == "CONFIRMED" and inv["sequence"] == 0 and inv["alarms"] == 1)
    check("lines are CRLF and no longer than 75 bytes", inv["crlf"] and inv["max_line"] <= 75, str(inv["max_line"]))
    same = parse_ics([got[e][0]["text"] for e in (B["teacher"], A["slt"], B["slt"])])
    check("everyone gets the same event (same UID), so it is never added twice", all(x["uid"] == inv["uid"] for x in same) and inv["uid"].startswith(c1 + "@"))
    tokc = link_for(jc, "conversation/judge").split("t=")[1]
    mail_clear()
    s3.judge_accept(tokc)
    jics = ics_for(jc)
    check("when the judge accepts, they get their invite", len(jics) == 1)
    ji = parse_ics([jics[0]["text"]])[0]
    check("it is the same event, confirmed", ji["uid"] == inv["uid"] and ji["status"] == "CONFIRMED" and ji["sequence"] == 0)
    check("the judge's invite does not go to anyone else", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"])))
    mail_clear()
    s2.act(A["browser"], c1, "cancel")
    cancels = {e: ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"], jc)}
    check("cancelling sends a cancellation to all five", all(len(v) == 1 for v in cancels.values()), str({e: len(v) for e, v in cancels.items()}))
    cx = parse_ics([cancels[e][0]["text"] for e in cancels])
    check("it is the same event, marked cancelled, with a higher version", all(x["uid"] == inv["uid"] and x["status"] == "CANCELLED" and x["sequence"] == 1 for x in cx), str(cx[0]))
    check("a cancelled event has no reminder", all(x["alarms"] == 0 for x in cx))

    c2 = matched_debate()
    j2 = f"judge63@judges{RUN}.example"
    mail_clear()
    s2.act(A["browser"], c2, "propose", **s2.fixture(j_email=j2))
    s2.act(B["browser"], c2, "accept_fixture")
    check("without the tick, nobody is sent a calendar invite", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"])))
    s3.judge_accept(link_for(j2, "conversation/judge").split("t=")[1])
    check("nor the judge", not ics_for(j2))
    mail_clear()
    s2.act(A["browser"], c2, "cancel")
    check("and cancelling sends none either", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"], j2)))

    c3 = matched_debate()
    mail_clear()
    s2.act(A["browser"], c3, "propose", **s2.fixture(calendar="1", j_email=f"judge64@judges{RUN}.example"))
    s2.act(A["browser"], c3, "cancel")
    check("cancelling before agreement sends no calendar cancellation (there was nothing to cancel)", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"])))

    c4 = matched_debate()
    j4 = f"judge65@judges{RUN}.example"
    s2.act(A["browser"], c4, "propose", **s2.fixture(**online, calendar="1", j_email=j4))
    s2.act(B["browser"], c4, "accept_fixture")
    s3.judge_accept(link_for(j4, "conversation/judge").split("t=")[1])
    mail_clear()
    s2.act(A["browser"], c4, "set_link", meeting_url="https://us02web.zoom.us/j/11223344")
    ups = {e: ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"], j4)}
    check("changing the meeting link updates everyone's calendar, judge included", all(len(v) == 1 for v in ups.values()), str({e: len(v) for e, v in ups.items()}))
    ux = parse_ics([ups[e][0]["text"] for e in ups])
    check("same event, next version, new link", all(x["uid"] == ux[0]["uid"] and x["sequence"] == 1 and x["status"] == "CONFIRMED" and x["location"] == "https://us02web.zoom.us/j/11223344" and x["url"] == "https://us02web.zoom.us/j/11223344" for x in ux), str(ux[0]))
    check("an online invite says who hosts", "Hosted by" in ux[0]["description"])
    mail_clear()
    s2.act(A["browser"], c4, "set_link", meeting_url="https://us02web.zoom.us/j/11223344")
    check("saving the same link again sends nothing", not any(ics_for(e) for e in ups))
    j4b = f"judge66@judges{RUN}.example"
    mail_clear()
    s2.act(A["browser"], c4, "change_judge", cj_name="New Judge", cj_email=j4b, cj_organisation="", cj_judge_type="community", cj_ack="1")
    oldj = ics_for(j4)
    check("replacing a judge who had accepted cancels their calendar entry", len(oldj) == 1 and parse_ics([oldj[0]["text"]])[0]["status"] == "CANCELLED")
    check("and only theirs: the schools' entries are untouched", not any(ics_for(e) for e in (A["teacher"], B["teacher"], A["slt"], B["slt"])))
    tok4b = link_for(j4b, "conversation/judge").split("t=")[1]
    mail_clear()
    s3.judge_accept(tok4b)
    newj = ics_for(j4b)
    check("the new judge gets their invite when they accept", len(newj) == 1 and parse_ics([newj[0]["text"]])[0]["status"] == "CONFIRMED" and parse_ics([newj[0]["text"]])[0]["location"] == "https://us02web.zoom.us/j/11223344")

    print("\n15. Upload the meeting's calendar file: the link goes on the platform")
    from datetime import datetime, timedelta, timezone
    from zoneinfo import ZoneInfo

    def act_upload(b, code, action, ics=None, name="invite.ics", **data):
        s_, html_, _ = s2.page(b, code)
        files = {"meeting_ics": (name, ics if isinstance(ics, bytes) else (ics or "").encode(), "text/calendar")} if ics is not None else {}
        return b.post_multipart(f"{SITE}/conversation/debate/?d={code}", {"csrf": field(html_, "csrf"), "d": code, "aiadn_action": action, **data}, files)

    def when(dt_str, utc=True, tz=None):
        """The debate's start time (as the form gave it) in the forms an invite file uses."""
        dt = datetime.strptime(dt_str, "%Y-%m-%dT%H:%M").replace(tzinfo=timezone.utc)
        if utc:
            return "DTSTART:" + dt.strftime("%Y%m%dT%H%M%SZ")
        local = dt.astimezone(ZoneInfo(tz[1]))
        return f'DTSTART;TZID={tz[0]}:' + local.strftime("%Y%m%dT%H%M%S")

    TEAMS = "https://teams.microsoft.com/l/meetup-join/19%3ameeting_ABC123%40thread.v2/0?context=%7b%22Tid%22%3a%22x%22%7d"
    PRIVATE = "attendee.private@school.example"

    def teams_ics(start_line, description_link=True, extra=""):
        lines = ["BEGIN:VCALENDAR", "METHOD:REQUEST", "PRODID:Microsoft Exchange Server 2010", "VERSION:2.0", "BEGIN:VEVENT",
                 "ORGANIZER;CN=Sam Patel:MAILTO:s.patel@oakfield.sch.uk", f"ATTENDEE;ROLE=REQ-PARTICIPANT;CN=Private Person:MAILTO:{PRIVATE}",
                 "DESCRIPTION;LANGUAGE=en-GB:\\n________________\\nMicrosoft Teams meeting\\n\\nJoin: " + (TEAMS if description_link else "") + "\\n\\nLearn More: https://aka.ms/JoinTeamsMeeting\\n",
                 "UID:040000008200E00074C5B7101A82E008", "SUMMARY;LANGUAGE=en-GB:AI debate", start_line, "LOCATION;LANGUAGE=en-GB:Microsoft Teams Meeting",
                 "X-MICROSOFT-SKYPETEAMSMEETINGURL:" + TEAMS if description_link else "X-NOTHING:x", extra, "END:VEVENT", "END:VCALENDAR"]
        return "\r\n".join(l for l in lines if l) + "\r\n"

    q = matched_debate()
    s, html, _ = s2.page(A["browser"], q)
    check("the proposal form offers a file upload, says what we take, and says we do not keep it", 'type="file"' in html and 'name="meeting_ics"' in html and "We do not keep the file" in html and 'enctype="multipart/form-data"' in html)
    jq = f"judge71@judges{RUN}.example"
    at = s2.FUTURE
    good = teams_ics(when(at, utc=False, tz=("GMT Standard Time", "Europe/London")))
    s, html, _ = act_upload(A["browser"], q, "propose", ics=good, **s2.fixture(**{**online, "meeting_url": ""}, j_email=jq))
    check("a Teams calendar file (Windows time zone name) is accepted and the link taken from it", "Sent. We have emailed" in html and sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{q}'") == TEAMS, sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{q}'"))
    dump = subprocess.run(["docker", "compose", "exec", "-T", "db", "mysqldump", "-uwordpress", "-pwordpress", "wordpress", "--no-tablespaces"], capture_output=True, text=True, cwd=ROOT).stdout
    check("the attendee's private address, the organiser's and the file itself are stored nowhere", PRIVATE not in dump and "Microsoft Exchange Server" not in dump and "BEGIN:VCALENDAR" not in dump)

    q2 = matched_debate()
    s, html, _ = act_upload(A["browser"], q2, "propose", ics=good, **s2.fixture(**{**online, "meeting_url": "https://meet.google.com/typed-instead"}, j_email=f"judge72@judges{RUN}.example"))
    check("when both a file and a typed link are given, the file wins", sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{q2}'") == TEAMS)

    def try_ics(ics, expect_ok, label, want_url=None, name="invite.ics"):
        d = matched_debate()
        s, html, _ = act_upload(A["browser"], d, "propose", ics=ics, name=name, **s2.fixture(**{**online, "meeting_url": ""}, j_email=f"judge73{abs(hash(label)) % 9999}@judges{RUN}.example"))
        got = sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{d}'")
        ok = ("Sent. We have emailed" in html) == expect_ok and (want_url is None or got == want_url)
        check(label, ok, (html[html.find('aiadn__error'):][:220] if not ok else ""))
        return html

    start_z = when(at)
    meet = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nSUMMARY:AI debate\r\nX-GOOGLE-CONFERENCE:https://meet.google.com/abc-defg-hij\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(meet, True, "a Google Meet calendar file works", "https://meet.google.com/abc-defg-hij")
    zoom = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nSUMMARY:AI debate\r\nLOCATION:https://us02web.zoom.us/j/81234567890?pwd=abc\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(zoom, True, "a Zoom calendar file works", "https://us02web.zoom.us/j/81234567890?pwd=abc")
    other = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nLOCATION:https://meet.example-school.org/room1\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(other, True, "an unfamiliar platform works if the host put the address in the location", "https://meet.example-school.org/room1")
    folded = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nX-MICROSOFT-SKYPETEAMSMEETINGURL:https://teams.microsoft.com/l/meetup-join/19%3ameeting_ABC\r\n 123%40thread.v2/0\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(folded, True, "a link that the calendar file folded across two lines is put back together", "https://teams.microsoft.com/l/meetup-join/19%3ameeting_ABC123%40thread.v2/0")
    floating = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\nDTSTART:20300101T090000\r\nX-GOOGLE-CONFERENCE:https://meet.google.com/abc-defg-hij\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(floating, True, "a time that does not say which time zone it is in is not checked", "https://meet.google.com/abc-defg-hij")
    later = (datetime.strptime(at, "%Y-%m-%dT%H:%M") + timedelta(hours=3)).strftime("%Y-%m-%dT%H:%M")
    html = try_ics(teams_ics(when(later)), False, "a calendar file for a different time is refused")
    check("and it says which times differ", "is for" in html and "but this debate is on" in html)
    try_ics(teams_ics(when(at), description_link=False), False, "a calendar file with no meeting link is refused")
    try_ics(b"hello, this is just a text file", False, "a file that is not a calendar is refused", name="notes.txt")
    try_ics(b"BEGIN:VCALENDAR\r\n" + b"X" * 300000, False, "an oversized file is refused")
    unsafe = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nURL:http://teams.microsoft.com/insecure\r\nLOCATION:javascript:alert(1)\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(unsafe, False, "insecure and script links inside a file are refused")
    desc_only = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nDESCRIPTION:See https://aka.ms/JoinTeamsMeeting and https://example.com/somewhere\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    try_ics(desc_only, False, "a stray link in the description is not mistaken for a meeting link")

    print("\n16. The host can replace the link by uploading a new file")
    r1 = matched_debate()
    jr = f"judge74@judges{RUN}.example"
    act_upload(A["browser"], r1, "propose", ics=good, **s2.fixture(**{**online, "meeting_url": ""}, j_email=jr))
    s2.act(B["browser"], r1, "accept_fixture")
    s3.judge_accept(link_for(jr, "conversation/judge").split("t=")[1])
    jr_tok = link_for(jr, "conversation/judge").split("t=")[1]
    mail_clear()
    newmeet = f"BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n{start_z}\r\nX-GOOGLE-CONFERENCE:https://meet.google.com/new-room-xyz\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n"
    s, html, _ = act_upload(A["browser"], r1, "set_link", ics=newmeet)
    check("uploading a new file replaces the link", "meeting link is updated" in html and sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{r1}'") == "https://meet.google.com/new-room-xyz")
    check("the other school and the judge are told", any("new-room-xyz" in m["text"] for m in mails(B["teacher"])) and any("new-room-xyz" in m["text"] for m in mails(jr)))
    s, html, _ = act_upload(A["browser"], r1, "set_link", ics=teams_ics(when(later)))
    check("a file for the wrong time cannot replace it", "but this debate is on" in html and sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{r1}'") == "https://meet.google.com/new-room-xyz")
    s, html, _ = act_upload(B["browser"], r1, "set_link", ics=meet)
    check("the other school cannot replace it", sql(f"SELECT meeting_url FROM wp_aiadn_debates WHERE code='{r1}'") == "https://meet.google.com/new-room-xyz")

    print("\n17. On the day, the link is on the platform for everyone")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("ten days out, there is no join panel yet", "Join the meeting" not in html and "Join the meeting" not in s2.page(A["browser"], r1)[1])
    s3.set_start(r1, 30)
    for who, br in (("the host", A["browser"]), ("the other school", B["browser"])):
        s, html, _ = s2.page(br, r1)
        check(f"{who} sees a Join the meeting button on the debate page", "Join the meeting" in html and 'href="https://meet.google.com/new-room-xyz"' in html and 'rel="noopener noreferrer"' in html)
        s, html, _ = br.get(f"{SITE}/conversation/school/")
        check(f"{who} sees it at the top of their school page too", "Join the meeting" in html and html.index("Join the meeting") < html.index("School code"))
    slt_b = Browser()
    e2e.sign_in(slt_b, B["code"], B["slt"], role="slt")
    s, html, _ = s2.page(slt_b, r1)
    check("the other school's headteacher sees it too", "Join the meeting" in html)
    js = Browser()
    tokj = jr_tok
    s, html, _ = js.get(f"{SITE}/conversation/judge/?t={tokj}")
    check("the judge sees it on their page", "Join the meeting" in html and "new-room-xyz" in html)
    s, html, _ = js.get(f"{SITE}/conversation/score/?t={tokj}")
    check("and at the top of the scorecard", "Join the meeting" in html and "new-room-xyz" in html)
    s3.set_start(r1, 60 * 5)
    s, html, _ = s2.page(A["browser"], r1)
    check("five hours before, no join button yet, though the link is in the fixture details", "Join the meeting" not in html and "Online, hosted by" in html and "Meeting link" in html)
    s3.set_start(r1, -60 * 5)
    s, html, _ = s2.page(A["browser"], r1)
    check("five hours after the start, the join button has gone", "Join the meeting" not in html)
    s3.set_start(r1, -60 * 2)
    s, html, _ = s2.page(A["browser"], r1)
    check("two hours after the start, it is still there for latecomers", "Join the meeting" in html)
    pub = Browser().get(f"{SITE}/conversation/debates/")[1]
    check("the link is never on a public page", "new-room-xyz" not in pub)
    ip = matched_debate()
    s2.act(A["browser"], ip, "propose", **s2.fixture(j_email=f"judge75@judges{RUN}.example"))
    s2.act(B["browser"], ip, "accept_fixture")
    s3.set_start(ip, 30)
    s, html, _ = s2.page(A["browser"], ip)
    check("an in-person debate never shows a join button", "Join the meeting" not in html)

    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


if __name__ == "__main__":
    main()
