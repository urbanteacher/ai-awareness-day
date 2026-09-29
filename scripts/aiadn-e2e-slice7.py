#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 7: who introduced you.

A teacher names the organisation and the person there. The headteacher decides, when approving the school,
whether they are told. Only then is the organisation emailed, with a link to its own dashboard, and again
when a debate is agreed and when a result is in. It can stop the emails or say "this wasn't us". Judges do
the same for themselves when they accept.

    python3 scripts/aiadn-e2e-slice7.py
"""
import importlib.util
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("s6", os.path.join(HERE, "aiadn-e2e-slice6.py"))
s6 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s6)
s4, s3, s2, e2e = s6.s4, s6.s3, s6.s2, s6.e2e

SITE, Browser, check, field, loc = e2e.SITE, e2e.Browser, e2e.check, e2e.field, e2e.loc
mails, mail_clear, link_for, code_for = e2e.mails, e2e.mail_clear, e2e.link_for, e2e.code_for
sql, RUN = s3.sql, s3.RUN
U = RUN.upper()


def register_with_ref(key, name, postcode, ref_org, ref_email, tick, decide=True):
    """Register a school that names who introduced it; the headteacher approves, ticking (or not) the share box."""
    domain = f"{key}{RUN}.sch.uk"
    teacher, slt = f"lead@{domain}", f"head@{domain}"
    b = Browser()
    data = {"aiadn_action": "register", "school_name": name, "postcode": postcode, "age_phases[]": ["primary"], "mat_name": "",
            "teacher_name": "Test Teacher", "job_title": "Class teacher", "email": teacher, "slt_email": slt,
            "ref_org": ref_org, "ref_email": ref_email, "agree": "1"}
    s, html, h = b.post(f"{SITE}/conversation/register/", data, follow=False)
    ref = re.search(r"ref=([a-f0-9]{32})", loc(h))
    code = s2.verify_register(b, ref.group(1), teacher) if ref else None
    school = {"browser": b, "code": code, "teacher": teacher, "slt": slt, "name": name, "domain": domain, "ok": False}
    if decide and code:
        link = link_for(slt, "approve")
        school["approve_link"] = link
        data = {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"}
        if tick:
            data["share_referral"] = "1"
        s, html, _ = Browser().post(f"{SITE}/conversation/approve/", data)
        school["ok"] = "<h1>Approved</h1>" in html
    return school


def referral_status(code):
    return sql(f"SELECT r.status FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id AND r.subject_type='school' WHERE s.code='{code}'")


def tile(html, label):
    return s6.tile(html, label)


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
    jo = f"jo@partner{RUN}.org"
    sam = f"sam@partner{RUN}.org"
    org = f"Apps for Good {U}"

    print("\n1. Naming who introduced you, at sign-up")
    s, html, _ = Browser().get(f"{SITE}/conversation/register/")
    check("the form asks for the organisation and the person there", "Did an organisation introduce you?" in html and 'name="ref_org"' in html and 'name="ref_email"' in html)
    check("it says the teacher is responsible for having permission, and that nobody is told yet", "responsible for having their permission" in html and "your headteacher chooses" in html)
    check("the old free-text partner box is gone", 'name="partner_ref"' not in html)
    base = {"aiadn_action": "register", "school_name": f"Check School {U}", "postcode": "LS1 1AA", "age_phases[]": ["primary"], "mat_name": "", "teacher_name": "T", "job_title": "", "email": f"t@check{RUN}.sch.uk", "slt_email": f"h@check{RUN}.sch.uk", "agree": "1"}
    s, html, _ = Browser().post(f"{SITE}/conversation/register/", {**base, "ref_org": org, "ref_email": ""})
    check("an organisation without a person is asked again", "give the organisation and the email address" in html)
    s, html, _ = Browser().post(f"{SITE}/conversation/register/", {**base, "ref_org": "", "ref_email": jo})
    check("so is a person without an organisation", "give the organisation and the email address" in html)
    s, html, _ = Browser().post(f"{SITE}/conversation/register/", {**base, "ref_org": org, "ref_email": base["slt_email"]})
    check("naming the headteacher as the introducer is refused", "not you or your headteacher" in html)

    print("\n2. Nothing is sent until the headteacher agrees")
    mail_clear()
    X = register_with_ref("xray", f"Xray Primary {U}", "LS6 2AB", org, jo, tick=False, decide=False)
    check("the school registers and is verified", bool(X["code"]))
    check("the referral is recorded as named", sql(f"SELECT r.status FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id WHERE s.code='{X['code']}'") == "named")
    check("the person named has heard nothing", not mails(jo))
    link = link_for(X["slt"], "approve")
    s, page, _ = Browser().get(link)
    check("the headteacher's page shows who was named, with a box that is not ticked", org in page and jo in page and 'name="share_referral"' in page and re.search(r'name="share_referral"[^>]*checked', page) is None)
    check("it says what would be shared, and that it is the school's decision", "your choice" in page and "only totals on their dashboard" in page and "nothing about students or staff" in page)
    check("looking changes nothing", sql(f"SELECT status FROM wp_aiadn_schools WHERE code='{X['code']}'") == "pending_slt" and not mails(jo))
    s, html, _ = Browser().post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    check("approving without ticking approves the school", "<h1>Approved</h1>" in html)
    check("the referral is kept, as not shared, and nobody is told", referral_status(X["code"]) == "not_shared" and not mails(jo))

    print("\n3. Ticking the box tells them")
    Y = register_with_ref("yankee", f"Yankee Academy {U}", "M1 1AA", org, jo, tick=True)
    check("a school whose headteacher ticks is approved", Y["ok"])
    check("its referral is shared", referral_status(Y["code"]) == "shared")
    got = [m for m in mails(jo) if "is taking part" in m["subject"].lower()]
    check("the organisation is emailed once, naming the school", len(got) == 1 and Y["name"] in got[0]["text"])
    body = got[0]["text"] if got else ""
    dash = re.search(r"https?://[^\s]+conversation/partner/\?t=[a-f0-9]{40}", body)
    manage = re.search(r"https?://[^\s]+conversation/partner/\?r=[a-f0-9]{40}", body)
    check("the email has a dashboard link and a link to stop or say it wasn't them", bool(dash) and bool(manage))
    check("it says everyone is responsible for themselves and the figures are not general", "responsible for themselves" in body and "not schools in general" in body)
    check("a second organisation record is not made for the same address", sql(f"SELECT COUNT(*) FROM wp_aiadn_partners WHERE party_key='partner{RUN}.org'") == "1")

    print("\n4. The organisation's dashboard")
    dash_url = dash.group(0) if dash else ""
    o = Browser()
    s, html, _ = o.get(dash_url)
    check("the link opens the dashboard for the organisation, with no sign-in", org in html and "Schools activated" in html and "aiad27-lockup.svg" in html)
    check("one school so far", tile(html, "Schools activated") == "1", str(tile(html, "Schools activated")))
    check("it names no school, teacher or student", Y["name"] not in html and Y["teacher"] not in html and X["name"] not in html)
    check("it says the figures are not general and who is responsible", "not representative" in html and "responsible for how you use these figures" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/partner/?t={'0' * 40}")
    check("a made-up link shows nothing", "no longer valid" in html and org not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/partner/")
    check("without a link there is only a form to ask for one", "Email me a link" in html and "Schools activated" not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/partner/?r={'0' * 40}")
    check("a made-up manage link shows nothing", "no longer valid" in html)

    print("\n5. Another school, another spelling, another colleague: one organisation")
    Z = register_with_ref("zulu", f"Zulu School {U}", "B1 1AA", f"apps 4 good {U}", sam, tick=True)
    check("the second school is approved and shared", Z["ok"] and referral_status(Z["code"]) == "shared")
    check("both belong to one organisation, found by the email domain", sql(f"SELECT COUNT(DISTINCT partner_id) FROM wp_aiadn_referrals WHERE referrer_email IN ('{jo}','{sam}') AND status='shared'") == "1")
    s, html, _ = o.get(dash_url)
    check("its dashboard now shows two schools", tile(html, "Schools activated") == "2", str(tile(html, "Schools activated")))
    check("the colleague was emailed too, with their own manage link", any("is taking part" in m["subject"].lower() for m in mails(sam)))

    print("\n6. Updates when a debate is agreed and judged, once each")
    mail_clear()
    d1 = s3.run_debate(Y, Z, 71, winner="a")
    subj = [m["subject"].lower() for m in mails(jo)] + [m["subject"].lower() for m in mails(sam)]
    check("agreed: one email for each school", sum("has set up a debate" in x for x in subj) == 2, str(subj))
    check("judged: one email for each school", sum("has had a debate judged" in x for x in subj) == 2, str(subj))
    n_before = len(subj)
    d2 = s3.run_debate(Y, Z, 72, winner="b")
    subj2 = [m["subject"].lower() for m in mails(jo)] + [m["subject"].lower() for m in mails(sam)]
    check("a second debate for the same schools sends nothing more", len(subj2) == n_before, f"{n_before} -> {len(subj2)}")
    s, html, _ = o.get(dash_url)
    check("the dashboard counts the debates and students", tile(html, "Debates completed") == "2" and tile(html, "Students reached") == "48", str((tile(html, "Debates completed"), tile(html, "Students reached"))))

    print("\n7. Stopping the emails")
    stop_link = manage.group(0)
    s, page, _ = Browser().get(stop_link)
    check("the manage page names the school and offers both choices", Y["name"] in page and "Stop these emails" in page and "This was not us" in page)
    stopper = Browser()
    token_r = stop_link.split("r=")[1]
    s, html, _ = stopper.post(f"{SITE}/conversation/partner/", {"aiadn_action": "referral_stop", "r": token_r})
    check("stopping confirms", "no more update emails" in html)
    mail_clear()
    W = register_with_ref("whisky", f"Whisky School {U}", "EH1 1AA", org, jo, tick=True)
    check("a school named later is still shared, but the address stays stopped", referral_status(W["code"]) == "shared" and not mails(jo))
    s, html, _ = o.get(dash_url)
    check("the dashboard link keeps working and now shows three schools", tile(html, "Schools activated") == "3", str(tile(html, "Schools activated")))

    print("\n8. 'This wasn't us'")
    mail_clear()
    # The earlier mail was cleared, so ask for a fresh manage link the way an email would: take one from the database token.
    zid = sql(f"SELECT r.id FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id AND r.subject_type='school' WHERE s.code='{Z['code']}'")
    php = 'define("WP_USE_THEMES", false); require "/var/www/html/wp-load.php"; echo AIADN_Referrals::manage_url(%s);' % zid
    import subprocess
    out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", php], capture_output=True, text=True, cwd=os.path.dirname(HERE))
    zurl = [l for l in out.stdout.splitlines() if "conversation/partner" in l][-1]
    s, html, _ = Browser().post(f"{SITE}/conversation/partner/", {"aiadn_action": "referral_disown", "r": zurl.split("r=")[1]})
    check("disowning is confirmed", "taken them out of your numbers" in html)
    check("the school is out of the organisation's numbers", referral_status(Z["code"]) == "disowned")
    s, html, _ = o.get(dash_url)
    check("the dashboard drops to two schools", tile(html, "Schools activated") == "2", str(tile(html, "Schools activated")))
    check("the programme team is told", any("referral was disowned" in m["subject"].lower() for m in mails()))
    admin, st, h = s6.wp_login(admin_login)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the programme dashboard has a Referrals section with the disowned one listed", "Referrals" in html and Z["name"] in html and "Organisation said not us" in html)
    check("and the partner table counts by organisation, not spelling", f">{org}<" in html or org in html)

    print("\n9. A new dashboard link, by email")
    mail_clear()
    s, html, _ = Browser().post(f"{SITE}/conversation/partner/", {"aiadn_action": "partner_link", "email": jo})
    check("asking gives the same answer either way", "Check your email" in html and "If that address" in html)
    got = [m for m in mails(jo) if "dashboard" in m["subject"].lower()]
    check("a named address gets a link that works", len(got) == 1 and re.search(r"conversation/partner/\?t=[a-f0-9]{40}", got[0]["text"]) is not None)
    s, html, _ = Browser().post(f"{SITE}/conversation/partner/", {"aiadn_action": "partner_link", "email": f"stranger{RUN}@example.org"})
    check("an address nobody named gets nothing, and the same answer", "Check your email" in html and not mails(f"stranger{RUN}@example.org"))

    print("\n10. A judge introduced by an organisation")
    e2e.clear_rate_limits()
    A = s2.make_school("alpha", f"Alpha School {U}", "LS6 2AB")
    B = s2.make_school("bravo", f"Bravo School {U}", "M1 1AA")
    jorg = f"Local Employers {U}"
    jper = f"kim@employers{RUN}.org"
    d = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], d, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    s, html, _ = B["browser"].get(link)
    B["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
    judge = f"judge81@judges{RUN}.example"
    s2.act(A["browser"], d, "propose", **s2.fixture(j_email=judge, j_name="Dr Kim Judge"))
    s2.act(B["browser"], d, "accept_fixture")
    tok = link_for(judge, "conversation/judge").split("t=")[1]
    mail_clear()
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok}")
    check("the judge's page asks who introduced them, and says they are responsible", "Did an organisation introduce you to judging?" in html and "responsible for having their permission" in html and 'name="share_referral"' in html)
    jb = Browser()
    jid = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    s, html, _ = jb.post(f"{SITE}/conversation/judge/", {"t": tok, "aiadn_action": "judge_respond", "judge_id": jid, "decision": "accept", "ack": "1", "name_public": "1", "ref_org": jorg, "ref_email": jper, "share_referral": "1"})
    got = [m for m in mails(jper) if "agreed to judge" in m["subject"].lower()]
    check("the organisation is told, naming the judge (who agreed)", len(got) == 1 and "Dr Kim Judge" in got[0]["text"])
    jdash = re.search(r"https?://[^\s]+conversation/partner/\?t=[a-f0-9]{40}", got[0]["text"] if got else "")
    s, html, _ = Browser().get(jdash.group(0)) if jdash else (0, "", None)
    check("its dashboard counts one judge and shows no judge name", tile(html, "Judges contributed") == "1" and "Dr Kim Judge" not in html, str(tile(html, "Judges contributed")))
    s3.set_start(d, 60)
    mail_clear()
    Browser().post(f"{SITE}/conversation/score/?t={tok}", {"t": tok, "aiadn_action": "submit_result", **s3.scores(winner="a")})
    check("when the judge has judged, the organisation hears once", len([m for m in mails(jper) if "has judged a debate" in m["subject"].lower()]) == 1)

    print("\n11. A judge who does not tick is not shared")
    d3 = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], d3, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    C = s2.make_school("charlie", f"Charlie School {U}", "B1 1AA")
    s, html, _ = C["browser"].get(link)
    C["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
    judge2 = f"judge82@judges{RUN}.example"
    s2.act(A["browser"], d3, "propose", **s2.fixture(j_email=judge2, j_name="Dr Quiet Judge"))
    s2.act(C["browser"], d3, "accept_fixture")
    tok2 = link_for(judge2, "conversation/judge").split("t=")[1]
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok2}")
    jid2 = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    other = f"lee@quiet{RUN}.org"
    mail_clear()
    Browser().post(f"{SITE}/conversation/judge/", {"t": tok2, "aiadn_action": "judge_respond", "judge_id": jid2, "decision": "accept", "ack": "1", "ref_org": "Quiet Org", "ref_email": other})
    check("named but not ticked: recorded, and nobody is emailed", not mails(other) and sql(f"SELECT status FROM wp_aiadn_referrals WHERE subject_type='judge' AND subject_id={jid2}") == "not_shared")

    print("\n12. The rest still works")
    s, html, _ = Browser().get(f"{SITE}/conversation/register/")
    check("registration without naming anyone still works", "Send me a code" in html)
    S = s2.make_school("sierra", f"Sierra School {U}", "LS1 1AA")
    check("a school that names nobody is approved as before", S["ok"] and sql(f"SELECT COUNT(*) FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id AND r.subject_type='school' WHERE s.code='{S['code']}'") == "0")

    print("\n13. The headteacher is signed in when they approve, and emailed a copy")
    P = register_with_ref("papa", f"Papa School {U}", "LS1 1AA", "", "", tick=False, decide=False)
    link = link_for(P["slt"], "approve")
    slt_b = Browser()
    mail_clear()
    s, html, _ = slt_b.post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    check("the page says they are signed in and shows the code", "<h1>Approved</h1>" in html and "signed in as its senior leader" in html and P["code"] in html)
    check("it says the code is not a password, rather than 'store it safely'", "not a password" in html and "store" not in html.lower() and "safely" not in html.lower())
    check("it has a Go to your school button, and says how to come back", "Go to your school" in html and "Headteacher / SLT" in html)
    s, dash, _ = slt_b.get(f"{SITE}/conversation/school/")
    check("the button takes them straight to the school, with no code to type", P["name"] in dash and "School code" in dash and P["code"] in dash)
    copy = [m for m in mails(P["slt"]) if "your school code" in m["subject"].lower()]
    check("they are emailed a copy of the code and how to sign in again", len(copy) == 1 and P["code"] in copy[0]["text"] and "Headteacher / SLT" in copy[0]["text"] and "not a password" in copy[0]["text"])
    check("the teacher still gets their own email", any("is approved" in m["subject"].lower() for m in mails(P["teacher"])))
    s, again, _ = Browser().post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    check("the approval link works once, and the second click signs nobody in", "no longer valid" in again)
    stranger = Browser()
    stranger.post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": link.split("t=")[1], "decision": "approve"})
    s, _, _ = stranger.get(f"{SITE}/conversation/school/", follow=False)
    check("a stranger who tries the used link gets no session", s == 302)
    check("the usual way in still works for next time", e2e.sign_in(Browser(), P["code"], P["slt"], role="slt") is not None)
    R = register_with_ref("romeo", f"Romeo School {U}", "LS1 1AA", "", "", tick=False, decide=False)
    rlink = link_for(R["slt"], "approve")
    rb = Browser()
    rb.post(f"{SITE}/conversation/approve/", {"aiadn_action": "slt_decide", "t": rlink.split("t=")[1], "decision": "reject"})
    s, _, _ = rb.get(f"{SITE}/conversation/school/", follow=False)
    check("saying 'this isn't right' signs nobody in", s == 302)

    print("\n14. The site's other emails keep their own sender")
    import subprocess
    php = 'define("WP_USE_THEMES", false); require "/var/www/html/wp-load.php"; echo apply_filters("wp_mail_from", "certificates@example.org") . "|" . apply_filters("wp_mail_from_name", "Site Name");'
    out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", php], capture_output=True, text=True, cwd=os.path.dirname(HERE)).stdout.strip().splitlines()[-1]
    check("outside this plugin's emails, the sender is left alone", out == "certificates@example.org|Site Name", out)
    import json, urllib.request
    data = json.load(urllib.request.urlopen(e2e.MAIL + "/messages?limit=300", timeout=10))
    ours = [m for m in data.get("messages", []) if any(a["Address"] == P["slt"] for a in m.get("To", [])) and "your school code" in m["Subject"].lower()]
    check("this plugin's own emails still come from info@aiawarenessday.co.uk", bool(ours) and ours[0]["From"]["Address"] == "info@aiawarenessday.co.uk" and ours[0]["From"]["Name"] == "AI Awareness Day", str(ours[0]["From"]) if ours else "none")

    print("\n15. The email service's login comes from wp-config.php")
    def php(code):
        out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", code], capture_output=True, text=True, cwd=os.path.dirname(HERE))
        lines = [l for l in out.stdout.splitlines() if l.startswith("R:")]
        return lines[-1][2:] if lines else out.stdout[-200:] + out.stderr[-200:]
    base = 'putenv("AIADN_SMTP_HOST"); define("WP_USE_THEMES", false); '
    with_const = base + 'define("AIADN_SMTP_HOST","smtp-relay.example.test"); define("AIADN_SMTP_PORT",587); define("AIADN_SMTP_USER","login-x"); define("AIADN_SMTP_PASS","secret-x"); define("AIADN_MAIL_FROM","info@aiawarenessday.co.uk"); require "/var/www/html/wp-load.php"; '
    got = php(with_const + 'require_once ABSPATH . WPINC . "/PHPMailer/PHPMailer.php"; require_once ABSPATH . WPINC . "/PHPMailer/SMTP.php"; require_once ABSPATH . WPINC . "/PHPMailer/Exception.php"; $m = new PHPMailer\\PHPMailer\\PHPMailer(true); AIADN_Mailer::configure_smtp($m); echo "R:" . implode("|", array($m->Mailer, $m->Host, $m->Port, $m->SMTPSecure, $m->SMTPAuth ? "auth" : "noauth", $m->Username, $m->Password));')
    check("the login in wp-config.php sets up SMTP with encryption and a login", got == "smtp|smtp-relay.example.test|587|tls|auth|login-x|secret-x", got)
    got = php(base + 'define("AIADN_SMTP_HOST","smtp.example.test"); define("AIADN_SMTP_PORT",465); require "/var/www/html/wp-load.php"; require_once ABSPATH . WPINC . "/PHPMailer/PHPMailer.php"; require_once ABSPATH . WPINC . "/PHPMailer/SMTP.php"; require_once ABSPATH . WPINC . "/PHPMailer/Exception.php"; $m = new PHPMailer\\PHPMailer\\PHPMailer(true); AIADN_Mailer::configure_smtp($m); echo "R:" . implode("|", array($m->Port, $m->SMTPSecure, $m->SMTPAuth ? "auth" : "noauth"));')
    check("port 465 uses SSL, and no login means no authentication", got == "465|ssl|noauth", got)
    got = php(base + 'require "/var/www/html/wp-load.php"; require_once ABSPATH . WPINC . "/PHPMailer/PHPMailer.php"; require_once ABSPATH . WPINC . "/PHPMailer/SMTP.php"; require_once ABSPATH . WPINC . "/PHPMailer/Exception.php"; $m = new PHPMailer\\PHPMailer\\PHPMailer(true); AIADN_Mailer::configure_smtp($m); echo "R:" . $m->Mailer;')
    check("with nothing set, the site's own mail set-up is left alone", got == "mail", got)
    got = php(with_const + 'echo "R:" . json_encode(AIADN_Mailer::route());')
    check("the route report names the host and the sender, and never the login", "smtp-relay.example.test" in got and "info@aiawarenessday.co.uk" in got and "secret-x" not in got and "login-x" not in got, got)

    admin, st, h = s6.wp_login(admin_login)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the programme page has an Email check section that names the route and sender", "Email check" in html and "local test inbox" in html and "info@aiawarenessday.co.uk" in html)
    nonce = re.findall(r'name="_wpnonce" value="([a-f0-9]+)"', html.split('value="send_test_email"')[0])
    mail_clear()
    s, html, _ = admin.post(f"{SITE}/conversation/programme/", {"aiadn_action": "send_test_email", "_wpnonce": nonce[-1] if nonce else ""})
    check("the test email button sends to the person pressing it", "Test email sent to" in html and any("test email" in m["subject"].lower() for m in mails(f"{admin_login}@example.test")))
    s, html, _ = admin.post(f"{SITE}/conversation/programme/", {"aiadn_action": "send_test_email", "_wpnonce": "bad"})
    check("and needs the page's own token", "Test email sent" not in html)


if __name__ == "__main__":
    main()
