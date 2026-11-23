<?php
/**
 * National Survey: Creates the survey page if it doesn't exist.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Auto-create the survey page if it doesn't exist
// ---------------------------------------------------------------------------

function aiad_maybe_create_survey_page(): void {
	if ( get_option( 'aiad_survey_page_created' ) ) {
		return;
	}

	// Check if a page with this slug already exists
	$existing = get_page_by_path( 'national-survey-2026' );
	if ( $existing ) {
		update_option( 'aiad_survey_page_created', $existing->ID );
		return;
	}

	$page_id = wp_insert_post( array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => __( 'National Survey 2026 — AI Awareness Day', 'aiad-core' ),
		'post_name'    => 'national-survey-2026',
		'post_content' => '<!-- wp:shortcode -->[aiad_national_survey]<!-- /wp:shortcode -->',
	) );

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'aiad_survey_page_created', $page_id );
	}
}
add_action( 'init', 'aiad_maybe_create_survey_page', 50 );
