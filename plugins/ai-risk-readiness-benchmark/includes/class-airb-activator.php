<?php
/**
 * Activation / deactivation.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin activation.
 */
class AIRB_Activator {

	/**
	 * Run on plugin activation.
	 */
	public static function activate(): void {
		AIRB_Database::create_table();
		AIRB_Config::maybe_seed_defaults();
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
