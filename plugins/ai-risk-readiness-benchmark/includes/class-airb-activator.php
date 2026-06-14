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
		AIRB_Events::create_table();
		AIRB_Leads::create_table();
		update_option( 'airb_db_version', AIRB_Database::DB_VERSION, false );
		update_option( 'airb_leads_db_version', AIRB_Leads::DB_VERSION, false );
		AIRB_Config::maybe_seed_defaults();
		AIRB_Hub_Pages::maybe_seed();
		flush_rewrite_rules();
	}

	/**
	 * Run on plugin deactivation.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
