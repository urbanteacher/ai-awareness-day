# AI Awareness Day Core

The site's functionality, kept separate from the theme so a redesign or a switch to a block theme cannot break it. The theme keeps presentation only. Part of Stage 1 of [the block theme migration](../../docs/BLOCK-THEME-MIGRATION.md).

**Status:** thirteen blocks (the interactive tools, the survey, the certificate showcase and the two benchmark blocks); modules `helpers-data`, `post-types`, `contact`, `resource-filter`, `tracking`, `admin`, `timeline`, `live-sessions`, `certificates`, `ai-tools`, `benchmark-content`, `survey`, `tools`, `certificate-showcase`, `seo` moved. All modules on the map are moved.

## Layout

```
aiad-core/
├── aiad-core.php          header, constants (AIAD_CORE_DIR / _URL / _VERSION), loader
├── includes/
│   ├── modules.php        which modules the plugin provides; the theme skips those
│   └── blocks.php         "AI Awareness Day" inserter category; registers build/blocks/*
├── modules/               code moved from the theme's inc/, one file or folder per module
├── src/blocks/<name>/     block.json, index.js, edit.js, render.php
├── build/blocks/<name>/   compiled by `npm run build` (committed)
└── package.json           @wordpress/scripts
```

## Working on blocks

```bash
cd plugins/aiad-core
npm install
npm run build     # or: npm start (rebuilds on save)
```

Commit `build/`. Deploys copy the plugin as it is and run no build step.

A new block is a folder in `src/blocks/` with a `block.json`. It is registered automatically once built. For a tool that already has a shortcode, `render.php` calls the shortcode function, so the block and the shortcode give identical output and existing posts keep working. `npm run lint:js` checks the source.

Blocks so far, all in the "AI Awareness Day" inserter category, one per post (`multiple: false`, the tools use fixed element IDs):

| Block | Shortcode | Settings |
|---|---|---|
| `aiad/speed-quiz` | `[aiad_speed_quiz]` | questions, seconds, bonus, points |
| `aiad/buzzwords` | `[aiad_buzzwords]` | hide intro, show quiz |
| `aiad/llm-explainer` | `[aiad_llm_explainer]` | hide intro, "explore more" link |
| `aiad/llm-order-game` | `[aiad_llm_order_game]` | — |
| `aiad/computing-curriculum` | `[aiad_computing_curriculum]` | — |
| `aiad/ict-curriculum` | `[aiad_ict_curriculum]` | — |
| `aiad/misinformation-detector` | `[aiad_misinformation_detector]` | introduction: automatic / show / hide |
| `aiad/neu-ai-report` | `[aiad_neu_ai_report]` | headline: automatic / show / hide |
| `aiad/risk-academy` | `[aiad_risk_academy]` | the eight sections on or off |
| `aiad/national-survey` | `[aiad_national_survey]` | — |
| `aiad/certificate-showcase` | `[aiad_certificate_showcase]` | wording, button, example strand, background, section ID (empty = default) |
| `aiad/risk-benchmark` | `[ai_risk_benchmark]` (benchmark plugin) | — |
| `aiad/school-dashboard` | `[ai_risk_school_dashboard]` (benchmark plugin) | school (empty = `?school=` parameter) |

The two benchmark blocks call the bundled AI Risk & Readiness Benchmark plugin's own shortcode methods and render nothing without it. They live here rather than in that plugin, which deploys on its own version bumps and ships standalone; the benchmark plugin itself is unchanged. Their editor previews are unstyled: the benchmark's CSS belongs to that plugin and is only registered on the front end.

The tool blocks share one editor (`src/shared/tool-block.js`): a server-rendered preview plus sidebar settings described in each block's `index.js`. `src/blocks/buzzwords/` is the pattern to copy.

Each tool's `aiad_post_content_has_*_shortcode()` check also looks for its block (`has_block()`), so a block-only page gets the tool's CSS in the head, as a shortcode page does.

Two expected differences from the shortcode, both standard WordPress behaviour: the shortcode's output gets a stray `<p>` from `wpautop` and the block's does not, and block output goes through `wptexturize` (so `...` becomes `…`) because blocks render before that filter and shortcodes after it.

## WordPress compatibility

Checked against WordPress 7.1.2 (latest, PHP 8.3; now also the local Docker image) and 6.6.2 (PHP 8.2), with `WP_DEBUG` on: every page, endpoint and module check behaves the same on both, all 13 blocks register and preview, and `aiad-core` logs no notices, warnings or deprecations.

- **Blocks** are registered from `build/blocks-manifest.php` with `wp_register_block_types_from_metadata_collection()` on 6.8+, and block by block on older versions ([docs](https://developer.wordpress.org/block-editor/getting-started/fundamentals/registration-of-a-block/)). Both routes register the same blocks.
- **Iframed editor** (always on from 7.1): editor styles come from each block's `block.json` (`editorStyle`) and the theme's `add_editor_style()`, both injected into the iframe. Nothing reaches into the editor's `document`.
- **`"role": "content"`** on the certificate showcase's text attributes, so they stay editable inside patterns (contentOnly by default since 7.0).
- **Deprecated functions**: none. `get_page_by_title()` (deprecated in 6.2) is replaced by `aiad_core_get_post_by_exact_title()`, the dev note's `WP_Query`; it returns the same post for every current title (it skips trashed posts, which the old function did not).
- **Plugin header**: `Update URI: false`, so WordPress never offers an update from a WordPress.org plugin with the same slug. `Requires at least: 6.6` and `Requires PHP: 8.0` match what the code uses.
- The editor previews use `ServerSideRender`, which the block editor docs treat as a fallback for dynamic blocks; it fits these shortcode-backed tools.

## Moving a module out of the theme

There is only ever **one copy** of a module: the file in `modules/`. This plugin folder ships inside the theme, so the theme can load the same file from `plugins/aiad-core/modules/` when the plugin isn't active. `helpers-data` is the worked example.

1. Move the functions from the theme's `inc/` file into `modules/<module>.php`, unchanged. Remove them from the theme file.
2. At the top of the module, after the `ABSPATH` check, mark it as loaded:
   ```php
   define( 'AIAD_CORE_MODULE_POST_TYPES', __FILE__ );
   ```
   The plugin's loader skips a module whose constant is already defined. Without this, activating the plugin is fatal: the theme has already loaded the module in that request, and PHP declares a file's functions before any code in it runs, so the check can't live inside the module itself.
3. Add the module to `aiad_core_modules()` in `includes/modules.php`.
4. In the theme's `functions.php`, replace the old `require_once` with:
   ```php
   aiad_require_core_module( 'post-types' );
   ```
   It loads the theme's bundled copy unless the active plugin already provides the module. Keep it at the same position the old require was, so load order stays the same with the plugin off.
5. For assets the module now owns, move them into the plugin and use `aiad_core_path()` / `aiad_core_url()` (the `paths` module). They work from both copies of the plugin; `AIAD_CORE_DIR` / `AIAD_CORE_URL` only exist when the plugin is active. Assets still in the theme keep the theme constants.
6. Test with the plugin active, inactive, and while activating it: pages should match byte for byte and the log should show no errors. For modules that register things, also compare rewrite rules, post types, taxonomies, meta, REST output, meta boxes, admin menus and hook priorities.

**Watch the load order.** With the plugin active, a module's hooks are added before any of the theme's, so callbacks that share a hook and priority with theme code now run first. `post-types` hit this with the Resources admin submenu order and `tracking` with the dashboard widget order; a priority bump fixed each. Admin pages added from the plugin also change the internal key order of `$submenu` (which parent entry is created first); that is harmless, because the sidebar is drawn from `$menu`, and the rendered menu stays identical. A module made of several files can use a folder plus an entry file that defines the constant and requires the rest (see `modules/post-types.php`).

Once the plugin is live everywhere, the theme's `aiad_require_core_module()` calls can go.

Keep text domains as `ai-awareness-day` while moving, so existing translations still match. Switch to `aiad-core` later, in one pass.

## Module map

Where each file in the theme's `inc/` should end up.

### To the plugin

| Module | Files | Notes |
|---|---|---|
| `helpers-data` ✅ | data half of `helpers.php` | **Moved.** Most other modules depend on it. Resource key stages and durations, organisation types, `aiad_get_post_by_title`, `aiad_youtube_video_id`, pledge counts, `aiad_normalise_*`. `aiad_sanitize_event_date_ymd` stayed in the theme: it falls back to a Customizer default |
| `post-types` ✅ | `post-types.php`, `field-registry.php`, `validation.php`, `admin-taxonomy-fields.php`, `resource-seeds.php` | **Moved** to `modules/post-types/`. `resource`, `partner`, `featured_resource`, `form_submission`. The Resources → Settings menu item now uses priority 11, so it stays below the theme's Import/Export items |
| `admin` ✅ | `meta-boxes.php`, `admin-columns.php`, `import-export.php`, `entry-figure.php`, `submissions-csv-export.php`, card image fetch (from `ajax-handlers.php`) | **Moved** to `modules/admin/`, with the same `is_admin()` conditions as before. `entry-figure.php` (focal point) and the card image fetch load on every request. Edit-screen scripts and styles still load from the theme via `AIAD_URI` (`assets/js/admin-*.js`, `admin/css/`), and `import-export.php` reads its sample file from the theme's `archive/` folder |
| `contact` ✅ | first part of `ajax-handlers.php` | **Moved.** Get Involved form handler, checklist labels, client IP / fingerprint. Reads the recipient from the Customizer (`aiad_contact_email`, see Risks) |
| `resource-filter` ✅ | middle of `ajax-handlers.php` | **Moved.** AJAX filter for both resource archives and the cached filter counts. Still renders the theme's `resource-tile` template part with `get_template_part()` (decision: keep until the card becomes an `aiad/resource-card` block with the archive templates in Stage 3) |
| `tracking` ✅ | download and view counters (from `ajax-handlers.php`), `engagement-tracking.php`, `dashboard.php` | **Moved** to `modules/tracking/`. The Campaign dashboard widgets now use priority 11, so they stay below the theme's Assets Pack widget. |
| `certificates` ✅ | `certificate-api.php`, `certificate-copy.php`, `certificate-admin.php`, `letter-copy.php`, `letter-admin.php`, `generator-embed.php` | **Moved** to `modules/certificates/`, same order and `is_admin()` conditions. The generator tools are HTML files in the theme (`archive/theme/generators/`), linked through `get_template_directory_uri()`; the REST bootstrap uses the theme's logo helpers (runtime only) |
| `timeline` ✅ | `cpt-meta.php`, `admin-meta-box.php`, `entries.php`, `topics.php`, `topic-assignments.php`, `query.php`, `ajax.php`, `benchmark-audience.php`, and the data half of `icons.php` (now `icon-options.php`) | **Moved** to `modules/timeline/`. The theme's `inc/timeline.php` loads the module, then the presentation files it keeps. The AJAX filter still renders with the theme's `timeline-layouts.php`, like the resource filter. `entries.php` still calls the theme's `aiad_get_customizer_defaults()` / `aiad_sanitize_event_date_ymd()` (runtime only) |
| `live-sessions` ✅ | `live-sessions.php` except its four markup helpers | **Moved** to `modules/live-sessions.php`: the `live_session` CPT and audience taxonomy, meta box, seeds and migrations, data and formatting helpers, admin columns, legacy `/schedule/` redirects and the calendar (ICS) feed. The action link and its icon, the audience tabs and the inline filter script stay in the theme's `inc/live-sessions.php` |
| `ai-tools` ✅ | `tools.php` except its row renderer | **Moved** to `modules/ai-tools.php`: the `ai_tool` CPT, `tool_category` taxonomy, seeds, meta and meta box. `aiad_render_tool_row()` stays in the theme's `inc/tools.php` (AI Tools archive, homepage tools section) |
| `tools` ✅ | `ai-buzzwords.php`, `ai-llm-explainer.php`, `ai-llm-order-game.php`, `ai-speed-quiz.php`, `ai-computing-curriculum-challenge.php`, `ai-ict-curriculum.php`, `ai-misinformation-detector.php`, `ai-neu-ai-report.php`, `ai-neu-ai-report-data.php`, `schools-ai-risk-academy.php` | **Moved** to `modules/tools/`, in their original order, listed before `benchmark-content` (shared seed priorities). Their CSS, JS, the NEU report data, the Risk Academy fonts and template moved too, into `assets/` and `template-parts/` laid out as in the theme (so relative font paths still work), loaded through `aiad_core_path()` / `aiad_core_url()`. `ai-curriculum-quiz.php` stays in the theme's `inc/`, unloaded: it was retired in cdc60430 and its two posts are in the trash |
| `survey` ✅ | `national-survey.php` | **Moved** to `modules/survey.php`: `survey_response` CPT, `[aiad_national_survey]` shortcode, AJAX submit, meta box, add-to-timeline action, timeline seed, CSV export, columns, analytics page, survey page creation. Its CSS and JS moved to the plugin's `assets/`; `aiad/national-survey` block |
| `certificate-showcase` ✅ | `certificate-showcase.php`, its template part and stylesheet; `benchmark-promo.php` | **Moved.** `modules/certificate-showcase.php` renders `modules/certificate-showcase/template.php` (`load_template()`, same `$args`) and loads `assets/css/certificate-showcase.css` from the plugin; the `aiad/certificate-showcase` block and `[aiad_certificate_showcase]` share it. The benchmark promo helpers joined `benchmark-content` (also used by the homepage promo, the previous hero and the National Conversation page) |
| `campaign` ✅ | new | The event date (Y-m-d) and the contact form recipient, in the `aiad_campaign` option (Settings → Campaign & contact). The single source of truth for them: the `theme_mod_aiad_event_date_ymd` / `theme_mod_aiad_contact_email` filters (priority 5, so a Customizer preview still wins) give it to every reader (countdown, National Conversation page, homepage editor, toolkit section, benchmark plugin), and `pre_set_theme_mod_*` copies Customizer and homepage-editor saves into it; saving the settings page writes the theme mods back. Copied once from the stored theme mods; empty = not set, so each reader keeps its own fallback |
| `seo` ✅ | `seo.php`, `sharing.php`, `llms.txt` | **Moved** to `modules/seo/` (+ `llms.txt` at the plugin root). Has its own settings (Settings → SEO & sharing, `aiad_seo` option): site name, event date, homepage description, 7 social profile URLs and 3 verification codes, copied once from the Customizer on first use. The Customizer's SEO-only fields (5 social URLs, verification) are removed from it; LinkedIn/Instagram and the hero wording stay there for the footer and homepage |
| `benchmark-content` ✅ | `ai-risk-benchmark-post.php`, `airb-hub-timeline-seed.php` | **Moved** to `modules/benchmark-content/`. Seed content for the bundled benchmark plugin; the theme's `timeline-layouts.php` calls `aiad_risk_benchmark_get_excerpt()`. Keep this module listed after the interactive tools' modules when they move (see Risks) |

### Stays in the theme

| Files | Why |
|---|---|
| `setup.php`, `theme-assets.php` | Theme supports, menus, styles and scripts |
| `customizer.php`, `customizer-smtp-control.php`, `front-page-layout.php`, `admin/class-aiad-homepage-editor.php` | Replaced by block editing in Stages 2–3. The SMTP setting moves to a plugin settings page then |
| presentation half of `helpers.php` | Hero text, logos, partner marquee, countdown, press release URL |
| `timeline-layouts.php`, `timeline/icons.php` (SVG renderers), `timeline/single-helpers.php` | Timeline presentation, loaded by the theme's `inc/timeline.php`. Becomes blocks or templates later |
| `hub-resource-page.php`, `national-conversation-page.php`, `walkthrough.php` | Page routes and layouts. The data parts of `national-conversation-page.php` (totals, URLs used by `sharing.php`) move with `seo` |
| `benchmark-promo.php`, `admin-assets-pack.php` | Homepage and dashboard presentation |
| `bundled-plugins.php` | Installs the bundled plugins on deploy, so it has to stay in the theme |
| `migrate-2027-branding.php` | One-off migration; remove once it has run everywhere |

## Risks

- **Customizer values belong to the theme.** SEO no longer reads them: it has its own `aiad_seo` settings, copied from the Customizer once. The homepage wording and the SEO copies are now separate, so a change to one does not update the other. The contact recipient and the event date live in the `campaign` module's option, kept in step with their theme mods. No module reads a theme mod directly any more.
- **Load order.** Plugins load before the theme. Module code must only call theme functions inside hooks, never when the file loads, and must not rely on `AIAD_DIR` / `AIAD_URI` / `AIAD_VERSION` for anything it owns.
- **Settings pages follow the Settings API** (`register_setting` with a `sanitize_callback`, `add_settings_section` / `add_settings_field` with `label_for`, `do_settings_sections`), per https://developer.wordpress.org/plugins/settings/custom-settings-page/. Validation errors use `add_settings_error()` and keep the previous value; pages under Settings get their notices from WordPress, so they do not call `settings_errors()`.
- **Seed order on fresh installs.** Several one-off `init` seeds (benchmark content and the interactive tools' timeline entries) share priorities 33–35 and set no post dates, so their order decides how same-second entries tie on a brand-new site. `tools` is listed before `benchmark-content` in `aiad_core_modules()`, which keeps the theme's original order; keep it that way.
- **Deploy.** Production gets bundled plugins through `inc/bundled-plugins.php`, which copies **and activates** them. Adding `aiad-core` to `aiad_bundled_plugins()` ships it live. Do that only once a module has moved and been tested on staging.
- **Editor previews.** Each block's `block.json` loads its tool's stylesheet in the editor (`editorStyle`, a `file:` path to `assets/`), and the theme gives the editor its design tokens (`assets/css/base/tokens.css`), so previews use the site's colours and fonts. The tools' scripts do not run in the editor: previews show each tool's starting state. The editor styles only exist while this plugin is active; a block theme would need to provide the tokens itself (e.g. `theme.json` or its own editor styles).

## Local development

`docker-compose.yml` (WordPress 7.1, PHP 8.3) mounts this folder at `wp-content/plugins/aiad-core`. Activate it under Plugins (it's inactive by default on a fresh database).
