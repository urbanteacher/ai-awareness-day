"""Redraw the arrows in the periodic table pictures from their specs, check them for clashes, and preview them.

    python3 tools/literacy-arrows/apply.py                 # every picture with a spec
    python3 tools/literacy-arrows/apply.py tr ag dt        # just these
    python3 tools/literacy-arrows/apply.py tr --preview DIR  # also write DIR/tr.png to look at
    python3 tools/literacy-arrows/apply.py --check          # check only, write nothing

Each picture has a spec, specs/{sym}.json (see README.md). The drawing comes from specs/{sym}.base.svg when there is
one (a picture redrawn or rearranged for this tool), otherwise from the archived original in
archive/literacy-table-pictures-before-arrow-tool/. Its hand-drawn arrows (anything with a marker), arrow markers and
any earlier calculated arrows are taken out, the spec's "edits" are applied, and the calculated arrows go in where the
first old arrow was, so they keep their place in front of or behind the drawing. The result is written to
assets/images/literacy-table/{sym}.svg. Running it again gives the same file.
"""
import json
import re
import subprocess
import sys
from pathlib import Path

import arrows as A

HERE = Path(__file__).resolve().parent
ROOT = HERE.parent.parent
LIVE = ROOT / 'assets/images/literacy-table'
ORIGINALS = ROOT / 'archive/literacy-table-pictures-before-arrow-tool'
SPECS = HERE / 'specs'

FAMILY = {'foundations': ('#E3E8EF', '#334155'), 'generative': ('#EEE6FF', '#6D28D9'), 'predictive': ('#DFE9FF', '#1D4ED8'),
          'agents': ('#FFEDD5', '#B45309'), 'automation': ('#D5F2EC', '#0F766E'), 'governance': ('#FFE1E7', '#BE123C'),
          'infra': ('#E8F3D6', '#3F6212')}
THEME = {'safe': '#00BEDD', 'smart': '#FF7038', 'creative': '#AC91FF', 'responsible': '#63DF93', 'future': '#FA83EB'}

ARROW_EL = re.compile(r'<(path|line|polyline)\b[^>]*\bmarker-(?:start|end|mid)="[^"]*"[^>]*/>')
OLD_DEFS = re.compile(r'<defs>(?:(?!</defs>).)*<marker\b.*?</defs>', re.S)
GROUP = re.compile(r'<g class="alpt-(?:arrows|arrow-labels)">.*?</g>\n?', re.S)


def element_info(sym):
    data = json.loads((ROOT / 'data/literacy-table/elements.json').read_text())
    for el in data['elements']:
        if el['sym'].lower() == sym:
            return el
    return {}


def build(sym, spec):
    base_file = SPECS / f'{sym}.base.svg'
    svg = (base_file if base_file.exists() else ORIGINALS / f'{sym}.svg').read_text()
    for find, replace in spec.get('edits', []):
        if find not in svg:
            raise SystemExit(f'{sym}: edit not found: {find[:60]}')
        svg = svg.replace(find, replace, 1)
    svg = GROUP.sub('', svg)
    svg = OLD_DEFS.sub('', svg)
    first = ARROW_EL.search(svg)
    where = first.start() if first else svg.rindex('</svg>')
    svg = svg[:where] + '\x00' + ARROW_EL.sub('', svg[where:])
    svg = ARROW_EL.sub('', svg)
    nodes = {k: tuple(v) for k, v in spec['nodes'].items()}
    edges = [A.edge(e.pop('from'), e.pop('side'), e.pop('to'), e.pop('to_side'), **e) for e in (dict(x) for x in spec['edges'])]
    paths, labels, _ = A.draw(sym, nodes, edges)
    used = set(re.findall(r'url\(#(alpt-[a-z]+-[a-z0-9]+(?:-d)?)\)', ' '.join(paths)))
    group = '<g class="alpt-arrows">' + ''.join(paths) + '</g>' if paths else ''
    svg = svg.replace('\x00', group)
    if labels:
        svg = svg.replace('</svg>', '  <g class="alpt-arrow-labels">' + ''.join(labels) + '</g>\n</svg>')
    # Markers go just inside the <svg> (after its opening comment, if any).
    m = re.search(r'<svg\b[^>]*>\s*(<!--.*?-->)?', svg, re.S)
    if used:
        svg = svg[:m.end()] + '\n  ' + A.defs(sym, used) + svg[m.end():]
    svg = re.sub(r'\n\s*\n', '\n', svg)
    problems = A.check(sym, nodes, edges, {k: tuple(v) for k, v in spec.get('shapes', {}).items()})
    return svg, problems


def preview(sym, svg, out_dir):
    el = element_info(sym)
    bg, deep = FAMILY.get(el.get('family', 'foundations'), FAMILY['foundations'])
    acc = THEME.get(el.get('theme', 'smart'), THEME['smart'])
    css = (f'<style>.i-bg{{fill:{bg}}}.i-deep{{fill:{deep}}}.i-paper{{fill:#fff}}.i-cream{{fill:#F6F4ED}}.i-accent{{fill:{acc}}}'
           f'.i-ink{{fill:#231F20}}.i-line{{fill:none;stroke:#231F20;stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}}'
           f'.i-main{{stroke:{deep}}}.i-deepline{{fill:none;stroke:{deep};stroke-width:3;stroke-linecap:round;stroke-linejoin:round}}'
           f'.i-deepline.i-main{{stroke-width:2.5}}.i-dash{{stroke-dasharray:7 7}}.i-faint{{opacity:.35}}'
           f'.i-head{{fill:none;stroke:#231F20;stroke-width:1.67;stroke-linecap:round;stroke-linejoin:round}}.i-head--deep{{stroke:{deep}}}'
           f'.i-dot{{fill:#231F20}}.i-dot--deep{{fill:{deep}}}text{{font-family:Helvetica,Arial,sans-serif;fill:#231F20}}'
           f'.t-xs{{font-size:13px;font-weight:600}}.t-s{{font-size:16px;font-weight:600}}.t-m{{font-size:22px;font-weight:700}}'
           f'.t-l{{font-size:34px;font-weight:700}}.t-w{{fill:#fff}}.t-d{{fill:{deep}}}</style>')
    # Square, with the whole 300 x 330 picture in it (Quick Look makes square thumbnails).
    styled = svg.replace('viewBox="0 0 300 330"', 'viewBox="-15 0 330 330" width="600" height="600"', 1)
    styled = re.sub(r'(<svg\b[^>]*?)>', r'\1>' + css, styled, count=1)
    out_dir = Path(out_dir)
    out_dir.mkdir(parents=True, exist_ok=True)
    src = out_dir / f'{sym}.svg'
    src.write_text(styled)
    subprocess.run(['qlmanage', '-t', '-s', '600', '-o', str(out_dir), str(src)], capture_output=True)
    png = out_dir / f'{sym}.svg.png'
    if png.exists():
        png.replace(out_dir / f'{sym}.png')
    return out_dir / f'{sym}.png'


def main(argv):
    check_only = '--check' in argv
    out = None
    if '--preview' in argv:
        out = argv[argv.index('--preview') + 1]
    syms = [a for a in argv if not a.startswith('--') and a != out] or sorted(p.stem for p in SPECS.glob('*.json'))
    bad = 0
    for sym in syms:
        spec = json.loads((SPECS / f'{sym}.json').read_text())
        svg, problems = build(sym, spec)
        for pr in problems:
            print(f'CLASH {sym}: {pr}')
        bad += bool(problems)
        if not check_only:
            (LIVE / f'{sym}.svg').write_text(svg)
        if out:
            print(f'{sym}: preview {preview(sym, svg, out)}')
    print(f'{len(syms)} picture(s), {bad} with clashes')
    return 1 if bad else 0


if __name__ == '__main__':
    sys.exit(main(sys.argv[1:]))
