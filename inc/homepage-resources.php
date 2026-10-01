<?php
/**
 * The homepage's free and featured resources sections in blocks: shared helpers for the section templates, their
 * patterns (patterns/homepage-free-resources.php, patterns/homepage-featured-resources.php) and the blocks those use
 * (blocks/resource-tiles, blocks/linkedin-card). The resources are picked on Settings → AI Awareness Day.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The free resources picked on Settings → AI Awareness Day, in the order picked; null when none are, which hides the
 * section.
 */
function aiad_free_resources_query(): ?WP_Query {
	$selected_ids = array();
	for ( $i = 1; $i <= 6; $i++ ) {
		$id = absint( aiad_site_value( 'free_resource_' . $i, 0 ) );
		if ( $id > 0 ) {
			$selected_ids[] = $id;
		}
	}
	if ( empty( $selected_ids ) ) {
		return null;
	}
	return new WP_Query(
		array(
			'post_type'      => 'resource',
			'post_status'    => 'publish',
			'posts_per_page' => 6,
			'post__in'       => $selected_ids,
			'orderby'        => 'post__in',
		)
	);
}

/**
 * The featured (partner) resources picked on Settings → AI Awareness Day, in the order picked, or else the first three.
 */
function aiad_featured_resources_query(): WP_Query {
	$selected_ids = array();
	for ( $i = 1; $i <= 3; $i++ ) {
		$id = absint( aiad_site_value( 'handpicked_resource_' . $i, 0 ) );
		if ( $id > 0 ) {
			$selected_ids[] = $id;
		}
	}
	if ( ! empty( $selected_ids ) ) {
		return new WP_Query(
			array(
				'post_type'      => 'featured_resource',
				'post_status'    => 'publish',
				'posts_per_page' => 3,
				'post__in'       => $selected_ids,
				'orderby'        => 'post__in',
			)
		);
	}
	return new WP_Query(
		array(
			'post_type'      => 'featured_resource',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
		)
	);
}

/**
 * Register the blocks the resources patterns use.
 */
function aiad_register_homepage_resource_blocks(): void {
	register_block_type( AIAD_DIR . '/blocks/resource-tiles' );
	register_block_type( AIAD_DIR . '/blocks/linkedin-card' );
}
add_action( 'init', 'aiad_register_homepage_resource_blocks' );

/**
 * The free and featured resources sections in blocks: like the templates, show the section only when it has
 * resources to show (its resource tiles block printed some).
 *
 * @param string $block_content The section's HTML.
 * @param array  $block         The parsed block.
 */
function aiad_homepage_resources_section_visibility( string $block_content, array $block ): string {
	$pattern = $block['attrs']['metadata']['patternName'] ?? '';
	if ( ! in_array( $pattern, array( 'aiad/homepage-free-resources', 'aiad/homepage-featured-resources' ), true ) ) {
		return $block_content;
	}
	return str_contains( $block_content, 'class="resource-tiles"' ) ? $block_content : '';
}
add_filter( 'render_block_core/group', 'aiad_homepage_resources_section_visibility', 10, 2 );
