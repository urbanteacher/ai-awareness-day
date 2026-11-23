<?php
/**
 * National Survey: The "National Moment" timeline entry, and the admin CSV export of responses.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// "National Moment" timeline entry
// ---------------------------------------------------------------------------

function aiad_national_moment_timeline_entry_exists(): bool {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_moment_2026',
			),
		),
	) );
	return ! empty( $existing );
}

function aiad_national_moment_get_timeline_entry_id(): int {
	$existing = get_posts( array(
		'post_type'      => 'timeline',
		'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'   => '_aiad_timeline_auto_type',
				'value' => 'national_moment_2026',
			),
		),
	) );
	return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Seed the "national moment" timeline entry once on init if it doesn't exist yet.
 */
function aiad_national_moment_seed_timeline_entry(): void {
	if ( get_option( 'aiad_national_moment_2026_seeded' ) ) {
		return;
	}
	if ( aiad_national_moment_timeline_entry_exists() ) {
		update_option( 'aiad_national_moment_2026_seeded', true );
		return;
	}

	$content = '<p>Looking back at AI Awareness Day 2026 as we build towards the 2027 campaign.</p>'
		. '<p>AI Awareness Day 2026 became a genuine national moment — and it exceeded every expectation we had. Multi-academy trusts, schools, charities, EdTech companies, universities, and grassroots organisations across the UK came together to bring artificial intelligence education into classrooms, assemblies, and homes on the same day.</p>'
		. '<h3>The numbers</h3>'
		. '<ul>'
		. '<li><strong>750,000+ students</strong> engaged with AI Awareness Day activities — with a path to <strong>1 million</strong> in the coming months</li>'
		. '<li><strong>1 million+ reach</strong> through MATs, charities, and educational providers</li>'
		. '<li><strong>Hundreds of thousands</strong> of social media engagements across LinkedIn, X, Facebook, and Instagram</li>'
		. '</ul>'
		. '<p>This was not a single school, a single organisation, or a single moment. It was a movement.</p>'
		. '<h3>What comes next</h3>'
		. '<p>AI Awareness Day was the launchpad, not the finish line. Browse the full resource library, watch YouTube Shorts featuring AI education leaders, and attend partner events as we look ahead to 2027. Our Mini Masterclass series is growing — submit your tips and insights to be featured.</p>'
		. '<p><em>Know it. Question it. Use it wisely.</em></p>';

	$post_id = aiad_create_timeline_entry( array(
		'title'      => 'Highlights from 2026',
		'content'    => $content,
		'auto_type'  => 'national_moment_2026',
		'icon'       => 'milestone',
		'related_id' => 0,
		'link_url'   => 'https://aiawarenessday.co.uk/timeline/the-day-after-and-what-a-day-it-was-%f0%9f%8e%89/',
		'link_label' => 'Read the full story →',
		'post_name'  => 'the-day-after-and-what-a-day-it-was',
	) );

	if ( $post_id ) {
		update_post_meta( (int) $post_id, '_aiad_timeline_pinned', true );
		update_option( 'aiad_national_moment_2026_seeded', true );
	}
}
add_action( 'init', 'aiad_national_moment_seed_timeline_entry', 40 );

function aiad_export_survey_csv(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Permission denied.', 'aiad-core' ) );
	}
	check_admin_referer( 'aiad_export_survey_csv' );

	$posts = get_posts( array(
		'post_type'      => 'survey_response',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'ASC',
	) );

	$columns = array(
		'ID', 'Date', 'Role', 'AI in curriculum', 'AI display board', 'AI policy', 'Participated', 'Participation scale',
		// Part 1 — Hopes
		'Primary hope (Q1)',
		'Rating: student empowerment (Q2)', 'Rating: critical scepticism (Q2)', 'Rating: inclusivity (Q2)',
		'Lasting classroom effect (Q3)',
		// Part 2 — Resource friction
		'Prep time (Q4)', 'Rating: plug & play (Q5)', 'Rating: student access (Q5)', 'Rating: tech delivery (Q5)',
		'Materials adequacy (Q6)', 'Useful formats (Q7)', 'Best content format (Q8)',
		// Part 3 — Learning efficacy
		'Staff room impact (Q9)',
		// Part 4 — Strategic roadmap
		'Bottleneck (Q10)', 'Support modules 2027 (Q11)', 'Open feedback (Q12)',
		// Non-participant track
		'Non-part: reason', 'Non-part: staff room attitude',
		// Reach & impact (v1.1)
		'Survey version', 'How heard about AAD', 'Confidence before', 'Confidence after', 'Intended next action',
		'Heard before today', 'Top AI concern', 'Support needed 2027', 'Participate next year', 'Recommend',
		// Contact / school details
		'School name', 'School type', 'Year groups', 'Contact email', 'Preferred update channels', 'Quoting permission',
	);

	$meta_keys = array(
		'_survey_role', '_survey_curriculum_embedded', '_survey_display_board', '_survey_ai_policy', '_survey_participated', '_survey_participation_scale',
		'_survey_primary_hope',
		'_survey_rating_student_empowerment', '_survey_rating_critical_skepticism', '_survey_rating_inclusivity',
		'_survey_lasting_effect',
		'_survey_prep_time',
		'_survey_rating_plug_and_play', '_survey_rating_student_access', '_survey_rating_tech_delivery',
		'_survey_materials_quality', '_survey_useful_formats', '_survey_best_format',
		'_survey_staffroom_impact', '_survey_bottleneck',
		'_survey_support_modules', '_survey_open_feedback',
		'_survey_non_part_reason', '_survey_staffroom_attitude',
		'_survey_version', '_survey_hear_about', '_survey_confidence_before', '_survey_confidence_after', '_survey_intended_action',
		'_survey_heard_before', '_survey_top_concern', '_survey_support_needed', '_survey_participate_next_year', '_survey_recommend',
		'_survey_school_name', '_survey_school_type', '_survey_year_groups', '_survey_contact_email', '_survey_comms_preference', '_survey_permission_quote',
	);

	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="aiad-survey-responses-' . gmdate( 'Y-m-d' ) . '.csv"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM for Excel
	fputcsv( $out, $columns );

	foreach ( $posts as $post ) {
		$row = array( $post->ID, $post->post_date );
		foreach ( $meta_keys as $key ) {
			$value = get_post_meta( $post->ID, $key, true );
			if ( str_starts_with( (string) $value, '[' ) ) {
				$decoded = json_decode( (string) $value, true );
				$value   = is_array( $decoded ) ? implode( '; ', $decoded ) : $value;
			}
			$row[] = (string) $value;
		}
		fputcsv( $out, $row );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_post_aiad_export_survey_csv', 'aiad_export_survey_csv' );
