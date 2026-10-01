<?php
/**
 * Front-end output for aiad/neu-ai-report.
 *
 * Calls the tool's render function, which [aiad_neu_ai_report] uses too, so the block and the shortcode always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_neu_ai_report_render' ) ) {
	return;
}

$aiad_core_tool = aiad_neu_ai_report_render(
	array(
		'headline' => in_array( $attributes['headline'] ?? 'auto', array( 'auto', '1', '0' ), true ) ? $attributes['headline'] : 'auto',
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
