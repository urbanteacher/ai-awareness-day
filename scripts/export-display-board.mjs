#!/usr/bin/env node
/**
 * Rasterise the display board SVG into a print PNG next to it.
 *
 * The site shows the SVG itself: it is vector, 56KB, and carries its own
 * subset of AIAD Sans. The PNG is for everyone the SVG does not reach: Word,
 * PowerPoint, a print shop. It is captured at 2x (4800x3200), so an A1 print
 * (841mm wide) comes out at about 145dpi.
 *
 * The SVG is opened on its own, not inlined into a host page, so the capture
 * uses only the font embedded in the file. If that embed were broken, the PNG
 * would show Arial rather than hiding the fault.
 *
 * Usage: node scripts/export-display-board.mjs
 *
 * Run after scripts/build-display-board.py and scripts/embed-brand-font.py.
 * Needs Playwright: either `npm install playwright` somewhere on this machine
 * and point AIAD_PLAYWRIGHT_DIR at that directory, or run the script from a
 * tree that already has it.
 */
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const svg = path.join(themeDir, 'assets/images/display-board/aiad27-display-board.svg');
const png = svg.replace(/\.svg$/, '.png');

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

const browser = await chromium.launch();
const page = await browser.newPage({ viewport: { width: 2400, height: 1600 }, deviceScaleFactor: 2 });
await page.goto('file://' + svg);
await page.evaluate(() => document.fonts.ready);
const faces = await page.evaluate(() => [...document.fonts].filter((f) => f.status === 'loaded').length);
if (!faces) throw new Error('No embedded face loaded. Run scripts/embed-brand-font.py on the SVG first.');
await page.screenshot({ path: png });
await browser.close();
console.log(`${path.relative(themeDir, png)}  4800x3200  (${faces} embedded faces loaded)`);
