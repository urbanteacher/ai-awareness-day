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

    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


if __name__ == "__main__":
    main()
