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

## Live schedule ↔ EVENT filter

Each published **`live_session`** syncs to a timeline card via `aiad_timeline_sync_live_session_entry()`:

- Icon `event` (shows under the **EVENT** filter pill)
- `_aiad_timeline_auto_type` = `live_session`, `_aiad_timeline_related_id` = session ID
- Body includes session time + format; CTA → registration URL or session page
- Partner logo copied as featured image when set on the session

Hooks: `save_post_live_session`. One-time backfill: `aiad_timeline_backfill_live_session_entries()` (option `aiad_timeline_live_sessions_synced_v1`).

## Removed auto-milestones

Sign-up threshold posts (10/25/50/100/250/500/1000 schools) and “All themes covered” auto entries are **disabled**. The **News** filter (`milestone` icon) is for manual timeline posts only.
