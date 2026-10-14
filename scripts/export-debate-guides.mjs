#!/usr/bin/env node
/**
 * Print the "How to run a debate" guides (primary, secondary, post-16) to PDF,
 * into plugins/aiad-debate-network/downloads/, where /conversation/guide/ serves
 * them to signed-in teachers and judges.
 *
 * Run it after changing the guide (class-aiadn-guide-front.php) or the running
 * order (class-aiadn-format.php), with the local site running (docker compose up):
 *
 *   node scripts/export-debate-guides.mjs
 *
 * Needs Playwright: either `npm install playwright` somewhere on this machine
 * and point AIAD_PLAYWRIGHT_DIR at that directory, or run the script from a
 * tree that already has it.
 */
import fs from 'node:fs';
import path from 'node:path';
import { execFileSync } from 'node:child_process';
import { createRequire } from 'node:module';
import { fileURLToPath, pathToFileURL } from 'node:url';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const outDir = path.join(themeDir, 'plugins/aiad-debate-network/downloads');
// Inside the theme, so the container (which mounts the theme) can write it and the relative font URLs resolve.
const buildDir = path.join(themeDir, 'scripts/.debate-guides');
fs.mkdirSync(buildDir, { recursive: true });

execFileSync('docker', ['compose', 'exec', '-T', 'wordpress', 'php',
	'wp-content/themes/ai-awareness-day/scripts/build-debate-guides.php',
	'wp-content/themes/ai-awareness-day/scripts/.debate-guides'], { cwd: themeDir, stdio: 'inherit' });

const chromium = await loadChromium();
const browser = await chromium.launch();
const page = await browser.newPage();
for (const age of ['primary', 'secondary', 'post16']) {
	const html = path.join(buildDir, `debate-guide-${age}.html`);
	const pdf = path.join(outDir, `debate-guide-${age}.pdf`);
	await page.goto(pathToFileURL(html).href, { waitUntil: 'networkidle' });
	await page.evaluate(() => document.fonts.ready);
	const faces = await page.evaluate(() => [...document.fonts].filter((f) => f.status === 'loaded').length);
	if (!faces) throw new Error(`AIAD Sans did not load for ${age}.`);
	await page.emulateMedia({ media: 'print' });
	await page.pdf({
		path: pdf, format: 'A4', printBackground: true, preferCSSPageSize: true,
		displayHeaderFooter: true, headerTemplate: '<span></span>',
		footerTemplate: '<div style="width:100%;font:8px Arial,sans-serif;color:#54504E;padding:0 14mm;display:flex;justify-content:space-between"><span>AI Awareness Day 2027 · National AI Conversation · How to run a debate</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>',
	});
	console.log(`${path.relative(themeDir, pdf)}  ${(fs.statSync(pdf).size / 1024).toFixed(0)} KB`);
}
await browser.close();
fs.rmSync(buildDir, { recursive: true, force: true });

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
