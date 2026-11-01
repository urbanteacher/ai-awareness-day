// Render archive/motion/badges at 600×600, 20 fps, into PNG frames (one folder per badge), or stills.
import { createRequire } from 'node:module';
import fs from 'node:fs'; import path from 'node:path'; import { fileURLToPath } from 'node:url';
const require = createRequire(process.env.PW + '/x.cjs');
const { chromium } = require('playwright');
const { serve } = await import(new URL('../motion/serve.mjs', import.meta.url).href);
const [outDir, which, stillsArg] = process.argv.slice(2);
const FPS = 20;
const server = await serve();
const base = `http://127.0.0.1:${server.address().port}/archive/motion/badges/index.html`;
const b = await chromium.launch(process.env.CHROME ? { executablePath: process.env.CHROME } : {});
const p = await b.newPage({ viewport: { width: 900, height: 900 }, deviceScaleFactor: 2 / 3 });
p.on('pageerror', e => console.log('PAGE ERROR', e.message));
for (const badge of which.split(',')) {
  await p.goto(`${base}?render&badge=${badge}`, { waitUntil: 'networkidle' });
  const ok = await p.evaluate(() => window.__ready);
  const dur = await p.evaluate(() => window.DURATION);
  const dir = path.join(outDir, badge); fs.rmSync(dir, { recursive: true, force: true }); fs.mkdirSync(dir, { recursive: true });
  const times = stillsArg ? stillsArg.split(',').map(Number) : Array.from({ length: Math.round(dur * FPS) }, (_, i) => i / FPS);
  for (let i = 0; i < times.length; i++) {
    await p.evaluate(t => seek(t), times[i]);
    await p.screenshot({ path: path.join(dir, `f${String(i).padStart(4, '0')}.png`), clip: { x: 0, y: 0, width: 900, height: 900 } });
  }
  console.log(badge, 'fonts', ok, 'dur', dur, 'frames', times.length);
}
await b.close(); server.close();
