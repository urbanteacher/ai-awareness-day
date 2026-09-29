#!/usr/bin/env python3
"""
End-to-end test for the Debate Network plugin, slice 12: sign-ups and going live.

Checks that every link in every email works and changes nothing when opened, that the go-live check tells the
truth, that the plugin can be installed by the theme, and that the pages hold together on a phone.

    python3 scripts/aiadn-e2e-slice12.py
"""
import importlib.util
import json
import os
import re
import sys
import urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
spec = importlib.util.spec_from_file_location("a11y", os.path.join(HERE, "aiadn-a11y-audit.py"))
a11y = importlib.util.module_from_spec(spec)
spec.loader.exec_module(a11y)
s11, s10, s7, s6, s4, s3, s2, e2e = a11y.s11, a11y.s10, a11y.s7, a11y.s6, a11y.s4, a11y.s3, a11y.s2, a11y.e2e

SITE, Browser, check, field, loc = e2e.SITE, e2e.Browser, e2e.check, e2e.field, e2e.loc
mails, mail_clear, link_for, code_for = e2e.mails, e2e.mail_clear, e2e.link_for, e2e.code_for
sql, RUN, php = s3.sql, s3.RUN, s10.php
U = RUN.upper()


def all_mail_for_run():
    data = json.load(urllib.request.urlopen(e2e.MAIL + "/messages?limit=500", timeout=15))
    out = []
    for m in data.get("messages", []):
        addrs = [a["Address"].lower() for a in m.get("To", [])]
        if any(RUN in a for a in addrs):
            full = json.load(urllib.request.urlopen(f"{e2e.MAIL}/message/{m['ID']}", timeout=15))
            out.append({"to": addrs, "subject": m["Subject"], "text": full.get("Text", "")})
    return out


def fingerprint():
    q = lambda s: sql(s)
    return {
        "schools": q("SELECT GROUP_CONCAT(status ORDER BY id) FROM wp_aiadn_schools WHERE name LIKE '%" + U + "%'"),
        "members": q("SELECT GROUP_CONCAT(CONCAT(role,email) ORDER BY id) FROM wp_aiadn_members WHERE email LIKE '%" + RUN + "%'"),
        "referrals": q("SELECT GROUP_CONCAT(status ORDER BY id) FROM wp_aiadn_referrals WHERE org_name LIKE '%" + U + "%'"),
        "debates": q("SELECT GROUP_CONCAT(status ORDER BY id) FROM wp_aiadn_debates WHERE school_a_id IN (SELECT id FROM wp_aiadn_schools WHERE name LIKE '%" + U + "%')"),
        "judges": q("SELECT GROUP_CONCAT(status ORDER BY id) FROM wp_aiadn_judges WHERE email LIKE '%" + RUN + "%'"),
        "nominations": q("SELECT GROUP_CONCAT(status ORDER BY id) FROM wp_aiadn_nominations WHERE org_name LIKE '%" + U + "%'"),
    }


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
    print("\n1. Every link in every email")
    jo = f"jo@intro{RUN}.org"
    P = s7.register_with_ref("lpend", f"Pending Primary {U}", "LS1 1AA", f"Intro {U}", jo, tick=False, decide=False)
    B = s2.make_school("lbb", f"Link Academy {U}", "M1 1AA")
    C = s7.register_with_ref("lcc", f"Linked School {U}", "EH1 1AA", f"Intro {U}", jo, tick=True)
    check("three schools: one waiting for its headteacher, two approved", bool(P["code"]) and B["ok"] and C["ok"])
    d1 = s3.run_debate(B, C, 81, score=False)
    d2 = s3.run_debate(B, C, 82, winner="a")
    Browser().post(f"{SITE}/conversation/colleague/", {"aiadn_action": "colleague_request", "school_code": B["code"], "name": "New Person", "email": f"new{RUN}@gmail.com"})
    nb = Browser()
    s, html, _ = nb.post(f"{SITE}/conversation/nominate/", {"aiadn_action": "nominate", "name": "Nora Nominator", "email": f"nora{RUN}@nom{RUN}.org", "org": f"Nom Org {U}", "school": f"Nominee {U}", "school_email": f"head@nominee{RUN}.sch.uk"})
    ref = re.search(r'name="ref" value="([a-f0-9]{32})"', html)
    nb.post(f"{SITE}/conversation/nominate/", {"aiadn_action": "nominate_confirm", "ref": ref.group(1), "code": code_for(f"nora{RUN}@nom{RUN}.org")})

    msgs = all_mail_for_run()
    urls = []
    for m in msgs:
        for u in re.findall(r"https?://[^\s\"<>]+", m["text"]):
            u = u.rstrip(".,;)")
            if u.startswith(SITE) and u not in [x[0] for x in urls]:
                urls.append((u, m["subject"]))
    kinds = sorted({re.search(r"/conversation/(\w+)/", u).group(1) for u, _ in urls if "/conversation/" in u})
    check("the emails carry links to many kinds of page", len(urls) >= 12 and len(kinds) >= 6, f"{len(urls)} links, kinds: {kinds}")
    before = fingerprint()
    dead = []
    for u, subject in urls:
        b = Browser()
        st, body, _ = b.get(u)
        if st >= 400 or "We could not find that" in body or "critical error" in body:
            dead.append((st, u[:90], subject[:40]))
    check("every link works (opens a real page, signed in or not)", not dead, str(dead[:4]))
    after = fingerprint()
    check("opening every link changes nothing: no school approved, no colleague added, no referral shared, no judge answered", before == after, str({k: (before[k], after[k]) for k in before if before[k] != after[k]}))
    check("none of the links points at the wrong site", all(u.startswith(SITE) for u, _ in urls))

    print("\n2. The go-live check")
    admin, st, h = s6.wp_login(admin_login)
    s, html, _ = admin.get(f"{SITE}/conversation/programme/")
    sec = html.split("Go-live check")[1].split("</details>")[0] if "Go-live check" in html else ""
    check("the programme page has a Go-live check, open when something needs a look", "Go-live check" in html and "aiadn__golive" in sec)
    check("each line says what it is and whether it is fine, in words as well as marks", "Email service" in sec and "Time zone" in sec and "Scheduled tasks" in sec and "HTTPS" in sec and "aiadn__sr" in sec)
    check("on this development copy it warns that email goes to a test inbox, and flags http", "local test inbox" in sec and "not https" in sec)
    define = 'putenv("AIADN_SMTP_HOST"); define("AIADN_SMTP_HOST","smtp-relay.example.test"); define("AIADN_SMTP_PORT",587); define("AIADN_SMTP_USER","login-y"); define("AIADN_SMTP_PASS","secret-y"); define("AIADN_MAIL_FROM","info@aiawarenessday.co.uk"); define("AIADN_TEAM_EMAIL","team@aiawarenessday.co.uk");'
    out = subprocess_php(define + ' echo "R:" . json_encode(AIADN_Golive::checks());')
    rows = {r["label"]: r for r in json.loads(out)}
    check("with the Brevo login, sender and team address set, those three lines are fine", rows["Email service"]["status"] == "ok" and rows["Sender address"]["status"] == "ok" and rows["Programme team address"]["status"] == "ok", str([rows[k]["status"] for k in ("Email service", "Sender address", "Programme team address")]))
    check("the check never shows a login or a password", "secret-y" not in out and "login-y" not in out)
    check("the database, the pages and the scheduled tasks are reported as fine here", rows["Database"]["status"] == "ok" and rows["The /conversation/ pages"]["status"] == "ok" and rows["Scheduled tasks"]["status"] == "ok", str({k: rows[k]["status"] for k in ("Database", "The /conversation/ pages", "Scheduled tasks")}))

    print("\n3. Installing on the live site")
    listed = json.loads(subprocess_php('echo "R:" . json_encode(aiad_bundled_plugin_sentinel_files()["aiad-debate-network"]);'))
    on_disk = sorted("includes/" + f for f in os.listdir(os.path.join(ROOT, "plugins/aiad-debate-network/includes")) if f.endswith(".php")) + ["public/aiadn.css"]
    missing = [f for f in on_disk if f not in listed]
    check("the theme's list of files used to spot a stale copy names every file in the plugin", not missing, str(missing))
    s, page, _ = Browser().get(f"{SITE}/conversation/join/")
    href = re.search(r"href='([^']*aiadn\.css[^']*)'", page)
    ctype = ""
    body = ""
    if href:
        req = urllib.request.urlopen(href.group(1), timeout=10)
        ctype, body = req.headers.get("Content-Type", ""), req.read().decode("utf8", "replace")
    check("the plugin's stylesheet address works (also when the theme loads the plugin itself)", bool(href) and "text/css" in ctype and ".aiadn__band" in body, href.group(1) if href else "no stylesheet link")
    main_php = open(os.path.join(ROOT, "plugins/aiad-debate-network/aiad-debate-network.php")).read()
    check("the plugin builds that address from the theme when it lives there", "get_template_directory_uri" in main_php)

    print("\n4. On a phone")
    s, res, _ = Browser().get(f"{SITE}/conversation/debates/")
    check("a table wider than a phone scrolls inside its own box, which a keyboard can reach, and is named", 'class="aiadn__scroll" role="region" tabindex="0" aria-label="Debate results"' in res)
    s, dash, _ = admin.get(f"{SITE}/conversation/programme/")
    check("every table on the programme page scrolls the same way", dash.count("<table") == dash.count('class="aiadn__scroll"'), f"{dash.count('<table')} tables, {dash.count('aiadn__scroll')} boxes")
    css = open(os.path.join(ROOT, "plugins/aiad-debate-network/public/aiadn.css")).read()
    check("the scroll box has a visible focus outline", ".aiadn__scroll:focus-visible" in css)

    print("\n5. Accessibility, the part a script can check")
    problems = []
    for label, html in [("register", Browser().get(f"{SITE}/conversation/register/")[1]), ("front door", Browser().get(f"{SITE}/conversation/join/")[1]), ("nominate", Browser().get(f"{SITE}/conversation/nominate/")[1]), ("programme team", dash), ("public results", res), ("teacher's debate", B["browser"].get(f"{SITE}/conversation/debate/?d={d2['code']}")[1])]:
        for f in a11y.audit_page(html, label):
            problems.append(f"{label}: {f}")
    check("no missing labels, names, alt text, table headers or duplicate ids on the main pages", not problems, "; ".join(problems[:4]))
    check("every colour pairing in the palette is readable (4.5 to 1 or better)", all(r >= need for _, r, need, _ in a11y.contrast_findings()))


def subprocess_php(code):
    """Run PHP inside WordPress. `code` may start with define()/putenv() calls, which have to run before WordPress loads."""
    import subprocess
    head, _, tail = code.partition("echo ")
    program = 'define("WP_USE_THEMES", false); ' + head + ' require "/var/www/html/wp-load.php"; echo ' + tail
    out = subprocess.run(["docker", "compose", "exec", "-T", "wordpress", "php", "-r", program], capture_output=True, text=True, cwd=ROOT)
    lines = [l for l in out.stdout.splitlines() if l.startswith("R:")]
    return lines[-1][2:] if lines else out.stdout[-300:] + out.stderr[-300:]


if __name__ == "__main__":
    main()
