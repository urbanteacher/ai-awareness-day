<?php
/**
 * Misinformation Detector — interactive article for teachers.
 *
 * Use [aiad_misinformation_detector] in any page or post.
 * Paste-friendly embed: html-snippets/misinformation-detector.html
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the misinformation detector shortcode.
 */
function aiad_post_content_has_misinformation_detector_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_misinformation_detector' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_misinformation_detector' );
}

/**
 * Whether the current singular view should load misinformation detector assets.
 */
function aiad_content_has_misinformation_detector_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_misinformation_detector_shortcode( $post );
}

/**
 * Register misinformation detector CSS/JS.
 */
function aiad_register_misinformation_detector_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-misinformation-detector.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-misinformation-detector.js';

	wp_register_style(
		'aiad-misinformation-detector',
		AIAD_URI . '/assets/css/components/ai-misinformation-detector.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-misinformation-detector',
		AIAD_URI . '/assets/js/ai-misinformation-detector.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_misinformation_detector_assets', 5 );

/**
 * Enqueue misinformation detector assets.
 */
function aiad_enqueue_misinformation_detector_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-misinformation-detector' );
	wp_enqueue_script( 'aiad-misinformation-detector' );
}

/**
 * Shortcode: [aiad_misinformation_detector]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_misinformation_detector_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_misinformation_detector_shortcode_rendered'] = true;

	shortcode_atts( array(), is_array( $atts ) ? $atts : array(), 'aiad_misinformation_detector' );

	aiad_enqueue_misinformation_detector_assets();

	ob_start();
	?>
	<div id="aiad-misinfo" class="aiad-misinformation-detector" aria-label="<?php esc_attr_e( 'Misinformation detector classroom activity', 'ai-awareness-day' ); ?>">
		<div id="aiad-mi-intro" class="mi-card">
			<p class="mi-kicker"><?php esc_html_e( 'AI Awareness Day · Media literacy', 'ai-awareness-day' ); ?></p>
			<h2 class="mi-title"><?php esc_html_e( 'Misinformation detector', 'ai-awareness-day' ); ?></h2>
			<p class="mi-lead"><?php esc_html_e( 'Six claims — headlines, AI outputs, and viral posts. Before you judge each one, log where you would check. Then pick a verdict and see the real answer, sources, and a discernment lesson.', 'ai-awareness-day' ); ?></p>
			<p class="mi-lead"><?php esc_html_e( '10 points for a correct verdict, plus 5 bonus points when you name two or more sources before answering.', 'ai-awareness-day' ); ?></p>
			<button type="button" class="mi-btn mi-btn--primary" id="aiad-mi-start"><?php esc_html_e( 'Start activity', 'ai-awareness-day' ); ?></button>
		</div>

		<div id="aiad-mi-quiz" class="mi-quiz" hidden>
			<div class="mi-progress-wrap">
				<p class="mi-progress-lbl" id="aiad-mi-progress-lbl"><?php esc_html_e( 'Item 1 of 6', 'ai-awareness-day' ); ?></p>
				<div class="mi-progress-track" role="progressbar" aria-valuemin="1" aria-valuemax="6" aria-valuenow="1">
					<div class="mi-progress-bar" id="aiad-mi-progress-bar"></div>
				</div>
			</div>

			<div class="mi-card">
				<span class="mi-type-pill" id="aiad-mi-type-pill"></span>
				<p class="mi-claim" id="aiad-mi-claim"></p>
				<p class="mi-source-lbl" id="aiad-mi-source"></p>

				<p class="mi-sources-title"><?php esc_html_e( 'Sources I would check', 'ai-awareness-day' ); ?></p>
				<p class="mi-sources-hint" id="aiad-mi-sources-hint"><?php esc_html_e( 'Name at least one before choosing a verdict (two or more earns bonus points).', 'ai-awareness-day' ); ?></p>
				<label class="screen-reader-text" for="aiad-mi-src-1"><?php esc_html_e( 'Source 1', 'ai-awareness-day' ); ?></label>
				<input type="text" class="mi-src-input" id="aiad-mi-src-1" placeholder="<?php esc_attr_e( 'e.g. GOV.UK, BBC News, Full Fact…', 'ai-awareness-day' ); ?>" autocomplete="off">
				<label class="screen-reader-text" for="aiad-mi-src-2"><?php esc_html_e( 'Source 2', 'ai-awareness-day' ); ?></label>
				<input type="text" class="mi-src-input" id="aiad-mi-src-2" placeholder="<?php esc_attr_e( 'Second source (optional)', 'ai-awareness-day' ); ?>" autocomplete="off">
				<label class="screen-reader-text" for="aiad-mi-src-3"><?php esc_html_e( 'Source 3', 'ai-awareness-day' ); ?></label>
				<input type="text" class="mi-src-input" id="aiad-mi-src-3" placeholder="<?php esc_attr_e( 'Third source (optional)', 'ai-awareness-day' ); ?>" autocomplete="off">

				<p class="mi-verdict-title"><?php esc_html_e( 'Your verdict', 'ai-awareness-day' ); ?></p>
				<div class="mi-verdicts" id="aiad-mi-verdicts" role="group" aria-label="<?php esc_attr_e( 'Choose a verdict', 'ai-awareness-day' ); ?>">
					<button type="button" class="mi-verdict-btn" data-verdict="true"><?php esc_html_e( 'Likely true', 'ai-awareness-day' ); ?></button>
					<button type="button" class="mi-verdict-btn" data-verdict="false"><?php esc_html_e( 'Likely false', 'ai-awareness-day' ); ?></button>
					<button type="button" class="mi-verdict-btn" data-verdict="mixed"><?php esc_html_e( 'Needs context', 'ai-awareness-day' ); ?></button>
				</div>

				<div class="mi-reveal" id="aiad-mi-reveal" hidden>
					<p class="mi-reveal-title" id="aiad-mi-reveal-title"></p>
					<p class="mi-reveal-fb" id="aiad-mi-reveal-fb"></p>
					<p class="mi-reveal-expl" id="aiad-mi-reveal-expl"></p>
					<p class="mi-reveal-sub"><?php esc_html_e( 'Where to verify', 'ai-awareness-day' ); ?></p>
					<ul class="mi-reveal-sources" id="aiad-mi-reveal-sources"></ul>
					<p class="mi-reveal-sub"><?php esc_html_e( 'Discernment lesson', 'ai-awareness-day' ); ?></p>
					<p class="mi-reveal-lesson" id="aiad-mi-reveal-lesson"></p>
				</div>

				<div class="mi-nav">
					<button type="button" class="mi-btn mi-btn--primary" id="aiad-mi-next" hidden><?php esc_html_e( 'Next item →', 'ai-awareness-day' ); ?></button>
				</div>
			</div>
		</div>

		<div id="aiad-mi-results" hidden>
			<div class="mi-card">
				<p class="mi-kicker"><?php esc_html_e( 'Your results', 'ai-awareness-day' ); ?></p>
				<h2 class="mi-title"><?php esc_html_e( 'How did you do?', 'ai-awareness-day' ); ?></h2>
				<span class="mi-conf-badge mi-conf--low" id="aiad-mi-conf-badge"></span>

				<div class="mi-stat-grid">
					<div class="mi-stat">
						<span class="mi-stat-val" id="aiad-mi-stat-points">0</span>
						<span class="mi-stat-lbl"><?php esc_html_e( 'Total points', 'ai-awareness-day' ); ?></span>
					</div>
					<div class="mi-stat">
						<span class="mi-stat-val" id="aiad-mi-stat-correct">0 / 6</span>
						<span class="mi-stat-lbl"><?php esc_html_e( 'Correct verdicts', 'ai-awareness-day' ); ?></span>
					</div>
					<div class="mi-stat">
						<span class="mi-stat-val" id="aiad-mi-stat-sources">0</span>
						<span class="mi-stat-lbl"><?php esc_html_e( 'Items with 2+ sources logged', 'ai-awareness-day' ); ?></span>
					</div>
				</div>

				<h3 class="mi-verdict-title"><?php esc_html_e( 'Five habits of discernment', 'ai-awareness-day' ); ?></h3>
				<ol class="mi-habits" id="aiad-mi-habits">
					<li><?php esc_html_e( 'Pause on urgency — “share before deleted” is a red flag.', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Name the source type: newsroom, AI output, or anonymous viral post.', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Log where you would check before you judge.', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Separate the number or headline from the story someone wants you to believe.', 'ai-awareness-day' ); ?></li>
					<li><?php esc_html_e( 'Teach students the same habit: verify, then verdict.', 'ai-awareness-day' ); ?></li>
				</ol>

				<h3 class="mi-verdict-title"><?php esc_html_e( 'What this exercise revealed', 'ai-awareness-day' ); ?></h3>
				<div class="mi-insights">
					<div class="mi-insight">
						<p class="mi-insight-title"><?php esc_html_e( 'AI hallucinations', 'ai-awareness-day' ); ?></p>
						<p class="mi-insight-text"><?php esc_html_e( 'Chatbots invent studies, laws, and statistics. Numbers without citations are not evidence.', 'ai-awareness-day' ); ?></p>
					</div>
					<div class="mi-insight">
						<p class="mi-insight-title"><?php esc_html_e( 'Real headlines, stretched meaning', 'ai-awareness-day' ); ?></p>
						<p class="mi-insight-text"><?php esc_html_e( 'BBC and Guardian stories can be real while captions or WhatsApp forwards add false certainty.', 'ai-awareness-day' ); ?></p>
					</div>
					<div class="mi-insight">
						<p class="mi-insight-title"><?php esc_html_e( 'Viral posts mimic news', 'ai-awareness-day' ); ?></p>
						<p class="mi-insight-text"><?php esc_html_e( 'WhatsApp chains borrow the tone of journalism without links, bylines, or accountability.', 'ai-awareness-day' ); ?></p>
					</div>
				</div>

				<button type="button" class="mi-btn" id="aiad-mi-retry"><?php esc_html_e( 'Try again', 'ai-awareness-day' ); ?></button>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_misinformation_detector', 'aiad_misinformation_detector_shortcode' );

/**
 * Load assets in head when shortcode is in post content.
 */
function aiad_maybe_enqueue_misinformation_detector_in_head(): void {
	if ( aiad_content_has_misinformation_detector_shortcode() ) {
		aiad_enqueue_misinformation_detector_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_misinformation_detector_in_head', 20 );

/**
 * Footer fallback when shortcode renders late.
 */
function aiad_misinformation_detector_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_misinformation_detector_shortcode_rendered'] ) ) {
		aiad_enqueue_misinformation_detector_assets();
	}
}
add_action( 'wp_footer', 'aiad_misinformation_detector_footer_assets', 1 );
