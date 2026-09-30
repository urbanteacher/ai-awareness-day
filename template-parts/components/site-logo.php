<?php
/**
 * The site logo: the header template part's site logo block (blocks/site-logo) prints it.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo" aria-label="<?php esc_attr_e( 'AI Awareness Day 2027 — Keep Humans in the Loop', 'ai-awareness-day' ); ?>">
                <img src="<?php echo esc_url( AIAD_URI . '/assets/brand/aiad27/aiad27-lockup.svg' ); ?>" alt="" aria-hidden="true" class="site-logo__img" />
            </a>
