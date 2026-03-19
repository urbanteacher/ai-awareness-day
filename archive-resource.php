<?php
/**
 * Archive template for Resources (Lesson Starter, Lesson Activity, Assembly).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="resources-archive">
    <section class="section pt-100">
        <div class="container">
            <span class="section-label"><?php esc_html_e('Free Resources', 'ai-awareness-day'); ?></span>
            <h1 class="section-title"><?php post_type_archive_title(); ?></h1>
            <p class="section-desc">
                <?php esc_html_e('Lesson starters, lesson activities, and assembly materials for AI Awareness Day.', 'ai-awareness-day'); ?>
            </p>

            <?php
            // Display theme badges (same as homepage toolkit section)
            $resources_url = get_post_type_archive_link('resource');
            $theme_terms = get_terms(array('taxonomy' => 'resource_principle', 'hide_empty' => false));
            if ($theme_terms && !is_wp_error($theme_terms)):
                ?>
                <div class="resources-theme-badges fade-up mt-1-5rem mb-2rem">
                    <div class="themes-links">
                        <?php foreach ($theme_terms as $term):
                            $url = add_query_arg('principle', $term->slug, $resources_url);
                            // Map term slug to Customizer badge setting (normalize to lowercase)
                            // Use same simple approach as display board images (which work reliably)
                            $badge_slug = strtolower($term->slug);
                            $theme_badge_id = absint(get_theme_mod('aiad_badge_' . $badge_slug, 0));
                            $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url($theme_badge_id, 'thumbnail') : '';
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="theme-link">
                                <?php if ($theme_badge_src): ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url($theme_badge_src); ?>" alt="" aria-hidden="true"
                                            class="theme-link__badge-img" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html($term->name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $type_filter = isset($_GET['resource_type']) ? sanitize_text_field( wp_unslash($_GET['resource_type']) ) : '';
            $principle_filter = isset($_GET['principle']) ? sanitize_text_field( wp_unslash($_GET['principle']) ) : '';
            $duration_filter = isset($_GET['duration']) ? sanitize_text_field( wp_unslash($_GET['duration']) ) : '';
            $activity_filter = isset($_GET['activity_type']) ? sanitize_text_field( wp_unslash($_GET['activity_type']) ) : '';
            $key_stage_filter = isset($_GET['key_stage']) ? sanitize_text_field( wp_unslash($_GET['key_stage']) ) : '';

            $args = array(
                'post_type' => 'resource',
                'post_status' => 'publish',
                'posts_per_page' => 200, // Limit to 200 resources for performance (can be increased if needed)
                'orderby' => 'title',
                'order' => 'ASC',
            );
            $tax_query = array();
            if ($type_filter) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_type',
                    'field' => 'slug',
                    'terms' => $type_filter,
                );
            }
            if ($principle_filter) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_principle',
                    'field' => 'slug',
                    'terms' => $principle_filter,
                );
            }
            if ($duration_filter) {
                $tax_query[] = array(
                    'taxonomy' => 'resource_duration',
                    'field' => 'slug',
                    'terms' => $duration_filter,
                );
            }
            if ($activity_filter) {
                $tax_query[] = array(
                    'taxonomy' => 'activity_type',
                    'field' => 'slug',
                    'terms' => $activity_filter,
                );
            }
            if (!empty($tax_query)) {
                $args['tax_query'] = array_merge(array('relation' => 'AND'), $tax_query);
            }
            if ($key_stage_filter && function_exists('aiad_key_stage_options') && array_key_exists($key_stage_filter, aiad_key_stage_options())) {
                $args['meta_query'] = array(
                    array(
                        'key' => '_aiad_key_stage',
                        'value' => $key_stage_filter,
                        'compare' => '=',
                    ),
                );
            }

            $resources = new WP_Query($args);
            ?>

            <div class="resource-filters fade-up">
                <?php
                // Use current URL so Apply filters keeps you on the resources archive (avoids redirect to home)
                $filter_form_action = get_post_type_archive_link('resource') ?: (home_url('/resources/'));
                ?>
                <form method="get" class="resource-filter-form" action="<?php echo esc_url($filter_form_action); ?>">
                    <?php if (!get_option('permalink_structure')): ?>
                        <input type="hidden" name="post_type" value="resource" />
                    <?php endif; ?>
                    <div class="resource-filter-group">
                        <label for="resource_type"
                            class="resource-filter-label"><?php esc_html_e('Resource Type', 'ai-awareness-day'); ?></label>
                        <select id="resource_type" name="resource_type" class="resource-filter-select"
                            data-filter="true">
                            <option value=""><?php esc_html_e('All types', 'ai-awareness-day'); ?></option>
                            <?php
                            $types = get_terms(array('taxonomy' => 'resource_type', 'hide_empty' => false));
                            foreach ($types as $term):
                                ?>
                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($type_filter, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group">
                        <label for="principle"
                            class="resource-filter-label"><?php esc_html_e('Theme', 'ai-awareness-day'); ?></label>
                        <select id="principle" name="principle" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e('All themes', 'ai-awareness-day'); ?></option>
                            <?php
                            $themes = get_terms(array('taxonomy' => 'resource_principle', 'hide_empty' => false));
                            foreach ($themes as $term):
                                ?>
                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($principle_filter, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group">
                        <label for="duration"
                            class="resource-filter-label"><?php esc_html_e('Length', 'ai-awareness-day'); ?></label>
                        <select id="duration" name="duration" class="resource-filter-select" data-filter="true">
                            <option value=""><?php esc_html_e('All session lengths', 'ai-awareness-day'); ?></option>
                            <?php
                            $durations = get_terms(array('taxonomy' => 'resource_duration', 'hide_empty' => false));
                            foreach ($durations as $term):
                                $badge_label = function_exists('aiad_duration_badge_label') ? aiad_duration_badge_label($term) : $term->name;
                                ?>
                                <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($duration_filter, $term->slug); ?>><?php echo esc_html($badge_label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="resource-filter-group">
                        <label for="activity_type"
                            class="resource-filter-label"><?php esc_html_e('Activity', 'ai-awareness-day'); ?></label>
                        <select id="activity_type" name="activity_type" class="resource-filter-select"
                            data-filter="true">
                            <option value=""><?php esc_html_e('All activity types', 'ai-awareness-day'); ?></option>
                            <?php
                            $activity_terms = get_terms(array('taxonomy' => 'activity_type', 'hide_empty' => false));
                            if ($activity_terms && !is_wp_error($activity_terms)):
                                foreach ($activity_terms as $term):
                                    ?>
                                    <option value="<?php echo esc_attr($term->slug); ?>" <?php selected($activity_filter, $term->slug); ?>><?php echo esc_html($term->name); ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <?php if (function_exists('aiad_key_stage_options')): ?>
                        <div class="resource-filter-group">
                            <label for="key_stage"
                                class="resource-filter-label"><?php esc_html_e('Key stage', 'ai-awareness-day'); ?></label>
                            <select id="key_stage" name="key_stage" class="resource-filter-select" data-filter="true">
                                <option value=""><?php esc_html_e('All key stages', 'ai-awareness-day'); ?></option>
                                <?php foreach (aiad_key_stage_options() as $ks_slug => $ks_label): ?>
                                    <option value="<?php echo esc_attr($ks_slug); ?>" <?php selected($key_stage_filter, $ks_slug); ?>><?php echo esc_html($ks_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    <div class="resource-filter-group resource-filter-group--submit">
                        <span class="resource-filter-label" aria-hidden="true">&nbsp;</span>
                        <a href="<?php echo esc_url(get_post_type_archive_link('resource') ?: home_url('/resources/')); ?>"
                            class="resource-filters-clear resource-filter-submit"><?php esc_html_e('Clear filters', 'ai-awareness-day'); ?></a>
                    </div>
                </form>
            </div>

            <div class="resources-loading" style="display:none" aria-live="polite">
                <?php esc_html_e('Loading…', 'ai-awareness-day'); ?></div>
            <div class="resources-grid">
                <?php if ($resources->have_posts()): ?>
                    <?php while ($resources->have_posts()):
                        $resources->the_post();
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
                        $download_url = get_post_meta(get_the_ID(), '_aiad_download_url', true);
                        $duration_slug = $durations && !is_wp_error($durations) ? $durations[0]->slug : '';
                        $session_cards = function_exists('aiad_explore_session_cards') ? aiad_explore_session_cards() : array();
                        $activity_terms = get_the_terms(get_the_ID(), 'activity_type');
                        $placeholder_text = ($activity_terms && !is_wp_error($activity_terms) && !empty($activity_terms))
                            ? $activity_terms[0]->name
                            : ($type_name ? $type_name : (($duration_slug && isset($session_cards[$duration_slug]['badge_short']))
                                ? ucwords($session_cards[$duration_slug]['badge_short'])
                                : ($duration_name ? $duration_name : '—')));
                        $has_overlay = $type_name || $theme_name;
                        ?>
                        <article class="resource-card resource-card--download fade-up">
                            <a href="<?php the_permalink(); ?>" class="resource-card__image-link">
                                <?php if (has_post_thumbnail()): ?>
                                    <?php the_post_thumbnail('medium_large', array('class' => 'resource-card__image')); ?>
                                <?php else: ?>
                                    <div class="resource-card__image-placeholder" aria-hidden="true">
                                        <span
                                            class="resource-card__image-placeholder-text"><?php echo esc_html($placeholder_text); ?></span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($has_overlay): ?>
                                    <div class="resource-card__image-overlay" aria-hidden="true">
                                        <div class="resource-card__image-top">
                                            <?php if ($type_name): ?>
                                                <span
                                                    class="resource-card__pill resource-card__pill--type"><?php echo esc_html($type_name); ?></span>
                                            <?php endif; ?>
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
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="resource-card__body">
                                <h2 class="resource-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <?php
                                $summary = '';
                                if (has_excerpt()) {
                                    $summary = get_the_excerpt();
                                } else {
                                    $content = get_the_content('');
                                    if ($content) {
                                        $summary = wp_trim_words(wp_strip_all_tags($content), 30);
                                    }
                                }
                                if ($summary):
                                    ?>
                                    <p class="resource-card__excerpt"><?php echo esc_html($summary); ?></p>
                                <?php endif; ?>
                                <p class="resource-card__action">
                                    <?php if ($download_url): ?>
                                        <?php $download_label = function_exists('aiad_resource_download_label') ? aiad_resource_download_label($download_url) : __('Download', 'ai-awareness-day'); ?>
                                        <a href="<?php echo esc_url($download_url); ?>"
                                            class="resource-card__link resource-download-link"
                                            data-resource-id="<?php echo esc_attr((string) get_the_ID()); ?>" download
                                            target="_blank" rel="noopener"><?php echo esc_html($download_label); ?> →</a>
                                    <?php else: ?>
                                        <a href="<?php the_permalink(); ?>"
                                            class="resource-card__link"><?php esc_html_e('View resource', 'ai-awareness-day'); ?>
                                            →</a>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </article>
                    <?php endwhile; ?>
                    <?php wp_reset_postdata(); ?>
                <?php endif; ?>
            </div>
            <p class="section-desc resources-empty-message" <?php echo $resources->have_posts() ? ' style="display:none"' : ''; ?>>
                <?php esc_html_e('We have decided to phase the resources in order in the lead-up to the campaign.', 'ai-awareness-day'); ?>
            </p>

            <?php
            // Use pretty URL when permalinks are enabled so the archive loads correctly (avoids ?post_type=... showing front page).
            $from_partners_url = get_option('permalink_structure')
                ? home_url('/from-partners/')
                : get_post_type_archive_link('featured_resource');
            if (!$from_partners_url) {
                $from_partners_url = home_url('/from-partners/');
            }
            if ($from_partners_url):
                ?>
                <div class="featured-from-orgs featured-from-orgs--teaser">
                    <h2 class="section-title"><?php esc_html_e('From other organisations', 'ai-awareness-day'); ?></h2>
                    <p class="section-desc">
                        <?php esc_html_e('Interactive AI games and learning tools from trusted partners. Filter by type, theme and session length on the dedicated page.', 'ai-awareness-day'); ?>
                    </p>
                    <p class="featured-from-orgs__cta">
                        <a href="<?php echo esc_url($from_partners_url); ?>"
                            class="resource-filter-submit featured-from-orgs__cta-btn"><?php esc_html_e('Browse resources from partners', 'ai-awareness-day'); ?></a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>