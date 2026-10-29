// Renders the carousel page as a swipeable PDF with one 4:5 page per slide (for a LinkedIn document post): node render-benchmark-slides-pdf.mjs <out.pdf> [page.html], with PW set to a folder that has Playwright.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT, PAGE = 'ig-benchmark-slides.html'] = process.argv.slice(2); fs.mkdirSync(path.dirname(OUT), { recursive: true });
const built = path.join(here, 'benchmark-slides-pdf.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, PAGE), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage();
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.addStyleTag({ content: '@page { size: 540px 675px; margin: 0 } html, body { width: 540px; margin: 0; padding: 0; display: block; gap: 0; background: #fff } section.s { margin: 0 !important; break-after: page; page-break-after: always } section.s:last-of-type { break-after: auto; page-break-after: auto }' });
await p.pdf({ path: OUT, width: '540px', height: '675px', printBackground: true, margin: { top: 0, right: 0, bottom: 0, left: 0 } });
await b.close(); fs.rmSync(built); console.log(OUT);
