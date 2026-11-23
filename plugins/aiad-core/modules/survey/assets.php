<?php
/**
 * National Survey: The survey's stylesheet and script (this plugin's assets/), registered early and queued where the form prints.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Assets
// ---------------------------------------------------------------------------

function aiad_register_national_survey_assets(): void {
	$css_path = aiad_core_path( 'assets/css/components/national-survey.css' );
	$js_path  = aiad_core_path( 'assets/js/national-survey.js' );

	wp_register_style(
		'aiad-national-survey',
		aiad_core_url( 'assets/css/components/national-survey.css' ),
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-national-survey',
		aiad_core_url( 'assets/js/national-survey.js' ),
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_national_survey_assets', 5 );
// Block templates render the page before wp_enqueue_scripts; register first, so the script exists when the
// shortcode or block enqueues and localises it.
add_action( 'wp', 'aiad_register_national_survey_assets' );

function aiad_enqueue_national_survey_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-national-survey' );
	wp_enqueue_script( 'aiad-national-survey' );
}
