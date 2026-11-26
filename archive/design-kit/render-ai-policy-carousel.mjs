// AI policy LinkedIn carousel: PW=<folder containing node_modules/playwright> node render-ai-policy-carousel.mjs <out folder>
// Writes ai-policy-linkedin-carousel.pdf (4 pages, 1080×1350) and slide-1..4.png from ai-policy-linkedin-carousel.html.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'ai-policy-carousel.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ai-policy-linkedin-carousel.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 1080, height: 1350 } });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
for (let i = 1; i <= 4; i++) await p.locator('#s' + i).screenshot({ path: path.join(OUT, `slide-${i}.png`) });
await p.pdf({ path: path.join(OUT, 'ai-policy-linkedin-carousel.pdf'), preferCSSPageSize: true, printBackground: true });
await b.close(); fs.rmSync(built);
