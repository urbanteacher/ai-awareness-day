<?php
/**
 * Front-end output for aiad/buzzwords.
 *
 * Calls the tool's shortcode function, so the block and [aiad_buzzwords] always produce the same markup; the function enqueues
 * the tool's CSS and JS itself.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_buzzwords_shortcode' ) ) {
	return;
}

$aiad_core_tool = aiad_buzzwords_shortcode(
	array(
		'hide_intro' => ! empty( $attributes['hideIntro'] ) ? '1' : '0',
		'quiz'       => ! empty( $attributes['quiz'] ) ? '1' : '0',
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_tool; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
