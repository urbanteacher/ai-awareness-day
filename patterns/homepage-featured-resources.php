<?php
/**
 * Title: Homepage: Featured resources
 * Slug: aiad/homepage-featured-resources
 * Categories: aiad-homepage
 * Keywords: homepage, resources, partners, linkedin, section
 * Description: The Featured partner resources section in blocks, so its heading is edited on the page. Replaces the Featured partner resources section block (template-parts/front-page/section-featured_resources.php).
 *
 * The heading is core blocks and starts with the site's wording. The tiles are the resource tiles block (picked in
 * Appearance > Edit Homepage, else the first three), and the LinkedIn card after the section is its own block.
 *
 * @package AI_Awareness_Day
 */

$aiad_title    = aiad_homepage_field_fallback( 'aiad_handpicked_resources_title', __( 'Handpicked Quality Resources', 'ai-awareness-day' ) );
$aiad_desc     = aiad_homepage_field_fallback( 'aiad_handpicked_resources_desc', __( 'A curated selection of interactive AI games and learning tools from trusted organisations.', 'ai-awareness-day' ) );
$aiad_linkedin = esc_url_raw( aiad_homepage_field_fallback( 'aiad_linkedin_post_url', '' ) );
$aiad_card     = '' !== $aiad_linkedin ? serialize_block_attributes( array( 'url' => $aiad_linkedin ) ) . ' ' : '';
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Featured resources","patternName":"aiad/homepage-featured-resources"},"className":"section section--alt","layout":{"type":"default"},"anchor":"partner-resources"} -->
<section id="partner-resources" class="wp-block-group section section--alt"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:group {"className":"fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Extra Resources', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php echo esc_html( $aiad_title ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo esc_html( $aiad_desc ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/resource-tiles {"source":"featured"} /--></div>
<!-- /wp:group --></section>
<!-- /wp:group -->

<!-- wp:aiad/linkedin-card <?php echo $aiad_card; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialised block attributes. ?>/-->
