<?php
/**
 * The header and footer as block template parts (parts/header.html, parts/footer.html), edited in Appearance >
 * Editor. header.php and footer.php print them with block_template_part(); the blocks in blocks/site-* and
 * blocks/footer-* print the pieces that come from settings, menus and widgets, with the markup the templates had.
 * Stage 2 of docs/BLOCK-THEME-MIGRATION.md.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Take back two things a block theme gets from core.
 */
function aiad_site_parts_support(): void {
	// WordPress gives a block theme core's opinionated block styles (a quote's left border and padding, padding on a
	// group with a background, and so on). The theme styles blocks itself.
	remove_theme_support( 'wp-block-styles' );
	// In a block theme, core gives the comment form's submit button the block button classes, and with them core's
	// default button colours. comments.php styles its own form.
	remove_filter( 'comment_form_defaults', 'comments_block_form_defaults' ); // wp-includes/blocks/comments.php
}
add_action( 'after_setup_theme', 'aiad_site_parts_support' );

/*
 * The header and footer blocks, and the ones the block templates use, are built blocks in aiad-core (src/blocks).
 */

/**
 * The tags every page's <head> starts with, printed by wp_head() so that block templates (template-canvas.php) get
 * them too, not only header.php: the meta description, the browser theme colour, the web-app tags, and the script
 * that swaps the <html> element's no-js class for js.
 */
function aiad_site_head_tags(): void {
	if ( ! function_exists( 'aiad_seo_should_output' ) || aiad_seo_should_output() ) {
		$og_data     = function_exists( 'aiad_get_og_data' ) ? aiad_get_og_data() : null;
		$description = $og_data && isset( $og_data['description'] ) ? $og_data['description'] : get_bloginfo( 'description' );
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo '<meta name="theme-color" content="#00BEDD">' . "\n";
	echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
	echo '<meta name="apple-mobile-web-app-status-bar-style" content="default">' . "\n";
	echo "<script>document.documentElement.className = document.documentElement.className.replace('no-js', 'js');</script>\n";
}
add_action( 'wp_head', 'aiad_site_head_tags', 0 );

/**
 * Give the site's pages <html class="no-js"> (the script in aiad_site_head_tags() makes it js), in header.php and in
 * block templates alike. Only for the theme's own pages, not the admin or the login screen.
 *
 * @param string $output The lang and dir attributes.
 */
function aiad_html_no_js_class( string $output ): string {
	return $output . ' class="no-js"';
}
add_action(
	'template_redirect',
	static function (): void {
		add_filter( 'language_attributes', 'aiad_html_no_js_class' );
	}
);

/**
 * As a block theme, WordPress chooses a block template first; some pages then swap in a PHP template of their own
 * (National Conversation, Walkthrough, the benchmark's hub pages: template_include). For any page that ends up on a
 * PHP template, keep the head as header.php makes it:
 * - core queued its viewport tag for the block template, and header.php prints one already;
 * - the header and footer template parts render after wp_head(), so their blocks' small stylesheets would print at
 *   the end of the page; queue them now so they print in the head, as they do in a hybrid theme.
 *
 * @param string $template The template file WordPress will load.
 */
function aiad_php_template_in_block_theme( string $template ): string {
	$canvas = wp_normalize_path( ABSPATH . WPINC . '/template-canvas.php' );
	if ( ! wp_is_block_theme() || wp_normalize_path( $template ) === $canvas ) {
		return $template;
	}
	remove_action( 'wp_head', '_block_template_viewport_meta_tag', 0 );
	add_action(
		'wp_enqueue_scripts',
		static function (): void {
			foreach ( array( 'wp-block-group', 'wp-block-paragraph' ) as $handle ) {
				wp_enqueue_style( $handle );
			}
		}
	);
	return $template;
}
add_filter( 'template_include', 'aiad_php_template_in_block_theme', PHP_INT_MAX );
