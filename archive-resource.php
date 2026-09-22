<?php
/**
 * Archive template for Resources (session length, theme, activity filters).
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="resources-archive">
    <section class="section pt-100">
        <div class="container">
            <span class="section-label"><?php esc_html_e('Free Resources', 'ai-awareness-day'); ?></span>
            <h1 class="section-title"><?php echo esc_html( post_type_archive_title( '', false ) ); ?></h1>
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
                            $theme_badge_src = function_exists( 'aiad_theme_link_badge_src' )
                                ? aiad_theme_link_badge_src( $term->slug )
                                : '';
                            ?>
                            <a href="<?php echo esc_url($url); ?>" class="theme-link theme-link--<?php echo esc_attr( strtolower( $term->slug ) ); ?>">
                                <?php if ($theme_badge_src): ?>
                                    <span class="theme-link__badge">
                                        <img src="<?php echo esc_url($theme_badge_src); ?>" alt="" aria-hidden="true"
                                            class="theme-link__badge-img" width="48" height="48" />
                                    </span>
                                <?php endif; ?>
                                <span class="theme-link__label"><?php echo esc_html($term->name); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $principle_filter = isset($_GET['principle']) ? sanitize_text_field( wp_unslash($_GET['principle']) ) : '';
            $duration_filter = isset($_GET['duration']) ? sanitize_text_field( wp_unslash($_GET['duration']) ) : '';
            $legacy_type_filter = isset($_GET['resource_type']) ? sanitize_text_field( wp_unslash($_GET['resource_type']) ) : '';
            if ( $legacy_type_filter && $duration_filter === '' && function_exists( 'aiad_legacy_resource_type_slug_to_duration_slug' ) ) {
                $mapped = aiad_legacy_resource_type_slug_to_duration_slug( $legacy_type_filter );
                if ( $mapped ) {
                    $duration_filter = $mapped;
                }
            }
            $activity_filter = isset($_GET['activity_type']) ? sanitize_text_field( wp_unslash($_GET['activity_type']) ) : '';
            $key_stage_filter = isset($_GET['key_stage']) ? sanitize_text_field( wp_unslash($_GET['key_stage']) ) : '';

            if ( $principle_filter && ! term_exists( $principle_filter, 'resource_principle' ) ) {
                $principle_filter = '';
            }
            if ( $duration_filter && ! term_exists( $duration_filter, 'resource_duration' ) ) {
                $duration_filter = '';
            }
            if ( $activity_filter && ! term_exists( $activity_filter, 'activity_type' ) ) {
                $activity_filter = '';
            }

            $args = array(
                'post_type' => 'resource',
                'post_status' => 'publish',
                'posts_per_page' => 200, // Limit to 200 resources for performance (can be increased if needed)
                'orderby' => 'title',
                'order' => 'ASC',
            );
            $tax_query = array();
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
                        'value' => '"' . $key_stage_filter . '"',
                        'compare' => 'LIKE',
                    ),
                );
            }

            $resources = new WP_Query($args);
            $has_resources = $resources->post_count > 0;
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
                            class="resource-filter-label"><?php esc_html_e('Session length', 'ai-awareness-day'); ?></label>
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
                            class="resource-filter-label"><?php esc_html_e('Format', 'ai-awareness-day'); ?></label>
                        <select id="activity_type" name="activity_type" class="resource-filter-select"
                            data-filter="true">
                            <option value=""><?php esc_html_e('All formats', 'ai-awareness-day'); ?></option>
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
            <?php /* resource-filters.js finds this grid and swaps in the cards the AJAX filter renders. */ ?>
            <div class="resource-tiles">
                <?php if ( $resources->have_posts() ) : ?>
                    <?php
                    while ( $resources->have_posts() ) :
                        $resources->the_post();
                        get_template_part( 'template-parts/components/resource-tile' );
                    endwhile;
                    wp_reset_postdata();
                    ?>
                <?php endif; ?>
            </div>
            <p class="section-desc resources-empty-message" <?php echo $has_resources ? ' style="display:none"' : ''; ?>>
                <?php esc_html_e('No resources found for the selected filters. Try adjusting or clearing your selection.', 'ai-awareness-day'); ?>
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