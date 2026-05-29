# Timeline Feature — Integration Guide

> **Note:** Legacy copies of `section-timeline.php`, `timeline.php`, `timeline.js`, and `timeline.css` in this folder were removed. Use the production paths below.

## Production files

```
inc/timeline.php                              ← CPT, meta, queries, AJAX filter/like
inc/timeline-layouts.php                      ← Mobile swipe deck + desktop magazine renderers
template-parts/front-page/section-timeline.php  ← Front page #timeline section
assets/css/components/timeline.css            ← Section, filters, swipe, magazine styles
assets/js/timeline.js                         ← Swipe sync, filter AJAX, like/share
```

## Front-page feed behaviour

- **Mobile (&lt;768px):** horizontal swipe deck (`.timeline-swipe`)
- **Desktop (768px+):** magazine hero + sub-card grid (`.timeline-magazine`)
- **Filters:** AJAX replaces `.timeline-feed__body` via `aiad_ajax_timeline_filter`
- **Batch size:** `aiad_timeline_feed_per_page()` (default **5** = 1 hero + 4 subs via `aiad_timeline_magazine_sub_count()`)
- **No load more:** initial batch only

## Enqueue (see `inc/setup.php`)

- CSS: front page, singular timeline, **timeline archive**, live-session archive (filter pill reuse)
- JS: front page, **timeline archive**, singular timeline (`#timeline-feed`)

## Archive (`/timeline/`)

- Template: `archive-timeline.php`
- List renderer: `aiad_render_timeline_archive_feed()` (magazine row cards, all breakpoints)
- Pagination: `aiad_timeline_archive_per_page()` (default 12), optional `?timeline_icon=` filter
- AJAX filters: pass `archive=1` with `aiad_timeline_filter` (see `data-timeline-archive` on `#timeline-feed`)
- Homepage CTA: “View all updates →” in `section-timeline.php`

## Customizer / layout

- Section slug: `timeline` in `aiad_get_front_page_sections()` / `aiad_is_section_visible( 'timeline' )`
- Rendered via `get_template_part( 'template-parts/front-page/section', 'timeline' )` in `front-page.php`

## Manual entries only

Timeline entries are **created and managed manually** in the WordPress admin (Timeline → Add New). All automatic creation and syncing is disabled.

The following automations were turned off (hooks commented out in `inc/timeline.php`; helper functions retained but dormant):

- **Live sessions:** `save_post_live_session` sync and one-time backfill (`aiad_timeline_sync_live_session_entry()`, `aiad_timeline_backfill_live_session_entries()`)
- **Resources / featured resources:** publish announcements and backfill (`aiad_timeline_on_resource_publish()`, `aiad_timeline_backfill_resource_announcements()`)
- **Partners:** publish announcements (`aiad_timeline_on_partner_publish()`)
- **Countdown:** daily “weeks to go” cron (`aiad_timeline_maybe_create_countdown_entries()`); the scheduled event is cleared by `aiad_timeline_clear_countdown_cron()`
- **Status syncing:** trashing/unpublishing a related post no longer changes its timeline card (`aiad_timeline_on_related_post_unpublished()`)

Display helpers `aiad_timeline_event_date()` and `aiad_timeline_days_until_event()` remain active (used by hero, SEO, and the timeline section). To re-enable any automation, uncomment the relevant `add_action` line in `inc/timeline.php`.

## Removed auto-milestones

Sign-up threshold posts (10/25/50/100/250/500/1000 schools) and “All themes covered” auto entries are **disabled**. The **News** filter (`milestone` icon) is for manual timeline posts only.
