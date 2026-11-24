// Copies the still illustrations from the AI Under the Hood courses (assets/courses/*/index.html, their figure.ill
// SVGs) into plugins/aiad-core/assets/data/literacy-table-stills.json (index: data/literacy-table/stills.json), so the AI Literacy Periodic Table can show them under
// "How it works", after the element's animation (if it has one). See docs/COURSE-ILLUSTRATIONS-AUDIT.md.
//
// Each SVG is styled by its course page's CSS, so the page is opened in a headless browser and every element's
// computed style (where it differs from a plain SVG element's) is written onto it; ids are prefixed so several can
// share a page. STILLS below names each illustration by its <title> and the element it explains.
//
//   PW=<folder with node_modules/playwright> node scripts/build-literacy-stills.mjs
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const ROOT = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');

const COURSES = {
  'sand-to-sentence': { title: 'From Sand to Sentence', part: 1 },
  'answer-to-action': { title: 'From Answer to Action', part: 2 },
  'agent-to-team': { title: 'From Agent to Team', part: 3 },
};
// [course, the illustration's <title>, element symbol, and a caption to use instead of the course's where that
// refers to the course itself ("this course", "the calculator")]. Left out: "The prompt fills up, chapter by chapter" and
// "Group-project habits are design patterns", whose drawings label the course's chapters.
const STILLS = [
  ['sand-to-sentence', 'A prompt is an instruction plus context', 'Pr'],
  ['sand-to-sentence', 'A sentence cut into tokens', 'Tk'],
  ['sand-to-sentence', 'The context window fills up', 'Cw'],
  ['sand-to-sentence', 'Token numbers versus embeddings', 'Em'],
  ['sand-to-sentence', 'Directions in latent space carry meaning', 'Ls'],
  ['sand-to-sentence', 'Model serving groups questions together', 'Ms'],
  ['sand-to-sentence', "Your question's journey to a data centre", 'Cd'],
  ['sand-to-sentence', 'Multiplying a matrix: many separate sums', 'Gp'],
  ['sand-to-sentence', 'Quantisation: fewer bits, rounded numbers', 'Qz'],
  ['sand-to-sentence', 'Who builds an AI chip', 'So'],
  ['sand-to-sentence', 'Attention: "blue" looks back at the words before it', 'Tf'],
  ['sand-to-sentence', 'The KV cache: notes that grow with every token', 'If'],
  ['sand-to-sentence', 'Training: guess, check, nudge, repeat', 'Tr'],
  ['sand-to-sentence', 'One model, many sources: DeepSeek-V3', 'So'],
  ['sand-to-sentence', 'Closed models and open-weight models', 'Ow'],
  ['sand-to-sentence', 'Distillation: a small student learns from a big teacher', 'Ds'],
  ['sand-to-sentence', 'A hallucination looks just as sure as a fact', 'Ha'],
  ['sand-to-sentence', 'Cloud AI and edge AI', 'Ed'],
  ['answer-to-action', 'A language model on its own has two limits', 'Cb'],
  ['answer-to-action', 'What the model actually reads', 'Pe', 'The app wraps your words in hidden instructions and the chat so far. Everything else is added to this same prompt: RAG adds pages, tools add their results, and a prompt injection can sneak in its own instructions.'],
  ['answer-to-action', 'A vector database finds the chunks nearest your question', 'Vd'],
  ['answer-to-action', 'What RAG adds to the prompt', 'Rg'],
  ['answer-to-action', 'A tool description and the request the model writes', 'Fn'],
  ['answer-to-action', 'An API is a door with a menu', 'Ap'],
  ['answer-to-action', 'Why a standard saves work', 'Mc'],
  ['answer-to-action', 'What one MCP server offers', 'Mc'],
  ['answer-to-action', 'Planning breaks a goal into ordered steps', 'Pl', 'Planning turns one goal into ordered steps, each using a different piece of the system. The agent loop runs each one: think, act, observe, then on to the next.'],
  ['answer-to-action', 'Two kinds of memory', 'Me'],
  ['answer-to-action', 'A prompt injection hides in plain sight', 'Jb'],
  ['answer-to-action', 'Autonomy is a dial, with layers of defence', 'Au'],
  ['answer-to-action', 'One assistant, three pieces', 'Ac'],
  ['agent-to-team', 'Three workflow shapes', 'Wo'],
  ['agent-to-team', 'What each check has to go on', 'Ev'],
  ['agent-to-team', 'Fixed jobs, or jobs decided as it goes', 'Or'],
  ['agent-to-team', 'Attention fades with every request', 'Hi', 'Made-up numbers: you start by catching 9 in 10 mistakes, and catch 3 percentage points fewer with every request. Real attention varies, but the pattern is well documented.'],
  ['agent-to-team', 'Who decides the steps?', 'Ac'],
];

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1200, height: 900 } });
const stills = {}, elements = {};
let n = 0;
for (const [slug, info] of Object.entries(COURSES)) {
  const wanted = STILLS.filter(s => s[0] === slug);
  await page.goto(pathToFileURL(path.join(ROOT, 'assets/courses', slug, 'index.html')).href, { waitUntil: 'load' });
  await page.evaluate(() => document.fonts.ready);
  const found = await page.evaluate(titles => {
    const PROPS = ['fill', 'fill-opacity', 'stroke', 'stroke-width', 'stroke-opacity', 'stroke-dasharray', 'stroke-linecap',
      'stroke-linejoin', 'opacity', 'font-size', 'font-weight', 'font-style', 'letter-spacing', 'text-anchor',
      'dominant-baseline', 'text-transform', 'paint-order'];
    // A plain SVG to compare against: only what the page's CSS changed is written onto each element.
    const probe = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    probe.style.cssText = 'position:absolute;left:-9999px;top:0';
    document.body.appendChild(probe);
    const plain = {};
    const base = tag => {
      if (!plain[tag]) {
        const e = document.createElementNS('http://www.w3.org/2000/svg', tag);
        probe.appendChild(e);
        const cs = getComputedStyle(e);
        plain[tag] = Object.fromEntries(PROPS.map(p => [p, cs.getPropertyValue(p)]));
      }
      return plain[tag];
    };
    const out = {};
    document.querySelectorAll('section.chapter figure').forEach(fig => {
      const svg = fig.querySelector(':scope > svg');
      const title = svg && svg.querySelector('title');
      if (!svg || !title || !titles.includes(title.textContent.trim())) return;
      const section = fig.closest('section.chapter');
      const clone = svg.cloneNode(true);
      const src = [svg, ...svg.querySelectorAll('*')], dst = [clone, ...clone.querySelectorAll('*')];
      src.forEach((el, i) => {
        if (i === 0) return;
        const cs = getComputedStyle(el), b = base(el.tagName), style = [];
        PROPS.forEach(p => { const v = cs.getPropertyValue(p); if (v && v !== b[p]) style.push(p + ':' + v); });
        dst[i].removeAttribute('class');
        if (style.length) dst[i].setAttribute('style', style.join(';'));
      });
      clone.removeAttribute('class');
      clone.setAttribute('width', '100%');
      clone.removeAttribute('height');
      const cap = fig.querySelector('figcaption');
      const h2 = section.querySelector('h2');
      out[title.textContent.trim()] = {
        chapter: section.id, chapterTitle: h2 ? h2.textContent.trim().replace(/\s+/g, ' ') : '',
        caption: cap ? cap.textContent.trim().replace(/\s+/g, ' ') : '', svg: clone.outerHTML,
      };
    });
    return out;
  }, wanted.map(s => s[1]));
  for (const [, title, sym, caption] of wanted) {
    const f = found[title];
    if (!f) throw new Error(`Not found in ${slug}: ${title}`);
    const id = 's' + (++n);
    // Prefix ids (and what points at them) so several illustrations can sit on one page.
    const svg = f.svg.replace(/\bid="([^"]+)"/g, `id="${id}-$1"`).replace(/url\(#([^)"']+)\)/g, `url(#${id}-$1)`)
      .replace(/url\(&quot;#([^&]+)&quot;\)/g, `url(#${id}-$1)`)
      .replace(/(href|xlink:href)="#([^"]+)"/g, `$1="#${id}-$2"`)
      .replace(/aria-labelledby="([^"]+)"/g, (m, v) => `aria-labelledby="${v.split(/\s+/).map(x => id + '-' + x).join(' ')}"`);
    stills[id] = { course: slug, courseTitle: info.title, part: info.part, chapter: f.chapter,
      chapterNumber: Number(f.chapter.replace('ch', '')), chapterTitle: f.chapterTitle, title, caption: caption || f.caption, svg };
    (elements[sym] = elements[sym] || []).push(id);
  }
}
await browser.close();

// The table's own illustrations (drawn by scripts/draw-literacy-illustrations.py for words the courses don't cover):
// already self-contained SVGs, credited to the table rather than a course.
const OWN = path.join(ROOT, 'plugins/aiad-core/data/literacy-table/illustrations');
if (fs.existsSync(path.join(OWN, 'illustrations.json'))) {
  const own = JSON.parse(fs.readFileSync(path.join(OWN, 'illustrations.json'), 'utf8'));
  for (const [sym, info] of Object.entries(own)) {
    const id = 's' + (++n);
    const svg = fs.readFileSync(path.join(OWN, sym + '.svg'), 'utf8')
      .replace(/\bid="([^"]+)"/g, `id="${id}-$1"`).replace(/url\(#([^)"']+)\)/g, `url(#${id}-$1)`)
      .replace(/aria-labelledby="([^"]+)"/g, (m, v) => `aria-labelledby="${v.split(/\s+/).map(x => id + '-' + x).join(' ')}"`);
    stills[id] = { course: null, title: info.title, caption: info.caption, svg };
    (elements[sym] = elements[sym] || []).push(id);
  }
}
// Two files: a small index the page carries (which elements have illustrations, and their titles), and the drawings
// themselves, which the table fetches only when How it works is first opened.
const about = 'Generated by scripts/build-literacy-stills.mjs from the AI Under the Hood courses. Do not edit by hand.';
const index = { about, elements, stills: Object.fromEntries(Object.entries(stills).map(([id, st]) => [id, { title: st.title }])) };
const indexFile = path.join(ROOT, 'plugins/aiad-core/data/literacy-table/stills.json');
const fullFile = path.join(ROOT, 'plugins/aiad-core/assets/data/literacy-table-stills.json');
fs.mkdirSync(path.dirname(fullFile), { recursive: true });
fs.writeFileSync(indexFile, JSON.stringify(index, null, 1));
fs.writeFileSync(fullFile, JSON.stringify({ about, stills }));
console.log(`${Object.keys(stills).length} illustrations for ${Object.keys(elements).length} elements: ${path.relative(ROOT, indexFile)} (${Math.round(fs.statSync(indexFile).size / 1024)} KB), ${path.relative(ROOT, fullFile)} (${Math.round(fs.statSync(fullFile).size / 1024)} KB)`);
