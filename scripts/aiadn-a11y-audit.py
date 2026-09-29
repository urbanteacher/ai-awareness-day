#!/usr/bin/env python3
"""
Accessibility audit for the Debate Network pages (WCAG 2.2 AA, the static part of it).

Builds a small campaign (two schools, a judged debate, a class survey, a referral, a nomination), visits every
kind of page as the person who would see it, and checks the HTML for the failures that can be found without a
screen reader: language, one h1, heading order, labels on every field, names on every button and link, image
alt text, table headers, alerts for errors, duplicate ids, positive tabindex, and colour contrast for every
pairing of the palette. It cannot replace a person using a screen reader and a keyboard.

    python3 scripts/aiadn-a11y-audit.py
"""
import importlib.util
import os
import re
import sys
from html.parser import HTMLParser

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("s11", os.path.join(HERE, "aiadn-e2e-slice11.py"))
s11 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s11)
s10, s7, s6, s3, s2, e2e = s11.s10, s11.s7, s11.s6, s11.s3, s11.s2, s11.e2e
s4 = s6.s4
SITE, Browser, field, loc = e2e.SITE, e2e.Browser, e2e.field, e2e.loc
mails, mail_clear, link_for, code_for = e2e.mails, e2e.mail_clear, e2e.link_for, e2e.code_for
sql, RUN = s3.sql, s3.RUN
U = RUN.upper()

# ---------------------------------------------------------------- colour contrast
PALETTE = {"cream": "F6F4ED", "ink": "231F20", "dim": "54504E", "card": "EAE7DF", "crit": "A32D2D", "white": "FFFFFF"}
STRANDS = {"safe": ("00BEDD", "006A7D"), "smart": ("FF7038", "A7350B"), "creative": ("AC91FF", "6441B8"), "responsible": ("63DF93", "176E3B"), "future": ("FA83EB", "983488")}


def lum(hexcol):
    def ch(v):
        v /= 255
        return v / 12.92 if v <= 0.03928 else ((v + 0.055) / 1.055) ** 2.4
    r, g, b = (int(hexcol[i:i + 2], 16) for i in (0, 2, 4))
    return 0.2126 * ch(r) + 0.7152 * ch(g) + 0.0722 * ch(b)


def ratio(a, b):
    la, lb = sorted((lum(a), lum(b)), reverse=True)
    return (la + 0.05) / (lb + 0.05)


def contrast_findings():
    """(pairing, ratio, needed, where it is used)"""
    out = []
    pairs = [("ink on cream", "ink", "cream", 4.5, "body text"), ("ink on card", "ink", "card", 4.5, "panels"), ("dim on cream", "dim", "cream", 4.5, "small grey text"), ("dim on card", "dim", "card", 4.5, "small grey text in panels"),
             ("critical red on cream", "crit", "cream", 4.5, "error text"), ("critical red on card", "crit", "card", 4.5, "error text in panels"), ("cream on ink", "cream", "ink", 4.5, "ink panels and buttons")]
    for name, a, b, need, where in pairs:
        out.append((name, ratio(PALETTE[a], PALETTE[b]), need, where))
    for s, (bright, deep) in STRANDS.items():
        out.append((f"ink on {s} band", ratio(PALETTE["ink"], bright), 4.5, "page title and eyebrow on the coloured band"))
        out.append((f"{s} deep on cream", ratio(deep, PALETTE["cream"]), 4.5, "links, eyebrows and headings"))
        out.append((f"{s} deep on card", ratio(deep, PALETTE["card"]), 4.5, "eyebrows in panels"))
        out.append((f"{s} bright on ink", ratio(bright, PALETTE["ink"]), 4.5, "eyebrows and buttons on ink"))
        out.append((f"ink on {s} button", ratio(PALETTE["ink"], bright), 4.5, "buttons"))
    return out


# ---------------------------------------------------------------- HTML checks
class Page(HTMLParser):
    """Collects what the checks need: every tag with its attributes, the text inside links, buttons, headings and
    labels, which fields sit inside a label, and which labels point at a field."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.tags = []
        self.stack = []
        self.text = {}
        self.labels_for = set()
        self.wrapped = set()
        self.open_labels = 0
        self.headings = []
        self.ids = []
        self.lang = None

    def handle_starttag(self, tag, attrs):
        a = dict(attrs)
        idx = len(self.tags)
        self.tags.append((tag, a))
        if tag == "html":
            self.lang = a.get("lang")
        if "id" in a:
            self.ids.append(a["id"])
        if tag == "label":
            self.open_labels += 1
            if a.get("for"):
                self.labels_for.add(a["for"])
        if tag in ("input", "select", "textarea") and self.open_labels:
            self.wrapped.add(idx)
        if tag in ("label", "a", "button", "h1", "h2", "h3", "h4", "summary", "th", "legend"):
            self.stack.append((tag, idx))
            self.text[idx] = ""
        if tag in ("h1", "h2", "h3", "h4"):
            self.headings.append(int(tag[1]))

    def handle_endtag(self, tag):
        if tag == "label" and self.open_labels:
            self.open_labels -= 1
        for i in range(len(self.stack) - 1, -1, -1):
            if self.stack[i][0] == tag:
                del self.stack[i:]
                break

    def handle_data(self, data):
        for _, idx in self.stack:
            self.text[idx] += data

    def has_img_alt(self, idx):
        return any(t == "img" and a.get("alt") for t, a in self.tags[idx + 1:idx + 4])


def audit_page(html, label):
    p = Page()
    p.feed(html)
    f = []
    if not p.lang:
        f.append("the page has no lang attribute")
    if p.headings.count(1) != 1:
        f.append(f"{p.headings.count(1)} h1 headings (want exactly one)")
    prev = 0
    for h in p.headings:
        if prev and h > prev + 1:
            f.append(f"heading order jumps from h{prev} to h{h}")
        prev = h
    if not any(t == "main" for t, _ in p.tags):
        f.append("no main landmark")
    dup = {i for i in p.ids if p.ids.count(i) > 1}
    if dup:
        f.append("duplicate ids: " + ", ".join(sorted(dup)))
    for idx, (tag, a) in enumerate(p.tags):
        if tag == "input" and a.get("type", "text") not in ("hidden", "submit", "button", "image", "reset"):
            if not (a.get("aria-label") or a.get("aria-labelledby") or a.get("title") or (a.get("id") and a["id"] in p.labels_for) or idx in p.wrapped):
                f.append(f"input '{a.get('name') or a.get('type')}' has no label")
        if tag in ("select", "textarea"):
            if not (a.get("aria-label") or (a.get("id") and a["id"] in p.labels_for) or idx in p.wrapped):
                f.append(f"{tag} '{a.get('name')}' has no label")
        if tag == "button" and not (p.text.get(idx, "").strip() or a.get("aria-label") or a.get("title")):
            f.append("a button has no text")
        if tag == "a" and "href" in a and a.get("aria-hidden") != "true":
            txt = p.text.get(idx, "").strip()
            if not (txt or a.get("aria-label")) and not p.has_img_alt(idx):
                f.append(f"a link to {a['href'][:50]} has no text")
            if txt.lower() in ("click here", "here", "read more", "more"):
                f.append(f"link text '{txt}' says nothing on its own")
        if tag == "img" and "alt" not in a:
            f.append(f"image {a.get('src', '')[-40:]} has no alt text")
        if tag == "th" and "scope" not in a:
            f.append("a table header cell has no scope")
        if a.get("tabindex", "0").lstrip("-").isdigit() and int(a.get("tabindex", "0")) > 0:
            f.append("positive tabindex")
        if tag == "input" and a.get("name") == "email" and "autocomplete" not in a:
            f.append("email field has no autocomplete (WCAG 1.3.5)")
        if tag == "details" and not any(t == "summary" for t, _ in p.tags[idx + 1:idx + 6]):
            f.append("a details element has no summary")
    return sorted(set(f))


def main():
    e2e.clear_rate_limits()
    mail_clear()
    admin_login = f"aiadn6{RUN}admin"
    s6.drop_users()
    s6.make_user(admin_login, 'a:1:{s:13:"administrator";b:1;}', 10)
    problems = 0
    try:
        print("Colour contrast (WCAG 1.4.3, 4.5:1 for text)")
        for name, r, need, where in contrast_findings():
            ok = r >= need
            problems += 0 if ok else 1
            print(f"  {'ok  ' if ok else 'FAIL'} {r:5.2f}  {name}   ({where})")
        pages = build_pages(admin_login)
        print("\nPage checks")
        for label, html in pages:
            found = audit_page(html, label)
            problems += len(found)
            print(f"  {'ok  ' if not found else 'FAIL'} {label}")
            for x in found:
                print(f"         - {x}")
    finally:
        s6.drop_users()
    print(f"\n{problems} problem(s)")
    sys.exit(1 if problems else 0)


def build_pages(admin_login):
    A = s2.make_school("aa", f"Access Primary {U}", "LS6 2AB")
    B = s2.make_school("bb", f"Bright Academy {U}", "M1 1AA")
    d = s3.run_debate(A, B, 71, winner="a")
    d_ready = s3.run_debate(A, B, 72, score=False)
    pin, _ = s4.start_pin(A)
    s4.class_of(A, pin, 10)
    R = s7.register_with_ref("rr", f"Referred School {U}", "EH1 1AA", f"Org {U}", f"jo@org{RUN}.org", tick=True)
    pages = []
    get = lambda label, b, url: pages.append((label, b.get(url)[1]))
    anon = Browser()
    for label, url in [("front door", "/conversation/join/"), ("register", "/conversation/register/"), ("ask to join (colleague)", "/conversation/colleague/"), ("nominate", "/conversation/nominate/"), ("check a certificate", "/conversation/check/"), ("public results", "/conversation/debates/"), ("partner: ask for a link", "/conversation/partner/")]:
        get(f"public: {label}", anon, SITE + url)
    inv = mails(A["teacher"])
    slt_link = None
    try:
        slt_link = link_for(R["slt"], "approve") or R.get("approve_link")
    except Exception:
        slt_link = R.get("approve_link")
    if slt_link:
        get("headteacher: approval page", anon, slt_link)
    for label, url in [("school dashboard", "/conversation/school/"), ("Join the conversation board", "/conversation/find/"), ("results and certificate", "/conversation/results/"), ("Student Voice results", "/conversation/voice/"), ("whiteboard", "/conversation/board/"), ("School AI Snapshot", "/conversation/snapshot/"), (f"a completed debate ({d['code']})", f"/conversation/debate/?d={d['code']}"), (f"a ready debate ({d_ready['code']})", f"/conversation/debate/?d={d_ready['code']}"), ("prep pack", f"/conversation/prep/?d={d_ready['code']}"), ("paper scorecard", f"/conversation/paper/?d={d_ready['code']}"), ("report an issue", f"/conversation/issue/?d={d['code']}")]:
        get(f"teacher: {label}", A["browser"], SITE + url)
    tok = d_ready["token"]
    for label, url in [("judge page", f"/conversation/judge/?t={tok}"), ("scorecard", f"/conversation/score/?t={tok}"), ("running order", f"/conversation/prep/?t={tok}")]:
        get(f"judge: {label}", Browser(), SITE + url)
    st, html = s4.student_in(A, pin)
    pages.append(("student: survey", html))
    admin, _, _ = s6.wp_login(admin_login)
    get("programme team: dashboard", admin, SITE + "/conversation/programme/")
    get("programme team: partner report", admin, SITE + "/conversation/programme/?partner=" + (sql(f"SELECT partner_id FROM wp_aiadn_referrals WHERE referrer_email='jo@org{RUN}.org' LIMIT 1") or "0"))
    get("homepage", anon, SITE + "/")
    return pages


if __name__ == "__main__":
    main()
