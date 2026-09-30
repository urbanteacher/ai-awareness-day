<?php
/**
 * The Footer Content widget area, when it has widgets: the footer template part's footer widgets block
 * (blocks/footer-widgets) prints it.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
        <?php if ( is_active_sidebar( 'footer_content' ) ) : ?>
        <div class="footer-widgets">
            <?php dynamic_sidebar( 'footer_content' ); ?>
        </div>
        <?php endif; ?>
