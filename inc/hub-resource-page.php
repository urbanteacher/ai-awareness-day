<?php
/**
 * Benchmark hub resource pages — timeline-style reading layout.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is an AIRB intervention hub page.
 */
function aiad_is_hub_resource_page( ?WP_Post $post = null ): bool {
	if ( ! class_exists( 'AIRB_Hub_Content' ) ) {
		return false;
	}

	if ( null === $post ) {
		if ( ! is_page() ) {
			return false;
		}
		$post = get_queried_object();
	}

	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return false;
	}

	return in_array( $post->post_name, AIRB_Hub_Content::intervention_slugs(), true );
}

/**
 * Audience badge label for a hub resource page.
 */
function aiad_hub_resource_badge_label( ?WP_Post $post = null ): string {
	if ( null === $post ) {
		$post = get_queried_object();
	}

	$audience = 'all';
	if (
		$post instanceof WP_Post
		&& class_exists( 'AIRB_Defaults' )
		&& method_exists( 'AIRB_Defaults', 'hub_audience_for_slug' )
	) {
		$audience = AIRB_Defaults::hub_audience_for_slug( $post->post_name );
	}

	if ( class_exists( 'AIRB_Defaults' ) && method_exists( 'AIRB_Defaults', 'hub_audience_badge_label' ) ) {
		return AIRB_Defaults::hub_audience_badge_label( $audience );
	}

	$labels = array(
		'teacher' => __( 'Teacher resource', 'ai-awareness-day' ),
		'student' => __( 'Student resource', 'ai-awareness-day' ),
		'parent'  => __( 'Parent resource', 'ai-awareness-day' ),
		'leader'  => __( 'Leadership resource', 'ai-awareness-day' ),
		'all'     => __( 'School resource', 'ai-awareness-day' ),
	);

	return $labels[ $audience ] ?? $labels['all'];
}

/**
 * Back link target for hub resource pages.
 */
function aiad_hub_resource_back_url(): string {
	if ( class_exists( 'AIRB_Defaults' ) ) {
		return AIRB_Defaults::benchmark_page_url();
	}

	return home_url( '/' );
}

/**
 * Use the hub resource template for AIRB intervention pages.
 *
 * @param string $template Path to template.
 */
function aiad_hub_resource_template( string $template ): string {
	if ( ! aiad_is_hub_resource_page() ) {
		return $template;
	}

	$custom = get_stylesheet_directory() . '/page-hub-resource.php';
	if ( is_readable( $custom ) ) {
		return $custom;
	}

	return $template;
}
add_filter( 'template_include', 'aiad_hub_resource_template', 20 );

/**
 * Timeline-style CSS for hub resource pages.
 */
function aiad_hub_resource_assets(): void {
	if ( is_admin() || ! aiad_is_hub_resource_page() ) {
		return;
	}

	$single_timeline_css = AIAD_DIR . '/assets/css/pages/single-timeline.css';
	if ( is_readable( $single_timeline_css ) ) {
		wp_enqueue_style(
			'aiad-single-timeline',
			AIAD_URI . '/assets/css/pages/single-timeline.css',
			array( 'aiad-style' ),
			AIAD_VERSION . '.' . (string) filemtime( $single_timeline_css )
		);
	}

	$hub_css = AIAD_DIR . '/assets/css/pages/single-hub-resource.css';
	if ( is_readable( $hub_css ) ) {
		wp_enqueue_style(
			'aiad-single-hub-resource',
			AIAD_URI . '/assets/css/pages/single-hub-resource.css',
			array( 'aiad-single-timeline' ),
			AIAD_VERSION . '.' . (string) filemtime( $hub_css )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_hub_resource_assets', 15 );
