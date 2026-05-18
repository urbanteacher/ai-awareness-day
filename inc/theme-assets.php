<?php
/**
 * Theme asset manifests and enqueue helpers.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CSS bundle groups for optional concat build (scripts/build-css-bundles.php).
 *
 * @return array<string, array<int, string>>
 */
function aiad_css_bundle_manifest(): array {
	static $manifest = null;

	if ( null === $manifest ) {
		$path = AIAD_DIR . '/assets/css/bundle-manifest.php';
		$manifest = file_exists( $path ) ? require $path : array();
		if ( ! is_array( $manifest ) ) {
			$manifest = array();
		}
	}

	return $manifest;
}

/**
 * Flat ordered list of modular stylesheet paths under assets/css/.
 *
 * @return array<int, string>
 */
function aiad_modular_stylesheet_paths(): array {
	$paths = array();
	foreach ( aiad_css_bundle_manifest() as $files ) {
		if ( ! is_array( $files ) ) {
			continue;
		}
		foreach ( $files as $file ) {
			$paths[] = $file;
		}
	}
	return $paths;
}

/**
 * Enqueue modular theme CSS (source of truth — no generated bundles on the live site).
 */
function aiad_enqueue_modular_theme_styles(): void {
	foreach ( aiad_modular_stylesheet_paths() as $file ) {
		$handle    = 'aiad-' . str_replace( array( '/', '.css' ), array( '-', '' ), $file );
		$file_path = AIAD_DIR . '/assets/css/' . $file;
		$version   = file_exists( $file_path ) ? (string) filemtime( $file_path ) : AIAD_VERSION;

		wp_enqueue_style(
			$handle,
			AIAD_URI . '/assets/css/' . $file,
			array( 'aiad-style' ),
			$version,
			'all'
		);
	}
}
