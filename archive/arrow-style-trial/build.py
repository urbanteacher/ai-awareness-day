"""Arrow style trial: the element pictures' arrows, calculated from the boxes rather than typed-in coordinates.

Run: python3 build.py   (writes tr.svg, ag.svg, dt.svg and index.html in this folder, and prints any clashes)

The one rule: an arrow is defined only by the box and side it leaves from and the box and side it enters,
for example ('bot', 'right', 's1', 'left'). Never by coordinates. From that the code works out:

1. Where on each side the arrow sits (its "port"):
   - Sides that face each other and overlap get one shared line, so the arrow is straight.
   - Several arrows leaving one side are spread evenly along it, in the order of their targets.
   - A fan (fan=True) leaves from one shared point, level with the middle target when it can be, and every
     branch runs level for STEM before it curves, so the fork doesn't pinch.
   - Otherwise the middle of the side.
   - Facing sides that miss by SNAP or less are snapped straight, so near-misses don't make a tiny S-wobble.
   - A quarter-turn arrow (sides at right angles) gets at least TURN of room on the axis it arrives along:
     if the start's middle is too close, the start slides along its side, so the arrow doesn't turn hard
     right in front of the box it points at.
   - For the rare arrow that shouldn't use these, at= / to_at= give the point as a share of the edge (0 to 1),
     never a coordinate, so it stays right if the box moves or changes size.
2. The curve: both control points sit straight out from their edge, so the arrow leaves and arrives at right
   angles with no wobble. Each reaches 55% of the distance on its own axis (sides at right angles make a quarter
   ellipse; facing sides make an even S), so a control point never passes the other end and the curve can't bulge.
3. Both ends stop GAP short of the edge, so the head never touches the border. One gap for every arrow.
   A curved arrow stops curving END_RUN before its tip and finishes in a straight line longer than the head,
   so the line always meets the middle of the head's back, never one of its arms.
4. The SVG marker (orient="auto-start-reverse") turns the head to follow the line's real end.

Labels are placed from the curve too: at its middle, set off to one side.

Ends: end='arrow' (the default), 'cross' (stopped, blocked, not allowed), 'dot' or 'none'; start='dot' for a
joined end. Related things are a line with a dot at each end (start='dot', end='dot').
"""
import math
import re
from pathlib import Path

HERE = Path(__file__).parent
LIVE = HERE.parent.parent / 'assets/images/literacy-table'
GAP = 8         # space between an arrow's ends and the edges they meet
REACH = 0.55    # control points, sides at right angles: share of the distance on their own axis
FACING = 0.5    # control points, sides facing each other: share of the gap between them
INSET = 10      # ports keep this far from a side's corners
STEM = 6        # a fan's branches leave level for this far before they curve
HEAD = 12       # the arrowhead's length (the marker's chevron: 8 of its 10 units, drawn 15 wide)
END_RUN = HEAD + 4  # every curved arrow ends with a straight run this long, so the head sits on a straight line
SNAP = 6        # facing sides this close to lining up are drawn straight
TURN = 44 + END_RUN  # a quarter-turn arrow gets at least this much room on the axis it arrives along

NORMALS = {'top': (0, -1), 'bottom': (0, 1), 'left': (-1, 0), 'right': (1, 0)}
OPPOSITE = {'top': 'bottom', 'bottom': 'top', 'left': 'right', 'right': 'left'}


def side_span(box, side):
    """The run of a side along its own axis (x for top/bottom, y for left/right), inset from the corners."""
    x, y, w, h = box
    lo, hi = (x, x + w) if side in ('top', 'bottom') else (y, y + h)
    return lo + INSET, hi - INSET


def side_line(box, side):
    """The fixed coordinate of a side (y for top/bottom, x for left/right)."""
    x, y, w, h = box
    return {'top': y, 'bottom': y + h, 'left': x, 'right': x + w}[side]


def centre(box, side):
    lo, hi = side_span(box, side)
    return (lo + hi) / 2


def point_on(box, side, along):
    line = side_line(box, side)
    return (along, line) if side in ('top', 'bottom') else (line, along)


def ports(nodes, edges):
    """The position along each side for both ends of every edge."""
    start, end = {}, {}
    # Group edges by the side they leave from.
    groups = {}
    for i, e in enumerate(edges):
        groups.setdefault((e['from'], e['side']), []).append(i)
    for (name, side), idx in groups.items():
        box = nodes[name]
        lo, hi = side_span(box, side)
        horizontal = side in ('top', 'bottom')
        key = lambda i: centre(nodes[edges[i]['to']], edges[i]['to_side']) if (horizontal == (edges[i]['to_side'] in ('top', 'bottom'))) else side_line(nodes[edges[i]['to']], edges[i]['to_side'])
        idx = sorted(idx, key=key)
        if any(edges[i].get('fan') for i in idx):
            mid = key(idx[len(idx) // 2])
            p = mid if lo <= mid <= hi else (lo + hi) / 2
            for i in idx:
                start[i] = p
        elif len(idx) > 1:
            for n, i in enumerate(idx):
                start[i] = lo + (hi - lo) * (n + 1) / (len(idx) + 1)
        else:
            i = idx[0]
            e = edges[i]
            if e['to_side'] == OPPOSITE[side]:
                tlo, thi = side_span(nodes[e['to']], e['to_side'])
                a, b = max(lo, tlo), min(hi, thi)
                start[i] = (a + b) / 2 if a <= b else (lo + hi) / 2
            else:
                start[i] = (lo + hi) / 2
    for i, e in enumerate(edges):
        if 'at' in e:
            rlo, rhi = side_span(nodes[e['from']], e['side'])
            start[i] = rlo - INSET + (rhi - rlo + 2 * INSET) * e['at']
    for i, e in enumerate(edges):
        tlo, thi = side_span(nodes[e['to']], e['to_side'])
        facing = e['to_side'] == OPPOSITE[e['side']]
        if 'to_at' in e:
            end[i] = tlo - INSET + (thi - tlo + 2 * INSET) * e['to_at']
        elif facing and tlo - SNAP <= start[i] <= thi + SNAP:
            end[i] = start[i]  # facing and in reach (or within SNAP of it): straight
        else:
            end[i] = (tlo + thi) / 2
        # Quarter turns: make room on the arriving axis by sliding a lone start along its side.
        if not facing and 'at' not in e and not e.get('fan') and len(groups[(e['from'], e['side'])]) == 1:
            arrive_horizontal = e['to_side'] in ('left', 'right')
            if arrive_horizontal == (e['side'] in ('top', 'bottom')):
                tx, ty = point_on(nodes[e['to']], e['to_side'], end[i])
                target = tx + NORMALS[e['to_side']][0] * GAP if arrive_horizontal else ty + NORMALS[e['to_side']][1] * GAP
                run = target - start[i]
                if abs(run) < TURN:
                    lo, hi = side_span(nodes[e['from']], e['side'])
                    want = target - math.copysign(TURN, run if run else 1)
                    start[i] = min(max(want, lo), hi)
        # A near-miss the other way: the end is fixed but the start is free (a lone arrow), so snap the start.
        if facing and 'at' not in e and not e.get('fan') and abs(end[i] - start[i]) <= SNAP:
            start[i] = end[i]
    return start, end


def fmt(v):
    v = round(v, 1)
    return str(int(v)) if v == int(v) else str(v)


def geometry(nodes, e, s_along, e_along):
    """Start, end and the two control points of one edge."""
    anx, any_ = NORMALS[e['side']]
    bnx, bny = NORMALS[e['to_side']]
    ax, ay = point_on(nodes[e['from']], e['side'], s_along)
    bx, by = point_on(nodes[e['to']], e['to_side'], e_along)
    g = GAP
    p0 = (ax + anx * (g + (STEM if e.get('fan') else 0)), ay + any_ * (g + (STEM if e.get('fan') else 0)))
    tip = (bx + bnx * g, by + bny * g)
    facing = e['to_side'] == OPPOSITE[e['side']]
    aligned = facing and (abs(tip[0] - p0[0]) < 0.5 or abs(tip[1] - p0[1]) < 0.5)
    p3 = tip if aligned else (tip[0] + bnx * END_RUN, tip[1] + bny * END_RUN)
    if facing:
        gap = abs(p3[0] - p0[0]) if anx else abs(p3[1] - p0[1])
        r1 = r2 = FACING * gap
    else:
        dx, dy = abs(p3[0] - p0[0]), abs(p3[1] - p0[1])
        r1 = REACH * (dx if anx else dy)
        r2 = REACH * (dx if bnx else dy)
    p1 = (p0[0] + anx * r1, p0[1] + any_ * r1)
    p2 = (p3[0] + bnx * r2, p3[1] + bny * r2)
    return p0, p1, p2, p3, tip


def bezier(p0, p1, p2, p3, t):
    u = 1 - t
    pt = tuple(u**3 * a + 3 * u * u * t * b + 3 * u * t * t * c + t**3 * d for a, b, c, d in zip(p0, p1, p2, p3))
    tan = tuple(3 * u * u * (b - a) + 6 * u * t * (c - b) + 3 * t * t * (d - c) for a, b, c, d in zip(p0, p1, p2, p3))
    return pt, tan


def draw(sym, nodes, edges):
    """The arrows, their labels and (for the page) a control-point overlay."""
    s_ports, e_ports = ports(nodes, edges)
    paths, labels, ctrl, traces, label_boxes = [], [], [], [], []
    for i, e in enumerate(edges):
        p0, p1, p2, p3, tip = geometry(nodes, e, s_ports[i], e_ports[i])
        straight = abs(p0[0] - tip[0]) < 0.5 or abs(p0[1] - tip[1]) < 0.5
        origin = p0
        if e.get('fan'):
            origin = (p0[0] - NORMALS[e['side']][0] * STEM, p0[1] - NORMALS[e['side']][1] * STEM)
        if straight and e['to_side'] == OPPOSITE[e['side']]:
            sx, sy = origin
            d = f'M{fmt(sx)} {fmt(sy)} L{fmt(p3[0])} {fmt(p3[1])}'
            pts = [(sx + (p3[0] - sx) * t / 24, sy + (p3[1] - sy) * t / 24) for t in range(25)]
        else:
            pts = ([origin] if origin != p0 else []) + [bezier(p0, p1, p2, p3, t / 24)[0] for t in range(25)]
            pts += [(p3[0] + (tip[0] - p3[0]) * t / 4, p3[1] + (tip[1] - p3[1]) * t / 4) for t in range(1, 5)]
            stem = ''
            if e.get('fan'):
                ax0, ay0 = p0[0] - NORMALS[e['side']][0] * STEM, p0[1] - NORMALS[e['side']][1] * STEM
                stem = f'M{fmt(ax0)} {fmt(ay0)} L'
            d = (stem or 'M') + f'{fmt(p0[0])} {fmt(p0[1])} C{fmt(p1[0])} {fmt(p1[1])} {fmt(p2[0])} {fmt(p2[1])} {fmt(p3[0])} {fmt(p3[1])}' + (f' L{fmt(tip[0])} {fmt(tip[1])}' if p3 != tip else '')
            ctrl.append(f'<path class="c-line" d="M{fmt(p0[0])} {fmt(p0[1])} L{fmt(p1[0])} {fmt(p1[1])} M{fmt(p3[0])} {fmt(p3[1])} L{fmt(p2[0])} {fmt(p2[1])}"/>'
                        f'<circle class="c-dot" cx="{fmt(p1[0])}" cy="{fmt(p1[1])}" r="3.5"/><circle class="c-dot" cx="{fmt(p2[0])}" cy="{fmt(p2[1])}" r="3.5"/>')
        main = e.get('main')
        cls = 'i-line' + (' i-main' if main else '') + (' i-dash' if e.get('dash') else '')
        tone = '-d' if main else ''
        kinds = {'arrow': 'arr', 'cross': 'x', 'dot': 'o'}
        ends = ''
        if e.get('start') in kinds:
            ends += f' marker-start="url(#alpt-{kinds[e["start"]]}-{sym}{tone})"'
        if e.get('end', 'arrow') in kinds:
            ends += f' marker-end="url(#alpt-{kinds[e.get("end", "arrow")]}-{sym}{tone})"'
        paths.append(f'<path class="{cls}" d="{d}"{ends}/>')
        traces.append({'name': f'{e["from"]} → {e["to"]}', 'from': e['from'], 'to': e['to'], 'pts': pts})
        if e.get('label'):
            (mx, my), (tx, ty) = bezier(p0, p1, p2, p3, 0.5)
            n = math.hypot(tx, ty) or 1
            # Off to one side of the line, as seen on the screen: left, right, above or below.
            ox, oy = -ty / n, tx / n
            want = e.get('label_side', 'right')
            if (want == 'left' and ox > 0) or (want == 'right' and ox < 0) or (want == 'above' and oy > 0) or (want == 'below' and oy < 0):
                ox, oy = -ox, -oy
            lx, ly = mx + ox * 12, my + oy * 12 + 4
            anchor = 'start' if ox > 0.3 else 'end' if ox < -0.3 else 'middle'
            labels.append(f'<text class="t-xs" x="{fmt(lx)}" y="{fmt(ly)}" text-anchor="{anchor}">{e["label"]}</text>')
            w = 7.2 * len(e['label'])  # 13px semibold, about 7px a letter
            x0 = lx if anchor == 'start' else lx - w if anchor == 'end' else lx - w / 2
            label_boxes.append({'name': f'label “{e["label"]}” ({e["from"]} → {e["to"]})', 'own': len(traces) - 1,
                                'box': (x0, ly - 11, w, 13)})
    draw.traces, draw.label_boxes = traces, label_boxes
    return paths, labels, ctrl


# ---- Clash check: run on every build, so a clash shows up before anyone sees the picture ----

CLEAR = 4      # an arrow or label must stay this far from boxes and shapes it isn't joined to
MIN_LEN = 16   # an arrow shorter than this is mostly arrowhead
STEEP = 0.6    # an S-curve whose run is less than this share of its sideways travel is too steep to read


def rect_dist(pt, box):
    """How far a point is from a box (0 when inside)."""
    x, y, w, h = box
    dx = max(x - pt[0], 0, pt[0] - (x + w))
    dy = max(y - pt[1], 0, pt[1] - (y + h))
    return math.hypot(dx, dy)


def boxes_touch(a, b, pad=0):
    return not (a[0] + a[2] + pad <= b[0] or b[0] + b[2] + pad <= a[0] or a[1] + a[3] + pad <= b[1] or b[1] + b[3] + pad <= a[1])


def seg_cross(p, q, r, s_):
    """Where segments p-q and r-s cross, or None."""
    d = (q[0] - p[0]) * (s_[1] - r[1]) - (q[1] - p[1]) * (s_[0] - r[0])
    if abs(d) < 1e-9:
        return None
    t = ((r[0] - p[0]) * (s_[1] - r[1]) - (r[1] - p[1]) * (s_[0] - r[0])) / d
    u = ((r[0] - p[0]) * (q[1] - p[1]) - (r[1] - p[1]) * (q[0] - p[0])) / d
    return (p[0] + t * (q[0] - p[0]), p[1] + t * (q[1] - p[1])) if 0 <= t <= 1 and 0 <= u <= 1 else None


def check(sym, nodes, edges, shapes):
    """Problems in one picture: arrows through boxes or shapes, labels on lines, crossings, stubby arrows."""
    draw(sym, nodes, edges)
    traces, label_boxes = draw.traces, draw.label_boxes
    problems = []
    others = dict(nodes, **shapes)
    for tr in traces:
        length = sum(math.dist(a, b) for a, b in zip(tr['pts'], tr['pts'][1:]))
        if length < MIN_LEN:
            problems.append(f'{tr["name"]}: only {length:.0f} long, so it is mostly arrowhead')
        for name, box in others.items():
            if name in (tr['from'], tr['to']):
                # Its own boxes: it may start and end beside them, but must not run back through them.
                if any(rect_dist(p, box) < 0.5 for p in tr['pts'][2:-2]):
                    problems.append(f'{tr["name"]}: runs back through {name}')
                continue
            near = min(rect_dist(p, box) for p in tr['pts'])
            if near < CLEAR:
                problems.append(f'{tr["name"]}: {"crosses" if near < 0.5 else f"comes {near:.0f} from"} {name}')
    s_ports, e_ports = ports(nodes, edges)
    for i, e in enumerate(edges):
        if e['to_side'] != OPPOSITE[e['side']]:
            continue
        p0, _, _, p3, tip = geometry(nodes, e, s_ports[i], e_ports[i])
        if p3 == tip:
            continue  # straight
        along = abs(p3[0] - p0[0]) if e['side'] in ('left', 'right') else abs(p3[1] - p0[1])
        across = abs(p3[1] - p0[1]) if e['side'] in ('left', 'right') else abs(p3[0] - p0[0])
        if along < STEEP * across:
            problems.append(f'{e["from"]} → {e["to"]}: too steep (curves {across:.0f} sideways in a run of {along:.0f}); move the boxes apart')
    for lb in label_boxes:
        for name, box in others.items():
            if boxes_touch(lb['box'], box, 2):
                problems.append(f'{lb["name"]}: overlaps {name}')
        for k, tr in enumerate(traces):
            near = min(rect_dist(p, lb['box']) for p in tr['pts'])
            if near < (2 if k == lb['own'] else CLEAR):
                problems.append(f'{lb["name"]}: sits on the {tr["name"]} arrow')
    for a in range(len(traces)):
        for b in range(a + 1, len(traces)):
            A, B = traces[a]['pts'], traces[b]['pts']
            for p, q in zip(A, A[1:]):
                hit = next((x for r, s_ in zip(B, B[1:]) if (x := seg_cross(p, q, r, s_))), None)
                # Branches that share a starting point (a fan) run together along its stem by design.
                shared = math.dist(A[0], B[0]) < 1
                if hit and not (shared and math.dist(hit, A[0]) < STEM + 4):
                    problems.append(f'{traces[a]["name"]} and {traces[b]["name"]} cross at ({hit[0]:.0f}, {hit[1]:.0f})')
                    break
    return sorted(set(problems))


def edge(frm, side, to, to_side, **opts):
    return dict({'from': frm, 'side': side, 'to': to, 'to_side': to_side}, **opts)


MARKERS = {
    # One head for every arrow; a cross for "stopped"; a dot for a joined end. Each comes in ink and family colour.
    'arr': ('refX="8" refY="5" markerWidth="15" markerHeight="15"', '<path class="i-head{cls}" d="M1 1 9 5 1 9"/>'),
    'x': ('refX="5" refY="5" markerWidth="18" markerHeight="18"', '<path class="i-head{cls}" d="M2.5 2.5 7.5 7.5 M7.5 2.5 2.5 7.5"/>'),
    'o': ('refX="5" refY="5" markerWidth="10" markerHeight="10"', '<circle class="i-dot{cls}" cx="5" cy="5" r="3.5"/>'),
}


def defs(sym, used=None):
    out = ''
    for kind, (size, shape) in MARKERS.items():
        for tone, cls in (('', ''), ('-d', ' i-head--deep' if kind != 'o' else ' i-dot--deep')):
            mid = f'alpt-{kind}-{sym}{tone}'
            if used is not None and mid not in used:
                continue
            out += (f'<marker id="{mid}" viewBox="0 0 10 10" {size} markerUnits="userSpaceOnUse" '
                    f'orient="auto-start-reverse">{shape.format(cls=cls)}</marker>')
    return '<defs>' + out + '</defs>'


# ---- The three pictures: boxes as data, arrows as side-to-side links ----

TR_NODES = dict(data=(112, 26, 44, 40), guess=(96, 98, 88, 32), check=(196, 222, 88, 32), adjust=(16, 222, 88, 32))
TR_EDGES = [
    edge('data', 'bottom', 'guess', 'top'),
    edge('guess', 'right', 'check', 'top', main=True),
    edge('check', 'left', 'adjust', 'right', main=True),
    edge('adjust', 'top', 'guess', 'left', main=True),
]
TR_SVG = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 330" class="alpt-pic">
  <!-- Training (arrow trial, drawn by build.py): the loop is the main path, in the family colour with matching heads. -->
  {DEFS}
  <rect class="i-bg" width="300" height="330"/>
  <g class="i-faint"><rect class="i-cream" x="120" y="18" width="44" height="40" rx="3"/><rect class="i-line" x="120" y="18" width="44" height="40" rx="3"/></g><rect class="i-paper" x="112" y="26" width="44" height="40" rx="3"/><rect class="i-line" x="112" y="26" width="44" height="40" rx="3"/><path class="i-line" d="M120 38 H148 M120 48 H142 M120 58 H148" style="stroke-width:2"/>
  <text class="t-s" x="172" y="50" text-anchor="start">Data</text>
  <rect class="i-deep" x="96" y="98" width="88" height="32" rx="16"/><text class="t-s t-w" x="140" y="120" text-anchor="middle">Guess</text>
  <rect class="i-paper" x="196" y="222" width="88" height="32" rx="16"/><rect class="i-line" x="196" y="222" width="88" height="32" rx="16"/><text class="t-s" x="240" y="244" text-anchor="middle">Check</text>
  <rect class="i-paper" x="16" y="222" width="88" height="32" rx="16"/><rect class="i-line" x="16" y="222" width="88" height="32" rx="16"/><text class="t-s" x="60" y="244" text-anchor="middle">Adjust</text>
  {ARROWS}
  <circle class="i-cream" cx="126" cy="178" r="15"/><circle class="i-line" cx="126" cy="178" r="15"/><path class="i-line" d="M126 178 L134 166"/>
  <circle class="i-cream" cx="170" cy="178" r="15"/><circle class="i-line" cx="170" cy="178" r="15"/><path class="i-line" d="M170 178 L158 168"/>
  <text class="t-xs" x="148" y="212" text-anchor="middle">Settings</text>
  <text class="t-xs" x="20" y="310" text-anchor="start">Score</text>
  <rect class="i-deep" x="76" y="302" width="20" height="10" rx="2"/><rect class="i-deep" x="104" y="296" width="20" height="16" rx="2"/><rect class="i-deep" x="132" y="288" width="20" height="24" rx="2"/><rect class="i-deep" x="160" y="280" width="20" height="32" rx="2"/><rect class="i-accent" x="188" y="272" width="20" height="40" rx="2"/>
  {LABELS}{CTRL}
</svg>
'''

# The robot's box is its body without the chamfered corner's top edge; the person is their head.
AG_NODES = dict(goal=(96, 20, 136, 58), bot=(16, 124, 50, 60), s1=(140, 100, 144, 36), s2=(140, 144, 144, 36),
                s3=(140, 188, 144, 36), person=(218, 272, 24, 24))
AG_EDGES = [
    edge('goal', 'left', 'bot', 'top'),
    edge('bot', 'right', 's1', 'left', fan=True),
    edge('bot', 'right', 's2', 'left', fan=True),
    edge('bot', 'right', 's3', 'left', fan=True),
    edge('s3', 'bottom', 'person', 'top', main=True),
]
AG_SVG = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 330" class="alpt-pic">
  <!-- AI Agent (arrow trial, drawn by build.py): the steps fan out from one point; "asks you first" is the main path. Top-left 80x80 stays clear for the symbol badge. -->
  {DEFS}
  <rect class="i-bg" width="300" height="330"/>
  <rect class="i-deep" x="96" y="20" width="136" height="58" rx="10"/>
  <text class="t-xs t-w" x="164" y="41" text-anchor="middle" letter-spacing="2">GOAL</text>
  <text class="t-s t-w" x="164" y="64" text-anchor="middle">Book the hall</text>
  <path class="i-deep" d="M16 124 H50 L66 140 V184 H16 Z"/>
  <rect class="i-paper" x="24" y="142" width="32" height="22" rx="5"/>
  <circle class="i-ink" cx="34" cy="153" r="3.5"/><circle class="i-ink" cx="46" cy="153" r="3.5"/>
  {ARROWS}
  <rect class="i-paper" x="140" y="100" width="144" height="36" rx="8"/><rect class="i-line" x="140" y="100" width="144" height="36" rx="8"/>
  <text class="t-s" x="152" y="124">Check diary</text>
  <path class="i-deepline" d="M254 118 l5 5 l10 -11" stroke-width="4"/>
  <rect class="i-paper" x="140" y="144" width="144" height="36" rx="8"/><rect class="i-line" x="140" y="144" width="144" height="36" rx="8"/>
  <text class="t-s" x="152" y="168">Email office</text>
  <path class="i-deepline" d="M254 162 l5 5 l10 -11" stroke-width="4"/>
  <rect class="i-accent" x="140" y="188" width="144" height="36" rx="8"/><rect class="i-line i-dash" x="140" y="188" width="144" height="36" rx="8"/>
  <text class="t-s" x="152" y="212">Pay £50?</text>
  <rect class="i-ink" x="252" y="197" width="5" height="18" rx="2"/><rect class="i-ink" x="263" y="197" width="5" height="18" rx="2"/>
  <circle class="i-cream" cx="230" cy="284" r="12"/><circle class="i-line" cx="230" cy="284" r="12"/>
  <path class="i-line" d="M210 320 C210 300 250 300 250 320"/>
  <text class="t-s" x="200" y="306" text-anchor="end">Asks you first</text>
  {LABELS}{CTRL}
</svg>
'''

DT_NODES = dict(rain=(124, 26, 124, 42), coat=(20, 162, 96, 40), cold=(168, 124, 112, 42), jumper=(112, 232, 88, 40),
                tshirt=(210, 232, 78, 40))
DT_EDGES = [
    edge('rain', 'bottom', 'coat', 'top', label='yes', label_side='left'),
    edge('rain', 'bottom', 'cold', 'top', main=True, label='no', label_side='right'),
    edge('cold', 'bottom', 'jumper', 'top', main=True, label='yes', label_side='left'),
    edge('cold', 'bottom', 'tshirt', 'top', label='no', label_side='right'),
]
DT_SVG = '''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 300 330" class="alpt-pic">
  <!-- Decision Tree (arrow trial, drawn by build.py): the route to today's answer is the main path. -->
  {DEFS}
  <rect class="i-bg" width="300" height="330"/>
  <rect class="i-deep" x="124" y="26" width="124" height="42" rx="8"/>
  <text class="t-s t-w" x="186" y="52" text-anchor="middle">Raining?</text>
  {ARROWS}
  <rect class="i-paper" x="20" y="162" width="96" height="40" rx="20"/><rect class="i-line" x="20" y="162" width="96" height="40" rx="20"/>
  <text class="t-s" x="68" y="188" text-anchor="middle">Coat</text>
  <rect class="i-deep" x="168" y="124" width="112" height="42" rx="8"/>
  <text class="t-s t-w" x="224" y="151" text-anchor="middle">Cold?</text>
  <rect class="i-accent" x="112" y="232" width="88" height="40" rx="20"/><rect class="i-line" x="112" y="232" width="88" height="40" rx="20"/>
  <text class="t-s" x="156" y="258" text-anchor="middle">Jumper</text>
  <rect class="i-paper" x="210" y="232" width="78" height="40" rx="20"/><rect class="i-line" x="210" y="232" width="78" height="40" rx="20"/>
  <text class="t-s" x="249" y="258" text-anchor="middle">T-shirt</text>
  <circle class="i-cream" cx="54" cy="226" r="14"/><circle class="i-line" cx="54" cy="226" r="14"/>
  <path class="i-cream" d="M30 268 C30 244 78 244 78 268 Z"/><path class="i-line" d="M30 268 C30 244 78 244 78 268"/>
  <text class="t-xs" x="54" y="292" text-anchor="middle">Dry, cold</text>
  <text class="t-xs" x="54" y="308" text-anchor="middle">today</text>
  {LABELS}{CTRL}
</svg>
'''

SHAPES = {
    'tr': {'the paper behind Data': (120, 18, 44, 40), 'Data label': (172, 38, 36, 16), 'the dials': (111, 163, 74, 30),
           'Settings label': (122, 201, 52, 14), 'the score bars': (76, 272, 132, 40), 'Score label': (20, 299, 36, 14)},
    'ag': {'Asks you first label': (88, 293, 112, 16), 'the person': (210, 298, 40, 22)},
    'dt': {'the person': (30, 212, 48, 56), 'Dry, cold today label': (24, 281, 60, 30)},
}

PICS = [
    ('tr', TR_SVG, TR_NODES, TR_EDGES, 'Training', '#E3E8EF', '#334155', '#FF7038', [
        'The loop is the main path, so it is drawn in the family colour, with heads to match (before, the heads stayed black).',
        'Check and Adjust face each other on one line, so the code draws that arrow straight.']),
    ('ag', AG_SVG, AG_NODES, AG_EDGES, 'AI Agent', '#FFEDD5', '#B45309', '#FA83EB', [
        'The goal arrow is solid: the goal is handed over, it is not a “maybe”, so it should not be dashed.',
        'The three steps are a fan: one shared starting point, level with the middle step, so that one is straight. The robot and the steps sit further apart, so the curves have room.',
        '“Asks you first” is the point of the picture, so that arrow is the main path.']),
    ('dt', DT_SVG, DT_NODES, DT_EDGES, 'Decision Tree', '#DFE9FF', '#1D4ED8', '#FF7038', [
        'The two arrows leaving each question are spread evenly along its edge; where one lands straight below, it is drawn straight.',
        'The route to today’s answer is the main path, with heads in the family colour.',
        'The yes and no labels are placed from the curve: at its middle, set off to one side.']),
]


def render(sym, svg, nodes, edges, overlay):
    paths, labels, ctrl = draw(sym, nodes, edges)
    used = set(re.findall(r'url\(#(alpt-[a-z]+-[a-z0-9]+(?:-d)?)\)', ' '.join(paths)))
    out = (svg.replace('{DEFS}', defs(sym, used))
              .replace('{ARROWS}', '\n  '.join(paths))
              .replace('{LABELS}', '\n  '.join(labels))
              .replace('{CTRL}', ('\n  <g class="ctrl">' + ''.join(ctrl) + '</g>') if overlay and ctrl else ''))
    return re.sub(r'\n\s*\n', '\n', out)


def inline(svg, suffix):
    svg = re.sub(r'<!--.*?-->', '', svg, flags=re.S)
    return re.sub(r'alpt-arr-(\w+)', lambda m: 'alpt-arr-' + m.group(1) + suffix, svg)


# ---- Three ways to draw AI Agent: Mermaid's hand-drawn look, Rough.js over our calculated paths, and ours ----

AG_TEXT = dict(goal='Goal: Book the hall', bot='Agent', s1='Check diary ✓', s2='Email office ✓', s3='Pay £50? ⏸', person='You')

MERMAID = """---
config:
  look: handDrawn
  theme: neutral
---
flowchart TD
  G[Goal: Book the hall] --> A[Agent]
  A --> D[Check diary ✓]
  A --> E[Email office ✓]
  A --> P[Pay £50? ⏸]
  P ==> Y((Asks you first))"""


def rough_data(nodes, edges):
    """Boxes and the calculated curves (with the end direction for the heads), for Rough.js to sketch."""
    import json
    s_ports, e_ports = ports(nodes, edges)
    arrows = []
    for i, e in enumerate(edges):
        p0, p1, p2, p3, tip = geometry(nodes, e, s_ports[i], e_ports[i])
        d = f'M{fmt(p0[0])} {fmt(p0[1])} C{fmt(p1[0])} {fmt(p1[1])} {fmt(p2[0])} {fmt(p2[1])} {fmt(p3[0])} {fmt(p3[1])} L{fmt(tip[0])} {fmt(tip[1])}'
        # The head points along the straight run into the tip.
        ang = math.atan2(tip[1] - p3[1], tip[0] - p3[0]) if p3 != tip else math.atan2(tip[1] - p0[1], tip[0] - p0[0])
        arrows.append({'d': d, 'end': [round(tip[0], 1), round(tip[1], 1)], 'angle': round(ang, 4), 'main': bool(e.get('main'))})
    boxes = [{'name': k, 'box': v, 'text': AG_TEXT[k]} for k, v in nodes.items()]
    return json.dumps({'boxes': boxes, 'arrows': arrows}, ensure_ascii=False)


def compare_section():
    ours = inline(render('ag', AG_SVG, AG_NODES, AG_EDGES, overlay=False), '-cmp')
    return f"""<section class="compare" style="--fam:#FFEDD5;--fam-deep:#B45309;--pic-accent:#FA83EB">
<h2>Three ways to draw it</h2>
<p class="cmp-lead">AI Agent drawn three ways. The first two use the libraries suggested for this; the third is our method.</p>
<div class="cmp-grid">
<figure><div class="cmp-box"><pre class="mermaid">{MERMAID}</pre></div><figcaption>Mermaid, hand-drawn look</figcaption>
<p>Written as text; Mermaid lays out the boxes itself. No coordinates to get wrong, but it decides the layout, it cannot draw our pictures (the robot, the person, the ticks) and every diagram looks like Mermaid.</p></figure>
<figure><div class="cmp-box"><svg id="rough-ag" viewBox="0 0 300 330" class="alpt-pic"></svg></div><figcaption>Rough.js over our calculated paths</figcaption>
<p>The same boxes and the same calculated curves as ours, sketched by Rough.js (roughness 0.6). Keeps our layout; the hand-drawn wobble is a different style from the rest of the site.</p></figure>
<figure><div class="cmp-box">{ours}</div><figcaption>Ours (build.py)</figcaption>
<p>Calculated from box and side, drawn in our line style with our illustrations. Matches the other 134 pictures.</p></figure>
</div>
<script type="application/json" id="rough-data">{rough_data(AG_NODES, AG_EDGES)}</script>
<script src="https://cdn.jsdelivr.net/npm/roughjs@4.6.6/bundled/rough.js"></script>
<script>
(function () {{
  var data = JSON.parse(document.getElementById('rough-data').textContent);
  var svg = document.getElementById('rough-ag');
  if (!window.rough || !svg) {{ return; }}
  var ns = 'http://www.w3.org/2000/svg';
  var rc = rough.svg(svg);
  var bg = document.createElementNS(ns, 'rect');
  bg.setAttribute('width', 300); bg.setAttribute('height', 330); bg.setAttribute('class', 'i-bg');
  svg.appendChild(bg);
  var ink = '#231F20', deep = '#B45309';
  var base = {{ roughness: 0.6, bowing: 1, seed: 7, strokeWidth: 2.5, stroke: ink }};
  data.boxes.forEach(function (b) {{
    var x = b.box[0], y = b.box[1], w = b.box[2], h = b.box[3];
    var fill = b.name === 'goal' || b.name === 'bot' ? deep : b.name === 's3' ? '#FA83EB' : '#FFFFFF';
    var shape = Object.assign({{}}, base, {{ fill: b.name === 'person' ? '#F6F4ED' : fill, fillStyle: 'solid' }});
    svg.appendChild(b.name === 'person' ? rc.circle(x + w / 2, y + h / 2, w, shape) : rc.rectangle(x, y, w, h, shape));
    if (b.name === 'person') {{ svg.appendChild(rc.path('M210 320 C210 300 250 300 250 320', base)); }}
    var t = document.createElementNS(ns, 'text');
    t.setAttribute('x', x + w / 2); t.setAttribute('y', y + h / 2 + 5); t.setAttribute('text-anchor', 'middle');
    t.setAttribute('class', 't-xs' + (fill === deep ? ' t-w' : ''));
    t.textContent = b.name === 'person' ? '' : b.text;
    svg.appendChild(t);
  }});
  data.arrows.forEach(function (a) {{
    var o = Object.assign({{}}, base, {{ stroke: a.main ? deep : ink, strokeWidth: 2.5 }});
    svg.appendChild(rc.path(a.d, o));
    var L = 10, s = 0.5, x = a.end[0], y = a.end[1];
    svg.appendChild(rc.linearPath([[x - L * Math.cos(a.angle - s), y - L * Math.sin(a.angle - s)], [x, y], [x - L * Math.cos(a.angle + s), y - L * Math.sin(a.angle + s)]], o));
  }});
  var lbl = document.createElementNS(ns, 'text');
  lbl.setAttribute('x', 200); lbl.setAttribute('y', 306); lbl.setAttribute('text-anchor', 'end'); lbl.setAttribute('class', 't-s');
  lbl.textContent = 'Asks you first';
  svg.appendChild(lbl);
}})();
</script>
<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11.4.1/dist/mermaid.esm.min.mjs';
mermaid.initialize({{ startOnLoad: true, look: 'handDrawn', theme: 'neutral' }});
</script>
</section>
"""


def main():
    cards = ''
    for sym, svg, nodes, edges, name, bg, deep, acc, notes in PICS:
        (HERE / f'{sym}.svg').write_text(render(sym, svg, nodes, edges, overlay=False))
        page_svg = render(sym, svg, nodes, edges, overlay=True)
        problems = check(sym, nodes, edges, SHAPES.get(sym, {}))
        for pr in problems:
            print(f'CLASH {sym}: {pr}')
        report = ('<p class="clash ok">Clash check: no arrow crosses a box, shape or label, nothing crosses, nothing is too short.</p>'
                  if not problems else '<div class="clash bad"><p>Clash check found:</p><ul>' + ''.join(f'<li>{pr}</li>' for pr in problems) + '</ul></div>')
        li = ''.join(f'<li>{n}</li>' for n in notes)
        links = ''.join(f'<li><code>{e["from"]} {e["side"]} → {e["to"]} {e["to_side"]}</code>{" (main)" if e.get("main") else ""}{" (fan)" if e.get("fan") else ""}</li>' for e in edges)
        cards += (f'<section class="pair" style="--fam:{bg};--fam-deep:{deep};--pic-accent:{acc}">\n<h2>{name}</h2>\n'
                  f'<div class="pics"><figure><div class="pic">{inline((LIVE / f"{sym}.svg").read_text(), "-old")}</div><figcaption>Now</figcaption></figure>\n'
                  f'<figure><div class="pic">{inline(page_svg, "-new")}</div><figcaption>Trial</figcaption></figure></div>\n'
                  f'<ul class="notes">{li}</ul>\n{report}\n<details class="links"><summary>The arrows as data</summary><ul>{links}</ul></details></section>\n')
    head = (HERE / 'page-head.html').read_text()
    (HERE / 'index.html').write_text(head + '\n' + cards + compare_section() + '</main></body></html>')


if __name__ == '__main__':
    main()
