# Arrows for the periodic table pictures

The arrows in `assets/images/literacy-table/*.svg` are calculated, never typed in. Each picture with arrows has a
spec here, `specs/{sym}.json`, and `apply.py` turns the spec into the arrows and checks them.

```bash
python3 tools/literacy-arrows/apply.py dt --preview /tmp/pv   # redraw Dt, check it, write /tmp/pv/dt.png
python3 tools/literacy-arrows/apply.py                        # every picture with a spec
python3 tools/literacy-arrows/apply.py --check                # check everything, write nothing
```

The originals, before this tool, are in `archive/literacy-table-pictures-before-arrow-tool/`. `apply.py` always starts
from the original (or from `specs/{sym}.base.svg` if the picture has been redrawn), so running it again gives the
same file.

## A spec

```json
{
 "nodes":  { "rain": [124, 26, 124, 42], "coat": [20, 162, 96, 40], "start": [150, 10, 0, 0] },
 "edges":  [ { "from": "rain", "side": "bottom", "to": "coat", "to_side": "top", "label": "yes", "label_side": "left" } ],
 "shapes": { "the person": [30, 212, 48, 56] },
 "edits":  [ ["text to find in the drawing", "what to put instead"] ]
}
```

- **nodes**: every box an arrow joins, as `[x, y, width, height]` in the 300 x 330 picture. For a circle, its square.
  For an arrow that starts or ends in open space, a point: `[x, y, 0, 0]`.
- **edges**: one per arrow, only ever box and side to box and side (`top`, `bottom`, `left`, `right`). Options:
  - `"main": true`: the one route the picture is about. Family colour. One per picture at most.
  - `"dash": true`: maybe, optional or later.
  - `"fan": true` on several arrows leaving one side: one shared starting point.
  - `"end": "cross"` (blocked, refused, not allowed), `"dot"` or `"none"`; `"start": "dot"`. Related things that
    don't flow: `"start": "dot", "end": "dot"`.
  - `"label"` and `"label_side"` (`left`, `right`, `above`, `below`): a short word placed from the curve. Remove the
    old hand-placed label with an edit.
  - `"bow"`: a number (about 20 to 30) for a rounder, hand-drawn arc, mainly for loop-back arrows.
  - `"at"` / `"to_at"`: only when the middle of the side is wrong, as a share of the side (0 to 1), never a coordinate.
- **shapes**: everything else an arrow or label must keep clear of: drawings, icons, text that isn't in a node.
- **edits**: find-and-replace on the drawing, for removing hand-drawn connector lines or old arrow labels, or
  moving things to make room. Each `find` must appear in the drawing.

## The rules the code applies

- Arrows leave and arrive at right angles to a box edge, stop 8 short of it, and finish with a straight run longer
  than the head, so every head sits on a straight line. One head, one thickness (2.5); only the colour changes.
- Facing sides that line up (or nearly, within 6) are joined by a straight line.
- Several arrows leaving one side are spread evenly; a fan shares one starting point.
- A quarter-turn arrow gets at least 60 of room on the side it arrives from, and swings in by at least 36, so it
  bends rather than hooks. An arrow between two sides that face the same way (right to right) is one smooth C.
- For a loop back, prefer leaving from the top or bottom of a box over its side: one arc reads better than a bracket.

## The clash check

`apply.py` reports, and exits with an error, when an arrow:

- crosses or comes within 4 of a box or shape it isn't joined to, or runs back through its own box;
- crosses another arrow;
- is shorter than 16 (mostly arrowhead);
- is an S-curve too steep to read (its run is less than 60% of its sideways travel);

or when a label sits on an arrow or overlaps a box or shape. The fix is nearly always to choose different sides, or
to move a box with an edit (and its node to match).

## Other tools here

- `sheet.py` writes `archive/arrow-style-trial/all-pictures.html`: all illustrated concepts, with before-and-current comparisons and a presentation view.
- `embedding.py` lays out the Embedding picture (`em.svg`) the same way: labels one fixed distance from their dots,
  each cluster's circle the smallest that holds its dots and labels (Welzl's algorithm), both circles the same size.

## Drawing rules (from data/literacy-table/README.md)

`viewBox="0 0 300 330"`, `class="alpt-pic"`, paint only with the `i-*` and `t-*` classes, keep the top-left corner
(about 80 x 80) light for the symbol badge, show the idea happening, up to about six short labels, no photographs and
nothing that could look like a real person.
