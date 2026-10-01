<?php
/**
 * Front-end output for aiad/hub-resource-excerpt: the page's own excerpt, if it has one. Core's Excerpt block makes
 * one up from the content when there is none, which a hub page has never shown.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var string               $content    Block content.
 * @var WP_Block             $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_hub_id = (int) ( $block->context['postId'] ?? 0 );
if ( ! $aiad_hub_id || ! has_excerpt( $aiad_hub_id ) ) {
	return;
}

$aiad_hub_excerpt = trim( (string) get_the_excerpt( $aiad_hub_id ) );
if ( '' === $aiad_hub_excerpt ) {
	return;
}
?>
<p class="single-timeline-entry__excerpt"><?php echo esc_html( $aiad_hub_excerpt ); ?></p>
