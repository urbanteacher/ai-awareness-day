#!/usr/bin/env node
/**
 * Export the AiAd27 LinkedIn card set: an opening brand card, five strand
 * starters, and a contact sheet of all six.
 *
 * The starters come out of assets/aiad27-review/preview.html rather than a
 * manual crop, so the export always matches whatever is in the review bundle.
 *
 * Slides are authored at 1280x720 and the review grid shows them through a
 * `transform: scale(clientWidth/1280)`. Screenshotting a tile therefore
 * captures a fractionally-scaled box, which bled ~5px of page background and
 * drop-shadow into the bottom edge of every card. So each slide is cloned into
 * a clean fixed-position host at scale 1 and captured there: pixel-exact 16:9,
 * no neighbours, and 2x the resolution.
 *
 * Usage: node scripts/export-aiad27-cards.mjs [outDir] [--url=<preview-url>]
 *
 * Needs Playwright. Either `npm install playwright` somewhere on this machine
 * and point AIAD_PLAYWRIGHT_DIR at that directory, or run the script from a
 * tree that already has it.
 */
import path from 'node:path';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const args = process.argv.slice(2);
/* Default the output at the theme rather than at the shell's cwd, so the run
   lands in the same place from wherever it is started. An explicit outDir
   argument is still taken as given, relative to cwd. */
const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const outDir =
	args.find((a) => !a.startsWith('--')) || path.join(themeDir, 'assets/aiad27-review/linkedin');
const urlArg = args.find((a) => a.startsWith('--url='));
const BASE = 'http://localhost:8888/wp-content/themes/ai-awareness-day/assets/aiad27-review';
const PREVIEW_URL = urlArg ? urlArg.slice('--url='.length) : `${BASE}/preview.html`;
const COVER_URL = `${BASE}/linkedin-cover.html`;
const SHEET_URL = `${BASE}/linkedin-sheet.html`;

/* Slides render at this size; capture them there and scale down in the sheet. */
const SLIDE_W = 1280;
const SLIDE_H = 720;

const chromium = await loadChromium();

async function loadChromium() {
	try {
		return (await import('playwright')).chromium;
	} catch (err) {
		const dir = process.env.AIAD_PLAYWRIGHT_DIR;
		if (!dir) {
			throw new Error(
				'Playwright not found. Set AIAD_PLAYWRIGHT_DIR to a directory that has it installed, or npm install playwright here.'
			);
		}
		return createRequire(path.join(dir, 'resolve-from-here.cjs'))('playwright').chromium;
	}
}

/* Matched on the slide's own visible text so the export survives the slides
   being reordered or renumbered in the bundle. */
const starters = [
	{ text: 'Would you tell an AI your secret?', file: '2-safe.png' },
	{ text: 'What happens when AI acts for you?', file: '3-smart.png' },
	{ text: 'Who really made it?', file: '4-creative.png' },
	{ text: 'Should AI decide?', file: '5-responsible.png' },
	{ text: 'What skills must stay human?', file: '6-future.png' },
];

fs.mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch();
const failures = [];

/* --- the opening brand card --- */
const coverPage = await browser.newPage({
	viewport: { width: SLIDE_W, height: SLIDE_H },
	deviceScaleFactor: 2,
});
await coverPage.goto(COVER_URL, { waitUntil: 'networkidle' });
await coverPage.waitForTimeout(300);
await (await coverPage.$('.card')).screenshot({ path: path.join(outDir, '1-lockup.png') });
console.log('OK    1-lockup.png');
await coverPage.close();

/* --- the five strand starters, out of the review page --- */
const page = await browser.newPage({
	viewport: { width: SLIDE_W + 120, height: SLIDE_H + 180 },
	deviceScaleFactor: 2,
});
const httpErrors = [];
page.on('response', (r) => {
	if (r.status() >= 400) httpErrors.push(`${r.status()} ${r.url()}`);
});
await page.goto(PREVIEW_URL, { waitUntil: 'networkidle' });
await page.waitForSelector('.slide');
await page.waitForTimeout(600);

await page.evaluate(
	({ w, h }) => {
		const host = document.createElement('div');
		host.id = 'sf-export-host';
		Object.assign(host.style, {
			position: 'fixed',
			left: '0',
			top: '0',
			width: `${w}px`,
			height: `${h}px`,
			overflow: 'hidden',
			zIndex: '2147483647',
		});
		document.body.appendChild(host);
	},
	{ w: SLIDE_W, h: SLIDE_H }
);

for (const item of starters) {
	const staged = await page.evaluate((text) => {
		const leaf = [...document.querySelectorAll('.slide *')].find(
			(e) => e.children.length === 0 && e.textContent.trim() === text
		);
		let node = leaf;
		while (node && !(node.classList && node.classList.contains('slide'))) node = node.parentElement;
		if (!node) return false;
		const clone = node.cloneNode(true);
		clone.style.transform = 'none';
		clone.style.margin = '0';
		document.getElementById('sf-export-host').replaceChildren(clone);
		return true;
	}, item.text);

	if (!staged) {
		failures.push(`no slide found for "${item.text}"`);
		console.log(`MISS  ${item.file} — "${item.text}"`);
		continue;
	}
	await page.waitForTimeout(120);
	await (await page.$('#sf-export-host')).screenshot({ path: path.join(outDir, item.file) });
	console.log(`OK    ${item.file}`);
}
await page.close();

/* --- the contact sheet, which reads the cards written above --- */
const sheetPage = await browser.newPage({
	viewport: { width: 1200, height: 1200 },
	deviceScaleFactor: 1,
});
await sheetPage.goto(SHEET_URL, { waitUntil: 'networkidle' });
await sheetPage.waitForTimeout(300);
await (await sheetPage.$('.sheet')).screenshot({
	path: path.join(outDir, '0-all-six-1200x1200.png'),
});
console.log('OK    0-all-six-1200x1200.png');
await sheetPage.close();

await browser.close();

if (httpErrors.length) {
	console.log('\nLoad errors on the review page:');
	console.log([...new Set(httpErrors)].join('\n'));
}
if (failures.length) {
	console.log('\nFailures:');
	console.log(failures.join('\n'));
	process.exitCode = 1;
}
