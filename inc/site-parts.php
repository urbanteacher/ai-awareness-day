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
 * Let a classic theme's template parts be edited in the Site Editor (WordPress 6.1+).
 */
function aiad_site_parts_support(): void {
	add_theme_support( 'block-template-parts' );
}
add_action( 'after_setup_theme', 'aiad_site_parts_support' );

/**
 * Register the header and footer blocks, and the ones the block templates use.
 */
function aiad_register_site_part_blocks(): void {
	foreach ( array( 'site-logo', 'site-navigation', 'breadcrumbs', 'footer-widgets', 'footer-links', 'footer-social', 'post-badge', 'post-navigation', 'comments', 'partners-directory' ) as $block ) {
		register_block_type( AIAD_DIR . '/blocks/' . $block );
	}
}
add_action( 'init', 'aiad_register_site_part_blocks' );

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
