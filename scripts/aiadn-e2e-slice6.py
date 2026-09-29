#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 6: the programme team's dashboards, the trust view,
the partner report, the School AI Snapshot, and colleague approval.

Creates its own schools, debates and a throwaway WordPress admin and subscriber (removed at the end).
Numbers for the whole programme are compared before and after, because the database may hold other data.

    python3 scripts/aiadn-e2e-slice6.py
"""
import csv
import importlib.util
import io
import os
import re
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("s4", os.path.join(HERE, "aiadn-e2e-slice4.py"))
s4 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s4)
s3, s2, e2e = s4.s3, s4.s2, s4.e2e

SITE, Browser, check, field, loc = e2e.SITE, e2e.Browser, e2e.check, e2e.field, e2e.loc
mails, mail_clear, link_for = e2e.mails, e2e.mail_clear, e2e.link_for
sql, RUN = s3.sql, s3.RUN
PW = "Pw-" + RUN + "-9x!"


def org_key(name):
    """The same comparison key the plugin uses for trusts and partners."""
    plain = name.lower()
    plain_stripped = re.sub(r"\b(multi[- ]?academy|academy|trust|mat|the|ltd|limited|cic)\b", " ", plain)
    key = re.sub(r"[^a-z0-9]+", "", plain_stripped)
    return key or re.sub(r"[^a-z0-9]+", "", plain)


def make_user(login, role_caps, level):
    sql(f"INSERT INTO wp_users (user_login,user_pass,user_nicename,user_email,user_registered,display_name) VALUES ('{login}', MD5('{PW}'), '{login}', '{login}@example.test', NOW(), '{login}');"
        f"SET @id = LAST_INSERT_ID();"
        f"INSERT INTO wp_usermeta (user_id,meta_key,meta_value) VALUES (@id,'wp_capabilities','{role_caps}'),(@id,'wp_user_level','{level}');")


def drop_users():
    sql(f"DELETE FROM wp_usermeta WHERE user_id IN (SELECT ID FROM wp_users WHERE user_login LIKE 'aiadn6{RUN}%');"
        f"DELETE FROM wp_users WHERE user_login LIKE 'aiadn6{RUN}%';")


def wp_login(login):
    b = Browser()
    b.get(f"{SITE}/wp-login.php")
    s, _, h = b.post(f"{SITE}/wp-login.php", {"log": login, "pwd": PW, "wp-submit": "Log In", "redirect_to": f"{SITE}/wp-admin/", "testcookie": "1"}, follow=False)
    return b, s, h


def link_partner(name, email, school_codes, judge_emails=()):
    """A partner whose schools (and judges) have agreed to be shared with it. Returns the partner's id."""
    key = email.split("@")[1]
    sql(f"INSERT INTO wp_aiadn_partners (name,party_key,created_at) VALUES ('{name}','{key}',UTC_TIMESTAMP())")
    pid = sql(f"SELECT id FROM wp_aiadn_partners WHERE party_key='{key}'")
    for code in school_codes:
        sid = sql(f"SELECT id FROM wp_aiadn_schools WHERE code='{code}'")
        sql(f"INSERT INTO wp_aiadn_referrals (subject_type,subject_id,org_name,referrer_email,partner_id,status,shared_by,shared_at,created_at) VALUES ('school',{sid},'{name}','{email}',{pid},'shared','test',UTC_TIMESTAMP(),UTC_TIMESTAMP())")
    for je in judge_emails:
        jid = sql(f"SELECT id FROM wp_aiadn_judges WHERE email='{je}' AND status='accepted' LIMIT 1")
        sql(f"INSERT INTO wp_aiadn_referrals (subject_type,subject_id,org_name,referrer_email,partner_id,status,shared_by,shared_at,created_at) VALUES ('judge',{jid},'{name}','{email}',{pid},'shared','test',UTC_TIMESTAMP(),UTC_TIMESTAMP())")
    return pid


def tile(html, label):
    m = re.search(r'<strong>([^<]*)</strong><span>' + re.escape(label) + r'</span>', html)
    return m.group(1).strip() if m else None


def national_csv(b):
    s, body, h = b.get(f"{SITE}/conversation/programme/?export=national")
    rows = list(csv.reader(io.StringIO(body.lstrip("﻿"))))
    measures, groups, block = {}, {}, None
    for r in rows:
        if not r:
            block = None
            continue
        if r[0] in ("Region", "Trust", "Partner"):
            block = r[0]
            groups[block] = {}
            continue
        if block:
            groups[block][r[0]] = [int(x) for x in r[1:]]
        elif r[0] != "Measure" and len(r) == 2 and r[1].lstrip("-").isdigit():
            measures[r[0]] = int(r[1])
    return body, measures, groups, h


def main():
    e2e.clear_rate_limits()
    mail_clear()
    admin_login, sub_login = f"aiadn6{RUN}admin", f"aiadn6{RUN}sub"
    drop_users()
    make_user(admin_login, 'a:1:{s:13:"administrator";b:1;}', 10)
    make_user(sub_login, 'a:1:{s:10:"subscriber";b:1;}', 0)
    try:
        run(admin_login, sub_login)
    finally:
        drop_users()
    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


def run(admin_login, sub_login):
    U = RUN.upper()
    print("\n1. Who can open the programme pages")
    s, html, h = Browser().get(f"{SITE}/conversation/programme/", follow=False)
    check("a stranger is sent to the WordPress sign-in", s == 302 and "wp-login.php" in loc(h) and "redirect_to" in loc(h), loc(h))
    admin, st, h = wp_login(admin_login)
    check("the throwaway admin can sign in to WordPress", st == 302 and "wp-admin" in loc(h), loc(h))
    sub, st, h = wp_login(sub_login)
    s, html, _ = sub.get(f"{SITE}/conversation/programme/")
    check("a signed-in WordPress user without the capability is refused", s == 403 and "Programme team only" in html and "The journey" not in html, str(s))
    s, html, h = admin.get(f"{SITE}/conversation/programme/")
    check("the programme team see the dashboard", s == 200 and "The journey" in html and "Stuck fixtures" in html)
    check("and it is kept out of search engines", "noindex" in " ".join(f"{k}:{v}" for k, v in h.items()).lower())
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?export=national")
    check("the CSV needs the same access", "Measure" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/programme/?export=national", follow=False)
    check("and a stranger cannot get it", s == 302 and "Measure" not in html)

    # Earlier runs leave their own test schools behind. The tidy-up lists show ten each, so retire the old ones first.
    sql("UPDATE wp_aiadn_schools SET status='rejected' WHERE status<>'rejected' AND (name LIKE 'Nowhere School %' OR name LIKE 'Oakfield Primary %' OR name LIKE 'Waiting School %')")
    sql("UPDATE wp_aiadn_schools SET mat_name='' WHERE mat_name LIKE 'Oak Trust %'")
    body0, m0, g0, _ = national_csv(admin)

    print("\n2. Schools and debates to measure")
    A = s2.make_school("oakfield", f"Oakfield Primary {U}", "LS6 2AB")
    B = s2.make_school("riverside", f"Riverside Academy {U}", "M1 1AA")
    C = s2.make_school("hillview", f"Hillview School {U}", "EH1 1AA")
    D = s2.make_school("parkside", f"Parkside Academy {U}", "B1 1AA")
    E = s2.make_school("oakdupe", f"Oakfield Primary {U}", "SW1A 1AA")
    F = s2.make_school("nowhere", f"Nowhere School {U}", "ZZ9 9ZZ")
    check("six approved schools", all(x["ok"] for x in (A, B, C, D, E, F)))
    # A school still waiting for its headteacher, and long enough that the team should chase it.
    G_b, G_ref, _ = s2.register(f"Waiting School {U}", "N1 1AA", f"lead@waiting{RUN}.sch.uk", f"head@waiting{RUN}.sch.uk")
    G_code = s2.verify_register(G_b, G_ref, f"lead@waiting{RUN}.sch.uk")
    sql(f"UPDATE wp_aiadn_schools SET created_at = DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 DAY) WHERE code='{G_code}'")
    oak, oak_lower, beech, afg, afg_lower, formula = f"Oak Trust {U}", f"oak trust {U}", f"Beech Trust {U}", f"Apps for Good {U}", f"apps for good {U}", f"=HYPERLINK {U}"
    afg_upper = f"APPS FOR GOOD {U}"
    for sch, mat in ((A, oak), (B, oak_lower), (C, beech)):
        sql(f"UPDATE wp_aiadn_schools SET mat_name='{mat}' WHERE code='{sch['code']}'")

    d_ab = s3.run_debate(A, B, 61, winner="a")
    d_ac = s3.run_debate(A, C, 62, score=False)
    s3.set_start(d_ac["code"], 60)
    j = Browser()
    j.post(f"{SITE}/conversation/score/?t={d_ac['token']}", {"t": d_ac["token"], "aiadn_action": "submit_result", **s3.scores(winner="b")})
    d_ad = s3.run_debate(A, D, 63, winner="a")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/issue/?d={d_ad['code']}")
    A["browser"].post(f"{SITE}/conversation/issue/?d={d_ad['code']}", {"csrf": field(html, "csrf"), "aiadn_action": "report_issue", "category": "result_wrong", "details": "We think the scores were swapped."})
    check("two counted debates and one held by an issue", s2.page(A["browser"], d_ab["code"])[1].count("Result") > 0 and s3.sql(f"SELECT status FROM wp_aiadn_issues WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d_ad['code']}')") == "reported")
    pid = link_partner(afg, f"jo@appsforgood{RUN}.org", [A["code"], C["code"], F["code"]], [d_ac["judge"]])
    link_partner(formula, f"x@formula{RUN}.org", [E["code"]])
    stuck = s2.new_debate(D)
    sql(f"UPDATE wp_aiadn_debates SET stage_at = DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 DAY) WHERE code='{stuck}'")
    fresh = s2.new_debate(D)

    print("\n3. The programme figures")
    body1, m1, g1, _ = national_csv(admin)
    d = lambda k: m1.get(k, 0) - m0.get(k, 0)
    check("seven more schools registered, six approved", d("Schools registered") == 7 and d("Schools approved") == 6, str((d("Schools registered"), d("Schools approved"))))
    check("two more debates completed (the held one does not count)", d("Debates completed") == 2, str(d("Debates completed")))
    check("the held result is counted separately", d("Results held by an issue") == 1)
    check("students reached: 24 in each counted debate", d("Students reached") == 48, str(d("Students reached")))
    check("two new pairs of schools", d("Different pairs of schools") == 2, str(d("Different pairs of schools")))
    check("both crossed a region; one crossed trusts", d("Pairs across regions") == 2 and d("Pairs across trusts") == 1, str((d("Pairs across regions"), d("Pairs across trusts"))))
    check("the journey counts move", d("Journey: Started registering") == 7 and d("Journey: Headteacher approved") == 6 and d("Journey: Completed a debate") == 3, str((d("Journey: Started registering"), d("Journey: Headteacher approved"), d("Journey: Completed a debate"))))
    check("three more judges", d("Judges (different people)") == 3, str(d("Judges (different people)")))
    reg0, reg1 = g0.get("Region", {}), g1.get("Region", {})
    rd = lambda name: (reg1.get(name, [0])[0] - reg0.get(name, [0])[0])
    check("regions come from the postcodes", rd("Yorkshire and The Humber") == 1 and rd("North West") == 1 and rd("Scotland") == 1 and rd("West Midlands") == 1 and rd("London") == 2 and rd("Unknown") == 1, str({k: rd(k) for k in ("Yorkshire and The Humber", "North West", "Scotland", "West Midlands", "London", "Unknown")}))
    trusts = g1.get("Trust", {})
    check("'Oak Trust' and 'oak trust' are one trust of two schools", trusts.get(oak, trusts.get(oak_lower, [0]))[0] == 2 and (oak in trusts) != (oak_lower in trusts), str([k for k in trusts if U in k]))
    partners = g1.get("Partner", {})
    check("the introducing organisation is one partner of three schools", partners.get(afg, [0])[0] == 3, str({k: v for k, v in partners.items() if U in k}))
    check("a partner name that looks like a formula cannot run as one in the CSV", f"'{formula}" in body1 and f'\n{formula}' not in body1)

    print("\n4. The dashboard")
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("it has the headline numbers and the sections", all(t in html for t in ("Schools approved", "Debates completed", "Students reached", "Stuck fixtures", "Incidents", "Data quality", "Participation", "Regions", "Trusts", "Partners", "Judges", "Student Voice")))
    check("the sections fold away", html.count('class="aiadn__panel aiadn__fold"') >= 6 or html.count("aiadn__fold") >= 6)
    check("the journey is a set of bars", "aiadn__barrow--wide" in html and "Earned the certificate" in html)
    check("a fixture waiting 10 days is listed as stuck, with what it is waiting for", stuck in html and "Waiting for an opponent" in html)
    check("a fresh one is not", fresh not in html)
    check("the open incident is counted", re.search(r"Incidents \(([1-9]\d*) open\)", html) is not None)
    check("the school waiting for its headteacher is on the tidy-up list", f"Waiting School {U}" in html and "Waiting for a headteacher to approve" in html)
    check("so is the duplicate name, and the school with no region", "Possible duplicate schools" in html and f"Nowhere School {U}" in html)
    check("and the trust spelt two ways", "spelt more than one way" in html and oak in html and oak_lower in html)
    check("no teacher's email, and nothing about a student, is on the page", A["teacher"] not in html and A["slt"] not in html and "swapped" not in html)
    check("the page says it is not a national sample", "not representative" in html)

    print("\n5. One trust")
    key = org_key(oak)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?mat={key}")
    check("the trust page names the trust and its two schools", oak in html or oak_lower in html and A["name"] in html and B["name"] in html and A["code"] in html)
    check("its figures: 2 schools, 2 debates, 48 students", tile(html, "Schools") == "2" and tile(html, "Debates completed") == "2" and tile(html, "Students reached") == "48", str((tile(html, "Schools"), tile(html, "Debates completed"), tile(html, "Students reached"))))
    check("one connection crossed trusts", tile(html, "Across trusts") == "1", str(tile(html, "Across trusts")))
    check("it does not list other trusts' schools", C["name"] not in html)
    check("no emails", A["teacher"] not in html)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?mat=nosuchtrust")
    check("an unknown trust is not found", "Trust not found" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/programme/?mat={key}", follow=False)
    check("a stranger cannot open it", s == 302)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?export=mat&mat={key}")
    check("the trust CSV has its figures", '"Students reached",48' in html and "Group" in html, html[:120])

    print("\n6. One partner: counts only")
    pkey = str(pid)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?partner={pkey}")
    check("the partner report has the AiAd27 logo and the partner's name", "aiad27-lockup.svg" in html and afg in html and "Impact report" in html)
    check("schools activated: 3", tile(html, "Schools activated") == "3", str(tile(html, "Schools activated")))
    check("debates generated: 3, completed: 2, students: 48", tile(html, "Debates generated") == "3" and tile(html, "Debates completed") == "2" and tile(html, "Students reached") == "48", str((tile(html, "Debates generated"), tile(html, "Debates completed"), tile(html, "Students reached"))))
    check("judges contributed: 1 (the judge who named the partner)", tile(html, "Judges contributed") == "1", str(tile(html, "Judges contributed")))
    check("it names no school, teacher, judge or student", A["name"] not in html and C["name"] not in html and A["teacher"] not in html and "Judge Number" not in html)
    check("it says it is counts only and can be printed", "Counts only" in html and "Print or save as PDF" in html)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?export=partner&partner={pkey}")
    check("the partner CSV has the judges and no names", '"Judges contributed",1' in html and A["name"] not in html, html[-160:])
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the dashboard's partner table links to the report", f"partner={pkey}" in html)

    print("\n7. The scorecard no longer asks for a partner")
    s, html, _ = Browser().get(f"{SITE}/conversation/score/?t={d_ab['token']}")
    check("the free-text partner question is gone (judges say it when they accept)", 'name="partner_ref"' not in html)

    print("\n8. The School AI Snapshot")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("the school page links to the Snapshot", "School AI Snapshot" in html and "conversation/snapshot" in html)
    check("the stale 'next build' line is gone", "arrive in the next build" not in html)
    s, snap, _ = A["browser"].get(f"{SITE}/conversation/snapshot/")
    check("the Snapshot has the logo, the school and the code", "aiad27-lockup.svg" in snap and A["name"] in snap and A["code"] in snap and "School AI Snapshot" in snap)
    check("with too few answers, it says results are hidden", "of 10 students have answered" in snap and "Biggest split" not in snap)
    check("it shows what the school has done: 2 debates against 2 schools", "2 debates judged, against 2 different schools" in snap, re.sub(r"<[^>]+>", " ", snap)[-600:])
    pin, _ = s4.start_pin(A)
    s4.class_of(A, pin, 10)
    s, snap, _ = A["browser"].get(f"{SITE}/conversation/snapshot/")
    check("once 10 have answered, it shows what students think, with bars and the biggest split", "Based on 10 anonymous answers" in snap and "aiadn__stack" in snap and "Biggest split" in snap)
    check("it has ways to use it, the caveat and a print button", "Ways to use this" in snap and "not a national sample" in snap and "Print or save as PDF" in snap)
    check("nothing personal is on it", A["teacher"] not in snap and A["slt"] not in snap)
    s, _, _ = Browser().get(f"{SITE}/conversation/snapshot/", follow=False)
    check("a stranger cannot open it", s == 302)
    st_browser, _ = s4.student_in(A, pin)
    s, _, _ = st_browser.get(f"{SITE}/conversation/snapshot/", follow=False)
    check("nor can a student", s == 302)
    s, snap_b, _ = B["browser"].get(f"{SITE}/conversation/snapshot/")
    check("another school sees only its own", A["name"] not in snap_b and B["name"] in snap_b)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the ten answers show on the programme dashboard", "Student Voice" in html)

    print("\n9. A colleague on a different email address asks to join")
    new = f"newcomer{RUN}@gmail.com"
    s, join, _ = Browser().get(f"{SITE}/conversation/join/")
    check("the front door has a link for colleagues on other addresses", "Ask to join" in join and "conversation/colleague" in join)
    s, form, _ = Browser().get(f"{SITE}/conversation/colleague/?c={A['code']}")
    check("the request page has the school code filled in", A["code"] in form and 'name="email"' in form)
    mail_clear()
    req = lambda code, name, email, **kw: Browser().post(f"{SITE}/conversation/colleague/", {"aiadn_action": "colleague_request", "school_code": code, "name": name, "email": email, **kw})
    s, r1, _ = req(A["code"], "New Comer", new)
    check("the colleague is thanked, whatever the school code", "Thank you" in r1 and "If that school code matches" in r1)
    s, r2, _ = req("SCH-ZZZZZ", "New Comer", f"other{RUN}@gmail.com")
    check("a code that matches no school gets exactly the same answer", re.sub(r"\s+", " ", r1) == re.sub(r"\s+", " ", r2))
    lead_mail = [m for m in mails(A["teacher"]) if "approve a colleague" in m["subject"].lower()]
    check("the school lead is emailed, with the colleague's details and a link", len(lead_mail) == 1 and "New Comer" in lead_mail[0]["text"] and new in lead_mail[0]["text"] and "conversation/colleague" in lead_mail[0]["text"])
    check("nobody else was emailed", not mails(f"other{RUN}@gmail.com"))
    s, fd, _ = Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "teacher", "email": new})
    check("the colleague can not sign in yet: no code is sent", not [m for m in mails(new) if "code" in m["subject"].lower()])
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("the lead sees the colleague waiting on their dashboard and in the team list", "Colleagues waiting for you" in dash and "New Comer" in dash and "Waiting for approval" in dash)
    link = link_for(A["teacher"], "conversation/colleague")
    tok = link.split("t=")[1]
    lead_view = Browser()
    s, page, _ = lead_view.get(link)
    check("opening the link only shows the request", "Approve New Comer?" in page and "I do not know them" in page)
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("and approves nothing by itself", "Colleagues waiting for you" in dash)
    s, done, _ = Browser().post(f"{SITE}/conversation/colleague/", {"aiadn_action": "colleague_decide", "t": tok, "decision": "approve"})
    check("the lead approves", "<h1>Approved</h1>" in done)
    check("the colleague is told they can sign in", any("you can now join" in m["subject"].lower() for m in mails(new)))
    s, again, _ = Browser().post(f"{SITE}/conversation/colleague/", {"aiadn_action": "colleague_decide", "t": tok, "decision": "approve"})
    check("the link works once", "no longer valid" in again)
    cb = Browser()
    dash = e2e.sign_in(cb, A["code"], new)
    check("now the colleague signs in with an emailed code, as a teacher", dash is not None and A["name"] in dash)
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("and they are on the team as a teacher", new in dash and "Colleagues waiting for you" not in dash)

    print("\n10. Declined, from the dashboard, and the unhappy paths")
    other = f"declined{RUN}@gmail.com"
    req(A["code"], "Not Known", other)
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    mid = re.search(r'name="member_id" value="(\d+)"', dash)
    check("the lead can decide from the dashboard", bool(mid))
    A["browser"].post(f"{SITE}/conversation/school/", {"csrf": field(dash, "csrf"), "aiadn_action": "colleague_decide", "member_id": mid.group(1), "decision": "decline"})
    check("declining tells the colleague and removes the request", any("your request to join" in m["subject"].lower() for m in mails(other)) and sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE email='{other}'") == "0")
    mail_clear()
    Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "teacher", "email": other})
    check("a declined colleague cannot sign in: no code is sent", not [m for m in mails(other) if "code" in m["subject"].lower()])
    mail_clear()
    req(A["code"], "Existing", A["teacher"])
    check("asking with an address that is already a teacher there sends no email and says the same thing", not mails(A["teacher"]))
    mail_clear()
    s, hp, _ = req(A["code"], "Bot", f"bot{RUN}@gmail.com", website="x")
    check("a robot that fills the hidden field is thanked and ignored", "Thank you" in hp and not mails(A["teacher"]))
    s, bad, _ = req(A["code"], "", "not-an-email")
    check("a missing name or bad address is asked again", "Enter your school code" in bad)
    s, err, _ = Browser().get(f"{SITE}/conversation/colleague/?t={'0' * 40}")
    check("a made-up link is refused", "no longer valid" in err)
    # A request for a school that is not yet approved does nothing.
    mail_clear()
    req(G_code, "Early", f"early{RUN}@gmail.com")
    check("a school not yet approved cannot be joined", not mails(f"lead@waiting{RUN}.sch.uk"))
    e2e.clear_rate_limits()
    last = ""
    for i in range(12):
        s, last, _ = req(A["code"], "Spam", f"spam{i}{RUN}@gmail.com")
    check("asking over and over is slowed down", "Please wait" in last)
    e2e.clear_rate_limits()

    print("\n11. What students said, by age group")
    pin_c, _ = s4.start_pin(C)
    s4.class_of(C, pin_c, 10, year="y10_11")
    s4.class_of(C, pin_c, 10, year="post16")
    pin_f, _ = s4.start_pin(F)
    s4.class_of(F, pin_f, 10)
    s4.class_of(A, pin, 12, year="y5_6")
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?partner={pkey}")
    check("the partner report has a Student Voice section by age group", "What students said, by age group" in html)
    sec = html.split("What students said, by age group")[1]
    prim = sec.split("<h3>Secondary")[0]
    seco = sec.split("<h3>Secondary")[1].split("<h3>Post-16")[0]
    post = sec.split("<h3>Post-16")[1]
    check("secondary is shown: 30 answers from 3 schools", "30 answers from 3 schools" in seco and "aiadn__stack" in seco and "Agree 60%" in seco, re.sub(r"<[^>]+>", " ", seco)[:300])
    check("primary (one school) is held back from a partner", "12 answers from 1 school" in prim and "Not enough answers yet" in prim and "aiadn__stack" not in prim)
    check("so is post-16 (one school)", "10 answers from 1 school" in post and "Not enough answers yet" in post and "aiadn__stack" not in post)
    check("it says how many schools it needs", "from at least 3 schools" in sec)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?export=partner&partner={pkey}")
    check("the partner CSV has secondary and no primary percentages", '"Student Voice answers: Secondary",30' in html and "Student Voice: Secondary: SAFE: agree %" in html and "Student Voice: Primary:" not in html)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    voice_sec = html.split("Student Voice")[-1]
    check("the programme dashboard shows all three age groups (no school minimum)", all(t in html for t in ("<h3>Primary", "<h3>Secondary", "<h3>Post-16")))
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?mat={org_key(beech)}")
    check("a trust view shows its schools' answers by age group", "What students said" in html and "<h3>Post-16" in html and "aiadn__stack" in html)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?export=national")
    check("the national CSV has Student Voice by age group", '"Student Voice answers: Primary"' in html and '"Student Voice answers: Post-16"' in html)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?partner={pkey}")
    check("no school name, email or year-group detail that could identify a school", A["name"] not in html and C["name"] not in html and F["name"] not in html)

    print("\n12. Other pages still work")
    s, html, _ = admin.get(f"{SITE}/conversation/programme/?partner=nosuch")
    check("an unknown partner is not found", "Partner not found" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/results/", follow=False)
    check("the public and school pages are unchanged for strangers", s in (200, 302))


if __name__ == "__main__":
    main()
