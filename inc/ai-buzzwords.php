<?php
/**
 * AI Buzzwords interactive glossary shortcode.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current singular content uses the buzzwords shortcode.
 */
function aiad_content_has_buzzwords_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return has_shortcode( $post->post_content, 'aiad_buzzwords' );
}

/**
 * Enqueue buzzwords assets when the shortcode is present.
 */
function aiad_enqueue_buzzwords_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;

	$css_path = AIAD_DIR . '/assets/css/components/ai-buzzwords.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-buzzwords.js';

	wp_enqueue_style(
		'aiad-buzzwords',
		AIAD_URI . '/assets/css/components/ai-buzzwords.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_enqueue_script(
		'aiad-buzzwords',
		AIAD_URI . '/assets/js/ai-buzzwords.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}

/**
 * Shortcode: [aiad_buzzwords]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_buzzwords_shortcode( $atts = array() ): string {
	$atts = shortcode_atts(
		array(
			'title'       => __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-awareness-day' ),
			'description' => __( 'From "agentic AI" to "vibe coding" — the terms everyone\'s talking about, explained in plain English with classroom angles.', 'ai-awareness-day' ),
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_buzzwords'
	);

	aiad_enqueue_buzzwords_assets();

	ob_start();
	?>
	<div data-aiad-buzzwords class="aiad-buzzwords-widget" aria-label="<?php esc_attr_e( 'AI buzzwords glossary', 'ai-awareness-day' ); ?>">
		<div class="aiad-intro">
			<h2><?php echo esc_html( $atts['title'] ); ?></h2>
			<p><?php echo esc_html( $atts['description'] ); ?></p>
		</div>
		<div class="aiad-filters" data-aiad-bz-filters></div>
		<p class="aiad-count" data-aiad-bz-count></p>
		<div class="aiad-grid" data-aiad-bz-grid></div>
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
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_buzzwords_in_head' );

/**
 * Gutenberg post content for the seeded buzzwords blog post.
 */
function aiad_get_ai_buzzwords_blog_post_content(): string {
	$contact_url = esc_url( home_url( '/contact/' ) );

	return '<!-- wp:paragraph -->
<p>If you teach in 2026, you have probably heard colleagues mention <em>agentic AI</em>, <em>vibe coding</em>, or <em>AI slop</em> — often in the same breath. These terms show up in staff briefings, vendor pitches, and student conversations. Knowing what they mean helps you guide classroom discussions with confidence.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Below is an interactive glossary of <strong>15 buzzwords</strong> that matter for educators right now. Filter by theme, check the hype meter, and expand any card for a plain-English definition plus a <strong>classroom angle</strong> you can use in lessons or policy debates.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_buzzwords]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>How to use this with students:</strong> pick three terms from different categories and ask learners to explain each in their own words, then compare with the card definitions. For a safeguarding angle, focus on <em>hallucination</em>, <em>AI slop</em>, and <em>human in the loop</em>.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>We will keep updating this list as the language shifts. If there is a term your school keeps hearing, <a href="' . $contact_url . '">let us know</a> and we may add it in a future revision.</p>
<!-- /wp:paragraph -->';
}

/**
 * One-time seed: published blog post with the interactive buzzwords glossary.
 */
function aiad_seed_ai_buzzwords_blog_post(): void {
	if ( get_option( 'aiad_ai_buzzwords_blog_post_seeded' ) === 'yes' ) {
		return;
	}

	$title = __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-awareness-day' );

	if ( function_exists( 'aiad_get_post_by_title' ) && aiad_get_post_by_title( $title, 'post' ) ) {
		update_option( 'aiad_ai_buzzwords_blog_post_seeded', 'yes' );
		return;
	}

	$existing = get_page_by_path( '15-ai-buzzwords-teachers-2026', OBJECT, 'post' );
	if ( $existing instanceof WP_Post ) {
		update_option( 'aiad_ai_buzzwords_blog_post_seeded', 'yes' );
		return;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'post',
			'post_title'   => $title,
			'post_name'    => '15-ai-buzzwords-teachers-2026',
			'post_excerpt' => __( 'An interactive glossary of the AI terms educators hear most in 2026 — from agentic AI to vibe coding — with classroom angles for every buzzword.', 'ai-awareness-day' ),
			'post_content' => aiad_get_ai_buzzwords_blog_post_content(),
			'post_status'  => 'publish',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return;
	}

	update_option( 'aiad_ai_buzzwords_blog_post_seeded', 'yes' );
}
add_action( 'init', 'aiad_seed_ai_buzzwords_blog_post', 29 );
