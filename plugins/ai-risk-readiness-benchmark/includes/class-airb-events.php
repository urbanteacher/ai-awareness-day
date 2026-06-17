<?php
/**
 * Benchmark journey event log (anonymous session tracking).
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Event persistence and funnel aggregates.
 */
class AIRB_Events {

	/**
	 * Allowed event types (whitelist).
	 */
	private const ALLOWED_TYPES = array(
		'benchmark_completed',
		'results_viewed',
		'guided_resource_click',
		'share_click',
		'share_copy',
		'consultation_click',
		'report_request_click',
		'email_report',
		'certificate_allocated',
		'interest_submitted',
		'hub_interest_submitted',
		'leader_next_step_click',
		'gateway_click',
	);

	/**
	 * Fully qualified events table name.
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'airb_events';
	}

	/**
	 * Create events table.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_id varchar(64) NOT NULL DEFAULT '',
			submission_id bigint(20) unsigned NOT NULL DEFAULT 0,
			event_type varchar(40) NOT NULL DEFAULT '',
			role varchar(20) NOT NULL DEFAULT '',
			metadata longtext NOT NULL,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY session_id (session_id),
			KEY submission_id (submission_id),
			KEY event_type (event_type),
			KEY role (role),
			KEY created_at (created_at)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert one event row.
	 *
	 * @param array<string, mixed> $data Event data.
	 * @return int Insert ID or 0.
	 */
	public static function insert( array $data ): int {
		global $wpdb;

		$type = sanitize_key( (string) ( $data['event_type'] ?? '' ) );
		if ( ! in_array( $type, self::ALLOWED_TYPES, true ) ) {
			return 0;
		}

		$metadata = $data['metadata'] ?? array();
		if ( ! is_array( $metadata ) ) {
			$metadata = array();
		}

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'session_id'    => sanitize_text_field( substr( (string) ( $data['session_id'] ?? '' ), 0, 64 ) ),
				'submission_id' => max( 0, (int) ( $data['submission_id'] ?? 0 ) ),
				'event_type'    => $type,
				'role'          => sanitize_key( (string) ( $data['role'] ?? '' ) ),
				'metadata'      => wp_json_encode( $metadata ),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%s', '%d', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? (int) $wpdb->insert_id : 0;
	}

	/**
	 * Count events grouped by type.
	 *
	 * @param array<string, mixed> $args Optional date_from, date_to, role filters.
	 * @return array<string, int>
	 */
	public static function count_by_type( array $args = array() ): array {
		global $wpdb;

		$table  = self::table_name();
		$where  = array( '1=1' );
		$vals   = array();

		if ( ! empty( $args['role'] ) ) {
			$where[] = 'role = %s';
			$vals[]  = sanitize_key( (string) $args['role'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'created_at >= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'created_at <= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_to'] ) . ' 23:59:59';
		}

		$sql = 'SELECT event_type, COUNT(*) AS n FROM ' . $table . ' WHERE ' . implode( ' AND ', $where ) . ' GROUP BY event_type ORDER BY n DESC';

		if ( $vals ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $vals ), ARRAY_A );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results( $sql, ARRAY_A );
		}

		$out = array();
		foreach ( (array) $rows as $row ) {
			$out[ (string) $row['event_type'] ] = (int) $row['n'];
		}
		return $out;
	}

	/**
	 * Recent events for admin table.
	 *
	 * @param array<string, mixed> $args Query args.
	 * @return array<int, object>
	 */
	public static function get_events( array $args = array() ): array {
		global $wpdb;

		$defaults = array(
			'event_type' => '',
			'role'       => '',
			'date_from'  => '',
			'date_to'    => '',
			'limit'      => 50,
			'offset'     => 0,
		);
		$args  = wp_parse_args( $args, $defaults );
		$table = self::table_name();

		$where = array( '1=1' );
		$vals  = array();

		if ( ! empty( $args['event_type'] ) ) {
			$where[] = 'event_type = %s';
			$vals[]  = sanitize_key( (string) $args['event_type'] );
		}
		if ( ! empty( $args['role'] ) ) {
			$where[] = 'role = %s';
			$vals[]  = sanitize_key( (string) $args['role'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'created_at >= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'created_at <= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_to'] ) . ' 23:59:59';
		}

		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' AND ', $where ) . ' ORDER BY created_at DESC LIMIT %d OFFSET %d';
		$vals[] = (int) $args['limit'];
		$vals[] = (int) $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $wpdb->prepare( $sql, $vals ) );
	}

	/**
	 * Count events for filters.
	 *
	 * @param array<string, mixed> $args Query args.
	 */
	public static function count_events( array $args = array() ): int {
		global $wpdb;

		$args['limit']  = 1;
		$args['offset'] = 0;
		unset( $args['limit'], $args['offset'] );

		$table = self::table_name();
		$where = array( '1=1' );
		$vals  = array();

		if ( ! empty( $args['event_type'] ) ) {
			$where[] = 'event_type = %s';
			$vals[]  = sanitize_key( (string) $args['event_type'] );
		}
		if ( ! empty( $args['role'] ) ) {
			$where[] = 'role = %s';
			$vals[]  = sanitize_key( (string) $args['role'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'created_at >= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_from'] ) . ' 00:00:00';
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'created_at <= %s';
			$vals[]  = sanitize_text_field( (string) $args['date_to'] ) . ' 23:59:59';
		}

		$sql = 'SELECT COUNT(*) FROM ' . $table . ' WHERE ' . implode( ' AND ', $where );
		if ( $vals ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $vals ) );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Weak domain counts from submissions (readiness below threshold).
	 *
	 * @param int $threshold Readiness percentage below which a domain counts as weak.
	 * @return array<string, int> Domain slug => count.
	 */
	public static function weak_domain_counts( int $threshold = 70 ): array {
		global $wpdb;

		$table = AIRB_Database::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows  = $wpdb->get_col( "SELECT domain_scores FROM {$table}" );

		$counts = array();
		foreach ( (array) $rows as $json ) {
			$domains = json_decode( (string) $json, true );
			if ( ! is_array( $domains ) ) {
				continue;
			}
			foreach ( $domains as $slug => $dom ) {
				if ( ! is_array( $dom ) ) {
					continue;
				}
				if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
					continue;
				}
				$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
				if ( $readiness >= $threshold ) {
					continue;
				}
				$key = sanitize_key( (string) $slug );
				if ( ! isset( $counts[ $key ] ) ) {
					$counts[ $key ] = 0;
				}
				++$counts[ $key ];
			}
		}

		arsort( $counts );
		return $counts;
	}

	/**
	 * Human-readable event labels for admin.
	 *
	 * @return array<string, string>
	 */
	public static function event_labels(): array {
		return array(
			'benchmark_completed'       => __( 'Benchmark completed (stored)', 'ai-risk-benchmark' ),
			'results_viewed'            => __( 'Results viewed', 'ai-risk-benchmark' ),
			'guided_resource_click'     => __( 'Guided resource click', 'ai-risk-benchmark' ),
			'share_click'               => __( 'Share with school', 'ai-risk-benchmark' ),
			'share_copy'                => __( 'Copy result', 'ai-risk-benchmark' ),
			'consultation_click'        => __( 'Consultation CTA', 'ai-risk-benchmark' ),
			'report_request_click'      => __( 'Full report request', 'ai-risk-benchmark' ),
			'email_report'              => __( 'Email report sent', 'ai-risk-benchmark' ),
			'certificate_allocated'     => __( 'Certificate allocated', 'ai-risk-benchmark' ),
			'interest_submitted'        => __( 'Interest form submitted (results)', 'ai-risk-benchmark' ),
			'hub_interest_submitted'    => __( 'Interest form submitted (hub)', 'ai-risk-benchmark' ),
			'leader_next_step_click'    => __( 'Leader next-step CTA', 'ai-risk-benchmark' ),
			'gateway_click'             => __( 'Gateway CTA click', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Clear submission_id on events linked to deleted submissions.
	 *
	 * @param array<int, int> $submission_ids Submission IDs.
	 */
	public static function unlink_submissions( array $submission_ids ): void {
		$submission_ids = array_values(
			array_unique(
				array_filter(
					array_map( 'intval', $submission_ids ),
					static function ( int $id ): bool {
						return $id > 0;
					}
				)
			)
		);

		if ( empty( $submission_ids ) ) {
			return;
		}

		global $wpdb;
		$table        = self::table_name();
		$placeholders = implode( ',', array_fill( 0, count( $submission_ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET submission_id = 0 WHERE submission_id IN ({$placeholders})",
				$submission_ids
			)
		);
	}

	/**
	 * Clear submission_id on all events that reference a submission.
	 */
	public static function unlink_all_submissions(): void {
		global $wpdb;
		$table = self::table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "UPDATE {$table} SET submission_id = 0 WHERE submission_id > 0" );
	}
}
