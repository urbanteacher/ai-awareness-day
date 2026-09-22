#!/usr/bin/env python3
"""Draw the AiAd27 school display board as one graphic.

The Blueprint tab used to build the board out of HTML cards. A grid of cards
reflows, so it always ended up reading as a web page rather than as a board on
a wall. This draws the board as a fixed composition instead: a framed ink
backing, the lockup in the middle, and cards pinned around it. A school can see
what to make, and can download and print it.

It follows the AiAd27 style guide (SlideForge docs/aiad27-style-guide.md):

  * The three grounds map onto the board. The ink backing is the quiet ground
    and carries the title. The cream cards are the working ground: lists,
    photos, QR codes. The strand cards are the loud ground. Loud never touches
    loud, so no strand card is placed next to another.
  * The chamfer is used on strand panels only, cut at 20.8% of the shorter side
    on the top-right and bottom-left corners. Paper cards stay square.
  * Contrast follows the guide's table: black on a strand ground, cream on ink,
    black (or a strand's deep shade) on cream. Hierarchy comes from size and
    weight, not colour.
  * The lockup is the reverse file's own geometry under one transform. It is
    scaled, never rebuilt.

Text is wrapped with the real advance widths of AIAD Sans, and the script
stops if any block would overflow its card, so wording can change without
anything silently spilling out.

    python3 scripts/build-display-board.py
    python3 scripts/embed-brand-font.py assets/images/display-board/aiad27-display-board.svg

Needs fonttools with woff2 support, and qrcode:
pip install 'fonttools[woff]' qrcode. Both are build-time only.
"""
import html
import pathlib
import re
import sys

import qrcode
from fontTools.ttLib import TTFont

THEME = pathlib.Path(__file__).resolve().parent.parent
FONTS = THEME / "assets/fonts/aiad27"
BRAND = THEME / "assets/brand/aiad27"
OUT = THEME / "assets/images/display-board/aiad27-display-board.svg"

STACK = "'AIAD Sans', Arial, sans-serif"
INK, CREAM, DIM, RULE, CARD, WHITE = "#231F20", "#F6F4ED", "#54504E", "#C9C6BE", "#EAE7DF", "#FFFFFF"
BRIGHT = {"safe": "#00BEDD", "smart": "#FF7038", "creative": "#AC91FF", "responsible": "#63DF93", "future": "#FA83EB"}
DEEP = {"safe": "#006A7D", "smart": "#A7350B", "creative": "#6441B8", "responsible": "#176E3B", "future": "#983488"}

# Copy comes from the site's own blueprint (template-parts/front-page/section-toolkit.php)
# and the decks, so the board and the website say the same thing.
STRANDS = {
    "safe": ("Would you tell an AI your secret?",
             "Start with what should stay private: trust, sharing and the data AI holds about you."),
    "smart": ("What happens when AI acts for you?",
              "Decide what an AI must always ask about before it acts."),
    "creative": ("Who really made it?",
                 "Own what you make with AI: authorship, attribution and honest creative work."),
    "responsible": ("Should AI decide?",
                    "Keep human judgement in the moments that matter."),
    "future": ("What skills must stay human?",
               "Name the skills worth keeping human, and practise them on purpose."),
}
EVENT_DATE = "Friday 4 June 2027"     # aiad_event_date_ymd on the live site: 2027-06-04
RESOURCES_URL = "https://aiawarenessday.co.uk/resources/"

# Bounds of each icon's path in its 24-unit box, measured with getBBox(), so an
# icon is sized and centred by its own ink rather than by its canvas.
ICON_BBOX = {
    "safe": (3, 2, 18, 20), "smart": (4.918, 2, 14.163, 20.6), "creative": (2, 1, 20, 20),
    "responsible": (1.4, 2, 21.2, 20), "future": (3, 1, 21, 20),
}

W, H = 2400, 1600


# --- type ---------------------------------------------------------------

class Face:
    def __init__(self, file):
        f = TTFont(FONTS / file)
        self.upm = f["head"].unitsPerEm
        self.cmap = f.getBestCmap()
        self.hmtx = f["hmtx"].metrics

    def width(self, s, size, track=0.0):
        units = 0
        for ch in s:
            g = self.cmap.get(ord(ch))
            if g is None:
                sys.exit(f"AIAD Sans has no glyph for {ch!r} in {s!r}")
            units += self.hmtx[g][0]
        return units * size / self.upm + track * size * max(len(s) - 1, 0)


FACE = {400: Face("UncutSans-Regular.woff2"), 600: Face("UncutSans-Semibold.woff2"),
        700: Face("UncutSans-Bold.woff2")}


def wrap(s, size, weight, width, track=0.0):
    lines, line = [], ""
    for word in s.split():
        trial = f"{line} {word}".strip()
        # 3% headroom: kerning is not applied here, and the browser applies it.
        if FACE[weight].width(trial, size, track) * 1.03 <= width or not line:
            line = trial
        else:
            lines.append(line)
            line = word
    lines.append(line)
    return lines


def text(x, y, s, size, weight, fill, anchor="start", track=0.0, opacity=None):
    ls = f' letter-spacing="{track * size:.2f}"' if track else ""
    an = f' text-anchor="{anchor}"' if anchor != "start" else ""
    op = f' opacity="{opacity}"' if opacity is not None else ""
    return (f'<text x="{x:.1f}" y="{y:.1f}" font-family="{STACK}" font-size="{size}" '
            f'font-weight="{weight}" fill="{fill}"{ls}{an}{op}>{html.escape(s, quote=False)}</text>')


def para(x, y, s, size, weight, fill, width, lead, track=0.0, anchor="start"):
    """A wrapped block whose first baseline is y. Returns (svg, last baseline)."""
    lines = wrap(s, size, weight, width, track)
    out = [text(x, y + i * lead, ln, size, weight, fill, anchor, track) for i, ln in enumerate(lines)]
    return "\n".join(out), y + (len(lines) - 1) * lead


def fits(name, bottom, limit):
    if bottom > limit:
        sys.exit(f"{name}: content reaches y={bottom:.0f}, past its card at {limit:.0f}. Shorten the copy.")


# --- marks --------------------------------------------------------------

def icon_d(slug):
    return re.search(r'<path d="([^"]+)"', (BRAND / f"icon-{slug}.svg").read_text()).group(1)


def icon(slug, cx, cy, height, fill, opacity=None):
    x, y, w, h = ICON_BBOX[slug]
    k = height / h
    op = f' opacity="{opacity}"' if opacity is not None else ""
    return (f'<path fill="{fill}"{op} transform="translate({cx - (x + w / 2) * k:.2f} '
            f'{cy - (y + h / 2) * k:.2f}) scale({k:.4f})" d="{icon_d(slug)}"/>')


def chamfer(x, y, w, h):
    c = round(min(w, h) * 0.208)       # the panel cut: 100/480 in shape-chamfer-panel.svg
    return f"M{x},{y} H{x + w - c} L{x + w},{y + c} V{y + h} H{x + c} L{x},{y + h - c} Z", c


def pin(cx, cy):
    return (f'<circle cx="{cx}" cy="{cy + 3}" r="12" fill="#000" opacity="0.35"/>'
            f'<circle cx="{cx}" cy="{cy}" r="12" fill="{INK}"/>'
            f'<circle cx="{cx - 4}" cy="{cy - 4}" r="4" fill="{DIM}"/>')


def lockup(cx, top, ink_width):
    """The reverse lockup's own elements, scaled so its visible ink is ink_width wide.

    Its type is anchored at x=300 and the ink runs from x=76.9 (measured for the
    LinkedIn cover card), so the ink is centred on cx rather than the artboard.
    """
    src = (BRAND / "aiad27-lockup-reverse.svg").read_text()
    body = src[src.index(">", src.index("<svg")) + 1: src.rindex("</svg>")].strip()
    body = body.replace("Inter, Helvetica, Arial, sans-serif", STACK.replace("'", "&apos;"))
    s = ink_width / (300 - 76.9)
    tx = cx - (76.9 + 300) / 2 * s
    ty = top - 13 * s                  # cap top of the 20px line sits at y=13
    return f'<g transform="translate({tx:.2f} {ty:.2f}) scale({s:.4f})">{body}</g>', top + 40 * s


def qr_modules(url, x, y, size):
    q = qrcode.QRCode(error_correction=qrcode.constants.ERROR_CORRECT_M, border=4)
    q.add_data(url)
    q.make(fit=True)
    m = q.get_matrix()                 # rows of columns, quiet zone included
    n = len(m)
    u = size / n
    rects = [f'<rect x="{x}" y="{y}" width="{size}" height="{size}" fill="{WHITE}"/>']
    for r, row in enumerate(m):
        c = 0
        while c < n:                   # one rect per horizontal run keeps the file small
            if row[c]:
                start = c
                while c < n and row[c]:
                    c += 1
                rects.append(f'<rect x="{x + start * u:.3f}" y="{y + r * u:.3f}" '
                             f'width="{(c - start) * u + 0.02:.3f}" height="{u + 0.02:.3f}" fill="{INK}"/>')
            else:
                c += 1
    return "\n".join(rects)


# --- cards --------------------------------------------------------------

def pinned(x, y, w, h, rot, body):
    cx, cy = x + w / 2, y + h / 2
    return f'<g transform="rotate({rot} {cx:.1f} {cy:.1f})" filter="url(#lift)">\n{body}\n{pin(cx, y + 26)}\n</g>'


def strand_card(slug, x, y, w, h, rot):
    question, line = STRANDS[slug]
    d, c = chamfer(x, y, w, h)
    cid = f"card-{slug}"
    out = [
        f'<clipPath id="{cid}"><path d="{d}"/></clipPath>',
        f'<path d="{d}" fill="{BRIGHT[slug]}"/>',
        # The strand mark as a background: the deep shade at 30% over the bright,
        # the treatment the polygon shapes use, bleeding off the card's edge.
        f'<g clip-path="url(#{cid})">{icon(slug, x + w * 0.74, y + h * 0.66, h * 0.86, DEEP[slug], 0.3)}</g>',
        icon(slug, x + 62, y + 76, 40, INK),
        text(x + 98, y + 88, slug.upper(), 26, 600, INK, track=0.14),
    ]
    q, qb = para(x + 44, y + 170, question, 52, 700, INK, w - 96, 56, track=-0.035)
    out.append(q)
    b, bb = para(x + 44, qb + 56, line, 26, 400, INK, w - 110, 35)
    out.append(b)
    fits(slug, bb + 8, y + h - c * 0.55)   # stay clear of the bottom-left cut
    return pinned(x, y, w, h, rot, "\n".join(out))


def paper(x, y, w, h, label):
    return [f'<rect x="{x}" y="{y}" width="{w}" height="{h}" fill="{CREAM}"/>',
            text(x + 40, y + 78, label, 24, 600, INK, track=0.14),
            f'<rect x="{x + 40}" y="{y + 96}" width="{w - 80}" height="2" fill="{RULE}"/>']


def questions_card(x, y, w, h, rot):
    out = paper(x, y, w, h, "THIS WEEK’S QUESTIONS")
    row = y + 170
    for i, slug in enumerate(["safe", "smart", "creative"]):
        out.append(text(x + 40, row, f"0{i + 1}", 40, 700, DEEP[slug], track=-0.02))
        q, qb = para(x + 120, row - 4, STRANDS[slug][0], 32, 700, INK, w - 170, 38, track=-0.02)
        out.append(q)
        row = qb + 72
    fits("questions", row - 72 + 12, y + h - 24)
    return pinned(x, y, w, h, rot, "\n".join(out))


def responses_card(x, y, w, h, rot):
    out = paper(x, y, w, h, "STUDENT RESPONSES")
    notes = [("safe", "“I’d tell a person first.”", -4),
             ("smart", "“It can draft. I decide.”", 3),
             ("creative", "“I made it. AI helped.”", -2)]
    s = 138
    for i, (slug, quote, r) in enumerate(notes):
        nx, ny = x + 40 + i * 150, y + 128 + (12 if i == 1 else 0)
        body = [f'<rect x="{nx}" y="{ny}" width="{s}" height="{s}" fill="{BRIGHT[slug]}"/>']
        lines = wrap(quote, 24, 600, s - 26)
        fits(f"sticky {i + 1}", ny + 40 + (len(lines) - 1) * 29, ny + s - 12)
        body += [text(nx + 13, ny + 40 + j * 29, ln, 24, 600, INK) for j, ln in enumerate(lines)]
        out.append(f'<g transform="rotate({r} {nx + s / 2} {ny + s / 2})" filter="url(#lift-small)">'
                   + "".join(body) + "</g>")
    by = y + 312
    out.append(f'<rect x="{x + 40}" y="{by}" width="{w - 80}" height="{h - (by - y) - 36}" fill="none" '
               f'stroke="{DIM}" stroke-width="2" stroke-dasharray="10 8"/>')
    out.append(text(x + w / 2, by + (h - (by - y) - 36) / 2 + 9, "Add yours on a sticky note", 24, 400, DIM, "middle"))
    return pinned(x, y, w, h, rot, "\n".join(out))


def qr_card(x, y, w, h, rot):
    out = paper(x, y, w, h, "QR CHALLENGES")
    out.append(text(x + 40, y + 150, "Scan and investigate.", 28, 400, INK))
    t = 170
    lx, rx, ty = x + 40, x + w - 40 - t, y + 178
    out.append(qr_modules(RESOURCES_URL, lx, ty, t))
    out.append(f'<rect x="{rx}" y="{ty}" width="{t}" height="{t}" fill="none" stroke="{DIM}" '
               f'stroke-width="2" stroke-dasharray="10 8"/>')
    out.append(text(rx + t / 2, ty + t / 2 + 8, "Your QR code", 24, 400, DIM, "middle"))
    cap = ty + t + 36
    out.append(text(lx, cap, "All the resources", 24, 600, INK))
    out.append(text(rx, cap, "Your school’s", 24, 600, INK))
    out.append(text(rx, cap + 29, "AI policy", 24, 600, INK))
    fits("qr", cap + 29, y + h - 20)
    return pinned(x, y, w, h, rot, "\n".join(out))


def leaders_card(x, y, w, h, rot):
    out = paper(x, y, w, h, "AI LEADERS & INNOVATORS")
    pw, ph = 164, 196
    for i, r in enumerate([-3, 1.5, -1.5]):
        px, py = x + 38 + i * 196, y + 124
        head = f'<circle cx="{px + pw / 2}" cy="{py + 62}" r="26" fill="{RULE}"/>'
        body = (f'<path d="M{px + 30},{py + 150} C{px + 30},{py + 104} {px + pw - 30},{py + 104} '
                f'{px + pw - 30},{py + 150} Z" fill="{RULE}"/>')
        out.append(f'<g transform="rotate({r} {px + pw / 2} {py + ph / 2})" filter="url(#lift-small)">'
                   f'<rect x="{px}" y="{py}" width="{pw}" height="{ph}" fill="{WHITE}"/>'
                   f'<rect x="{px + 12}" y="{py + 12}" width="{pw - 24}" height="{ph - 58}" fill="{CARD}"/>'
                   f'{head}{body}{text(px + pw / 2, py + ph - 15, "Add photo", 22, 600, DIM, "middle")}</g>')
    c, cb = para(x + 40, y + 374, "Challenge: find three living people working in AI and add them here.",
                 24, 400, INK, w - 80, 32)
    out.append(c)
    fits("leaders", cb, y + h - 16)
    return pinned(x, y, w, h, rot, "\n".join(out))


def spotlight_card(x, y, w, h, rot):
    out = paper(x, y, w, h, "STUDENT SPOTLIGHT")
    row = y + 146
    for _ in range(3):
        out.append(f'<circle cx="{x + 70}" cy="{row - 8}" r="30" fill="{CARD}"/>')
        out.append(text(x + 118, row - 10, "Student name", 26, 700, INK))
        b, bb = para(x + 118, row + 22, "Add their work or project here", 22, 400, DIM, w - 150, 27)
        out.append(b)
        row = bb + 58
    fits("spotlight", row - 58 + 6, y + h - 16)
    return pinned(x, y, w, h, rot, "\n".join(out))


# --- the board ------------------------------------------------------------

def build():
    frame, trim = 20, 28
    inner = frame + trim
    # Five-colour trim: the border roll of a real board, in the strands' brights,
    # which the guide allows as surfaces and shapes.
    stripe = 36
    stripes = "".join(f'<rect x="{i * stripe}" y="0" width="{stripe}" height="{5 * stripe}" fill="{BRIGHT[s]}"/>'
                      for i, s in enumerate(BRIGHT))
    defs = f"""<defs>
  <pattern id="trim" width="{5 * stripe}" height="{5 * stripe}" patternUnits="userSpaceOnUse"
           patternTransform="rotate(45)">{stripes}</pattern>
  <filter id="lift" x="-10%" y="-10%" width="120%" height="125%">
    <feGaussianBlur in="SourceAlpha" stdDeviation="10"/><feOffset dy="12" result="b"/>
    <feComponentTransfer><feFuncA type="linear" slope="0.55"/></feComponentTransfer>
    <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
  </filter>
  <filter id="lift-small" x="-15%" y="-15%" width="130%" height="135%">
    <feGaussianBlur in="SourceAlpha" stdDeviation="4"/><feOffset dy="5" result="b"/>
    <feComponentTransfer><feFuncA type="linear" slope="0.3"/></feComponentTransfer>
    <feMerge><feMergeNode/><feMergeNode in="SourceGraphic"/></feMerge>
  </filter>
</defs>"""

    parts = [
        f'<rect width="{W}" height="{H}" fill="{RULE}"/>',                      # aluminium frame
        f'<path fill-rule="evenodd" fill="url(#trim)" d="M{frame},{frame} H{W - frame} V{H - frame} H{frame} Z '
        f'M{inner},{inner} V{H - inner} H{W - inner} V{inner} Z"/>',
        f'<rect x="{inner}" y="{inner}" width="{W - 2 * inner}" height="{H - 2 * inner}" fill="{INK}"/>',
    ]

    # Three columns on a three-row grid, 44 between rows.
    L, C, R = (96, 520), (680, 1040), (1784, 520)
    rows = [(96, 440), (580, 440), (1064, 440)]

    # Title: straight on the ink backing, no card. The lockup is set from its
    # right edge (every line ends on one vertical), so the rest of the block
    # follows it: a letterhead line above, logo slot left and date right, and the
    # campaign line ending on the lockup's right edge rather than centred under it.
    cx, ink = C[0] + C[1] / 2, 900
    left, right = cx - ink / 2, cx + ink / 2
    ly, lw, lh = 100, 176, 96
    parts.append(f'<rect x="{left}" y="{ly}" width="{lw}" height="{lh}" fill="none" stroke="{CREAM}" '
                 f'stroke-opacity="0.55" stroke-width="2" stroke-dasharray="8 7"/>')
    parts.append(text(left + lw / 2, ly + 43, "YOUR SCHOOL", 17, 600, CREAM, "middle", 0.14, 0.75))
    parts.append(text(left + lw / 2, ly + 67, "LOGO", 17, 600, CREAM, "middle", 0.14, 0.75))
    fits("logo label", left + lw / 2 + FACE[600].width("YOUR SCHOOL", 17, 0.14) / 2, left + lw - 12)
    parts.append(text(right, ly + 58, EVENT_DATE, 32, 600, CREAM, "end", 0.02))
    lk, lbottom = lockup(cx, ly + lh + 40, ink)
    parts.append(lk)
    parts.append(text(right, lbottom + 78, "Your AI. Your choices.", 60, 700, CREAM, "end", -0.035))
    fits("title", lbottom + 78, rows[0][0] + rows[0][1] - 16)

    # Reading order runs Safe, Smart, Creative, Responsible, Future, and no strand
    # card shares an edge with another.
    parts.append(strand_card("safe", L[0], rows[0][0], L[1], rows[0][1], -1.2))
    parts.append(strand_card("smart", R[0], rows[0][0], R[1], rows[0][1], 1.1))
    parts.append(questions_card(L[0], rows[1][0], L[1], rows[1][1], 0.7))
    parts.append(strand_card("creative", C[0], rows[1][0], 500, rows[1][1], -0.8))
    parts.append(responses_card(C[0] + 540, rows[1][0], 500, rows[1][1], 0.9))
    parts.append(qr_card(R[0], rows[1][0], R[1], rows[1][1], -0.6))
    parts.append(strand_card("responsible", L[0], rows[2][0], L[1], rows[2][1], 0.8))
    parts.append(leaders_card(C[0], rows[2][0], 620, rows[2][1], -0.5))
    parts.append(spotlight_card(C[0] + 660, rows[2][0], 380, rows[2][1], 0.6))
    parts.append(strand_card("future", R[0], rows[2][0], R[1], rows[2][1], -1.0))

    svg = (f'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {W} {H}" width="{W}" height="{H}" '
           f'role="img" aria-label="Example AI Awareness Day 2027 school display board">\n'
           f'<title>AI Awareness Day 2027 school display board</title>\n'
           + defs + "\n" + "\n".join(parts) + "\n</svg>\n")
    OUT.parent.mkdir(parents=True, exist_ok=True)
    OUT.write_text(svg)
    print(f"wrote {OUT.relative_to(THEME)}  {len(svg) / 1024:.1f}KB")


if __name__ == "__main__":
    build()
