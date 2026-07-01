<?php
/**
 * Certificate allocation storage.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stores named benchmark certificates against saved submissions.
 */
class AIRB_Certificates {

	/** @var int Schema version for dbDelta upgrades. */
	const DB_VERSION = 2;

	/**
	 * Fully qualified table name.
	 */
	public static function table_name(): string {
		global $wpdb;
		return $wpdb->prefix . 'airb_certificates';
	}

	/**
	 * Create certificate table.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table   = self::table_name();
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			certificate_id varchar(64) NOT NULL DEFAULT '',
			submission_id bigint(20) unsigned NOT NULL DEFAULT 0,
			session_id varchar(64) NOT NULL DEFAULT '',
			role varchar(30) NOT NULL DEFAULT '',
			participant_name varchar(255) NOT NULL DEFAULT '',
			school_name varchar(255) NOT NULL DEFAULT '',
			school_key varchar(255) NOT NULL DEFAULT '',
			baseline_score smallint(3) NOT NULL DEFAULT 0,
			completed_score smallint(3) NOT NULL DEFAULT 0,
			unlock_at smallint(3) NOT NULL DEFAULT 0,
			unlock_reason varchar(60) NOT NULL DEFAULT '',
			evidence_theme varchar(20) NOT NULL DEFAULT '',
			evidence_action text NOT NULL,
			evidence_change text NOT NULL,
			evidence_link varchar(500) NOT NULL DEFAULT '',
			evidence_quality_score smallint(3) NOT NULL DEFAULT 0,
			evidence_quality_tier varchar(30) NOT NULL DEFAULT '',
			verification_hash varchar(64) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'unlocked',
			awarded_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY certificate_id (certificate_id),
			UNIQUE KEY submission_id (submission_id),
			KEY session_id (session_id),
			KEY role (role),
			KEY school_key (school_key),
			KEY verification_hash (verification_hash),
			KEY evidence_theme (evidence_theme)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Run schema upgrades on existing installs.
	 */
	public static function maybe_upgrade(): void {
		$stored = (int) get_option( 'airb_certificates_db_version', 0 );
		if ( $stored >= self::DB_VERSION ) {
			return;
		}
		self::create_table();
		update_option( 'airb_certificates_db_version', self::DB_VERSION, false );
	}

	/**
	 * Get one certificate by submission.
	 */
	public static function get_by_submission( int $submission_id ): ?object {
		global $wpdb;
		if ( $submission_id <= 0 ) {
			return null;
		}
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE submission_id = %d", $submission_id ) );
		return $row ?: null;
	}

	/**
	 * Get one certificate by its public verification hash (used for the
	 * "check your certificate" link sent once a manual review is approved,
	 * so a participant can find it again without relying on browser storage).
	 */
	public static function get_by_verification_hash( string $hash ): ?object {
		global $wpdb;
		$hash = sanitize_text_field( $hash );
		if ( '' === $hash ) {
			return null;
		}
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE verification_hash = %s", $hash ) );
		return $row ?: null;
	}

	/**
	 * Certificates currently waiting for manual review, oldest first.
	 *
	 * @return array<int, object>
	 */
	public static function get_pending_review(): array {
		global $wpdb;
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( "SELECT * FROM {$table} WHERE status = 'pending_review' ORDER BY created_at ASC" );
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Count certificates currently waiting for manual review.
	 */
	public static function count_pending_review(): int {
		global $wpdb;
		$table = self::table_name();
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'pending_review'" );
	}

	/**
	 * Certificate status payload for front-end models.
	 *
	 * @param int                  $submission_id Saved submission id.
	 * @param array<string, mixed> $results       Result payload.
	 * @param string               $role          Role slug.
	 * @param string               $school_name   Submitted school/org name.
	 * @return array<string, mixed>
	 */
	public static function status_for_submission( int $submission_id, array $results, string $role, string $school_name = '' ): array {
		$score           = self::clamp_percent( (int) ( $results['alignment_score'] ?? 0 ) );
		$score_threshold = AIRB_Certificate_Evidence::SCORE_THRESHOLD;
		$row             = self::get_by_submission( $submission_id );

		$status = array(
			'current_score'        => $score,
			'score_threshold'      => $score_threshold,
			'score_eligible'       => $score >= $score_threshold,
			'min_reflection_chars' => AIRB_Certificate_Evidence::MIN_REFLECTION_CHARS,
			'quality_threshold'    => AIRB_Certificate_Evidence::QUALITY_THRESHOLD,
			'unlock_at'            => $score_threshold,
			'needed'               => max( 0, $score_threshold - $score ),
			'unlocked'             => false,
			'status'               => 'draft',
			'role'                 => sanitize_key( $role ),
			'school_name'          => sanitize_text_field( $school_name ),
		);

		if ( $row ) {
			$status['unlocked']              = 'unlocked' === (string) $row->status;
			$status['pending_review']        = 'pending_review' === (string) $row->status;
			$status['status']                = (string) $row->status;
			$status['certificate_id']        = (string) $row->certificate_id;
			$status['participant_name']      = (string) $row->participant_name;
			$status['school_name']           = (string) $row->school_name;
			$status['awarded_at']            = (string) $row->awarded_at;
			$status['verification_hash']     = (string) $row->verification_hash;
			$status['evidence_theme']        = (string) ( $row->evidence_theme ?? '' );
			$status['evidence_action']       = (string) ( $row->evidence_action ?? '' );
			$status['evidence_change']       = (string) ( $row->evidence_change ?? '' );
			$status['evidence_link']         = (string) ( $row->evidence_link ?? '' );
			$status['evidence_quality_score'] = (int) ( $row->evidence_quality_score ?? 0 );
			$status['evidence_quality_tier'] = (string) ( $row->evidence_quality_tier ?? '' );
		}

		return $status;
	}

	/**
	 * Allocate or update a named certificate for a submission.
	 *
	 * @param array<string, mixed> $args Certificate fields.
	 */
	public static function allocate( array $args ): ?object {
		global $wpdb;

		$submission_id = max( 0, (int) ( $args['submission_id'] ?? 0 ) );
		if ( ! $submission_id ) {
			return null;
		}

		$existing       = self::get_by_submission( $submission_id );
		$certificate_id = $existing ? (string) $existing->certificate_id : self::make_certificate_id( $submission_id );
		$school_name    = sanitize_text_field( (string) ( $args['school_name'] ?? '' ) );
		$school_key     = $school_name && class_exists( 'AIRB_School_Dashboard' )
			? AIRB_School_Dashboard::school_key_for( $school_name )
			: '';
		$now            = current_time( 'mysql' );
		$status         = sanitize_key( (string) ( $args['status'] ?? 'unlocked' ) );
		if ( ! in_array( $status, array( 'unlocked', 'pending_review' ), true ) ) {
			$status = 'unlocked';
		}
		$row            = array(
			'certificate_id'          => $certificate_id,
			'submission_id'           => $submission_id,
			'session_id'              => sanitize_text_field( substr( (string) ( $args['session_id'] ?? '' ), 0, 64 ) ),
			'role'                    => sanitize_key( (string) ( $args['role'] ?? '' ) ),
			'participant_name'        => sanitize_text_field( (string) ( $args['participant_name'] ?? '' ) ),
			'school_name'             => $school_name,
			'school_key'              => $school_key,
			'baseline_score'          => self::clamp_percent( (int) ( $args['baseline_score'] ?? 0 ) ),
			'completed_score'         => self::clamp_percent( (int) ( $args['completed_score'] ?? 0 ) ),
			'unlock_at'               => self::clamp_percent( (int) ( $args['unlock_at'] ?? AIRB_Certificate_Evidence::SCORE_THRESHOLD ) ),
			'unlock_reason'           => sanitize_key( (string) ( $args['unlock_reason'] ?? 'evidenced_progress' ) ),
			'evidence_theme'          => sanitize_key( (string) ( $args['evidence_theme'] ?? '' ) ),
			'evidence_action'         => sanitize_textarea_field( (string) ( $args['evidence_action'] ?? '' ) ),
			'evidence_change'         => sanitize_textarea_field( (string) ( $args['evidence_change'] ?? '' ) ),
			'evidence_link'           => esc_url_raw( (string) ( $args['evidence_link'] ?? '' ) ),
			'evidence_quality_score'  => self::clamp_percent( (int) ( $args['evidence_quality_score'] ?? 0 ) ),
			'evidence_quality_tier'   => sanitize_key( (string) ( $args['evidence_quality_tier'] ?? '' ) ),
			'verification_hash'       => self::verification_hash( $certificate_id, $submission_id ),
			'status'                  => $status,
			'updated_at'              => $now,
		);

		$formats = array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' );

		if ( $existing ) {
			$wpdb->update(
				self::table_name(),
				$row,
				array( 'submission_id' => $submission_id ),
				$formats,
				array( '%d' )
			);
		} else {
			$row['awarded_at'] = $now;
			$row['created_at'] = $now;
			$wpdb->insert(
				self::table_name(),
				$row,
				array_merge( $formats, array( '%s', '%s' ) )
			);
		}

		return self::get_by_submission( $submission_id );
	}

	/**
	 * Delete certificates linked to deleted submissions.
	 *
	 * @param array<int, int> $submission_ids Submission IDs.
	 */
	public static function delete_by_submissions( array $submission_ids ): void {
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
				"DELETE FROM {$table} WHERE submission_id IN ({$placeholders})",
				$submission_ids
			)
		);
	}

	/**
	 * Delete all certificates linked to any submission.
	 */
	public static function delete_all_submission_certificates(): void {
		global $wpdb;
		$table = self::table_name();

		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "DELETE FROM {$table} WHERE submission_id > 0" );
	}

	/**
	 * Generate a compact, human-readable certificate id.
	 */
	private static function make_certificate_id( int $submission_id ): string {
		$base = strtoupper( base_convert( (string) max( 1, $submission_id ), 10, 36 ) );
		$salt = strtoupper( substr( wp_hash( $submission_id . '|' . microtime( true ) ), 0, 6 ) );
		return 'AIAD-' . $base . '-' . $salt;
	}

	/**
	 * Verification hash for public lookups later.
	 */
	private static function verification_hash( string $certificate_id, int $submission_id ): string {
		return hash_hmac( 'sha256', $certificate_id . '|' . $submission_id, wp_salt( 'auth' ) );
	}

	/**
	 * Clamp percentage fields.
	 */
	private static function clamp_percent( int $value ): int {
		return max( 0, min( 100, $value ) );
	}

	/**
	 * Update certificate status for a submission.
	 */
	public static function update_status( int $submission_id, string $status ): bool {
		global $wpdb;

		$submission_id = max( 0, $submission_id );
		$status        = sanitize_key( $status );
		if ( $submission_id <= 0 || ! in_array( $status, array( 'unlocked', 'pending_review', 'draft' ), true ) ) {
			return false;
		}

		$updated = $wpdb->update(
			self::table_name(),
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'submission_id' => $submission_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return false !== $updated;
	}

	/**
	 * Notify AI Awareness Day when a certificate needs manual review.
	 *
	 * @param object $certificate Certificate row.
	 * @param object $submission  Benchmark submission row.
	 * @param string $notify_email Email to reach the participant.
	 */
	public static function notify_pending_review( object $certificate, object $submission, string $notify_email = '' ): void {
		$to = class_exists( 'AIRB_Interest' ) ? AIRB_Interest::contact_email() : get_option( 'admin_email' );
		if ( ! $to ) {
			return;
		}

		$admin_url = add_query_arg(
			array(
				'page'          => 'airb-benchmark',
				'submission_id' => (int) $submission->id,
			),
			admin_url( 'admin.php' )
		);

		$subject = sprintf(
			/* translators: %s: participant name */
			__( 'Certificate review needed — %s', 'ai-risk-benchmark' ),
			(string) $certificate->participant_name
		);

		$body  = "A benchmark certificate is waiting for manual review.\n\n";
		$body .= 'Participant: ' . (string) $certificate->participant_name . "\n";
		$body .= 'Role: ' . (string) $certificate->role . "\n";
		$body .= 'School: ' . ( (string) $certificate->school_name ?: '—' ) . "\n";
		$body .= 'Contact email: ' . ( $notify_email ?: '—' ) . "\n";
		$body .= 'Certificate ID: ' . (string) $certificate->certificate_id . "\n";
		$body .= 'Benchmark score: ' . (int) $submission->alignment_score . "%\n";
		$body .= 'Evidence theme: ' . (string) $certificate->evidence_theme . "\n";
		$body .= 'Evidence quality: ' . (int) $certificate->evidence_quality_score . '/100 · ' . (string) $certificate->evidence_quality_tier . "\n\n";
		$body .= "Action evidenced:\n" . (string) $certificate->evidence_action . "\n\n";
		$body .= "Change described:\n" . (string) $certificate->evidence_change . "\n\n";
		if ( ! empty( $certificate->evidence_link ) ) {
			$body .= 'Evidence link: ' . (string) $certificate->evidence_link . "\n\n";
		}
		$body .= "Review in WordPress:\n{$admin_url}\n";

		wp_mail( $to, $subject, $body );
	}

	/**
	 * Tell the participant their certificate is ready to download.
	 *
	 * @param object $certificate Certificate row.
	 * @param string $notify_email Recipient email.
	 */
	public static function notify_approved( object $certificate, string $notify_email ): void {
		$notify_email = sanitize_email( $notify_email );
		if ( ! $notify_email ) {
			return;
		}

		$verify_url = class_exists( 'AIRB_Defaults' )
			? add_query_arg( 'airb_verify', (string) $certificate->verification_hash, AIRB_Defaults::benchmark_page_url() )
			: '';

		$subject = __( 'Your AI Awareness Day certificate is ready', 'ai-risk-benchmark' );
		$body    = sprintf(
			/* translators: 1: participant name, 2: certificate id, 3: direct link to view/print the certificate */
			__( "Hi %1\$s,\n\nYour AI Risk & Readiness Benchmark certificate has been approved.\n\nCertificate ID: %2\$s\n\nOpen this link to view, download or print it (works even if you're on a different device or your last visit has expired):\n%3\$s\n\nAI Awareness Day", 'ai-risk-benchmark' ),
			(string) $certificate->participant_name,
			(string) $certificate->certificate_id,
			$verify_url ?: __( 'Return to your benchmark results and open Progress & certificate.', 'ai-risk-benchmark' )
		);

		wp_mail( $notify_email, $subject, $body );
	}
}
