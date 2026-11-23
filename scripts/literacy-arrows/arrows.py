"""The arrow engine for the AI Literacy Periodic Table pictures: arrows calculated from the boxes, never typed-in coordinates.

Used by apply.py (see README.md in this folder). A box can be a point, [x, y, 0, 0], for an arrow that starts or ends
in open space.

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

GAP = 8         # space between an arrow's ends and the edges they meet
REACH = 0.55    # control points, sides at right angles: share of the distance on their own axis
FACING = 0.5    # control points, sides facing each other: share of the gap between them
INSET = 10      # ports keep this far from a side's corners
STEM = 6        # a fan's branches leave level for this far before they curve
HEAD = 12       # the arrowhead's length (the marker's chevron: 8 of its 10 units, drawn 15 wide)
END_RUN = HEAD + 4  # every curved arrow ends with a straight run this long, so the head sits on a straight line
SNAP = 6        # facing sides this close to lining up are drawn straight
SWEEP = 36      # a curve that arrives from beyond a box swings out at least this far, so it bends, never hooks
TURN = 44 + END_RUN  # a quarter-turn arrow gets at least this much room on the axis it arrives along

NORMALS = {'top': (0, -1), 'bottom': (0, 1), 'left': (-1, 0), 'right': (1, 0)}
OPPOSITE = {'top': 'bottom', 'bottom': 'top', 'left': 'right', 'right': 'left'}


def side_span(box, side):
    """The run of a side along its own axis (x for top/bottom, y for left/right), inset from the corners."""
    x, y, w, h = box
    lo, hi = (x, x + w) if side in ('top', 'bottom') else (y, y + h)
    if hi - lo < 2 * INSET:
        mid = (lo + hi) / 2  # a point or a narrow box: its middle
        return mid, mid
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
    defaulted = set()
    for i, e in enumerate(edges):
        tlo, thi = side_span(nodes[e['to']], e['to_side'])
        facing = e['to_side'] == OPPOSITE[e['side']]
        if 'to_at' in e:
            end[i] = tlo - INSET + (thi - tlo + 2 * INSET) * e['to_at']
        elif facing and tlo - SNAP <= start[i] <= thi + SNAP:
            end[i] = start[i]  # facing and in reach (or within SNAP of it): straight
        else:
            end[i] = (tlo + thi) / 2
            defaulted.add(i)
        # Quarter turns: make room on the arriving axis by sliding a lone start along its side.
        if not facing and 'at' not in e and not e.get('fan') and len(groups[(e['from'], e['side'])]) == 1:
            arrive_horizontal = e['to_side'] in ('left', 'right')
            if arrive_horizontal == (e['side'] in ('top', 'bottom')):
                tx, ty = point_on(nodes[e['to']], e['to_side'], end[i])
                target = tx + NORMALS[e['to_side']][0] * GAP if arrive_horizontal else ty + NORMALS[e['to_side']][1] * GAP
                n = NORMALS[e['to_side']][0] if arrive_horizontal else NORMALS[e['to_side']][1]
                # The arrow comes in from the n side of the target, so the start belongs at least TURN that way.
                if (start[i] - target) * n < TURN:
                    lo, hi = side_span(nodes[e['from']], e['side'])
                    start[i] = min(max(target + n * TURN, lo), hi)
        # A near-miss the other way: the end is fixed but the start is free (a lone arrow), so snap the start.
        if facing and 'at' not in e and not e.get('fan') and abs(end[i] - start[i]) <= SNAP:
            start[i] = end[i]
    # Arrivals: several arrows that would land on the middle of the same side are spread evenly along it, in the
    # order of where they come from, so they never meet at one point.
    arrive = {}
    for i in defaulted:
        arrive.setdefault((edges[i]['to'], edges[i]['to_side']), []).append(i)
    for (name, side), idx in arrive.items():
        if len(idx) < 2:
            continue
        lo, hi = side_span(nodes[name], side)
        along = 0 if side in ('top', 'bottom') else 1
        src = lambda i: point_on(nodes[edges[i]['from']], edges[i]['side'], start[i])[along]
        for n, i in enumerate(sorted(idx, key=src)):
            end[i] = lo + (hi - lo) * (n + 1) / (len(idx) + 1)
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
    elif e['side'] == e['to_side']:
        # Both ends face the same way (right to right, say): one smooth C that bows out past the further end.
        k = 0 if anx else 1
        n = anx or any_
        beyond = max(0.0, (p3[k] - p0[k]) * n), max(0.0, (p0[k] - p3[k]) * n)
        r1, r2 = beyond[0] + SWEEP, beyond[1] + SWEEP
    else:
        dx, dy = abs(p3[0] - p0[0]), abs(p3[1] - p0[1])
        r1 = REACH * (dx if anx else dy)
        r2 = REACH * (dx if bnx else dy)
        # Arriving from beyond the box (the start is on the side the arrow comes in from): sweep in, don't hook.
        r2 = max(r2, min(SWEEP, 0.5 * (dy if bnx else dx)))
    p1 = (p0[0] + anx * r1, p0[1] + any_ * r1)
    p2 = (p3[0] + bnx * r2, p3[1] + bny * r2)
    bow = e.get('bow', 0)
    if bow and not facing:
        # A rounder arc: the first handle leans out early toward the side the arrow arrives from, so the line
        # swings wide in one bend (as a hand-drawn loop back would) and still finishes on its straight run in.
        p1 = (p0[0] + anx * r1 * 0.6 + bnx * bow, p0[1] + any_ * r1 * 0.6 + bny * bow)
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
            w, h = 7.2 * len(e['label']), 13  # 13px semibold, about 7px a letter
            reach = 6 + abs(ox) * w / 2 + abs(oy) * h / 2  # half the label's size in that direction, plus 6
            cx, cy = mx + ox * reach, my + oy * reach
            lx, ly, anchor = cx, cy + 4.5, 'middle'
            labels.append(f'<text class="t-xs" x="{fmt(lx)}" y="{fmt(ly)}" text-anchor="{anchor}">{e["label"]}</text>')
            x0 = cx - w / 2
            label_boxes.append({'name': f'label “{e["label"]}” ({e["from"]} → {e["to"]})', 'own': len(traces) - 1,
                                'box': (x0, cy - h / 2, w, h)})
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
