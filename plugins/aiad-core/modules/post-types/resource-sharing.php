<?php
/**
 * Share buttons on a single resource: the Web Share API with pre-written messages, a printable card and copy-link
 * (assets/js/resource-sharing.js). Moved from the theme's inc/setup.php, as social sharing is plugin territory.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Queue the share script on single resource pages.
 */
function aiad_enqueue_resource_sharing(): void {
	if ( ! is_singular( 'resource' ) ) {
		return;
	}
	wp_enqueue_script(
		'aiad-resource-sharing',
		aiad_core_url( 'assets/js/resource-sharing.js' ),
		array(),
		aiad_core_asset_version( 'assets/js/resource-sharing.js' ),
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aiad_enqueue_resource_sharing' );
