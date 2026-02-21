<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
<section class="hero-section <?php echo esc_attr( $text_alignment_class ); ?>" id="hero">
    <div class="container">
        <div class="hero-title-block fade-up">
            <p class="hero-date">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_date', aiad_get_customizer_defaults()['aiad_hero_date'] ) ); ?>
            </p>
            <div class="hero-logo">
                <?php
                $defaults      = aiad_get_customizer_defaults();
                $hero_logo_id  = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
                $hero_logo_url = $hero_logo_id ? wp_get_attachment_image_url( $hero_logo_id, 'full' ) : '';
                if ( $hero_logo_url ) :
                    ?><img src="<?php echo esc_url( $hero_logo_url ); ?>"
                        alt="<?php echo esc_attr( get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ); ?>"
                        class="hero-logo__img"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';" />
                    <span class="hero-logo__placeholder" aria-hidden="true"
                        style="display: none;"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span><?php
                else :
                    ?><span class="hero-logo__placeholder"
                        aria-hidden="true"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span><?php
                endif;
                ?>
                <p class="hero-slogan">
                    <?php echo esc_html( get_theme_mod( 'aiad_hero_slogan', $defaults['aiad_hero_slogan'] ) ); ?>
                </p>
                <p class="section-desc">
                    <?php echo esc_html( get_theme_mod( 'aiad_hero_subtitle', $defaults['aiad_hero_subtitle'] ) ); ?>
                </p>
            </div>
        </div>
    </div>
</section>
