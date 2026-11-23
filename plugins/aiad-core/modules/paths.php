<?php
/**
 * Paths module: file paths and URLs inside this plugin that work from either copy of it.
 *
 * Modules run from wp-content/plugins/aiad-core/ when the plugin is active, and from the theme's bundled copy
 * (themes/ai-awareness-day/plugins/aiad-core/) when it isn't. AIAD_CORE_DIR / AIAD_CORE_URL only exist in the first
 * case, so modules that ship their own assets use these instead.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_PATHS', __FILE__ );

/**
 * Absolute path to a file in this plugin, e.g. aiad_core_path( 'assets/css/certificate-showcase.css' ).
 */
function aiad_core_path( string $relative = '' ): string {
	return dirname( __DIR__ ) . '/' . ltrim( $relative, '/' );
}

/**
 * URL of a file in this plugin, from whichever copy is running.
 */
function aiad_core_url( string $relative = '' ): string {
	$root  = wp_normalize_path( dirname( __DIR__ ) );
	$theme = trailingslashit( wp_normalize_path( get_template_directory() ) );
	// Inside the theme, plugins_url() cannot build a working address, so build it from the theme's address instead.
	$base = 0 === strpos( $root, $theme )
		? trailingslashit( get_template_directory_uri() ) . substr( $root, strlen( $theme ) )
		: plugins_url( '', __DIR__ );
	return trailingslashit( $base ) . ltrim( $relative, '/' );
}

/**
 * Version string for a file in this plugin, from its modified time, so browsers fetch it again whenever it changes.
 */
function aiad_core_asset_version( string $relative ): string {
	$file = aiad_core_path( $relative );
	if ( file_exists( $file ) ) {
		return (string) filemtime( $file );
	}
	return defined( 'AIAD_CORE_VERSION' ) ? AIAD_CORE_VERSION : '1';
}
