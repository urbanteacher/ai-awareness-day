<?php
/**
 * Title: Homepage: Free resources
 * Slug: aiad/homepage-free-resources
 * Categories: aiad-homepage
 * Keywords: homepage, resources, activities, section
 * Description: The Free resources section in blocks, so its heading is edited on the page. Replaces the Free resources section block (template-parts/front-page/section-free_resources.php).
 *
 * The heading is core blocks and starts with the site's wording. The tiles are the resource tiles block: the
 * resources are still picked in Appearance > Edit Homepage, and without any the section is hidden.
 *
 * @package AI_Awareness_Day
 */

$aiad_title = aiad_homepage_field_fallback( 'aiad_free_resources_title', __( 'Free Resources', 'ai-awareness-day' ) );
$aiad_desc  = aiad_homepage_field_fallback( 'aiad_free_resources_desc', __( 'Ready-to-use activities and materials for AI Awareness Day.', 'ai-awareness-day' ) );
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Free resources","patternName":"aiad/homepage-free-resources"},"className":"section","layout":{"type":"default"},"anchor":"free-resources"} -->
<section id="free-resources" class="wp-block-group section"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:group {"className":"fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'AI Awareness Activities', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php echo esc_html( $aiad_title ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo esc_html( $aiad_desc ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/resource-tiles /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
