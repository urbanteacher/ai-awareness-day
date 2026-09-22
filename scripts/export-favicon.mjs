#!/usr/bin/env node
/**
 * Rasterise the square AiAd27 mark into the favicon set the theme serves from
 * assets/images/favicon/.
 *
 * The mark is authored once, as assets/brand/aiad27/aiad27-mark.svg. Browsers
 * will not load a webfont from inside a standalone SVG document, so the SVG is
 * inlined into a host page that carries the @font-face rules — the same trick
 * the review bundle uses — and captured there at 1x for each size.
 *
 * The family is declared as 'AIAD Sans', which is what the theme's own CSS
 * calls the Uncut Sans woff2s, so the mark resolves the same font whether it is
 * rasterised here or dropped inline into a page.
 *
 * The 32px icon is a fifth the height of the 512: the three stacked words
 * collapse into grey noise at that size, so it gets its own reduction — the
 * "AI" and the year on the same tile — rather than a downscale of the full
 * lockup. See buildHost().
 *
 * Usage: node scripts/export-favicon.mjs [outDir]
 *
 * Needs Playwright. Either `npm install playwright` somewhere on this machine
 * and point AIAD_PLAYWRIGHT_DIR at that directory, or run the script from a
 * tree that already has it.
 */
import path from 'node:path';
import fs from 'node:fs';
import { createRequire } from 'node:module';
import { fileURLToPath } from 'node:url';

const themeDir = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const args = process.argv.slice(2);
const outDir = args.find((a) => !a.startsWith('--')) || path.join(themeDir, 'assets/images/favicon');

const markSvg = fs.readFileSync(path.join(themeDir, 'assets/brand/aiad27/aiad27-mark.svg'), 'utf8');
const fontsDir = path.join(themeDir, 'assets/fonts/aiad27');

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

/* The full mark, plus the small-size reduction. Both are 512-unit squares so
   they share the tile geometry exactly; only the type inside them differs. */
const SMALL_MARK = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <path fill="#231F20" d="M0 0H400L512 112V512H112L0 400Z"/>
  <g font-family="'AIAD Sans', Arial, Helvetica, sans-serif" fill="#F6F4ED">
    <text x="52" y="270" font-size="210" font-weight="700" letter-spacing="-6">AI</text>
    <text x="462" y="442" text-anchor="end" font-size="112" font-weight="600">27</text>
  </g>
</svg>`;

/* At 16px even the reduction loses its year: two 1px-stroke digits sit on top
   of each other. The 16px frame keeps the tile and the "AI" only. */
const TINY_MARK = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="512" height="512">
  <path fill="#231F20" d="M0 0H400L512 112V512H112L0 400Z"/>
  <text x="256" y="360" text-anchor="middle"
        font-family="'AIAD Sans', Arial, Helvetica, sans-serif" fill="#F6F4ED"
        font-size="300" font-weight="700" letter-spacing="-10">AI</text>
</svg>`;

const targets = [
	{ file: 'favicon.png', px: 512, svg: markSvg },
	{ file: 'favicon-180.png', px: 180, svg: markSvg },
	{ file: 'favicon-32.png', px: 32, svg: SMALL_MARK },
];

/* favicon.ico still earns its place: it is what a browser asks for at the site
   root before it has parsed any markup, and what Windows pins use. */
const icoSizes = [
	{ px: 48, svg: SMALL_MARK },
	{ px: 32, svg: SMALL_MARK },
	{ px: 16, svg: TINY_MARK },
];

function buildHost(svg, px) {
	/* file:// keeps the woff2 loads local, so the capture never depends on a
	   dev server being up. */
	/* Weights mirror assets/css/editor-style.css, so a weight in the mark picks
	   the same face here as it does inside a theme page. */
	const face = (file, weight) => `
    @font-face {
      font-family: 'AIAD Sans';
      src: url('${path.join(fontsDir, file)}') format('woff2');
      font-weight: ${weight};
      font-style: normal;
    }`;
	return `<!doctype html><html><head><meta charset="utf-8"><style>
    ${face('UncutSans-Regular.woff2', 400)}
    ${face('UncutSans-Semibold.woff2', 600)}
    ${face('UncutSans-Bold.woff2', '700 900')}
    html, body { margin: 0; padding: 0; background: transparent; }
    svg { display: block; width: ${px}px; height: ${px}px; }
  </style></head><body>${svg}</body></html>`;
}

fs.mkdirSync(outDir, { recursive: true });

const browser = await chromium.launch();

async function render(svg, px) {
	const page = await browser.newPage({ viewport: { width: px, height: px } });
	await page.setContent(buildHost(svg, px));
	await page.evaluate(() => document.fonts.ready);
	/* omitBackground keeps the two chamfered corners transparent rather than
	   filling them white, which is the whole point of the shape. */
	const buf = await page.locator('svg').screenshot({ omitBackground: true });
	await page.close();
	return buf;
}

for (const { file, px, svg } of targets) {
	fs.writeFileSync(path.join(outDir, file), await render(svg, px));
	console.log(`${file}  ${px}x${px}`);
}

/* An .ico is a 6-byte directory header, one 16-byte entry per image, then the
   image payloads. Every modern target reads PNG payloads, so the frames go in
   as the PNGs Chromium just produced rather than as raw DIBs. */
const frames = [];
for (const { px, svg } of icoSizes) frames.push({ px, png: await render(svg, px) });

const header = Buffer.alloc(6);
header.writeUInt16LE(0, 0); // reserved
header.writeUInt16LE(1, 2); // type: icon
header.writeUInt16LE(frames.length, 4);

let offset = 6 + 16 * frames.length;
const dir = [];
for (const { px, png } of frames) {
	const entry = Buffer.alloc(16);
	entry.writeUInt8(px === 256 ? 0 : px, 0); // width
	entry.writeUInt8(px === 256 ? 0 : px, 1); // height
	entry.writeUInt8(0, 2); // palette size: none
	entry.writeUInt8(0, 3); // reserved
	entry.writeUInt16LE(1, 4); // colour planes
	entry.writeUInt16LE(32, 6); // bits per pixel
	entry.writeUInt32LE(png.length, 8);
	entry.writeUInt32LE(offset, 12);
	offset += png.length;
	dir.push(entry);
}

fs.writeFileSync(
	path.join(outDir, 'favicon.ico'),
	Buffer.concat([header, ...dir, ...frames.map((f) => f.png)])
);
console.log(`favicon.ico  ${icoSizes.map((f) => f.px).join('/')}`);

await browser.close();
console.log(`\nWrote ${targets.length + 1} files to ${outDir}`);
