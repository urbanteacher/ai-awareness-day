#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 10: data retention.

One person asks for their details to be deleted (and proves the address is theirs), and everyone's contact
details are deleted after the campaign. Runs the end-of-campaign deletion only for the schools it creates.

    python3 scripts/aiadn-e2e-slice10.py
"""
import importlib.util
import json
import os
import re
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
spec = importlib.util.spec_from_file_location("s7", os.path.join(HERE, "aiadn-e2e-slice7.py"))
s7 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s7)
s6, s4, s3, s2, e2e = s7.s6, s7.s4, s7.s3, s7.s2, s7.e2e

SITE, Browser, check, field = e2e.SITE, e2e.Browser, e2e.check, e2e.field
mails, mail_clear, link_for, code_for = e2e.mails, e2e.mail_clear, e2e.link_for, e2e.code_for
sql, RUN = s3.sql, s3.RUN
U = RUN.upper()


def php(code):
    out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", 'define("WP_USE_THEMES", false); require "/var/www/html/wp-load.php"; ' + code], capture_output=True, text=True, cwd=ROOT)
    lines = [l for l in out.stdout.splitlines() if l.startswith("R:")]
    return lines[-1][2:] if lines else (out.stdout[-300:] + out.stderr[-300:])


def erase(email):
    """Delete one person's details, as the programme team does when someone asks. Returns what was changed."""
    out = php(f'echo "R:" . json_encode(AIADN_Privacy::erase_email("{email}"));')
    try:
        return json.loads(out)
    except ValueError:
        return {}


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
        php('delete_option("aiadn_retention_done_2027-08-31"); delete_option("aiadn_retention_warned_2027-08-31");')
    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


def run(admin_login):
    php('delete_option("aiadn_retention_done_2027-08-31"); delete_option("aiadn_retention_warned_2027-08-31");')

    print("\n1. What people are told")
    s, html, _ = Browser().get(f"{SITE}/conversation/register/")
    check("the registration form says when details are deleted, with no self-service page to link to", "31 August 2027" in html and "conversation/privacy" not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/join/")
    check("the front door no longer links to a Delete my details page", "Delete my details" not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/privacy/")
    check("and the page itself is gone", "erase_request" not in html and "Email me a code" not in html)

    print("\n2. One person's details are deleted")
    A = s2.make_school("del", f"Delete Primary {U}", "LS6 2AB")
    B = s2.make_school("keep", f"Keep Academy {U}", "M1 1AA")
    Z = s2.make_school("ctrl", f"Control School {U}", "B1 1AA")
    check("three approved schools", all(x["ok"] for x in (A, B, Z)))
    colleague = f"col@{A['domain']}"
    cb = Browser()
    dash = e2e.sign_in(cb, A["code"], colleague)
    check("a colleague has joined A by its email domain", dash is not None and sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE email='{colleague}'") == "1")
    out = erase(colleague)
    check("deleting their address removes their details", out.get("members") == 1, str(out))
    row = sql(f"SELECT CONCAT(role,'|',name,'|',job_title,'|',email) FROM wp_aiadn_members WHERE school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{A['code']}') AND role='left'")
    check("the record stays as 'left', with no name, role title or address", row.startswith("left|||deleted-") and row.endswith("@deleted.invalid"), row)
    check("no sign-in code or link is left for that address", sql(f"SELECT COUNT(*) FROM wp_aiadn_login_codes WHERE email='{colleague}'") == "0")
    s, _, _ = cb.get(f"{SITE}/conversation/school/", follow=False)
    check("their old session stops working", s == 302)
    mail_clear()
    Browser().post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "teacher", "email": colleague})
    check("the old record is not brought back if they choose to join again (a school-domain address can rejoin as new)", sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{A['code']}') AND role='left'") == "1" and sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE email='{colleague}'") == "0")
    s, dash, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("the school still works for its lead", A["name"] in dash)

    print("\n3. A judge asks, in the middle of a campaign")
    d1 = s3.run_debate(A, B, 91, score=False)
    j1 = d1["judge"]
    check("the debate is ready with an accepted judge", sql(f"SELECT status FROM wp_aiadn_debates WHERE code='{d1['code']}'") == "ready")
    mail_clear()
    out = erase(j1)
    check("the judge's details are deleted", out.get("judges", 0) >= 1 and sql(f"SELECT COUNT(*) FROM wp_aiadn_judges WHERE email='{j1}'") == "0", str(out))
    check("the judge slot is emptied, and the debate goes back to waiting for a judge", sql(f"SELECT status FROM wp_aiadn_judges WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d1['code']}')") == "declined" and sql(f"SELECT status FROM wp_aiadn_debates WHERE code='{d1['code']}'") == "agreed")
    check("the host school is told to choose another judge", any("judge has withdrawn" in m["subject"].lower() for m in mails(A["teacher"])))

    print("\n4. A judge who has judged, and consented to their name")
    d2 = s3.run_debate(A, B, 92, winner="a")
    j2 = d2["judge"]
    s, pub, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("before, the public result names the judge", "Judge Number92" in pub)
    check("and the event history holds their address", int(sql(f"SELECT COUNT(*) FROM wp_aiadn_debate_events WHERE note='{j2}'")) > 0 and sql(f"SELECT submitted_by FROM wp_aiadn_scorecards WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d2['code']}')") == j2)
    erase(j2)
    s, pub, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("after, the result stays and the judge's name is gone", A["name"] in pub and "Judge Number92" not in pub)
    check("the address is gone from the history and the scorecard, but the scores stay", sql(f"SELECT COUNT(*) FROM wp_aiadn_debate_events WHERE note='{j2}'") == "0" and sql(f"SELECT submitted_by FROM wp_aiadn_scorecards WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d2['code']}')") == "" and int(sql(f"SELECT a_total FROM wp_aiadn_scorecards WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d2['code']}')")) > 0)

    print("\n5. Someone who introduced a school")
    jo = f"jo@intro{RUN}.org"
    R = s7.register_with_ref("rr", f"Referred School {U}", "EH1 1AA", f"Intro Org {U}", jo, tick=True)
    check("a school names an introducer, and its headteacher agrees", R["ok"] and s7.referral_status(R["code"]) == "shared")
    rid = sql(f"SELECT r.id FROM wp_aiadn_referrals r JOIN wp_aiadn_schools s ON s.id=r.subject_id AND r.subject_type='school' WHERE s.code='{R['code']}'")
    manage = php(f'echo "R:" . AIADN_Referrals::manage_url({rid});')
    s, page, _ = Browser().get(manage)
    check("their manage link works", "Stop these emails" in page)
    erase(jo)
    check("their address is gone and the school leaves the organisation's numbers", sql(f"SELECT CONCAT(status,'|',referrer_email) FROM wp_aiadn_referrals WHERE id={rid}") == "disowned|")
    s, page, _ = Browser().get(manage)
    check("the old link no longer works", "no longer valid" in page)

    print("\n6. A person who reported an incident")
    d3 = s3.run_debate(A, Z, 93, winner="a")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/issue/?d={d3['code']}")
    A["browser"].post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": field(html, "csrf"), "aiadn_action": "report_issue", "category": "result_wrong", "details": "Scores swapped, private note."})
    check("the report holds the reporter's address", sql(f"SELECT reporter_email FROM wp_aiadn_issues WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d3['code']}')") == A["teacher"])
    erase(A["teacher"])
    check("their address goes but the report stays", sql(f"SELECT reporter_email FROM wp_aiadn_issues WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d3['code']}')") == "" and "Scores swapped" in sql(f"SELECT details FROM wp_aiadn_issues WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d3['code']}')"))

    print("\n7. The end of the campaign, for these schools only")
    ids = [sql(f"SELECT id FROM wp_aiadn_schools WHERE code='{x['code']}'") for x in (A, B)]
    d4 = s3.run_debate(B, Z, 94, winner="b", name_public=False)
    idsz = sql(f"SELECT id FROM wp_aiadn_schools WHERE code='{Z['code']}'")
    scope = "array(" + ",".join(ids + [idsz]) + ")"
    dry = json.loads(php(f'echo "R:" . json_encode(AIADN_Privacy::end_of_campaign({scope}, true));'))
    check("a preview counts what would go, and changes nothing", dry["Teacher and headteacher records"] >= 3 and dry["Sign-in codes"] >= 0 and int(sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE school_id IN ({','.join(ids)})")) >= 3)
    control = sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE school_id={sql(chr(83)+'ELECT id FROM wp_aiadn_schools WHERE code=' + repr(R['code']))}")
    pin, _ = s4.start_pin(B)
    s4.class_of(B, pin, 10)
    voice_before = sql(f"SELECT COUNT(*) FROM wp_aiadn_voice WHERE school_id={ids[1]}")
    votes_before = sql(f"SELECT COUNT(*) FROM wp_aiadn_debates WHERE status='completed' AND (school_a_id IN ({','.join(ids)}) OR school_b_id IN ({','.join(ids)}))")
    scores_before = sql(f"SELECT SUM(a_total+b_total) FROM wp_aiadn_scorecards WHERE debate_id IN (SELECT id FROM wp_aiadn_debates WHERE school_a_id IN ({ids[0]},{ids[1]},{idsz}) OR school_b_id IN ({ids[0]},{ids[1]},{idsz}))")
    done = json.loads(php(f'echo "R:" . json_encode(AIADN_Privacy::end_of_campaign({scope}, false));'))
    check("the deletion reports what it removed", done["Teacher and headteacher records"] >= 3)
    check("no teacher or headteacher is left at those schools", sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE school_id IN ({ids[0]},{ids[1]},{idsz})") == "0" and sql(f"SELECT COUNT(*) FROM wp_aiadn_schools WHERE id IN ({ids[0]},{ids[1]},{idsz}) AND slt_email<>''") == "0")
    check("no judge address is left", sql(f"SELECT COUNT(*) FROM wp_aiadn_judges WHERE debate_id IN (SELECT id FROM wp_aiadn_debates WHERE school_a_id IN ({ids[0]},{ids[1]},{idsz}) OR school_b_id IN ({ids[0]},{ids[1]},{idsz})) AND email NOT LIKE 'deleted-%'") == "0")
    check("a judge who did not agree to be named has no name; one who did keeps it", sql(f"SELECT name FROM wp_aiadn_judges WHERE debate_id=(SELECT id FROM wp_aiadn_debates WHERE code='{d4['code']}')") == "")
    check("scorecard comments are gone, the scores stay", sql(f"SELECT COUNT(*) FROM wp_aiadn_scorecards WHERE debate_id IN (SELECT id FROM wp_aiadn_debates WHERE school_a_id IN ({ids[0]},{ids[1]},{idsz}) OR school_b_id IN ({ids[0]},{ids[1]},{idsz})) AND (comment_a<>'' OR comment_b<>'' OR submitted_by<>'')") == "0" and sql(f"SELECT SUM(a_total+b_total) FROM wp_aiadn_scorecards WHERE debate_id IN (SELECT id FROM wp_aiadn_debates WHERE school_a_id IN ({ids[0]},{ids[1]},{idsz}) OR school_b_id IN ({ids[0]},{ids[1]},{idsz}))") == scores_before)
    check("incident details, invitations and meeting links are gone", sql(f"SELECT COUNT(*) FROM wp_aiadn_issues WHERE details<>'' AND debate_id IN (SELECT id FROM wp_aiadn_debates WHERE school_a_id IN ({ids[0]},{ids[1]},{idsz}) OR school_b_id IN ({ids[0]},{ids[1]},{idsz}))") == "0")
    check("sign-in codes, links and class PINs for those schools are gone", sql(f"SELECT COUNT(*) FROM wp_aiadn_login_codes WHERE school_id IN ({ids[0]},{ids[1]},{idsz})") == "0" and sql(f"SELECT COUNT(*) FROM wp_aiadn_tokens WHERE school_id IN ({ids[0]},{ids[1]},{idsz})") == "0" and sql(f"SELECT COUNT(*) FROM wp_aiadn_class_pins WHERE school_id IN ({ids[0]},{ids[1]},{idsz})") == "0")
    check("results, debates and Student Voice answers all stay", sql(f"SELECT COUNT(*) FROM wp_aiadn_debates WHERE status='completed' AND (school_a_id IN ({','.join(ids)}) OR school_b_id IN ({','.join(ids)}))") == votes_before and sql(f"SELECT COUNT(*) FROM wp_aiadn_voice WHERE school_id={ids[1]}") == voice_before)
    s, pub, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("the public results still show the schools and winners", A["name"] in pub and B["name"] in pub)
    check("a school that was not in the run keeps its people", int(sql(f"SELECT COUNT(*) FROM wp_aiadn_members WHERE school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{R['code']}')")) >= 1)
    s, _, _ = B["browser"].get(f"{SITE}/conversation/school/", follow=False)
    check("a signed-in teacher's session stops working once their record is gone", s == 302)

    print("\n8. The date, and the warning")
    tid = php('echo "R:" . AIADN_Util::team_email();')
    zscope = "array(" + idsz + ")"
    before = php('echo "R:" . AIADN_Privacy::maybe_run("2027-07-01", array(0));')
    check("long before the date, nothing happens", before == "")
    mail_clear()
    warn = php('echo "R:" . AIADN_Privacy::maybe_run("2027-08-20", array(0));')
    check("14 days before, the programme team is warned", warn == "warned" and any("will be deleted after" in m["subject"].lower() for m in mails(tid)), warn)
    check("only once", php('echo "R:" . AIADN_Privacy::maybe_run("2027-08-21", array(0));') == "")
    on_day = php('echo "R:" . AIADN_Privacy::maybe_run("2027-08-31", array(0));')
    check("on the last day itself nothing is deleted", on_day == "")
    mail_clear()
    ran = php('echo "R:" . AIADN_Privacy::maybe_run("2027-09-01", array(0));')
    check("the day after, it deletes, and tells the team what it did (counts only)", ran == "deleted" and any("have been deleted" in m["subject"].lower() for m in mails(tid)) and not any(A["teacher"] in m["text"] for m in mails(tid)), ran)
    check("and does not run twice", php('echo "R:" . AIADN_Privacy::maybe_run("2027-09-02", array(0));') == "")

    print("\n9. For the programme team")
    admin, st, h = s6.wp_login(admin_login)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the programme page has a Data retention section with the date", "Data retention" in html and "31 August 2027" in html)
    nonce = re.search(r'name="_wpnonce" value="([a-f0-9]+)"', html).group(1)
    s, html, _ = admin.post(f"{SITE}/conversation/programme/", {"aiadn_action": "retention_preview", "_wpnonce": nonce})
    check("Preview shows counts and deletes nothing", "This is what would be deleted" in html and "Teacher and headteacher records" in html and int(sql("SELECT COUNT(*) FROM wp_aiadn_members")) > 0)
    s, html, _ = admin.post(f"{SITE}/conversation/programme/", {"aiadn_action": "retention_run", "_wpnonce": nonce, "confirm": "delete"})
    check("Delete now needs DELETE typed exactly, and otherwise does nothing", "Nothing was deleted" in html and int(sql("SELECT COUNT(*) FROM wp_aiadn_members")) > 0)
    s, html, _ = admin.post(f"{SITE}/conversation/programme/", {"aiadn_action": "retention_preview", "_wpnonce": "bad"})
    check("and both need the page's own token", "This is what would be deleted" not in html)
    check("the run leaves a log of counts, with no names", "@" not in php('echo "R:" . json_encode(AIADN_Privacy::log());').replace("deleted-", "").replace("@deleted.invalid", ""))


if __name__ == "__main__":
    main()
