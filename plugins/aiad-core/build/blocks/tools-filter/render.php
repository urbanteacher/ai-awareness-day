<?php
/**
 * Front-end output for aiad/tools-filter: the category buttons on the AI tools archive (assets/js/tools-filter.js
 * finds them by their classes and data-filter).
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_categories = aiad_tools_archive_categories();
if ( empty( $aiad_categories ) ) {
	return;
}
?>
<div class="tools-filter" role="group" aria-label="<?php esc_attr_e( 'Filter by category', 'ai-awareness-day' ); ?>">
	<button class="tools-filter__btn tools-filter__btn--active" data-filter="all" aria-pressed="true" type="button">
		<?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
	</button>
	<?php foreach ( $aiad_categories as $aiad_cat ) : ?>
		<button class="tools-filter__btn" data-filter="<?php echo esc_attr( $aiad_cat->slug ); ?>" aria-pressed="false" type="button">
			<?php echo esc_html( $aiad_cat->name ); ?>
		</button>
	<?php endforeach; ?>
</div>
