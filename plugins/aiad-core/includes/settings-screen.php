<?php
/**
 * Site settings: one screen, Settings → AI Awareness Day.
 *
 * The campaign and SEO options are registered for REST, so the screen reads and saves them through core's own
 * settings endpoint (/wp/v2/settings) and the `site` entity in @wordpress/core-data, as WordPress documents for
 * a settings screen. The screen is React (src/settings/, built to build/settings/) on core's @wordpress/components.
 * It replaces the two Settings API pages these options had (Campaign & contact, SEO & sharing); their old addresses
 * redirect here. The options and the code that reads them are unchanged, so nothing that reads a setting moves.
 *
 * WordPress 7.1.2 does not give plugins DataForm (no `wp-dataviews` script or module is registered; the package is
 * only bundled into core's own admin pages), so the form is built from components. See docs/WP71-STANDARDISATION.md.
 *
 * @see https://developer.wordpress.org/reference/functions/register_setting/
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-core-data/
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The string properties of an object setting, for its REST schema.
 *
 * @param string[] $keys Property names.
 * @return array<string, array<string, string>>
 */
function aiad_core_string_properties( array $keys ): array {
	return array_fill_keys( $keys, array( 'type' => 'string' ) );
}

/**
 * Register the options the screen edits, for REST.
 *
 * They hold their own sanitiser (aiad_campaign_sanitize_settings(), aiad_seo_sanitize_settings()), which WordPress
 * runs on every write, REST included.
 */
function aiad_core_register_site_settings(): void {
	$campaign = aiad_core_string_properties( array( 'event_date', 'contact_email' ) );
	// A real date or nothing. The sanitiser holds the same rule for a write that did not come from the screen.
	$campaign['event_date']['pattern'] = '^(\d{4}-\d{2}-\d{2})?$';

	register_setting(
		'aiad_campaign',
		'aiad_campaign',
		array(
			'type'              => 'object',
			'label'             => __( 'Campaign & contact', 'aiad-core' ),
			'description'       => __( 'The event date and the contact form recipient.', 'aiad-core' ),
			'sanitize_callback' => 'aiad_campaign_sanitize_settings',
			'default'           => array(
				'event_date'    => '',
				'contact_email' => '',
			),
			'show_in_rest'      => array(
				'schema' => array(
					'type'                 => 'object',
					'properties'           => $campaign,
					'additionalProperties' => false,
				),
			),
		)
	);

	register_setting(
		'aiad_seo',
		'aiad_seo',
		array(
			'type'              => 'object',
			'label'             => __( 'SEO & sharing', 'aiad-core' ),
			'description'       => __( 'Site name, homepage sharing, social profiles and search-engine verification.', 'aiad-core' ),
			'sanitize_callback' => 'aiad_seo_sanitize_settings',
			'default'           => array(),
			'show_in_rest'      => array(
				'schema' => array(
					'type'                 => 'object',
					'properties'           => aiad_core_string_properties( array_keys( aiad_seo_setting_fields() ) ),
					'additionalProperties' => false,
				),
			),
		)
	);
}
add_action( 'init', 'aiad_core_register_site_settings' );

/**
 * Add the screen under Settings.
 */
function aiad_core_add_settings_screen(): void {
	add_options_page(
		__( 'AI Awareness Day', 'aiad-core' ),
		__( 'AI Awareness Day', 'aiad-core' ),
		'manage_options',
		'aiad-settings',
		'aiad_core_render_settings_screen'
	);
}
add_action( 'admin_menu', 'aiad_core_add_settings_screen' );

/**
 * The two pages that were here before the screen; anyone with the old address or bookmark lands on the new one.
 *
 * A page that is no longer registered is refused before admin_init runs, so this uses the hook core fires
 * just before it refuses.
 */
function aiad_core_redirect_old_settings_pages(): void {
	global $pagenow;
	$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( 'options-general.php' === $pagenow && in_array( $page, array( 'aiad-campaign', 'aiad-seo' ), true ) ) {
		wp_safe_redirect( add_query_arg( 'page', 'aiad-settings', admin_url( 'options-general.php' ) ) );
		exit;
	}
}
add_action( 'admin_page_access_denied', 'aiad_core_redirect_old_settings_pages', 1 );

/**
 * The screen's mount point. What the form needs from PHP (the dynamic help text) is JSON on the element, not a global.
 */
function aiad_core_render_settings_screen(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	// The first read copies each option from the values it used to be kept in, so the form opens with them.
	aiad_campaign_settings();
	aiad_seo_settings();

	$config = array(
		'defaultEventDate' => aiad_campaign_default_event_date(),
		'adminEmail'       => (string) get_option( 'admin_email' ),
	);
	printf(
		'<div class="wrap"><div id="aiad-settings-root" data-config="%s"></div></div>',
		esc_attr( wp_json_encode( $config ) )
	);
}

/**
 * Load the screen's script and style on it only.
 *
 * @param string $hook_suffix The admin screen.
 */
function aiad_core_enqueue_settings_screen( string $hook_suffix ): void {
	if ( 'settings_page_aiad-settings' !== $hook_suffix ) {
		return;
	}
	$asset_file = AIAD_CORE_DIR . 'build/settings/index.asset.php';
	if ( ! file_exists( $asset_file ) ) {
		return;
	}
	$asset = require $asset_file;
	wp_enqueue_script( 'aiad-core-settings', AIAD_CORE_URL . 'build/settings/index.js', $asset['dependencies'], $asset['version'], true );
	wp_set_script_translations( 'aiad-core-settings', 'aiad-core' );
	wp_enqueue_style( 'wp-components' );
	if ( file_exists( AIAD_CORE_DIR . 'build/settings/index.css' ) ) {
		wp_enqueue_style( 'aiad-core-settings', AIAD_CORE_URL . 'build/settings/index.css', array( 'wp-components' ), $asset['version'] );
	}
}
add_action( 'admin_enqueue_scripts', 'aiad_core_enqueue_settings_screen' );
