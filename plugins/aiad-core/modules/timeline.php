<?php
/**
 * Timeline module: the timeline post type, taxonomy and meta, the admin meta box, icon options, entry helpers, topics,
 * query helpers, the REST routes for the filter and the like, and the benchmark audience settings.
 *
 * Moved from the theme's inc/timeline/ folder. The theme's inc/timeline.php loads this module (from its bundled copy
 * of the plugin when the plugin isn't active), then the timeline's presentation files: SVG icons, layouts and the
 * single template helpers. The feed route still renders with the theme's layout functions.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_TIMELINE', __FILE__ );

// Same order the theme loaded them in.
require_once __DIR__ . '/timeline/cpt-meta.php';
require_once __DIR__ . '/timeline/admin-meta-box.php';
require_once __DIR__ . '/timeline/icon-options.php';
require_once __DIR__ . '/timeline/entries.php';
require_once __DIR__ . '/timeline/topics.php';
require_once __DIR__ . '/timeline/query.php';
require_once __DIR__ . '/timeline/rest.php';
require_once __DIR__ . '/timeline/benchmark-audience.php';
