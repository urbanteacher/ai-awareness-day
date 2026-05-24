<?php
/**
 * NEU State of Education: AI Report 2026 — interactive data visualisation.
 *
 * Use [aiad_neu_ai_report] in any page or post.
 * Paste-friendly embed: html-snippets/neu-ai-report.html
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the NEU AI report shortcode.
 */
function aiad_post_content_has_neu_ai_report_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_neu_ai_report' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_neu_ai_report' );
}

/**
 * Whether the current singular view should load NEU AI report assets.
 */
function aiad_content_has_neu_ai_report_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_neu_ai_report_shortcode( $post );
}

/**
 * Register NEU AI report CSS/JS.
 */
function aiad_register_neu_ai_report_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-neu-ai-report.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-neu-ai-report.js';

	wp_register_style(
		'aiad-neu-ai-report',
		AIAD_URI . '/assets/css/components/ai-neu-ai-report.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-neu-ai-report',
		AIAD_URI . '/assets/js/ai-neu-ai-report.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_neu_ai_report_assets', 5 );

/**
 * Enqueue NEU AI report assets.
 */
function aiad_enqueue_neu_ai_report_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-neu-ai-report' );
	wp_enqueue_script( 'aiad-neu-ai-report' );

	if ( function_exists( 'aiad_neu_ai_report_get_config' ) ) {
		wp_localize_script( 'aiad-neu-ai-report', 'aiadNeuReportData', aiad_neu_ai_report_get_config() );
	}
}

/**
 * Shortcode: [aiad_neu_ai_report]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_neu_ai_report_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_neu_ai_report_shortcode_rendered'] = true;

	shortcode_atts( array(), is_array( $atts ) ? $atts : array(), 'aiad_neu_ai_report' );

	aiad_enqueue_neu_ai_report_assets();

	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.co.uk';
	$source_url = 'https://neu.org.uk/latest/press-releases/state-education-2026-ai';

	ob_start();
	?>
	<div id="aiad-neu-report" class="aiad-neu-ai-report nr-editorial" aria-label="<?php esc_attr_e( 'NEU State of Education AI report interactive data', 'ai-awareness-day' ); ?>">
		<div class="nr-intro">
			<p class="nr-kicker"><?php esc_html_e( 'Analysis', 'ai-awareness-day' ); ?></p>
			<div id="aiad-neu-standfirst" class="nr-standfirst"></div>
			<p class="nr-scope" id="aiad-neu-scope"></p>
		</div>

		<p class="nr-lens-label" id="aiad-neu-lens-label"><?php esc_html_e( 'View through the lens of:', 'ai-awareness-day' ); ?></p>
		<div class="nr-lens-row" role="tablist" aria-labelledby="aiad-neu-lens-label">
			<button type="button" class="nr-lens-btn" role="tab" data-lens="teacher" aria-pressed="true" aria-selected="true"><?php esc_html_e( 'Classroom teacher', 'ai-awareness-day' ); ?></button>
			<button type="button" class="nr-lens-btn" role="tab" data-lens="leader" aria-pressed="false" aria-selected="false"><?php esc_html_e( 'School leader / governor', 'ai-awareness-day' ); ?></button>
		</div>

		<div class="nr-sections" id="aiad-neu-sections"></div>

		<p class="nr-footer">
			<?php esc_html_e( 'Data source:', 'ai-awareness-day' ); ?>
			<a href="<?php echo esc_url( $source_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'NEU State of Education: AI Report 2026', 'ai-awareness-day' ); ?></a><br>
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_neu_ai_report', 'aiad_neu_ai_report_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_neu_ai_report_in_head(): void {
	if ( aiad_content_has_neu_ai_report_shortcode() ) {
		aiad_enqueue_neu_ai_report_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_neu_ai_report_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_neu_ai_report_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_neu_ai_report_shortcode_rendered'] ) ) {
		aiad_enqueue_neu_ai_report_assets();
	}
}
add_action( 'wp_footer', 'aiad_neu_ai_report_footer_assets', 1 );
