<?php
/**
 * AI Awareness Day blocks: category and registration.
 *
 * Blocks are written in src/blocks/<name>/ and compiled by `npm run build` into build/blocks/<name>/. The build folder
 * is committed, because deploys copy the plugin as it is and run no build step.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Put the site's blocks in their own inserter category.
 *
 * @param array<int, array<string, mixed>> $categories Existing categories.
 * @return array<int, array<string, mixed>>
 */
function aiad_core_block_categories( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'aiad',
			'title' => __( 'AI Awareness Day', 'aiad-core' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'aiad_core_block_categories' );

/**
 * Register every compiled block in build/blocks/.
 */
function aiad_core_register_blocks(): void {
	$block_files = glob( AIAD_CORE_DIR . 'build/blocks/*/block.json' );
	if ( ! $block_files ) {
		return;
	}
	foreach ( $block_files as $block_file ) {
		register_block_type( dirname( $block_file ) );
	}
}
add_action( 'init', 'aiad_core_register_blocks' );
