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

            <div class="resource-tiles">
                <?php
                while ( $free_resources->have_posts() ) :
                    $free_resources->the_post();
                    get_template_part( 'template-parts/components/resource-tile', null, array() );
                endwhile;
                ?>
                <?php
                $resources_archive_url = get_post_type_archive_link( 'resource' );
                if ( ! $resources_archive_url ) {
                    $resources_archive_url = home_url( '/resources/' );
                    if ( get_option( 'permalink_structure' ) === '' ) {
                        $resources_archive_url = add_query_arg( 'post_type', 'resource', home_url( '/' ) );
                    }
                }
                ?>
            </div>
            <a class="resource-tiles__more" href="<?php echo esc_url( $resources_archive_url ); ?>"><?php esc_html_e( 'View all free resources', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></a>
        </div>
    </section>
    <?php
    wp_reset_postdata();
endif;
