// November 2026 newsletter banners: PW=<playwright dir> node render-nov-banners.mjs <out folder>
// Transparent PNGs at 1200×600 (2×), so dark-mode email apps show no light corners. Also writes nov-banners-sheet.png.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const require = createRequire(process.env.PW + '/x.cjs');
const { chromium } = require('playwright');
const here = path.dirname(fileURLToPath(import.meta.url));
const THEME = path.resolve(here, '../..');
const OUT = process.argv[2]; fs.mkdirSync(OUT, { recursive: true });
fs.writeFileSync(path.join(here, 'nov-banners.built.html'), fs.readFileSync(path.join(here, 'nov-banners.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const names = { n01: '01-oracy-is-back', n02: '02-bbc-bitesize-calendar', n03: '03-free-ai-audit', n04: '04-lesson-starters', n05: '05-new-partners', n06: '06-human-bottleneck', n07: '07-debate-pilots', n08: '08-instagram', n09: '09-coming-up', n10: '10-dessa-classroom-debate', n11: '11-eight-skills', n12: '12-partner-with-us' };
const b = await chromium.launch(process.env.CHROME ? { executablePath: process.env.CHROME } : {});
const p = await b.newPage({ viewport: { width: 660, height: 800 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(path.join(here, 'nov-banners.built.html')).href, { waitUntil: 'networkidle' });
await p.evaluate(() => document.fonts.ready);
await p.screenshot({ path: path.join(here, 'nov-banners-sheet.png'), fullPage: true });
await p.addStyleTag({ content: 'html,body{background:transparent!important}' });
for (const [id, name] of Object.entries(names)) await p.locator('#' + id).screenshot({ path: path.join(OUT, name + '.png'), omitBackground: true });
await p.addStyleTag({ content: '#cover{clip-path:none}' });
await p.locator('#cover').screenshot({ path: path.join(OUT, '00-website-cover.jpg'), type: 'jpeg', quality: 88 });
await b.close();
fs.rmSync(path.join(here, 'nov-banners.built.html'));
