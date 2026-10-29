// Renders ig-benchmark-pdf.html to a four-page A4 PDF: node render-benchmark-pdf.mjs <out-file.pdf>, with PW set to a folder that has Playwright.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { pathToFileURL, fileURLToPath } from 'node:url';
const { chromium } = createRequire(process.env.PW + '/x.cjs')('playwright');
const here = path.dirname(fileURLToPath(import.meta.url)), THEME = path.resolve(here, '../..');
const [OUT] = process.argv.slice(2); fs.mkdirSync(path.dirname(OUT), { recursive: true });
const built = path.join(here, 'benchmark-pdf.built.html');
fs.writeFileSync(built, fs.readFileSync(path.join(here, 'ig-benchmark-pdf.html'), 'utf8').replaceAll('THEME', pathToFileURL(THEME).href));
const b = await chromium.launch(); const p = await b.newPage();
await p.goto(pathToFileURL(built).href, { waitUntil: 'networkidle' }); await p.evaluate(() => document.fonts.ready);
await p.pdf({ path: OUT, width: '210mm', height: '297mm', printBackground: true, margin: { top: 0, right: 0, bottom: 0, left: 0 } });
await b.close(); fs.rmSync(built); console.log(OUT);
