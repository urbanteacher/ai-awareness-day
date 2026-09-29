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
		AIADN_Reminders::unschedule();
		AIADN_Privacy::unschedule();
		flush_rewrite_rules();
	}
}
