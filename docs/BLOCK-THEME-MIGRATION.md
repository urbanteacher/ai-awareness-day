# Block Theme Migration Plan

Moving the AI Awareness Day theme from classic PHP templates and the Customizer to a WordPress block theme (Full Site Editing), in stages, without breaking the live site.

- **Target platform:** WordPress 7.1.x (7.1.2 current at time of writing), Gutenberg 24.x
- **Reference theme:** Twenty Twenty-Five v1.5 (`wordpress-develop/src/wp-content/themes/twentytwentyfive`)
- **Status:** Planning. Nothing migrated yet.

---

## 1. Why

- Editors can change wording, images and section order on the page itself, without the Customizer or a developer.
- Layout, spacing, typography and colour are set once in `theme.json` and apply to every block.
- It follows WordPress's current direction for themes, so the theme stays aligned with core updates.

This is not mainly a code-reduction exercise. Expect the theme's own PHP and CSS to shrink by roughly 15–25%, mostly from removing the Customizer panels. The interactive tools stay largely as they are.

---

## 2. Current state (inventory)

| Area | Where | Size |
|---|---|---|
| Page templates | 24 top-level PHP files (`front-page.php`, `single-*.php`, `archive-*.php`, `page-*.php`, `template-*.php`) | ~3,500 lines |
| Header / footer | `header.php`, `footer.php` | (included above) |
| Homepage sections | `template-parts/front-page/section-*.php` (15 files) | ~1,750 lines |
| Customizer + section ordering | `inc/customizer.php`, `inc/customizer-smtp-control.php`, `inc/front-page-layout.php` | ~1,140 lines, 52 settings |
| Meta boxes | `inc/meta-boxes.php`, `inc/timeline/admin-meta-box.php`, `assets/js/admin-meta-boxes.js` + 5 other files | ~1,830 lines (main files) |
| Shortcodes (theme) | 12 in `inc/` — buzzwords, speed quiz, LLM explainer, computing curriculum, certificate showcase, misinformation detector, ICT curriculum, curriculum quiz, NEU AI report, risk academy, LLM order game, national survey | ~6,100 PHP + ~10,200 JS |
| Shortcodes (plugin) | `ai_risk_benchmark`, `ai_risk_school_dashboard` in `plugins/ai-risk-readiness-benchmark/` | — |
| Post types | `resource`, `partner`, `featured_resource`, `form_submission`, `ai_tool`, `live_session`, `timeline` | all public ones have `show_in_rest` |
| Registered meta | 21 `register_post_meta` / `register_meta` calls | REST-ready |
| Patterns | `patterns/hero.php`, `patterns/principles.php` (read Customizer values at render time) | — |
| CSS | `assets/css/` modular files | ~32,800 lines |
| `theme.json` | v3, palette + layout (720 / 1200px), schema `wp/6.6` | — |

Already in our favour: `theme.json` v3, REST-enabled post types and meta, and no code disabling the block editor.

---

## 3. Target structure

Mirrors Twenty Twenty-Five:

```
ai-awareness-day/
├── style.css            header only; add "full-site-editing" to Tags
├── theme.json           schema wp/7.1, version 3; + templateParts, customTemplates
├── functions.php        slim: requires, block registration, pattern categories, bindings
├── templates/           *.html block markup
├── parts/               header.html, footer.html (+ variants)
├── patterns/            section patterns + full-page starter patterns
├── styles/              style variations; sections/ for the five theme colour bands
├── src/blocks/<name>/   block.json, edit.js, render.php, view.js, style.scss
├── build/blocks/        compiled output (committed or built on deploy — decide)
├── inc/                 existing PHP logic (tools, CPTs, AJAX, certificates) stays
├── assets/              existing CSS / JS / images
└── package.json         @wordpress/scripts build
```

`src/blocks/` and `build/blocks/` live in the `aiad-core` plugin, not in the theme (see Stage 1).

### Mapping

| Now | Becomes |
|---|---|
| `header.php` / `footer.php` | `parts/header.html` / `parts/footer.html` (core Navigation + Site Logo) |
| `index.php`, `page.php`, `single.php`, `404.php` | `templates/index.html`, `page.html`, `single.html`, `404.html` |
| `single-{cpt}.php` / `archive-{cpt}.php` | `templates/single-{cpt}.html` / `templates/archive-{cpt}.html` |
| `front-page.php` | `templates/front-page.html` rendering a normal editable page |
| `page-national-conversation.php`, `page-hub-resource.php`, `page-walkthrough.php`, `template-press-release.php`, `template-assets-pack.php` | `templates/*.html` listed under `customTemplates` |
| `template-parts/front-page/section-*.php` | `patterns/section-*.php` (static) or dynamic blocks (data-driven) |
| Customizer text/image settings | Content in the homepage `post_content` |
| Customizer section visibility/order | Removed — editors move/remove blocks |
| Customizer colours/fonts | `theme.json` + `styles/` variations |
| Shortcodes | Dynamic blocks (`render.php` calls existing PHP, `viewScript` loads existing JS) |
| Meta displayed in templates | Block bindings (`register_block_bindings_source`) or small dynamic blocks |
| Meta boxes | Keep initially; move to `PluginDocumentSettingPanel` later where worthwhile |

---

## 4. Stages

Each stage ships on its own and leaves the site fully working.

**The switch point.** WordPress treats the theme as a block theme as soon as `templates/index.html` exists. From then on, Appearance → Editor takes over, and the Customize and Menus screens leave the Appearance menu. Until then, the site is a classic theme with block features added. So `templates/index.html` is added **last**, in Stage 3.

While both kinds of template exist, a PHP template still wins over any **less specific** block template: `single-resource.php` beats `templates/single.html` and `templates/index.html`. At **equal** specificity the block template wins, so adding `templates/single.html` replaces `single.php`. Old templates can therefore stay until each one has a block replacement. (Source: `locate_block_template()` in `wp-includes/block-template.php`, WordPress 7.1.2.)

Templates edited in the Site Editor are saved to the database and override the theme's files, so compare against the database copy when checking a template.

### Stage 1 — Functionality plugin and blocks (~2–3 weeks)

The theme stays classic. Editors gain blocks. Functionality moves out of the theme into its own plugin (`plugins/aiad-core/`), so the design can change later without touching the tools.

- [x] Create `aiad-core` plugin skeleton (`plugins/aiad-core/`, see its README for the module map)
- [x] First module moved: data helpers (`helpers-data`)
- [x] Second module moved: post types, field registry, validation, taxonomy fields, seeds (`post-types`)
- [x] `ajax-handlers.php` split and moved: `contact`, `resource-filter`, plus the first part of `admin`. The filter keeps rendering the theme's resource tile until the card becomes a block in Stage 3
- [x] `tracking` module finished: download/view counters, engagement tracking, dashboard widgets
- [x] `admin` module finished: meta boxes, list columns and filters, import/export, focal point, submissions CSV export
- [x] `timeline` module: CPT, meta, admin meta box, entries, topics, queries, AJAX, benchmark audience (presentation stays in the theme)
- [x] `live-sessions` module: events CPT, admin, data helpers, redirects, calendar feed (markup helpers stay in the theme)
- [x] `certificates` module: certificate and letter wording, REST API, generator config, admin pages
- [x] `ai-tools` module: AI tools CPT, taxonomy, seeds, meta (row renderer stays in the theme)
- [x] `benchmark-content` module: benchmark launch article and hub timeline seeds
- [x] `survey` module: survey responses CPT, shortcode, AJAX submit, admin analytics and CSV export (assets stay in the theme until it becomes a block)
- [x] `tools` module: the 10 live interactive tools (the retired curriculum quiz stays unloaded in the theme)
- [ ] Certificate showcase: stays in the theme until it becomes the `aiad/certificate-showcase` block (with its template, stylesheet and the two benchmark promo helpers) in the interactive-tools phase
- [ ] Move the remaining module (SEO, after the Customizer settings decision; see the plugin README) from `inc/` into it (theme keeps only presentation)
- [x] Add `package.json` with `@wordpress/scripts`; `npm run build` → `build/blocks/`
- [ ] Bump `theme.json` `$schema` to `https://schemas.wp.org/wp/7.1/theme.json`
- [x] Register blocks from the plugin's `build/blocks/` (metadata collection / `register_block_type`)
- [x] Convert the theme shortcodes to dynamic blocks in the plugin; shortcodes stay registered for existing content. 10 of 11 done (tools + survey); the certificate showcase is next. The 12th, `[aiad_curriculum_quiz]`, was retired and needs no block
- [ ] Tool CSS/JS into the plugin (registered on `init`, referenced from `block.json`), so editor previews are styled
- [ ] Wrap the 2 plugin shortcodes as blocks inside the plugin
- [x] Block category "AI Awareness Day"
- [ ] Proof of concept: hero (the quizzes are done)

**Done when:** every shortcode has a matching block that renders identically on the front end, and editors can insert them from the inserter.

### Stage 2 — Hybrid theme (~1–2 weeks)

- [ ] `add_theme_support( 'block-template-parts' )` so editors can edit parts on screen (Appearance → Template Parts)
- [ ] Add `parts/header.html` and `parts/footer.html`; call them from `header.php` / `footer.php` via `block_template_part()`
- [ ] Rebuild the 15 homepage sections as patterns / dynamic blocks
- [ ] Migration script: read the 52 `theme_mod` values → write the homepage `post_content` (run on staging first; keep a backup of `theme_mods_ai-awareness-day`)
- [ ] Switch `front-page.php` to output `the_content()` of the static front page
- [ ] Section styles in `styles/sections/` for Safe / Smart / Creative / Responsible / Future

**Done when:** the homepage is edited entirely in the block editor and matches the current design.

### Stage 3 — Full block theme (~2–4 weeks)

- [ ] Convert templates to `templates/*.html` one at a time; delete each PHP version once its block version matches
- [ ] Add `templates/index.html` last — this is the switch to a full block theme
- [ ] Register `templateParts` and `customTemplates` in `theme.json`
- [ ] Migrate menus to `wp_navigation` posts (core Navigation block)
- [ ] Block bindings for CPT meta (timeline, live sessions, partners, resources)
- [ ] Move spacing/typography/colour CSS into `theme.json`; remove the duplicated rules
- [ ] Remove `inc/customizer.php` and `inc/front-page-layout.php`; move the SMTP setting to a settings page
- [ ] Add `full-site-editing` to `style.css` Tags; update the description

**Done when:** no PHP template files remain at theme root except `functions.php`, and the Customizer is no longer used.

Estimates are rough, for one developer.

### Alternative considered: Twenty Twenty-Five child theme

Use Twenty Twenty-Five as the parent, with a child theme holding brand styles, templates and patterns, and all functionality in the plugin.

- **For:** starts from a maintained block theme; less template work.
- **Against:** the current design is custom, so most of it would be rebuilt rather than converted, and the parent's own styles and patterns would need overriding.
- **Decision:** keep and convert the AI Awareness Day theme. The plugin split from this option is adopted either way.

---

## 5. What stays

- Tool logic and JS for the quizzes, games, survey, risk academy, certificates and AJAX handlers (moved into the `aiad-core` plugin, not rewritten)
- CPT and taxonomy registration, meta registration (moved to the plugin), validation, import/export, SEO, engagement tracking
- Bundled plugins (`plugins/ai-risk-readiness-benchmark`, `plugins/aiad-debate-network`)
- Brand and component CSS

---

## 6. Risks and mitigations

| Risk | Mitigation |
|---|---|
| CSS and JS target current class names | Blocks output the same classes via `className` / wrapper markup; visual diff each converted section |
| Customizer content lost in migration | Export `theme_mods` before running the script; run on staging; keep Customizer code until Stage 3 |
| Existing posts use shortcodes | Shortcodes stay registered permanently (or convert content with a one-off script later) |
| Editors unfamiliar with the Site Editor | Short editor guide; lock key patterns with `templateLock` where needed |
| Menus change location | Recreate menus as Navigation blocks and verify every link before switching |
| SEO / URLs | Templates change, URLs do not; compare titles, meta and schema output before and after |

---

## 7. Testing checklist (per stage)

- [ ] Front end matches before/after at phone, tablet and desktop widths
- [ ] Every interactive tool loads and works (including AJAX submissions)
- [ ] Editor preview matches the front end for each custom block
- [ ] No PHP notices or JS console errors
- [ ] Forms, certificates and survey submissions still save
- [ ] Accessibility: headings order, landmarks, keyboard use, contrast
- [ ] Page speed not worse than before

---

## 8. Decisions needed

1. ~~Commit `build/` to git, or build on deploy?~~ Commit it: deploys copy the plugin as it is and run no build step.
2. Stop at Stage 2 (hybrid) or go to a full block theme?
3. Which meta boxes are worth rebuilding as editor panels, and which stay as they are?
4. Who signs off the visual match for each stage?

---

## References

- Twenty Twenty-Five source: https://github.com/WordPress/wordpress-develop/tree/trunk/src/wp-content/themes/twentytwentyfive
- `theme.json` 7.1 schema: https://schemas.wp.org/wp/7.1/theme.json
- create-block template: https://github.com/WordPress/gutenberg/tree/trunk/packages/create-block/lib/templates/block
- Block Editor Handbook: https://developer.wordpress.org/block-editor/
- Theme Handbook (block themes): https://developer.wordpress.org/themes/
