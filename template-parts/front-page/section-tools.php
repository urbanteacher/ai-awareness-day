<?php
/**
 * Template Part: Free AI Tools front page section.
 *
 * A directory, not a showcase. It used to show the first three tools by menu
 * order, which were always the same three from Content Creation, under a label
 * saying there were 36. Now there is a chip for every category with its count,
 * each opening the archive filtered to it, and one row from each of the six
 * largest categories, so the section shows the range.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tool_cats = get_terms( array( 'taxonomy' => 'tool_category', 'hide_empty' => true ) );
if ( is_wp_error( $tool_cats ) ) {
	$tool_cats = array();
}
// Largest categories first; ties by name, so the order is stable between visits.
usort( $tool_cats, static function ( $a, $b ) {
	return ( $b->count <=> $a->count ) ?: strcasecmp( $a->name, $b->name );
} );

// One tool from each of the six largest categories: its first by menu order.
$tool_picks = array();
foreach ( array_slice( $tool_cats, 0, 6 ) as $tool_cat ) {
	$first = get_posts( array(
		'post_type'      => 'ai_tool',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'tax_query'      => array( array( 'taxonomy' => 'tool_category', 'field' => 'term_id', 'terms' => $tool_cat->term_id ) ), // phpcs:ignore WordPress.DB.SlowDBQuery
	) );
	if ( $first ) {
		$tool_picks[] = $first[0];
	}
}
// No categories yet: fall back to the first six tools.
if ( ! $tool_picks ) {
	$tool_picks = get_posts( array( 'post_type' => 'ai_tool', 'post_status' => 'publish', 'posts_per_page' => 6, 'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ) ) );
}
if ( ! $tool_picks ) {
	return;
}

$archive_url          = get_post_type_archive_link( 'ai_tool' );
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

		<?php if ( $tool_cats && $archive_url ) : ?>
			<nav class="tool-chips" aria-label="<?php esc_attr_e( 'AI tools by category', 'ai-awareness-day' ); ?>">
				<a class="tool-chip tool-chip--all" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'All', 'ai-awareness-day' ); ?> <span class="tool-chip__count"><?php echo esc_html( (string) $published_tools_count ); ?></span></a>
				<?php foreach ( $tool_cats as $tool_cat ) : ?>
					<a class="tool-chip" href="<?php echo esc_url( add_query_arg( 'category', $tool_cat->slug, $archive_url ) ); ?>"><?php echo esc_html( html_entity_decode( $tool_cat->name, ENT_QUOTES, 'UTF-8' ) ); ?> <span class="tool-chip__count"><?php echo esc_html( (string) $tool_cat->count ); ?></span></a>
				<?php endforeach; ?>
			</nav>
		<?php endif; ?>

		<ul class="tool-rows fade-up">
			<?php foreach ( $tool_picks as $tool_pick ) : ?>
				<?php echo aiad_render_tool_row( $tool_pick ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside the renderer. ?>
			<?php endforeach; ?>
		</ul>

		<?php if ( $archive_url ) : ?>
			<a class="tool-rows__more" href="<?php echo esc_url( $archive_url ); ?>">
				<?php
				/* translators: %d: number of published AI tools. */
				echo esc_html( sprintf( __( 'Browse all %d tools', 'ai-awareness-day' ), $published_tools_count ) );
				?>
				<span aria-hidden="true">&rarr;</span>
			</a>
		<?php endif; ?>
	</div>
</section>
