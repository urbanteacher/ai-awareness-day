<?php
/**
 * AI Awareness Day Theme Functions
 *
 * @package AI_Awareness_Day
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'AIAD_VERSION', '1.5.0' );

if ( ! defined( 'AIAD_DIR' ) ) {
    define( 'AIAD_DIR', __DIR__ );
}
if ( ! defined( 'AIAD_URI' ) ) {
    define( 'AIAD_URI', get_template_directory_uri() );
}

$aiad_dir = AIAD_DIR;

/**
 * Load a module that moved to the aiad-core plugin, from the plugin's copy in this theme, unless the active plugin
 * already provides it. Keeps the theme working with or without the plugin.
 */
function aiad_require_core_module( string $module ): void {
	if ( function_exists( 'aiad_core_provides' ) && aiad_core_provides( $module ) ) {
		return;
	}
	require_once AIAD_DIR . '/plugins/aiad-core/modules/' . $module . '.php';
}

// Loaded first: modules that ship their own assets build their paths and URLs with it.
aiad_require_core_module( 'paths' );

if ( is_admin() ) {
    require_once $aiad_dir . '/admin/class-aiad-homepage-editor.php';
}
aiad_require_core_module( 'admin' );

/*
 * Despite the filename this is not admin-only: footer.php calls
 * aiad_get_assets_pack_public_url() to link the Assets Pack, and the init hook
 * that creates the page lives here too. Loaded only under is_admin(), neither
 * existed on the front end, so the footer's function_exists() check failed and
 * the link fell back to an empty Customizer URL and rendered as dead text.
 * Its only other hook is wp_dashboard_setup, which simply never fires here.
 */
require_once $aiad_dir . '/inc/admin-assets-pack.php';

require_once $aiad_dir . '/inc/theme-assets.php';
require_once $aiad_dir . '/inc/setup.php';
require_once $aiad_dir . '/inc/helpers.php';
aiad_require_core_module( 'helpers-data' );
aiad_require_core_module( 'post-types' );
require_once $aiad_dir . '/inc/walkthrough.php';
require_once $aiad_dir . '/inc/migrate-2027-branding.php';
require_once $aiad_dir . '/inc/customizer.php';
require_once $aiad_dir . '/inc/front-page-layout.php';
aiad_require_core_module( 'contact' );
aiad_require_core_module( 'resource-filter' );
aiad_require_core_module( 'certificates' );
require_once $aiad_dir . '/inc/timeline.php';
aiad_require_core_module( 'live-sessions' );
require_once $aiad_dir . '/inc/live-sessions.php';
aiad_require_core_module( 'tracking' );
aiad_require_core_module( 'ai-tools' );
require_once $aiad_dir . '/inc/tools.php';
aiad_require_core_module( 'seo' );
aiad_require_core_module( 'tools' );
aiad_require_core_module( 'survey' );
require_once $aiad_dir . '/inc/bundled-plugins.php';
aiad_require_core_module( 'benchmark-content' );
aiad_require_core_module( 'certificate-showcase' );
require_once $aiad_dir . '/inc/hub-resource-page.php';
require_once $aiad_dir . '/inc/national-conversation-page.php';
