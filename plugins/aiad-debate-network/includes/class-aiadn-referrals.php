<?php
/**
 * "Who introduced you?" A school, or a judge, names an organisation and a person there.
 *
 * The platform passes the details along and says clearly who is responsible for what. Nothing is sent to
 * the organisation until the school's headteacher (for a school) or the judge (for a judge) agrees. Then the
 * organisation gets an email with a link to its dashboard, and a short update at three points: the school
 * is approved, its first debate is agreed, its first result is in. Every email carries a link to stop them,
 * and to say "this wasn't us", which takes the school out of the organisation's numbers.
 *
 * Status: named (waiting for the headteacher or judge) -> shared, or not_shared. shared -> disowned.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Referrals {

	const DASHBOARD_TTL = 30 * DAY_IN_SECONDS;
	const MANAGE_TTL    = 90 * DAY_IN_SECONDS;

	private static function table(): string {
		return AIADN_Database::table( 'referrals' );
	}

	/* ------------------------------------------------------------------ */
	/* Records                                                             */
	/* ------------------------------------------------------------------ */

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function for_subject( string $type, int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE subject_type = %s AND subject_id = %d', $type, $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function partner( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'partners' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** Record who was named. Nothing is sent. Returns the referral id, or 0 if nothing was named. */
	public static function name_referrer( string $type, int $subject_id, string $org, string $email ): int {
		global $wpdb;
		$org   = mb_substr( trim( $org ), 0, 255 );
		$email = AIADN_Util::normalise_email( $email );
		if ( '' === $org || ! is_email( $email ) || self::for_subject( $type, $subject_id ) ) {
			return 0;
		}
		$wpdb->insert( // phpcs:ignore WordPress.DB
			self::table(),
			array( 'subject_type' => $type, 'subject_id' => $subject_id, 'org_name' => $org, 'referrer_email' => $email, 'status' => 'named', 'created_at' => AIADN_Util::now() )
		);
		return (int) $wpdb->insert_id;
	}

	/**
	 * The organisation behind an email address: its domain, or the whole address for a personal one.
	 * Colleagues at the same organisation share one dashboard, however each school spelt its name.
	 */
	public static function party_key( string $email ): string {
		$email  = AIADN_Util::normalise_email( $email );
		$domain = AIADN_Util::email_domain( $email );
		return ( '' === $domain || AIADN_Util::is_free_mail_domain( $domain ) ) ? $email : $domain;
	}

	private static function partner_for( string $email, string $org ): int {
		global $wpdb;
		$key   = self::party_key( $email );
		$table = AIADN_Database::table( 'partners' );
		$id    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE party_key = %s", $key ) ); // phpcs:ignore WordPress.DB
		if ( ! $id ) {
			$wpdb->insert( $table, array( 'name' => $org, 'party_key' => $key, 'created_at' => AIADN_Util::now() ) ); // phpcs:ignore WordPress.DB
			$id = (int) $wpdb->insert_id;
		}
		return $id;
	}

	/* ------------------------------------------------------------------ */
	/* The decision                                                        */
	/* ------------------------------------------------------------------ */

	/**
	 * The headteacher (or the judge) says yes or no to telling the organisation.
	 *
	 * @param string $by who decided: their email address
	 */
	public static function decide( array $referral, bool $share, string $by ): void {
		global $wpdb;
		if ( 'named' !== $referral['status'] ) {
			return;
		}
		if ( ! $share ) {
			$wpdb->update( self::table(), array( 'status' => 'not_shared', 'shared_by' => $by ), array( 'id' => (int) $referral['id'] ) ); // phpcs:ignore WordPress.DB
			return;
		}
		$partner_id = self::partner_for( $referral['referrer_email'], $referral['org_name'] );
		$wpdb->update( self::table(), array( 'status' => 'shared', 'partner_id' => $partner_id, 'shared_by' => $by, 'shared_at' => AIADN_Util::now() ), array( 'id' => (int) $referral['id'] ) ); // phpcs:ignore WordPress.DB
		self::alert( self::get( (int) $referral['id'] ), 'approved' );
	}

	/** The organisation says it was not them. The school leaves its numbers and the emails stop. */
	public static function disown( array $referral ): void {
		global $wpdb;
		if ( 'shared' !== $referral['status'] ) {
			return;
		}
		$wpdb->update( self::table(), array( 'status' => 'disowned', 'disowned_at' => AIADN_Util::now() ), array( 'id' => (int) $referral['id'] ) ); // phpcs:ignore WordPress.DB
		AIADN_Mailer::send_notice( AIADN_Util::team_email(), 'A referral was disowned', ( 'judge' === $referral['subject_type'] ? 'A judge' : 'A school' ) . ' named ' . $referral['org_name'] . ' (' . $referral['referrer_email'] . ') as the organisation that introduced them, and the organisation says it was not them. It has been removed from the organisation\'s numbers.' );
	}

	/** Has this address asked for the emails to stop? Once it has, it stays stopped, for schools named later too. */
	public static function is_stopped( string $email ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . self::table() . ' WHERE referrer_email = %s AND emails_stopped = 1 LIMIT 1', AIADN_Util::normalise_email( $email ) ) ); // phpcs:ignore WordPress.DB
	}

	/** Stop every email to this address, for every school and judge it was named for. */
	public static function stop_emails( string $email ): void {
		global $wpdb;
		$wpdb->update( self::table(), array( 'emails_stopped' => 1 ), array( 'referrer_email' => AIADN_Util::normalise_email( $email ) ) ); // phpcs:ignore WordPress.DB
	}

	/* ------------------------------------------------------------------ */
	/* The emails                                                          */
	/* ------------------------------------------------------------------ */

	public static function dashboard_url( int $partner_id ): string {
		return AIADN_Front::url( 'partner', array( 't' => AIADN_Auth::issue_token( 0, 'partner', self::DASHBOARD_TTL, $partner_id, false ) ) );
	}

	public static function manage_url( int $referral_id ): string {
		return AIADN_Front::url( 'partner', array( 'r' => AIADN_Auth::issue_token( 0, 'referral', self::MANAGE_TTL, $referral_id, false ) ) );
	}

	private static function subject_name( array $referral ): string {
		if ( 'judge' === $referral['subject_type'] ) {
			$judge = AIADN_Debates::get_judge( (int) $referral['subject_id'] );
			return $judge ? $judge['name'] : 'A judge';
		}
		$school = AIADN_Schools::get( (int) $referral['subject_id'] );
		return $school ? $school['name'] : 'A school';
	}

	/**
	 * Tell the organisation, once per milestone, and only if it has been agreed and not stopped.
	 *
	 * @param string $milestone approved, agreed or result
	 */
	public static function alert( ?array $referral, string $milestone ): void {
		global $wpdb;
		if ( ! $referral || 'shared' !== $referral['status'] || ! (int) $referral['partner_id'] || self::is_stopped( $referral['referrer_email'] ) ) {
			return;
		}
		$column = 'sent_' . $milestone;
		if ( ! in_array( $milestone, array( 'approved', 'agreed', 'result' ), true ) || null !== $referral[ $column ] ) {
			return;
		}
		// Marked first, so two events at once cannot send it twice.
		$done = $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::table() . " SET {$column} = %s WHERE id = %d AND {$column} IS NULL", AIADN_Util::now(), (int) $referral['id'] ) ); // phpcs:ignore WordPress.DB
		if ( 1 !== (int) $done ) {
			return;
		}
		$who = self::subject_name( $referral );
		if ( 'judge' === $referral['subject_type'] ) {
			$lines = array(
				'approved' => array( "{$who} has agreed to judge a debate", "{$who} has agreed to judge a debate in the National AI Conversation, and said {$referral['org_name']} introduced them. They agreed we could tell you." ),
				'agreed'   => array( '', '' ),
				'result'   => array( "{$who} has judged a debate", "{$who}, who you introduced, has judged a debate in the National AI Conversation." ),
			);
		} else {
			$lines = array(
				'approved' => array( "{$who} is taking part", "{$who} has joined the National AI Conversation, and said {$referral['org_name']} introduced them. Their headteacher agreed we could tell you." ),
				'agreed'   => array( "{$who} has set up a debate", "{$who}, who you introduced, has agreed the date for its first debate." ),
				'result'   => array( "{$who} has had a debate judged", "{$who}, who you introduced, has had its first debate judged." ),
			);
		}
		list( $subject, $intro ) = $lines[ $milestone ];
		if ( '' === $subject ) {
			return;
		}
		$body  = $intro . "\n\n";
		$body .= "See how everyone you introduced is getting on. It shows totals only, never students or staff:\n" . self::dashboard_url( (int) $referral['partner_id'] ) . "\n\n";
		$body .= "Everyone is responsible for themselves: each school for its own students, each judge for their own conduct, and you for how you use these figures. They describe the schools taking part, not schools in general.\n\n";
		$body .= "Was this not you, or do you want these emails to stop?\n" . self::manage_url( (int) $referral['id'] );
		AIADN_Mailer::send_notice( $referral['referrer_email'], $subject, $body );
	}

	/** A school's first agreed debate. */
	public static function on_debate_agreed( array $debate ): void {
		foreach ( array( (int) $debate['school_a_id'], (int) $debate['school_b_id'] ) as $school_id ) {
			if ( $school_id ) {
				self::alert( self::for_subject( 'school', $school_id ), 'agreed' );
			}
		}
	}

	/** A school's first result, and the judge's first. */
	public static function on_result( array $debate ): void {
		foreach ( array( (int) $debate['school_a_id'], (int) $debate['school_b_id'] ) as $school_id ) {
			if ( $school_id ) {
				self::alert( self::for_subject( 'school', $school_id ), 'result' );
			}
		}
		if ( (int) $debate['judge_id'] ) {
			self::alert( self::for_subject( 'judge', (int) $debate['judge_id'] ), 'result' );
		}
	}

	/** Send a fresh dashboard link to an address that has been named and agreed. Returns true if one was sent. */
	public static function send_dashboard_link( string $email ): bool {
		global $wpdb;
		$email = AIADN_Util::normalise_email( $email );
		$id    = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT partner_id FROM ' . self::table() . " WHERE referrer_email = %s AND status = 'shared' AND partner_id > 0 ORDER BY id DESC LIMIT 1", $email ) ); // phpcs:ignore WordPress.DB
		if ( ! $id ) {
			return false;
		}
		return AIADN_Mailer::send_notice( $email, 'Your National AI Conversation dashboard', "Here is a link to your dashboard. It shows totals for the schools and judges you introduced, and never students or staff:\n\n" . self::dashboard_url( $id ) . "\n\nThe link works for 30 days." );
	}
}
