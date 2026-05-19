<?php
/**
 * Cross-curricular curriculum insight quiz for teachers — shortcode and timeline seed.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the curriculum quiz timeline entry.
 */
function aiad_curriculum_quiz_post_slug(): string {
	return 'cross-curricular-ai-curriculum-quiz';
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
		aria-label="<?php esc_attr_e( 'Cross-curricular AI curriculum insight for teachers', 'ai-awareness-day' ); ?>"
	>
		<div data-cq-hub></div>

		<div data-cq-main hidden>
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
			<nav class="cq-tabs" data-cq-tabs aria-label="<?php esc_attr_e( 'Key stage', 'ai-awareness-day' ); ?>"></nav>
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

/**
 * Gutenberg body for curriculum quiz timeline entry.
 */
function aiad_get_curriculum_quiz_timeline_content(): string {
	$speed_url = esc_url( home_url( '/timeline/' . aiad_speed_quiz_post_slug() . '/' ) );
	$buzz_url  = esc_url( home_url( '/timeline/' . aiad_buzzwords_post_slug() . '/' ) );
	$llm_url   = esc_url( home_url( '/timeline/' . aiad_llm_explainer_post_slug() . '/' ) );

	return '<!-- wp:paragraph -->
<p>What does a <strong>modern, cross-curricular AI curriculum</strong> feel like at each key stage? This interactive experience lets you <strong>taste KS3, KS4, and KS5</strong> — read the objectives and content focus, then <strong>have a go</strong> at sample exam-style questions or <strong>reveal model answers</strong> with mark schemes, discussion prompts, and optional facilitator notes.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Topics include ethics, bias, hallucinations, reliability, and classroom AI use — without referencing any specific exam board. Turn on <strong>facilitator notes</strong> for CPD, or <strong>team challenge prompts</strong> for a staff-room activity.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_curriculum_quiz]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>Staff-room idea:</strong> split into subject teams, work through one key stage, then compare how science, English, and citizenship would mark the same AI scenario differently. Pair with the <a href="' . $speed_url . '">AI speed quiz</a>, <a href="' . $llm_url . '">How LLMs work</a> explainer, or <a href="' . $buzz_url . '">15 buzzwords glossary</a> for a full AI Awareness Day session.</p>
<!-- /wp:paragraph -->';
}

/**
 * Timeline meta for curriculum quiz entry.
 */
function aiad_set_curriculum_quiz_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'announcement' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create curriculum quiz timeline entry.
 *
 * @return int Post ID or 0.
 */
function aiad_create_curriculum_quiz_timeline_entry(): int {
	$slug  = aiad_curriculum_quiz_post_slug();
	$title = __( 'Cross-Curricular AI Curriculum Insight for Teachers', 'ai-awareness-day' );

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( 'KS3, KS4 and KS5 exam-style AI questions with facilitator notes, discussion prompts, and team challenges — for any subject teacher.', 'ai-awareness-day' ),
			'post_content' => aiad_get_curriculum_quiz_timeline_content(),
			'post_status'  => 'draft',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_curriculum_quiz_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * One-time seed.
 */
function aiad_seed_curriculum_quiz_timeline_entry(): void {
	if ( get_option( 'aiad_curriculum_quiz_timeline_seeded' ) === 'yes' ) {
		return;
	}

	$slug = aiad_curriculum_quiz_post_slug();

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_curriculum_quiz_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_curriculum_quiz_timeline_entry() ) {
		update_option( 'aiad_curriculum_quiz_timeline_seeded', 'yes' );
	}
}
add_action( 'init', 'aiad_seed_curriculum_quiz_timeline_entry', 32 );

/**
 * One-time: set speed quiz and curriculum quiz timeline entries to draft (including if already published).
 */
function aiad_draft_new_interactive_timeline_entries(): void {
	if ( get_option( 'aiad_interactive_timeline_entries_drafted' ) === 'yes' ) {
		return;
	}

	if ( ! function_exists( 'aiad_speed_quiz_post_slug' ) || ! function_exists( 'aiad_curriculum_quiz_post_slug' ) ) {
		return;
	}

	foreach ( array( aiad_speed_quiz_post_slug(), aiad_curriculum_quiz_post_slug() ) as $slug ) {
		$post = get_page_by_path( $slug, OBJECT, 'timeline' );
		if ( $post instanceof WP_Post && 'draft' !== $post->post_status ) {
			wp_update_post(
				array(
					'ID'          => (int) $post->ID,
					'post_status' => 'draft',
				)
			);
		}
	}

	update_option( 'aiad_interactive_timeline_entries_drafted', 'yes' );
}
add_action( 'init', 'aiad_draft_new_interactive_timeline_entries', 33 );
