#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 3: scoring, results, issues, certificates.

Runs whole debates over HTTP, reads the emails from Mailpit, and moves a debate's start time in the
database so the scorecard opens. Reuses the helpers from the slice 1 and 2 tests.

    python3 scripts/aiadn-e2e-slice3.py
"""
import importlib.util
import json
import os
import random
import re
import string
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
spec = importlib.util.spec_from_file_location("s2", os.path.join(HERE, "aiadn-e2e-slice2.py"))
s2 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s2)
e2e = s2.e2e

SITE, Browser, check, loc, field = e2e.SITE, e2e.Browser, e2e.check, e2e.loc, e2e.field
mails, mail_clear, code_for, link_for = e2e.mails, e2e.mail_clear, e2e.code_for, e2e.link_for
RUN = s2.RUN


def sql(query):
    out = subprocess.run(["docker", "compose", "exec", "-T", "db", "mysql", "-uwordpress", "-pwordpress", "wordpress", "-N", "-e", query], capture_output=True, text=True, cwd=ROOT)
    return out.stdout.strip()


def set_start(code, minutes):
    sql(f"UPDATE wp_aiadn_debates SET starts_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL {minutes} MINUTE) WHERE code = '{code}'")


def subjects(to):
    return [m["subject"].lower() for m in mails(to)]


def has_mail(to, *words):
    return any(all(w in s for w in words) for s in subjects(to))


def judge_accept(token, name_public=True):
    j = Browser()
    s, html, _ = j.get(f"{SITE}/conversation/judge/?t={token}")
    jid = re.search(r'name="judge_id" value="(\d+)"', html).group(1)
    data = {"t": token, "aiadn_action": "judge_respond", "judge_id": jid, "decision": "accept", "ack": "1"}
    if name_public:
        data["name_public"] = "1"
    return j.post(f"{SITE}/conversation/judge/", data)


def scores(a=(4, 3, 5, 4), b=(3, 4, 3, 4), winner="a", students=24, comment_a="Strong rebuttals, use more evidence.", comment_b="Great evidence, slow down.", **over):
    data = {"students": students, "vb_agree": 12, "vb_disagree": 8, "vb_unsure": 5, "va_agree": 15, "va_disagree": 6, "va_unsure": 4,
            "winner": winner, "comment_a": comment_a, "comment_b": comment_b}
    for i, c in enumerate(("argument", "evidence", "rebuttal", "delivery")):
        data[f"a_{c}"], data[f"b_{c}"] = a[i], b[i]
    data.update(over)
    return data


def run_debate(A, B, n, winner="a", score=True, fx=None, **kw):
    """A and B debate. Returns a dict describing the debate, ready (or completed if score)."""
    d = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], d, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    s, html, _ = B["browser"].get(link)
    B["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
    judge = f"judge{n}@judges{RUN}.example"
    s2.act(A["browser"], d, "propose", **s2.fixture(j_email=judge, j_name=f"Judge Number{n}", **(fx or {})))
    s2.act(B["browser"], d, "accept_fixture")
    shortcut = link_for(judge, "conversation/judge")
    token = shortcut.split("t=")[1]
    judge_accept(token, name_public=kw.get("name_public", True))
    info = {"code": d, "token": token, "judge": judge}
    if score:
        set_start(d, 60)
        j = Browser()
        j.post(f"{SITE}/conversation/score/?t={token}", {"t": token, "aiadn_action": "submit_result", **scores(winner=winner)})
    return info


def main():
    e2e.clear_rate_limits()
    mail_clear()

    print("\n1. Schools")
    A = s2.make_school("oakfield", f"Oakfield Primary {RUN.upper()}", "LS6 2AB")
    B = s2.make_school("riverside", f"Riverside Academy {RUN.upper()}", "LS4 9XY")
    C = s2.make_school("hillview", f"Hillview School {RUN.upper()}", "M1 1AA")
    D = s2.make_school("parkside", f"Parkside Academy {RUN.upper()}", "B1 1AA")
    check("four approved schools to work with", all(x["ok"] for x in (A, B, C, D)))

    print("\n2. Scoring is closed until the day")
    d1 = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], d1, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    s, html, _ = B["browser"].get(link)
    B["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
    judge1 = f"judge1@judges{RUN}.example"
    s2.act(A["browser"], d1, "propose", **s2.fixture(j_email=judge1, j_name="Dr Amy Chen"))
    s2.act(B["browser"], d1, "accept_fixture")
    tok1 = link_for(judge1, "conversation/judge").split("t=")[1]
    s, html, _ = judge_accept(tok1, name_public=True)
    check("the judge accepts", "down to judge" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok1}")
    check("the judge is told when scoring opens", "Scoring opens" in html and "Open the scorecard" not in html)
    j = Browser()
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("the scorecard says it is not open yet", "Scoring opens soon" in html and "Submit result" not in html)
    s, html, _ = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "submit_result", **scores()})
    s, html, _ = s2.page(A["browser"], d1)
    check("submitting too early does nothing", "Everything is set" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/score/?t={'0' * 40}")
    check("a made-up scorecard link is refused", "no longer valid" in html)

    print("\n3. Scoring on the day")
    set_start(d1, 60)
    s, html, _ = Browser().get(f"{SITE}/conversation/judge/?t={tok1}")
    check("the judge sees an Open the scorecard button", "Open the scorecard" in html)
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("the scorecard shows both schools and the criteria", A["name"] in html and B["name"] in html and "Argument" in html and "Rebuttal" in html and "Room vote" in html)
    check("the page works without JavaScript", 'name="aiadn_action" value="save_scores"' in html and 'value="submit_result"' in html)
    s, body, hdr = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "save_scores", **scores(a_argument=9)}, follow=True)
    check("Save draft works", "Draft saved" in body)
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("the draft is remembered", 'value="24"' in html)
    check("a score outside 1-5 is clamped, never trusted", re.search(r'<option value="5" selected', html) is not None and 'value="9"' not in html)

    # Autosave, the way the page's script does it.
    import urllib.request, urllib.parse
    req = urllib.request.Request(f"{SITE}/conversation/score/?t={tok1}", data=urllib.parse.urlencode({"t": tok1, "aiadn_action": "save_scores", **scores(students=31)}).encode(), headers={"X-AIADN-Autosave": "1"})
    resp = urllib.request.urlopen(req, timeout=20)
    check("autosave answers with JSON", json.loads(resp.read().decode()).get("saved") is True)
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("autosave stored the values", 'value="31"' in html)

    s, html, _ = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "submit_result", **scores(a_evidence=0, winner="", students=0)})
    check("submitting with gaps is refused with clear messages", "Score each criterion" in html and "Choose the winner" in html and "students took part" in html)
    check("what was typed is kept", 'value="4"' in html or "selected" in html)
    s, page_html, _ = s2.page(A["browser"], d1)
    check("nothing was submitted", "Everything is set" in page_html)
    s, _, h = Browser().post(f"{SITE}/conversation/score/", {"aiadn_action": "submit_result", **scores()}, follow=False)
    s2_, page_html, _ = s2.page(A["browser"], d1)
    check("a submission with no judge link at all is sent to sign in and does nothing", s == 302 and "Everything is set" in page_html)
    s, _, h = Browser().post(f"{SITE}/conversation/score/?d={d1}", {"aiadn_action": "submit_result", **scores()}, follow=False)
    s2_, page_html, _ = s2.page(A["browser"], d1)
    check("knowing the Debate ID alone does not let anyone score", s == 302 and "Everything is set" in page_html)

    mail_clear()
    s, html, _ = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "submit_result", **scores()})
    check("a complete scorecard is submitted", "Result submitted" in html and "16 - 14" in html)
    check("both teachers are emailed the result", has_mail(A["teacher"], "result is in") and has_mail(B["teacher"], "result is in"))
    s, page_html, _ = s2.page(A["browser"], d1)
    check("the debate is now completed and shows the result", "16 - 14" in page_html and "Strong rebuttals" in page_html)
    check("School A sees only the comment for School A", "Great evidence, slow down" not in page_html)
    s, page_b, _ = s2.page(B["browser"], d1)
    check("School B sees only the comment for School B", "Great evidence, slow down" in page_b and "Strong rebuttals" not in page_b)
    check("the tracker shows scorecard and result done", "Scorecard" in page_html and "Result" in page_html and "16 - 14" in page_html)
    s, html, _ = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "submit_result", **scores(winner="b")})
    s, page_html, _ = s2.page(A["browser"], d1)
    check("a result is final: it cannot be submitted twice", "Strong rebuttals" in page_html and "16 - 14" in page_html)
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("the judge sees what they submitted and can rate", "Result submitted" in html and "How was judging" in html)
    s, html, _ = j.post(f"{SITE}/conversation/score/?t={tok1}", {"t": tok1, "aiadn_action": "rate", "rating": "5"})
    check("the judge can rate", "Thank you for the feedback" in html)
    s, html, _ = j.get(f"{SITE}/conversation/score/?t={tok1}")
    check("the judge is only asked once", "How was judging" not in html)
    check("teachers are asked how easy it was", "Quick feedback" in page_html)
    s, html, _ = s2.act(A["browser"], d1, "rate", rating="4")
    check("a teacher rates the debate", "Thank you for the feedback" in html and "Quick feedback" not in html)
    check("ratings are stored once each", sql(f"SELECT COUNT(*) FROM wp_aiadn_ratings r JOIN wp_aiadn_debates d ON d.id=r.debate_id WHERE d.code='{d1}'") == "2")

    print("\n4. Results and public page")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/results/")
    check("School A's results page shows one debate won", "Won" in html and B["name"] in html and ">1<" in html)
    scored = int(sql("SELECT COUNT(*) FROM wp_aiadn_debates d JOIN wp_aiadn_scorecards c ON c.debate_id=d.id AND c.status='submitted' WHERE d.age_group='primary' AND d.status='completed' AND NOT EXISTS (SELECT 1 FROM wp_aiadn_issues i WHERE i.debate_id=d.id AND i.status='reported')") or "0")
    if scored < 5:
        check("averages are held back until there is enough data", "Averages appear once" in html and "average " not in html.lower().replace("averages appear once", ""))
    else:
        check("averages are shown once there are 5 or more scored debates in the age group", "average " in html.lower())
    check("certificate progress is 1 of 2", "1 of 2" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("the public page lists the debate with both schools, theme and motion", A["name"] in html and B["name"] in html and "AI art should win prizes" in html and "CREATIVE" in html)
    check("the judge is named because they agreed", "Dr Amy Chen" in html or "Judge Number" in html or "Amy" in html)
    check("no school codes, teacher emails or student details are public", A["code"] not in html and A["teacher"] not in html and B["teacher"] not in html and "Sam Patel" not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/?theme=safe")
    check("the theme filter works", A["name"] not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/results/", follow=False)
    check("results need sign-in", s == 302)
    s, html, _ = Browser().get(f"{SITE}/conversation/certificate/", follow=False)
    check("the certificate page needs sign-in", s == 302)

    print("\n5. The certificate needs two DIFFERENT schools")
    d2 = run_debate(A, B, 2, winner="b")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    check("a second debate against the same school does not unlock it", "1 of 2" in html and "AIAD-DS-" not in html)
    mail_clear()
    d3 = run_debate(A, C, 3, winner="a")
    check("a debate against a different school unlocks it", has_mail(A["teacher"], "certificate"))
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    ref = re.search(r"AIAD-DS-[2-9A-HJ-NP-Z]{5}", html)
    check("the certificate names both different schools and has a reference", bool(ref) and B["name"] in html and C["name"] in html)
    ref = ref.group(0) if ref else ""
    check("the reference does not reveal the school code", A["code"].split("-")[1] not in ref and A["code"] not in html)
    s, html, _ = B["browser"].get(f"{SITE}/conversation/certificate/")
    check("the other school, which only met Oakfield, has none yet", "AIAD-DS-" not in html and "1 of 2" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/check/?ref={ref}")
    check("anyone can check the certificate is valid", "Valid" in html and A["name"] in html)
    check("the check page never shows the school code", A["code"] not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/check/?ref=AIAD-DS-22222")
    s2_, html2, _ = Browser().get(f"{SITE}/conversation/check/?ref=nonsense")
    check("an unknown or malformed reference gives the same answer", "could not find" in html and "could not find" in html2)

    print("\n6. Reporting an issue holds the result")
    mail_clear()
    s, html, _ = C["browser"].get(f"{SITE}/conversation/issue/?d={d3['code']}")
    token = field(html, "csrf")
    check("the issue page offers the categories and a safeguarding reminder", "The result is wrong" in html and "Designated Safeguarding Lead" in html)
    s, html, _ = C["browser"].post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": token, "aiadn_action": "report_issue", "category": "", "details": "x"})
    check("an issue needs a category", "Choose what happened" in html)
    s, html, _ = C["browser"].post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": token, "aiadn_action": "report_issue", "category": "result_wrong", "details": "We think the scores were swapped."})
    check("an issue can be reported", "Issue logged" in html and "ISS-" in html)
    check("the result is on hold", "Result on hold" in html)
    check("both schools' senior leaders and the team are emailed", has_mail(A["slt"], "issue reported") and has_mail(C["slt"], "issue reported") and has_mail(e2e.SITE and A["teacher"], "issue reported"))
    text = " ".join(m["text"] for m in mails(A["slt"]))
    check("the details are not put in email", "scores were swapped" not in text)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("a held result disappears from the public page", C["name"] not in html)
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    check("an existing certificate stays valid while an issue is open", ref in html)
    s, html, _ = s2.page(A["browser"], d3["code"])
    check("the debate page says the result is on hold", "Result on hold" in html)
    s, html, _ = C["browser"].post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": token, "aiadn_action": "resolve_issue", "issue_id": "1", "resolution": "stands"})
    s, html2, _ = C["browser"].get(f"{SITE}/conversation/issue/?d={d3['code']}")
    check("a teacher cannot close an issue, only a senior leader", "Result on hold" in html2 and "Close this issue" not in html2)
    s, html, _ = D["browser"].get(f"{SITE}/conversation/issue/?d={d3['code']}")
    check("another school cannot see the issue", "could not find" in html and "scores were swapped" not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/issue/?d={d3['code']}", follow=False)
    check("a stranger is sent to sign in", s == 302)

    print("\n7. A senior leader closes it; the other school can re-open it")
    slt_c = Browser()
    dash = e2e.sign_in(slt_c, C["code"], C["slt"], role="slt")
    check("Hillview's headteacher signs in", bool(dash))
    s, html, _ = slt_c.get(f"{SITE}/conversation/issue/?d={d3['code']}")
    check("the senior leader sees the details and can close it", "scores were swapped" in html and "Close this issue" in html)
    iid = re.search(r'name="issue_id" value="(\d+)"', html).group(1)
    tk = field(html, "csrf")
    mail_clear()
    s, html, _ = slt_c.post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": tk, "aiadn_action": "resolve_issue", "issue_id": iid, "resolution": "stands"})
    check("closing it with 'the result stands' works", "The issue is closed" in html and "Closed: The result stands" in html)
    check("everyone is told it is closed", has_mail(A["slt"], "issue resolved") and has_mail(C["slt"], "issue resolved"))
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("the result is public again", C["name"] in html)
    check("the resolver cannot re-open their own decision", "Re-open this issue" not in slt_c.get(f"{SITE}/conversation/issue/?d={d3['code']}")[1])
    slt_a = Browser()
    e2e.sign_in(slt_a, A["code"], A["slt"], role="slt")
    s, html, _ = slt_a.get(f"{SITE}/conversation/issue/?d={d3['code']}")
    check("the other school can re-open it", "Re-open this issue" in html)
    tk_a = field(html, "csrf")
    s, html, _ = slt_a.post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": tk_a, "aiadn_action": "reopen_issue", "issue_id": iid})
    check("re-opening puts the result on hold again", "re-opened" in html and "Result on hold" in html)

    print("\n8. The judge re-submits")
    mail_clear()
    s, html, _ = slt_a.post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": tk_a, "aiadn_action": "resolve_issue", "issue_id": iid, "resolution": "resubmit"})
    check("closing it as 're-submit' reopens the scorecard", "Closed: The judge re-submits" in html)
    check("the judge is asked to check the scores", has_mail(d3["judge"], "check your scores"))
    s, page, _ = s2.page(A["browser"], d3["code"])
    check("the debate is back to waiting for the scorecard", "Everything is set" in page)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("the result is not public while it is being re-scored", C["name"] not in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/score/?t={d3['token']}")
    check("the judge can edit the scorecard again", "Submit result" in html)
    Browser().post(f"{SITE}/conversation/score/?t={d3['token']}", {"t": d3["token"], "aiadn_action": "submit_result", **scores(a=(3, 3, 3, 3), b=(4, 4, 4, 4), winner="b")})
    s, page, _ = s2.page(C["browser"], d3["code"])
    check("the corrected result replaces the old one", "12 - 16" in page)
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("the corrected result is public", C["name"] in html)

    print("\n9. A void debate withdraws the certificate, never deletes it")
    s, html, _ = C["browser"].get(f"{SITE}/conversation/issue/?d={d3['code']}")
    tk = field(html, "csrf")
    C["browser"].post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": tk, "aiadn_action": "report_issue", "category": "cancelled", "details": "It did not go ahead."})
    s, html, _ = slt_a.get(f"{SITE}/conversation/issue/?d={d3['code']}")
    iid2 = re.findall(r'name="issue_id" value="(\d+)"', html)
    open_id = [i for i in iid2 if i != iid][0]
    mail_clear()
    s, html, _ = slt_a.post(f"{SITE}/conversation/issue/?d={d3['code']}", {"csrf": field(html, "csrf"), "aiadn_action": "resolve_issue", "issue_id": open_id, "resolution": "void"})
    check("a senior leader can void a debate", "Closed: The debate is void" in html)
    check("the certificate holder is told it is withdrawn", has_mail(A["teacher"], "withdrawn"))
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    check("the certificate page says withdrawn, and why", "Withdrawn" in html and "corrected" in html)
    s, html, _ = Browser().get(f"{SITE}/conversation/check/?ref={ref}")
    check("the check page says withdrawn, never a dead link", "Withdrawn" in html and A["name"] in html)
    check("the certificate row is kept", sql(f"SELECT status FROM wp_aiadn_certificates WHERE reference='{ref}'") == "withdrawn")
    s, html, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("a void debate is not public", C["name"] not in html)
    s, page, _ = s2.page(A["browser"], d3["code"])
    check("the debate page says void", "voided" in page.lower())

    print("\n10. A new debate against a different school restores it")
    mail_clear()
    run_debate(A, D, 4, winner="a")
    s, html, _ = A["browser"].get(f"{SITE}/conversation/certificate/")
    check("the same certificate is restored", ref in html and "Withdrawn" not in html)
    check("the school is told", has_mail(A["teacher"], "certificate"))
    s, html, _ = Browser().get(f"{SITE}/conversation/check/?ref={ref}")
    check("the check page is valid again", "Valid" in html)

    print("\n11. Access")
    st = Browser()
    st_dash = A["browser"].get(f"{SITE}/conversation/school/")[1]
    pin_form = field(st_dash, "csrf")
    A["browser"].post(f"{SITE}/conversation/school/", {"csrf": pin_form, "aiadn_action": "new_pin"})
    pin = re.search(r'aiadn__bigcode--pin">(\d{4})<', A["browser"].get(f"{SITE}/conversation/school/")[1])
    if pin:
        st.post(f"{SITE}/conversation/join/", {"aiadn_action": "front_door", "school_code": A["code"], "role": "student", "pin": pin.group(1)})
        s, _, h = st.get(f"{SITE}/conversation/results/", follow=False)
        check("a student cannot open the results page", s == 302)
        s, _, h = st.get(f"{SITE}/conversation/certificate/", follow=False)
        check("a student cannot open the certificate", s == 302)
    else:
        check("could start a class PIN", False)
    s, html, _ = D["browser"].get(f"{SITE}/conversation/debate/?d={d1}")
    check("another school cannot open a completed debate", "could not find" in html)

    print(f"\n{len(e2e.PASSED)} passed, {len(e2e.FAILED)} failed")
    if e2e.FAILED:
        print("Failed: " + "; ".join(e2e.FAILED))
        sys.exit(1)


if __name__ == "__main__":
    main()
