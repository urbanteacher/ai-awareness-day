<?php
/**
 * Breadcrumbs block: the trail header.php prints below the header (aiad_render_breadcrumbs(), in the aiad-core SEO
 * module), when Customizer > breadcrumbs is on.
 *
 * @package AI_Awareness_Day
 */

// This block prints the AI Awareness Day theme's markup with the theme's own functions; under another theme it
// prints nothing rather than stopping the page with a fatal error.
foreach ( array( 'aiad_site_value' ) as $aiad_theme_fn ) {
	if ( ! function_exists( $aiad_theme_fn ) ) {
		return;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( aiad_site_value( 'show_breadcrumbs', false ) && function_exists( 'aiad_render_breadcrumbs' ) ) {
	aiad_render_breadcrumbs();
}
