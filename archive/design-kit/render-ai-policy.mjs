// AI policy comparison post images: PW=<folder containing node_modules/playwright> node render-ai-policy.mjs <image folder> <cover.jpg>
// Writes policy-grid.png, learner-journey.png and reading-list-residue.png (1440 wide, 2×, transparent corners) into the image folder and #cover from
// ai-policy.html as a 1500×1000 JPEG.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT, COVER] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'ai-policy.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ai-policy.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 840, height: 1000 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.locator('#cover').screenshot({ path: COVER, type: 'jpeg', quality: 88 });
await p.addStyleTag({ content: 'html,body{background:transparent!important}' });
await p.locator('#grid').screenshot({ path: path.join(OUT, 'policy-grid.png'), omitBackground: true });
await p.locator('#journey').screenshot({ path: path.join(OUT, 'learner-journey.png'), omitBackground: true });
await p.locator('#residue').screenshot({ path: path.join(OUT, 'reading-list-residue.png'), omitBackground: true });
await b.close(); fs.rmSync(built);
