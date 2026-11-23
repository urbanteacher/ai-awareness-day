"""A before-and-after sheet of every illustrated concept: archive/arrow-style-trial/all-pictures.html.

    python3 scripts/literacy-arrows/sheet.py
"""
from html import escape
import json
import re
from pathlib import Path

HERE = Path(__file__).resolve().parent
ROOT = HERE.parent.parent
from apply import FAMILY, THEME, ORIGINALS, LIVE  # noqa: E402

els = {e['sym'].lower(): e for e in json.loads((ROOT / 'plugins/aiad-core/data/literacy-table/elements.json').read_text())['elements']}


def inline(svg, suffix, description):
    svg = svg.replace('<svg ', f'<svg role="img" aria-label="{escape(description)}" ', 1)
    svg = re.sub(r'[ \t]*<!--.*?-->[ \t]*\n?', '', svg, flags=re.S)
    return re.sub(r'(alpt-(?:arr|x|o)-[a-z0-9]+(?:-d)?)', r'\1' + suffix, svg)


cards = []
for sym, el in els.items():
    current = LIVE / f'{sym}.svg'
    if not current.exists():
        continue
    original = ORIGINALS / current.name
    before = ''
    if original.exists():
        svg = inline(original.read_text(), "-b", "Original illustration of " + el["name"])
        before = f'<figure>{svg}<figcaption>Before</figcaption></figure>'
    after = current.read_text()
    bg, deep = FAMILY[el['family']]
    acc = THEME[el['theme']]
    style = f'--fam:{bg};--fam-deep:{deep};--pic-accent:{acc}'
    name = escape(el['name'])
    cards.append(f'<section class="card" id="{sym}" style="{style}">'
                 f'<h2>{name} <span>{escape(el["sym"])}</span></h2>'
                 f'<div class="two">{before}'
                 f'<figure>{inline(after, "-a", el["picture_alt"])}<figcaption>Current</figcaption></figure></div>'
                 f'<p>{escape(el["def"])}</p><button type="button" aria-label="Present {name}">Present</button></section>')

css = """:root{--ink:#231F20;--cream:#F6F4ED;--dim:#54504E}*{box-sizing:border-box}
body{margin:0;background:var(--cream);color:var(--ink);font:15px/1.45 "AIAD Sans",system-ui,-apple-system,"Segoe UI",sans-serif}
main{max-width:1240px;margin:0 auto;padding:32px 16px 64px}h1{font-size:2rem;margin:0 0 .4rem}.lead{margin:0 0 1.5rem;max-width:46rem}
.grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(min(100%,360px),1fr))}
.card{background:#fff;border:2px solid var(--ink);padding:10px}.card h2{font-size:1rem;margin:0 0 .5rem}.card h2 span{font-weight:500;color:var(--dim);font-size:.85rem}
.two{display:grid;grid-template-columns:1fr 1fr;gap:8px}figure{margin:0;min-width:0}.two>figure:only-child{grid-column:1/-1;width:min(100%,300px);justify-self:center}figure svg{display:block;width:100%;height:auto}
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
css += """
button{font:inherit;cursor:pointer;padding:.35em .8em}
dialog{width:min(95vw,940px);max-height:96dvh;padding:1rem;background:var(--cream);border:2px solid var(--ink)}
dialog::backdrop{background:#161417cf}dialog nav{display:flex;gap:.5rem;align-items:center}
dialog nav span{margin-right:auto}dialog article{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:center;gap:1rem}
dialog h2{font-size:clamp(1.5rem,3vw,2.5rem);line-height:1.1;margin:.6em 0 .3em}dialog h2 span{font-size:.55em;color:var(--dim);vertical-align:middle}dialog svg{display:block;width:100%;max-height:76dvh}dialog p{font-size:clamp(1rem,2vw,1.5rem)}
@media(max-width:600px){dialog article{display:block}dialog svg{max-height:48dvh}dialog nav{flex-wrap:wrap}}
"""
viewer = r'''<dialog aria-labelledby="slide-title"><nav><span></span><button data-step="-1">Previous</button>
<button data-step="1">Next</button><button id="close">Close</button></nav><article></article></dialog>
<script>
const cards = [...document.querySelectorAll('.card')];
const dialog = document.querySelector('dialog');
let index = 0;
function show(i) {
  index = i;
  const card = cards[i];
  const copy = document.createElement('div');
  const title = card.querySelector('h2').cloneNode(true);
  title.id = 'slide-title';
  copy.append(title, card.querySelector('p').cloneNode(true));
  const svg = card.querySelector('figure:last-child svg').cloneNode(true);
  for (const marker of svg.querySelectorAll('[id]')) marker.id += '-slide';
  for (const node of svg.querySelectorAll('*')) {
    for (const attr of [...node.attributes]) {
      if (attr.value.includes('url(#')) node.setAttribute(attr.name, attr.value.replace(/url\(#([^)]+)\)/g, 'url(#$1-slide)'));
    }
  }
  dialog.style.cssText = card.style.cssText;
  dialog.querySelector('article').replaceChildren(svg,copy);
  dialog.querySelector('nav span').textContent = (i+1)+' / '+cards.length;
  for (const btn of dialog.querySelectorAll('[data-step]')) btn.disabled = !cards[i+Number(btn.dataset.step)];
}
cards.forEach((card,i) => card.querySelector('button').onclick = () => { show(i); dialog.showModal(); });
dialog.querySelector('#close').onclick = () => dialog.close();
dialog.querySelectorAll('[data-step]').forEach(btn => btn.onclick = () => show(index+Number(btn.dataset.step)));
dialog.onkeydown = e => {
  const step = {ArrowLeft:-1, ArrowRight:1}[e.key];
  if (step && cards[index+step]) { e.preventDefault(); show(index+step); }
};
</script>'''
html = (f'<!doctype html><html lang="en-GB"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
        f'<title>AI literacy illustrations</title><style>{css}</style></head><body><main><h1>AI literacy illustrations</h1>'
        f'<p class="lead">{len(cards)} concepts. Compare the original and current pictures, or select Present for a larger view.</p>'
        f'<div class="grid">{"".join(cards)}</div></main>{viewer}</body></html>')
out = ROOT / 'archive/arrow-style-trial/all-pictures.html'
out.write_text('\n'.join(line.rstrip() for line in html.splitlines()) + '\n')
print(out, len(cards))
