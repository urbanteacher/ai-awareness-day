<?php
/**
 * National Survey: Admin: the meta box that shows a response's answers.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Admin: meta box display
// ---------------------------------------------------------------------------

function aiad_survey_add_meta_box(): void {
	add_meta_box(
		'aiad-survey-response',
		__( 'Survey Response', 'aiad-core' ),
		'aiad_survey_meta_box_render',
		'survey_response',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aiad_survey_add_meta_box' );

function aiad_survey_meta_box_render( WP_Post $post ): void {
	$fields = array(
		'_survey_role'                  => __( 'Role', 'aiad-core' ),
		'_survey_curriculum_embedded'   => __( 'AI activities in curriculum', 'aiad-core' ),
		'_survey_display_board'         => __( 'AI Awareness display board', 'aiad-core' ),
		'_survey_ai_policy'             => __( 'AI Awareness / AI-use policy', 'aiad-core' ),
		'_survey_participated'          => __( 'Participated on 4th June', 'aiad-core' ),
		'_survey_participation_scale'           => __( 'Participation scale', 'aiad-core' ),
		'_survey_primary_hope'                  => __( 'Primary hope (Q1)', 'aiad-core' ),
		'_survey_rating_student_empowerment'    => __( 'Rating: student empowerment (Q2)', 'aiad-core' ),
		'_survey_rating_critical_skepticism'    => __( 'Rating: critical scepticism (Q2)', 'aiad-core' ),
		'_survey_rating_inclusivity'            => __( 'Rating: inclusivity & access (Q2)', 'aiad-core' ),
		'_survey_lasting_effect'                => __( 'Lasting classroom effect (Q3)', 'aiad-core' ),
		'_survey_prep_time'                     => __( 'Prep time (Q4)', 'aiad-core' ),
		'_survey_rating_plug_and_play'          => __( 'Rating: plug & play usability (Q5)', 'aiad-core' ),
		'_survey_rating_student_access'         => __( 'Rating: student accessibility (Q5)', 'aiad-core' ),
		'_survey_rating_tech_delivery'          => __( 'Rating: technical delivery (Q5)', 'aiad-core' ),
		'_survey_materials_quality'             => __( 'Materials adequacy (Q6)', 'aiad-core' ),
		'_survey_useful_formats'                => __( 'Useful session formats (Q7)', 'aiad-core' ),
		'_survey_best_format'                   => __( 'Best content format (Q8)', 'aiad-core' ),
		'_survey_staffroom_impact'              => __( 'Staff room / parental impact (Q9)', 'aiad-core' ),
		'_survey_bottleneck'            => __( 'Biggest bottleneck (Q10)', 'aiad-core' ),
		'_survey_support_modules'       => __( 'Support modules for 2027 (Q11)', 'aiad-core' ),
		'_survey_open_feedback'         => __( 'Open feedback (Q12)', 'aiad-core' ),
		'_survey_non_part_reason'       => __( 'Non-participant: primary reason', 'aiad-core' ),
		'_survey_staffroom_attitude'    => __( 'Non-participant: staff room attitude', 'aiad-core' ),
		'_survey_version'               => __( 'Survey version', 'aiad-core' ),
		'_survey_hear_about'            => __( 'How heard about AAD', 'aiad-core' ),
		'_survey_confidence_before'     => __( 'Confidence before (participant)', 'aiad-core' ),
		'_survey_confidence_after'      => __( 'Confidence after (participant)', 'aiad-core' ),
		'_survey_intended_action'       => __( 'Intended next action (participant)', 'aiad-core' ),
		'_survey_heard_before'          => __( 'Heard before today (non-participant)', 'aiad-core' ),
		'_survey_top_concern'           => __( 'Top AI concern (non-participant)', 'aiad-core' ),
		'_survey_support_needed'        => __( 'Support needed for 2027 (non-participant)', 'aiad-core' ),
		'_survey_participate_next_year' => __( 'Participate next year (non-participant)', 'aiad-core' ),
		'_survey_recommend'             => __( 'Recommend to colleague', 'aiad-core' ),
		'_survey_school_name'           => __( 'School name', 'aiad-core' ),
		'_survey_school_type'           => __( 'School type', 'aiad-core' ),
		'_survey_year_groups'           => __( 'Year groups engaged', 'aiad-core' ),
		'_survey_contact_email'         => __( 'Contact email', 'aiad-core' ),
		'_survey_comms_preference'      => __( 'Preferred update channels', 'aiad-core' ),
		'_survey_permission_quote'      => __( 'Quoting permission', 'aiad-core' ),
	);

	echo '<table class="form-table"><tbody>';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		if ( $value === '' || $value === false ) {
			continue;
		}
		// Pretty-print JSON arrays
		if ( str_starts_with( (string) $value, '[' ) ) {
			$decoded = json_decode( (string) $value, true );
			$value   = is_array( $decoded ) ? implode( ', ', $decoded ) : $value;
		}
		echo '<tr><th scope="row">' . esc_html( $label ) . '</th><td>' . nl2br( esc_html( (string) $value ) ) . '</td></tr>';
	}
	echo '</tbody></table>';
}
