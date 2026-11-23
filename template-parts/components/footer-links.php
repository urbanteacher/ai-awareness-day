<?php
/**
 * The footer's download and resource links (newsletter, press release, asset pack, implementation guide): the
 * footer template part's footer links block (blocks/footer-links) prints them. The addresses are set on Settings → AI Awareness Day
 * and on their pages; a link without one stays hidden until it has one.
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
            __( 'Newsletter', 'ai-awareness-day' )           => aiad_site_value( 'newsletter_url', 'https://aiawarenessday.beehiiv.com/p/november-2026' ),
            __( 'Press Release', 'ai-awareness-day' )        => $press_url,
            __( 'Asset Pack', 'ai-awareness-day' )           => function_exists( 'aiad_get_assets_pack_public_url' ) ? aiad_get_assets_pack_public_url() : aiad_site_value( 'asset_pack_url', '' ),
            __( 'Implementation Guide', 'ai-awareness-day' ) => aiad_site_value( 'implementation_guide_url', '' ),
        );
        // Shown once the page exists (it is created the first time the theme loads: inc/editable-pages.php).
        if ( function_exists( 'aiad_editable_page_post' ) && aiad_editable_page_post( 'ai-literacy-periodic-table' ) ) {
            $resource_links[ __( 'AI Literacy Periodic Table', 'ai-awareness-day' ) ] = aiad_literacy_table_url();
        } ?>
        <nav class="footer-links" aria-label="<?php esc_attr_e( 'Downloads and resources', 'ai-awareness-day' ); ?>">
            <?php foreach ( array_filter( $resource_links ) as $label => $url ) : ?>
                    <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $label ); ?></a>
            <?php endforeach; ?>
        </nav>
