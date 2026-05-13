<?php
/**
 * Front page section: Free Resources (AI Awareness Activities)
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

// Check if user has manually selected resources via Homepage Editor
$selected_ids = array();
for ( $i = 1; $i <= 6; $i++ ) {
    $id = absint( get_theme_mod( 'aiad_free_resource_' . $i, 0 ) );
    if ( $id > 0 ) {
        $selected_ids[] = $id;
    }
}

// If no resources selected, don't render the section
if ( empty( $selected_ids ) ) {
    return;
}

// Query the selected resources
$free_resources = new WP_Query( array(
    'post_type'      => 'resource',
    'post_status'    => 'publish',
    'posts_per_page' => 6,
    'post__in'       => $selected_ids,
    'orderby'        => 'post__in',
) );

// Get custom section title/description
$section_title = get_theme_mod( 'aiad_free_resources_title', __( 'Free Resources', 'ai-awareness-day' ) );
$section_desc = get_theme_mod( 'aiad_free_resources_desc', __( 'Ready-to-use activities and materials for AI Awareness Day.', 'ai-awareness-day' ) );

if ( $free_resources->have_posts() ):
    ?>
    <section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="free-resources">
        <div class="container">
            <div class="fade-up">
                <span class="section-label"><?php esc_html_e( 'AI Awareness Activities', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php echo esc_html( $section_title ); ?></h2>
                <p class="section-desc">
                    <?php echo esc_html( $section_desc ); ?>
                </p>
            </div>

            <div class="resources-grid" style="margin-top: 2rem;">
                <?php while ( $free_resources->have_posts() ):
                    $free_resources->the_post();
                    $themes = get_the_terms( get_the_ID(), 'resource_principle' );
                    $durations = get_the_terms( get_the_ID(), 'resource_duration' );
                    $duration_labels = ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_resource_duration_term_labels' ) )
                        ? aiad_resource_duration_term_labels( $durations )
                        : array();
                    $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                    $download_url = get_post_meta( get_the_ID(), '_aiad_download_url', true );
                    $activity_terms = get_the_terms( get_the_ID(), 'activity_type' );
                    $placeholder_type = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
                        ? $activity_terms[0]->name
                        : ( ! empty( $duration_labels ) ? $duration_labels[0] : '—' );
                    ?>
                    <?php
                    $theme_slug   = in_array( strtolower( $theme_name ), array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true )
                        ? strtolower( $theme_name ) : '';
                    $format_label = ( $activity_terms && ! is_wp_error( $activity_terms ) && ! empty( $activity_terms ) )
                        ? strtoupper( $activity_terms[0]->name ) : 'SLIDE';
                    $duration_str = ! empty( $duration_labels ) ? strtoupper( $duration_labels[0] ) : '';
                    $article_class = 'resource-card resource-card--pointed fade-up';
                    if ( $theme_slug ) {
                        $article_class .= ' resource-card--' . $theme_slug;
                    }
                    ?>
                    <article class="<?php echo esc_attr( $article_class ); ?>">
                        <a href="<?php the_permalink(); ?>" class="resource-card__hero" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
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
                            <span class="resource-card__format-label"><?php echo esc_html( $format_label ); ?></span>
                            <a href="<?php the_permalink(); ?>" class="resource-card__title-below"><?php echo esc_html( get_the_title() ); ?></a>
                            <?php if ( has_excerpt() ): ?>
                                <p class="resource-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
                            <?php endif; ?>
                            <p class="resource-card__action">
                                <a href="<?php the_permalink(); ?>" class="resource-card__link"><?php esc_html_e( 'View resource', 'ai-awareness-day' ); ?> →</a>
                            </p>
                        </div>
                    </article>
                <?php endwhile; ?>
                <?php
                $resources_archive_url = get_post_type_archive_link( 'resource' );
                if ( ! $resources_archive_url ) {
                    $resources_archive_url = home_url( '/resources/' );
                    if ( get_option( 'permalink_structure' ) === '' ) {
                        $resources_archive_url = add_query_arg( 'post_type', 'resource', home_url( '/' ) );
                    }
                }
                ?>
                <a href="<?php echo esc_url( $resources_archive_url ); ?>"
                    class="resource-card resource-card--placeholder fade-up"
                    aria-label="<?php esc_attr_e( 'View all free resources', 'ai-awareness-day' ); ?>">
                    <div class="resource-card__placeholder-inner">
                        <span class="resource-card__placeholder-title"><?php esc_html_e( 'View all free resources', 'ai-awareness-day' ); ?></span>
                        <span class="resource-card__placeholder-desc"><?php esc_html_e( 'Explore activities', 'ai-awareness-day' ); ?></span>
                    </div>
                </a>
            </div>
        </div>
    </section>
    <?php
    wp_reset_postdata();
endif;
