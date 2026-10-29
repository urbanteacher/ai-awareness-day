// Renders ig-benchmark-slides.html: node render-benchmark-slides.mjs <out-dir> → slide-1.png … slide-8.png (1080×1350), with PW set to a folder that has Playwright.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'benchmark-slides.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ig-benchmark-slides.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 700, height: 800 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
for (let i = 1; i <= 8; i++) await p.locator('#b' + i).screenshot({ path: path.join(OUT, `slide-${i}.png`) });
await b.close(); fs.rmSync(built); console.log('8 slides');
