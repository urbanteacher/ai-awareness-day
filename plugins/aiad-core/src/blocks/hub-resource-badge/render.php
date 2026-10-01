<?php
/**
 * Front-end output for aiad/hub-resource-badge: the audience badge on a benchmark hub page.
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

if ( ! function_exists( 'aiad_hub_resource_badge_label' ) ) {
	return; // The theme's hub page helpers (inc/hub-resource-page.php).
}

$aiad_hub_post = get_post( (int) ( $block->context['postId'] ?? 0 ) );
?>
<span class="single-timeline-entry__badge single-timeline-entry__badge--outline">
	<?php echo esc_html( aiad_hub_resource_badge_label( $aiad_hub_post instanceof WP_Post ? $aiad_hub_post : null ) ); ?>
</span>
