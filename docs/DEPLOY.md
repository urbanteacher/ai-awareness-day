# Deploying to Hostinger (git)

The repository is the theme. A git deploy puts every tracked file in `wp-content/themes/ai-awareness-day/` and does
nothing else; the theme then installs its plugins itself the first time WordPress loads it.

## What a deploy does

1. `functions.php` checks WordPress 7.1+ and PHP 8.0+ (`inc/requirements.php`). If the site is behind, the theme switches
   itself off: administrators see a notice, visitors see a 503 "being updated" page. Nothing else runs, so nothing breaks
   half-way. The same check is at the top of each plugin.
2. `inc/bundled-plugins.php` copies `plugins/aiad-core`, `ai-risk-readiness-benchmark` and `aiad-debate-network` into
   `wp-content/plugins/` when they are missing or older (a higher `Version:` header, or a newer sentinel file), and
   activates them. If the host blocks the copy, they load from the theme folder instead. aiad-core is listed as active
   without being included in that request (the theme has already loaded its modules); it loads from `wp-content/plugins`
   from the next request.
3. On the first request, once each, with a lock and a retry if one could not finish:
   - `aiad_maybe_convert_homepage()` builds the block homepage from the Customizer's section order, visibility and
     wording and makes it the front page. The old page and the Customizer values are kept.
   - `aiad_maybe_convert_theme_pages()` creates the National Conversation and walkthrough pages and gives the Assets Pack
     and Press Release pages their blocks (the old content is kept in `_aiad_builtin_content`).
   - aiad-core converts shortcodes in existing content to blocks, saving each post's old content first: as a revision
     (so revisions must not be switched off: `WP_POST_REVISIONS` is not `false` in `wp-config.php`), or in the
     `_aiad_pre_block_content` field for a post type without revisions (the timeline).
   - The theme's version (`AIAD_VERSION`, 1.6.0) changed, so permalink rules are flushed once on the first request: routes
     removed in this release (`/national-conversation/` was a virtual route) no longer redirect to the homepage. Bump the
     version with any change to post types or routes.
   - The settings the Customizer held are copied to `aiad_site`, `aiad_campaign` and `aiad_seo`. The Customizer values stay.

## Before the push

- [ ] Update **PHP to 8.3** (hPanel, Advanced, PHP Configuration) and **WordPress to 7.1** on the live site first. Pushing
      first leaves visitors on the 503 page until you do. Turn **auto-deploy off** until then.
- [ ] Full backup of files and database, downloaded (hPanel, Files, Backups). Auto-updates set to "No updates" meanwhile.
- [ ] Run `scripts/rehearse-deploy.sh` (needs the local docker stack): it deploys the tracked files onto a clean WordPress 7.1
      over a database backup and reports plugins, migrations, data kept, every page and the PHP log. Run it again after
      any change, then `--down`.
- [ ] Rehearse on a Hostinger **staging** copy first. Do not push staging's database to live: it overwrites survey,
      benchmark, contact and debate data submitted since the copy.
- [ ] `wp-config.php`: `WP_POST_REVISIONS` is not `false`.
- [ ] Plugins other than ours: updated, or deactivated until checked.

## After the push

- [ ] `/` shows the block homepage, `/national-conversation/` and `/walkthrough/` answer 200, the Assets Pack and
      Press Release pages show their blocks, the footer links work.
- [ ] Plugins page: AI Awareness Day Core, the Benchmark and the Debate Network are active.
- [ ] Contact form, benchmark report email (needs a real sender domain) and the school dashboard lookup.
- [ ] Clear the plugin cache and Hostinger's cache. Delete `readme.html` from the site root (it names the WordPress version).
- [ ] Hostinger's PHP error log is clean. Keep the backup for two weeks.

Rollback is a restore of files and database together from the backup taken before the push.
