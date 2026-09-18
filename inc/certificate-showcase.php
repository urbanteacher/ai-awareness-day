<?php
/**
 * Certificate showcase — "what you earn" block for the AI Risk & Readiness
 * Benchmark.
 *
 * Carries both halves of the pitch: what the audit is, and what finishing it
 * earns you. It replaced the separate ink promo block, which said the first
 * half again a screen apart.
 *
 * Self-contained: one shortcode, one template part, one stylesheet. Drop
 * [aiad_certificate_showcase] into any page, or call
 * aiad_certificate_showcase() from a template.
 *
 * The artwork is drawn by the benchmark plugin's own renderer rather than a
 * flat image, so this block cannot advertise a certificate the audit no
 * longer issues. No plugin, no block.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the benchmark plugin can draw its certificate artwork here.
 */
function aiad_certificate_art_available(): bool {
	if ( ! defined( 'AIRB_PLUGIN_URL' ) || ! defined( 'AIRB_PLUGIN_DIR' ) ) {
		return false;
	}

	return file_exists( AIRB_PLUGIN_DIR . 'public/js/airb-certificate-art.js' );
}

/**
 * Register the showcase stylesheet and the plugin's certificate renderer.
 */
function aiad_register_certificate_showcase_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/certificate-showcase.css';

	wp_register_style(
		'aiad-certificate-showcase',
		AIAD_URI . '/assets/css/components/certificate-showcase.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	if ( ! aiad_certificate_art_available() ) {
		return;
	}

	/*
	 * The plugin registers this for its own pages only. It is standalone and
	 * resolves its own asset paths, so it can be registered again here under
	 * the same handle for pages the plugin knows nothing about.
	 */
	if ( ! wp_script_is( 'airb-certificate-art', 'registered' ) ) {
		wp_register_script(
			'airb-certificate-art',
			AIRB_PLUGIN_URL . 'public/js/airb-certificate-art.js',
			array(),
			defined( 'AIRB_VERSION' ) ? AIRB_VERSION : AIAD_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_register_certificate_showcase_assets', 5 );

/**
 * Render the showcase.
 *
 * @param array $args {
 *     @type string $eyebrow  Small label above the heading.
 *     @type string $title    Heading.
 *     @type string $lead     Supporting paragraph.
 *     @type array  $steps    Numbered steps.
 *     @type string $strand   AiAd27 strand colouring the example certificate.
 *     @type string $cta_url  Optional button URL.
 *     @type string $cta_text Optional button label.
 *     @type string $tone     'cream' (default) or 'ink'.
 * }
 */
function aiad_certificate_showcase( array $args = array() ): string {
	if ( ! aiad_certificate_art_available() ) {
		return '';
	}

	wp_enqueue_style( 'aiad-certificate-showcase' );
	wp_enqueue_script( 'airb-certificate-art' );

	$args = wp_parse_args(
		$args,
		array(
			/* The audit pitch, carried over from the old ink promo block so
			   that section could be retired without losing its messaging. */
			'section_id'  => 'benchmark-audit',
			'eyebrow'     => __( 'New · Free for UK schools', 'ai-awareness-day' ),
			'title'       => __( 'Audit your AI usage', 'ai-awareness-day' ),
			'lead'        => __( 'Take the AI Risk & Readiness Benchmark™ — a free interactive audit to measure adoption, dependency and readiness across your whole school community.', 'ai-awareness-day' ),
			'roles'       => function_exists( 'aiad_benchmark_promo_roles' ) ? aiad_benchmark_promo_roles() : array(),
			'cta_url'     => function_exists( 'aiad_get_benchmark_start_url' ) ? aiad_get_benchmark_start_url() : '',
			'cta_text'    => __( 'Start the free audit', 'ai-awareness-day' ),

			/* What completing it earns. */
			'cert_eyebrow' => __( 'What you earn', 'ai-awareness-day' ),
			'cert_title'   => __( 'A certificate you can evidence', 'ai-awareness-day' ),
			'cert_lead'    => __( 'Score 70 or above, then describe one thing you changed in your practice. Your certificate is issued in the AI Awareness Day strand you worked on, and downloads as an image you can keep, print or add to your CPD record.', 'ai-awareness-day' ),
			'steps'        => array(
				__( 'Complete your benchmark', 'ai-awareness-day' ),
				__( 'Evidence one change you made', 'ai-awareness-day' ),
				__( 'Download your certificate', 'ai-awareness-day' ),
			),

			'strand'      => 'safe',
			/* Ink by default: the neighbouring sections are cream, and a cream
			   block here reads as part of whatever sits above it. */
			'tone'        => 'ink',
		)
	);

	ob_start();
	get_template_part( 'template-parts/components/certificate-showcase', null, $args );
	return (string) ob_get_clean();
}

/**
 * [aiad_certificate_showcase] — same block, pasteable into any page or post.
 */
function aiad_certificate_showcase_shortcode( $atts ): string {
	$atts = shortcode_atts(
		array(
			'eyebrow'      => '',
			'title'        => '',
			'lead'         => '',
			'cert_eyebrow' => '',
			'cert_title'   => '',
			'cert_lead'    => '',
			'strand'       => 'safe',
			'cta_text'     => '',
			'cta_url'      => '',
			'tone'         => '',
			'section_id'   => '',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_certificate_showcase'
	);

	return aiad_certificate_showcase( array_filter( $atts, 'strlen' ) );
}
add_shortcode( 'aiad_certificate_showcase', 'aiad_certificate_showcase_shortcode' );
