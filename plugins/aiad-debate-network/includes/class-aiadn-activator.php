<?php
/**
 * Activation / deactivation.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Activator {

	public static function activate(): void {
		AIADN_Database::create_tables();
		AIADN_Front::add_rewrites();
		flush_rewrite_rules();
	}

	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
