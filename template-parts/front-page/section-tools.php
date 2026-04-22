<?php
/**
 * Template Part: Free AI Tools front page section.
 *
 * Shows up to 4 tools in a grid with a "View all" CTA.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tools_query = new WP_Query( array(
	'post_type'      => 'ai_tool',
	'post_status'    => 'publish',
	'posts_per_page' => 4,
	'orderby'        => 'menu_order date',
	'order'          => 'ASC',
) );

if ( ! $tools_query->have_posts() ) {
	return;
}

$archive_url = get_post_type_archive_link( 'ai_tool' );
$text_alignment_class = aiad_get_text_alignment_class();
$published_tools_count = (int) ( wp_count_posts( 'ai_tool' )->publish ?? 0 );
$tools_label = sprintf(
	/* translators: %d: number of published AI tools. */
	__( 'AI Tools (%d)', 'ai-awareness-day' ),
	$published_tools_count
);
?>

<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="ai-tools">
	<div class="container">
		<div class="fade-up">
			<span class="section-label"><?php echo esc_html( $tools_label ); ?></span>
			<h2 class="section-title"><?php esc_html_e( 'Start using AI in your classroom today', 'ai-awareness-day' ); ?></h2>
			<p class="section-desc"><?php esc_html_e( 'Our curated collection of trending AI tools designed to enhance your lessons.', 'ai-awareness-day' ); ?></p>
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
					<?php esc_html_e( 'View all tools', 'ai-awareness-day' ); ?>
					<span class="btn-action__icon" aria-hidden="true">→</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</section>
