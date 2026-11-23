<?php
/**
 * Resource tiles block: the tiles the homepage's free or featured resources section prints
 * (template-parts/components/free-resource-tiles.php, featured-resource-tiles.php). Nothing without resources, and
 * then aiad_homepage_resources_section_visibility() hides the section around it.
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_free_resources_query', 'aiad_featured_resources_query', 'aiad_homepage_resources_section_visibility' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

// Discussion: three discussion starters from the library (newest first), for pages outside the homepage such as the
// National Conversation. The tiles are the library's own card, and the link opens the library on discussions.
if ( 'discussion' === ( $attributes['source'] ?? 'free' ) ) {
	$aiad_query = new WP_Query(
		array(
			'post_type'           => 'resource',
			'post_status'         => 'publish',
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'tax_query'           => array( array( 'taxonomy' => 'activity_type', 'field' => 'slug', 'terms' => 'discussion' ) ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);
	if ( ! $aiad_query->have_posts() ) {
		return;
	}
	$aiad_library = get_post_type_archive_link( 'resource' ) ?: home_url( '/resources/' );
	echo '<div class="resource-tiles">';
	while ( $aiad_query->have_posts() ) {
		$aiad_query->the_post();
		get_template_part( 'template-parts/components/resource-tile', null, array() );
	}
	echo '</div>';
	printf(
		'<a class="resource-tiles__more" href="%1$s">%2$s <span aria-hidden="true">&rarr;</span></a>',
		esc_url( add_query_arg( 'activity_type', 'discussion', $aiad_library ) ),
		esc_html__( 'See all discussion starters', 'aiad-core' )
	);
	wp_reset_postdata();
	return;
}

$aiad_featured = 'featured' === ( $attributes['source'] ?? 'free' );
$aiad_query    = $aiad_featured ? aiad_featured_resources_query() : aiad_free_resources_query();
if ( ! $aiad_query || ! $aiad_query->have_posts() ) {
	return;
}
get_template_part( 'template-parts/components/' . ( $aiad_featured ? 'featured' : 'free' ) . '-resource-tiles', null, array( 'query' => $aiad_query ) );
wp_reset_postdata();
