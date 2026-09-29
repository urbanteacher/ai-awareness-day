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
	const DB_VERSION = 5;

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
		$debates = self::table( 'debates' );
		$events  = self::table( 'debate_events' );
		$judges  = self::table( 'judges' );
		$cards   = self::table( 'scorecards' );
		$issues  = self::table( 'issues' );
		$certs   = self::table( 'certificates' );
		$ratings = self::table( 'ratings' );
		$voice   = self::table( 'voice' );

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
			ref_id bigint(20) unsigned NOT NULL DEFAULT 0,
			hint varchar(8) NOT NULL DEFAULT '',
			expires_at datetime NOT NULL,
			used_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY token_hash (token_hash),
			KEY school_kind (school_id,kind,ref_id)
		) {$charset};";

		// Class PINs for students. Short-lived; the teacher can see the current one.
		$sql[] = "CREATE TABLE {$pins} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			pin varchar(4) NOT NULL DEFAULT '',
			purpose varchar(10) NOT NULL DEFAULT 'general',
			debate_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_by bigint(20) unsigned NOT NULL DEFAULT 0,
			expires_at datetime NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY school_expiry (school_id,expires_at)
		) {$charset};";

		// One row per fixture. The code (AID-XXXXX) is the public reference, never a password.
		$sql[] = "CREATE TABLE {$debates} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			code varchar(12) NOT NULL DEFAULT '',
			school_a_id bigint(20) unsigned NOT NULL,
			school_b_id bigint(20) unsigned DEFAULT NULL,
			a_member_id bigint(20) unsigned NOT NULL DEFAULT 0,
			b_member_id bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(24) NOT NULL DEFAULT 'awaiting_opponent',
			invite_name varchar(255) NOT NULL DEFAULT '',
			invite_email varchar(255) NOT NULL DEFAULT '',
			age_group varchar(10) NOT NULL DEFAULT '',
			theme varchar(12) NOT NULL DEFAULT '',
			motion_key varchar(40) NOT NULL DEFAULT '',
			motion_text varchar(255) NOT NULL DEFAULT '',
			a_side varchar(8) NOT NULL DEFAULT '',
			format varchar(10) NOT NULL DEFAULT '',
			venue varchar(255) NOT NULL DEFAULT '',
			starts_at datetime DEFAULT NULL,
			proposed_by bigint(20) unsigned NOT NULL DEFAULT 0,
			judge_id bigint(20) unsigned NOT NULL DEFAULT 0,
			stage_at datetime DEFAULT NULL,
			reminders_sent tinyint(3) unsigned NOT NULL DEFAULT 0,
			checklist_a text,
			checklist_b text,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY code (code),
			KEY school_a (school_a_id),
			KEY school_b (school_b_id),
			KEY status (status)
		) {$charset};";

		// What happened and when. Feeds the debate tracker and, later, the drop-off figures.
		$sql[] = "CREATE TABLE {$events} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			debate_id bigint(20) unsigned NOT NULL,
			event varchar(30) NOT NULL DEFAULT '',
			school_id bigint(20) unsigned NOT NULL DEFAULT 0,
			note varchar(255) NOT NULL DEFAULT '',
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY debate_event (debate_id,event)
		) {$charset};";

		// Judges are named by a teacher for one debate. No account, no vetting by the platform.
		$sql[] = "CREATE TABLE {$judges} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			debate_id bigint(20) unsigned NOT NULL,
			name varchar(255) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			organisation varchar(255) NOT NULL DEFAULT '',
			judge_type varchar(12) NOT NULL DEFAULT '',
			status varchar(10) NOT NULL DEFAULT 'pending',
			name_public tinyint(1) NOT NULL DEFAULT 0,
			ack tinyint(1) NOT NULL DEFAULT 0,
			invited_at datetime DEFAULT NULL,
			responded_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY debate_id (debate_id),
			KEY email (email)
		) {$charset};";

		// The judge's scorecard. Scores of 0 mean "not scored yet". Final once submitted.
		$sql[] = "CREATE TABLE {$cards} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			debate_id bigint(20) unsigned NOT NULL,
			judge_id bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(10) NOT NULL DEFAULT 'draft',
			students smallint(5) unsigned NOT NULL DEFAULT 0,
			vb_agree smallint(5) unsigned NOT NULL DEFAULT 0,
			vb_disagree smallint(5) unsigned NOT NULL DEFAULT 0,
			vb_unsure smallint(5) unsigned NOT NULL DEFAULT 0,
			va_agree smallint(5) unsigned NOT NULL DEFAULT 0,
			va_disagree smallint(5) unsigned NOT NULL DEFAULT 0,
			va_unsure smallint(5) unsigned NOT NULL DEFAULT 0,
			a_argument tinyint(3) unsigned NOT NULL DEFAULT 0,
			a_evidence tinyint(3) unsigned NOT NULL DEFAULT 0,
			a_rebuttal tinyint(3) unsigned NOT NULL DEFAULT 0,
			a_delivery tinyint(3) unsigned NOT NULL DEFAULT 0,
			b_argument tinyint(3) unsigned NOT NULL DEFAULT 0,
			b_evidence tinyint(3) unsigned NOT NULL DEFAULT 0,
			b_rebuttal tinyint(3) unsigned NOT NULL DEFAULT 0,
			b_delivery tinyint(3) unsigned NOT NULL DEFAULT 0,
			a_total tinyint(3) unsigned NOT NULL DEFAULT 0,
			b_total tinyint(3) unsigned NOT NULL DEFAULT 0,
			winner varchar(1) NOT NULL DEFAULT '',
			comment_a text,
			comment_b text,
			submitted_by varchar(255) NOT NULL DEFAULT '',
			submitted_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			updated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY debate_id (debate_id)
		) {$charset};";

		// Something went wrong (cancelled, wrong result, conduct, safeguarding). Schools handle it; we record it.
		$sql[] = "CREATE TABLE {$issues} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			debate_id bigint(20) unsigned NOT NULL,
			reporter_school_id bigint(20) unsigned NOT NULL DEFAULT 0,
			reporter_role varchar(10) NOT NULL DEFAULT '',
			reporter_email varchar(255) NOT NULL DEFAULT '',
			category varchar(20) NOT NULL DEFAULT '',
			details text,
			status varchar(10) NOT NULL DEFAULT 'reported',
			resolution varchar(10) NOT NULL DEFAULT '',
			resolved_by_school_id bigint(20) unsigned NOT NULL DEFAULT 0,
			resolved_at datetime DEFAULT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY debate_status (debate_id,status)
		) {$charset};";

		// One certificate per school. Withdrawn, never deleted.
		$sql[] = "CREATE TABLE {$certs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			reference varchar(16) NOT NULL DEFAULT '',
			status varchar(10) NOT NULL DEFAULT 'issued',
			issued_at datetime NOT NULL,
			withdrawn_at datetime DEFAULT NULL,
			withdrawn_reason varchar(255) NOT NULL DEFAULT '',
			snapshot longtext,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY school_id (school_id),
			UNIQUE KEY reference (reference)
		) {$charset};";

		// One-tap ratings: how easy a debate was to organise (teachers) and how judging went (judges).
		$sql[] = "CREATE TABLE {$ratings} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			debate_id bigint(20) unsigned NOT NULL,
			school_id bigint(20) unsigned NOT NULL DEFAULT 0,
			who varchar(8) NOT NULL DEFAULT '',
			rating tinyint(3) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY one_each (debate_id,school_id,who)
		) {$charset};";

		// Student Voice answers. Anonymous by design: no name, no email, no IP, no way back to a student.
		// 1 = agree, 2 = not sure, 3 = disagree. phase is general, before or after a debate.
		$sql[] = "CREATE TABLE {$voice} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			school_id bigint(20) unsigned NOT NULL,
			pin_id bigint(20) unsigned NOT NULL DEFAULT 0,
			debate_id bigint(20) unsigned NOT NULL DEFAULT 0,
			phase varchar(10) NOT NULL DEFAULT 'general',
			year_group varchar(10) NOT NULL DEFAULT '',
			q_safe tinyint(3) unsigned NOT NULL DEFAULT 0,
			q_smart tinyint(3) unsigned NOT NULL DEFAULT 0,
			q_creative tinyint(3) unsigned NOT NULL DEFAULT 0,
			q_responsible tinyint(3) unsigned NOT NULL DEFAULT 0,
			q_planet tinyint(3) unsigned NOT NULL DEFAULT 0,
			q_future tinyint(3) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY school_phase (school_id,phase),
			KEY pin_id (pin_id),
			KEY debate_id (debate_id)
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
