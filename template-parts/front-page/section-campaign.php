<?php
/**
 * Front page section: Campaign (includes momentum/reach)
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();
$defaults = aiad_get_customizer_defaults();
$campaign_embed_src = esc_url(get_theme_mod('aiad_campaign_linkedin_embed_src', $defaults['aiad_campaign_linkedin_embed_src']));
$campaign_has_embed = !empty($campaign_embed_src);
$campaign_partners    = aiad_campaign_partners();
$partners             = $campaign_partners['partners'];
$partners_count       = $campaign_partners['count'];
$initial_show_mobile  = $campaign_partners['initial_show_mobile'];
$initial_show_desktop = $campaign_partners['initial_show_desktop'];
?>
<section
    class="section <?php echo esc_attr($text_alignment_class); ?> <?php echo $campaign_has_embed ? 'campaign--split' : ''; ?>"
    id="campaign"
    data-anchor-target=".campaign-split">
    <div class="container">
        <?php get_template_part( 'template-parts/components/partner-marquee' ); ?>

        <div class="campaign-split<?php echo $campaign_has_embed ? '' : ' campaign-split--single'; ?>">
            <div class="campaign-content fade-up">
                <span class="section-label"><?php esc_html_e('Campaign', 'ai-awareness-day'); ?></span>
                <h2 class="section-title">
                    <?php echo esc_html(get_theme_mod('aiad_campaign_title', $defaults['aiad_campaign_title'])); ?>
                </h2>
                <p class="section-desc">
                    <?php echo wp_kses_post(get_theme_mod('aiad_campaign_text', $defaults['aiad_campaign_text'])); ?>
                </p>
                <p class="section-desc">
                    <?php echo wp_kses_post(get_theme_mod('aiad_campaign_text_2', $defaults['aiad_campaign_text_2'])); ?>
                </p>
            </div>
<?php get_template_part( 'template-parts/components/campaign-embed', null, array( 'src' => $campaign_embed_src ) ); ?>
        </div>

        <div id="reach" class="momentum-section fade-up"
            data-initial-show-mobile="<?php echo (int) $initial_show_mobile; ?>"
            data-initial-show-desktop="<?php echo (int) $initial_show_desktop; ?>">
            <div class="momentum-intro">
                <span class="section-label"><?php esc_html_e('Traction', 'ai-awareness-day'); ?></span>
                <h2 class="section-title"><?php esc_html_e('1,000,000 reach so far', 'ai-awareness-day'); ?></h2>
                <p class="section-desc">
                    <?php esc_html_e("The support for AI Awareness Day is growing fast. With the help of our partners — charities, edtech organisations, multi-academy trusts, a national broadcaster, and a multinational publishing and education company — sharing the campaign via social media, newsletters and more, we estimate we're already reaching over 1,000,000 students. Together, we're building a national movement.", 'ai-awareness-day'); ?>
                </p>
            </div>

<?php get_template_part( 'template-parts/components/partners-grid', null, $campaign_partners ); ?>
        </div>
    </div>
</section>