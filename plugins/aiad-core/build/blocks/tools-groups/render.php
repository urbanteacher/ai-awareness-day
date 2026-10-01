<?php
/**
 * Front-end output for aiad/tools-groups: the AI tools archive's tools, one group per category with a count, or
 * a single list while no tool has a category. The groups carry the data-category the filter buttons hide and show.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_categories = aiad_tools_archive_categories();

foreach ( $aiad_categories as $aiad_cat ) :
	$aiad_tools = new WP_Query(
		array(
			'post_type'      => 'ai_tool',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order title',
			'order'          => 'ASC',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array(
					'taxonomy' => 'tool_category',
					'field'    => 'slug',
					'terms'    => $aiad_cat->slug,
				),
			),
		)
	);
	if ( ! $aiad_tools->have_posts() ) {
		continue;
	}
	?>
	<div class="tools-group fade-up" data-category="<?php echo esc_attr( $aiad_cat->slug ); ?>">
		<div class="tools-group__header">
			<h2 class="tools-group__title"><?php echo esc_html( $aiad_cat->name ); ?></h2>
			<span class="tools-group__count">
				<?php
				printf(
					/* translators: %d: number of tools */
					esc_html( _n( '%d tool', '%d tools', $aiad_tools->found_posts, 'ai-awareness-day' ) ),
					(int) $aiad_tools->found_posts
				);
				?>
			</span>
		</div>
		<ul class="tool-rows tool-rows--archive">
			<?php while ( $aiad_tools->have_posts() ) : $aiad_tools->the_post(); ?>
				<?php // The group heading names the category, so the rows don't repeat it. ?>
				<?php echo aiad_render_tool_row( get_post(), array( 'show_category' => false, 'show_features' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
	</div>
	<?php
endforeach;

// No categories yet: one list of every tool.
if ( empty( $aiad_categories ) ) :
	$aiad_all_tools = new WP_Query(
		array(
			'post_type'      => 'ai_tool',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		)
	);
	if ( $aiad_all_tools->have_posts() ) :
		?>
		<ul class="tool-rows tool-rows--archive">
			<?php while ( $aiad_all_tools->have_posts() ) : $aiad_all_tools->the_post(); ?>
				<?php echo aiad_render_tool_row( get_post(), array( 'show_features' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</ul>
		<?php
	endif;
endif;
