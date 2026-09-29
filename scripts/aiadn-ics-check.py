#!/usr/bin/env python3
"""
Parses calendar invites with a real iCalendar library, so the tests do not rely on string matching.
Reads a JSON list of .ics texts on stdin; prints a JSON list of what a calendar would understand.

    echo '["BEGIN:VCALENDAR..."]' | uv run --with icalendar python scripts/aiadn-ics-check.py
"""
import json
import sys

from icalendar import Calendar

out = []
for text in json.load(sys.stdin):
    try:
        cal = Calendar.from_ical(text)
        ev = [c for c in cal.walk() if c.name == "VEVENT"][0]
        alarms = [c for c in ev.walk() if c.name == "VALARM"]
        out.append({
            "ok": True,
            "version": str(cal.get("VERSION")),
            "method": str(cal.get("METHOD")),
            "uid": str(ev.get("UID")),
            "start": ev.decoded("DTSTART").isoformat(),
            "end": ev.decoded("DTEND").isoformat(),
            "sequence": int(ev.get("SEQUENCE", 0)),
            "status": str(ev.get("STATUS")),
            "summary": str(ev.get("SUMMARY")),
            "description": str(ev.get("DESCRIPTION")),
            "location": str(ev.get("LOCATION", "")),
            "url": str(ev.get("URL", "")),
            "alarms": len(alarms),
            "crlf": "\r\n" in text and "\n" not in text.replace("\r\n", ""),
            "max_line": max(len(l.encode()) for l in text.split("\r\n")),
        })
    except Exception as e:  # noqa
        out.append({"ok": False, "error": str(e)})
print(json.dumps(out))
