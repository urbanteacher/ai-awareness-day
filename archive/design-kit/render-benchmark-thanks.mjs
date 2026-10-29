// Renders ig-benchmark-thanks.html: a LinkedIn square (1200×1200) and an Instagram portrait (1080×1350). node render-benchmark-cards.mjs <linkedin-dir> <instagram-dir>, with PW set to a folder that has Playwright.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [LI, IG] = process.argv.slice(2); fs.mkdirSync(LI, { recursive: true }); fs.mkdirSync(IG, { recursive: true });
const built = path.join(here, 'benchmark-thanks.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ig-benchmark-thanks.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 700, height: 800 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.locator('#li').screenshot({ path: path.join(LI, 'ai-audit-thanks-1200x1200.png') });
await p.locator('#ig').screenshot({ path: path.join(IG, 'ai-audit-thanks-1080x1350.png') });
await b.close(); fs.rmSync(built); console.log('2 images');
