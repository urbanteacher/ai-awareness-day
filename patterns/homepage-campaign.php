<?php
/**
 * Title: Homepage: Campaign
 * Slug: aiad/homepage-campaign
 * Categories: aiad-homepage
 * Keywords: homepage, campaign, video, partners, reach, section
 * Description: The Campaign section in blocks, so its text and video are edited on the page. Replaces the Campaign section block (template-parts/front-page/section-campaign.php).
 *
 * The text is core blocks and starts with the site's wording (the Customizer's, or the standard wording). The
 * partner logo strip, the video and the partner cards are small blocks (blocks/); the logos and cards come from
 * Partners. aiad_campaign_section_attributes() adds what depends on them when the section renders.
 *
 * @package AI_Awareness_Day
 */

$aiad_defaults = aiad_get_customizer_defaults();
$aiad_title    = aiad_homepage_field_fallback( 'aiad_campaign_title', $aiad_defaults['aiad_campaign_title'] );
$aiad_text     = aiad_homepage_field_fallback( 'aiad_campaign_text', $aiad_defaults['aiad_campaign_text'] );
$aiad_text_2   = aiad_homepage_field_fallback( 'aiad_campaign_text_2', $aiad_defaults['aiad_campaign_text_2'] );
$aiad_embed    = esc_url_raw( aiad_homepage_field_fallback( 'aiad_campaign_linkedin_embed_src', $aiad_defaults['aiad_campaign_linkedin_embed_src'] ) );
$aiad_video    = '' !== $aiad_embed ? serialize_block_attributes( array( 'url' => $aiad_embed ) ) . ' ' : '';
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Campaign","patternName":"aiad/homepage-campaign"},"className":"section","layout":{"type":"default"},"anchor":"campaign"} -->
<section id="campaign" class="wp-block-group section"><!-- wp:group {"className":"container","layout":{"type":"default"}} -->
<div class="wp-block-group container"><!-- wp:aiad/partner-marquee /-->

<!-- wp:group {"className":"campaign-split","layout":{"type":"default"}} -->
<div class="wp-block-group campaign-split"><!-- wp:group {"className":"campaign-content fade-up","layout":{"type":"default"}} -->
<div class="wp-block-group campaign-content fade-up"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Campaign', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php echo esc_html( $aiad_title ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo wp_kses_post( $aiad_text ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo wp_kses_post( $aiad_text_2 ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/campaign-embed <?php echo $aiad_video; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- serialised block attributes. ?>/--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"momentum-section fade-up","layout":{"type":"default"},"anchor":"reach"} -->
<div id="reach" class="wp-block-group momentum-section fade-up"><!-- wp:group {"className":"momentum-intro","layout":{"type":"default"}} -->
<div class="wp-block-group momentum-intro"><!-- wp:paragraph {"className":"section-label"} -->
<p class="section-label"><?php esc_html_e( 'Traction', 'ai-awareness-day' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:heading {"className":"section-title"} -->
<h2 class="wp-block-heading section-title"><?php esc_html_e( '1,000,000 reach so far', 'ai-awareness-day' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"section-desc"} -->
<p class="section-desc"><?php echo wp_kses_post( __( "The support for AI Awareness Day is growing fast. With the help of our partners — charities, edtech organisations, multi-academy trusts, a national broadcaster, and a multinational publishing and education company — sharing the campaign via social media, newsletters and more, we estimate we're already reaching over 1,000,000 students. Together, we're building a national movement.", 'ai-awareness-day' ) ); // Apostrophes as the editor saves them. ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:aiad/partners-grid /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></section>
<!-- /wp:group -->
