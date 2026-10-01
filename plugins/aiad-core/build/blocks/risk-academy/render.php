<?php
/**
 * Front-end output for aiad/risk-academy.
 *
 * Calls the tool's render function, which [aiad_risk_academy] uses too, so the block and the shortcode always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_risk_academy_render' ) ) {
	return;
}

$aiad_core_tool = aiad_risk_academy_render(
	array(
		'hero'         => ! empty( $attributes['hero'] ) ? '1' : '0',
		'methodology'  => ! empty( $attributes['methodology'] ) ? '1' : '0',
		'meter'        => ! empty( $attributes['meter'] ) ? '1' : '0',
		'curriculum'   => ! empty( $attributes['curriculum'] ) ? '1' : '0',
		'contributors' => ! empty( $attributes['contributors'] ) ? '1' : '0',
		'resources'    => ! empty( $attributes['resources'] ) ? '1' : '0',
		'sources'      => ! empty( $attributes['sources'] ) ? '1' : '0',
		'enrol'        => ! empty( $attributes['enrol'] ) ? '1' : '0',
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
