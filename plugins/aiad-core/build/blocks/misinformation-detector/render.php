<?php
/**
 * Front-end output for aiad/misinformation-detector.
 *
 * Calls the tool's render function, which [aiad_misinformation_detector] uses too, so the block and the shortcode always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_misinformation_detector_render' ) ) {
	return;
}

$aiad_core_tool = aiad_misinformation_detector_render(
	array(
		'hide_intro' => in_array( $attributes['hideIntro'] ?? 'auto', array( 'auto', '1', '0' ), true ) ? $attributes['hideIntro'] : 'auto',
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
