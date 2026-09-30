<?php
/**
 * Title: Homepage: Get involved
 * Slug: aiad/homepage-contact
 * Categories: aiad-homepage
 * Keywords: homepage, contact, get involved, form, section
 * Description: The Get Involved section in blocks, so its heading and text are edited on the page. Replaces the Get involved form section block (template-parts/front-page/section-contact.php).
 *
 * The heading is core blocks and starts with the site's wording (the Customizer's, or the standard wording); the form
 * is the Get Involved form block.
 *
 * @package AI_Awareness_Day
 */

$aiad_defaults = aiad_get_customizer_defaults();
$aiad_title    = aiad_homepage_field_fallback( 'aiad_contact_title', $aiad_defaults['aiad_contact_title'] );
$aiad_desc     = aiad_homepage_field_fallback( 'aiad_contact_desc', $aiad_defaults['aiad_contact_desc'] );
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Get involved","patternName":"aiad/homepage-contact"},"className":"section section--alt","layout":{"type":"default"},"anchor":"contact"} -->
<section id="contact" class="wp-block-group section section--alt"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:group {"className":"contact-wrapper","layout":{"type":"default"}} -->
<div class="wp-block-group contact-wrapper"><!-- wp:group {"className":"contact-info fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group contact-info fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Contact Us', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php echo esc_html( $aiad_title ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo wp_kses_post( $aiad_desc ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/contact-form /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
