<?php
/**
 * Principles grid: the cards' container from template-parts/front-page/section-principles.php, with the region
 * label a core group cannot carry.
 *
 * @package AI_Awareness_Day
 *
 * @var string $content The rendered principle cards.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}
?>
<div class="principles-grid" role="region" aria-label="<?php echo esc_attr__( 'Core principles — swipe sideways to explore each card', 'aiad-core' ); ?>">
<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered blocks. ?>
</div>
