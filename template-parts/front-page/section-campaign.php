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

        <?php
        // Campaign resources: all free resources + partner resources
        $campaign_resources = new WP_Query(array(
            'post_type'      => 'resource',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ));
        $campaign_featured = new WP_Query(array(
            'post_type'      => 'featured_resource',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ));
        $has_campaign_resources = $campaign_resources->have_posts() || $campaign_featured->have_posts();
        if ($has_campaign_resources):
        ?>
        <div class="campaign-resources-block fade-up" style="margin-top: 3rem;">
            <div class="fade-up">
                <span class="section-label"><?php esc_html_e('Campaign Updates', 'ai-awareness-day'); ?></span>
                <h2 class="section-title"><?php esc_html_e('Resources & Activities', 'ai-awareness-day'); ?></h2>
            </div>
            <div class="resources-grid" style="margin-top: 2rem;">
                <?php
                // Regular resources
                while ($campaign_resources->have_posts()):
                    $campaign_resources->the_post();
                    $types     = get_the_terms(get_the_ID(), 'resource_type');
                    $themes    = get_the_terms(get_the_ID(), 'resource_principle');
                    $durations = get_the_terms(get_the_ID(), 'resource_duration');
                    $type_name     = $types && !is_wp_error($types) ? $types[0]->name : '';
                    $theme_name    = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
                    $duration_name = '';
                    $duration_slug = $durations && !is_wp_error($durations) ? $durations[0]->slug : '';
                    if ($durations && !is_wp_error($durations) && function_exists('aiad_duration_badge_label')) {
                        $duration_name = aiad_duration_badge_label($durations[0]);
                    } elseif ($durations && !is_wp_error($durations)) {
                        $duration_name = $durations[0]->name;
                    }
                    $session_cards = function_exists('aiad_explore_session_cards') ? aiad_explore_session_cards() : array();
                    $pill_duration = ($duration_slug && isset($session_cards[$duration_slug]['badge_short']))
                        ? ucwords($session_cards[$duration_slug]['badge_short'])
                        : ($duration_name ? $duration_name : '');
                    ?>
                    <article class="resource-card resource-card--download fade-up">
                        <a href="<?php the_permalink(); ?>" class="resource-card__image-link">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('medium_large', array('class' => 'resource-card__image')); ?>
                            <?php else: ?>
                                <div class="resource-card__image-placeholder" aria-hidden="true">
                                    <span class="resource-card__image-placeholder-text"><?php esc_html_e('Resource', 'ai-awareness-day'); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($theme_name || $pill_duration): ?>
                                <div class="resource-card__image-overlay" aria-hidden="true">
                                    <div class="resource-card__image-top">
                                        <?php if ($theme_name):
                                            $theme_slug = strtolower($theme_name);
                                            $pill_class = 'resource-card__pill--theme';
                                            if (in_array($theme_slug, array('safe', 'smart', 'creative', 'responsible', 'future'), true)) {
                                                $pill_class .= ' resource-card__pill--' . $theme_slug;
                                            }
                                            ?>
                                            <span class="resource-card__pill <?php echo esc_attr($pill_class); ?>"><?php echo esc_html($theme_name); ?></span>
                                        <?php endif; ?>
                                        <?php if ($pill_duration): ?>
                                            <span class="resource-card__pill resource-card__pill--type"><?php echo esc_html($pill_duration); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="resource-card__body">
                            <h2 class="resource-card__title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            <?php if (has_excerpt()): ?>
                                <p class="resource-card__excerpt section-desc"><?php echo esc_html(get_the_excerpt()); ?></p>
                            <?php endif; ?>
                            <p class="resource-card__action">
                                <a href="<?php the_permalink(); ?>" class="resource-card__link"><?php esc_html_e('View resource', 'ai-awareness-day'); ?> →</a>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>

                <?php
                // Partner resources (featured_resource)
                while ($campaign_featured->have_posts()):
                    $campaign_featured->the_post();
                    $types     = get_the_terms(get_the_ID(), 'resource_type');
                    $themes    = get_the_terms(get_the_ID(), 'resource_principle');
                    $durations = get_the_terms(get_the_ID(), 'resource_duration');
                    $type_name     = $types && !is_wp_error($types) ? $types[0]->name : '';
                    $theme_name    = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
                    $duration_name = '';
                    if ($durations && !is_wp_error($durations) && function_exists('aiad_duration_badge_label')) {
                        $duration_name = aiad_duration_badge_label($durations[0]);
                    } elseif ($durations && !is_wp_error($durations)) {
                        $duration_name = $durations[0]->name;
                    }
                    $url      = get_post_meta(get_the_ID(), '_featured_resource_url', true);
                    $org_name = get_post_meta(get_the_ID(), '_featured_resource_org_name', true);
                    $link     = $url ? $url : get_permalink();
                    $activity_terms   = get_the_terms(get_the_ID(), 'activity_type');
                    $placeholder_type = ($activity_terms && !is_wp_error($activity_terms) && !empty($activity_terms))
                        ? $activity_terms[0]->name
                        : ($type_name ? $type_name : '—');
                    ?>
                    <article class="resource-card resource-card--external fade-up">
                        <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer" class="resource-card__image-link">
                            <?php if (has_post_thumbnail()): ?>
                                <?php the_post_thumbnail('medium_large', array('class' => 'resource-card__image')); ?>
                            <?php else: ?>
                                <div class="resource-card__image-placeholder" aria-hidden="true">
                                    <span class="resource-card__image-placeholder-text"><?php echo esc_html($placeholder_type); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($org_name || $theme_name): ?>
                                <div class="resource-card__image-overlay" aria-hidden="true">
                                    <div class="resource-card__image-top">
                                        <?php if ($theme_name):
                                            $theme_slug = strtolower($theme_name);
                                            $pill_class = 'resource-card__pill--theme';
                                            if (in_array($theme_slug, array('safe', 'smart', 'creative', 'responsible', 'future'), true)) {
                                                $pill_class .= ' resource-card__pill--' . $theme_slug;
                                            }
                                            ?>
                                            <span class="resource-card__pill <?php echo esc_attr($pill_class); ?>"><?php echo esc_html($theme_name); ?></span>
                                        <?php endif; ?>
                                        <?php if ($org_name): ?>
                                            <span class="resource-card__pill resource-card__pill--org"><?php echo esc_html($org_name); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="resource-card__image-title"><?php the_title(); ?></div>
                                </div>
                            <?php endif; ?>
                        </a>
                        <div class="resource-card__body">
                            <?php if ($org_name || $type_name || $theme_name): ?>
                                <p class="resource-card__meta">
                                    <?php echo esc_html(implode(' · ', array_filter(array($org_name, $type_name, $theme_name)))); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ($duration_name): ?>
                                <span class="duration-badge duration-badge--card"><?php echo esc_html($duration_name); ?></span>
                            <?php endif; ?>
                            <h2 class="resource-card__title">
                                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer"><?php the_title(); ?></a>
                            </h2>
                            <?php if (has_excerpt()): ?>
                                <p class="resource-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                            <?php endif; ?>
                            <p class="resource-card__action">
                                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer" class="resource-card__link"><?php esc_html_e('View resource', 'ai-awareness-day'); ?> →</a>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>