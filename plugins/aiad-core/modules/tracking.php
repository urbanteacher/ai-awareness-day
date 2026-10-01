<?php
/**
 * Tracking module: the REST routes that count resource downloads and views, engagement tracking (clicks, shares, views) and the
 * dashboard widgets that report on sign-ups, resources, tools, the survey and engagement.
 *
 * Moved from the theme's inc/ajax-handlers.php, inc/dashboard.php and inc/engagement-tracking.php. The theme loads
 * this file from its bundled copy of the plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_TRACKING', __FILE__ );

require_once __DIR__ . '/tracking/rest.php';
// Same order the theme loaded them in.
require_once __DIR__ . '/tracking/dashboard.php';
require_once __DIR__ . '/tracking/engagement-tracking.php';
