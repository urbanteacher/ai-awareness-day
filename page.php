<?php
/**
 * The template for displaying pages.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="page-default section">
	<div class="container page-default__inner">

		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="section-title page-default__title"><?php the_title(); ?></h1>
				<div class="entry-content">
					<?php
					the_content();
					wp_link_pages(
						array(
							'before' => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'ai-awareness-day' ) . '">' . esc_html__( 'Pages:', 'ai-awareness-day' ),
							'after'  => '</nav>',
						)
					);
					?>
				</div>
			</article>
			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
		endwhile;
		?>

	</div>
</main>

<?php get_footer(); ?>
