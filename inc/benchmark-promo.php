<?php
/**
 * Homepage benchmark promo helpers.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the AI Risk & Readiness Benchmark plugin is available for promo links.
 */
function aiad_benchmark_promo_is_available(): bool {
	return class_exists( 'AIRB_Defaults' );
}

/**
 * Canonical benchmark start URL, optionally pre-selecting a respondent role.
 *
 * @param string $role Optional role slug (teacher, student, parent, leader, support_staff).
 */
function aiad_get_benchmark_start_url( string $role = '' ): string {
	if ( ! aiad_benchmark_promo_is_available() ) {
		return home_url( '/timeline/student-parent-teacher-or-school-leader-audit-your-ai-usage-with-our-ai-risk-readiness-benchmark/#airb-benchmark' );
	}

	$url = AIRB_Defaults::benchmark_page_url();
	if ( '' !== $role ) {
		$url = add_query_arg( 'airb_role', sanitize_key( $role ), $url );
	}
	if ( false === strpos( $url, '#airb-benchmark' ) ) {
		$url .= '#airb-benchmark';
	}

	return $url;
}

/**
 * Role pills for the homepage benchmark promo.
 *
 * @return array<int, array{slug: string, label: string, url: string}>
 */
function aiad_benchmark_promo_roles(): array {
	if ( ! aiad_benchmark_promo_is_available() ) {
		return array(
			array(
				'slug'  => 'teacher',
				'label' => __( 'Teacher', 'ai-awareness-day' ),
				'url'   => aiad_get_benchmark_start_url( 'teacher' ),
			),
			array(
				'slug'  => 'student',
				'label' => __( 'Student', 'ai-awareness-day' ),
				'url'   => aiad_get_benchmark_start_url( 'student' ),
			),
			array(
				'slug'  => 'parent',
				'label' => __( 'Parent / Carer', 'ai-awareness-day' ),
				'url'   => aiad_get_benchmark_start_url( 'parent' ),
			),
			array(
				'slug'  => 'leader',
				'label' => __( 'School Leader', 'ai-awareness-day' ),
				'url'   => aiad_get_benchmark_start_url( 'leader' ),
			),
			array(
				'slug'  => 'support_staff',
				'label' => __( 'Support Staff', 'ai-awareness-day' ),
				'url'   => aiad_get_benchmark_start_url( 'support_staff' ),
			),
		);
	}

	$roles  = AIRB_Defaults::roles();
	$shorts = array(
		'support_staff' => __( 'Support Staff', 'ai-awareness-day' ),
	);

	$items = array();
	foreach ( $roles as $slug => $label ) {
		$items[] = array(
			'slug'  => $slug,
			'label' => $shorts[ $slug ] ?? $label,
			'url'   => aiad_get_benchmark_start_url( $slug ),
		);
	}

	return $items;
}
