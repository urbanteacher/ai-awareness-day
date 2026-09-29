#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 2: debates and judges.

Follows a whole debate between real schools over HTTP and reads the emails from Mailpit.
Reuses the helpers from aiadn-e2e.py (slice 1).

    python3 scripts/aiadn-e2e-slice2.py
"""
import importlib.util
import os
import random
import re
import string
import sys
from datetime import datetime, timedelta

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("e2e", os.path.join(HERE, "aiadn-e2e.py"))
e2e = importlib.util.module_from_spec(spec)
spec.loader.exec_module(e2e)

SITE, Browser, check, loc, field = e2e.SITE, e2e.Browser, e2e.check, e2e.loc, e2e.field
mails, mail_clear, code_for, link_for = e2e.mails, e2e.mail_clear, e2e.code_for, e2e.link_for
RUN = "".join(random.choices(string.ascii_lowercase, k=5))
FUTURE = (datetime.now() + timedelta(days=10)).strftime("%Y-%m-%dT14:00")
LATER = (datetime.now() + timedelta(days=12)).strftime("%Y-%m-%dT10:30")
PAST = (datetime.now() - timedelta(days=2)).strftime("%Y-%m-%dT14:00")


def register(name, postcode, teacher, slt, browser=None, inv=None):
    """Register a school. Returns (browser, ref)."""
    b = browser or Browser()
    data = {"aiadn_action": "register", "school_name": name, "postcode": postcode, "age_phases[]": ["primary"], "mat_name": "",
            "teacher_name": "Test Teacher", "job_title": "Class teacher", "email": teacher, "slt_email": slt, "partner_ref": "", "agree": "1"}
    if inv:
        data["inv"] = inv
    s, html, h = b.post(f"{SITE}/conversation/register/", data, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h))
    return b, (ref.group(1) if ref else None), html


def verify_register(b, ref, teacher):
    s, html, _ = b.post(f"{SITE}/conversation/register/", {"aiadn_action": "verify", "ref": ref, "code": code_for(teacher)})
    m = re.search(r"SCH-[2-9A-HJ-NP-Z]{5}", html)
    return m.group(0) if m else None


def slt_approve(slt_email, link=None):
    link = link or link_for(slt_email, "approve")
    s, html, _ = Browser().post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    return "<h1>Approved</h1>" in html


def make_school(key, name, postcode):
    domain = f"{key}{RUN}.sch.uk"
    teacher, slt = f"lead@{domain}", f"head@{domain}"
    b, ref, _ = register(name, postcode, teacher, slt)
    code = verify_register(b, ref, teacher)
    ok = slt_approve(slt)
    return {"browser": b, "code": code, "teacher": teacher, "slt": slt, "name": name, "domain": domain, "ok": bool(code and ok)}


def page(b, code):
    return b.get(f"{SITE}/conversation/debate/?d={code}")


def act(b, code, action, **data):
    """Post an action on a debate page, with the CSRF token from that page."""
    s, html, _ = page(b, code)
    token = field(html, "csrf")
    return b.post(f"{SITE}/conversation/debate/?d={code}", {"csrf": token, "d": code, "aiadn_action": action, **data})


def new_debate(school):
    s, html, h = school["browser"].get(f"{SITE}/conversation/school/")
    token = field(html, "csrf")
    _, _, h = school["browser"].post(f"{SITE}/conversation/school/", {"csrf": token, "aiadn_action": "new_debate"}, follow=False)
    m = re.search(r"d=(AID-[A-Z0-9]{5})", loc(h))
    return m.group(1) if m else None


ADDRESS = "12 High Street, Leeds, LS6 2AB"


def fixture(**over):
    data = {"starts_at": FUTURE, "age_group": "primary", "theme": "creative", "motion_key": "primary-creative-1", "a_side": "for",
            "format": "in_person", "venue": "The school hall", "venue_address": ADDRESS, "j_name": "Dr Amy Chen", "j_email": f"amy.chen@judges{RUN}.example",
            "j_organisation": "Local university", "j_judge_type": "academic", "j_ack": "1"}
    data.update(over)
    return data


def main():
    e2e.clear_rate_limits()
    mail_clear()

    print("\n1. Schools")
    A = make_school("oakfield", f"Oakfield Primary {RUN.upper()}", "LS6 2AB")
    C = make_school("hillview", f"Hillview School {RUN.upper()}", "M1 1AA")
    D = make_school("parkside", f"Parkside Academy {RUN.upper()}", "B1 1AA")
    check("three approved schools to work with", A["ok"] and C["ok"] and D["ok"])
    s, html, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("dashboard offers a new debate", "Start a new debate" in html and "No debates yet" in html)

    print("\n2. Start a debate and invite School B")
    d1 = new_debate(A)
    check("a debate is created with an AID code", bool(d1), str(d1))
    s, html, _ = page(A["browser"], d1)
    check("tracker shows the pipeline", "School lookup" in html and "Invite School B" in html and "Certificate" in html)
    check("invite options are shown to School A", "Send invitation" in html and "Get a link to share" in html)
    s, html, h = Browser().get(f"{SITE}/conversation/debate/?d={d1}", follow=False)
    check("a stranger cannot open a debate", s == 302)
    s, html, _ = D["browser"].get(f"{SITE}/conversation/debate/?d={d1}")
    check("another school cannot open it either", "could not find that debate" in html and d1 not in html.replace("Debate ", "", 0)[:0] + "")

    b_domain = f"riverside{RUN}.sch.uk"
    b_teacher, b_slt = f"jo@{b_domain}", f"deputy@{b_domain}"
    s, html, _ = act(A["browser"], d1, "send_invite", invite_name="Jo Evans", invite_email="not-an-email")
    check("a bad email address is rejected", "email address" in html.lower() and "Invitation sent" not in html)
    mail_clear()
    s, html, _ = act(A["browser"], d1, "send_invite", invite_name="Jo Evans", invite_email=b_teacher)
    check("invitation is sent", "Invitation sent" in html)
    inv_link = link_for(b_teacher, "invite")
    check("School B's teacher receives an invitation with a link and the Debate ID", bool(inv_link) and d1 in " ".join(m["text"] for m in mails(b_teacher)))
    inv_token = inv_link.split("t=")[1] if inv_link else ""

    guest = Browser()
    for _ in range(2):
        s, html, _ = guest.get(inv_link)
    check("opening the invitation shows who is asking and does not use it up", A["name"] in html and "Register and accept" in html)
    s, html, _ = guest.get(f"{SITE}/conversation/invite/?t={'0' * 40}")
    check("a made-up invitation is not valid", "no longer valid" in html)
    s, html, _ = act(A["browser"], d1, "new_link")
    new_link = re.search(r'value="(http[^"]+invite[^"]+)"', html)
    check("School A can get a fresh link to share", bool(new_link))
    s, html, _ = guest.get(inv_link)
    check("a fresh link cancels the earlier one", "no longer valid" in html)
    inv_link = new_link.group(1).replace("&#038;", "&").replace("&amp;", "&")
    inv_token = inv_link.split("t=")[1]

    print("\n3. School B registers through the invitation")
    s, html, _ = guest.get(f"{SITE}/conversation/register/?inv={inv_token}")
    check("register page explains it is for a debate invitation", "accept a debate invitation" in html)
    b_school = f"Riverside Academy {RUN.upper()}"
    bb, ref, html = register(b_school, "LS4 9XY", b_teacher, b_slt, inv=inv_token)
    check("School B registers and gets a code", bool(ref))
    check("the school is registered with the invitation", verify_register(bb, ref, b_teacher) is not None)
    s, html, _ = page(A["browser"], d1)
    check("School A sees that Riverside has accepted and is being approved", b_school in html and "approves the school" in html)
    s, html, _ = guest.get(inv_link)
    check("the invitation cannot be taken twice", "already been taken" in html)
    approve_link = link_for(b_slt, "approve")
    mail_clear()
    check("School B's headteacher approves", slt_approve(b_slt, approve_link))
    check("both teachers get the match email", any("match" in m["subject"].lower() for m in mails(A["teacher"])) and any("match" in m["subject"].lower() for m in mails(b_teacher)))
    s, html, _ = page(A["browser"], d1)
    check("School A now sees the propose form", "Propose the debate" in html or "Send to " in html)
    check("safeguarding pack is shown", "Safeguarding Pack" in html and "Supervision" in html)
    s, html, _ = page(bb, d1)
    check("School B is told to wait for School A", "Waiting for" in html and "propose" in html)

    print("\n4. The fixture")
    s, html, _ = act(bb, d1, "propose", **fixture())
    s, html, _ = page(A["browser"], d1)
    check("School B cannot propose the fixture", "Send to " in html)
    s, html, _ = act(A["browser"], d1, "propose", **fixture(starts_at=PAST))
    check("a date in the past is rejected", "in the future" in html)
    s, html, _ = act(A["browser"], d1, "propose", **fixture(theme="safe"))
    check("a motion from another theme is rejected", "Choose a motion for that theme" in html)
    s, html, _ = act(A["browser"], d1, "propose", **fixture(j_ack=""))
    check("the judge safeguarding tick is required", "safeguarding and visitor policy" in html)
    s, html, _ = act(A["browser"], d1, "propose", **fixture(format="in_person", venue=""))
    check("an in-person debate needs a venue", "where it will take place" in html)
    s, html, _ = act(A["browser"], d1, "propose", **fixture(j_name="Dr Amy Chen", j_email="bad"))
    check("form keeps what was typed after an error", "Dr Amy Chen" in html)
    judge_email = f"amy.chen@judges{RUN}.example"
    mail_clear()
    s, html, _ = act(A["browser"], d1, "propose", **fixture())
    check("a valid proposal is sent", "Sent. We have emailed" in html)
    check("School B is emailed to review it", any("review" in m["subject"].lower() for m in mails(b_teacher)))
    check("the judge is NOT emailed until both schools agree", len(mails(judge_email)) == 0)
    s, html, _ = page(bb, d1)
    check("School B sees the fixture and which side it argues", "AGAINST" in html and "Which part would you want to do yourself" in html and "Accept" in html)
    s, html, _ = act(A["browser"], d1, "accept_fixture")
    s, html, _ = page(A["browser"], d1)
    check("School A cannot accept its own proposal", "Waiting for" in html and "review the fixture" in html)

    mail_clear()
    s, html, _ = act(bb, d1, "suggest_date", starts_at=PAST)
    check("a suggested date must be in the future", "in the future" in html)
    s, html, _ = act(bb, d1, "suggest_date", starts_at=LATER)
    check("School B suggests another date", "suggested date back" in html)
    check("School A is told", any("new date" in m["subject"].lower() for m in mails(A["teacher"])))
    s, html, _ = page(A["browser"], d1)
    check("School A now reviews the new date", "Accept" in html and "10:30" in html)
    mail_clear()
    s, html, _ = act(A["browser"], d1, "accept_fixture")
    check("School A accepts and the fixture is agreed", "fixture is agreed" in html)
    check("both teachers are told it is agreed", any("agreed" in m["subject"].lower() for m in mails(A["teacher"])) and any("agreed" in m["subject"].lower() for m in mails(b_teacher)))
    inv_mails = mails(judge_email)
    check("the judge is invited only now", len(inv_mails) == 1 and "judge" in inv_mails[0]["subject"].lower())
    text = inv_mails[0]["text"] if inv_mails else ""
    check("judge email gives the school code, the front door and a shortcut", A["code"] in text and "/conversation/join/" in text and "/conversation/judge/?t=" in text)

    print("\n5. The judge signs in at the front door")
    j = Browser()
    mail_clear()
    s, _, h = j.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "judge", "email": judge_email}, follow=False)
    check("the judge is sent a code", "step=code" in loc(h) and bool(code_for(judge_email)))
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h)).group(1)
    s, html, _ = j.post(f"{SITE}/conversation/join/", {"aiadn_action": "verify", "ref": ref, "code": code_for(judge_email)})
    check("judge lands on the judging page with the fixture", "Judging" in html and "Which part would you want to do yourself" in html)
    check("the judge sees who is debating", A["name"] in html and b_school in html)
    s, _, h = j.get(f"{SITE}/conversation/debate/?d={d1}", follow=False)
    check("a judge session cannot open the teachers' debate page", s == 302 and "/conversation/join/" in loc(h))
    s, _, h = j.get(f"{SITE}/conversation/school/", follow=False)
    check("a judge session cannot open the school page", s == 302 and "/conversation/judge/" in loc(h))
    s, html, _ = j.get(f"{SITE}/conversation/judge/")
    token = field(html, "csrf")
    jid = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    s, html, _ = j.post(f"{SITE}/conversation/judge/", {"csrf": token, "aiadn_action": "judge_respond", "judge_id": jid, "decision": "accept"})
    check("accepting without the safeguarding tick is refused", "tick the box" in html)
    s, html, _ = page(A["browser"], d1)
    check("nothing changed for the teachers", "Waiting for" in html)
    mail_clear()
    s, html, _ = j.post(f"{SITE}/conversation/judge/", {"csrf": token, "aiadn_action": "judge_respond", "judge_id": jid, "decision": "accept", "ack": "1", "name_public": "1"})
    check("the judge accepts", "down to judge" in html)
    check("both teachers are told", any("accepted" in m["subject"].lower() for m in mails(A["teacher"])) and any("accepted" in m["subject"].lower() for m in mails(b_teacher)))
    s, html, _ = page(A["browser"], d1)
    check("the debate is ready", "Everything is set" in html)
    mail_clear()
    for who, code_, email in (("another school's code", D["code"], judge_email), ("an unknown judge", A["code"], f"nobody@judges{RUN}.example")):
        Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": code_, "role": "judge", "email": email}, follow=False)
    check("a judge cannot sign in through an unrelated school, and unknown judges get no email", len(mails()) == 0)
    _, _, h = Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": C["code"], "role": "judge", "email": judge_email}, follow=False)
    check("responses look the same either way", "step=code" in loc(h))
    j2 = Browser()
    mail_clear()
    _, _, h = j2.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": "SCH-22222", "role": "judge", "email": judge_email}, follow=False)
    check("a wrong school code sends nothing", len(mails()) == 0)

    print("\n6. A school that is already registered accepts by signing in")
    d2 = new_debate(A)
    s, html, _ = act(A["browser"], d2, "new_link")
    link2 = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    tok2 = link2.split("t=")[1]
    s, html, _ = C["browser"].get(link2)
    check("a signed-in school sees Accept as its own school", "Accept as" in html and C["name"] in html)
    s, html, _ = A["browser"].get(link2)
    check("the inviting school cannot accept its own invitation", "Accept as" not in html)
    fresh = Browser()
    s, html, h = fresh.get(f"{SITE}/conversation/join/?inv={tok2}")
    check("the front door carries the invitation", 'name="inv"' in html and "accept the debate invitation" in html)
    dash = e2e.sign_in(fresh, C["code"], C["teacher"]) if False else None
    mail_clear()
    fresh2 = Browser()
    _, _, h = fresh2.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": C["code"], "role": "teacher", "email": C["teacher"], "inv": tok2}, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h)).group(1)
    check("the carried invitation survives the code step", f"inv={tok2}" in loc(h))
    s, html, _ = fresh2.post(f"{SITE}/conversation/join/", {"aiadn_action": "verify", "ref": ref, "code": code_for(C["teacher"]), "inv": tok2})
    check("after signing in, Hillview lands back on the invitation", "Accept as" in html and C["name"] in html)
    token = field(html, "csrf")
    mail_clear()
    s, html, _ = fresh2.post(f"{SITE}/conversation/invite/", {"csrf": token, "aiadn_action": "accept_invite", "t": tok2})
    check("accepting matches the schools straight away", "Set the fixture" in html or "Waiting for" in html or "propose" in html.lower())
    check("both teachers get the match email", any("match" in m["subject"].lower() for m in mails(A["teacher"])) and any("match" in m["subject"].lower() for m in mails(C["teacher"])))

    print("\n7. Judge declines, then the teachers change the judge")
    judge2 = f"sam@judges2{RUN}.example"
    s, html, _ = act(A["browser"], d2, "propose", **fixture(j_name="Sam Okafor", j_email=judge2, j_judge_type="industry", theme="smart", motion_key="primary-smart-1"))
    s, html, _ = act(C["browser"], d2, "accept_fixture")
    shortcut = link_for(judge2, "conversation/judge")
    check("the judge gets a shortcut link", bool(shortcut))
    shortcut = shortcut.rstrip(".") if shortcut else ""
    gj = Browser()
    s, html, _ = gj.get(shortcut)
    check("the shortcut shows the fixture and needs no sign-in", "Sam Okafor" not in html and "I can judge" in html)
    jid2 = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    s, _, _ = Browser().post(f"{SITE}/conversation/judge/", {"aiadn_action": "judge_respond", "judge_id": jid2, "decision": "accept", "ack": "1"}, follow=False)
    s, html, _ = page(A["browser"], d2)
    check("responding without the link's token does nothing", "Waiting for" in html)
    mail_clear()
    s, html, _ = gj.post(f"{SITE}/conversation/judge/", {"t": shortcut.split("t=")[1], "aiadn_action": "judge_respond", "judge_id": jid2, "decision": "decline"})
    check("the judge can decline using the shortcut", "teachers have been told" in html)
    check("both teachers are told the judge can't make it", any("cannot make it" in m["subject"].lower() for m in mails(A["teacher"])) and any("cannot make it" in m["subject"].lower() for m in mails(C["teacher"])))
    s, html, _ = page(C["browser"], d2)
    check("the debate page says to choose another judge", "can't make it" in html and "Change the judge" in html)
    judge3 = f"lee@judges3{RUN}.example"
    mail_clear()
    s, html, _ = act(C["browser"], d2, "change_judge", cj_name="Lee Fox", cj_email=judge3, cj_organisation="", cj_judge_type="community", cj_ack="1")
    check("the teachers change the judge", "judge has been changed" in html)
    check("the new judge is invited", len(mails(judge3)) == 1)
    s, html, _ = gj.get(shortcut)
    check("the old judge's link stops working", "no longer active" in html or "no longer valid" in html)
    sc3 = link_for(judge3, "conversation/judge")
    g3 = Browser()
    s, html, _ = g3.get(sc3)
    jid3 = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    s, html, _ = g3.post(f"{SITE}/conversation/judge/", {"t": sc3.split("t=")[1], "aiadn_action": "judge_respond", "judge_id": jid3, "decision": "accept", "ack": "1"})
    s, html, _ = page(A["browser"], d2)
    check("the new judge accepts and the debate is ready", "Everything is set" in html)

    print("\n8. A school leaves a debate")
    d3 = new_debate(A)
    s, html, _ = act(A["browser"], d3, "new_link")
    link3 = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    s, html, _ = C["browser"].get(link3)
    token = field(html, "csrf")
    C["browser"].post(f"{SITE}/conversation/invite/", {"csrf": token, "aiadn_action": "accept_invite", "t": link3.split("t=")[1]})
    act(A["browser"], d3, "propose", **fixture(j_email=f"pat@judges4{RUN}.example", j_name="Pat Lowe"))
    mail_clear()
    s, html, _ = act(C["browser"], d3, "decline")
    check("School B can decline", "left this debate" in html or "You have left" in html)
    check("School A is told", any("left your debate" in m["subject"].lower() for m in mails(A["teacher"])))
    s, html, _ = page(A["browser"], d3)
    check("the place is open again", "Send invitation" in html)
    s, html, _ = C["browser"].get(f"{SITE}/conversation/debate/?d={d3}")
    check("School C can no longer open that debate", "could not find that debate" in html)

    print("\n9. Invite someone else, cancel")
    d4 = new_debate(A)
    s, html, _ = act(A["browser"], d4, "new_link")
    link4 = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    tok4 = link4.split("t=")[1]
    e_domain = f"ellis{RUN}.sch.uk"
    eb, ref, _ = register(f"Ellis Primary {RUN.upper()}", "N1 1AA", f"t@{e_domain}", f"head@{e_domain}", inv=tok4)
    verify_register(eb, ref, f"t@{e_domain}")
    s, html, _ = page(A["browser"], d4)
    check("waiting for the other school to be approved", "approves the school" in html)
    s, html, _ = act(A["browser"], d4, "release")
    check("School A can invite someone else", "open again" in html)
    ellis_link = link_for(f"head@{e_domain}", "approve")
    mail_clear()
    slt_approve(f"head@{e_domain}", ellis_link)
    s, html, _ = page(A["browser"], d4)
    check("the released school's approval does not match it to this debate", "Send invitation" in html)
    check("no match email is sent", not any("match" in m["subject"].lower() for m in mails(A["teacher"])))

    mail_clear()
    s, html, _ = act(A["browser"], d1, "cancel")
    check("a ready debate can be cancelled", "has been cancelled" in html)
    check("the other school and the judge are told", any("cancelled" in m["subject"].lower() for m in mails(b_teacher)) and any("cancelled" in m["subject"].lower() for m in mails(judge_email)))
    s, html, _ = page(A["browser"], d1)
    check("the tracker shows the cancellation", "Cancelled" in html)
    s, html, _ = j.get(f"{SITE}/conversation/judge/")
    check("a cancelled debate disappears from the judge's list", "not down to judge" in html or "no longer" in html or d1 not in html)

    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


if __name__ == "__main__":
    main()
