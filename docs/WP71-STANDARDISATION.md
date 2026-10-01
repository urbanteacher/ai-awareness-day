# One way to edit, on WordPress 7.1

The site is a block theme on WordPress 7.1.2 (local and live, checked 1 October 2026), and says so: `theme.json` is v3 and points at the 7.1 schema, and every block is block API v3. Underneath, a good deal of it still works the 6.x way, so editors meet two or three different ways of editing depending on what they open. This is the plan to make it one, and the check that stops it drifting back.

**Two principles, from the people who edit it:**

1. **A page is edited on the page.** The homepage, National Conversation, Walkthrough, Assets Pack and Press Release are blocks on the canvas, with instant access to edit in place. That works and is the model for pages.
2. **A record is edited in a focused editor.** The teacher editor keeps a lesson's whole plan in one place, in the order the lesson page shows it. That is the model for content that is data (lessons, timeline entries, events, partners).

## Where we were (audit, 1 October 2026)

Counted from source by `scripts/audit-wp71.mjs`. Counts are by pattern, so they say where the old habits are, not how any page looks.

| Habit | Count | What it is |
|---|---|---|
| Meta boxes shown in the block editor | 11 | In 7.1 they sit in a collapsed "Meta Boxes" drawer under the canvas. A type whose content is in them opens to an empty page. |
| Customizer settings / reads | 41 / 80 | Hero, campaign, contact, social links, SMTP, section wording: content, kept in a preview panel. |
| Shortcodes / shortcode-only blocks | 14 / 12 | 12 of the plugin's 14 blocks are a `render.php` that runs the shortcode. |
| `admin-ajax` handlers | 35 | Against 3 REST routes. |
| `wp_localize_script` / jQuery-dependent scripts / echoed `<script>` tags | 11 / 5 / 10 | Globals, jQuery, inline tags. |
| PHP templates (`get_header()`) | 20 | Against 11 block templates. Cannot be edited in the Site Editor. |
| Hand-written, unbuilt theme blocks | 26 | A second way of writing blocks next to aiad-core's built ones. |
| Version floors below 7.1 | 5 | The theme, three plugins and a readme said 6.0 to 6.6 (and "tested up to" 6.6 or 6.9) on a site that only runs, and is only tested on, 7.1.2. Fixed. |

Not used anywhere: block bindings, the Interactivity API, script modules, the Abilities API. The admin screens (17 menu or settings pages) are PHP forms; none use DataViews.

**What is genuinely 7.1:** WordPress 7.1.2 and PHP 8.3 running; `theme.json` v3; block API v3 on all 40+ blocks; one header and footer (`header.php` and `footer.php` only call the block parts); the homepage and five pages as editable blocks; patterns; the bundled plugins.

## The standard

Three editing surfaces. Each kind of content has exactly one.

| Content | Surface | Examples |
|---|---|---|
| A page | Blocks on the canvas, edited in place | Homepage, National Conversation, Walkthrough, Assets Pack, Press Release, hub pages |
| A record | A Details panel in the sidebar, plus a block on the canvas where the content is structured meta | Lessons, timeline entries, events, partners, AI tools, featured resources |
| Site settings | One settings screen (Settings → AI Awareness Day), and the Site Editor for the header, footer and wording | Logos, social links, contact, SMTP, event dates, SEO |

**Rules for records**

- The fields are on the editor's own screen. Nothing a person must fill in lives in the Meta Boxes drawer.
- **Two variants, one kit.**
  - *The body is the content* (timeline entries, events): the canvas stays the normal editor, and the type gets a Details panel. Timeline is the first.
  - *The content is structured meta* (lessons): a block on the canvas draws the fields in the order the front end shows them, and the type also gets a Details panel. Lessons are the first. The block saves nothing into the page, so the front end and anything else that reads the meta need no change.
- **Use core's panel where core has one.** Topics, Session length, Formats and the benchmark audience are core taxonomy panels. Our panel only replaces core's where the choice differs (a lesson has one theme, so it is a radio group, and core's checkbox panel is hidden).
- A field is registered with `show_in_rest` and stored under the same meta key as before, so a conversion needs no data migration and the classic editor keeps working.
- **A field the server changes is not in REST.** The block editor sends back every meta field it was given when a record is saved, not just the one that changed. A counter that moved while an editor had the screen open (views, joins, likes, clicks) would be rolled back to the value it had when the screen opened. Counters are therefore registered with `show_in_rest => false` (`modules/tracking/engagement-tracking.php`, and the timeline like count). Nothing reads them over REST. A new field written by the front end or by a background job follows the same rule. The old meta box is kept for the classic editor only, flagged `__back_compat_meta_box`.
- Words and option lists stay defined once, in PHP, and reach the panel through `block_editor_settings_all` (see the timeline's `aiadTimeline`).

**The kit** (`plugins/aiad-core/src/shared/record-editor/`):

| Piece | What it does |
|---|---|
| `registerRecordDetails()` | Registers the sidebar panel; checks the post type; hides core's duplicate panels; opens the first time an editor sees it |
| `useRecordMeta()` | The post's meta, with `set`, `text`, `flag` and `list` helpers |
| `Section`, `Rows` | The canvas block's building blocks: a ruled section, and a list with add, reorder and remove |
| `FocalPoint` | An image's focal point, on core's Focal Point Picker |
| `SingleTerm` | A taxonomy where a record has exactly one term (a lesson's theme), as a radio group or a select; replaces core's checkbox panel |
| `CardImageKeywords` | The card image keywords and the Fetch image button, which sets the featured image |
| `useEditorSetting()` | A value PHP put in the editor's settings |

**Adding a type** takes: (1) register any missing meta and taxonomies for REST; (2) a file in `src/editors/<type>.js` calling `registerRecordDetails()` (or a block in `src/blocks/` if the content is structured meta, with the panel in its folder); (3) PHP passes any option lists through `block_editor_settings_all`; (4) the old meta box gets `__back_compat_meta_box`; (5) `npm run build` in `plugins/aiad-core` and commit `build/`; (6) run the audit and `--update` the baseline.

## Order of work

| # | Work | Moves | Status |
|---|---|---|---|
| 0 | Declare 7.1 truthfully; add the audit ratchet | `staleVersionFloors` 5 to 0 | **Done** |
| 1a | Lessons: Lesson plan block and Lesson details panel | `recordEditors` | **Done** |
| 1b | Shared record editor kit; timeline entries | `recordEditors`, `metaBoxesInBlockEditor` 11 to 10 | **Done** |
| 1c | Events (`live_session`) | `recordEditors`, `metaBoxesInBlockEditor` 10 to 9 | **Done** |
| 1d | Partners | `recordEditors`, `metaBoxesInBlockEditor` 9 to 7 | **Done.** The two old boxes showed the profile intro and the links but never saved them; the panel does. |
| 1e | Featured resources, AI tools | `recordEditors` 4 to 6, `metaBoxesInBlockEditor` 7 to 5 | **Done.** AI tools had no block editor at all (no `editor` support, so they opened the classic screen); they now have it. |
| 1f | Admin-only boxes (survey responses, form submissions with their certificate and letter boxes) | `metaBoxesInBlockEditor` 5 to 0 | **Decided: they stay as admin screens.** They are records people read and act on, not content they write, and their post types are not in REST, so WordPress opens them on the classic edit screen. Import/export is an admin page, not a box, and is part of the settings work in row 2. |
| 2a | The settings screen, with the two options that were already outside the Customizer (campaign and contact, SEO and sharing) | `settingsApiForms` (new, 0), `settingsScreenOptions` (new, 2) | **Done.** Settings → AI Awareness Day. Details below. |
| 2b | The site-wide Customizer settings: footer links, breadcrumbs, the header logo fallback, social links, press release file, Assets Pack images | `customizerSettings` 41 to 29, `themeModReads` 80 to 70, `settingsScreenOptions` 2 to 3 | **Done.** Details below. Left for 2c: the hero and AI literacy logos and the Get Involved wording, which are homepage content; the SMTP note moves in 2e. |
| 2c | The Customizer's homepage controls (hero, campaign, YouTube, badges, principles, toolkit, display board, section order and visibility) | `customizerSettings`, `themeModReads` | Waits on one fact: whether the live site runs the block homepage. Locally it does (`aiad_block_homepage_id` is set), and the Customizer already hides these controls then. If live does too, they are removed with their readers; if not, the homepage is converted first with the buttons on Appearance, Block homepage. |
| 2d | Whether the footer's links and social icons become blocks in the Site Editor | `blockBindingSources` | Decide after 2b. If editors should change them in the Site Editor, a small block bindings source over the settings option feeds a Button or Paragraph (both support bindings). If they only ever change on the settings screen, the footer stays PHP reading the option. |
| 2e | Remove `inc/customizer.php`, `inc/customizer-smtp-control.php` and `inc/front-page-layout.php`; the SMTP note moves to the settings screen | `customizerSettings` to 0 | After 2b and 2c. |
| 3 | New server calls as REST routes; scripts off jQuery and globals | `ajaxHandlers`, `localizeScript`, `jquerySignedScripts`, `echoedScriptTags` | Not started. New code first; old handlers only when touched. |
| 4 | Shortcode-only blocks become real blocks; the 26 unbuilt theme blocks move to aiad-core | `shortcodeBlocks`, `shortcodes`, `unbuiltThemeBlocks` | Not started. Keep the shortcodes until the pages using them are re-saved. |
| 5 | PHP templates: each stays on purpose or becomes a block template | `phpTemplates` | Not started. See below. |

### The settings screen (2a)

**What the WordPress docs say for 7.1, and what this follows.** A plugin settings screen is: options registered with `register_setting()` and a `show_in_rest` schema; a menu page under Settings; a script built with `@wordpress/scripts`, reading and saving through core's `/wp/v2/settings` endpoint (in core-data that is the `site` entity); core's components for the controls. The developer blog's DataForm tutorial uses the same pieces. This follows it, with one change that matters:

- **DataForm is not available to plugins on WordPress 7.1.2.** Checked on the running site: no `wp-dataviews` script handle and no DataViews script module is registered; the package is bundled only into core's own admin pages. The tutorial depends on a `wp-dataviews` handle, which exists with the Gutenberg plugin but not here, so following it as written would load a script whose dependency never loads. The screen is built from `@wordpress/components` (Card, TextControl, TextareaControl, Notice, Button) and `useEntityRecord( 'root', 'site' )` instead. Bundling the npm package into the plugin would be the other way; it was not chosen because it would ship a second copy of a package that drifts from core's own components, for a form of thirteen text fields. Revisit when core registers it.
- **`@wordpress/build` pages and routes are not used.** The docs mark them experimental ("subject to drastic and breaking changes").
- **No script globals.** What the form needs from PHP (the default date and the admin email for help text) is JSON on the mount element, not `wp_localize_script`.

**How it behaves**

- Fields are checked in the screen as they are typed (a real date, a valid email, a full `https://` address) and Save is off while one is wrong. The server keeps its own rules, because a write need not come from the screen: the REST schema refuses a malformed date and unknown keys, and the options' sanitisers keep the previous value for anything else. After a save the screen compares what it sent with what came back and says which field the server did not accept, so it never reports "saved" about a value that was refused.
- The options are unchanged (`aiad_campaign`, `aiad_seo`), so nothing that reads them moved. The two Settings API pages are gone; their old addresses redirect to the new screen (through `admin_page_access_denied`, because a page that is no longer registered is refused before `admin_init`).
- **Three faults found by testing, fixed:** (1) a date that matches the pattern but does not exist (`2027-02-31`) crashed the request, because the campaign sanitiser called `add_settings_error()`, which WordPress only loads on admin screens, not for REST. The sanitiser now keeps the previous value without it. (2) Both sanitisers called `wp_unslash()`, which the old form needed but REST does not (REST input is not slashed), so a backslash in the site name or description was stripped on every save. (3) A write naming only some SEO keys blanked the rest; a key that is not sent now keeps its value.

### Site links and files (2b)

**Where the values live.** A new option, `aiad_site` (`plugins/aiad-core/modules/site-settings.php`), holds the footer's newsletter, Assets Pack and Implementation Guide addresses, the breadcrumbs switch, and five files (header logo fallback, press release, Assets Pack logo and two banners, as attachment IDs). The footer's LinkedIn and Instagram addresses are the SEO option's social profiles, so the footer and the search-engine markup share one address.

**How it moved without changing a page.** The option is the source of truth the way `aiad_campaign` already was: the `theme_mod_*` filter returns it, and a Customizer save is copied into it. The first read copies each value from the stored theme mod, or from the default the Customizer would have shown, so the site is the same the moment the code loads. The option holds the value in effect, so empty means empty (a footer link left blank shows as pending). The readers in the theme then switched to two helpers, `aiad_site_value()` and `aiad_social_url()` (`inc/helpers.php`), which fall back to the theme mod when the plugin is off. Ten reads of `get_theme_mod()` went. The Customizer controls and the 313 lines that registered them went too, so each value is edited in one place.

**The one place data could have been lost, and what stops it.** The footer's LinkedIn and Instagram addresses used to be two copies: the Customizer's (what visitors see) and SEO's (copied from it once, then separate). If they had drifted, switching the footer to SEO's would have changed a public link. A one-time step on `init` (`aiad_site_reconcile_social()`) makes the footer's address win where they differ, and keeps SEO's previous value in the option `aiad_social_reconciled` with the time. Where they already agree it records an empty list.

**Files.** The picker is `MediaUpload` from `@wordpress/media-utils`, which is the public media-library component, with `wp_enqueue_media()` on the screen. The package's newer `MediaUploadModal` is a private API in 7.1.2 (locked behind `lock`/`unlock`), so it is not used. Files are stored as attachment IDs; the server turns an ID that is not an attachment into none.

**Fault found while testing, fixed before this** (committed on its own as `462f4429`): registering the campaign and SEO options for REST in 2a put their sanitisers on every request, and the sanitisers called the readers that create the option on first read, which ran the sanitiser again. A site without the option ran out of memory on the front end. Found by deleting both options and loading the front page. The sanitisers now read the stored value directly, and this module's sanitiser does the same.

### Deliberately not doing

- **The Interactivity API, script modules or block bindings for their own sake.** The aim is a consistent editing experience, not a count of APIs. They get used when a feature needs them.
- **Turning every data-heavy template into blocks.** WordPress supports a PHP template that wins over a block one. The single resource, timeline, partner and event pages and their archives stay PHP until an editing need appears. When it does, the way is a small block-bindings source of our own for the `_aiad_*` meta (the migration doc explains why core's meta source cannot read them), so those pages become editable in the Site Editor without losing their logic.
- **Raising the PHP floor.** It stays 8.0 because the live PHP version is not known from here.

## The ratchet

```sh
node scripts/audit-wp71.mjs            # compare with docs/wp71-baseline.json
node scripts/audit-wp71.mjs --update   # accept today's counts
```

A habit may go down, a 7.1 count may go up, and anything else fails. Run `--update` only when a number moved for a reason you can write here, in the log below. It reads source only, so it needs no WordPress. It is not wired into any automatic check yet; there is no CI in this repository.

## What was and was not verified

- The audit counts were run against the source and checked by hand against greps for the same patterns.
- Lessons and timeline entries were opened in the 7.1.2 block editor: the panels appear, the old boxes and the drawer do not, edits save, and the saved values read back correctly from the database. The focal point was set through the picker's own inputs with a real image attached.
- Events were opened in the editor and a real save was read back from the database. A counter changed in the database while the event was open survived the save (see the rule above; it did not before the fix). A new event starts with the usual format filled in.
- Partners were opened in the editor and every field was set through the real controls (including adding links with the Add link button), saved, and read back from the database. The profile intro and the links then appeared on the partner page, and the stats and AI link on the homepage card. Switching "provides AI resources" off stored an empty value, the homepage dropped the link, and the tracking query that tests for `'1'` found no flagged partners. An empty link row is dropped, as the old rule did.
- Featured resources were opened in the editor and every field set through the real controls (including the theme radio and the format select), saved, and read back from the database, then restored. Fetch image runs the real handler; on this machine it fails with "Download failed: Unauthorized" because loremflickr.com now answers 401. A success was checked only against a stubbed response. If the live site uses the same source, the button is broken there too and was before this change.
- AI tools were opened in the block editor (the classic screen before), a use case and features were changed through the panel, saved, read back from the database, and shown on the AI tools page; the single page still returns 200. The original values were put back. The features field strips markup on save, as the classic box did.
- The lesson panel was re-opened after it moved to the shared theme radio and Fetch image control; both render and the theme shows the saved value. The focal point picker only shows once the lesson has a featured image, as before.
- The survey response and form submission edit screens were fetched as the editor would load them: both are the classic screen (no block editor), and their four boxes (submission details, certificate, thank-you letter, survey response) are all there. This is why the audit no longer counts them.
- The settings screen was opened on the running site, and every field was set through the real controls and saved. The saved options were read back from the database, the site name appeared in the page's `og:site_name`, and a contact address saved on the screen reached the Customizer's `aiad_contact_email` through the existing mirror. A malformed email and a URL without `https://` turned Save off with a message. An address the screen accepts but WordPress rejects (`a@b.c`) produced the "server did not accept" warning and kept the old value. Over REST: a malformed date and an unknown key are refused (400), a non-existent date keeps the old date, a backslash survives, and a one-key write keeps the other keys. The options were put back and compared with a copy taken first: identical. The old addresses redirect.
- Site links and files were set through the real screen. The saved option was read back from the database; the Customizer's stored theme mods agreed (the mirror); the public footer showed the new newsletter, guide and both social addresses, and breadcrumbs appeared on an inner page. A malformed address turned Save off. The real media library modal opened from the picker (filtered to images), a logo was chosen, its thumbnail and file name showed, and after saving the Assets Pack page showed it. Over REST: a non-attachment ID became none, `javascript:` was stripped from a URL, an unknown key and a non-boolean breadcrumbs value were refused, and a partial write kept the other values. A Customizer save (`set_theme_mod`) reached the option. The one-time social step was run with real drift (footer and SEO differing): the footer's address won and SEO's old values were kept. After the readers switched, the footer output was identical to before. The Customizer and its preview load, with only the homepage sections left. Local options were put back and compared.
- Not done on 2b: the live site's Customizer values are not visible from here, so the first-read copy and the social step have been run only on local data. Run on staging first. The Customizer's own save flow was exercised through `set_theme_mod`, not by clicking Publish in the Customizer.
- Not done: no test with a second person editing the same entry at once (7.1's real-time collaboration); no keyboard-only or screen-reader pass over the new panels; the live site has not been touched, and the classic editor path was checked only by reading the code.
- The audit says where the habits are. It does not say that every page is fine; the page-by-page comparison in the migration doc still applies.

## Log

- **1 October 2026 (2b).** Site links and files moved to Settings → AI Awareness Day on the new `aiad_site` option, with the footer's social links joined to SEO's. Readers switched to `aiad_site_value()` and `aiad_social_url()`; the Customizer controls and their registration code were removed (`customizerSettings` 41 to 29, `themeModReads` 80 to 70, `settingsScreenOptions` 2 to 3). The first-read recursion in the campaign and SEO options was found and fixed before this (see the section above). The press release page returns 404 on the local site; it was a pending footer link before and after. Still on the Customizer: homepage content (2c), the SMTP note and the front page layout (2e).

- **1 October 2026 (site settings).** Row 2 broken into 2a to 2e. 2a done: Settings → AI Awareness Day replaces the Campaign and contact and the SEO and sharing pages, on `register_setting` with a REST schema, core-data's `site` entity and core's components. WordPress 7.1.2 gives plugins no DataForm (no `wp-dataviews` handle or module is registered), so the documented tutorial could not be followed as written; the reasons and what was used instead are in the section above. Fixed while testing: a fatal on an impossible date (`add_settings_error()` is admin-only), `wp_unslash()` stripping backslashes from REST input, and a partial write blanking the other SEO keys. Audit: `settingsApiForms` (new habit, 0) and `settingsScreenOptions` (new, 2). No Customizer setting has moved yet: `customizerSettings` is still 41 and `themeModReads` 80, and the plan says why they cannot move until the 2b mirror and the 2c question are settled.

- **1 October 2026 (admin-only boxes).** Decided that survey responses and form submissions stay admin screens. Their post types have `show_in_rest` off, so WordPress opens them on the classic edit screen, and their four boxes (submission details, certificate, thank-you letter, survey response) are that screen's form, not a box in the block editor's drawer. The audit now counts a box only if its post type can open in the block editor, so `metaBoxesInBlockEditor` goes 5 to 0. That change is in how it counts, not in any screen, and the log says so to keep the number honest. If one of these types ever gets `show_in_rest`, its box is counted again. The audit also stopped counting a function named `aiad_survey_add_meta_box()` as a call to `add_meta_box()`, which had added one to the count. Not done: these screens are still PHP forms (no DataViews), and the submission screen's chase-up note and status save on a classic form post.

- **1 October 2026 (end of day).** Featured resources and AI tools moved to the record editor (Resource details, Tool details). Featured resources get one panel with the link, organisation, card image keywords, one theme (radio) and an optional format; core's duplicate Themes and Format panels are hidden, session length keeps core's panel. AI tools had `title, editor, excerpt, thumbnail` support but no `editor` support the block editor needs, and no REST meta, so they opened the classic screen; they now declare `custom-fields` and register their meta with sanitisers. An AI tool now has a post body that nothing shows yet. The lesson panel's theme and Fetch image were rebuilt on two new kit pieces (`SingleTerm`, `CardImageKeywords`) so lessons and featured resources share them; the lesson panel's Fetch image had been dropped in the first conversion and is back. `recordEditors` 4 to 6, `metaBoxesInBlockEditor` 7 to 5. Still in REST and server-managed: the timeline's `_aiad_timeline_source`, `auto_type`, `related_id` and `hub_slug`. Nothing writes them while an editor is open, so I left them; they follow the counter rule if that changes.

- **1 October 2026 (later still).** Partners moved to the record editor (Partner details panel). Found in passing: the old Partner URL box rendered the profile intro and the partner links, and `single-partner.php` reads them, but the box's save handler never listed them, so nothing an editor typed there was kept. No partner had a value. The panel saves them. The classic box's save list is unchanged, so the classic editor still drops them: it is no longer used by the block editor and I did not test the classic path. The audit's meta-box counter mistook an apostrophe in a comment for the start of a string and miscounted by one after the partner change; it now skips comments. `recordEditors` 3 to 4, `metaBoxesInBlockEditor` 9 to 7.

- **1 October 2026 (later).** Events moved to the record editor (Event details panel). While testing it, saving an event reset its view counter: the editor saves all the meta it holds, and the engagement counters were in REST. This also affected the timeline entry panel (like count) pushed earlier the same day. Counters are now out of REST (six content types' engagement counters and the timeline like count). Times use the browser's own date and time field so the stored `YYYY-MM-DDTHH:MM` and the front end are unchanged; a time in any other format is refused by the field's sanitiser. `recordEditors` 2 to 3, `metaBoxesInBlockEditor` 10 to 9.

- **1 October 2026.** Audit written. Versions declared as 7.1 (the live site runs 7.1.2). Lessons and timeline entries moved to the record editor kit. `recordEditors` 0 to 2, `metaBoxesInBlockEditor` 11 to 10, `staleVersionFloors` 5 to 0 (recorded in the baseline after the fix, so the file itself shows 0). Featured resources: core's duplicate Themes and Session length panels, which appeared when lessons needed those taxonomies in REST, are hidden.
