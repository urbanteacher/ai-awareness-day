#!/usr/bin/env node
/**
 * Rasterise the whole AiAd27 brand kit to PNG, next to each source SVG.
 *
 * Every mark in assets/brand/aiad27/ and assets/images/polygon-shapes/ ships
 * as SVG only. That's the right source format, but not everyone who needs the
 * logo — a partner filling in a slide deck, a print shop, a form that only
 * accepts PNG/JPG — can use one. This exports a same-name .png beside every
 * .svg, at a size generous enough for print, so the SVG stays the source of
 * truth and the PNG is a rendered copy of it.
 *
 * Three render paths, depending on what each file already carries:
 *
 *   - Lockups declare 'Inter, Helvetica, Arial, sans-serif' (not AIAD Sans —
 *     that's the file's own existing choice, unrelated to this script) and
 *     have no embedded font, so they render with whatever of that stack the
 *     machine has; opened standalone with file://, same as this script does.
 *   - aiad27-mark.svg and every polygon-shapes/*.svg already carry a subset
 *     of AIAD Sans inlined (scripts/embed-brand-font.py), so opening them
 *     standalone renders correctly with no help from this script.
 *   - The chamfer shapes and icon-*.svg are drawn with fill="currentColor",
 *     so they have no colour until something sets one. Opened bare, a
 *     browser's initial `color` is black, which isn't the brand's ink. This
 *     script gives those two currentColor motifs (chamfer only — the icons
 *     already carry their own fill, see below) an explicit colour by
 *     inlining the SVG into a tiny host page instead of navigating to the
 *     file, so the exported PNG matches how the site actually uses them.
 *
 * The icon-*.svg files already set fill="<strand bright>" directly (not
 * currentColor) despite the style guide describing them as recolourable — so
 * they, too, render correctly standalone; no wrapper needed for those.
 *
 * Usage: node scripts/export-brand-pngs.mjs
 *
 * Needs Playwright: either `npm install playwright` somewhere on this machine
 * and point AIAD_PLAYWRIGHT_DIR at that directory, or run from a tree that
 * already has it (see scripts/export-favicon.mjs for the same pattern).
 */
import fs from 'node:fs';
import path from 'node:path';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const BRAND = path.join(themeDir, 'assets/brand/aiad27');
const POLY = path.join(themeDir, 'assets/images/polygon-shapes');
const INK = '#231F20';

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

/** A PNG's own width/height, read straight from its IHDR chunk (no dependency). */
async function readPngDims(pngPath) {
	const buf = Buffer.alloc(24);
	const fh = await fs.promises.open(pngPath, 'r');
	await fh.read(buf, 0, 24, 0);
	await fh.close();
	return { width: buf.readUInt32BE(16), height: buf.readUInt32BE(20) };
}

function viewBoxSize(svgText) {
	const m = svgText.match(/viewBox="[\d.\-]+\s+[\d.\-]+\s+([\d.]+)\s+([\d.]+)"/);
	if (!m) throw new Error('no viewBox found');
	return [parseFloat(m[1]), parseFloat(m[2])];
}

const browser = await chromium.launch();

/**
 * @param file     Absolute path to the source .svg.
 * @param scale    Multiple of the SVG's own viewBox size to render at.
 * @param options.wrapColor  If set, the SVG is inlined into a host page with
 *                 this as the CSS `color`, so its currentColor paths pick it
 *                 up. Omitted, the file is opened directly with file://.
 */
async function exportPng(file, scale, { wrapColor } = {}) {
	const svgText = fs.readFileSync(file, 'utf8');
	const [w, h] = viewBoxSize(svgText);
	const width = Math.round(w * scale);
	const height = Math.round(h * scale);
	const page = await browser.newPage({ viewport: { width, height } });

	if (wrapColor) {
		const inline = svgText.replace(/<svg /, `<svg style="width:${width}px;height:${height}px" `);
		await page.setContent(
			`<!doctype html><meta charset="utf-8"><style>html,body{margin:0;padding:0;color:${wrapColor}}</style>${inline}`
		);
	} else {
		await page.goto('file://' + file);
		// Both dimensions, always. A bare `svg.style.width` leaves height at
		// whatever the file's own width="…" height="…" attributes say (most of
		// these have them, matching the viewBox 1:1), so the element's box came
		// out e.g. 2400x56 instead of 2400x448: correct width, native height.
		// With default preserveAspectRatio, that scales the content by
		// min(2400/300, 56/56) = 1 — the logo rendered at its original tiny
		// size, centred in a mostly-empty transparent canvas. Setting height
		// too (inline style beats the attribute either way) is what actually
		// scales the artwork up.
		await page.evaluate(([w, h]) => {
			const svg = document.querySelector('svg');
			svg.style.width = w + 'px';
			svg.style.height = h + 'px';
		}, [width, height]);
	}
	await page.evaluate(() => document.fonts.ready);

	const out = file.replace(/\.svg$/, '.png');
	await page.locator('svg').screenshot({ path: out, omitBackground: true });
	await page.close();

	// Verify rather than trust: a wrong-but-plausible-looking render (small
	// content padded into an oversized canvas) is exactly the bug above, and
	// it's easy to miss by eye. Fail loudly instead of shipping it again.
	const dims = await readPngDims(out);
	const ok = dims.width === width && dims.height === height;
	console.log(
		`${path.relative(themeDir, out).padEnd(58)} ${dims.width}x${dims.height}` +
			(ok ? '' : `  MISMATCH (expected ${width}x${height})`)
	);
	if (!ok) throw new Error(`${out}: rendered ${dims.width}x${dims.height}, expected ${width}x${height}`);
}

// Sizes are generous on purpose: these are vector-sourced, so a bigger PNG
// costs render time and a little disk space, not quality. Aimed at "drop this
// into a slide or a printed page at a sensible size and it's still crisp",
// not just screen use.

// --- lockups: 20x (300x56 -> 6000x1120), transparent, system-font fallback ---
const lockups = fs.readdirSync(BRAND).filter((f) => /^aiad27-lockup.*\.svg$/.test(f));
for (const f of lockups) await exportPng(path.join(BRAND, f), 20);

// --- the square mark: 8x (512 -> 4096), the same file the favicon comes from ---
await exportPng(path.join(BRAND, 'aiad27-mark.svg'), 8);

// --- the square mark, plus the tagline (a separate file: the plain mark
//     above still feeds the favicon, where a fourth text line has no room) ---
await exportPng(path.join(BRAND, 'aiad27-mark-tagline.svg'), 8);

// --- strand icons: a 24-unit box upscaled to a full 1024, own fill already set ---
for (const s of ['safe', 'smart', 'creative', 'responsible', 'future']) {
	await exportPng(path.join(BRAND, `icon-${s}.svg`), 1024 / 24);
}

// --- posters: 6x (480x490 -> 2880x2940) ---
for (const s of ['safe', 'smart', 'creative', 'responsible', 'future']) {
	await exportPng(path.join(BRAND, `poster-${s}.svg`), 6);
}

// --- chamfer shapes: currentColor, so wrapped and given the brand ink ---
await exportPng(path.join(BRAND, 'shape-chamfer-tile.svg'), 12, { wrapColor: INK });
await exportPng(path.join(BRAND, 'shape-chamfer-panel.svg'), 6, { wrapColor: INK });

// --- polygon shapes: 8x (160 -> 1280), already carry their own embedded font ---
for (const f of fs.readdirSync(POLY).filter((f) => f.endsWith('.svg'))) {
	await exportPng(path.join(POLY, f), 8);
}

await browser.close();
console.log('\nDone. Each PNG sits next to its source SVG.');
