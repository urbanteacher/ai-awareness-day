<?php
/**
 * National Survey: the survey_response post type, the form (printed by the aiad/national-survey block, which replaced
 * the [aiad_national_survey] shortcode; shortcode-migration.php converts old content), its REST route, the admin meta
 * box, list table, CSV export and analytics page, the timeline entries and the survey page.
 *
 * Moved from the theme's inc/national-survey.php. The theme loads this file from its bundled copy of the plugin when
 * the plugin isn't active, so this is the only copy. The parts are in survey/, loaded in the order they ran in when
 * they were one file, so their hooks still register in the same order.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_SURVEY', __FILE__ );

/** Survey schema version — v1.0 = launch; v1.1 = reach/impact questions (June 2027). */
define( 'AIAD_SURVEY_VERSION', '1.1' );


require_once __DIR__ . '/survey/post-type.php';
require_once __DIR__ . '/survey/assets.php';
require_once __DIR__ . '/survey/form.php';
require_once __DIR__ . '/survey/rest.php';
require_once __DIR__ . '/survey/meta-box.php';
require_once __DIR__ . '/survey/admin-timeline.php';
require_once __DIR__ . '/survey/timeline-and-export.php';
require_once __DIR__ . '/survey/list-table.php';
require_once __DIR__ . '/survey/analytics.php';
require_once __DIR__ . '/survey/page.php';
