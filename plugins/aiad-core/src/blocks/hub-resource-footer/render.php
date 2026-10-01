<?php
/**
 * Front-end output for aiad/hub-resource-footer: what the benchmark plugin adds after a hub page's content (its
 * improvement form, on airb_after_hub_resource_content), then the link back to the benchmark.
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

do_action( 'airb_after_hub_resource_content' );

$aiad_back_url = function_exists( 'aiad_hub_resource_back_url' ) ? aiad_hub_resource_back_url() : home_url( '/' );
?>
<div class="single-timeline-entry__footer">
	<a href="<?php echo esc_url( $aiad_back_url ); ?>" class="single-timeline-entry__back">
		<?php if ( function_exists( 'aiad_back_icon_svg' ) ) : ?>
			<span aria-hidden="true"><?php echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		<?php endif; ?>
		<?php esc_html_e( 'Back to the AI Risk & Readiness Benchmark', 'ai-awareness-day' ); ?>
	</a>
</div>
