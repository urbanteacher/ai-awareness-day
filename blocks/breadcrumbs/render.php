<?php
/**
 * Breadcrumbs block: the trail header.php prints below the header (aiad_render_breadcrumbs(), in the aiad-core SEO
 * module), when Customizer > breadcrumbs is on.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( get_theme_mod( 'aiad_show_breadcrumbs', false ) && function_exists( 'aiad_render_breadcrumbs' ) ) {
	aiad_render_breadcrumbs();
}
