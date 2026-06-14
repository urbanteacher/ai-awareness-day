<?php
/**
 * Template for AIRB benchmark hub / intervention resource pages.
 *
 * Matches the stacked timeline single layout: badge, title, excerpt, body.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-timeline">
	<?php
	while ( have_posts() ) :
		the_post();
		$excerpt = has_excerpt() ? get_the_excerpt() : '';
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-timeline-entry single-timeline-entry--stacked single-timeline-entry--hub' ); ?>>
			<div class="single-timeline-entry__container">

				<span class="single-timeline-entry__badge single-timeline-entry__badge--outline">
					<?php echo esc_html( aiad_hub_resource_badge_label() ); ?>
				</span>

				<h1 class="single-timeline-entry__title"><?php the_title(); ?></h1>

				<?php if ( '' !== trim( $excerpt ) ) : ?>
					<p class="single-timeline-entry__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<div class="single-timeline-entry__content entry-content entry-content--timeline">
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

				<?php do_action( 'airb_after_hub_resource_content' ); ?>

				<div class="single-timeline-entry__footer">
					<a href="<?php echo esc_url( aiad_hub_resource_back_url() ); ?>" class="single-timeline-entry__back">
						<?php if ( function_exists( 'aiad_back_icon_svg' ) ) : ?>
							<span aria-hidden="true"><?php echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php esc_html_e( 'Back to the AI Risk & Readiness Benchmark', 'ai-awareness-day' ); ?>
					</a>
				</div>

			</div>
		</article>
		<?php
	endwhile;
	?>
</main>

<?php
get_footer();
