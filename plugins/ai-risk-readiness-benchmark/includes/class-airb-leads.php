<?php
/**
 * Interest / support lead records (results + hub forms).
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lead persistence and admin helpers.
 */
class AIRB_Leads {

	/** @var int Schema version for dbDelta upgrades. */
	const DB_VERSION = 1;

	/**
	 * Allowed lead statuses.
	 *
	 * @return array<string, string>
	 */
	public static function statuses(): array {
		return array(
			'new'                  => __( 'New', 'ai-risk-benchmark' ),
			'engaged'              => __( 'Engaged', 'ai-risk-benchmark' ),
			'contacted'            => __( 'Contacted', 'ai-risk-benchmark' ),
			'consultation_booked'  => __( 'Consultation booked', 'ai-risk-benchmark' ),
			'converted'            => __( 'Converted', 'ai-risk-benchmark' ),
			'closed'               => __( 'Closed', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Fully qualified table name.
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'airb_leads';
	}

	/**
	 * Create leads table.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id varchar(64) NOT NULL DEFAULT '',
			submission_id bigint(20) unsigned NOT NULL DEFAULT 0,
			source varchar(20) NOT NULL DEFAULT 'results',
			status varchar(30) NOT NULL DEFAULT 'new',
			role varchar(20) NOT NULL DEFAULT '',
			name varchar(255) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			school varchar(255) NOT NULL DEFAULT '',
			child_school varchar(255) NOT NULL DEFAULT '',
			message longtext NOT NULL,
			alignment_score smallint(3) NOT NULL DEFAULT 0,
			risk_level varchar(20) NOT NULL DEFAULT '',
			risk_level_label varchar(80) NOT NULL DEFAULT '',
			readiness_level_label varchar(80) NOT NULL DEFAULT '',
			year_group varchar(30) NOT NULL DEFAULT '',
			stakeholder_role varchar(40) NOT NULL DEFAULT '',
			interests longtext NOT NULL,
			weak_domains longtext NOT NULL,
			hub_page varchar(80) NOT NULL DEFAULT '',
			hub_title varchar(255) NOT NULL DEFAULT '',
			hub_ref varchar(40) NOT NULL DEFAULT '',
			hub_url varchar(500) NOT NULL DEFAULT '',
			checklist_done smallint(5) NOT NULL DEFAULT 0,
			checklist_total smallint(5) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY session_id (session_id),
			KEY submission_id (submission_id),
			KEY source (source),
			KEY status (status),
			KEY role (role),
			KEY email (email),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Run schema upgrades on existing installs.
	 */
	public static function maybe_upgrade(): void {
		$stored = (int) get_option( 'airb_leads_db_version', 0 );
		if ( $stored >= self::DB_VERSION ) {
			return;
		}
		self::create_table();
		update_option( 'airb_leads_db_version', self::DB_VERSION, false );
	}

	/**
	 * Insert a lead from interest form payload.
	 *
	 * @param array<string, mixed> $data Lead data.
	 * @return int Insert ID or 0.
	 */
	public static function insert( array $data ): int {
		global $wpdb;

		$statuses = array_keys( self::statuses() );
		$status   = sanitize_key( (string) ( $data['status'] ?? 'new' ) );
		if ( ! in_array( $status, $statuses, true ) ) {
			$status = 'new';
		}

		$interests    = $data['interests'] ?? array();
		$weak_domains = $data['weak_domains'] ?? array();
		if ( ! is_array( $interests ) ) {
			$interests = array();
		}
		if ( ! is_array( $weak_domains ) ) {
			$weak_domains = array();
		}

		$now = current_time( 'mysql' );

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'session_id'            => sanitize_text_field( substr( (string) ( $data['session_id'] ?? '' ), 0, 64 ) ),
				'submission_id'         => max( 0, (int) ( $data['submission_id'] ?? 0 ) ),
				'source'                => sanitize_key( (string) ( $data['source'] ?? 'results' ) ),
				'status'                => $status,
				'role'                  => sanitize_key( (string) ( $data['role'] ?? '' ) ),
				'name'                  => sanitize_text_field( (string) ( $data['name'] ?? '' ) ),
				'email'                 => sanitize_email( (string) ( $data['email'] ?? '' ) ),
				'school'                => sanitize_text_field( (string) ( $data['school'] ?? '' ) ),
				'child_school'          => sanitize_text_field( (string) ( $data['child_school'] ?? '' ) ),
				'message'               => sanitize_textarea_field( (string) ( $data['message'] ?? '' ) ),
				'alignment_score'       => max( 0, min( 100, (int) ( $data['alignment_score'] ?? 0 ) ) ),
				'risk_level'            => sanitize_key( (string) ( $data['risk_level'] ?? '' ) ),
				'risk_level_label'      => sanitize_text_field( (string) ( $data['risk_level_label'] ?? '' ) ),
				'readiness_level_label' => sanitize_text_field( (string) ( $data['readiness_level_label'] ?? '' ) ),
				'year_group'            => sanitize_key( (string) ( $data['year_group'] ?? '' ) ),
				'stakeholder_role'      => sanitize_key( (string) ( $data['stakeholder_role'] ?? '' ) ),
				'interests'             => wp_json_encode( array_values( $interests ) ),
				'weak_domains'          => wp_json_encode( array_values( $weak_domains ) ),
				'hub_page'              => sanitize_key( (string) ( $data['hub_page'] ?? '' ) ),
				'hub_title'             => sanitize_text_field( (string) ( $data['hub_title'] ?? '' ) ),
				'hub_ref'               => sanitize_key( (string) ( $data['hub_ref'] ?? '' ) ),
				'hub_url'               => esc_url_raw( (string) ( $data['hub_url'] ?? '' ) ),
				'checklist_done'        => max( 0, (int) ( $data['checklist_done'] ?? 0 ) ),
				'checklist_total'       => max( 0, (int) ( $data['checklist_total'] ?? 0 ) ),
				'created_at'            => $now,
				'updated_at'            => $now,
			),
			array(
				'%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
				'%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
				'%s', '%s', '%s', '%d', '%d', '%s', '%s',
			)
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Update lead status.
	 */
	public static function update_status( int $id, string $status ): bool {
		global $wpdb;

		$statuses = array_keys( self::statuses() );
		$status   = sanitize_key( $status );
		if ( ! in_array( $status, $statuses, true ) ) {
			return false;
		}

		$updated = $wpdb->update(
			self::table_name(),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Get one lead.
	 */
	public static function get( int $id ): ?object {
		global $wpdb;
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		return $row ?: null;
	}

	/**
	 * Fetch leads with filters.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, object>
	 */
	public static function get_leads( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'status'    => '',
			'source'    => '',
			'role'      => '',
			'email'     => '',
			'school'    => '',
			'date_from' => '',
			'date_to'   => '',
			'limit'     => 50,
			'offset'    => 0,
		);
		$args  = wp_parse_args( $args, $defaults );
		$table = self::table_name();
		$clause = self::build_where( $args );
		$vals   = $clause['vals'];

		$sql = "SELECT * FROM {$table} WHERE " . $clause['where'] . ' ORDER BY created_at DESC LIMIT %d OFFSET %d';
		$vals[] = (int) $args['limit'];
		$vals[] = (int) $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $vals ) );
	}

	/**
	 * Count leads for filters.
	 *
	 * @param array<string, mixed> $args Query args.
	 */
	public static function count_leads( array $args = array() ): int {
		global $wpdb;

		$table  = self::table_name();
		$clause = self::build_where( $args );
		$vals   = $clause['vals'];

		$sql = "SELECT COUNT(*) FROM {$table} WHERE " . $clause['where'];
		if ( $vals ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $vals ) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Count leads grouped by status.
	 *
	 * @return array<string, int>
	 */
	public static function count_by_status(): array {
		global $wpdb;

		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT status, COUNT(*) AS n FROM {$table} GROUP BY status ORDER BY n DESC",
			ARRAY_A
		);

		$out = array_fill_keys( array_keys( self::statuses() ), 0 );
		foreach ( (array) $rows as $row ) {
			$key = (string) $row['status'];
			if ( isset( $out[ $key ] ) ) {
				$out[ $key ] = (int) $row['n'];
			}
		}
		return $out;
	}

	/**
	 * Build WHERE clause from filter args.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array{where:string, vals:array<int, mixed>}
	 */
	private static function build_where( array $args ): array {
		global $wpdb;

		$where = array( '1=1' );
		$vals  = array();

		if ( ! empty( $args['status'] ) ) {
			$where[] = 'status = %s';
			$vals[]  = sanitize_key( (string) $args['status'] );
		}
		if ( ! empty( $args['source'] ) ) {
			$where[] = 'source = %s';
			$vals[]  = sanitize_key( (string) $args['source'] );
		}
		if ( ! empty( $args['role'] ) ) {
			$where[] = 'role = %s';
			$vals[]  = sanitize_key( (string) $args['role'] );
		}
		if ( ! empty( $args['email'] ) ) {
			$where[] = 'email LIKE %s';
			$vals[]  = '%' . $wpdb->esc_like( sanitize_email( (string) $args['email'] ) ) . '%';
		}
		if ( ! empty( $args['school'] ) ) {
			$where[] = '(school LIKE %s OR child_school LIKE %s)';
			$like    = '%' . $wpdb->esc_like( sanitize_text_field( (string) $args['school'] ) ) . '%';
			$vals[]  = $like;
			$vals[]  = $like;
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'created_at >= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'created_at <= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_to'] ) . ' 23:59:59';
		}

		return array(
			'where' => implode( ' AND ', $where ),
			'vals'  => $vals,
		);
	}

	/**
	 * Decode JSON list column to string array.
	 *
	 * @return array<int, string>
	 */
	public static function decode_list( string $json ): array {
		$decoded = json_decode( $json, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}
		return array_map( 'strval', $decoded );
	}
}
