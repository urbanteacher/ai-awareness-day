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
- [x] Certificate showcase: moved with its template, stylesheet and the benchmark promo helpers; `aiad/certificate-showcase` block
- [x] `seo` module, with its own settings (Settings → SEO & sharing) copied once from the Customizer. All modules on the plugin's map are now moved
- [x] `campaign` module: the event date and contact form recipient in their own settings (Settings → Campaign & contact), kept in step with the Customizer and homepage editor
- [x] Add `package.json` with `@wordpress/scripts`; `npm run build` → `build/blocks/`
- [x] Bump `theme.json` `$schema` to `https://schemas.wp.org/wp/7.1/theme.json` (validates against it; version stays 3)
- [x] Register blocks from the plugin's `build/blocks/` (metadata collection / `register_block_type`)
- [x] Convert the theme shortcodes to dynamic blocks in the plugin; shortcodes stay registered for existing content. All 11 done (tools, survey, certificate showcase). The 12th, `[aiad_curriculum_quiz]`, was retired and needs no block
- [x] Tool CSS/JS, data, fonts and the Risk Academy template moved into the plugin
- [x] Editor previews styled: each block loads its tool's stylesheet in the editor; theme tokens moved to `assets/css/base/tokens.css`, loaded first on the front end and in the editor
- [x] Blocks for the benchmark plugin's 2 shortcodes (`aiad/risk-benchmark`, `aiad/school-dashboard`), in aiad-core rather than inside the benchmark plugin, which deploys on its own version bumps
- [x] Block category "AI Awareness Day"
- [ ] Proof of concept: hero (the quizzes are done)

**Done when:** every shortcode has a matching block that renders identically on the front end, and editors can insert them from the inserter.

### Stage 2 — Hybrid theme (~1–2 weeks)

- [x] `add_theme_support( 'block-template-parts' )` (`inc/site-parts.php`), so the header and footer are edited in Appearance → Design (WordPress 7's name for the Site Editor in a classic theme); `templateParts` in `theme.json` gives them their areas
- [x] `parts/header.html` and `parts/footer.html`, printed by `header.php` / `footer.php` with `block_template_part()`. Groups for the structure; small blocks (`blocks/site-logo`, `site-navigation`, `footer-widgets`, `footer-links`, `footer-social`) print the pieces that come from menus, widgets and settings, with the templates' markup (`template-parts/components/`); the copyright line is an ordinary paragraph. Same tags and text on every page checked, every element matches at 1280px and 390px (mobile menu open too). The menu stays in Appearance → Menus until the Navigation block can take over its markup and script (stage 3)
- [x] Homepage sections as blocks: 11 section blocks (theme, `inc/homepage-blocks.php`) that render the existing section templates; Appearance → Block homepage creates a "Home" page from the current order and visibility and makes it the front page (and can switch back). The homepage prints the same markup
- [x] Section wording in each block's sidebar (35 fields across 6 sections), copied from the Customizer by a button on Appearance → Block homepage; the Customizer hides those controls while the block homepage is on
- [ ] Sections rebuilt from core blocks/patterns for on-canvas editing, section by section. A section block with a pattern (`aiad_homepage_section_patterns()`) gets an "Edit on the page" button that swaps it for the pattern's core blocks; the theme CSS styles core's markup alongside the template's
  - [x] Aim (`patterns/homepage-aim.php`): same layout at 1280px and 390px, "Show more" still works; the aims fade in together rather than one by one
  - Checked against the 7.0/7.1 docs: the outer group's `metadata.patternName` makes it a section block, so WordPress 7.0+ opens it in content-only mode ([Pattern editing in 7.0](https://make.wordpress.org/core/2026/03/15/pattern-editing-in-wordpress-7-0/)): text is edited and aims added or removed, while layout and styles stay locked ("Edit pattern" unlocks them). The pattern file's markup is exactly what the editor saves, so no block shows as changed or invalid. The site's `blockGap` setting is on (24px flow margins for post content), so the gap is switched off with CSS inside section groups rather than in `theme.json`
  - [x] Principles (`patterns/homepage-principles.php`): heading in core blocks; the cards are two small theme blocks with `block.json` (`blocks/principles-grid`, `blocks/principle-card`), because a whole card is a link and the grid carries a region label, which core blocks cannot do. Their title and description have `"role": "content"`, so they are edited on the card in content-only mode; the strand (colour and icon) is set in the sidebar and the icon is worked out when the page renders. Cards and grid print the template's markup exactly; same layout at 1280px and 390px. The pattern starts from the site's wording, and the swap carries over wording set in the section block's sidebar
  - [x] Get involved (`patterns/homepage-contact.php`): heading in core blocks, the form as a small block (`blocks/contact-form`) that prints `template-parts/components/contact-form.php`, which the section template now includes too. Sidebar wording lands in the core block whose class `aiad_homepage_pattern_wording_targets()` names. Every element matches at 1280px and 390px, and the form's script runs
  - Core's flow layout also zeroes the last block's bottom margin with a rule stronger than the theme's; `base/wp-core.css` gives the section label, title and description theirs back inside section groups
  - [x] Campaign (`patterns/homepage-campaign.php`): text in core blocks; the partner logo strip, the video and the partner cards are small blocks (`blocks/partner-marquee`, `blocks/campaign-embed`, `blocks/partners-grid`) that print the template's markup from shared components (`template-parts/components/`) and partner data (`aiad_campaign_partners()`, `inc/homepage-campaign.php`). What depends on them (`campaign--split`, `campaign-split--single`, `data-anchor-target`, the reach group's card counts) is added at render with the HTML API (`aiad_campaign_section_attributes()`). The video address is edited on its block; every element matches at 1280px and 390px
  - [x] Hero: edited on the canvas rather than swapped for a pattern, because its join link, dates, countdown and portal links are worked out when the page renders. The block's editor (`assets/js/homepage-hero-edit.js`) shows the hero with the site's markup and edits its words in place (the strand tabs choose which question); they are saved in the block's wording attribute as plain text, so the page renders exactly as before. Same sizes, fonts and colours as the rendered hero in the same canvas. The link stays in the sidebar; the previous hero keeps its preview
  - [x] Free resources, featured resources (`patterns/homepage-free-resources.php`, `patterns/homepage-featured-resources.php`): heading in core blocks; the tiles are a resource tiles block (`blocks/resource-tiles`, free or featured) and the LinkedIn card its own block (`blocks/linkedin-card`), printing shared components; the resources are still picked in Appearance → Edit Homepage (`inc/homepage-resources.php`). As in the templates, a section without resources is hidden (`aiad_homepage_resources_section_visibility()`). Every element matches at 1280px and 390px
- [x] Migration, in two buttons on Appearance → Block homepage: "Copy the Customizer wording into the blocks" (the theme mods into each section block's wording) and "Make every section editable on the page" (`aiad_rebuild_homepage_sections()`: each section with a pattern becomes its blocks, keeping its wording). Theme mods are only read, and the page's Revisions keep the version before. Run on staging first
- [x] `front-page.php` renders the block homepage's section blocks when it is the front page (each block on its own, without the_content's filters), and the classic section loop otherwise
- [x] Section styles in `styles/sections/` for Safe / Smart / Creative / Responsible / Future: block style variations for the Group block (WordPress 6.6+), picked under Styles; the strand's ground with ink text and headings from the palette. Links keep the theme's link colour, which the theme's own rule sets

**Done when:** the homepage is edited entirely in the block editor and matches the current design.

### Stage 3 — Full block theme (~2–4 weeks)

- [ ] Convert templates to `templates/*.html` one at a time; the PHP versions stay as the fallback until `templates/index.html` (the switch), then go
  - [x] Groundwork: the head tags `header.php` printed come from `wp_head()` (`aiad_site_head_tags()`) and `<html class="no-js">` from a `language_attributes` filter, because block templates render through core's `template-canvas.php`; a breadcrumbs block (`blocks/breadcrumbs`) for the trail `header.php` printed
  - [x] 404 (`templates/404.html`): header and footer template parts, the page in core blocks (the Back to Home button is a Custom HTML block, keeping `.btn-submit` and its arrow), its inline styles in `base/wp-core.css`. Every element matches at 1280px and 390px. Core adds its "Skip to content" link, as block templates do
  - [x] Page (`templates/page.html`): post title and post content in the old template's structure; pages with their own template (National Conversation, Hub resource, Walkthrough, Assets Pack, Press Release) keep it. All 24 pages that use it match element for element at 1280px and 390px. Two things block templates change and needed care: they render before `wp_head()`, so a plugin that registers scripts on `wp_enqueue_scripts` and localises them while its shortcode renders loses the data (fixed in the benchmark plugin); and core's post content block is `display: flow-root` and a flow layout, reset in `base/wp-core.css` so the content spaces as before while groups inside it keep core's gap
  - [x] Single (`templates/single.html`): posts, and the featured resources and AI tools, which have no single template of their own. Post title, date and content in the old structure; the Featured badge, the previous/next links and the comments are small blocks (`blocks/post-badge`, `post-navigation`, `comments`) printing what `single.php` printed. All 44 such pages match in position, size, style and text at 1280px and 390px (the date's wrapper is a div, and an empty post prints no empty content div). The page template gets the comments block too
  - [x] Archive and search (`templates/archive.html`, `templates/search.html`): what `index.php` rendered, namely category, tag, date and author archives, the resource taxonomy archives, and search results, in core's query blocks (query title, term description, post template, excerpt, pagination, no results). No `templates/index.html` yet: that file is what makes WordPress treat the theme as a block theme. Same positions and styles at 1280px and 390px; core's search heading quotes the term ("Search results for: “ai”"), and an author with no posts shows the heading above "No posts found."
  - [x] Partners archive (`templates/archive-partner.html`): the heading in core blocks, the Partner Type filter and logos as a partners directory block (`blocks/partners-directory`) printing `template-parts/components/partners-directory.php`, which `archive-partner.php` now includes (byte for byte the same). Every element matches at 1280px and 390px
  - PHP templates keep working next to block templates (a more specific PHP file wins, in block themes too), so the data-heavy templates (single resource, partner, live session and timeline; the resources, live sessions, AI tools and featured resources archives; National Conversation; Assets Pack) stay PHP until each is worth rebuilding. Their custom fields start with an underscore, which core's post-meta binding source cannot read, so a rebuild needs a small binding source of our own
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
