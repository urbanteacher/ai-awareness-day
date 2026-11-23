<?php
/**
 * The site's shared REST client for script modules: "aiad/rest" (assets/js/rest.js). It finds the REST routes through
 * the discovery link WordPress prints in the page head, so a cached page carries no address or token of its own.
 *
 * Registered here, not by the theme, because this plugin's own front-end modules (aiad/contact-form, aiad/tracking)
 * import it; the theme's modules (resource filters, timeline) import it too. Moved from the theme's inc/setup.php.
 * The theme loads this file from its bundled copy of the plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_REST_CLIENT', __FILE__ );

/**
 * Register the aiad/rest script module, before anything that imports it is queued.
 */
function aiad_register_rest_client_module(): void {
	wp_register_script_module( 'aiad/rest', aiad_core_url( 'assets/js/rest.js' ), array(), aiad_core_asset_version( 'assets/js/rest.js' ) );
}
add_action( 'wp_enqueue_scripts', 'aiad_register_rest_client_module', 5 );
