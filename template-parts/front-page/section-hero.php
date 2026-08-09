<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$hero_marquee = function_exists( 'aiad_get_hero_partner_marquee_entries' )
    ? aiad_get_hero_partner_marquee_entries()
    : array();
?>
<section class="hero-section <?php echo esc_attr( $text_alignment_class ); ?>" id="hero">
    <div class="container">
        <div class="hero-title-block fade-up<?php echo ! empty( $hero_marquee ) ? ' hero-title-block--partner-marquee' : ''; ?>">
            <?php
            $defaults = aiad_get_customizer_defaults();
            ?>
            <p class="hero-date">
                <?php
                $hero_date = (string) get_theme_mod( 'aiad_hero_date', $defaults['aiad_hero_date'] );
                if ( preg_match( '/^(.*?)\s+(\d{4})$/u', trim( $hero_date ), $hero_date_parts ) ) {
                    echo esc_html( $hero_date_parts[1] );
                    echo '<span class="hero-date__year">' . esc_html( $hero_date_parts[2] ) . '</span>';
                } else {
                    echo esc_html( $hero_date );
                }
                ?>
            </p>

            <p class="hero-slogan">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_slogan', $defaults['aiad_hero_slogan'] ) ); ?>
            </p>
            <p class="hero-subtitle">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_subtitle', $defaults['aiad_hero_subtitle'] ) ); ?>
            </p>

            <div class="hero-cta">
                <a href="#contact" class="hero-cta__btn hero-cta__btn--primary">
                    <?php esc_html_e( 'Register Your School', 'ai-awareness-day' ); ?>
                </a>
                <a href="#campaign" class="hero-cta__btn hero-cta__btn--secondary">
                    <?php esc_html_e( 'Learn More', 'ai-awareness-day' ); ?>
                </a>
            </div>

            <?php
            if ( ! empty( $hero_marquee ) ) :
                $marquee_count = count( $hero_marquee );
                // Slower when more logos so the strip does not feel frantic.
                $marquee_secs = (int) min( 90, max( 28, $marquee_count * 5 ) );
                ?>
            <div class="hero-partner-marquee" role="region"
                aria-label="<?php esc_attr_e( 'Partner organisations', 'ai-awareness-day' ); ?>">
                <div class="hero-partner-marquee__viewport">
                    <div class="hero-partner-marquee__track"
                        style="<?php echo esc_attr( '--hero-marquee-duration: ' . $marquee_secs . 's' ); ?>">
                        <?php foreach ( array( 1, 2 ) as $_dup ) : ?>
                        <ul class="hero-partner-marquee__list">
                            <?php foreach ( $hero_marquee as $row ) : ?>
                            <li class="hero-partner-marquee__item">
                                <a class="hero-partner-marquee__link" href="<?php echo esc_url( $row['href'] ); ?>" data-partner-id="<?php echo esc_attr( (string) $row['id'] ); ?>">
                                    <img class="hero-partner-marquee__img"
                                        src="<?php echo esc_url( $row['img'] ); ?>"
                                        alt="<?php echo esc_attr( $row['title'] ); ?>"
                                        width="240"
                                        height="120"
                                        loading="lazy"
                                        decoding="async" />
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
