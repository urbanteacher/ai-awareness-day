<?php
/**
 * Archive template for AI Tools (free-tools/).
 *
 * Groups tools by tool_category, with a category filter bar at the top.
 *
 * @package AI_Awareness_Day
 */

get_header();

$categories   = get_terms( array(
	'taxonomy'   => 'tool_category',
	'hide_empty' => true,
	'orderby'    => 'name',
	'order'      => 'ASC',
) );

$active_cat   = isset( $_GET['category'] ) ? sanitize_text_field( wp_unslash( $_GET['category'] ) ) : '';
?>

<main id="main" role="main" class="tools-archive">

	<div class="tools-archive__hero">
		<div class="container">
			<span class="section-label"><?php esc_html_e( 'Free Tools', 'ai-awareness-day' ); ?></span>
			<h1 class="tools-archive__title"><?php esc_html_e( 'Free AI Tools for Teachers', 'ai-awareness-day' ); ?></h1>
			<p class="tools-archive__desc"><?php esc_html_e( 'No budget required — start using AI in your classroom today.', 'ai-awareness-day' ); ?></p>

			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
				<div class="tools-filter" role="group" aria-label="<?php esc_attr_e( 'Filter by category', 'ai-awareness-day' ); ?>">
					<a href="<?php echo esc_url( get_post_type_archive_link( 'ai_tool' ) ); ?>"
					   class="tools-filter__btn <?php echo empty( $active_cat ) ? 'tools-filter__btn--active' : ''; ?>">
						<?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
					</a>
					<?php foreach ( $categories as $cat ) : ?>
						<a href="<?php echo esc_url( add_query_arg( 'category', $cat->slug, get_post_type_archive_link( 'ai_tool' ) ) ); ?>"
						   class="tools-filter__btn <?php echo ( $active_cat === $cat->slug ) ? 'tools-filter__btn--active' : ''; ?>">
							<?php echo esc_html( $cat->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="container tools-archive__body">

		<?php
		// If a category filter is active, show just that category
		if ( $active_cat && ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			$filtered = array_filter( $categories, fn( $c ) => $c->slug === $active_cat );
			$display_cats = array_values( $filtered );
		} else {
			$display_cats = is_array( $categories ) && ! is_wp_error( $categories ) ? $categories : array();
		}

		foreach ( $display_cats as $cat ) :
			$tools = new WP_Query( array(
				'post_type'      => 'ai_tool',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'menu_order title',
				'order'          => 'ASC',
				'tax_query'      => array( array( // phpcs:ignore WordPress.DB.SlowDBQuery
					'taxonomy' => 'tool_category',
					'field'    => 'slug',
					'terms'    => $cat->slug,
				) ),
			) );

			if ( ! $tools->have_posts() ) {
				continue;
			}
			?>
			<div class="tools-group fade-up">
				<div class="tools-group__header">
					<h2 class="tools-group__title"><?php echo esc_html( $cat->name ); ?></h2>
					<span class="tools-group__count">
						<?php
						printf(
							/* translators: %d: number of tools */
							esc_html( _n( '%d free tool ready to use', '%d free tools ready to use', $tools->found_posts, 'ai-awareness-day' ) ),
							(int) $tools->found_posts
						);
						?>
					</span>
				</div>
				<div class="tools-grid">
					<?php while ( $tools->have_posts() ) : $tools->the_post(); ?>
						<?php echo aiad_render_tool_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
			</div>
			<?php
		endforeach;

		// If no categories yet, fall back to ungrouped grid
		if ( empty( $display_cats ) ) :
			$all_tools = new WP_Query( array(
				'post_type'      => 'ai_tool',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			) );
			if ( $all_tools->have_posts() ) :
				?>
				<div class="tools-grid">
					<?php while ( $all_tools->have_posts() ) : $all_tools->the_post(); ?>
						<?php echo aiad_render_tool_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>
				</div>
				<?php
			endif;
		endif;
		?>

	</div>
</main>

<?php get_footer(); ?>
