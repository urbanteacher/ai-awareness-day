# Cursor brief — port the Schools AI Risk Academy into a WordPress block

Hand this whole file to Cursor, **and attach `agentic-risks-academy.html`** (it is the source of truth for all markup, styles, and logic). The short version to paste into Cursor's chat is at the very top; the rest is the detailed spec.

---

## ⮕ Paste this into Cursor

> Build a self-contained WordPress Gutenberg **block plugin** called `schools-ai-risk-academy` that renders the interactive app in the attached `agentic-risks-academy.html`. Do **not** use the Custom HTML block — register a real dynamic block with a PHP `render_callback`, and enqueue the CSS and JS as block assets. Extract the `<style>` into `style.css`, the `<script>` into `view.js`, and the contents of `<main>` plus header/footer into the PHP render template. **Critical:** scope every CSS selector under a single root wrapper class `.sara` and delete all global resets and bare-element rules (`*`, `body`, `section`, `h1–h4`, `a`, `.btn`, `.wrap`, `.panel`, etc.) so nothing leaks into or out of the theme. Make the JS query only inside the block's root element so multiple instances and theme scripts don't clash. Self-host the fonts instead of calling Google Fonts. Keep all behaviour identical: the four Risk Meter tabs, the accordions, the scroll reveals, and the SVG gauges. Follow the detailed spec in this file and finish against the acceptance criteria.

---

## 1. Goal

Make the app available as a block editors can drop onto any page, titled **"Schools AI Risk Academy."** It must look and behave exactly like the standalone HTML file, but live safely inside a WordPress theme.

## 2. Approach (and why)

The app is **100% client-side vanilla JS/CSS with no React dependency.** The cleanest port is a **dynamic block** (server-rendered container + enqueued front-end script), not a React `save.js` rewrite. This preserves the existing JS untouched and avoids re-implementing it in React.

- **Do:** register a block via `block.json` with a PHP `render_callback`, and ship `style.css` + `view.js` (front-end) and a light editor preview.
- **Do not:** rely on the **Custom HTML / "Code" block** — it strips/sanitizes scripts and the interactivity will silently fail.
- Acceptable alternative if the team prefers tooling: scaffold with `@wordpress/create-block` and use the `viewScript` field in `block.json` for the front-end JS. Either way the front-end logic stays vanilla.

## 3. Plugin structure

```
schools-ai-risk-academy/
├── schools-ai-risk-academy.php   # plugin header + register_block_type()
├── block.json                    # block metadata + asset handles
├── render.php                    # PHP template: outputs <div class="sara">…</div>
├── build/ or src/
│   ├── style.css                 # extracted + SCOPED styles (front-end + editor)
│   ├── view.js                   # extracted interactivity (front-end only)
│   └── editor.js                 # registers block, renders a simple editor preview
├── fonts/                        # self-hosted Fraunces, IBM Plex Sans, IBM Plex Mono
│   └── fonts.css                 # @font-face rules (scoped/neutral)
└── readme.txt
```

## 4. Tasks (in order)

1. **Scaffold the plugin** with a standard header, text domain `schools-ai-risk-academy`, and `register_block_type( __DIR__ . '/block.json' )`.
2. **Extract assets from the HTML file:** the `<style>` block → `style.css`; the `<script>` block → `view.js`; the markup inside `<main>` (plus the `<header>` nav and `<footer>`) → `render.php`.
3. **Scope the CSS — the most important step.** Wrap all output in `<div class="sara">`. Then:
   - Prefix **every** selector with `.sara ` (e.g. `.sara .btn`, `.sara section`, `.sara h2`).
   - **Delete** the global reset `*{margin:0;padding:0;box-sizing:border-box}` and replace with `.sara, .sara *{box-sizing:border-box}` only.
   - **Delete** the bare `body{…}` and `html{…}` rules; move the relevant typography/colour onto `.sara`.
   - Move the `:root{ --ink: … }` CSS custom properties onto `.sara{ … }` so they don't become global.
   - Remove anything that targets `section`, `a`, `h1–h4`, `header`, `footer` globally — re-prefix them under `.sara`.
4. **Enqueue assets** via `block.json` fields: `style` (front + editor), `viewScript` (front-end `view.js`), `editorScript` (`editor.js`). Register `fonts.css` as a dependency of the style, or `@import` it at the top of `style.css`.
5. **Make the JS instance-safe.** The current code uses `document.getElementById(...)` and `document.querySelectorAll(...)`. Refactor `view.js` to:
   - Find each `.sara` root, and for each one run setup **scoped to that root** (`root.querySelector`, `root.querySelectorAll`).
   - Replace `id="…"` lookups with class- or `data-`-attribute lookups within the root, so two blocks on one page don't fight over the same IDs.
   - Guard with `DOMContentLoaded` / run on `wp.domReady` equivalently for the front end.
6. **Self-host fonts** (Fraunces, IBM Plex Sans, IBM Plex Mono) under `/fonts` with `@font-face`. Remove the `fonts.googleapis.com` `<link>` tags. Rationale: a UK schools site under UK GDPR should avoid shipping visitor IPs to Google — and it matches this app's own data-protection message.
7. **Editor preview** (`editor.js`): register the block with `@wordpress/blocks`; for `edit`, render a lightweight static preview (a heading + "Schools AI Risk Academy — interactive block" note, or `ServerSideRender`) so editors aren't running the full app inside the editor canvas.
8. **Block metadata:** category `widgets` (or a custom category), an icon, `supports: { html: false }`, keywords like "AI", "risk", "school". Optionally add boolean attributes to show/hide sections (e.g. `showCurriculum`, `showMeter`) — nice to have, not required.
9. **Security/escaping:** `render.php` is mostly static markup, but escape any dynamic/attribute output with `esc_attr`/`esc_html`. No user input is processed server-side.
10. **The sign-up form** in the app is front-end only. Leave it inert, OR (preferred) wire it to the site's existing newsletter/contact mechanism — flag this as a decision for the team rather than inventing an endpoint.

## 5. Watch-outs specific to this app

- **CSS bleed is the #1 risk.** The file currently restyles global elements; unscoped, it will break the surrounding theme and vice-versa. Scoping under `.sara` is non-negotiable.
- **Duplicate IDs.** IDs such as `modules`, `vsteps`, `classNeedle`, `quizCard` must not be global if the block can appear twice. Scope them.
- **No browser storage** is used (intentional) — keep it that way; don't add `localStorage`.
- **Reduced motion** is already handled via `prefers-reduced-motion`; preserve it.
- **SVG gauges** rotate a needle by inline `transform`; make sure the CSS transition rules survive scoping.
- **Theme container width.** The app uses a `max-width:1140px` `.wrap`; inside a constrained theme column it should still look right — test in a full-width template and a normal content column.

## 6. Acceptance criteria

- [ ] Block appears in the inserter as **"Schools AI Risk Academy"** and can be added to any page/post.
- [ ] Front end is **visually and behaviourally identical** to `agentic-risks-academy.html`.
- [ ] All four Risk Meter tabs work (Assess an activity, Reliance self-check, Class & school picture, Verify), including scoring, the gauge needle, the accordions, and scroll reveals.
- [ ] **Zero style bleed** — the theme's header/footer/typography are unaffected, and the block looks the same across at least two themes (e.g. a block theme like Twenty Twenty-Four and the site's active theme).
- [ ] Placing the block **twice on one page** does not break either instance.
- [ ] Fonts are **self-hosted**; no requests to `fonts.googleapis.com`.
- [ ] Works on mobile (≤480px) and tablet (≤860px) breakpoints.
- [ ] No JavaScript console errors on front end or in the editor.
- [ ] Plugin activates cleanly with no PHP notices; uninstalls without leaving cruft.

## 7. Environment notes to give Cursor

- Target **WordPress 6.x**, block editor (Gutenberg), PHP 8.x.
- State the **active theme** (block theme vs classic) — affects full-width rendering.
- If the team uses `@wordpress/scripts`, run `npm i` / `npm run build`; otherwise ship plain files with no build step.
- Confirm whether the block should be **full-width** by default (`align: full` support) — likely yes for this layout.

---

*Source of truth: `agentic-risks-academy.html`. If markup, copy, or scoring changes, update that file first, then re-extract.*
