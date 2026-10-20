// Renders ig-article-bottleneck-card.html to a text-free 1500×1000 JPEG: node render-article-card.mjs <out-dir>, with PW set to a folder that has Playwright.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'article-bottleneck.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ig-article-bottleneck-card.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 750, height: 500 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.locator('#cv').screenshot({ path: path.join(OUT, 'human-bottleneck-card.jpg'), type: 'jpeg', quality: 90 });
await b.close(); fs.rmSync(built); console.log('human-bottleneck-card.jpg');
