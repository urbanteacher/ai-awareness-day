<?php
/**
 * Live Timeline: loader for the modular timeline includes.
 *
 * The timeline feature is an "aiad_timeline" CPT storing entries that are
 * written manually in the admin. (Automatic generation from resources,
 * partners, live sessions, and countdowns is disabled — see inc/timeline/entries.php.)
 *
 * Implementation is split across inc/timeline/ for maintainability:
 *   - cpt-meta.php       CPT, taxonomy & meta registration
 *   - admin-meta-box.php Admin meta box for manual entries
 *   - icons.php          Icon options & SVG renderer
 *   - entries.php        Entry creation helpers + (disabled) auto-generation hooks
 *   - query.php          Query helpers
 *   - ajax.php           AJAX filter & like handlers
 *   - single-helpers.php Single template helpers (single-timeline.php)
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/timeline/cpt-meta.php';
require_once __DIR__ . '/timeline/admin-meta-box.php';
require_once __DIR__ . '/timeline/icons.php';
require_once __DIR__ . '/timeline/entries.php';
require_once __DIR__ . '/timeline/query.php';
require_once __DIR__ . '/timeline/ajax.php';
require_once __DIR__ . '/timeline/single-helpers.php';
