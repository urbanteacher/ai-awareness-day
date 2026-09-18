#!/usr/bin/env node
/**
 * Export individual AiAd27 slide cards as PNGs for social sharing.
 *
 * Screenshots the live `.slide` element for each requested starter question
 * out of assets/aiad27-review/preview.html, rather than a manual crop, so
 * the export always matches whatever is currently in the review bundle.
 *
 * Usage: node scripts/export-aiad27-cards.mjs [outDir] [--url=<preview-url>]
 * Requires Playwright — run from a machine with SlideForge's node_modules,
 * or `npm install playwright` locally first.
 */
import { chromium } from 'playwright';
import path from 'node:path';
import fs from 'node:fs';

const args = process.argv.slice(2);
const outDir = args.find((a) => !a.startsWith('--')) || 'assets/aiad27-review/linkedin';
const urlArg = args.find((a) => a.startsWith('--url='));
const URL = urlArg
	? urlArg.slice('--url='.length)
	: 'http://localhost:8888/wp-content/themes/ai-awareness-day/assets/aiad27-review/preview.html';

fs.mkdirSync(outDir, { recursive: true });

const wanted = [
	{ strand: 'Safe', text: 'Would you tell an AI your secret?', file: '1-safe.png' },
	{ strand: 'Smart', text: 'What happens when AI acts for you?', file: '2-smart.png' },
	{ strand: 'Creative', text: 'Who really made it?', file: '3-creative.png' },
	{ strand: 'Responsible', text: 'Should AI decide?', file: '4-responsible.png' },
	{ strand: 'Future', text: 'What skills must stay human?', file: '5-future.png' },
	{
		strand: 'Safe',
		text: 'Where does a secret go when you tell it to something that cannot keep one?',
		file: '6-safe-04.png',
	},
];

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 1400, height: 1000 }, deviceScaleFactor: 2 });
const httpErrors = [];
page.on('response', (r) => {
	if (r.status() >= 400) httpErrors.push(`${r.status()} ${r.url()}`);
});
await page.goto(URL, { waitUntil: 'networkidle' });
await page.waitForTimeout(600);

for (const item of wanted) {
	const handle = await page.evaluateHandle((text) => {
		const el = [...document.querySelectorAll('*')].find(
			(e) => e.textContent.trim() === text && e.children.length === 0
		);
		if (!el) return null;
		let node = el;
		while (node && !(node.classList && node.classList.contains('slide'))) node = node.parentElement;
		return node;
	}, item.text);
	const el = handle.asElement();
	if (!el) {
		console.log(`MISS  ${item.strand} — "${item.text}"`);
		continue;
	}
	await el.scrollIntoViewIfNeeded();
	await page.waitForTimeout(150);
	await el.screenshot({ path: path.join(outDir, item.file) });
	console.log(`OK    ${item.strand} -> ${item.file}`);
}

if (httpErrors.length) {
	console.log('Load errors (check assets/aiad27-review is fully synced):');
	console.log([...new Set(httpErrors)].join('\n'));
}
await browser.close();
