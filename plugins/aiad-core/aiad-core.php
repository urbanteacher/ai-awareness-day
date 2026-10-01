<?php
/**
 * Plugin Name:       AI Awareness Day Core
 * Plugin URI:        https://aiawarenessday.co.uk/
 * Description:       Site functionality that should survive a theme change: post types, meta, tools, certificates, survey, AJAX and the AI Awareness Day blocks. The theme keeps presentation only.
 * Version:           0.1.0
 * Requires at least: 7.1
 * Requires PHP:      8.0
 * Author:            AI Awareness Day
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       aiad-core
 * Update URI:        false
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIAD_CORE_VERSION', '0.1.0' );
define( 'AIAD_CORE_FILE', __FILE__ );
define( 'AIAD_CORE_DIR', plugin_dir_path( __FILE__ ) );
// Same as the Debate Network plugin: when the theme loads this plugin from its own plugins/ folder, plugin_dir_url()
// cannot build a working address, so build it from the theme's address instead.
$aiad_core_dir   = wp_normalize_path( plugin_dir_path( __FILE__ ) );
$aiad_core_theme = trailingslashit( wp_normalize_path( get_template_directory() ) );
define( 'AIAD_CORE_URL', 0 === strpos( $aiad_core_dir, $aiad_core_theme ) ? trailingslashit( get_template_directory_uri() ) . substr( $aiad_core_dir, strlen( $aiad_core_theme ) ) : plugin_dir_url( __FILE__ ) );
unset( $aiad_core_dir, $aiad_core_theme );

require_once AIAD_CORE_DIR . 'includes/modules.php';
require_once AIAD_CORE_DIR . 'includes/blocks.php';
require_once AIAD_CORE_DIR . 'includes/editors.php';
require_once AIAD_CORE_DIR . 'includes/settings-screen.php';

aiad_core_load_modules();
