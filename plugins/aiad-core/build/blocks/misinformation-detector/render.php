<?php
/**
 * Front-end output for aiad/misinformation-detector.
 *
 * Calls the tool's shortcode function, so the block and [aiad_misinformation_detector] always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_misinformation_detector_shortcode' ) ) {
	return;
}

$aiad_core_tool = aiad_misinformation_detector_shortcode(
	array(
		'hide_intro' => in_array( $attributes['hideIntro'] ?? 'auto', array( 'auto', '1', '0' ), true ) ? $attributes['hideIntro'] : 'auto',
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
