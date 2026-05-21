<?php
/**
 * Computing Curriculum Challenge KS1–KS5 — shortcode for teachers.
 *
 * Use [aiad_computing_curriculum] in any page or post.
 * Paste-friendly embed: html-snippets/computing-curriculum-challenge.html
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the computing curriculum challenge shortcode.
 */
function aiad_post_content_has_computing_curriculum_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_computing_curriculum' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_computing_curriculum' );
}

/**
 * Whether the current singular view should load computing curriculum assets.
 */
function aiad_content_has_computing_curriculum_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_computing_curriculum_shortcode( $post );
}

/**
 * Register computing curriculum challenge CSS/JS.
 */
function aiad_register_computing_curriculum_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-computing-curriculum-challenge.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-computing-curriculum-challenge.js';

	wp_register_style(
		'aiad-computing-curriculum',
		AIAD_URI . '/assets/css/components/ai-computing-curriculum-challenge.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	$rich_illus_path = AIAD_DIR . '/assets/js/ai-computing-curriculum-rich-illus.js';
	$illus_path      = AIAD_DIR . '/assets/js/ai-computing-curriculum-illustrations.js';

	wp_register_script(
		'aiad-computing-curriculum-rich-illus',
		AIAD_URI . '/assets/js/ai-computing-curriculum-rich-illus.js',
		array(),
		file_exists( $rich_illus_path ) ? (string) filemtime( $rich_illus_path ) : AIAD_VERSION,
		true
	);

	wp_register_script(
		'aiad-computing-curriculum-illustrations',
		AIAD_URI . '/assets/js/ai-computing-curriculum-illustrations.js',
		array( 'aiad-computing-curriculum-rich-illus' ),
		file_exists( $illus_path ) ? (string) filemtime( $illus_path ) : AIAD_VERSION,
		true
	);

	wp_register_script(
		'aiad-computing-curriculum',
		AIAD_URI . '/assets/js/ai-computing-curriculum-challenge.js',
		array( 'aiad-computing-curriculum-illustrations' ),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_computing_curriculum_assets', 5 );

/**
 * Enqueue computing curriculum challenge assets.
 */
function aiad_enqueue_computing_curriculum_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-computing-curriculum' );
	wp_enqueue_script( 'aiad-computing-curriculum' );
}

/**
 * Shortcode: [aiad_computing_curriculum]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_computing_curriculum_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_computing_curriculum_shortcode_rendered'] = true;

	shortcode_atts( array(), is_array( $atts ) ? $atts : array(), 'aiad_computing_curriculum' );

	aiad_enqueue_computing_curriculum_assets();

	$host = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.co.uk';

	ob_start();
	?>
	<div id="aiad-cc" class="aiad-computing-curriculum" aria-label="<?php esc_attr_e( 'Computing and AI curriculum taster for all teachers', 'ai-awareness-day' ); ?>">
		<div class="cc-intro">
			<p class="cc-intro-kicker"><?php esc_html_e( 'AI Awareness Day · Computing & AI taster', 'ai-awareness-day' ); ?></p>
			<h2><?php esc_html_e( 'A quick taste of computing — for every teacher', 'ai-awareness-day' ); ?></h2>
			<p><?php esc_html_e( 'You do not need to be a computing specialist to take part. This embedded activity gives colleagues from any subject a short, low-pressure introduction to what students learn in the UK computing curriculum — and how AI connects at each key stage.', 'ai-awareness-day' ); ?></p>
			<p><?php esc_html_e( 'Choose a key stage below. Each section takes a few minutes and uses familiar classroom-style tasks: multiple choice, matching, and a short self-marked response at GCSE and A level. Nothing to submit — explore at your own pace, compare notes with colleagues, and notice the computing ideas behind the AI topics your students already meet.', 'ai-awareness-day' ); ?></p>
			<p class="cc-intro-note"><?php esc_html_e( 'This is a taster of how students across the different key stages encounter the backbone of AI.', 'ai-awareness-day' ); ?></p>
		</div>
		<p class="cc-hub-title"><?php esc_html_e( 'Choose a key stage to try', 'ai-awareness-day' ); ?></p>
		<div class="cc-ks-tabs" id="aiad-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Key stages', 'ai-awareness-day' ); ?>"></div>
		<div id="aiad-screens"></div>
		<p class="cc-footer">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_computing_curriculum', 'aiad_computing_curriculum_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_computing_curriculum_in_head(): void {
	if ( aiad_content_has_computing_curriculum_shortcode() ) {
		aiad_enqueue_computing_curriculum_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_computing_curriculum_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_computing_curriculum_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_computing_curriculum_shortcode_rendered'] ) ) {
		aiad_enqueue_computing_curriculum_assets();
	}
}
add_action( 'wp_footer', 'aiad_computing_curriculum_footer_assets', 1 );
