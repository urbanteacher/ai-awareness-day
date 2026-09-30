<?php
/**
 * Tracking module: resource download and view counters.
 *
 * Moved from the theme's inc/ajax-handlers.php. The theme loads this file from its bundled copy of the plugin when
 * the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_TRACKING', __FILE__ );

require_once __DIR__ . '/tracking/resource-tracking.php';
