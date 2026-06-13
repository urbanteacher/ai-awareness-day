<?php
/**
 * Schools AI Risk Academy — interactive risk assessment mini-app.
 *
 * Shortcode: [aiad_risk_academy]
 * Paste-friendly: html-snippets/schools-ai-risk-academy.html
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the timeline entry.
 */
function aiad_risk_academy_post_slug(): string {
	return 'schools-ai-risk-academy';
}

/**
 * Whether post content includes the risk academy shortcode.
 */
function aiad_post_content_has_risk_academy_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_risk_academy' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_risk_academy' );
}

/**
 * Whether the current singular view should load risk academy assets.
 */
function aiad_content_has_risk_academy_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_risk_academy_shortcode( $post );
}

/**
 * Register risk academy CSS/JS.
 */
function aiad_register_risk_academy_assets(): void {
	$fonts_path = AIAD_DIR . '/assets/css/components/sara-fonts.css';
	$css_path   = AIAD_DIR . '/assets/css/components/schools-ai-risk-academy.css';
	$js_path    = AIAD_DIR . '/assets/js/schools-ai-risk-academy.js';

	wp_register_style(
		'aiad-sara-fonts',
		AIAD_URI . '/assets/css/components/sara-fonts.css',
		array(),
		file_exists( $fonts_path ) ? (string) filemtime( $fonts_path ) : AIAD_VERSION
	);

	wp_register_style(
		'aiad-risk-academy',
		AIAD_URI . '/assets/css/components/schools-ai-risk-academy.css',
		array( 'aiad-sara-fonts' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-risk-academy',
		AIAD_URI . '/assets/js/schools-ai-risk-academy.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_risk_academy_assets', 5 );

/**
 * Enqueue risk academy assets.
 */
function aiad_enqueue_risk_academy_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-risk-academy' );
	wp_enqueue_script( 'aiad-risk-academy' );
}

/**
 * Timeline headline.
 */
function aiad_risk_academy_get_headline(): string {
	return __( 'Schools AI Risk Academy', 'ai-awareness-day' );
}

/**
 * Shortcode: [aiad_risk_academy]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_risk_academy_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_risk_academy_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'hero'          => '1',
			'methodology'   => '1',
			'meter'         => '1',
			'curriculum'    => '1',
			'contributors'  => '1',
			'resources'     => '1',
			'sources'       => '1',
			'enrol'         => '1',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_risk_academy'
	);

	aiad_enqueue_risk_academy_assets();

	$show = array();
	foreach ( array( 'hero', 'methodology', 'meter', 'curriculum', 'contributors', 'resources', 'sources', 'enrol' ) as $key ) {
		$show[ $key ] = ! in_array( strtolower( (string) $atts[ $key ] ), array( '0', 'false', 'no' ), true );
	}

	ob_start();
	$aiad_risk_academy_show = $show;
	include AIAD_DIR . '/template-parts/interactive/risk-academy.php';
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_risk_academy', 'aiad_risk_academy_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_risk_academy_in_head(): void {
	if ( aiad_content_has_risk_academy_shortcode() ) {
		aiad_enqueue_risk_academy_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_risk_academy_in_head', 20 );

/**
 * Footer fallback when shortcode rendered outside singular detection.
 */
function aiad_risk_academy_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_risk_academy_shortcode_rendered'] ) ) {
		aiad_enqueue_risk_academy_assets();
	}
}
add_action( 'wp_footer', 'aiad_risk_academy_footer_assets', 1 );

/**
 * Body class when the full academy is on the page (for full-width layout).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function aiad_risk_academy_body_class( array $classes ): array {
	if ( aiad_content_has_risk_academy_shortcode() ) {
		$classes[] = 'has-aiad-risk-academy';
	}
	return $classes;
}
add_filter( 'body_class', 'aiad_risk_academy_body_class' );

/**
 * Apply timeline meta for the risk academy entry.
 */
function aiad_set_risk_academy_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'shield' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create the risk academy timeline entry if missing.
 *
 * @return int Post ID or 0.
 */
function aiad_create_risk_academy_timeline_entry(): int {
	$slug = aiad_risk_academy_post_slug();

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => aiad_risk_academy_get_headline(),
			'post_excerpt' => __( 'A free mini-app for UK schools: assess AI exposure in your tasks, check student reliance, roll up a school risk level, and work through six DfE-aligned curriculum modules — all in the browser, nothing stored.', 'ai-awareness-day' ),
			'post_content' => '<!-- wp:shortcode -->[aiad_risk_academy]<!-- /wp:shortcode -->',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_risk_academy_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * Seed timeline entry (once).
 */
function aiad_seed_risk_academy_timeline_entry(): void {
	if ( get_option( 'aiad_risk_academy_timeline_seeded' ) === 'yes' ) {
		return;
	}

	if ( get_page_by_path( aiad_risk_academy_post_slug(), OBJECT, 'timeline' ) ) {
		update_option( 'aiad_risk_academy_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_risk_academy_timeline_entry() ) {
		update_option( 'aiad_risk_academy_timeline_seeded', 'yes' );
	}
}
add_action( 'init', 'aiad_seed_risk_academy_timeline_entry', 32 );

/**
 * One-time backfill: give the academy entry a proper excerpt if it predates
 * the excerpt being added to the seeder. Without it, the SEO/OG description
 * falls back to the post title instead of a meaningful summary.
 */
function aiad_backfill_risk_academy_excerpt(): void {
	if ( get_option( 'aiad_risk_academy_excerpt_backfilled' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_risk_academy_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( '' === trim( (string) $post->post_excerpt ) ) {
		wp_update_post(
			array(
				'ID'           => $post->ID,
				'post_excerpt' => __( 'A free mini-app for UK schools: assess AI exposure in your tasks, check student reliance, roll up a school risk level, and work through six DfE-aligned curriculum modules — all in the browser, nothing stored.', 'ai-awareness-day' ),
			)
		);
	}

	update_option( 'aiad_risk_academy_excerpt_backfilled', 'yes' );
}
add_action( 'init', 'aiad_backfill_risk_academy_excerpt', 33 );
