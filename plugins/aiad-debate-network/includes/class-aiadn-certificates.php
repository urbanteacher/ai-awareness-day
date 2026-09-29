<?php
/**
 * The National AI Debate School certificate.
 *
 * Unlocks after two completed debates against two DIFFERENT schools (decision 23.7). A result that
 * is on hold (open issue) does not count towards a new certificate. An issued certificate is never
 * deleted: it is withdrawn, with a reason, and the check page says so plainly (Annex A5). It is only
 * withdrawn when a debate is voided; an open issue alone does not withdraw it.
 *
 * The reference is generated separately from the school code, so a certificate on a wall never
 * reveals the code.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Certificates {

	const NEEDED = 2;

	public static function get_for_school( int $school_id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'certificates' ) . ' WHERE school_id = %d', $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function get_by_reference( string $reference ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'certificates' ) . ' WHERE reference = %s', $reference ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** Turn what someone typed into a certificate reference, or ''. */
	public static function normalise_reference( string $input ): string {
		$clean = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $input ) );
		if ( str_starts_with( $clean, 'AIADDS' ) ) {
			$clean = substr( $clean, 6 );
		}
		return preg_match( '/^[' . AIADN_Util::CODE_ALPHABET . ']{5}$/', $clean ) ? 'AIAD-DS-' . $clean : '';
	}

	/**
	 * The school's counting debates, one per different opponent (the first against each), oldest first.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public static function qualifying_debates( int $school_id ): array {
		$seen = array();
		$out  = array();
		foreach ( AIADN_Results::counting_debates( $school_id ) as $debate ) {
			$opponent = AIADN_Debates::other_school_id( $debate, $school_id );
			if ( $opponent && ! isset( $seen[ $opponent ] ) ) {
				$seen[ $opponent ] = true;
				$out[]             = $debate;
			}
		}
		return $out;
	}

	/** @return array{have:int,need:int} */
	public static function progress( int $school_id ): array {
		return array( 'have' => min( self::NEEDED, count( self::qualifying_debates( $school_id ) ) ), 'need' => self::NEEDED );
	}

	/**
	 * Issue, restore or withdraw the school's certificate so it matches its results.
	 *
	 * @param bool $allow_withdraw Only true when a debate has been voided.
	 */
	public static function recalc( int $school_id, bool $allow_withdraw = false ): void {
		global $wpdb;
		$table      = AIADN_Database::table( 'certificates' );
		$qualifying = self::qualifying_debates( $school_id );
		$qualifies  = count( $qualifying ) >= self::NEEDED;
		$cert       = self::get_for_school( $school_id );
		$school     = AIADN_Schools::get( $school_id );
		if ( ! $school ) {
			return;
		}

		if ( $qualifies && ( ! $cert || 'withdrawn' === $cert['status'] ) ) {
			$snapshot = array();
			foreach ( array_slice( $qualifying, 0, self::NEEDED ) as $d ) {
				$opp        = AIADN_Schools::get( AIADN_Debates::other_school_id( $d, $school_id ) );
				$snapshot[] = array(
					'opponent' => $opp ? $opp['name'] : '',
					'date'     => $d['starts_at'],
					'theme'    => $d['theme'],
				);
			}
			$now = AIADN_Util::now();
			if ( $cert ) {
				$wpdb->update( $table, array( 'status' => 'issued', 'withdrawn_at' => null, 'withdrawn_reason' => '', 'snapshot' => wp_json_encode( $snapshot ) ), array( 'id' => (int) $cert['id'] ) ); // phpcs:ignore WordPress.DB
			} else {
				$reference = '';
				for ( $i = 0; $i < 20 && '' === $reference; $i++ ) {
					$candidate = 'AIAD-DS-' . AIADN_Util::random_string( 5 );
					if ( ! self::get_by_reference( $candidate ) ) {
						$reference = $candidate;
					}
				}
				$wpdb->insert( $table, array( 'school_id' => $school_id, 'reference' => $reference, 'status' => 'issued', 'issued_at' => $now, 'snapshot' => wp_json_encode( $snapshot ), 'created_at' => $now ) ); // phpcs:ignore WordPress.DB
			}
			AIADN_Debates::log( (int) $qualifying[0]['id'], 'certificate_issued', $school_id );
			self::tell( $school, 'You have earned the National AI Debate School certificate', "Congratulations! {$school['name']} has debated two different schools, which unlocks the National AI Debate School certificate.\n\nSign in to see and print it:\n\n" . AIADN_Front::url( 'certificate' ) );
		} elseif ( ! $qualifies && $cert && 'issued' === $cert['status'] && $allow_withdraw ) {
			$wpdb->update( $table, array( 'status' => 'withdrawn', 'withdrawn_at' => AIADN_Util::now(), 'withdrawn_reason' => 'A debate result was corrected' ), array( 'id' => (int) $cert['id'] ) ); // phpcs:ignore WordPress.DB
			self::tell( $school, 'Your debate certificate has been withdrawn', "A debate result was corrected, so {$school['name']} no longer has two completed debates against different schools. The certificate has been withdrawn, and its check page says so. It will be restored when a second debate against a different school is completed.\n\n" . AIADN_Front::url( 'certificate' ) );
		}
	}

	/** Tell the school's lead and senior leaders. */
	private static function tell( array $school, string $subject, string $body ): void {
		$emails = AIADN_Schools::slt_emails( (int) $school['id'] );
		$lead   = AIADN_Schools::lead( (int) $school['id'] );
		if ( $lead ) {
			$emails[] = $lead['email'];
		}
		foreach ( array_unique( $emails ) as $to ) {
			AIADN_Mailer::send_notice( $to, $subject, $body );
		}
	}
}
