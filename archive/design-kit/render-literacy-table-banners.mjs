// "Introducing the AI Literacy Periodic Table" banners: PW=<folder containing node_modules/playwright> node render-literacy-table-banners.mjs <banner folder>
// Four transparent banners at 1200×600 (2×) into the banner folder, and literacy-table-banners-sheet.png beside this file.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const require = createRequire(process.env.PW + '/x.cjs');
const { chromium } = require('playwright');
const here = path.dirname(fileURLToPath(import.meta.url));
const THEME = path.resolve(here, '../..');
const [OUT] = process.argv.slice(2); fs.mkdirSync(OUT, { recursive: true });
const built = path.join(here, 'literacy-table-banners.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'literacy-table-banners.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const names = { l1: '01-national-conversation', l2: '02-benchmark-revision', l3: '03-display-board', l4: '04-show-to-class' };
const b = await chromium.launch(process.env.CHROME ? { executablePath: process.env.CHROME } : {});
const p = await b.newPage({ viewport: { width: 660, height: 800 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' });
await p.evaluate(() => document.fonts.ready);
await p.screenshot({ path: path.join(here, 'literacy-table-banners-sheet.png'), fullPage: true });
await p.addStyleTag({ content: 'html,body{background:transparent!important}' });
for (const [id, name] of Object.entries(names)) await p.locator('#' + id).screenshot({ path: path.join(OUT, name + '.png'), omitBackground: true });
await b.close();
fs.rmSync(built);
