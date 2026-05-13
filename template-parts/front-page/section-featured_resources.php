<?php
/**
 * Front page section: Featured resources (partner resources) + LinkedIn card
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

// Check if user has manually selected resources via Homepage Editor
$selected_ids = array();
for ( $i = 1; $i <= 3; $i++ ) {
    $id = absint( get_theme_mod( 'aiad_handpicked_resource_' . $i, 0 ) );
    if ( $id > 0 ) {
        $selected_ids[] = $id;
    }
}

// If manual selection exists, use it; otherwise fall back to automatic query
if ( ! empty( $selected_ids ) ) {
    $featured_resources = new WP_Query( array(
        'post_type'      => 'featured_resource',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'post__in'       => $selected_ids,
        'orderby'        => 'post__in',
    ) );
} else {
    $featured_resources = new WP_Query( array(
        'post_type'      => 'featured_resource',
        'post_status'    => 'publish',
        'posts_per_page' => 3,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    ) );
}

// Get custom section title/description
$section_title = get_theme_mod( 'aiad_handpicked_resources_title', __( 'Handpicked Quality Resources', 'ai-awareness-day' ) );
$section_desc = get_theme_mod( 'aiad_handpicked_resources_desc', __( 'A curated selection of interactive AI games and learning tools from trusted organisations.', 'ai-awareness-day' ) );

        if ($featured_resources->have_posts()):
            ?>
            <section class="section section--alt" id="partner-resources">
                <div class="container">
                    <div class="fade-up">
                        <span class="section-label"><?php esc_html_e('Extra Resources', 'ai-awareness-day'); ?></span>
                        <h2 class="section-title"><?php echo esc_html( $section_title ); ?>
                        </h2>
                        <p class="section-desc">
                            <?php echo esc_html( $section_desc ); ?>
                        </p>
                    </div>

                    <div class="resources-grid" style="margin-top: 2rem;">
                        <?php while ($featured_resources->have_posts()):
                            $featured_resources->the_post();
                            $themes = get_the_terms(get_the_ID(), 'resource_principle');
                            $durations = get_the_terms(get_the_ID(), 'resource_duration');
                            $duration_labels = ($durations && !is_wp_error($durations) && function_exists('aiad_resource_duration_term_labels'))
                                ? aiad_resource_duration_term_labels($durations)
                                : array();
                            $theme_name = $themes && !is_wp_error($themes) ? $themes[0]->name : '';
                            $url = get_post_meta(get_the_ID(), '_featured_resource_url', true);
                            $org_name = get_post_meta(get_the_ID(), '_featured_resource_org_name', true);
                            $org_url = get_post_meta(get_the_ID(), '_featured_resource_org_url', true);
                            $link = $url ? $url : get_permalink();
                            $activity_terms = get_the_terms(get_the_ID(), 'activity_type');
                            $placeholder_type = ($activity_terms && !is_wp_error($activity_terms) && !empty($activity_terms))
                                ? $activity_terms[0]->name
                                : (!empty($duration_labels) ? $duration_labels[0] : '—');
                            $theme_slug    = in_array( strtolower( $theme_name ), array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true )
                                ? strtolower( $theme_name ) : '';
                            $format_label  = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
                                ? strtoupper( $activity_terms[0]->name ) : 'SLIDE';
                            $duration_parts = ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_parts' ) )
                                ? aiad_duration_badge_parts( $durations[0] ) : null;
                            $duration_str  = $duration_parts ? strtoupper( $duration_parts['time'] ) : ( ! empty( $duration_labels ) ? strtoupper( $duration_labels[0] ) : '' );
                            $article_class = 'resource-card resource-card--pointed fade-up';
                            if ( $theme_slug ) {
                                $article_class .= ' resource-card--' . $theme_slug;
                            }
                            ?>
                            <article class="<?php echo esc_attr( $article_class ); ?>">
                                <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"
                                    class="resource-card__hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
                                    <?php if ( has_post_thumbnail() ): ?>
                                        <?php the_post_thumbnail( 'medium_large', array( 'class' => 'resource-card__hero-img' ) ); ?>
                                    <?php else: ?>
                                        <div class="resource-card__hero-img" style="background:#111;" aria-hidden="true"></div>
                                    <?php endif; ?>

                                    <div class="resource-card__wedge" aria-hidden="true"></div>
                                    <div class="resource-card__fade"  aria-hidden="true"></div>

                                    <?php if ( $theme_name ): ?>
                                        <span class="resource-card__theme-label" aria-hidden="true"><?php echo esc_html( strtoupper( $theme_name ) ); ?></span>
                                    <?php endif; ?>

                                    <span class="resource-card__format" aria-hidden="true"><?php echo esc_html( $format_label ); ?></span>

                                    <?php if ( $duration_str ): ?>
                                        <span class="resource-card__duration-label" aria-hidden="true"><?php echo esc_html( $duration_str ); ?></span>
                                    <?php endif; ?>

                                    <h3 class="resource-card__title-overlay"><?php echo esc_html( get_the_title() ); ?></h3>
                                </a>

                                <div class="resource-card__body">
                                    <p class="resource-card__title-below"><?php echo esc_html( get_the_title() ); ?></p>
                                    <?php if ( has_excerpt() ): ?>
                                        <p class="resource-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                                    <?php endif; ?>
                                    <p class="resource-card__action">
                                        <a href="<?php echo esc_url( $link ); ?>" target="_blank" rel="noopener noreferrer"
                                            class="resource-card__link"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</a>
                                    </p>
                                </div>
                            </article>
                        <?php endwhile; ?>
                        <?php
                        $featured_archive_url = get_post_type_archive_link('featured_resource');
                        if ( ! $featured_archive_url ) {
                            $featured_archive_url = home_url( '/from-partners/' );
                            if ( get_option( 'permalink_structure' ) === '' ) {
                                $featured_archive_url = add_query_arg( 'post_type', 'featured_resource', home_url( '/' ) );
                            }
                        }
                        ?>
                        <a href="<?php echo esc_url( $featured_archive_url ); ?>"
                            class="resource-card resource-card--placeholder fade-up"
                            aria-label="<?php esc_attr_e( 'View all handpicked resources', 'ai-awareness-day' ); ?>">
                            <div class="resource-card__placeholder-inner">
                                <span class="resource-card__placeholder-title"><?php esc_html_e( 'View all handpicked resources', 'ai-awareness-day' ); ?></span>
                                <span class="resource-card__placeholder-desc"><?php esc_html_e( 'Browse games & learning tools', 'ai-awareness-day' ); ?></span>
                            </div>
                        </a>
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

