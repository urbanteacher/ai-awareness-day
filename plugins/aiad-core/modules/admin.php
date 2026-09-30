<?php
/**
 * Admin module: meta boxes, list table columns and filters, demo resource import/export, the featured image focal
 * point, the submissions CSV export, and the card image fetch.
 *
 * Moved from the theme's inc/ folder (meta-boxes.php, admin-columns.php, import-export.php, entry-figure.php,
 * submissions-csv-export.php, and part of ajax-handlers.php). The theme loads this file from its bundled copy of the
 * plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_ADMIN', __FILE__ );

// Same order and conditions the theme loaded them with.
if ( is_admin() ) {
	require_once __DIR__ . '/admin/meta-boxes.php';
	require_once __DIR__ . '/admin/admin-columns.php';
	require_once __DIR__ . '/admin/import-export.php';
}
// The focal point helpers are used on the front end too, and the card image fetch is an AJAX handler.
require_once __DIR__ . '/admin/entry-figure.php';
require_once __DIR__ . '/admin/card-image.php';
if ( is_admin() ) {
	require_once __DIR__ . '/admin/submissions-csv-export.php';
}
