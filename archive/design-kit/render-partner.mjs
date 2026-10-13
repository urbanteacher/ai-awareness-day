import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const require = createRequire(process.env.PW + '/x.cjs');
const { chromium } = require('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [LI, key = 'ailitkit'] = process.argv.slice(2);
const cards = JSON.parse(fs.readFileSync(path.join(here, 'partner-cards.json'), 'utf8')), card = cards[key];
if (!card) throw new Error(`no "${key}" in partner-cards.json (have: ${Object.keys(cards).join(', ')})`);
fs.mkdirSync(LI, { recursive: true });
fs.writeFileSync(path.join(here, 'partner.built.html'), fs.readFileSync(path.join(here, 'ig-partner.html'), 'utf8')
  .replaceAll('PARTNER_NAME', card.name).replaceAll('PARTNER_LOGO', card.logo).replaceAll('PARTNER_LEAD', card.lead).replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage({ viewport: { width: 700, height: 700 }, deviceScaleFactor: 2 });
await p.goto(pathToFileURL(path.join(here, 'partner.built.html')).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.locator('#li').screenshot({ path: path.join(LI, card.file) });
await b.close(); fs.rmSync(path.join(here, 'partner.built.html')); console.log(card.file);
