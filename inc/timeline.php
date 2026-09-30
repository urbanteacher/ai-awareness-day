<?php
/**
 * Live Timeline: loader for the modular timeline includes.
 *
 * The timeline feature is an "aiad_timeline" CPT storing entries that are
 * written manually in the admin. (Automatic generation from resources,
 * partners, live sessions, and countdowns is disabled — see the plugin's timeline/entries.php.)
 *
 * The timeline's data and admin side lives in the aiad-core plugin (plugins/aiad-core/modules/timeline/): CPT,
 * taxonomy and meta, admin meta box, icon options, entry helpers, topics, query helpers, AJAX handlers and the
 * benchmark audience. This theme keeps the presentation:
 *   - timeline/icons.php          SVG icon and cover renderers, YouTube facade
 *   - timeline-layouts.php        Feed and archive layouts (also used by the AJAX filter)
 *   - timeline/single-helpers.php Single template helpers (single-timeline.php)
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

aiad_require_core_module( 'timeline' );

require_once __DIR__ . '/timeline/icons.php';
require_once __DIR__ . '/timeline-layouts.php';
require_once __DIR__ . '/timeline/single-helpers.php';
