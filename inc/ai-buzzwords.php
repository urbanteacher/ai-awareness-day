<?php
/**
 * AI Buzzwords interactive glossary shortcode and timeline entry seed.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the buzzwords timeline entry.
 */
function aiad_buzzwords_post_slug(): string {
	return '15-ai-buzzwords-teachers-2026';
}

/**
 * Whether post content includes the buzzwords shortcode.
 */
function aiad_post_content_has_buzzwords_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_buzzwords' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_buzzwords' );
}

/**
 * Whether the current singular view should load buzzwords assets.
 */
function aiad_content_has_buzzwords_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_buzzwords_shortcode( $post );
}

/**
 * Register buzzwords CSS/JS (enqueue separately).
 */
function aiad_register_buzzwords_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-buzzwords.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-buzzwords.js';

	wp_register_style(
		'aiad-buzzwords',
		AIAD_URI . '/assets/css/components/ai-buzzwords.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-buzzwords',
		AIAD_URI . '/assets/js/ai-buzzwords.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_buzzwords_assets', 5 );

/**
 * Enqueue buzzwords assets when the shortcode is present.
 */
function aiad_enqueue_buzzwords_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-buzzwords' );
	wp_enqueue_script( 'aiad-buzzwords' );
}

/**
 * Shortcode: [aiad_buzzwords]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_buzzwords_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_buzzwords_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'title'       => __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-awareness-day' ),
			'description' => __( 'From "agentic AI" to "vibe coding" — the terms everyone\'s talking about, explained in plain English with classroom angles.', 'ai-awareness-day' ),
			'hide_intro'  => '0',
			'quiz'        => '1',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_buzzwords'
	);

	aiad_enqueue_buzzwords_assets();

	$hide_intro = in_array( strtolower( (string) $atts['hide_intro'] ), array( '1', 'true', 'yes' ), true );
	$show_quiz  = ! in_array( strtolower( (string) $atts['quiz'] ), array( '0', 'false', 'no' ), true );

	ob_start();
	?>
	<div data-aiad-buzzwords class="aiad-buzzwords-widget" aria-label="<?php esc_attr_e( 'AI buzzwords glossary', 'ai-awareness-day' ); ?>"<?php echo $show_quiz ? ' data-aiad-bz-quiz-enabled="1"' : ''; ?>>
		<?php if ( ! $hide_intro ) : ?>
			<div class="aiad-intro">
				<h2><?php echo esc_html( $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $atts['description'] ); ?></p>
			</div>
		<?php endif; ?>
		<div class="aiad-filters" data-aiad-bz-filters></div>
		<p class="aiad-count" data-aiad-bz-count></p>
		<div class="aiad-grid" data-aiad-bz-grid></div>
		<?php if ( $show_quiz ) : ?>
			<section class="aiad-bz-quiz" data-aiad-bz-quiz aria-labelledby="aiad-bz-quiz-heading">
				<h3 id="aiad-bz-quiz-heading" class="aiad-bz-quiz__heading"><?php esc_html_e( 'Quick quiz: score out of 5', 'ai-awareness-day' ); ?></h3>
				<p class="aiad-bz-quiz__intro"><?php esc_html_e( 'Five questions drawn from the glossary. Match each definition to the right buzzword.', 'ai-awareness-day' ); ?></p>
				<div class="aiad-bz-quiz__start-card" data-aiad-bz-quiz-start-card>
					<p class="aiad-bz-quiz__start-eyebrow"><?php esc_html_e( 'Ready to test yourself?', 'ai-awareness-day' ); ?></p>
					<ul class="aiad-bz-quiz__start-list">
						<li><?php esc_html_e( 'Match each definition to a buzzword', 'ai-awareness-day' ); ?></li>
						<li><?php esc_html_e( 'New random questions every time', 'ai-awareness-day' ); ?></li>
						<li><?php esc_html_e( 'See your score out of 5 with a review', 'ai-awareness-day' ); ?></li>
					</ul>
					<button type="button" class="aiad-bz-quiz__btn aiad-bz-quiz__btn--cta" data-aiad-bz-quiz-start>
						<?php esc_html_e( 'Start quiz', 'ai-awareness-day' ); ?>
					</button>
				</div>

				<div class="aiad-bz-quiz__play" data-aiad-bz-quiz-play hidden>
					<div class="aiad-bz-quiz__stepper" data-aiad-bz-quiz-stepper role="list" aria-label="<?php esc_attr_e( 'Quiz progress', 'ai-awareness-day' ); ?>"></div>
					<p class="aiad-bz-quiz__progress" data-aiad-bz-quiz-progress aria-live="polite"></p>
					<p class="aiad-bz-quiz__hint"><?php esc_html_e( 'Choose one answer, then tap the button below.', 'ai-awareness-day' ); ?></p>
					<div class="aiad-bz-quiz__panel" data-aiad-bz-quiz-panel></div>
					<div class="aiad-bz-quiz__actions">
						<button type="button" class="aiad-bz-quiz__btn aiad-bz-quiz__btn--cta" data-aiad-bz-quiz-next>
							<?php esc_html_e( 'Next question', 'ai-awareness-day' ); ?>
						</button>
					</div>
				</div>

				<div class="aiad-bz-quiz__results" data-aiad-bz-quiz-results hidden aria-live="polite"></div>
			</section>
		<?php endif; ?>
		<p class="aiad-bz-credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			·
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com' ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_buzzwords', 'aiad_buzzwords_shortcode' );

/**
 * Load assets in <head> when the shortcode is in post content.
 */
function aiad_maybe_enqueue_buzzwords_in_head(): void {
	if ( aiad_content_has_buzzwords_shortcode() ) {
		aiad_enqueue_buzzwords_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_buzzwords_in_head', 20 );

/**
 * Ensure scripts load if the shortcode rendered after wp_enqueue_scripts.
 */
function aiad_buzzwords_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_buzzwords_shortcode_rendered'] ) ) {
		aiad_enqueue_buzzwords_assets();
	}
}
add_action( 'wp_footer', 'aiad_buzzwords_footer_assets', 1 );

/**
 * Gutenberg body for the buzzwords timeline entry.
 */
function aiad_get_ai_buzzwords_timeline_content(): string {
	$contact_url = esc_url( home_url( '/contact/' ) );

	return '<!-- wp:paragraph -->
<p>If you teach in 2026, you have probably heard colleagues mention <em>agentic AI</em>, <em>vibe coding</em>, or <em>AI slop</em> — often in the same breath. These terms show up in staff briefings, vendor pitches, and student conversations. Knowing what they mean helps you guide classroom discussions with confidence.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Below is an interactive glossary of <strong>15 buzzwords</strong> that matter for educators right now. Filter by theme, check the hype meter, and expand any card for a plain-English definition plus a <strong>classroom angle</strong> you can use in lessons or policy debates. When you are ready, take the <strong>quick quiz</strong> at the end — five questions, scored out of 5.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_buzzwords hide_intro="1"]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>How to use this with students:</strong> pick three terms from different categories and ask learners to explain each in their own words, then compare with the card definitions. For a safeguarding angle, focus on <em>hallucination</em>, <em>AI slop</em>, and <em>human in the loop</em>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We will keep updating this list as the language shifts. If there is a term your school keeps hearing, <a href="' . $contact_url . '">let us know</a> and we may add it in a future revision.</p>
<!-- /wp:paragraph -->';
}

/**
 * Apply timeline meta for a manual buzzwords entry.
 */
function aiad_set_buzzwords_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'announcement' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create the buzzwords timeline entry if missing.
 *
 * @return int Post ID or 0.
 */
function aiad_create_buzzwords_timeline_entry(): int {
	$slug  = aiad_buzzwords_post_slug();
	$title = __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-awareness-day' );

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( 'An interactive glossary of the AI terms educators hear most in 2026 — from agentic AI to vibe coding — with classroom angles for every buzzword.', 'ai-awareness-day' ),
			'post_content' => aiad_get_ai_buzzwords_timeline_content(),
			'post_status'  => 'publish',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_buzzwords_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * Move the seeded blog post to the timeline CPT (existing live sites).
 */
function aiad_migrate_buzzwords_blog_to_timeline(): void {
	if ( get_option( 'aiad_ai_buzzwords_timeline_migrated' ) === 'yes' ) {
		return;
	}

	$slug = aiad_buzzwords_post_slug();

	$timeline = get_page_by_path( $slug, OBJECT, 'timeline' );
	$blog     = get_page_by_path( $slug, OBJECT, 'post' );

	if ( $timeline instanceof WP_Post && $blog instanceof WP_Post && (int) $timeline->ID !== (int) $blog->ID ) {
		wp_trash_post( (int) $blog->ID );
		update_option( 'aiad_ai_buzzwords_timeline_migrated', 'yes' );
		return;
	}

	if ( $blog instanceof WP_Post && ! $timeline ) {
		$updated = wp_update_post(
			array(
				'ID'        => (int) $blog->ID,
				'post_type' => 'timeline',
			),
			true
		);
		if ( $updated && ! is_wp_error( $updated ) ) {
			aiad_set_buzzwords_timeline_meta( (int) $blog->ID );
			update_option( 'aiad_ai_buzzwords_timeline_migrated', 'yes' );
			update_option( 'aiad_ai_buzzwords_timeline_seeded', 'yes' );
			set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
		}
		return;
	}

	if ( $timeline instanceof WP_Post ) {
		update_option( 'aiad_ai_buzzwords_timeline_migrated', 'yes' );
		return;
	}

	update_option( 'aiad_ai_buzzwords_timeline_migrated', 'yes' );
}
add_action( 'init', 'aiad_migrate_buzzwords_blog_to_timeline', 28 );

/**
 * One-time seed: buzzwords as a timeline entry (same URL pattern as resource updates).
 */
function aiad_seed_ai_buzzwords_timeline_entry(): void {
	if ( get_option( 'aiad_ai_buzzwords_timeline_seeded' ) === 'yes' ) {
		return;
	}

	$title = __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-awareness-day' );
	$slug  = aiad_buzzwords_post_slug();

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_ai_buzzwords_timeline_seeded', 'yes' );
		return;
	}

	if ( function_exists( 'aiad_get_post_by_title' ) && aiad_get_post_by_title( $title, 'timeline' ) ) {
		update_option( 'aiad_ai_buzzwords_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_buzzwords_timeline_entry() ) {
		update_option( 'aiad_ai_buzzwords_timeline_seeded', 'yes' );
		update_option( 'aiad_ai_buzzwords_blog_post_seeded', 'yes' );
	}
}
add_action( 'init', 'aiad_seed_ai_buzzwords_timeline_entry', 29 );

/**
 * 301 redirect from the old blog URL to /timeline/{slug}/.
 */
function aiad_redirect_legacy_buzzwords_blog_url(): void {
	if ( is_admin() ) {
		return;
	}

	$slug = aiad_buzzwords_post_slug();
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post || $post->post_name !== $slug ) {
		return;
	}

	$timeline = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( ! $timeline instanceof WP_Post ) {
		return;
	}

	wp_safe_redirect( get_permalink( $timeline ), 301 );
	exit;
}
add_action( 'template_redirect', 'aiad_redirect_legacy_buzzwords_blog_url' );

/**
 * 301 redirect from the old root-level URL after the blog duplicate is removed.
 */
function aiad_redirect_legacy_buzzwords_root_url(): void {
	if ( is_admin() ) {
		return;
	}

	$slug = aiad_buzzwords_post_slug();
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$path = trim( (string) wp_parse_url( $uri, PHP_URL_PATH ), '/' );

	if ( $path !== $slug || is_singular( 'timeline' ) ) {
		return;
	}

	$timeline = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $timeline instanceof WP_Post ) {
		wp_safe_redirect( get_permalink( $timeline ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'aiad_redirect_legacy_buzzwords_root_url', 0 );

/**
 * Flush permalinks once after buzzwords migration.
 */
function aiad_maybe_flush_rewrites_after_buzzwords_migration(): void {
	if ( ! get_transient( 'aiad_flush_rewrites' ) ) {
		return;
	}
	delete_transient( 'aiad_flush_rewrites' );
	flush_rewrite_rules( false );
}
add_action( 'init', 'aiad_maybe_flush_rewrites_after_buzzwords_migration', 99 );
