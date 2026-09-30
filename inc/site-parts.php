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
 * Register the header and footer blocks.
 */
function aiad_register_site_part_blocks(): void {
	foreach ( array( 'site-logo', 'site-navigation', 'footer-widgets', 'footer-links', 'footer-social' ) as $block ) {
		register_block_type( AIAD_DIR . '/blocks/' . $block );
	}
}
add_action( 'init', 'aiad_register_site_part_blocks' );
