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

/**
 * Gutenberg body for curriculum quiz timeline entry.
 */
function aiad_get_curriculum_quiz_timeline_content(): string {
	$speed_url = esc_url( home_url( '/timeline/' . aiad_speed_quiz_post_slug() . '/' ) );
	$buzz_url  = esc_url( home_url( '/timeline/' . aiad_buzzwords_post_slug() . '/' ) );
	$llm_url   = esc_url( home_url( '/timeline/' . aiad_llm_explainer_post_slug() . '/' ) );

	return '<!-- wp:heading -->
<h2 class="wp-block-heading">AI Awareness Day</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><strong>Exploring Digital &amp; Computing Assessment Across the Key Stages</strong></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Artificial intelligence is becoming part of everyday conversations in education. From online safety and misinformation to algorithms, automation and digital ethics, schools are increasingly exploring how emerging technologies connect to teaching, learning and assessment.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>However, this project is <strong>not</strong> about creating a new AI qualification or replacing the National Curriculum. Instead, AI Awareness Day has been designed to help teachers from <strong>all subject areas</strong> experience the style of modern digital, IT and Computer Science assessment using familiar classroom formats.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Why this matters</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Many teachers outside of Computing and IT are now encountering AI-generated content, misinformation, automated systems, digital safeguarding, and questions from students about emerging technologies. This experience builds confidence, encourages discussion, supports digital literacy, and helps staff explore how digital thinking develops across the key stages — without turning every teacher into a Computer Science specialist.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Interactive assessment experience</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Use the tool below to open each section: <strong>Online Safety &amp; Digital Awareness</strong> (overview), then <strong>KS2 Digital Awareness</strong>, <strong>KS3 Algorithms &amp; Programming Logic</strong>, <strong>KS4 Ethics &amp; Technology Evaluation</strong>, and <strong>KS5 Data Structures &amp; Emerging Technologies</strong>. Each section includes context for staff and interactive cards with try/reveal answers, mark scheme guidance, and optional facilitator or team prompts.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><strong>Exam board note:</strong> KS4 and KS5 samples are aligned to <strong>OCR</strong> and <strong>AQA</strong> GCSE/A level Computer Science specifications and published mark schemes (England). Tariffs vary — for example OCR GCSE impacts items are usually on <strong>Paper 1 (Computer systems)</strong> at <strong>6 or 8 marks</strong>; AQA uses <strong>6 marks</strong> on Paper 1 and <strong>up to 9 marks</strong> on Paper 2; OCR A level legal/ethical discuss items are typically <strong>9 marks</strong> on Component 01. Confirm entries with your computing department.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_curriculum_quiz]
<!-- /wp:shortcode -->

<!-- wp:heading {"level":3} -->
<h3 class="wp-block-heading">Staff-room idea</h3>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Split into subject teams, work through one key stage, then compare how science, English, and citizenship would discuss the same digital scenario differently. Pair with the <a href="' . $speed_url . '">AI speed quiz</a>, <a href="' . $llm_url . '">How LLMs work</a> explainer, or <a href="' . $buzz_url . '">15 buzzwords glossary</a> for a full AI Awareness Day session.</p>
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
	$title = __( 'Exploring Digital & Computing Assessment Across the Key Stages', 'ai-awareness-day' );

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( 'Online safety overview plus KS2–KS5 digital and computing assessment experience — interactive cards for all subject teachers.', 'ai-awareness-day' ),
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

/**
 * One-time: refresh timeline copy for assessment experience v2.
 */
function aiad_refresh_curriculum_quiz_timeline_copy(): void {
	if ( get_option( 'aiad_curriculum_quiz_timeline_v3' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_curriculum_quiz_post_slug(), OBJECT, 'timeline' );
	if ( $post instanceof WP_Post ) {
		wp_update_post(
			array(
				'ID'           => (int) $post->ID,
				'post_title'   => __( 'Exploring Digital & Computing Assessment Across the Key Stages', 'ai-awareness-day' ),
				'post_excerpt' => __( 'Online safety overview plus KS2–KS5 digital and computing assessment experience — interactive cards for all subject teachers.', 'ai-awareness-day' ),
				'post_content' => aiad_get_curriculum_quiz_timeline_content(),
			)
		);
	}

	update_option( 'aiad_curriculum_quiz_timeline_v3', 'yes' );
}
add_action( 'init', 'aiad_refresh_curriculum_quiz_timeline_copy', 34 );
