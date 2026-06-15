# AIRB archive

Legacy and superseded files kept here instead of deleting them during the Phase 2 migration.

**Do not enqueue or require from this folder in production** unless you are restoring old behaviour for reference or one-off migration scripts.

## Contents

### `includes/data/*-copy-tiers.php`

Original PHP tier registries (teacher, leader, student, parent, support, public).

- **Superseded by:** `includes/data/copy-tiers-{role}.json` for `copy_tiers` / `focus_tiers`
- **Still loaded for:** `strength_tiers`, `domain_descriptions`, section labels, rollout copy, and other flat keys via `AIRB_Defaults::role_tier_data()` (paths updated to read from here first)

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
wp eval-file wp-content/plugins/ai-risk-readiness-benchmark/archive/bin/export-copy-tiers-json.php
```
