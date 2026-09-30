<?php
/**
 * Front page section: Contact (Get Involved form)
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();
?>
<section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="contact">
    <div class="container">
                <div class="contact-wrapper">

                    <div class="contact-info fade-up">
                        <span class="section-label"><?php esc_html_e('Contact Us', 'ai-awareness-day'); ?></span>
                        <?php
                        $defaults = aiad_get_customizer_defaults();
                        ?>
                        <h2 class="section-title">
                            <?php echo esc_html(get_theme_mod('aiad_contact_title', $defaults['aiad_contact_title'])); ?>
                        </h2>
                        <p class="section-desc">
                            <?php echo wp_kses_post(get_theme_mod('aiad_contact_desc', $defaults['aiad_contact_desc'])); ?>
                        </p>

                    </div>

<?php get_template_part( 'template-parts/components/contact-form' ); ?>

                </div>
    </div>
</section>
