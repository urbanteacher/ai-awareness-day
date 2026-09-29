#!/usr/bin/env python3
"""
Proves the plugin's QR generator: makes codes for many texts (all versions 1-10) and decodes each one
with OpenCV's QR reader. Needs docker (to run PHP) and uv (to get OpenCV).

    uv run --with opencv-python-headless --with numpy python scripts/aiadn-qr-check.py
"""
import base64
import json
import os
import random
import string
import subprocess
import sys

import cv2
import numpy as np

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
random.seed(7)

texts = [
    "http://localhost:8888/conversation/join/?c=SCH-ZP5M8",
    "https://aiawarenessday.co.uk/conversation/join/?c=SCH-3F9A2",
    "https://aiawarenessday.co.uk/conversation/check/?ref=AIAD-DS-8Q4KM",
    "A", "AI Awareness Day 2027", "SCH-3F9A2",
]
for n in (10, 20, 30, 40, 50, 62, 63, 64, 70, 84, 86, 87, 100, 120, 150, 180, 200, 213):
    texts.append("".join(random.choices(string.ascii_letters + string.digits + "/:?=-._~", k=n)))
texts.append("Café – debate ✓")  # non-ASCII, encoded as UTF-8 bytes

payload = base64.b64encode(json.dumps(texts).encode()).decode()
php = r'''
define("ABSPATH", "/");
require "/w/plugins/aiad-debate-network/includes/class-aiadn-qr.php";
$texts = json_decode(base64_decode(file_get_contents("php://stdin")), true);
$out = array();
foreach ($texts as $t) { $out[] = AIADN_QR::matrix_text($t); }
echo json_encode($out);
'''
res = subprocess.run(["docker", "run", "--rm", "-i", "-v", f"{ROOT}:/w", "php:8.2-cli", "php", "-r", php], input=payload, capture_output=True, text=True)
if res.returncode != 0:
    print(res.stderr or res.stdout)
    sys.exit(1)
matrices = json.loads(res.stdout)

det = cv2.QRCodeDetector()
bad = 0
for text, m in zip(texts, matrices):
    rows = m.split("\n")
    n = len(rows)
    arr = np.array([[0 if c == "1" else 255 for c in r] for r in rows], dtype=np.uint8)
    img = np.pad(arr, 4, constant_values=255)
    img = cv2.resize(img, None, fx=10, fy=10, interpolation=cv2.INTER_NEAREST)
    got, points, _ = det.detectAndDecode(img)
    version = (n - 17) // 4
    ok = got == text
    bad += 0 if ok else 1
    print(("PASS " if ok else "FAIL ") + f"v{version:<2} {len(text.encode()):>3} bytes  {text[:40]!r}" + ("" if ok else f"   decoded {got!r}"))
print(f"\n{len(texts) - bad} of {len(texts)} decoded correctly")
sys.exit(1 if bad else 0)
