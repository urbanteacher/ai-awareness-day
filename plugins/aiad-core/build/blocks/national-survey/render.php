<?php
/**
 * Front-end output for aiad/national-survey.
 *
 * Calls the tool's shortcode function, so the block and [aiad_national_survey] always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_national_survey_shortcode' ) ) {
	return;
}

$aiad_core_tool = aiad_national_survey_shortcode( array() );
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
