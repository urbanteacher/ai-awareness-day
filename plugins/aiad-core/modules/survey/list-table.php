<?php
/**
 * National Survey: Admin: the responses list table's columns and sorting.
 *
 * Part of the survey module (modules/survey.php).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Admin: custom list table columns
// ---------------------------------------------------------------------------

function aiad_survey_response_columns( array $cols ): array {
	return array(
		'cb'            => $cols['cb'] ?? '',
		'title'         => __( 'Response', 'aiad-core' ),
		'school'        => __( 'School', 'aiad-core' ),
		'role'          => __( 'Role', 'aiad-core' ),
		'year_groups'   => __( 'Year groups', 'aiad-core' ),
		'participated'  => __( 'Participated?', 'aiad-core' ),
		'avg_rating'    => __( 'Avg rating', 'aiad-core' ),
		'date'          => __( 'Date', 'aiad-core' ),
	);
}
add_filter( 'manage_survey_response_posts_columns', 'aiad_survey_response_columns' );

function aiad_survey_response_column_content( string $col, int $post_id ): void {
	switch ( $col ) {
		case 'school':
			$name = (string) get_post_meta( $post_id, '_survey_school_name', true );
			$type = (string) get_post_meta( $post_id, '_survey_school_type', true );
			echo esc_html( $name ?: '—' );
			if ( $type ) {
				echo ' <span style="color:#888;font-size:11px;">(' . esc_html( $type ) . ')</span>';
			}
			break;
		case 'role':
			$map = array(
				'teacher_primary'   => 'Teacher (Primary)',
				'teacher_secondary' => 'Teacher (Secondary)',
				'computing_lead'    => 'Computing Lead',
				'headteacher'       => 'Headteacher / Leader',
				'slt_mat'           => 'SLT / Digital Lead',
				'alt_provision'     => 'Alt Provision',
			);
			$val = (string) get_post_meta( $post_id, '_survey_role', true );
			echo esc_html( $map[ $val ] ?? $val ?: '—' );
			break;
		case 'year_groups':
			$raw = (string) get_post_meta( $post_id, '_survey_year_groups', true );
			$arr = $raw ? json_decode( $raw, true ) : array();
			echo esc_html( is_array( $arr ) && $arr ? implode( ', ', array_map( 'strtoupper', $arr ) ) : '—' );
			break;
		case 'participated':
			$val = (string) get_post_meta( $post_id, '_survey_participated', true );
			if ( $val === 'yes' ) {
				$scale = (string) get_post_meta( $post_id, '_survey_participation_scale', true );
				$badge = '<span style="background:#dcfce7;color:#166534;padding:2px 7px;border-radius:99px;font-size:11px;font-weight:600;">Yes</span>';
				echo wp_kses_post( $badge );
				if ( $scale ) {
					echo ' <span style="color:#888;font-size:11px;">' . esc_html( $scale ) . '</span>';
				}
			} elseif ( $val === 'no' ) {
				echo '<span style="background:#fee2e2;color:#991b1b;padding:2px 7px;border-radius:99px;font-size:11px;font-weight:600;">No</span>';
			} else {
				echo '—';
			}
			break;
		case 'avg_rating':
			$fields = array(
				'_survey_rating_student_empowerment',
				'_survey_rating_critical_skepticism',
				'_survey_rating_inclusivity',
				'_survey_rating_plug_and_play',
				'_survey_rating_student_access',
				'_survey_rating_tech_delivery',
			);
			$vals = array_filter( array_map( function( $k ) use ( $post_id ) {
				$v = (int) get_post_meta( $post_id, $k, true );
				return $v > 0 ? $v : null;
			}, $fields ) );
			if ( $vals ) {
				$avg = round( array_sum( $vals ) / count( $vals ), 1 );
				$stars = str_repeat( '★', (int) round( $avg ) ) . str_repeat( '☆', 5 - (int) round( $avg ) );
				echo '<span title="' . esc_attr( $avg . '/5' ) . '" style="color:#f59e0b;">' . esc_html( $stars ) . '</span> <span style="font-size:11px;color:#888;">' . esc_html( $avg ) . '</span>';
			} else {
				echo '<span style="color:#888;">—</span>';
			}
			break;
	}
}
add_action( 'manage_survey_response_posts_custom_column', 'aiad_survey_response_column_content', 10, 2 );

function aiad_survey_response_sortable_columns( array $cols ): array {
	$cols['school']       = 'school';
	$cols['participated'] = 'participated';
	return $cols;
}
add_filter( 'manage_edit-survey_response_sortable_columns', 'aiad_survey_response_sortable_columns' );
