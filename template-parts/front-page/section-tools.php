<?php
/**
 * Template Part: Free AI Tools front page section.
 *
 * Shows up to 6 tools in a grid with a "View all" CTA.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tools_query = new WP_Query( array(
	'post_type'      => 'ai_tool',
	'post_status'    => 'publish',
	'posts_per_page' => 6,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
) );

if ( ! $tools_query->have_posts() ) {
	return;
}

$archive_url = get_post_type_archive_link( 'ai_tool' );
$text_alignment_class = aiad_get_text_alignment_class();
?>

<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="free-tools">
	<div class="container">
		<div class="fade-up">
			<span class="section-label"><?php esc_html_e( 'Free Tools', 'ai-awareness-day' ); ?></span>
			<h2 class="section-title"><?php esc_html_e( 'Start using AI in your classroom today', 'ai-awareness-day' ); ?></h2>
			<p class="section-desc"><?php esc_html_e( 'No budget required — our curated collection of free AI tools designed for educators.', 'ai-awareness-day' ); ?></p>
		</div>

		<div class="tools-grid">
			<?php while ( $tools_query->have_posts() ) : $tools_query->the_post(); ?>
				<?php echo aiad_render_tool_card( get_post() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped inside renderer ?>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>

		<?php if ( $archive_url ) : ?>
			<div class="tools-section__cta fade-up">
				<a href="<?php echo esc_url( $archive_url ); ?>" class="btn-action">
					<?php esc_html_e( 'View all free tools', 'ai-awareness-day' ); ?>
					<span class="btn-action__icon" aria-hidden="true">→</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
