<?php
/**
 * How LLMs work — 6-step interactive explainer shortcode and timeline seed.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the LLM explainer timeline entry.
 */
function aiad_llm_explainer_post_slug(): string {
	return 'how-does-a-large-language-model-work';
}

/**
 * Whether post content includes the LLM explainer shortcode.
 */
function aiad_post_content_has_llm_explainer_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_llm_explainer' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_llm_explainer' );
}

/**
 * Whether the current singular view should load LLM explainer assets.
 */
function aiad_content_has_llm_explainer_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_llm_explainer_shortcode( $post );
}

/**
 * Register LLM explainer CSS/JS.
 */
function aiad_register_llm_explainer_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-llm-explainer.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-llm-explainer.js';

	wp_register_style(
		'aiad-llm-explainer',
		AIAD_URI . '/assets/css/components/ai-llm-explainer.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-llm-explainer',
		AIAD_URI . '/assets/js/ai-llm-explainer.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_llm_explainer_assets', 5 );

/**
 * Enqueue LLM explainer assets.
 */
function aiad_enqueue_llm_explainer_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-llm-explainer' );
	wp_enqueue_script( 'aiad-llm-explainer' );
}

/**
 * Shortcode: [aiad_llm_explainer]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_llm_explainer_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_llm_explainer_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'title'       => __( 'How does a large language model work?', 'ai-awareness-day' ),
			'description' => __( 'A 6-step interactive explainer — no jargon, just clear analogies designed for teachers.', 'ai-awareness-day' ),
			'hide_intro'  => '0',
			'explore_url' => '',
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_llm_explainer'
	);

	aiad_enqueue_llm_explainer_assets();

	$hide_intro = in_array( strtolower( (string) $atts['hide_intro'] ), array( '1', 'true', 'yes' ), true );
	$more_url   = $atts['explore_url'] ? esc_url( $atts['explore_url'] ) : esc_url( home_url( '/' ) );
	$host       = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.com';

	ob_start();
	?>
	<div
		id="aiad-llm"
		data-aiad-llm
		class="aiad-llm-explainer"
		data-more-url="<?php echo esc_attr( $more_url ); ?>"
		aria-label="<?php esc_attr_e( 'How large language models work — interactive explainer', 'ai-awareness-day' ); ?>"
	>
		<?php if ( ! $hide_intro ) : ?>
			<div class="llm-header">
				<h2><?php echo esc_html( $atts['title'] ); ?></h2>
				<p><?php echo esc_html( $atts['description'] ); ?></p>
			</div>
		<?php endif; ?>
		<div class="step-nav" data-aiad-llm-nav role="navigation" aria-label="<?php esc_attr_e( 'Explainer steps', 'ai-awareness-day' ); ?>"></div>
		<div class="step-panel" data-aiad-llm-panel></div>
		<p class="aiad-llm-credit">
			<?php esc_html_e( 'Produced by', 'ai-awareness-day' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( (string) $host ); ?></a>
		</p>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_llm_explainer', 'aiad_llm_explainer_shortcode' );

/**
 * Load assets in head when the shortcode is in post content.
 */
function aiad_maybe_enqueue_llm_explainer_in_head(): void {
	if ( aiad_content_has_llm_explainer_shortcode() ) {
		aiad_enqueue_llm_explainer_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_llm_explainer_in_head', 20 );

/**
 * Footer fallback when shortcode renders after wp_enqueue_scripts.
 */
function aiad_llm_explainer_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_llm_explainer_shortcode_rendered'] ) ) {
		aiad_enqueue_llm_explainer_assets();
	}
}
add_action( 'wp_footer', 'aiad_llm_explainer_footer_assets', 1 );

/**
 * Gutenberg body for the LLM explainer timeline entry.
 */
function aiad_get_llm_explainer_timeline_content(): string {
	$buzzwords_url = esc_url( home_url( '/timeline/' . aiad_buzzwords_post_slug() . '/' ) );
	$contact_url   = esc_url( home_url( '/contact/' ) );

	return '<!-- wp:paragraph -->
<p>Large language models power ChatGPT, Copilot, Gemini, and the tools your students may already be using. But what actually happens when you type a prompt? This explainer walks through the process in <strong>six short steps</strong> — each with a classroom analogy and a simple interactive visual.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>No calculus, no code — just enough intuition to explain AI confidently in a staff meeting or a Year 9 lesson. If you have not yet explored our <a href="' . $buzzwords_url . '">15 AI buzzwords glossary</a>, that is a great companion piece for the language side of the same conversation.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[aiad_llm_explainer hide_intro="1"]
<!-- /wp:shortcode -->

<!-- wp:shortcode -->
[aiad_llm_order_game]
<!-- /wp:shortcode -->

<!-- wp:paragraph -->
<p><strong>How to use this with students:</strong> work through one step per lesson starter, or assign pairs to explain step 3 (attention) to the class using their own example sentence. After the explainer, challenge pairs to <strong>sort the pipeline</strong> in the game below before you reveal the answer key. Ask: <em>where could this model still get things wrong?</em> — that leads naturally into hallucinations and human-in-the-loop from the buzzwords list.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>These visuals are simplified teaching models, not live AI systems. If you spot something we should clarify, <a href="' . $contact_url . '">get in touch</a>.</p>
<!-- /wp:paragraph -->';
}

/**
 * Apply timeline meta for the LLM explainer entry.
 */
function aiad_set_llm_explainer_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'announcement' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create the LLM explainer timeline entry if missing.
 *
 * @return int Post ID or 0.
 */
function aiad_create_llm_explainer_timeline_entry(): int {
	$slug  = aiad_llm_explainer_post_slug();
	$title = __( 'How Does a Large Language Model Work?', 'ai-awareness-day' );

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => __( 'A 6-step interactive explainer for teachers — tokens, attention, layers, prediction, and training, with classroom analogies and no jargon.', 'ai-awareness-day' ),
			'post_content' => aiad_get_llm_explainer_timeline_content(),
			'post_status'  => 'publish',
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_llm_explainer_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * One-time seed: LLM explainer timeline entry.
 */
function aiad_seed_llm_explainer_timeline_entry(): void {
	if ( get_option( 'aiad_llm_explainer_timeline_seeded' ) === 'yes' ) {
		return;
	}

	$slug  = aiad_llm_explainer_post_slug();
	$title = __( 'How Does a Large Language Model Work?', 'ai-awareness-day' );

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_llm_explainer_timeline_seeded', 'yes' );
		return;
	}

	if ( function_exists( 'aiad_get_post_by_title' ) && aiad_get_post_by_title( $title, 'timeline' ) ) {
		update_option( 'aiad_llm_explainer_timeline_seeded', 'yes' );
		return;
	}

	if ( aiad_create_llm_explainer_timeline_entry() ) {
		update_option( 'aiad_llm_explainer_timeline_seeded', 'yes' );
	}
}
add_action( 'init', 'aiad_seed_llm_explainer_timeline_entry', 30 );
