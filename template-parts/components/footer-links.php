<?php
/**
 * The footer's download and resource links (newsletter, press release, asset pack, implementation guide): the
 * footer template part's footer links block (blocks/footer-links) prints them. The addresses are set in the Customizer
 * and on their pages; a link without one shows as pending.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}
?>
        <?php
        $defaults      = aiad_get_customizer_defaults();
        $press_url = function_exists( 'aiad_get_press_release_public_url' ) ? aiad_get_press_release_public_url() : '';
        $resource_links = array(
            __( 'Newsletter', 'ai-awareness-day' )           => get_theme_mod( 'aiad_newsletter_url', 'https://aiawarenessday.beehiiv.com/p/ai-awareness-day-launched' ),
            __( 'Press Release', 'ai-awareness-day' )        => $press_url,
            __( 'Asset Pack', 'ai-awareness-day' )           => function_exists( 'aiad_get_assets_pack_public_url' ) ? aiad_get_assets_pack_public_url() : get_theme_mod( 'aiad_asset_pack_url', '' ),
            __( 'Implementation Guide', 'ai-awareness-day' ) => get_theme_mod( 'aiad_implementation_guide_url', '' ),
        ); ?>
        <nav class="footer-links" aria-label="<?php esc_attr_e( 'Downloads and resources', 'ai-awareness-day' ); ?>">
            <?php foreach ( $resource_links as $label => $url ) :
                if ( $url ) : ?>
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a>
                <?php else : ?>
                    <span class="footer-links__pending"><?php echo esc_html( $label ); ?></span>
                <?php endif;
            endforeach; ?>
        </nav>
