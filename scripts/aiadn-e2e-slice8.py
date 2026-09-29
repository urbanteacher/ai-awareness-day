#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 8: Find a Debate.

A school with no opponent puts a request on the board. Other schools ask, the host chooses, and the normal
debate flow starts. Nobody's name or email is shown to another school.

    python3 scripts/aiadn-e2e-slice8.py
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

SITE, Browser, check, field = e2e.SITE, e2e.Browser, e2e.check, e2e.field
mails, mail_clear, link_for = e2e.mails, e2e.mail_clear, e2e.link_for
sql, RUN = s3.sql, s3.RUN
U = RUN.upper()

REQ = dict(age_group="primary", theme="creative", req_dates="Any Tuesday in February", req_format="either", req_host="yes", req_travel="region")


def board(school, query=""):
    s, html, _ = school["browser"].get(f"{SITE}/conversation/find/{query}")
    return html


def ask(school, code):
    html = board(school)
    return school["browser"].post(f"{SITE}/conversation/find/", {"csrf": field(html, "csrf"), "aiadn_action": "find_ask", "d": code})


def publish(school, **over):
    code = s2.new_debate(school)
    s, html, _ = s2.act(school["browser"], code, "publish_request", **{**REQ, **over})
    return code, html


def status(code):
    return sql(f"SELECT status FROM wp_aiadn_debates WHERE code='{code}'")


def req_status(code, school):
    return sql(f"SELECT r.status FROM wp_aiadn_debate_requests r JOIN wp_aiadn_debates d ON d.id=r.debate_id WHERE d.code='{code}' AND r.school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{school['code']}')")


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
    A = s2.make_school("aa", f"Alder Primary {U}", "LS6 2AB")
    B = s2.make_school("bb", f"Birch Academy {U}", "M1 1AA")
    C = s2.make_school("cc", f"Cedar School {U}", "B1 1AA")
    D = s2.make_school("dd", f"Dogwood School {U}", "EH1 1AA")
    check("four approved schools", all(x["ok"] for x in (A, B, C, D)))

    print("\n1. Who can see the board")
    s, _, _ = Browser().get(f"{SITE}/conversation/find/", follow=False)
    check("a stranger is sent to the front door", s == 302)
    pin, _ = s4.start_pin(A)
    st, _ = s4.student_in(A, pin)
    s, _, _ = st.get(f"{SITE}/conversation/find/", follow=False)
    check("a student is sent away too", s == 302)
    s, html, _ = A["browser"].get(f"{SITE}/conversation/school/")
    check("a school's page links to Find a Debate", "conversation/find" in html)
    check("the board says schools see names and areas only", "nothing else about you" in board(A))

    print("\n2. Putting a request on the board")
    d1 = s2.new_debate(A)
    s, html, _ = s2.page(A["browser"], d1)
    check("a debate waiting for an opponent offers Find a Debate as an option", "Option 3: Find a Debate" in html and "They never see anyone" in html)
    s, html, _ = s2.act(A["browser"], d1, "publish_request", **{**REQ, "theme": ""})
    check("a theme is required", "Choose a theme" in html and status(d1) == "awaiting_opponent")
    s, html, _ = s2.act(A["browser"], d1, "publish_request", **{**REQ, "req_dates": "x"})
    check("so is a note of when suits", "Say when suits" in html)
    s, html, _ = s2.act(A["browser"], d1, "publish_request", **{**REQ, "req_host": "maybe"})
    check("and a valid choice for hosting", "Choose an option" in html)
    s, html, _ = s2.act(A["browser"], d1, "publish_request", **REQ)
    check("a good request goes on the board", "on Find a Debate" in html and sql(f"SELECT open_request FROM wp_aiadn_debates WHERE code='{d1}'") == "1")

    print("\n3. What other schools see")
    html = board(B)
    check("the card names the school and its area, and what it is looking for", A["name"] in html and "Yorkshire and The Humber" in html and "Any Tuesday in February" in html and "CREATIVE" in html and "Primary" in html and "We can host" in html and "Within our region" in html)
    check("no teacher name, email or code is shown", "Test Teacher" not in html and A["teacher"] not in html and A["slt"] not in html and A["code"] not in html)
    check("a school does not see its own request on the board", A["name"] not in board(A).split("Waiting for an answer")[0] or "No open requests" in board(A))
    check("the theme filter works", A["name"] not in board(B, "?theme=safe") and A["name"] in board(B, "?theme=creative"))
    check("the age filter works", A["name"] not in board(B, "?age=secondary") and A["name"] in board(B, "?age=primary"))
    check("an 'either' request shows for online and for in person", A["name"] in board(B, "?format=online") and A["name"] in board(B, "?format=in_person"))

    print("\n3b. Finding schools in your own area")
    E = s2.make_school("ee", f"Elm Primary {U}", "EH2 2AA")
    check("a fifth school, in Scotland", E["ok"])
    dE, _ = publish(E)
    html = board(D)
    check("a school in your own area is marked and comes first", "In your area" in html and html.index(E["name"]) < html.index(A["name"]), "order")
    check("schools in other areas are not marked as near", ("<h2>" + E["name"] + "</h2><p class=\"aiadn__eyebrow\">In your area") in html and ("<h2>" + A["name"] + "</h2><dl") in html)
    check("the area filter offers every region, and marks yours", '<option value="Scotland" selected' not in html and "Scotland (yours)" in html and "London" in html)
    check("filtering by Scotland shows only Scottish schools", E["name"] in board(D, "?region=Scotland") and A["name"] not in board(D, "?region=Scotland"))
    check("filtering by Yorkshire shows the Yorkshire school and hides the rest", A["name"] in board(B, "?region=Yorkshire%20and%20The%20Humber") and E["name"] not in board(B, "?region=Yorkshire%20and%20The%20Humber"))
    check("an area with nothing in it says so", "No open requests match" in board(B, "?region=London"))
    check("the area is worked out from the postcode, not stored", "region" not in sql("SHOW COLUMNS FROM wp_aiadn_schools").lower())
    s2.act(E["browser"], dE, "unpublish_request")

    print("\n4. Asking")
    mail_clear()
    s, html, _ = ask(B, d1)
    check("asking is confirmed", "We have told them" in html)
    got = [m for m in mails(A["teacher"]) if "would like to debate you" in m["subject"].lower()]
    check("the host is emailed, with the school's name and no other detail", len(got) == 1 and B["name"] in got[0]["subject"] and B["teacher"] not in got[0]["text"])
    s, html, _ = ask(B, d1)
    check("asking twice is refused", "already asked" in html)
    s, html, _ = ask(C, d1)
    check("a second school can ask too", "We have told them" in html)
    check("the board shows a school it has asked", "Waiting for their answer" in board(B))
    bad = B["browser"].post(f"{SITE}/conversation/find/", {"csrf": "nope", "aiadn_action": "find_ask", "d": d1})
    check("a request without the page's token does nothing", req_status(d1, D) == "")

    print("\n5. The host chooses")
    s, html, _ = s2.page(A["browser"], d1)
    check("the host sees both, by name and area", "2 schools would like to debate you" in html and B["name"] in html and C["name"] in html and "Accept" in html and "Decline" in html)
    rid = re.search(r'name="request_id" value="(\d+)"', html).group(1)
    s, html, _ = B["browser"].post(f"{SITE}/conversation/debate/?d={d1}", {"csrf": field(board(B), "csrf"), "d": d1, "aiadn_action": "decide_request", "request_id": rid, "decision": "accept"})
    check("another school cannot accept on the host's behalf", status(d1) == "awaiting_opponent")
    rid_c = sql(f"SELECT r.id FROM wp_aiadn_debate_requests r JOIN wp_aiadn_debates d ON d.id=r.debate_id WHERE d.code='{d1}' AND r.school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{C['code']}')")
    mail_clear()
    s, html, _ = s2.act(A["browser"], d1, "decide_request", request_id=rid_c, decision="accept")
    check("the host accepts Cedar: it is a match", status(d1) == "matched" and sql(f"SELECT school_b_id FROM wp_aiadn_debates WHERE code='{d1}'") == sql(f"SELECT id FROM wp_aiadn_schools WHERE code='{C['code']}'") and "you have accepted" in html.lower())
    check("both schools are told about the match", any("match" in m["subject"].lower() or "debate" in m["subject"].lower() for m in mails(C["teacher"])) and any("match" in m["subject"].lower() or "debate" in m["subject"].lower() for m in mails(A["teacher"])))
    check("the other school is told it is taken", any("about your request" in m["subject"].lower() and "taken" in m["text"].lower() for m in mails(B["teacher"])))
    check("the request comes off the board", A["name"] not in board(D) and sql(f"SELECT open_request FROM wp_aiadn_debates WHERE code='{d1}'") == "0")
    check("the losing ask is closed", req_status(d1, B) == "declined" and req_status(d1, C) == "accepted")
    s, html, _ = s2.page(A["browser"], d1)
    check("the normal flow carries on: the proposal form starts with the age group and theme asked for", 'value="primary" checked' in html and re.search(r'<option value="creative"[^>]*selected', html) is not None)

    print("\n6. Declining, and asking again after withdrawing")
    d2, _ = publish(A)
    mail_clear()
    ask(D, d2)
    rid_d = sql(f"SELECT r.id FROM wp_aiadn_debate_requests r JOIN wp_aiadn_debates d ON d.id=r.debate_id WHERE d.code='{d2}'")
    s, html, _ = s2.act(A["browser"], d2, "decide_request", request_id=rid_d, decision="decline")
    check("declining is polite and says nothing about why", "declined" in html.lower() and any("chosen not to go ahead" in m["text"] for m in mails(D["teacher"])) and status(d2) == "awaiting_opponent")
    check("the board tells the school it was not chosen this time", "chose not to go ahead" in board(D))
    s, html, _ = ask(D, d2)
    check("a declined school cannot keep asking", "already asked" in html)
    d3, _ = publish(A)
    ask(B, d3)
    html = board(B)
    rid_w = re.search(r'name="request_id" value="(\d+)"', html).group(1)
    B["browser"].post(f"{SITE}/conversation/find/", {"csrf": field(html, "csrf"), "aiadn_action": "find_withdraw", "request_id": rid_w})
    check("a school can withdraw its ask", req_status(d3, B) == "withdrawn")
    s, html, _ = s2.page(A["browser"], d3)
    check("the host no longer sees it", "would like to debate you" not in html)
    s, html, _ = ask(B, d3)
    check("and can ask again", "We have told them" in html and req_status(d3, B) == "pending")

    print("\n7. Limits")
    d4, _ = publish(A)
    d5 = s2.new_debate(A)
    s, html, _ = s2.act(A["browser"], d5, "publish_request", **REQ)
    check("a school can have three requests open at once", "already have 3 open requests" in html, re.sub(r"<[^>]+>", " ", html)[-300:])
    ds = []
    for i in range(3):
        c, _ = publish(D)
        ds.append(c)
    mail_clear()
    for code in (d2, d3, d4):
        ask(B, code) if req_status(code, B) == "" else None
    for code in ds:
        s, html, _ = ask(B, code)
    pend = sql(f"SELECT COUNT(*) FROM wp_aiadn_debate_requests WHERE status='pending' AND school_id=(SELECT id FROM wp_aiadn_schools WHERE code='{B['code']}')")
    check("a school can have five asks waiting, and no more", pend == "5" and "requests waiting" in html, f"{pend}")

    print("\n8. When the place is taken another way, or the request comes down")
    other_link = None
    s, html, _ = s2.act(A["browser"], d4, "new_link")
    link = re.search(r'value="(http[^"]+invite[^"]+)"', html).group(1).replace("&#038;", "&").replace("&amp;", "&")
    ask(C, d4)
    mail_clear()
    s, html, _ = D["browser"].get(link)
    D["browser"].post(f"{SITE}/conversation/invite/", {"csrf": field(html, "csrf"), "aiadn_action": "accept_invite", "t": link.split("t=")[1]})
    check("someone accepting an invitation takes the request off the board", status(d4) == "matched" and sql(f"SELECT open_request FROM wp_aiadn_debates WHERE code='{d4}'") == "0")
    check("schools waiting on it are told", req_status(d4, B) == "declined" and req_status(d4, C) == "declined" and any("taken" in m["text"].lower() for m in mails(B["teacher"])))
    mail_clear()
    s2.act(A["browser"], d3, "unpublish_request")
    check("taking a request down closes the asks and tells the schools", sql(f"SELECT open_request FROM wp_aiadn_debates WHERE code='{d3}'") == "0" and req_status(d3, B) == "declined" and any("taken down" in m["text"].lower() for m in mails(B["teacher"])))
    check("and it is off the board (only the one still open remains)", board(C).count("<h2>" + A["name"] + "</h2>") == 1, str(board(C).count("<h2>" + A["name"] + "</h2>")))
    before = board(C).count("<h2>" + D["name"] + "</h2>")
    sql(f"UPDATE wp_aiadn_debates SET status='expired' WHERE code='{ds[0]}'")
    check("an expired debate drops off the board", board(C).count("<h2>" + D["name"] + "</h2>") == before - 1, f"{before} -> {board(C).count('<h2>' + D['name'] + '</h2>')}")

    print("\n9. For the programme team")
    admin, st, h = s6.wp_login(admin_login)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    check("the programme page counts Find a Debate", "Find a Debate" in html and "Open requests now" in html and "Accepted" in html)
    check("and names no school in that section", A["name"] not in html.split("Find a Debate")[1].split("Email check")[0])


if __name__ == "__main__":
    main()
