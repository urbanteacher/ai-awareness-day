<?php
/**
 * LLM pipeline order game — rearrange steps into the correct sequence.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether post content includes the order game shortcode.
 */
function aiad_post_content_has_llm_order_game_shortcode( WP_Post $post ): bool {
	if ( has_shortcode( $post->post_content, 'aiad_llm_order_game' ) ) {
		return true;
	}
	return false !== strpos( $post->post_content, '[aiad_llm_order_game' );
}

/**
 * Whether the current singular view should load order game assets.
 */
function aiad_content_has_llm_order_game_shortcode(): bool {
	if ( ! is_singular() ) {
		return false;
	}
	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return false;
	}
	return aiad_post_content_has_llm_order_game_shortcode( $post );
}

/**
 * Register order game CSS/JS.
 */
function aiad_register_llm_order_game_assets(): void {
	$css_path = AIAD_DIR . '/assets/css/components/ai-llm-order-game.css';
	$js_path  = AIAD_DIR . '/assets/js/ai-llm-order-game.js';

	wp_register_style(
		'aiad-llm-order-game',
		AIAD_URI . '/assets/css/components/ai-llm-order-game.css',
		array(),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	wp_register_script(
		'aiad-llm-order-game',
		AIAD_URI . '/assets/js/ai-llm-order-game.js',
		array(),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_register_llm_order_game_assets', 5 );

/**
 * Enqueue order game assets.
 */
function aiad_enqueue_llm_order_game_assets(): void {
	static $enqueued = false;
	if ( $enqueued ) {
		return;
	}
	$enqueued = true;
	wp_enqueue_style( 'aiad-llm-order-game' );
	wp_enqueue_script( 'aiad-llm-order-game' );
}

/**
 * Shortcode: [aiad_llm_order_game]
 *
 * @param array<string, string>|string $atts Attributes.
 */
function aiad_llm_order_game_shortcode( $atts = array() ): string {
	$GLOBALS['aiad_llm_order_game_shortcode_rendered'] = true;

	$atts = shortcode_atts(
		array(
			'title' => __( 'Put the pipeline in order', 'ai-awareness-day' ),
			'intro' => __( 'Drag each definition into the correct step — or use the arrows on mobile. When you have finished the explainer above, this is a quick check that the sequence sticks.', 'ai-awareness-day' ),
		),
		is_array( $atts ) ? $atts : array(),
		'aiad_llm_order_game'
	);

	aiad_enqueue_llm_order_game_assets();

	ob_start();
	?>
	<section
		class="aiad-llm-order"
		data-aiad-llm-order
		aria-labelledby="aiad-llm-order-heading"
	>
		<h3 id="aiad-llm-order-heading" class="aiad-llm-order__heading"><?php echo esc_html( $atts['title'] ); ?></h3>
		<p class="aiad-llm-order__intro"><?php echo esc_html( $atts['intro'] ); ?></p>
		<div class="aiad-llm-order__panel">
			<p class="aiad-llm-order__hint"><?php esc_html_e( 'Tip: drag cards or tap ↑ ↓ to reorder. Position numbers show the slot (1 = first step in the pipeline).', 'ai-awareness-day' ); ?></p>
			<ol class="aiad-llm-order__list" data-aiad-llm-order-list></ol>
			<div class="aiad-llm-order__actions">
				<button type="button" class="aiad-llm-order__btn aiad-llm-order__btn--primary" data-aiad-llm-order-check>
					<?php esc_html_e( 'Check order', 'ai-awareness-day' ); ?>
				</button>
				<button type="button" class="aiad-llm-order__btn aiad-llm-order__btn--ghost" data-aiad-llm-order-shuffle>
					<?php esc_html_e( 'Shuffle again', 'ai-awareness-day' ); ?>
				</button>
				<button
					type="button"
					class="aiad-llm-order__btn aiad-llm-order__btn--ghost"
					data-aiad-llm-order-hint
					aria-expanded="false"
					aria-controls="aiad-llm-order-answer"
				>
					<?php esc_html_e( 'Show answer key', 'ai-awareness-day' ); ?>
				</button>
			</div>
			<p class="aiad-llm-order__feedback" data-aiad-llm-order-feedback hidden role="status" aria-live="polite"></p>
			<div id="aiad-llm-order-answer" class="aiad-llm-order__answer" data-aiad-llm-order-answer hidden></div>
		</div>
	</section>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'aiad_llm_order_game', 'aiad_llm_order_game_shortcode' );

/**
 * Load assets in head when the shortcode is in post content.
 */
function aiad_maybe_enqueue_llm_order_game_in_head(): void {
	if ( aiad_content_has_llm_order_game_shortcode() ) {
		aiad_enqueue_llm_order_game_assets();
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_maybe_enqueue_llm_order_game_in_head', 20 );

/**
 * Footer fallback when shortcode renders after wp_enqueue_scripts.
 */
function aiad_llm_order_game_footer_assets(): void {
	if ( ! empty( $GLOBALS['aiad_llm_order_game_shortcode_rendered'] ) ) {
		aiad_enqueue_llm_order_game_assets();
	}
}
add_action( 'wp_footer', 'aiad_llm_order_game_footer_assets', 1 );

/**
 * Append order game shortcode to existing LLM explainer timeline posts (one-time).
 */
function aiad_patch_llm_explainer_timeline_order_game(): void {
	if ( get_option( 'aiad_llm_explainer_order_game_patched' ) === 'yes' ) {
		return;
	}

	if ( ! function_exists( 'aiad_llm_explainer_post_slug' ) ) {
		return;
	}

	$post = get_page_by_path( aiad_llm_explainer_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		update_option( 'aiad_llm_explainer_order_game_patched', 'yes' );
		return;
	}

	if ( aiad_post_content_has_llm_order_game_shortcode( $post ) ) {
		update_option( 'aiad_llm_explainer_order_game_patched', 'yes' );
		return;
	}

	$block = "\n\n<!-- wp:shortcode -->\n[aiad_llm_order_game]\n<!-- /wp:shortcode -->\n";

	wp_update_post(
		array(
			'ID'           => (int) $post->ID,
			'post_content' => $post->post_content . $block,
		)
	);

	update_option( 'aiad_llm_explainer_order_game_patched', 'yes' );
}
add_action( 'init', 'aiad_patch_llm_explainer_timeline_order_game', 31 );
