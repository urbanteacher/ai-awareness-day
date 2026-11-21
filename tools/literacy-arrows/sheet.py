"""A before-and-after sheet of every picture with a spec: archive/arrow-style-trial/all-pictures.html.

    python3 tools/literacy-arrows/sheet.py
"""
import json
import re
from pathlib import Path

HERE = Path(__file__).resolve().parent
ROOT = HERE.parent.parent
from apply import FAMILY, THEME, ORIGINALS, LIVE, SPECS  # noqa: E402

els = {e['sym'].lower(): e for e in json.loads((ROOT / 'data/literacy-table/elements.json').read_text())['elements']}


def inline(svg, suffix):
    svg = re.sub(r'<!--.*?-->', '', svg, flags=re.S)
    return re.sub(r'(alpt-(?:arr|x|o)-[a-z0-9]+(?:-d)?)', r'\1' + suffix, svg)


cards = []
for spec in sorted(SPECS.glob('*.json')):
    sym = spec.stem
    el = els.get(sym, {})
    bg, deep = FAMILY.get(el.get('family', ''), FAMILY['foundations'])
    acc = THEME.get(el.get('theme', ''), THEME['smart'])
    redrawn = (SPECS / f'{sym}.base.svg').exists() or bool(json.loads(spec.read_text()).get('edits'))
    cards.append(f'<section class="card" id="{sym}" style="--fam:{bg};--fam-deep:{deep};--pic-accent:{acc}">'
                 f'<h2>{el.get("name", sym)} <span>{el.get("sym", sym)}{" · drawing changed" if redrawn else ""}</span></h2>'
                 f'<div class="two"><figure>{inline((ORIGINALS / f"{sym}.svg").read_text(), "-b")}<figcaption>Before</figcaption></figure>'
                 f'<figure>{inline((LIVE / f"{sym}.svg").read_text(), "-a")}<figcaption>After</figcaption></figure></div></section>')

css = """:root{--ink:#231F20;--cream:#F6F4ED;--dim:#54504E}*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--ink);font:15px/1.45 "AIAD Sans",system-ui,-apple-system,"Segoe UI",sans-serif}
main{max-width:1240px;margin:0 auto;padding:32px 16px 64px}h1{font-size:2rem;margin:0 0 .4rem}.lead{margin:0 0 1.5rem;max-width:46rem}
.grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(360px,1fr))}
.card{background:#fff;border:2px solid var(--ink);padding:10px}.card h2{font-size:1rem;margin:0 0 .5rem}.card h2 span{font-weight:500;color:var(--dim);font-size:.85rem}
.two{display:grid;grid-template-columns:1fr 1fr;gap:8px}figure{margin:0}figure svg{display:block;width:100%;height:auto}
figcaption{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-top:.2rem}
.alpt-pic text{font-family:inherit;fill:var(--ink)}.alpt-pic .i-bg{fill:var(--fam)}.alpt-pic .i-deep{fill:var(--fam-deep)}.alpt-pic .i-paper{fill:#fff}
.alpt-pic .i-cream{fill:var(--cream)}.alpt-pic .i-accent{fill:var(--pic-accent)}.alpt-pic .i-ink{fill:var(--ink)}
.alpt-pic .i-line{fill:none;stroke:var(--ink);stroke-width:2.5;stroke-linecap:round;stroke-linejoin:round}
.alpt-pic .i-deepline{fill:none;stroke:var(--fam-deep);stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
.alpt-pic .i-main{stroke:var(--fam-deep)}.alpt-pic .i-dash{stroke-dasharray:7 7}.alpt-pic .i-faint{opacity:.35}
.alpt-pic .i-head{fill:none;stroke:var(--ink);stroke-width:1.67;stroke-linecap:round;stroke-linejoin:round}.alpt-pic .i-head--deep{stroke:var(--fam-deep)}
.alpt-pic .i-dot{fill:var(--ink)}.alpt-pic .i-dot--deep{fill:var(--fam-deep)}
.alpt-pic .t-xs{font-size:13px;font-weight:600}.alpt-pic .t-s{font-size:16px;font-weight:600}.alpt-pic .t-m{font-size:22px;font-weight:700}
.alpt-pic .t-l{font-size:34px;font-weight:700}.alpt-pic .t-w{fill:#fff}.alpt-pic .t-d{fill:var(--fam-deep)}"""
html = (f'<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        f'<title>Picture Arrows Review</title><style>{css}</style></head><body><main><h1>Picture arrows: before and after</h1>'
        f'<p class="lead">{len(cards)} AI Literacy Periodic Table pictures, before and after their arrows were calculated by '
        f'tools/literacy-arrows. “Drawing changed” marks pictures whose layout was also adjusted.</p>'
        f'<div class="grid">{"".join(cards)}</div></main></body></html>')
out = ROOT / 'archive/arrow-style-trial/all-pictures.html'
out.write_text(html)
print(out, len(cards))
