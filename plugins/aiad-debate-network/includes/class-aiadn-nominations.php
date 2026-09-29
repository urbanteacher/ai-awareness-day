<?php
/**
 * Nominate a school: someone who works with a school (a trust, a partner, a teacher elsewhere) asks us to invite it.
 *
 * Because this makes the platform email a school on someone else's behalf, it is careful:
 *  - the nominator proves their own address with an emailed code first;
 *  - there is no free-text message, so it cannot carry a sales pitch or a link of the nominator's choosing;
 *  - the school's address must be on its own domain, not a personal mailbox;
 *  - a school is invited once, and never again if it says "do not contact me";
 *  - the school's address is cleared as soon as the invitation is sent (only a hash is kept for the opt-out);
 *  - a nominator can send only a few a day.
 *
 * The invitation only opens the registration page. Nothing is shared with the nominator; if they named an organisation,
 * that becomes "who introduced you", which the school's headteacher still decides on.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Nominations {

	const MAX_PER_DAY   = 3;
	const RESEND_DAYS   = 90;
	const LINK_TTL_DAYS = 60;

	private static function table(): string {
		return AIADN_Database::table( 'nominations' );
	}

	private static function hash( string $email ): string {
		return AIADN_Util::hash_secret( 'nominee|' . AIADN_Util::normalise_email( $email ) );
	}

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/**
	 * Record a nomination, waiting for the nominator to confirm their address. Returns the new id, or an error message.
	 *
	 * @return int|string
	 */
	public static function create( string $name, string $email, string $org, string $school, string $school_email ) {
		global $wpdb;
		$email        = AIADN_Util::normalise_email( $email );
		$school_email = AIADN_Util::normalise_email( $school_email );
		$name         = mb_substr( trim( $name ), 0, 120 );
		$org          = mb_substr( trim( $org ), 0, 255 );
		$school       = mb_substr( trim( $school ), 0, 255 );
		if ( mb_strlen( $name ) < 2 || ! is_email( $email ) ) {
			return 'Enter your name and your email address.';
		}
		if ( mb_strlen( $school ) < 3 ) {
			return 'Enter the school\'s name.';
		}
		if ( ! is_email( $school_email ) || AIADN_Util::is_free_mail_domain( AIADN_Util::email_domain( $school_email ) ) || $school_email === $email ) {
			return 'Enter an email address at the school\'s own domain, such as the headteacher\'s or the school office\'s. We cannot invite a personal mailbox.';
		}
		$today = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . " WHERE nominator_email = %s AND created_at > %s", $email, gmdate( 'Y-m-d H:i:s', time() - DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
		if ( $today >= self::MAX_PER_DAY ) {
			return 'You have nominated ' . self::MAX_PER_DAY . ' schools today. Please come back tomorrow.';
		}
		$wpdb->insert( // phpcs:ignore WordPress.DB
			self::table(),
			array( 'nominator_name' => $name, 'nominator_email' => $email, 'org_name' => $org, 'school_name' => $school, 'school_email' => $school_email, 'school_email_hash' => self::hash( $school_email ), 'status' => 'unverified', 'created_at' => AIADN_Util::now() )
		);
		return (int) $wpdb->insert_id;
	}

	/** Email the nominator a code. The reference the page needs is remembered for the life of the code. */
	public static function send_code( int $id ): string {
		$n = self::get( $id );
		if ( ! $n ) {
			return AIADN_Auth::decoy_ref();
		}
		list( $ref, $code ) = AIADN_Auth::issue_code( 0, $n['nominator_email'], 'nominate', '' );
		set_transient( 'aiadn_nom_' . $ref, $id, AIADN_Auth::CODE_TTL );
		AIADN_Mailer::send_code( $n['nominator_email'], $code );
		return $ref;
	}

	/** The right code: send the school's invitation, once. Returns true if the code was right. */
	public static function confirm( string $ref, string $code ): bool {
		$row = AIADN_Auth::verify_code( $ref, $code );
		if ( ! $row || 'nominate' !== $row['purpose'] ) {
			return false;
		}
		$id = (int) get_transient( 'aiadn_nom_' . preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) ) );
		$n  = $id ? self::get( $id ) : null;
		if ( $n && 'unverified' === $n['status'] && strtolower( $n['nominator_email'] ) === strtolower( $row['email'] ) ) {
			self::dispatch( $n );
		}
		return true;
	}

	/** Send the invitation unless the school has asked not to be contacted, or has been invited recently. */
	private static function dispatch( array $n ): void {
		global $wpdb;
		$recent = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::table() . " WHERE school_email_hash = %s AND id <> %d AND (status = 'opted_out' OR (status IN ('sent','registered') AND sent_at > %s))", $n['school_email_hash'], (int) $n['id'], gmdate( 'Y-m-d H:i:s', time() - self::RESEND_DAYS * DAY_IN_SECONDS ) ) ); // phpcs:ignore WordPress.DB
		if ( $recent > 0 || ! AIADN_Util::allow( 'nominate-send|' . $n['school_email_hash'], 1, self::RESEND_DAYS * DAY_IN_SECONDS ) ) {
			$wpdb->update( self::table(), array( 'status' => 'blocked', 'school_email' => '' ), array( 'id' => (int) $n['id'] ) ); // phpcs:ignore WordPress.DB
			return;
		}
		$register = AIADN_Front::url( 'register', array( 'nom' => AIADN_Auth::issue_token( 0, 'nomination', self::LINK_TTL_DAYS * DAY_IN_SECONDS, (int) $n['id'], false ) ) );
		$stop     = AIADN_Front::url( 'nominate', array( 'stop' => AIADN_Auth::issue_token( 0, 'nomstop', self::LINK_TTL_DAYS * DAY_IN_SECONDS, (int) $n['id'], false ) ) );
		AIADN_Mailer::send_nomination( $n['school_email'], $n['school_name'], $n['nominator_name'], $n['org_name'], $n['nominator_email'], $register, $stop );
		$wpdb->update( self::table(), array( 'status' => 'sent', 'sent_at' => AIADN_Util::now(), 'school_email' => '' ), array( 'id' => (int) $n['id'] ) ); // phpcs:ignore WordPress.DB
	}

	/** The nomination a registration link belongs to, if it is still good. */
	public static function from_token( string $raw ): ?array {
		$tok = AIADN_Auth::find_token( $raw, 'nomination' );
		return $tok ? self::get( (int) $tok['ref_id'] ) : null;
	}

	/** A school registered from an invitation: note it, and carry over who nominated them if they named an organisation. */
	public static function on_registered( array $n, int $school_id ): void {
		global $wpdb;
		$wpdb->update( self::table(), array( 'status' => 'registered', 'registered_school_id' => $school_id ), array( 'id' => (int) $n['id'] ) ); // phpcs:ignore WordPress.DB
		if ( '' !== $n['org_name'] && '' !== $n['nominator_email'] ) {
			AIADN_Referrals::name_referrer( 'school', $school_id, $n['org_name'], $n['nominator_email'] ); // The headteacher still decides whether they are told.
		}
	}

	/** "Do not contact me": this school address is never invited again. */
	public static function opt_out( int $id ): void {
		global $wpdb;
		$n = self::get( $id );
		if ( $n ) {
			$wpdb->update( self::table(), array( 'status' => 'opted_out', 'school_email' => '' ), array( 'school_email_hash' => $n['school_email_hash'] ) ); // phpcs:ignore WordPress.DB
		}
	}

	/** Counts for the programme team. */
	public static function summary(): array {
		global $wpdb;
		$out = array( 'unverified' => 0, 'sent' => 0, 'registered' => 0, 'opted_out' => 0, 'blocked' => 0 );
		foreach ( (array) $wpdb->get_results( 'SELECT status, COUNT(*) AS n FROM ' . self::table() . ' GROUP BY status', ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			$out[ $r['status'] ] = (int) $r['n'];
		}
		return $out;
	}
}
