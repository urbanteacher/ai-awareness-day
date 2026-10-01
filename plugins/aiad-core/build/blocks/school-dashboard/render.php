<?php
/**
 * Front-end output for aiad/school-dashboard.
 *
 * Calls the benchmark plugin's own shortcode method, so the block and [ai_risk_school_dashboard] always produce the same markup; the
 * method enqueues the benchmark's CSS and JS itself. The benchmark stays in its own bundled plugin.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AIRB_School_Dashboard' ) || ! method_exists( 'AIRB_School_Dashboard', 'render' ) ) {
	return; // No benchmark plugin: nothing to show, as with the shortcode.
}

$aiad_core_benchmark = AIRB_School_Dashboard::render(
	array(
		'school' => (string) ( $attributes['school'] ?? '' ),
	)
);
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_benchmark; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped by the benchmark plugin's templates. ?>
</div>
