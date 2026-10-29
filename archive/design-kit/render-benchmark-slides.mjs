// Renders every slide of an Instagram carousel page: node render-benchmark-slides.mjs <out-dir> [page.html] → slide-01.png … (1080×1350), with PW set to a folder that has Playwright. The page defaults to ig-benchmark-slides.html; ig-benchmark-slides-v1-dense.html is the first, denser version.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT, PAGE = 'ig-benchmark-slides.html'] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'benchmark-slides.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, PAGE), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 700, height: 800 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
const slides = await p.locator('section.s').all();
for (let i = 0; i < slides.length; i++) await slides[i].screenshot({ path: path.join(OUT, `slide-${String(i + 1).padStart(2, '0')}.png`) });
await b.close(); fs.rmSync(built); console.log(slides.length + ' slides');
