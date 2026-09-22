#!/usr/bin/env python3
"""Make a standalone brand SVG carry its own type.

The SVGs under assets/images/polygon-shapes/ and assets/brand/aiad27/ set
font-family 'AIAD Sans', which is the theme's name for the Uncut Sans woff2s in
assets/fonts/aiad27/. That name only resolves where the theme's CSS has already
run. Opened on their own, dropped into a deck, or loaded through <img src>, the
files fall all the way through to Arial -- which is not the brand face, and is
wider, so the type also stops fitting the tile it was set into.

So the font travels with the file: each SVG gets a <defs><style> carrying an
@font-face per weight it uses, with a subset of the face -- only the characters
that SVG actually sets -- inlined as a data URI. A polygon needs about thirty
glyphs, so this costs a few KB rather than the 39KB of a whole face, and the
browser still does the layout, so kerning is identical to today's rendering.

Weights are declared to match assets/css/editor-style.css (400 / 600 / 700-900)
rather than the woff2 filenames, so a weight resolves to the same face whether
the SVG is standalone or inside a theme page.

Run after editing any text in those SVGs:

    python3 scripts/embed-brand-font.py [file ...]

Needs fonttools with woff2 support (`pip install 'fonttools[woff]'`); it is a
build-time dependency only, nothing ships it.
"""
import base64
import io
import pathlib
import re
import sys

try:
    from fontTools.subset import Subsetter
    from fontTools.ttLib import TTFont
except ImportError:  # pragma: no cover - dependency hint
    sys.exit("fonttools missing. pip install 'fonttools[woff]'")

THEME = pathlib.Path(__file__).resolve().parent.parent
FONTS = THEME / "assets/fonts/aiad27"

# CSS weight -> the face the theme maps it to. A face covers the weights listed.
FACES = [
    ("UncutSans-Regular.woff2", "400", (400,)),
    ("UncutSans-Semibold.woff2", "600", (600,)),
    ("UncutSans-Bold.woff2", "700 900", (700, 800, 900)),
]

DEFAULTS = [
    THEME / "assets/images/polygon-shapes",
    THEME / "assets/brand/aiad27/aiad27-mark.svg",
]

GENERATED = re.compile(r"\n?  <defs>\n    <style>.*?</style>\n  </defs>\n", re.S)
TEXT_EL = re.compile(r"<text\b([^>]*)>(.*?)</text>", re.S)


def used(svg):
    """Characters set in the SVG, grouped by the weight each run is drawn at."""
    by_weight = {}
    for attrs, body in TEXT_EL.findall(svg):
        m = re.search(r'font-weight="(\d+)"', attrs)
        if not m:
            # Inherited from an ancestor <g>; take the nearest one declared above.
            before = svg[: svg.index(attrs)]
            outer = re.findall(r'font-weight="(\d+)"', before)
            weight = int(outer[-1]) if outer else 400
        else:
            weight = int(m.group(1))
        by_weight.setdefault(weight, set()).update(re.sub(r"\s+", " ", body).strip())
    return by_weight


def subset(path, chars):
    font = TTFont(FONTS / path)
    sub = Subsetter()
    sub.populate(text="".join(sorted(chars)))
    sub.subset(font)
    font.flavor = "woff2"
    buf = io.BytesIO()
    font.save(buf)
    return base64.b64encode(buf.getvalue()).decode("ascii")


def embed(svg_path):
    svg = GENERATED.sub("\n", svg_path.read_text())
    by_weight = used(svg)
    if not by_weight:
        return None

    rules = []
    for filename, css_weight, covered in FACES:
        chars = set()
        for weight, cs in by_weight.items():
            if weight in covered:
                chars |= cs
        if not chars:
            continue
        rules.append(
            "      @font-face { font-family: 'AIAD Sans'; font-style: normal;\n"
            f"        font-weight: {css_weight};\n"
            f"        src: url(data:font/woff2;base64,{subset(filename, chars)}) format('woff2'); }}"
        )

    defs = (
        "  <defs>\n    <style>\n"
        "      /* Subset of assets/fonts/aiad27, inlined by scripts/embed-brand-font.py\n"
        "         so this file renders in the brand face on its own. Do not hand-edit:\n"
        "         change the type above, then re-run the script. */\n"
        + "\n".join(rules)
        + "\n    </style>\n  </defs>\n"
    )

    close = svg.index(">", svg.index("<svg")) + 1
    out = svg[:close] + "\n" + defs + svg[close:].lstrip("\n")
    svg_path.write_text(out)
    return sorted(by_weight)


targets = [pathlib.Path(a).resolve() for a in sys.argv[1:]] or DEFAULTS
files = []
for t in targets:
    files.extend(sorted(t.glob("*.svg")) if t.is_dir() else [t])

for f in files:
    weights = embed(f)
    kb = f.stat().st_size / 1024
    print(f"{f.relative_to(THEME)}  weights {weights}  {kb:.1f}KB")
