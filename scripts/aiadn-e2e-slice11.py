#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 11: the homepage entry and Nominate a school.

    python3 scripts/aiadn-e2e-slice11.py
"""
import importlib.util
import os
import re
import sys
from datetime import datetime, timezone

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
spec = importlib.util.spec_from_file_location("s10", os.path.join(HERE, "aiadn-e2e-slice10.py"))
s10 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s10)
s7, s6, s3, s2, e2e = s10.s7, s10.s6, s10.s3, s10.s2, s10.e2e

SITE, Browser, check, field, loc = e2e.SITE, e2e.Browser, e2e.check, e2e.field, e2e.loc
mails, mail_clear, link_for, code_for = e2e.mails, e2e.mail_clear, e2e.link_for, e2e.code_for
sql, RUN, php = s3.sql, s3.RUN, s10.php
U = RUN.upper()


def nominate(name, email, org, school, school_email, extra=None):
    b = Browser()
    data = {"aiadn_action": "nominate", "name": name, "email": email, "org": org, "school": school, "school_email": school_email}
    data.update(extra or {})
    s, html, h = b.post(f"{SITE}/conversation/nominate/", data)
    return b, html


def confirm(b, html, email, code=None):
    ref = re.search(r'name="ref" value="([a-f0-9]{32})"', html)
    code = code or code_for(email)
    return b.post(f"{SITE}/conversation/nominate/", {"aiadn_action": "nominate_confirm", "ref": ref.group(1) if ref else "", "code": code or "000000"})


def main():
    e2e.clear_rate_limits()
    mail_clear()
    admin_login = f"aiadn6{RUN}admin"
    s6.drop_users()
    s6.make_user(admin_login, 'a:1:{s:13:"administrator";b:1;}', 10)
    try:
        run(admin_login)
    finally:
        s6.drop_users()
    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


def run(admin_login):
    print("\n1. The homepage entry")
    s, home, _ = Browser().get(f"{SITE}/")
    box = home.split('class="hero-strand-feature"')[1].split("</section>")[0] if 'class="hero-strand-feature"' in home else ""
    copy = home.split('class="hero-copy"')[1].split('class="hero-strand-feature"')[0] if 'class="hero-strand-feature"' in home else ""
    check("the box carries the name National AI Conversation 2027", "National AI Conversation 2027" in box)
    check("the buttons are in the main area, not the box", "Join the National Conversation" in copy and "conversation/register" in copy and "Check your AI readiness" in copy and "hero-cta__btn" not in box)
    check("with the nominate and sign-in links under them", "Nominate a school you work with" in copy and "conversation/nominate" in copy and "conversation/join" in copy and "hero-cta-more" in copy)
    check("the box asks one thing: the debate motion, in place of the old question", "hero-strand-feature__summary" in box and "Debate it" in box and "This house believes users, not companies" in box and "Would you tell an AI your secret" not in box)
    check("there are no partner or judge buttons in the hero (safeguarding)", not re.search(r"become a partner|volunteer to judge|judge a debate", home.split("</section>")[0], re.I))
    check("the old 'Get involved' pair is gone from the hero", "hero-cta__btn--primary\">Get involved" not in home)
    js = open(os.path.join(ROOT, "assets/js/main.js")).read()
    check("all five strands have a motion", all(t in js for t in ("what AI remembers about them", "guide students rather than give answers", "not be eligible for creative prizes", "environmental cost of every request", "formal say in how AI is used in schools")))
    ts = re.search(r'class="hero-countdown"[^>]*data-event-ts="(\d+)"', home)
    expected = datetime(2027, 1, 1, tzinfo=timezone.utc).timestamp() * 1000
    check("a countdown to the day the conversation opens, outside the box", bool(ts) and abs(int(ts.group(1)) - expected) < 86400000 and "The National AI Conversation opens in" in home and "hero-countdown" not in box and "hero-countdown-wrap" in copy, str(ts.group(1) if ts else None))
    check("the countdown has days, hours, minutes and seconds", all(f'data-unit="{u}"' in home for u in ("days", "hours", "minutes", "seconds")))
    check("live totals appear once there are enough schools", re.search(r"<strong>[\d,]+</strong> schools", home) is not None and "students taking part" in home)
    css = open(os.path.join(ROOT, "assets/css/layout/hero-presentation.css")).read()
    check("the styles are in the loaded stylesheet", "hero-strand-feature__motion" in css and "hero-countdown__item" in css)

    print("\n2. The nomination form")
    s, html, _ = Browser().get(f"{SITE}/conversation/nominate/")
    check("the page explains how it works and what it does not do", "Nominate a school" in html and "one short email" in html and "cannot add a link or a pitch" in html and "never email the same school twice" in html)
    check("it has no message box", "<textarea" not in html)
    nom = f"pat{RUN}@partner{RUN}.org"
    sch_email = f"head@nominee{RUN}.sch.uk"
    b, html = nominate("Pat Partner", nom, f"Partner Org {U}", f"Nominee Primary {U}", f"someone{RUN}@gmail.com")
    check("a personal mailbox for the school is refused", "own domain" in html and code_for(nom) is None)
    b, html = nominate("Pat Partner", nom, "", f"Nominee Primary {U}", nom)
    check("so is the nominator's own address", "own domain" in html)
    b, html = nominate("P", nom, "", f"Nominee Primary {U}", sch_email)
    check("a missing name is asked again", "Enter your name" in html)
    b, html = nominate("Bot", nom, "", f"Nominee Primary {U}", sch_email, {"website": "x"})
    check("a robot that fills the hidden field gets nothing sent", code_for(nom) is None and not mails(sch_email))

    print("\n3. Confirming the nominator, then one invitation")
    mail_clear()
    b, html = nominate("Pat Partner", nom, f"Partner Org {U}", f"Nominee Primary {U}", sch_email)
    check("the nominator is emailed a code", "Check your email" in html and code_for(nom) is not None)
    check("the school has heard nothing yet", not mails(sch_email))
    s, bad, _ = confirm(b, html, nom, code="000000")
    check("a wrong code sends nothing", "couldn't match" in bad.replace("&#039;", "'") and not mails(sch_email))
    s, ok, _ = confirm(b, html, nom)
    check("the right code invites the school", "Thank you" in ok and len(mails(sch_email)) == 1)
    inv = mails(sch_email)[0]
    check("the invitation says who nominated, with their confirmed address, and that the name was not checked", "Pat Partner" in inv["text"] and f"Partner Org {U}" in inv["text"] and nom in inv["text"] and "not checked" in inv["text"])
    check("it has a register link and a do-not-contact link, and says it is the only email", "conversation/register/?nom=" in inv["text"] and "conversation/nominate/?stop=" in inv["text"] and "only email" in inv["text"])
    check("the school's address is cleared as soon as it is sent", sql(f"SELECT school_email FROM wp_aiadn_nominations WHERE nominator_email='{nom}' AND status='sent'") == "")
    check("the nomination is recorded as sent", sql(f"SELECT COUNT(*) FROM wp_aiadn_nominations WHERE nominator_email='{nom}' AND status='sent'") == "1")

    print("\n4. A school is invited once")
    other = f"lee{RUN}@other{RUN}.org"
    mail_clear()
    b2, html2 = nominate("Lee Other", other, "", f"Nominee Primary {U}", sch_email)
    confirm(b2, html2, other)
    check("a second nominator for the same address sends nothing more", not mails(sch_email))
    check("that one is recorded as blocked", sql(f"SELECT COUNT(*) FROM wp_aiadn_nominations WHERE nominator_email='{other}' AND status='blocked'") != "0")

    print("\n5. Limits")
    e2e.clear_rate_limits()
    lim = f"quick{RUN}@lim{RUN}.org"
    for i in range(3):
        nominate("Quick", lim, "", f"Limit School {i}", f"h{i}@limit{i}{RUN}.sch.uk")
    b, html = nominate("Quick", lim, "", "Limit School 4", f"h4@limit4{RUN}.sch.uk")
    check("a nominator can send three a day, and no more", "nominated 3 schools today" in html)

    print("\n6. The school registers from the invitation")
    reg_link = re.search(r"https?://[^\s]+conversation/register/\?nom=[a-f0-9]{40}", inv["text"]).group(0)
    s, page, _ = Browser().get(reg_link)
    check("the registration page says someone nominated the school, and fills in its name", "Someone has nominated your school" in page and f"Nominee Primary {U}" in page)
    check("registering stays the school's choice", "Registering is your choice" in page and "your headteacher approves" in page)
    s, page, _ = Browser().get(f"{SITE}/conversation/register/?nom={'0' * 40}")
    check("a made-up link shows no such notice", "Someone has nominated" not in page)
    domain = f"nominee{RUN}.sch.uk"
    tb = Browser()
    data = {"aiadn_action": "register", "school_name": f"Nominee Primary {U}", "postcode": "NE1 1AA", "age_phases[]": ["primary"], "mat_name": "", "teacher_name": "Test Teacher", "job_title": "", "email": f"lead@{domain}", "slt_email": sch_email, "ref_org": "", "ref_email": "", "agree": "1", "nom": reg_link.split("nom=")[1]}
    s, html, h = tb.post(f"{SITE}/conversation/register/", data, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h))
    code = s2.verify_register(tb, ref.group(1), f"lead@{domain}") if ref else None
    check("the school registers", bool(code))
    check("the nomination is marked registered", sql(f"SELECT status FROM wp_aiadn_nominations WHERE nominator_email='{nom}' AND registered_school_id>0") == "registered")
    check("the organisation the nominator named becomes 'who introduced you', still waiting for the headteacher", sql(f"SELECT CONCAT(r.status,'|',r.referrer_email) FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id AND r.subject_type='school' WHERE s.code='{code}'") == f"named|{nom}")
    link = link_for(sch_email, "approve")
    mail_clear()
    s, page, _ = Browser().get(link)
    check("the headteacher is asked whether to tell them, unticked", f"Partner Org {U}" in page and 'name="share_referral"' in page and not mails(nom))

    e2e.clear_rate_limits()
    print("\n7. Do not contact me")
    stop_link = re.search(r"https?://[^\s]+conversation/nominate/\?stop=[a-f0-9]{40}", inv["text"]).group(0)
    s, page, _ = Browser().get(stop_link)
    before = sql("SELECT COUNT(*) FROM wp_aiadn_nominations WHERE status='opted_out'")
    check("looking at the link changes nothing", "Do not contact me" in page and sql("SELECT COUNT(*) FROM wp_aiadn_nominations WHERE status='opted_out'") == before)
    s, page, _ = Browser().post(f"{SITE}/conversation/nominate/", {"aiadn_action": "nominate_stop", "stop": stop_link.split("stop=")[1]})
    check("the button stops it for good", "will not contact this address" in page and int(sql("SELECT COUNT(*) FROM wp_aiadn_nominations WHERE status='opted_out'")) > int(before))
    mail_clear()
    third = f"sam{RUN}@third{RUN}.org"
    b3, html3 = nominate("Sam Third", third, "", f"Nominee Primary {U}", sch_email)
    confirm(b3, html3, third)
    check("after that, nobody can invite that address", not mails(sch_email))
    s, page, _ = Browser().get(f"{SITE}/conversation/nominate/?stop={'0' * 40}")
    check("a made-up stop link shows nothing", "no longer valid" in page)

    print("\n8. Privacy and the programme team")
    php(f'AIADN_Privacy::erase_email("{nom}");')
    check("the nominator can ask for their details to go, and they do", sql(f"SELECT COUNT(*) FROM wp_aiadn_nominations WHERE nominator_email='{nom}'") == "0")
    dry = php('echo "R:" . json_encode(AIADN_Privacy::end_of_campaign(null, true));')
    check("the end-of-campaign deletion covers nominations", "Nominations: who nominated" in dry)
    admin, st, h = s6.wp_login(admin_login)
    s, page, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the programme page counts nominations, with no names", "Nominations" in page and "Invitations sent" in page and "Pat Partner" not in page and nom not in page)


if __name__ == "__main__":
    main()
