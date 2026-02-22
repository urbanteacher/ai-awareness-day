<?php
/**
 * Front page section: Campaign (includes momentum/reach)
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    return;
}
$defaults = aiad_get_customizer_defaults();
$campaign_embed_src = esc_url(get_theme_mod('aiad_campaign_linkedin_embed_src', $defaults['aiad_campaign_linkedin_embed_src']));
$campaign_has_embed = !empty($campaign_embed_src);
?>
<section
    class="section <?php echo esc_attr($text_alignment_class); ?> <?php echo $campaign_has_embed ? 'campaign--split' : ''; ?>"
    id="campaign">
    <div class="container">
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
            <?php if ($campaign_has_embed): ?>
                <div class="campaign-embed fade-up">
                    <div class="campaign-embed__wrapper">
                        <iframe src="<?php echo esc_url($campaign_embed_src); ?>" height="399" width="504" frameborder="0"
                            allowfullscreen title="<?php esc_attr_e('Embedded LinkedIn post', 'ai-awareness-day'); ?>"
                            loading="lazy"></iframe>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div id="reach" class="momentum-section fade-up"
            data-wp-interactive="aiad/partners" data-wp-context='{ "isExpanded": false }'>
            <div class="momentum-intro">
                <span class="section-label"><?php esc_html_e('Traction', 'ai-awareness-day'); ?></span>
                <h2 class="section-title"><?php esc_html_e('500,000 reach so far', 'ai-awareness-day'); ?></h2>
                <p class="section-desc">
                    <?php esc_html_e("The support for AI Awareness Day 2026 is growing fast. With schools now signing up across the UK, we estimate we're already reaching over 500,000 students and we're confident we'll hit 1 million in the coming months. We're thrilled to welcome our interested schools, charities, and partners. Together, we're building a national movement.", 'ai-awareness-day'); ?>
                </p>
            </div>

            <div class="partners-grid">
                <?php
                $partner_posts = new WP_Query(array(
                    'post_type' => 'partner',
                    'posts_per_page' => 50,
                    'orderby' => 'menu_order title',
                    'order' => 'ASC',
                    'post_status' => 'publish',
                ));
                $partners = array();
                if ($partner_posts->have_posts()) {
                    while ($partner_posts->have_posts()) {
                        $partner_posts->the_post();
                        $partner_stats = get_post_meta(get_the_ID(), '_partner_stats', true);
                        $partners[] = array(
                            'name' => get_the_title(),
                            'stats' => $partner_stats ? $partner_stats : '',
                            'logo' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                        );
                    }
                    wp_reset_postdata();
                }
                $partners_count = count($partners);
                $initial_show = 10;
                foreach ($partners as $index => $partner):
                    if (empty($partner['name'])) {
                        continue;
                    }
                    $is_initially_hidden = $index >= $initial_show;
                    $hidden_attr = $is_initially_hidden ? 'data-wp-class--partner-card--hidden="!context.isExpanded"' : '';
                    ?>
                    <div class="partner-card fade-up stagger-<?php echo $index + 1; ?>" <?php echo $hidden_attr; ?>
                        data-partner-index="<?php echo $index; ?>">
                        <div class="partner-logo">
                            <?php if ($partner['logo']): ?>
                                <img src="<?php echo esc_url($partner['logo']); ?>"
                                    alt="<?php echo esc_attr($partner['name']); ?>" class="partner-logo__img"
                                    onerror="this.classList.add('is-broken');" />
                            <?php endif; ?>
                        </div>
                        <h3><?php echo esc_html($partner['name']); ?></h3>
                        <?php if ($partner['stats']): ?>
                            <p class="partner-stats"><?php echo esc_html($partner['stats']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <?php
                $dummy_index = $partners_count;
                ?>
                <a href="#contact"
                    class="partner-card partner-card--dummy fade-up stagger-<?php echo $dummy_index + 1; ?>"
                    data-partner-index="<?php echo $dummy_index; ?>">
                    <div class="partner-logo">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                            style="color: var(--gray-400);">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <line x1="12" y1="8" x2="12" y2="16" />
                            <line x1="8" y1="12" x2="16" y2="12" />
                        </svg>
                    </div>
                    <h3><?php esc_html_e('Join the campaign', 'ai-awareness-day'); ?></h3>
                    <p class="partner-stats">
                        <?php esc_html_e('Complete form to join movement', 'ai-awareness-day'); ?>
                    </p>
                </a>
            </div>

            <?php if ($partners_count > $initial_show): ?>
                <div class="partners-reveal-wrapper" style="text-align: center; margin-top: 2.5rem;">
                    <button type="button" class="partners-reveal-btn" data-wp-on--click="actions.toggleReveal"
                        data-wp-class--active="context.isExpanded">
                        <span class="reveal-text" data-wp-text="state.revealText"></span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            style="margin-left: 0.5rem; transition: transform 0.3s;"
                            data-wp-style--transform="context.isExpanded ? 'rotate(180deg)' : 'rotate(0deg)'">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>