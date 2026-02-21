<?php
/**
 * Title: Principles Grid
 * Slug: aiad/principles
 * Categories: featured
 *
 * Uses PHP at render time. Block comments aid editor recognition; for full Site Editor
 * support this would need to be a dynamic block or static markup.
 */
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Principles"},"className":"section section--alt","layout":{"type":"constrained"},"id":"principles"} -->
<section class="wp-block-group section section--alt" id="principles">
    <!-- wp:group {"className":"container","layout":{"type":"constrained"}} -->
    <div class="wp-block-group container">
        <!-- wp:group {"className":"fade-up","layout":{"type":"constrained"}} -->
        <div class="wp-block-group fade-up">
            <!-- wp:paragraph {"className":"section-label"} -->
            <p class="section-label">
                <?php esc_html_e('Principles', 'ai-awareness-day'); ?>
            </p>
            <!-- /wp:paragraph -->
            <!-- wp:heading {"className":"section-title"} -->
            <h2 class="wp-block-heading section-title">
                <?php esc_html_e('Five Core Principles', 'ai-awareness-day'); ?>
            </h2>
            <!-- /wp:heading -->
            <!-- wp:paragraph {"className":"section-desc"} -->
            <p class="section-desc">
                <?php esc_html_e('Our educational framework is built on five foundational principles that guide how we approach AI learning.', 'ai-awareness-day'); ?>
            </p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:group -->

        <!-- wp:group {"className":"principles-grid","layout":{"type":"grid","columnCount":null,"minimumColumnWidth":"280px"}} -->
        <div class="wp-block-group principles-grid">
            <?php
            $principle_slugs = array('safe', 'smart', 'creative', 'responsible', 'future');
            $principle_default_titles = array(
                'safe' => __('Safe', 'ai-awareness-day'),
                'smart' => __('Smart', 'ai-awareness-day'),
                'creative' => __('Creative', 'ai-awareness-day'),
                'responsible' => __('Responsible', 'ai-awareness-day'),
                'future' => __('Future', 'ai-awareness-day'),
            );
            $principle_default_descs = array(
                'safe' => __('Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day'),
                'smart' => __('Building intelligent understanding of how AI works.', 'ai-awareness-day'),
                'creative' => __('Harnessing AI as a tool for creativity and innovation.', 'ai-awareness-day'),
                'responsible' => __('Promoting ethical and responsible use of AI.', 'ai-awareness-day'),
                'future' => __('Preparing for an AI-shaped future with confidence.', 'ai-awareness-day'),
            );
            foreach ($principle_slugs as $index => $slug):
                $title_mod = get_theme_mod('aiad_principle_title_' . $slug, '');
                $title = !empty($title_mod) ? $title_mod : ($principle_default_titles[$slug] ?? ucfirst($slug));
                $desc_mod = get_theme_mod('aiad_principle_desc_' . $slug, '');
                $desc = !empty($desc_mod) ? $desc_mod : ($principle_default_descs[$slug] ?? '');
                $badge_id = absint(get_theme_mod('aiad_badge_' . $slug, 0));
                $badge_src = $badge_id ? wp_get_attachment_image_url($badge_id, 'medium') : '';
                ?>
                <!-- wp:group {"className":"principle-card principle-card--<?php echo esc_attr($slug); ?> fade-up stagger-<?php echo $index + 1; ?>","layout":{"type":"constrained"}} -->
                <div
                    class="wp-block-group principle-card principle-card--<?php echo esc_attr($slug); ?> fade-up stagger-<?php echo $index + 1; ?>">
                    <div class="principle-badge">
                        <?php if ($badge_src): ?>
                            <img src="<?php echo esc_url($badge_src); ?>" alt="" aria-hidden="true"
                                class="principle-badge__img" />
                        <?php endif; ?>
                    </div>
                    <!-- wp:heading {"level":3} -->
                    <h3 class="wp-block-heading">
                        <?php echo esc_html($title); ?>
                    </h3>
                    <!-- /wp:heading -->
                    <!-- wp:paragraph {"className":"section-desc"} -->
                    <p class="section-desc">
                        <?php echo esc_html($desc); ?>
                    </p>
                    <!-- /wp:paragraph -->
                </div>
                <!-- /wp:group -->
            <?php endforeach; ?>
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
</section>
<!-- /wp:group -->