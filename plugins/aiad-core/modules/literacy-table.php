<?php
/**
 * The AI Literacy Periodic Table: its data (data/literacy-table/), the aiad/literacy-table and aiad/literacy-week
 * blocks, the elements' pictures, script and stylesheet, and Tools → Periodic Table in wp-admin.
 *
 * Moved from the theme's inc/literacy-table.php and inc/literacy-table-admin.php, so the table and its content survive
 * a theme change. The page's presentation (title, body class, homepage promo) stays in the theme's
 * inc/literacy-table-page.php. The suggestion form's route is its own module, literacy-term.php.
 * The theme loads this file from its bundled copy of the plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_LITERACY_TABLE', __FILE__ );

require_once __DIR__ . '/literacy-table/table.php';
require_once __DIR__ . '/literacy-table/admin.php';
