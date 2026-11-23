<?php
/**
 * National Survey: POST aiad/v1/survey: validates and saves a response.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// REST route
// ---------------------------------------------------------------------------

/**
 * POST aiad/v1/survey: a response to the national survey.
 *
 * It was an admin-ajax handler behind a nonce printed into the page, which expires on a cached page. What protects it is
 * the honeypot, the limit of two responses per visitor per hour and the validation, all kept here. The script
 * (assets/js/national-survey.js) finds the route through the REST discovery link.
 *
 * @param WP_REST_Request $request The form's fields.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_survey_submit( WP_REST_Request $request ) {
	// The form's fields, already unslashed by the REST server.
	$post = $request->get_body_params();

	// Honeypot
	$honeypot = isset( $post['aiad_website'] ) ? sanitize_text_field( $post['aiad_website'] ) : '';
	if ( $honeypot !== '' ) {
		return new WP_Error( 'aiad_survey', __( 'Invalid submission.', 'aiad-core' ), array( 'status' => 400 ) );
	}

	// Rate limit: 2 per fingerprint per hour
	$fingerprint = md5(
		( isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '' )
		. '|'
		. ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' )
	);
	$rate_key = 'aiad_survey_rate_' . $fingerprint;
	$count    = (int) get_transient( $rate_key );
	if ( $count >= 2 ) {
		return new WP_Error( 'aiad_survey', __( 'You have already submitted a response recently. Thank you!', 'aiad-core' ), array( 'status' => 429 ) );
	}

	// Sanitise scalar fields
	$role                   = sanitize_text_field( $post['role'] ?? '' );
	$display_board          = sanitize_text_field( $post['display_board'] ?? '' );
	$ai_policy              = sanitize_text_field( $post['ai_policy'] ?? '' );
	$curriculum_embedded    = sanitize_text_field( $post['curriculum_embedded'] ?? '' );
	$participated           = sanitize_text_field( $post['participated'] ?? '' );
	$participation_scale    = sanitize_text_field( $post['participation_scale'] ?? '' );
	$primary_hope           = sanitize_text_field( $post['primary_hope'] ?? '' );
	$lasting_effect         = sanitize_text_field( $post['lasting_effect'] ?? '' );
	$prep_time              = sanitize_text_field( $post['prep_time'] ?? '' );
	$materials_quality      = sanitize_text_field( $post['materials_quality'] ?? '' );
	$best_format            = sanitize_text_field( $post['best_format'] ?? '' );
	$bottleneck             = sanitize_text_field( $post['bottleneck'] ?? '' );
	$open_feedback          = sanitize_textarea_field( $post['open_feedback'] ?? '' );
	$non_part_reason        = sanitize_text_field( $post['non_part_reason'] ?? '' );
	$staffroom_attitude     = sanitize_text_field( $post['staffroom_attitude'] ?? '' );
	$school_name            = sanitize_text_field( $post['school_name'] ?? '' );
	$school_type            = sanitize_text_field( $post['school_type'] ?? '' );
	$contact_email          = sanitize_email( $post['contact_email'] ?? '' );
	$perm_quote             = isset( $post['permission_quote'] ) ? '1' : '0';
	$survey_version         = sanitize_text_field( $post['survey_version'] ?? AIAD_SURVEY_VERSION );
	$hear_about             = sanitize_text_field( $post['hear_about'] ?? '' );
	$confidence_before      = sanitize_text_field( $post['confidence_before'] ?? '' );
	$confidence_after       = sanitize_text_field( $post['confidence_after'] ?? '' );
	$intended_action        = sanitize_text_field( $post['intended_action'] ?? '' );
	$heard_before           = sanitize_text_field( $post['heard_before'] ?? '' );
	$top_concern            = sanitize_text_field( $post['top_concern'] ?? '' );
	$participate_next_year  = sanitize_text_field( $post['participate_next_year'] ?? '' );
	$recommend              = sanitize_text_field( $post['recommend'] ?? '' );

	$allowed_year_groups = array( 'eyfs', 'ks1', 'ks2', 'ks3', 'ks4', 'ks5', 'staff' );
	$raw_year_groups = isset( $post['year_groups'] ) && is_array( $post['year_groups'] )
		? array_map( 'sanitize_text_field', $post['year_groups'] )
		: array();
	$year_groups = array_values( array_intersect( $raw_year_groups, $allowed_year_groups ) );

	$allowed_school_types = array( 'primary', 'secondary', 'all_through', 'special', 'fe_college', 'higher_education', 'mat_trust' );
	if ( ! in_array( $school_type, $allowed_school_types, true ) ) {
		$school_type = '';
	}

	// Validate required field
	if ( empty( $role ) ) {
		return new WP_Error( 'aiad_survey', __( 'Please tell us your role before submitting.', 'aiad-core' ), array( 'status' => 400 ) );
	}

	// School maturity questions (everyone) — validate against allowed values.
	$allowed_maturity = array( 'yes', 'in_development', 'no' );
	if ( ! in_array( $display_board, $allowed_maturity, true ) ) {
		$display_board = '';
	}
	if ( ! in_array( $ai_policy, $allowed_maturity, true ) ) {
		$ai_policy = '';
	}
	if ( ! in_array( $curriculum_embedded, $allowed_maturity, true ) ) {
		$curriculum_embedded = '';
	}

	// Sanitise array fields — participant path
	$allowed_staffroom = array( 'admin_workload', 'data_privacy', 'parents_engaged', 'no_change' );
	$raw_staffroom = isset( $post['staffroom_impact'] ) && is_array( $post['staffroom_impact'] )
		? array_map( 'sanitize_text_field', $post['staffroom_impact'] )
		: array();
	$staffroom_impact = array_values( array_intersect( $raw_staffroom, $allowed_staffroom ) );

	$allowed_support = array( 'display_kits', 'cross_curricular', 'cpd_pathways', 'pta_packs' );
	$raw_support = isset( $post['support_modules'] ) && is_array( $post['support_modules'] )
		? array_map( 'sanitize_text_field', $post['support_modules'] )
		: array();
	$support_modules = array_values( array_intersect( $raw_support, $allowed_support ) );

	// Materials feedback (participants) — validate against allowed values.
	$allowed_materials_quality = array( 'ideal', 'adequate', 'basic', 'inadequate' );
	if ( ! in_array( $materials_quality, $allowed_materials_quality, true ) ) {
		$materials_quality = '';
	}
	$allowed_best_format = array( 'video', 'slides', 'teacher_instructions', 'mix' );
	if ( ! in_array( $best_format, $allowed_best_format, true ) ) {
		$best_format = '';
	}
	$allowed_useful_formats = array( 'starter_5', 'tutor_15', 'assembly_20', 'none' );
	$raw_useful = isset( $post['useful_formats'] ) && is_array( $post['useful_formats'] )
		? array_map( 'sanitize_text_field', $post['useful_formats'] )
		: array();
	$useful_formats = array_values( array_intersect( $raw_useful, $allowed_useful_formats ) );

	// Non-participant path
	// Communication preferences (everyone)
	$allowed_comms = array( 'website_timeline', 'linkedin', 'newsletter' );
	$raw_comms = isset( $post['comms_preference'] ) && is_array( $post['comms_preference'] )
		? array_map( 'sanitize_text_field', $post['comms_preference'] )
		: array();
	$comms_preference = array_values( array_intersect( $raw_comms, $allowed_comms ) );

	$allowed_hear_about = array( 'social', 'email', 'colleague', 'mat_trust', 'dfe_org', 'search', 'other' );
	if ( ! in_array( $hear_about, $allowed_hear_about, true ) ) {
		$hear_about = '';
	}

	$allowed_confidence = array( 'very', 'fairly', 'not_very', 'not' );
	if ( ! in_array( $confidence_before, $allowed_confidence, true ) ) {
		$confidence_before = '';
	}
	if ( ! in_array( $confidence_after, $allowed_confidence, true ) ) {
		$confidence_after = '';
	}

	$allowed_intended_action = array(
		'learn_more', 'review_policy', 'discuss_students', 'discuss_child',
		'safeguarding', 'assessment', 'explore_tools', 'no_action',
	);
	if ( ! in_array( $intended_action, $allowed_intended_action, true ) ) {
		$intended_action = '';
	}

	$allowed_heard_before = array( 'yes', 'no', 'not_sure' );
	if ( ! in_array( $heard_before, $allowed_heard_before, true ) ) {
		$heard_before = '';
	}

	$allowed_top_concern = array(
		'misinformation', 'privacy', 'safeguarding', 'deepfakes', 'integrity', 'bias', 'over_reliance',
	);
	if ( ! in_array( $top_concern, $allowed_top_concern, true ) ) {
		$top_concern = '';
	}

	$allowed_likelihood = array( 'definitely', 'probably', 'maybe', 'unlikely' );
	if ( ! in_array( $participate_next_year, $allowed_likelihood, true ) ) {
		$participate_next_year = '';
	}
	if ( ! in_array( $recommend, $allowed_likelihood, true ) ) {
		$recommend = '';
	}

	$allowed_support_needed = array(
		'training', 'policy', 'parent_resources', 'student_resources', 'safeguarding', 'case_studies', 'webinars',
	);
	$raw_support_needed = isset( $post['support_needed'] ) && is_array( $post['support_needed'] )
		? array_map( 'sanitize_text_field', $post['support_needed'] )
		: array();
	$support_needed = array_values( array_intersect( $raw_support_needed, $allowed_support_needed ) );

	// Sanitise Likert ratings (1–5)
	$likert_fields = array(
		'rating_student_empowerment',
		'rating_critical_skepticism',
		'rating_inclusivity',
		'rating_plug_and_play',
		'rating_student_access',
		'rating_tech_delivery',
	);
	$ratings = array();
	foreach ( $likert_fields as $field ) {
		$val = (int) ( $post[ $field ] ?? 0 );
		$ratings[ $field ] = ( $val >= 1 && $val <= 5 ) ? $val : 0;
	}

	// Build a title for the admin list
	$title = sprintf(
		'%s — %s — %s — %s',
		$school_name ?: 'Anonymous',
		$role ?: 'unknown',
		$participated === 'yes' ? 'participant' : 'non-participant',
		gmdate( 'Y-m-d H:i' )
	);

	$post_id = wp_insert_post( array(
		'post_type'   => 'survey_response',
		'post_status' => 'publish',
		'post_title'  => $title,
	) );

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		return new WP_Error( 'aiad_survey', __( 'Sorry, your response could not be saved. Please try again.', 'aiad-core' ), array( 'status' => 400 ) );
	}

	$meta = array(
		// Part 1 — School profile
		'_survey_role'                  => $role,
		'_survey_curriculum_embedded'   => $curriculum_embedded,
		'_survey_display_board'         => $display_board,
		'_survey_ai_policy'             => $ai_policy,
		// Gate
		'_survey_participated'          => $participated,
		'_survey_participation_scale'   => $participation_scale,
		// Part 1 — Hopes & attitudinal shifts (participants)
		'_survey_primary_hope'                  => $primary_hope,
		'_survey_rating_student_empowerment'    => $ratings['rating_student_empowerment'],
		'_survey_rating_critical_skepticism'    => $ratings['rating_critical_skepticism'],
		'_survey_rating_inclusivity'            => $ratings['rating_inclusivity'],
		'_survey_lasting_effect'                => $lasting_effect,
		// Part 2 — Resource friction (participants)
		'_survey_prep_time'                     => $prep_time,
		'_survey_rating_plug_and_play'          => $ratings['rating_plug_and_play'],
		'_survey_rating_student_access'         => $ratings['rating_student_access'],
		'_survey_rating_tech_delivery'          => $ratings['rating_tech_delivery'],
		'_survey_materials_quality'             => $materials_quality,
		'_survey_useful_formats'                => wp_json_encode( $useful_formats ),
		'_survey_best_format'                   => $best_format,
		// Part 3 — Learning efficacy (participants)
		'_survey_staffroom_impact'              => wp_json_encode( $staffroom_impact ),
		// Part 4 — Strategic roadmap (participants)
		'_survey_bottleneck'            => $bottleneck,
		'_survey_support_modules'       => wp_json_encode( $support_modules ),
		'_survey_open_feedback'         => $open_feedback,
		// Part 1B — Non-participant track
		'_survey_non_part_reason'       => $non_part_reason,
		'_survey_staffroom_attitude'    => $staffroom_attitude,
		// Contact / school details
		'_survey_school_name'           => $school_name,
		'_survey_school_type'           => $school_type,
		'_survey_year_groups'           => wp_json_encode( $year_groups ),
		'_survey_contact_email'         => $contact_email,
		'_survey_comms_preference'      => wp_json_encode( $comms_preference ),
		'_survey_permission_quote'      => $perm_quote,
		'_survey_year'                  => '2026',
		'_survey_version'               => $survey_version ?: '1.1',
		// Reach & impact (v1.1)
		'_survey_hear_about'            => $hear_about,
		'_survey_confidence_before'     => $confidence_before,
		'_survey_confidence_after'      => $confidence_after,
		'_survey_intended_action'       => $intended_action,
		'_survey_heard_before'          => $heard_before,
		'_survey_top_concern'           => $top_concern,
		'_survey_support_needed'        => wp_json_encode( $support_needed ),
		'_survey_participate_next_year' => $participate_next_year,
		'_survey_recommend'             => $recommend,
	);

	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	// Bump rate limit transient
	set_transient( $rate_key, $count + 1, HOUR_IN_SECONDS );

	return new WP_REST_Response( array( 'message' => __( 'Thank you for your response!', 'aiad-core' ) ) );
}

/**
 * Register the route.
 */
function aiad_register_survey_rest_route(): void {
	register_rest_route(
		'aiad/v1',
		'/survey',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'aiad_rest_survey_submit',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'aiad_register_survey_rest_route' );
