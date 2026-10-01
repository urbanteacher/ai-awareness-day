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

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$aiad_featured = 'featured' === ( $attributes['source'] ?? 'free' );
$aiad_query    = $aiad_featured ? aiad_featured_resources_query() : aiad_free_resources_query();
if ( ! $aiad_query || ! $aiad_query->have_posts() ) {
	return;
}
get_template_part( 'template-parts/components/' . ( $aiad_featured ? 'featured' : 'free' ) . '-resource-tiles', null, array( 'query' => $aiad_query ) );
wp_reset_postdata();
