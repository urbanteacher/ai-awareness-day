<?php
/**
 * Custom tables.
 *
 * All times are stored in UTC.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Database {

	/** Bump when a table changes so dbDelta runs again. */
	const DB_VERSION = 1;

	const OPTION = 'aiadn_db_version';

	public static function table( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'aiadn_' . $name;
	}

	public static function create_tables(): void {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$schools = self::table( 'schools' );
		$members = self::table( 'members' );
		$codes   = self::table( 'login_codes' );
		$tokens  = self::table( 'tokens' );
		$pins    = self::table( 'class_pins' );

		$sql = array();

		// One row per school. The school code is created once the lead teacher verifies their email.
		$sql[] = "CREATE TABLE {$schools} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(12) DEFAULT NULL,
			name varchar(255) NOT NULL DEFAULT '',
			name_key varchar(255) NOT NULL DEFAULT '',
			postcode varchar(12) NOT NULL DEFAULT '',
			age_phases varchar(60) NOT NULL DEFAULT '',
			mat_name varchar(255) NOT NULL DEFAULT '',
			partner_ref varchar(120) NOT NULL DEFAULT '',
			slt_email varchar(255) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'pending_email',
			slt_approved_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY name_key (name_key),
			KEY status (status)
		) {$charset};";

		// Teachers and SLT. No WordPress accounts, no passwords.
		$sql[] = "CREATE TABLE {$members} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			email varchar(255) NOT NULL DEFAULT '',
			name varchar(255) NOT NULL DEFAULT '',
			job_title varchar(120) NOT NULL DEFAULT '',
			role varchar(10) NOT NULL DEFAULT 'teacher',
			email_verified_at datetime DEFAULT NULL,
			last_login_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY school_email (school_id,email),
			KEY email (email)
		) {$charset};";

		// Six-digit codes. Only a hash is stored. ref is what the browser holds while the person checks their email.
		$sql[] = "CREATE TABLE {$codes} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ref varchar(40) NOT NULL DEFAULT '',
			school_id bigint(20) unsigned NOT NULL,
			email varchar(255) NOT NULL DEFAULT '',
			purpose varchar(10) NOT NULL DEFAULT 'signin',
			role_hint varchar(10) NOT NULL DEFAULT '',
			code_hash varchar(64) NOT NULL DEFAULT '',
			attempts tinyint(3) unsigned NOT NULL DEFAULT 0,
			expires_at datetime NOT NULL,
			used_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY ref (ref),
			KEY lookup (school_id,email,purpose)
		) {$charset};";

		// Link tokens (SLT approval now; judge and teacher links later). Only a hash is stored.
		$sql[] = "CREATE TABLE {$tokens} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			kind varchar(20) NOT NULL DEFAULT '',
			token_hash varchar(64) NOT NULL DEFAULT '',
			hint varchar(8) NOT NULL DEFAULT '',
			expires_at datetime NOT NULL,
			used_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY token_hash (token_hash),
			KEY school_kind (school_id,kind)
		) {$charset};";

		// Class PINs for students. Short-lived; the teacher can see the current one.
		$sql[] = "CREATE TABLE {$pins} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			pin varchar(4) NOT NULL DEFAULT '',
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY school_expiry (school_id,expires_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $sql as $statement ) {
			dbDelta( $statement );
		}
		update_option( self::OPTION, self::DB_VERSION, false );
	}

	public static function maybe_upgrade(): void {
		if ( (int) get_option( self::OPTION, 0 ) >= self::DB_VERSION ) {
			return;
		}
		self::create_tables();
	}

	/** True when the schools table exists (used to recover when hosting blocks plugin activation). */
	public static function tables_exist(): bool {
		global $wpdb;
		$table = self::table( 'schools' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}
}
