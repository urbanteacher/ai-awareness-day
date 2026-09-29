#!/usr/bin/env python3
"""
A busy-day rehearsal: two classes of 30 students join and answer at the same moment, while a crowd opens the
public pages. Reports failures and how long people waited.

This runs on the local copy, on one small server, so the times only show whether anything breaks or blocks
when many people arrive together, not how fast the live site will be.

    python3 scripts/aiadn-load-rehearsal.py
"""
import importlib.util
import os
import statistics
import sys
import time
from concurrent.futures import ThreadPoolExecutor

HERE = os.path.dirname(os.path.abspath(__file__))
spec = importlib.util.spec_from_file_location("s11", os.path.join(HERE, "aiadn-e2e-slice11.py"))
s11 = importlib.util.module_from_spec(spec)
spec.loader.exec_module(s11)
s6, s3, s2, e2e = s11.s6, s11.s3, s11.s2, s11.e2e
s4 = s6.s4
SITE, Browser = e2e.SITE, e2e.Browser
sql, RUN = s3.sql, s3.RUN
U = RUN.upper()


def timed(fn, *a):
    t = time.time()
    try:
        ok, detail = fn(*a)
    except Exception as e:  # noqa: BLE001
        ok, detail = False, str(e)[:80]
    return ok, time.time() - t, detail


def student(school, pin, i):
    st, html = s4.student_in(school, pin)
    if "Which year are you in?" not in html:
        return False, "did not reach the survey"
    s, done, _ = s4.submit_survey(st, html, **s4.answers({"safe": 1 + i % 3, "smart": 1, "creative": 2, "responsible": 3, "future": 1}))
    return ("Thank you" in done), "survey not saved" if "Thank you" not in done else ""


def page(url, needle):
    s, html, _ = Browser().get(url)
    return (s == 200 and needle in html), f"{s}"


def percentile(values, p):
    values = sorted(values)
    return values[min(len(values) - 1, int(len(values) * p))]


def main():
    e2e.clear_rate_limits()
    A = s2.make_school("la", f"Load Primary {U}", "LS6 2AB")
    B = s2.make_school("lb", f"Load Academy {U}", "M1 1AA")
    pinA, _ = s4.start_pin(A)
    pinB, _ = s4.start_pin(B)
    jobs = []
    for i in range(30):
        jobs.append(("student", student, (A, pinA, i)))
        jobs.append(("student", student, (B, pinB, i)))
    for i in range(60):
        jobs.append(("public page", page, (SITE + "/conversation/join/", "school code")))
    for i in range(30):
        jobs.append(("homepage", page, (SITE + "/", "National AI Conversation 2027")))
    t0 = time.time()
    with ThreadPoolExecutor(max_workers=40) as ex:
        futs = [(kind, ex.submit(timed, fn, *args)) for kind, fn, args in jobs]
        results = [(k, f.result()) for k, f in futs]
    wall = time.time() - t0
    bad = 0
    print(f"{len(jobs)} requests at once, finished in {wall:.1f}s\n")
    for kind in ("student", "public page", "homepage"):
        rows = [r for k, r in results if k == kind]
        fails = [r for r in rows if not r[0]]
        times = [r[1] for r in rows]
        bad += len(fails)
        print(f"  {kind:12} {len(rows):3} requests   failed {len(fails):2}   median {statistics.median(times):5.2f}s   95th {percentile(times, .95):5.2f}s   slowest {max(times):5.2f}s")
        for r in fails[:3]:
            print(f"      - {r[2]}")
    saved = int(sql(f"SELECT COUNT(*) FROM wp_aiadn_voice WHERE school_id IN (SELECT id FROM wp_aiadn_schools WHERE code IN ('{A['code']}','{B['code']}'))"))
    print(f"\n  answers saved: {saved} of 60")
    if saved != 60:
        bad += 1
    ok_lim = "Please wait" not in Browser().get(SITE + "/conversation/join/")[1]
    print(f"  the front door still answers after the rush: {'yes' if ok_lim else 'NO, it is blocking'}")
    bad += 0 if ok_lim else 1
    print(f"\n{'PASS' if not bad else 'FAIL'}: {bad} problem(s)")
    sys.exit(1 if bad else 0)


if __name__ == "__main__":
    main()
