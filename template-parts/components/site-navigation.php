<?php
/**
 * The main menu and its toggle for small screens: the header template part's site navigation block
 * (blocks/site-navigation) prints them. The links come from aiad_fallback_menu() in inc/setup.php; main.js opens it.
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
                // The theme sets the header links (see aiad_fallback_menu()), so they always match the homepage.
                aiad_fallback_menu();
                ?>
            </nav>
