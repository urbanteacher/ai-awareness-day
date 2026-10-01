<?php
/**
 * Front-end output for aiad/national-survey.
 *
 * Calls the tool's render function, which [aiad_national_survey] uses too, so the block and the shortcode always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_national_survey_render' ) ) {
	return;
}

$aiad_core_tool = aiad_national_survey_render( array() );
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
