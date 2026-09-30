<?php
/**
 * Front-end output for aiad/speed-quiz.
 *
 * Calls the existing shortcode function, so the block and [aiad_speed_quiz] always produce the same markup, and the
 * function enqueues the quiz's CSS and JS itself. The function lives in the theme (inc/ai-speed-quiz.php) until that
 * module moves into this plugin.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_speed_quiz_shortcode' ) ) {
	return;
}

$aiad_core_quiz = aiad_speed_quiz_shortcode(
	array(
		'questions' => (string) absint( $attributes['questions'] ?? 10 ),
		'seconds'   => (string) absint( $attributes['seconds'] ?? 15 ),
		'bonus'     => (string) absint( $attributes['bonus'] ?? 10 ),
		'points'    => (string) absint( $attributes['points'] ?? 100 ),
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_quiz; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the shortcode function. ?>
</div>
