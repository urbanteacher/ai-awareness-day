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
 * Timeline headline for the misinformation detector article.
 */
function aiad_misinformation_detector_get_headline(): string {
	return __( 'How good are you at detecting misinformation? The teacher challenge', 'ai-awareness-day' );
}

/**
 * Use the editorial headline when the shortcode is on a timeline or post.
 *
 * @param string $title Post title.
 * @param int    $post_id Post ID.
 */
function aiad_misinformation_detector_filter_the_title( string $title, int $post_id = 0 ): string {
	if ( is_admin() || ! $post_id ) {
		return $title;
	}
	$post = get_post( $post_id );
	if ( ! $post instanceof WP_Post || ! aiad_post_content_has_misinformation_detector_shortcode( $post ) ) {
		return $title;
	}
	return aiad_misinformation_detector_get_headline();
}
add_filter( 'the_title', 'aiad_misinformation_detector_filter_the_title', 10, 2 );

/**
 * @param array<string, string> $parts Document title parts.
 * @return array<string, string>
 */
function aiad_misinformation_detector_filter_document_title_parts( array $parts ): array {
	if ( ! is_singular() || ! aiad_content_has_misinformation_detector_shortcode() ) {
		return $parts;
	}
	$parts['title'] = aiad_misinformation_detector_get_headline();
	return $parts;
}
add_filter( 'document_title_parts', 'aiad_misinformation_detector_filter_document_title_parts' );

/**
 * Shortcode: [aiad_misinformation_detector]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_misinformation_detector_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_misinformation_detector_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'hide_intro' => 'auto',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_misinformation_detector'
	);

	$hide_intro = in_array( strtolower( (string) $atts['hide_intro'] ), array( '1', 'true', 'yes' ), true );
	if ( 'auto' === $atts['hide_intro'] && is_singular() ) {
		$hide_intro = true;
	}

	aiad_enqueue_misinformation_detector_assets();

	ob_start();
	?>
	<div id="aiad-misinfo" class="aiad-misinformation-detector" aria-label="<?php echo esc_attr( aiad_misinformation_detector_get_headline() ); ?>">
		<?php if ( ! $hide_intro ) : ?>
		<div id="aiad-mi-intro" class="mi-card">
			<p class="mi-kicker"><?php esc_html_e( 'AI Awareness Day · Media literacy', 'ai-awareness-day' ); ?></p>
			<h2 class="mi-title"><?php echo esc_html( aiad_misinformation_detector_get_headline() ); ?></h2>
			<p class="mi-lead"><?php esc_html_e( 'Six real claims — genuine BBC headlines, AI-generated text, and viral posts. Check your sources, give your verdict, then see the truth revealed.', 'ai-awareness-day' ); ?></p>
			<p class="mi-lead"><?php esc_html_e( '10 points for a correct verdict, plus 5 bonus points when you name two or more sources before answering.', 'ai-awareness-day' ); ?></p>
			<button type="button" class="mi-btn mi-btn--primary" id="aiad-mi-start"><?php esc_html_e( 'Start activity', 'ai-awareness-day' ); ?></button>
		</div>
		<?php else : ?>
		<div id="aiad-mi-intro" class="mi-card" hidden>
			<button type="button" class="mi-btn mi-btn--primary" id="aiad-mi-start"><?php esc_html_e( 'Start activity', 'ai-awareness-day' ); ?></button>
		</div>
		<?php endif; ?>

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
				<p class="mi-context" id="aiad-mi-context" hidden></p>

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

/**
 * Timeline entry slug.
 */
function aiad_misinformation_detector_post_slug(): string {
	return 'misinformation-detector-teachers';
}

/**
 * WordPress block content for the timeline entry.
 */
function aiad_get_misinformation_detector_timeline_content(): string {
	return '<!-- wp:paragraph -->
<p>Staff rooms and WhatsApp groups are full of claims about AI in education — some accurate, some exaggerated, and some entirely invented. Before you forward the next screenshot or quote a chatbot in a policy meeting, how well can you tell the difference?</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>This interactive challenge presents <strong>six real-world examples</strong>: verified BBC headlines, AI hallucinations, viral social posts, and research shared out of context. Log the sources you would check, give your verdict, then see the truth revealed with links and a discernment lesson for each item.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_misinformation_detector hide_intro="1"]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>How to use this with students:</strong> run one item per tutor time or assembly starter. Ask pairs to agree on two sources before revealing the answer, then discuss the discernment lesson. Pair with our <a href="/timeline/15-ai-buzzwords-teachers-2026/">AI buzzwords glossary</a> for the language side of the same conversation.</p>
<!-- /wp:paragraph -->';
}

/**
 * Apply timeline meta for the misinformation detector entry.
 */
function aiad_set_misinformation_detector_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'announcement' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create the misinformation detector timeline entry if missing.
 *
 * @return int Post ID or 0.
 */
function aiad_create_misinformation_detector_timeline_entry(): int {
	$slug  = aiad_misinformation_detector_post_slug();
	$title = aiad_misinformation_detector_get_headline();

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( 'Six-claim media literacy challenge for teachers: verify headlines, AI outputs, and viral posts before you share — with scoring, sources, and discernment habits.', 'ai-awareness-day' ),
			'post_content' => aiad_get_misinformation_detector_timeline_content(),
			'post_status'  => 'publish',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_misinformation_detector_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * One-time seed: misinformation detector timeline entry.
 */
function aiad_seed_misinformation_detector_timeline_entry(): void {
	if ( get_option( 'aiad_misinformation_detector_timeline_seeded' ) === 'yes' ) {
		return;
	}

	$slug  = aiad_misinformation_detector_post_slug();
	$title = aiad_misinformation_detector_get_headline();

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_misinformation_detector_timeline_seeded', 'yes' );
		return;
	}

	if ( function_exists( 'aiad_get_post_by_title' ) && aiad_get_post_by_title( $title, 'timeline' ) ) {
		update_option( 'aiad_misinformation_detector_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_misinformation_detector_timeline_entry() ) {
		update_option( 'aiad_misinformation_detector_timeline_seeded', 'yes' );
		set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
	}
}
add_action( 'init', 'aiad_seed_misinformation_detector_timeline_entry', 34 );
