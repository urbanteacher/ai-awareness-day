<?php
/**
 * CLI helper: export copy-tiers JSON from legacy PHP tier files.
 *
 * Usage (from WordPress root): wp eval-file wp-content/plugins/ai-risk-readiness-benchmark/archive/bin/export-copy-tiers-json.php
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	// Standalone bootstrap for local dev without full WP.
	define( 'ABSPATH', __DIR__ . '/' );
	if ( ! function_exists( '__' ) ) {
		/**
		 * @param string $text Text.
		 * @param string $domain Domain.
		 * @return string
		 */
		function __( $text, $domain = 'default' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return $text;
		}
	}
	if ( ! function_exists( 'wp_json_encode' ) ) {
		/**
		 * @param mixed $data Data.
		 * @param int   $options Options.
		 * @return string|false
		 */
		function wp_json_encode( $data, $options = 0 ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
			return json_encode( $data, $options ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}
	define( 'AIRB_PLUGIN_DIR', dirname( __DIR__, 2 ) . '/' );
	define( 'AIRB_USE_JSON_COPY', false );
	require_once AIRB_PLUGIN_DIR . 'includes/class-airb-copy-tiers.php';
}

AIRB_Copy_Tiers::export_missing_json_files();

echo "Export complete.\n";
