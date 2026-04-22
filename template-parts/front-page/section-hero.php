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
            <?php
            $defaults      = aiad_get_customizer_defaults();
            $hero_logo_id  = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
            $hero_logo_url = $hero_logo_id ? wp_get_attachment_image_url( $hero_logo_id, 'full' ) : '';
            ?>
            <p class="hero-date">
                <?php echo esc_html( get_theme_mod( 'aiad_hero_date', $defaults['aiad_hero_date'] ) ); ?>
            </p>
            <div class="hero-logo">
                <?php if ( $hero_logo_url ) : ?>
                    <img src="<?php echo esc_url( $hero_logo_url ); ?>"
                        alt="<?php echo esc_attr( get_theme_mod( 'aiad_hero_title', $defaults['aiad_hero_title'] ) ); ?>"
                        class="hero-logo__img"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';" />
                    <span class="hero-logo__placeholder" aria-hidden="true"
                        style="display: none;"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span>
                <?php else : ?>
                    <span class="hero-logo__placeholder"
                        aria-hidden="true"><?php esc_html_e( 'Logo', 'ai-awareness-day' ); ?></span>
                <?php endif; ?>
            </div>

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
            $event_date   = apply_filters( 'aiad_timeline_event_date', '2026-06-04' );
            $event_ts_utc = strtotime( $event_date . ' 00:00:00 UTC' );
            // UTC midnight as ms so JS never parses a date string (Safari/locale safe).
            $event_ts_ms = false !== $event_ts_utc ? $event_ts_utc * 1000 : 0;
            // Server-render days so the number shows before JS or if JS is blocked.
            $days_until = false !== $event_ts_utc
                ? max( 0, (int) floor( ( $event_ts_utc - time() ) / 86400 ) )
                : 0;
            ?>
            <div class="hero-countdown"
                data-event-date="<?php echo esc_attr( $event_date ); ?>"
                data-event-ts="<?php echo esc_attr( (string) $event_ts_ms ); ?>"
                aria-live="polite">
                <div class="hero-countdown__item">
                    <span class="hero-countdown__value" data-unit="days"><?php echo esc_html( (string) $days_until ); ?></span>
                    <span class="hero-countdown__label"><?php esc_html_e( 'Days', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="hero-countdown__item">
                    <span class="hero-countdown__value" data-unit="hours">00</span>
                    <span class="hero-countdown__label"><?php esc_html_e( 'Hours', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="hero-countdown__item">
                    <span class="hero-countdown__value" data-unit="minutes">00</span>
                    <span class="hero-countdown__label"><?php esc_html_e( 'Minutes', 'ai-awareness-day' ); ?></span>
                </div>
                <div class="hero-countdown__item">
                    <span class="hero-countdown__value" data-unit="seconds">00</span>
                    <span class="hero-countdown__label"><?php esc_html_e( 'Seconds', 'ai-awareness-day' ); ?></span>
                </div>
            </div>

            <?php
            // Live stat counts — pulled directly from WP so they stay current.
            $resource_count  = ( wp_count_posts( 'resource' )->publish ?? 0 )
                             + ( wp_count_posts( 'featured_resource' )->publish ?? 0 );
            $partner_count   = wp_count_posts( 'partner' )->publish ?? 0;
            $tool_count      = wp_count_posts( 'ai_tool' )->publish ?? 0;
            $school_count    = function_exists( 'aiad_timeline_schools_registered_count' )
                             ? aiad_timeline_schools_registered_count()
                             : (int) get_option( 'aiad_school_pledge_count', 0 );
            $stats = array(
                array(
                    'value' => $school_count,
                    'label' => __( 'Schools', 'ai-awareness-day' ),
                    'url'   => '#contact',
                ),
                array(
                    'value' => $resource_count,
                    'label' => __( 'Resources', 'ai-awareness-day' ),
                    'url'   => get_post_type_archive_link( 'resource' ) ?: home_url( '/resources/' ),
                ),
                array(
                    'value' => $partner_count,
                    'label' => __( 'Partners', 'ai-awareness-day' ),
                    'url'   => get_post_type_archive_link( 'partner' ) ?: home_url( '/partners/' ),
                ),
                array(
                    'value' => $tool_count,
                    'label' => __( 'AI tools', 'ai-awareness-day' ),
                    'url'   => home_url( '/ai-tools/' ),
                ),
            );
            ?>
            <div class="hero-stats">
                <?php foreach ( $stats as $stat ) : ?>
                <a href="<?php echo esc_url( $stat['url'] ); ?>" class="hero-stats__item">
                    <span class="hero-stats__value"><?php echo esc_html( number_format_i18n( $stat['value'] ) ); ?></span>
                    <span class="hero-stats__label"><?php echo esc_html( $stat['label'] ); ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
