<?php
/**
 * Custom submissions table.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database layer.
 */
class AIRB_Database {

	/** @var int Schema version for dbDelta upgrades. */
	const DB_VERSION = 3;

	/**
	 * Fully qualified table name.
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . AIRB_TABLE;
	}

	/**
	 * Create submissions table.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id varchar(64) NOT NULL DEFAULT '',
			role varchar(20) NOT NULL DEFAULT '',
			school_name varchar(255) NOT NULL DEFAULT '',
			school_key varchar(255) NOT NULL DEFAULT '',
			email varchar(255) NOT NULL DEFAULT '',
			consent tinyint(1) NOT NULL DEFAULT 0,
			contact_opt_in tinyint(1) NOT NULL DEFAULT 0,
			risk_level varchar(20) NOT NULL DEFAULT '',
			alignment_score smallint(3) NOT NULL DEFAULT 0,
			dependency_index smallint(3) NOT NULL DEFAULT 0,
			human_oversight_label varchar(40) NOT NULL DEFAULT '',
			privacy_risk smallint(3) NOT NULL DEFAULT 0,
			safeguarding_readiness smallint(3) NOT NULL DEFAULT 0,
			governance_maturity smallint(3) NOT NULL DEFAULT 0,
			domain_scores longtext NOT NULL,
			answers longtext NOT NULL,
			recommendations longtext NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY session_id (session_id),
			KEY role (role),
			KEY school_key (school_key),
			KEY risk_level (risk_level),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Run schema upgrades on existing installs.
	 */
	public static function maybe_upgrade(): void {
		$stored = (int) get_option( 'airb_db_version', 0 );
		if ( $stored >= self::DB_VERSION ) {
			return;
		}
		self::create_table();
		if ( class_exists( 'AIRB_Events' ) ) {
			AIRB_Events::create_table();
		}
		if ( class_exists( 'AIRB_Leads' ) ) {
			AIRB_Leads::create_table();
		}
		if ( $stored < 3 ) {
			self::backfill_school_keys();
			if ( class_exists( 'AIRB_Leads' ) ) {
				AIRB_Leads::backfill_school_keys();
				update_option( 'airb_leads_db_version', AIRB_Leads::DB_VERSION, false );
			}
		}
		update_option( 'airb_db_version', self::DB_VERSION, false );
	}

	/**
	 * Populate school_key for existing submission rows.
	 */
	public static function backfill_school_keys(): void {
		global $wpdb;

		if ( ! class_exists( 'AIRB_School_Dashboard' ) ) {
			return;
		}

		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results(
			"SELECT id, school_name FROM {$table} WHERE school_name != '' AND school_key = ''"
		);

		foreach ( (array) $rows as $row ) {
			$key = AIRB_School_Dashboard::school_key_for( (string) $row->school_name );
			if ( '' === $key ) {
				continue;
			}
			$wpdb->update(
				$table,
				array( 'school_key' => $key ),
				array( 'id' => (int) $row->id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Insert a submission row.
	 *
	 * @param array<string, mixed> $data Row data.
	 * @return int Insert ID or 0.
	 */
	public static function insert( array $data ): int {
		global $wpdb;

		$defaults = array(
			'session_id'             => '',
			'role'                   => '',
			'school_name'            => '',
			'email'                  => '',
			'risk_level'             => '',
			'alignment_score'        => 0,
			'dependency_index'       => 0,
			'human_oversight_label'    => '',
			'privacy_risk'           => 0,
			'safeguarding_readiness' => 0,
			'governance_maturity'    => 0,
			'domain_scores'          => '{}',
			'answers'                => '{}',
			'recommendations'        => '[]',
			'created_at'             => current_time( 'mysql' ),
		);

		$row = wp_parse_args( $data, $defaults );

		$school_name = sanitize_text_field( (string) $row['school_name'] );
		$school_key  = $school_name ? AIRB_School_Dashboard::school_key_for( $school_name ) : '';

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'session_id'             => sanitize_text_field( substr( (string) $row['session_id'], 0, 64 ) ),
				'role'                   => sanitize_key( (string) $row['role'] ),
				'school_name'            => $school_name,
				'school_key'             => $school_key,
				'email'                  => sanitize_email( (string) $row['email'] ),
				'risk_level'             => sanitize_text_field( (string) $row['risk_level'] ),
				'alignment_score'        => (int) $row['alignment_score'],
				'dependency_index'       => (int) $row['dependency_index'],
				'human_oversight_label'  => sanitize_text_field( (string) $row['human_oversight_label'] ),
				'privacy_risk'           => (int) $row['privacy_risk'],
				'safeguarding_readiness' => (int) $row['safeguarding_readiness'],
				'governance_maturity'    => (int) $row['governance_maturity'],
				'domain_scores'          => wp_json_encode( $row['domain_scores'] ),
				'answers'                => wp_json_encode( $row['answers'] ),
				'recommendations'        => wp_json_encode( $row['recommendations'] ),
				'created_at'             => $row['created_at'],
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Fetch submissions with optional filters.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, object>
	 */
	public static function get_submissions( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'role'       => '',
			'risk_level' => '',
			'school'     => '',
			'date_from'  => '',
			'date_to'    => '',
			'limit'      => 50,
			'offset'     => 0,
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
	 * Build a shared WHERE clause and bound values from filter args.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array{where:string, vals:array<int, mixed>}
	 */
	private static function build_where( array $args ): array {
		global $wpdb;

		$where = array( '1=1' );
		$vals  = array();

		if ( ! empty( $args['role'] ) ) {
			$where[] = 'role = %s';
			$vals[]  = sanitize_key( (string) $args['role'] );
		}
		if ( ! empty( $args['risk_level'] ) ) {
			$where[] = 'risk_level = %s';
			$vals[]  = sanitize_text_field( (string) $args['risk_level'] );
		}
		if ( ! empty( $args['school'] ) ) {
			$school_key = AIRB_School_Dashboard::school_key_for( (string) $args['school'] );
			$like       = '%' . $wpdb->esc_like( sanitize_text_field( (string) $args['school'] ) ) . '%';
			if ( $school_key ) {
				$where[] = '(school_key = %s OR school_name LIKE %s)';
				$vals[]  = $school_key;
				$vals[]  = $like;
			} else {
				$where[] = 'school_name LIKE %s';
				$vals[]  = $like;
			}
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
	 * Submission counts grouped by role.
	 *
	 * @return array<string, int>
	 */
	public static function count_by_role(): array {
		global $wpdb;

		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows  = $wpdb->get_results(
			"SELECT role, COUNT(*) AS n FROM {$table} GROUP BY role ORDER BY n DESC",
			ARRAY_A
		);

		$out = array();
		foreach ( (array) $rows as $row ) {
			$out[ (string) $row['role'] ] = (int) $row['n'];
		}
		return $out;
	}

	/**
	 * Count submissions with a school name stored.
	 */
	public static function count_with_school(): int {
		global $wpdb;

		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE school_name != ''" );
	}

	/**
	 * Count submissions for filters.
	 *
	 * @param array<string, mixed> $args Query args.
	 */
	public static function count_submissions( array $args = array() ): int {
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
	 * Get one submission.
	 *
	 * @param int $id Submission ID.
	 */
	public static function get_submission( int $id ): ?object {
		global $wpdb;
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		return $row ?: null;
	}

	/**
	 * Latest submission for an anonymous session (optionally filtered by role).
	 *
	 * @param string $session_id Session id.
	 * @param string $role       Optional role filter.
	 */
	public static function get_latest_submission_by_session( string $session_id, string $role = '' ): ?object {
		global $wpdb;
		$session_id = sanitize_text_field( substr( $session_id, 0, 64 ) );
		if ( '' === $session_id ) {
			return null;
		}

		$table = self::table_name();
		if ( $role ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE session_id = %s AND role = %s ORDER BY id DESC LIMIT 1",
					$session_id,
					sanitize_key( $role )
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT * FROM {$table} WHERE session_id = %s ORDER BY id DESC LIMIT 1",
					$session_id
				)
			);
		}

		return $row ?: null;
	}

	/**
	 * National benchmark stats for a role.
	 * Below this, averages could de-anonymise a small cohort, so we suppress them.
	 */
	const BENCHMARK_MIN_SAMPLE = 8;

	/**
	 * National benchmark stats for a role.
	 *
	 * Returns averages for each headline metric, the sample size, and — when a
	 * reference alignment score is supplied — the percentile that score sits in.
	 * Returns null when the sample is below BENCHMARK_MIN_SAMPLE (privacy floor).
	 *
	 * @param string   $role            Role slug to benchmark within.
	 * @param int|null $alignment_score Optional score to compute a percentile for.
	 * @return array<string, mixed>|null
	 */
	public static function get_benchmark_stats( string $role, ?int $alignment_score = null ): ?array {
		global $wpdb;
		$table = self::table_name();
		$role  = sanitize_key( $role );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS n,
					AVG(alignment_score) AS alignment_score,
					AVG(dependency_index) AS dependency_index,
					AVG(privacy_risk) AS privacy_risk,
					AVG(safeguarding_readiness) AS safeguarding_readiness,
					AVG(governance_maturity) AS governance_maturity
				FROM {$table}
				WHERE role = %s",
				$role
			),
			ARRAY_A
		);

		$sample = isset( $row['n'] ) ? (int) $row['n'] : 0;
		if ( $sample < self::BENCHMARK_MIN_SAMPLE ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$scores = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT alignment_score FROM {$table} WHERE role = %s ORDER BY alignment_score ASC",
				$role
			)
		);

		$top_quartile = (int) round( (float) $row['alignment_score'] );
		if ( is_array( $scores ) && count( $scores ) > 0 ) {
			$idx          = (int) floor( 0.75 * ( count( $scores ) - 1 ) );
			$top_quartile = (int) $scores[ max( 0, $idx ) ];
		}

		$stats = array(
			'sample_size' => $sample,
			'average'     => (int) round( (float) $row['alignment_score'] ),
			'top_quartile'=> $top_quartile,
			'averages'    => array(
				'alignment_score'        => (int) round( (float) $row['alignment_score'] ),
				'dependency_index'       => (int) round( (float) $row['dependency_index'] ),
				'privacy_risk'           => (int) round( (float) $row['privacy_risk'] ),
				'safeguarding_readiness' => (int) round( (float) $row['safeguarding_readiness'] ),
				'governance_maturity'    => (int) round( (float) $row['governance_maturity'] ),
			),
		);

		if ( null !== $alignment_score ) {
			// Percentile = share of submissions this score meets or beats.
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$below = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table} WHERE role = %s AND alignment_score <= %d",
					$role,
					(int) $alignment_score
				)
			);
			$stats['percentile'] = (int) round( ( $below / $sample ) * 100 );
		}

		return $stats;
	}

	/**
	 * Count submissions at a school, grouped by stakeholder role.
	 *
	 * @param string $school School name (partial match).
	 * @return array<string, int>
	 */
	public static function count_school_responses_by_role( string $school ): array {
		global $wpdb;
		$table = self::table_name();
		$roles = array( 'leader', 'teacher', 'support_staff', 'student', 'parent' );
		$out   = array();

		foreach ( $roles as $role ) {
			$out[ $role ] = self::count_submissions(
				array(
					'role'   => $role,
					'school' => $school,
				)
			);
		}

		return $out;
	}

	/**
	 * Peer benchmark stats for leaders filtered by school phase (from stored answers).
	 *
	 * Returns null when sample is below BENCHMARK_MIN_SAMPLE; caller should use fallback.
	 *
	 * @param string   $role            Role slug (typically leader).
	 * @param int|null $alignment_score Optional score for percentile.
	 * @param string   $school_phase    School phase slug (primary, secondary, all_through).
	 * @return array<string, mixed>|null
	 */
	public static function get_peer_benchmark_stats( string $role, ?int $alignment_score, string $school_phase ): ?array {
		global $wpdb;
		$table = self::table_name();
		$role  = sanitize_key( $role );
		$phase = sanitize_key( $school_phase );

		if ( ! $phase ) {
			return null;
		}

		$phase_pattern = '%"_school_phase":"' . $wpdb->esc_like( $phase ) . '"%';

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT COUNT(*) AS n, AVG(alignment_score) AS alignment_score
				FROM {$table}
				WHERE role = %s AND answers LIKE %s",
				$role,
				$phase_pattern
			),
			ARRAY_A
		);

		$sample = isset( $row['n'] ) ? (int) $row['n'] : 0;
		if ( $sample < self::BENCHMARK_MIN_SAMPLE ) {
			return null;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$scores = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT alignment_score FROM {$table}
				WHERE role = %s AND answers LIKE %s
				ORDER BY alignment_score ASC",
				$role,
				$phase_pattern
			)
		);

		$top_quartile = (int) round( (float) $row['alignment_score'] );
		if ( is_array( $scores ) && count( $scores ) > 0 ) {
			$idx = (int) floor( 0.75 * ( count( $scores ) - 1 ) );
			$top_quartile = (int) $scores[ max( 0, $idx ) ];
		}

		$stats = array(
			'sample_size' => $sample,
			'average'     => (int) round( (float) $row['alignment_score'] ),
			'top_quartile'=> $top_quartile,
			'is_estimated'=> false,
		);

		if ( null !== $alignment_score ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$below = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$table}
					WHERE role = %s AND answers LIKE %s AND alignment_score <= %d",
					$role,
					$phase_pattern,
					(int) $alignment_score
				)
			);
			$stats['percentile'] = (int) round( ( $below / $sample ) * 100 );
		}

		return $stats;
	}
}
