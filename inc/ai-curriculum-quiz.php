<?php
/**
 * Cross-curricular curriculum insight quiz for teachers — shortcode only (not used on timeline).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the curriculum quiz shortcode.
 */
function aiad_post_content_has_curriculum_quiz_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_curriculum_quiz' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_curriculum_quiz' );
}

/**
 * Whether the current singular view should load curriculum quiz assets.
 */
function aiad_content_has_curriculum_quiz_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_curriculum_quiz_shortcode( $post );
}

/**
 * Register curriculum quiz CSS/JS.
 */
function aiad_register_curriculum_quiz_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-curriculum-quiz.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-curriculum-quiz.js';

	wp_register_style(
		'aiad-curriculum-quiz',
		AIAD_URI . '/assets/css/components/ai-curriculum-quiz.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-curriculum-quiz',
		AIAD_URI . '/assets/js/ai-curriculum-quiz.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_curriculum_quiz_assets', 5 );

/**
 * Enqueue curriculum quiz assets.
 */
function aiad_enqueue_curriculum_quiz_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-curriculum-quiz' );
	wp_enqueue_script( 'aiad-curriculum-quiz' );
}

/**
 * Shortcode: [aiad_curriculum_quiz]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_curriculum_quiz_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_curriculum_quiz_shortcode_rendered'] = true;

	shortcode_atts( array(), is_array( $atts ) ? $atts : array(), 'aiad_curriculum_quiz' );

	aiad_enqueue_curriculum_quiz_assets();

	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com';

	ob_start();
	?>
	<div
		class="aiad-curriculum-quiz"
		data-aiad-curriculum-quiz
		aria-label="<?php esc_attr_e( 'Exploring digital and computing assessment across the key stages', 'ai-awareness-day' ); ?>"
	>
		<div data-cq-hub></div>

		<div data-cq-main hidden>
			<nav class="cq-ks-nav" data-cq-ks-tabs aria-label="<?php esc_attr_e( 'Key stage', 'ai-awareness-day' ); ?>"></nav>
			<nav class="cq-stage-nav" data-cq-stage-nav aria-label="<?php esc_attr_e( 'Section', 'ai-awareness-day' ); ?>" hidden></nav>
			<div class="cq-toolbar" role="toolbar" aria-label="<?php esc_attr_e( 'Session options', 'ai-awareness-day' ); ?>">
				<label class="cq-toggle">
					<input type="checkbox" data-cq-facilitator />
					<?php esc_html_e( 'Facilitator notes', 'ai-awareness-day' ); ?>
				</label>
				<label class="cq-toggle">
					<input type="checkbox" data-cq-team />
					<?php esc_html_e( 'Team challenge prompts', 'ai-awareness-day' ); ?>
				</label>
			</div>
			<div class="cq-progress" data-cq-progress aria-hidden="true"></div>
			<div data-cq-panel></div>
		</div>

		<p class="cq-credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_curriculum_quiz', 'aiad_curriculum_quiz_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_curriculum_quiz_in_head(): void {
	if ( aiad_content_has_curriculum_quiz_shortcode() ) {
		aiad_enqueue_curriculum_quiz_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_curriculum_quiz_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_curriculum_quiz_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_curriculum_quiz_shortcode_rendered'] ) ) {
		aiad_enqueue_curriculum_quiz_assets();
	}
}
add_action( 'wp_footer', 'aiad_curriculum_quiz_footer_assets', 1 );
