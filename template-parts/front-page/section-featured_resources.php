<?php
/**
 * Front page section: Featured resources (partner resources) + LinkedIn card
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
$featured_resources = new WP_Query( array(
            'post_type' => 'featured_resource',
            'post_status' => 'publish',
            'posts_per_page' => 3,
            'orderby' => 'menu_order title',
            'order' => 'ASC',
        ));

        if ($featured_resources->have_posts()):
            ?>
            <section class="section section--alt" id="partner-resources">
                <div class="container">
                    <div class="fade-up">
                        <span class="section-label"><?php esc_html_e('Extra Resources', 'ai-awareness-day'); ?></span>
                        <h2 class="section-title"><?php esc_html_e('Handpicked Quality Resources', 'ai-awareness-day'); ?>
                        </h2>
                        <p class="section-desc">
                            <?php esc_html_e('A curated selection of interactive AI games and learning tools from trusted organisations.', 'ai-awareness-day'); ?>
                        </p>
                    </div>

                    <div class="resources-grid" style="margin-top: 2rem;">
                        <?php while ($featured_resources->have_posts()):
                            $featured_resources->the_post();
                            $types = get_the_terms(get_the_ID(), 'resource_type');
                            $themes = get_the_terms(get_the_ID(), 'resource_principle');
                            $durations = get_the_terms(get_the_ID(), 'resource_duration');
                            $type_name = $types && !is_wp_error($types) ? $types[0]->name : '';
                            $theme_name = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
                            $duration_name = '';
                            if ($durations && !is_wp_error($durations) && function_exists('aiad_duration_badge_label')) {
                                $duration_name = aiad_duration_badge_label($durations[0]);
                            } elseif ($durations && !is_wp_error($durations)) {
                                $duration_name = $durations[0]->name;
                            }
                            $url = get_post_meta(get_the_ID(), '_featured_resource_url', true);
                            $org_name = get_post_meta(get_the_ID(), '_featured_resource_org_name', true);
                            $org_url = get_post_meta(get_the_ID(), '_featured_resource_org_url', true);
                            $link = $url ? $url : get_permalink();
                            $activity_terms = get_the_terms(get_the_ID(), 'activity_type');
                            $placeholder_type = ($activity_terms && !is_wp_error($activity_terms) && !empty($activity_terms))
                                ? $activity_terms[0]->name
                                : ($type_name ? $type_name : '—');
                            ?>
                            <article class="resource-card resource-card--external fade-up">
                                <?php
                                $meta_label = trim($type_name . ($type_name && $theme_name ? ' · ' : '') . $theme_name);
                                ?>
                                <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer"
                                    class="resource-card__image-link">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('medium_large', array('class' => 'resource-card__image')); ?>
                                    <?php else: ?>
                                        <div class="resource-card__image-placeholder" aria-hidden="true">
                                            <span
                                                class="resource-card__image-placeholder-text"><?php echo esc_html($placeholder_type); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($org_name || $meta_label): ?>
                                        <div class="resource-card__image-overlay" aria-hidden="true">
                                            <div class="resource-card__image-top">
                                                <?php if ($theme_name): ?>
                                                    <?php
                                                    $theme_slug = strtolower($theme_name);
                                                    $pill_class = 'resource-card__pill--theme';
                                                    if (in_array($theme_slug, array('safe', 'smart', 'creative', 'responsible', 'future'), true)) {
                                                        $pill_class .= ' resource-card__pill--' . $theme_slug;
                                                    }
                                                    ?>
                                                    <span
                                                        class="resource-card__pill <?php echo esc_attr($pill_class); ?>"><?php echo esc_html($theme_name); ?></span>
                                                <?php endif; ?>
                                                <?php if ($org_name): ?>
                                                    <span
                                                        class="resource-card__pill resource-card__pill--org"><?php echo esc_html($org_name); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="resource-card__image-title"><?php the_title(); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <div class="resource-card__body">
                                    <?php if ($org_name || $type_name || $theme_name): ?>
                                        <p class="resource-card__meta">
                                            <?php
                                            $meta_parts = array_filter(array($org_name, $type_name, $theme_name));
                                            echo esc_html(implode(' · ', $meta_parts));
                                            ?>
                                        </p>
                                    <?php endif; ?>
                                    <?php if ($duration_name): ?>
                                        <span class="duration-badge duration-badge--card"><?php echo esc_html($duration_name); ?></span>
                                    <?php endif; ?>
                                    <h2 class="resource-card__title">
                                        <a href="<?php echo esc_url($link); ?>" target="_blank"
                                            rel="noopener noreferrer"><?php the_title(); ?></a>
                                    </h2>
                                    <?php if (has_excerpt()): ?>
                                        <p class="resource-card__excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
                                    <?php endif; ?>
                                    <p class="resource-card__action">
                                        <a href="<?php echo esc_url($link); ?>" target="_blank" rel="noopener noreferrer"
                                            class="resource-card__link"><?php esc_html_e('View resource', 'ai-awareness-day'); ?>
                                            →</a>
                                    </p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;

        $linkedin_post_url = esc_url_raw(get_theme_mod('aiad_linkedin_post_url', ''));
    if (!empty($linkedin_post_url)):
        ?>
        <!-- LinkedIn post card -->
        <section class="section <?php echo esc_attr($text_alignment_class); ?>" id="linkedin-post"
            aria-labelledby="linkedin-post-title">
            <div class="container">
                <div class="linkedin-card-wrapper fade-up">
                    <a href="<?php echo esc_url($linkedin_post_url); ?>" class="linkedin-card" target="_blank"
                        rel="noopener noreferrer">
                        <span class="linkedin-card__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="currentColor" aria-hidden="true">
                                <path
                                    d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                            </svg>
                        </span>
                        <div class="linkedin-card__content">
                            <h2 id="linkedin-post-title" class="linkedin-card__title">
                                <?php esc_html_e('Latest from LinkedIn', 'ai-awareness-day'); ?>
                            </h2>
                            <p class="linkedin-card__desc">
                                <?php esc_html_e('See our latest post and join the conversation.', 'ai-awareness-day'); ?>
                            </p>
                            <span
                                class="linkedin-card__cta"><?php esc_html_e('View post on LinkedIn', 'ai-awareness-day'); ?>
                                →</span>
                        </div>
                    </a>
                </div>
            </div>
        </section>
        <?php
        endif;
        ?>

