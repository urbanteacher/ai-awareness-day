<?php
/**
 * Issues: something went wrong at or around a debate.
 *
 * Schools handle the issue under their own procedures; the platform records it privately
 * (decision 23.11). While an issue is open the result is held: it does not count towards
 * certificates and is not shown publicly.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Issues {

	const CATEGORIES = array(
		'cancelled'    => "The debate was cancelled or didn't happen",
		'result_wrong' => 'The result is wrong',
		'conduct'      => 'Behaviour or conduct concern',
		'safeguarding' => 'Safeguarding concern',
		'other'        => 'Something else',
	);

	const RESOLUTIONS = array(
		'stands'   => 'The result stands',
		'resubmit' => 'The judge re-submits the scores',
		'void'     => 'The debate is void',
	);

	/** The school that did not close an issue can re-open it for this long. */
	const REOPEN_WINDOW = 604800; // 7 days.

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'issues' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> newest first */
	public static function for_debate( int $debate_id ): array {
		global $wpdb;
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'issues' ) . ' WHERE debate_id = %d ORDER BY id DESC', $debate_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	public static function is_held( int $debate_id ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . AIADN_Database::table( 'issues' ) . " WHERE debate_id = %d AND status = 'reported' LIMIT 1", $debate_id ) ); // phpcs:ignore WordPress.DB
	}

	public static function code( array $issue ): string {
		return 'ISS-' . ( 2000 + (int) $issue['id'] );
	}

	/** Everyone who should know an issue exists: both schools' leads and SLT, plus the programme team. */
	private static function people( array $debate ): array {
		$emails = array();
		foreach ( array( 'a', 'b' ) as $side ) {
			$school_id = (int) ( 'a' === $side ? $debate['school_a_id'] : ( $debate['school_b_id'] ?? 0 ) );
			if ( ! $school_id ) {
				continue;
			}
			$owner = AIADN_Debates::owner( $debate, $side );
			if ( $owner ) {
				$emails[] = $owner['email'];
			}
			foreach ( AIADN_Schools::slt_emails( $school_id ) as $e ) {
				$emails[] = $e;
			}
		}
		$emails[] = AIADN_Util::team_email();
		return array_values( array_unique( array_filter( array_map( 'strtolower', $emails ) ) ) );
	}

	public static function create( array $debate, int $school_id, string $role, string $email, string $category, string $details ): ?array {
		global $wpdb;
		if ( ! isset( self::CATEGORIES[ $category ] ) ) {
			return null;
		}
		$wpdb->insert( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'issues' ),
			array(
				'debate_id'          => (int) $debate['id'],
				'reporter_school_id' => $school_id,
				'reporter_role'      => $role,
				'reporter_email'     => $email,
				'category'           => $category,
				'details'            => substr( $details, 0, 2000 ),
				'status'             => 'reported',
				'created_at'         => AIADN_Util::now(),
			)
		);
		$issue = self::get( (int) $wpdb->insert_id );
		AIADN_Debates::log( (int) $debate['id'], 'issue_reported', $school_id, $category );
		// Details stay on the issue page (behind sign-in), not in email.
		$url  = AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) );
		$body = 'An issue (' . self::code( $issue ) . ', ' . self::CATEGORIES[ $category ] . ') has been reported on debate ' . $debate['code'] . ".\n\nThe result is on hold and will not count towards certificates until it is resolved. Each school handles this under its own procedures. If it is a safeguarding concern, follow your own safeguarding procedure and tell your Designated Safeguarding Lead first.\n\nDetails are on the issue page, after signing in:\n\n" . $url;
		foreach ( self::people( $debate ) as $to ) {
			AIADN_Mailer::send_notice( $to, 'Issue reported on debate ' . $debate['code'], $body );
		}
		return $issue;
	}

	/**
	 * A senior leader at either school closes an issue. What happens next depends on how.
	 */
	public static function resolve( array $issue, array $debate, int $school_id, string $resolution ): bool {
		global $wpdb;
		if ( 'reported' !== $issue['status'] || ! isset( self::RESOLUTIONS[ $resolution ] ) ) {
			return false;
		}
		$done = $wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'UPDATE ' . AIADN_Database::table( 'issues' ) . " SET status = 'resolved', resolution = %s, resolved_by_school_id = %d, resolved_at = %s WHERE id = %d AND status = 'reported'",
			$resolution,
			$school_id,
			AIADN_Util::now(),
			(int) $issue['id']
		) );
		if ( 1 !== (int) $done ) {
			return false;
		}
		AIADN_Debates::log( (int) $debate['id'], 'issue_resolved', $school_id, $resolution );

		if ( 'void' === $resolution ) {
			AIADN_Debates::update( (int) $debate['id'], array( 'status' => 'void' ) );
			AIADN_Debates::log( (int) $debate['id'], 'void', $school_id );
		} elseif ( 'resubmit' === $resolution ) {
			AIADN_Scorecards::reopen( $debate );
			$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
			if ( $judge ) {
				AIADN_Mailer::send_notice( $judge['email'], 'Please check your scores for ' . $debate['code'], "The schools have asked you to check and re-submit the scores for this debate. Open your scorecard from the judge page and submit it again:\n\n" . AIADN_Front::url( 'judge' ) );
			}
		}

		$debate = AIADN_Debates::get( (int) $debate['id'] );
		foreach ( self::people( $debate ) as $to ) {
			AIADN_Mailer::send_notice( $to, 'Issue resolved on debate ' . $debate['code'], 'The issue on debate ' . $debate['code'] . ' has been closed: ' . self::RESOLUTIONS[ $resolution ] . ".\n\n" . AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) ) );
		}
		foreach ( array( (int) $debate['school_a_id'], (int) ( $debate['school_b_id'] ?? 0 ) ) as $sid ) {
			if ( $sid ) {
				AIADN_Certificates::recalc( $sid, 'void' === $resolution );
			}
		}
		return true;
	}

	/** The other school can re-open an issue that was closed with "the result stands", for a week. */
	public static function can_reopen( array $issue, int $school_id ): bool {
		return 'resolved' === $issue['status'] && 'stands' === $issue['resolution'] && (int) $issue['resolved_by_school_id'] !== $school_id
			&& strtotime( $issue['resolved_at'] . ' UTC' ) + self::REOPEN_WINDOW > time();
	}

	public static function reopen( array $issue, array $debate, int $school_id ): bool {
		global $wpdb;
		if ( ! self::can_reopen( $issue, $school_id ) ) {
			return false;
		}
		$done = $wpdb->query( $wpdb->prepare( 'UPDATE ' . AIADN_Database::table( 'issues' ) . " SET status = 'reported', resolution = '', resolved_by_school_id = 0, resolved_at = NULL WHERE id = %d AND status = 'resolved'", (int) $issue['id'] ) ); // phpcs:ignore WordPress.DB
		if ( 1 !== (int) $done ) {
			return false;
		}
		AIADN_Debates::log( (int) $debate['id'], 'issue_reopened', $school_id );
		foreach ( self::people( $debate ) as $to ) {
			AIADN_Mailer::send_notice( $to, 'Issue re-opened on debate ' . $debate['code'], 'The issue on debate ' . $debate['code'] . " has been re-opened, and the result is on hold again.\n\n" . AIADN_Front::url( 'issue', array( 'd' => $debate['code'] ) ) );
		}
		return true;
	}
}
