<?php
/**
 * The main menu and its toggle for small screens: the header template part's site navigation block
 * (blocks/site-navigation) prints them. The menu is Appearance > Menus > Primary Navigation; main.js opens it.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
            <button class="nav-toggle" id="nav-toggle" aria-label="Toggle navigation" aria-expanded="false"
                aria-controls="main-nav">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <nav class="main-navigation" id="main-nav" role="navigation"
                aria-label="<?php esc_attr_e('Main Navigation', 'ai-awareness-day'); ?>">
                <?php
                if (has_nav_menu('primary')) {
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'walker' => new AIAD_Nav_Walker(),
                        'fallback_cb' => 'aiad_fallback_menu',
                    ));
                } else {
                    aiad_fallback_menu();
                }
                ?>
            </nav>
