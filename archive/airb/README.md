# AIRB archive

Legacy and superseded plugin files kept here instead of deleting them during the Phase 2 migration.

Location: repository root `archive/airb/`. Nothing in production loads from here.

**Do not enqueue or require from this folder in production** unless you are restoring old behaviour for reference or one-off migration scripts.

## Contents

### `includes/data/*-copy-tiers.php` (moved back, 1.59.1)

The PHP tier registries (teacher, leader, student, parent, support, public) were kept here from 1.48.2, but they are still live data: `AIRB_Defaults::role_tier_data()` reads `strength_tiers`, `domain_descriptions`, section labels and rollout copy from them, with `includes/data/copy-tiers-{role}.json` replacing only `copy_tiers` / `focus_tiers`. The plugin copy in `wp-content/plugins` cannot reach this folder, so results lost that wording. They are back in the plugin's `includes/data/`.

### `js/airb-front-pre-1.48.0-snapshot.js`

Full `airb-front.js` immediately before Step 5 JS cleanup (plugin v1.47.0).

### `js/airb-front-1.48.0-removed-builders.diff`

Git diff of HTML builder functions removed from `airb-front.js` in v1.48.0. Superseded by `AIRB.Roles` + `AIRB.Results`.

Removed symbols:

- `publicStrengthsSectionHtml`
- `teacherStrengthsSectionHtml`, `teacherFocusAreasHtml`
- `studentStrengthsSectionHtml`, `studentFocusAreasHtml`
- `parentFocusTopicsHtml`
- `supportStrengthsSectionHtml`, `supportFocusAreasHtml`
- `leaderFocusAreasHtml`

### `bin/export-copy-tiers-json.php`

One-time CLI helper to generate JSON from the archived PHP tier files.

```bash
wp eval-file wp-content/plugins/ai-risk-readiness-benchmark/../archive/airb/bin/export-copy-tiers-json.php
```
