"""The Embedding picture (assets/images/literacy-table/em.svg), laid out by calculation rather than by eye.

    python3 tools/literacy-arrows/embedding.py [--preview DIR]

- Every label sits the same distance from its dot (dot radius + 10).
- Each cluster's dashed circle is the smallest circle around its dots AND their labels (Welzl's algorithm), plus
  padding, and both circles use the larger of the two radii, so neither cluster looks more spread out.
- Distance carries the meaning: kitten sits nearest cat, dog a little further; the vehicles are far away and
  "happy" sits alone.
- No axes: embedding dimensions mean nothing on their own, so the pale panel is the space.
- The vector label is joined to the cat dot by a thin leader line, and the cat dot is ringed.
- The top-left corner (100 x 95) stays clear for the symbol badge.
"""
import math
import random
import sys
from pathlib import Path

HERE = Path(__file__).resolve().parent
OUT = HERE.parent.parent / 'assets/images/literacy-table/em.svg'

DOT = 8                     # dot radius
OFFSET = DOT + 10           # dot centre to the start of its label (clears the ring round cat too)
CHAR = 8.6                  # width of a 16px semibold letter, roughly
CAP, DESC = 12, 4           # label height above and below its baseline
PAD = 10                    # space inside each circle around the dots and labels
BADGE = (100, 95)           # top-left corner kept clear

ANIMALS = [('cat', 180, 124), ('kitten', 192, 150), ('dog', 214, 84)]
VEHICLES = [('bus', 42, 238), ('car', 86, 222), ('train', 56, 276)]
HAPPY = ('happy', 214, 238)


def label_box(name, x, y):
    """The label's box: it starts OFFSET right of the dot, its baseline 5 below the dot's centre."""
    tx, base = x + OFFSET, y + 5
    return (tx, base - CAP, len(name) * CHAR, CAP + DESC)


def outline_points(items):
    """Points that must be inside the circle: around each dot and the corners of each label."""
    pts = []
    for name, x, y in items:
        pts += [(x + DOT * math.cos(a), y + DOT * math.sin(a)) for a in [k * math.pi / 4 for k in range(8)]]
        bx, by, bw, bh = label_box(name, x, y)
        pts += [(bx, by), (bx + bw, by), (bx, by + bh), (bx + bw, by + bh)]
    return pts


def circle2(a, b):
    c = ((a[0] + b[0]) / 2, (a[1] + b[1]) / 2)
    return (c, math.dist(a, b) / 2)


def circle3(a, b, c):
    ax, ay = a
    bx, by = b
    cx, cy = c
    d = 2 * (ax * (by - cy) + bx * (cy - ay) + cx * (ay - by))
    if abs(d) < 1e-9:
        return max((circle2(p, q) for p, q in ((a, b), (a, c), (b, c))), key=lambda t: t[1])
    ux = ((ax * ax + ay * ay) * (by - cy) + (bx * bx + by * by) * (cy - ay) + (cx * cx + cy * cy) * (ay - by)) / d
    uy = ((ax * ax + ay * ay) * (cx - bx) + (bx * bx + by * by) * (ax - cx) + (cx * cx + cy * cy) * (bx - ax)) / d
    return ((ux, uy), math.dist((ux, uy), a))


def inside(c, p):
    return math.dist(c[0], p) <= c[1] + 1e-7


def welzl(points):
    """Smallest circle containing every point (iterative Welzl / Megiddo)."""
    pts = points[:]
    random.Random(7).shuffle(pts)
    c = (pts[0], 0)
    for i, p in enumerate(pts):
        if inside(c, p):
            continue
        c = (p, 0)
        for j in range(i):
            q = pts[j]
            if inside(c, q):
                continue
            c = circle2(p, q)
            for k in range(j):
                r = pts[k]
                if not inside(c, r):
                    c = circle3(p, q, r)
    return c


def fmt(v):
    v = round(v, 1)
    return str(int(v)) if v == int(v) else str(v)


def build():
    a_c, a_r = welzl(outline_points(ANIMALS))
    v_c, v_r = welzl(outline_points(VEHICLES))
    r = max(a_r, v_r) + PAD
    problems = []
    for name, (c, _) in (('animals', (a_c, a_r)), ('vehicles', (v_c, v_r))):
        if c[0] - r < 6 or c[0] + r > 294 or c[1] - r < 6 or c[1] + r > 324:
            problems.append(f'{name} circle runs off the picture')
        # The badge corner: the circle must not reach into the top-left 100 x 95.
        nx, ny = min(max(c[0], 0), BADGE[0]), min(max(c[1], 0), BADGE[1])
        if math.dist((nx, ny), c) < r:
            problems.append(f'{name} circle reaches into the badge corner')
    if math.dist(a_c, v_c) < 2 * r + 12:
        problems.append('the circles touch')

    def dot(name, x, y, cls='i-deep'):
        return (f'<circle class="{cls}" cx="{fmt(x)}" cy="{fmt(y)}" r="{DOT}"/>'
                f'<text class="t-s" x="{fmt(x + OFFSET)}" y="{fmt(y + 5)}">{name}</text>')

    # The vector label: bottom right, joined to the cat dot by a leader line.
    cat = ANIMALS[0]
    text = 'cat = [0.2, −0.7, …]'
    bw, bh = 140, 28
    bx, by = 294 - 8 - bw, 324 - 10 - bh
    if by + bh > 324:
        problems.append('vector label runs off the picture')
    # Leader: from the top of the label box straight up toward the cat dot, stopping at the ring.
    lx = min(max(cat[1], bx + 16), bx + bw - 16)
    ang = math.atan2(cat[2] - by, cat[1] - lx)
    ex, ey = cat[1] - (DOT + 5) * math.cos(ang), cat[2] - (DOT + 5) * math.sin(ang)
    box = (bx, by, bw, bh)
    for name, c in (('animal', a_c), ('vehicle', v_c)):
        nx, ny = min(max(c[0], bx), bx + bw), min(max(c[1], by), by + bh)
        if math.dist((nx, ny), c) < r + 6:
            problems.append(f'vector label touches the {name} circle')
    # The leader must not cross the vehicle circle.
    for t in [k / 20 for k in range(21)]:
        px, py = lx + (ex - lx) * t, by + (ey - by) * t
        if math.dist((px, py), v_c) < r + 4:
            problems.append('leader crosses the vehicle circle')
            break
    hx, hy = HAPPY[1], HAPPY[2]
    if abs(hy - (by + bh / 2)) < 30 and hx + OFFSET + len(HAPPY[0]) * CHAR > bx:
        problems.append('happy collides with the vector label')
    svg = f'''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 330" class="alpt-pic">
  <!-- Embedding: a map of meanings where similar words sit close together, so distance means similarity. Laid out by
       tools/literacy-arrows/embedding.py: circles fit their dots and labels, labels sit one fixed distance from their
       dots. Top-left 100x95 stays clear for the symbol badge. -->
  <rect class="i-bg" width="300" height="330"/>
  <circle class="i-paper" cx="{fmt(a_c[0])}" cy="{fmt(a_c[1])}" r="{fmt(r)}"/><circle class="i-deepline i-dash" cx="{fmt(a_c[0])}" cy="{fmt(a_c[1])}" r="{fmt(r)}"/>
  <circle class="i-paper" cx="{fmt(v_c[0])}" cy="{fmt(v_c[1])}" r="{fmt(r)}"/><circle class="i-deepline i-dash" cx="{fmt(v_c[0])}" cy="{fmt(v_c[1])}" r="{fmt(r)}"/>
  <path class="i-line" d="M{fmt(lx)} {fmt(by)} L{fmt(ex)} {fmt(ey)}" style="stroke-width:1.5"/>
  <circle class="i-deepline" cx="{fmt(cat[1])}" cy="{fmt(cat[2])}" r="{DOT + 4}" style="stroke-width:2"/>
  {''.join(dot(*it) for it in ANIMALS)}
  {''.join(dot(*it) for it in VEHICLES)}
  {dot(*HAPPY, cls='i-accent')}
  <rect class="i-paper" x="{fmt(bx)}" y="{fmt(by)}" width="{bw}" height="{bh}" rx="4"/><rect class="i-line" x="{fmt(bx)}" y="{fmt(by)}" width="{bw}" height="{bh}" rx="4"/>
  <text class="t-xs" x="{fmt(bx + bw / 2)}" y="{fmt(by + 19)}" text-anchor="middle">{text}</text>
</svg>
'''
    return svg, problems, (a_c, v_c, r)


if __name__ == '__main__':
    svg, problems, geo = build()
    for p in problems:
        print('PROBLEM:', p)
    print('circles', [tuple(round(v) for v in c) for c in geo[:2]], 'radius', round(geo[2]))
    OUT.write_text(svg)
    if '--preview' in sys.argv:
        sys.path.insert(0, str(HERE))
        from apply import preview
        print(preview('em', svg, sys.argv[sys.argv.index('--preview') + 1]))
    sys.exit(1 if problems else 0)
