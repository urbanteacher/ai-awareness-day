<?php
/**
 * Front page section: Featured resources (partner resources) + LinkedIn card
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();

$featured_resources = aiad_featured_resources_query(); // Picked in Appearance > Edit Homepage, else the first three.

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

<?php get_template_part( 'template-parts/components/featured-resource-tiles', null, array( 'query' => $featured_resources ) ); ?>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        endif;

        get_template_part( 'template-parts/components/linkedin-card', null, array( 'url' => get_theme_mod( 'aiad_linkedin_post_url', '' ), 'text_alignment_class' => $text_alignment_class ) );
        ?>

