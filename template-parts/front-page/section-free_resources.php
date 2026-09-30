<?php
/**
 * Front page section: Free Resources (AI Awareness Activities)
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();

$free_resources = aiad_free_resources_query();
if ( ! $free_resources ) {
    return; // No resources picked in Appearance > Edit Homepage.
}

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

<?php get_template_part( 'template-parts/components/free-resource-tiles', null, array( 'query' => $free_resources ) ); ?>
        </div>
    </section>
    <?php
    wp_reset_postdata();
endif;
