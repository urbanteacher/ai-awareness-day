<?php
/**
 * Keeping personal details only as long as they are needed (section 23.12 of the brief).
 *
 * Personal data here means the contact details of teachers, headteachers, judges and the people who introduced a
 * school or a judge: names, email addresses and job titles, plus anything typed in that could name someone.
 * Student Voice answers hold no personal data and are kept, as are the totals, scores and results.
 *
 * Two ways in:
 *  - erase_email(): one person asks, and proves the address is theirs with an emailed code;
 *  - end_of_campaign(): everyone's contact details go on 31 August 2027, by a daily check that also warns the
 *    programme team 14 days before.
 *
 * Nothing is kept about what was deleted except counts.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Privacy {

	const HOOK         = 'aiadn_retention';
	const DEFAULT_DATE = '2027-08-31';
	const LOG_OPTION   = 'aiadn_retention_log';

	public static function register(): void {
		add_action( 'init', array( __CLASS__, 'schedule' ) );
		add_action( self::HOOK, array( __CLASS__, 'maybe_run' ) );
	}

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + 600, 'daily', self::HOOK );
		}
	}

	public static function unschedule(): void {
		wp_clear_scheduled_hook( self::HOOK );
	}

	/** The last day of the campaign. Everything personal goes the day after. Set AIADN_RETENTION_DATE (Y-m-d) to change it. */
	public static function retention_date(): string {
		return ( defined( 'AIADN_RETENTION_DATE' ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) AIADN_RETENTION_DATE ) ) ? (string) AIADN_RETENTION_DATE : self::DEFAULT_DATE;
	}

	private static function t( string $name ): string {
		return AIADN_Database::table( $name );
	}

	/** @return array<int,array<string,mixed>> what has been done: date, scope and counts. Never a name or address. */
	public static function log(): array {
		$log = get_option( self::LOG_OPTION, array() );
		return is_array( $log ) ? $log : array();
	}

	private static function record( string $what, array $counts ): void {
		$log   = self::log();
		$log[] = array( 'at' => AIADN_Util::now(), 'what' => $what, 'counts' => $counts );
		update_option( self::LOG_OPTION, array_slice( $log, -30 ), false );
	}

	/* ------------------------------------------------------------------ */
	/* One person                                                          */
	/* ------------------------------------------------------------------ */

	/** Do we hold anything under this address? */
	public static function holds( string $email ): bool {
		global $wpdb;
		$email = AIADN_Util::normalise_email( $email );
		foreach ( array( array( 'members', 'email' ), array( 'judges', 'email' ), array( 'referrals', 'referrer_email' ), array( 'issues', 'reporter_email' ), array( 'debates', 'invite_email' ), array( 'nominations', 'nominator_email' ), array( 'nominations', 'school_email' ) ) as $pair ) {
			if ( (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::t( $pair[0] ) . " WHERE {$pair[1]} = %s", $email ) ) > 0 ) { // phpcs:ignore WordPress.DB
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove one person's details wherever they are. Debates, scores and results stay, without them.
	 *
	 * @return array<string,int> what was changed
	 */
	public static function erase_email( string $email ): array {
		global $wpdb;
		$email = AIADN_Util::normalise_email( $email );
		$out   = array( 'members' => 0, 'judges' => 0, 'codes' => 0, 'notes' => 0, 'incidents' => 0, 'invitations' => 0, 'introductions' => 0, 'links' => 0 );
		if ( ! is_email( $email ) ) {
			return $out;
		}
		$gone = static fn( int $id ): string => 'deleted-' . $id . '@deleted.invalid';

		// Teachers and senior leaders: the record stays so debates still point somewhere, but nobody can sign in as it.
		foreach ( (array) $wpdb->get_results( $wpdb->prepare( 'SELECT id, school_id FROM ' . self::t( 'members' ) . ' WHERE email = %s', $email ), ARRAY_A ) as $m ) { // phpcs:ignore WordPress.DB
			$wpdb->update( self::t( 'members' ), array( 'email' => $gone( (int) $m['id'] ), 'name' => '', 'job_title' => '', 'role' => 'left', 'email_verified_at' => null ), array( 'id' => (int) $m['id'] ) ); // phpcs:ignore WordPress.DB
			++$out['members'];
		}
		$wpdb->update( self::t( 'schools' ), array( 'slt_email' => '' ), array( 'slt_email' => $email ) ); // phpcs:ignore WordPress.DB
		$out['codes'] = (int) $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::t( 'login_codes' ) . ' WHERE email = %s', $email ) ); // phpcs:ignore WordPress.DB

		// Judges: one who was still to judge has to be replaced, so the school is told.
		foreach ( (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::t( 'judges' ) . ' WHERE email = %s', $email ), ARRAY_A ) as $j ) { // phpcs:ignore WordPress.DB
			$debate = AIADN_Debates::get( (int) $j['debate_id'] );
			$fields = array( 'email' => $gone( (int) $j['id'] ), 'name' => '', 'organisation' => '', 'partner_ref' => '', 'name_public' => 0 );
			if ( $debate && in_array( $j['status'], array( 'pending', 'invited', 'accepted' ), true ) && in_array( $debate['status'], array( 'proposed', 'agreed', 'ready' ), true ) ) {
				$fields['status'] = 'declined';
				if ( 'ready' === $debate['status'] ) {
					AIADN_Debates::update( (int) $debate['id'], array( 'status' => 'agreed' ) );
				}
				$owner = AIADN_Debates::owner( $debate, 'a' );
				if ( $owner ) {
					AIADN_Mailer::send_notice( $owner['email'], 'Your judge has withdrawn', "The judge for this debate has asked us to delete their details, so they can no longer judge. Please choose another judge from the debate page:\n\n" . AIADN_Debates::url( $debate ) );
				}
			}
			$wpdb->update( self::t( 'judges' ), $fields, array( 'id' => (int) $j['id'] ) ); // phpcs:ignore WordPress.DB
			$wpdb->update( self::t( 'scorecards' ), array( 'submitted_by' => '' ), array( 'debate_id' => (int) $j['debate_id'], 'submitted_by' => $email ) ); // phpcs:ignore WordPress.DB
			++$out['judges'];
		}
		$wpdb->update( self::t( 'scorecards' ), array( 'submitted_by' => '' ), array( 'submitted_by' => $email ) ); // phpcs:ignore WordPress.DB

		// Where the address was written into the debate's history.
		$out['notes'] = (int) $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'debate_events' ) . " SET note = '' WHERE note = %s", $email ) ); // phpcs:ignore WordPress.DB
		// An incident someone reported stays as a safeguarding record, without their address.
		$out['incidents'] = (int) $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'issues' ) . " SET reporter_email = '' WHERE reporter_email = %s", $email ) ); // phpcs:ignore WordPress.DB
		$out['invitations'] = (int) $wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'debates' ) . " SET invite_email = '', invite_name = '' WHERE invite_email = %s", $email ) ); // phpcs:ignore WordPress.DB

		// Someone named as having introduced a school or a judge: they leave the organisation's numbers.
		foreach ( (array) $wpdb->get_results( $wpdb->prepare( 'SELECT id, partner_id FROM ' . self::t( 'referrals' ) . ' WHERE referrer_email = %s', $email ), ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			$wpdb->update( self::t( 'referrals' ), array( 'referrer_email' => '', 'status' => 'disowned', 'emails_stopped' => 1 ), array( 'id' => (int) $r['id'] ) ); // phpcs:ignore WordPress.DB
			$out['links'] += (int) $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::t( 'tokens' ) . " WHERE kind = 'referral' AND ref_id = %d", (int) $r['id'] ) ); // phpcs:ignore WordPress.DB
			++$out['introductions'];
		}
		foreach ( (array) $wpdb->get_results( $wpdb->prepare( 'SELECT id FROM ' . self::t( 'partners' ) . ' WHERE party_key = %s', $email ), ARRAY_A ) as $p ) { // phpcs:ignore WordPress.DB
			$wpdb->update( self::t( 'partners' ), array( 'party_key' => 'deleted-' . (int) $p['id'] ), array( 'id' => (int) $p['id'] ) ); // phpcs:ignore WordPress.DB
			$out['links'] += (int) $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::t( 'tokens' ) . " WHERE kind = 'partner' AND ref_id = %d", (int) $p['id'] ) ); // phpcs:ignore WordPress.DB
		}
		// Nominations: as the person who nominated, or as the address a school was to be invited at.
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'nominations' ) . " SET nominator_name = '', nominator_email = '', org_name = '' WHERE nominator_email = %s", $email ) ); // phpcs:ignore WordPress.DB
		$wpdb->query( $wpdb->prepare( 'UPDATE ' . self::t( 'nominations' ) . " SET school_email = '' WHERE school_email = %s", $email ) ); // phpcs:ignore WordPress.DB
		self::record( 'one person asked for their details to be deleted', $out );
		return $out;
	}

	/* ------------------------------------------------------------------ */
	/* Everyone, at the end of the campaign                                */
	/* ------------------------------------------------------------------ */

	/**
	 * Delete the contact details of everyone (or of everyone linked to some schools). With $dry_run, only count.
	 *
	 * @param array<int,int>|null $school_ids limit to these schools, or null for all
	 * @return array<string,int> label => rows
	 */
	public static function end_of_campaign( ?array $school_ids = null, bool $dry_run = false ): array {
		global $wpdb;
		if ( null === $school_ids ) {
			$sc = $dc = $rc = '1=1';
		} else {
			$ids = implode( ',', array_map( 'intval', $school_ids ) ) ?: '0';
			$d   = (array) $wpdb->get_col( 'SELECT id FROM ' . self::t( 'debates' ) . " WHERE school_a_id IN ({$ids}) OR school_b_id IN ({$ids})" ); // phpcs:ignore WordPress.DB
			$did = implode( ',', array_map( 'intval', $d ) ) ?: '0';
			$j   = (array) $wpdb->get_col( 'SELECT id FROM ' . self::t( 'judges' ) . " WHERE debate_id IN ({$did})" ); // phpcs:ignore WordPress.DB
			$jid = implode( ',', array_map( 'intval', $j ) ) ?: '0';
			$sc  = "school_id IN ({$ids})";
			$dc  = "debate_id IN ({$did})";
			$rc  = "((subject_type = 'school' AND subject_id IN ({$ids})) OR (subject_type = 'judge' AND subject_id IN ({$jid})))";
			$dbc = "id IN ({$did})";
			$sid = "id IN ({$ids})";
		}
		$dbc = $dbc ?? '1=1';
		$sid = $sid ?? '1=1';

		// label, table, where, SET clause (null means delete the rows)
		$ops = array(
			array( 'Teacher and headteacher records', 'members', $sc, null ),
			array( 'Sign-in codes', 'login_codes', $sc, null ),
			array( 'Links sent by email', 'tokens', $sc, null ),
			array( 'Class PINs', 'class_pins', $sc, null ),
			array( 'Headteacher emails on schools', 'schools', "{$sid} AND slt_email <> ''", "slt_email = ''" ),
			array( 'Judge email addresses', 'judges', "{$dc} AND email NOT LIKE 'deleted-%'", "email = CONCAT('deleted-', id, '@deleted.invalid')" ),
			array( 'Judge names not shown publicly', 'judges', "{$dc} AND name_public = 0 AND (name <> '' OR organisation <> '' OR partner_ref <> '')", "name = '', organisation = '', partner_ref = ''" ),
			array( 'Scorecard comments and who submitted', 'scorecards', "{$dc} AND (submitted_by <> '' OR comment_a <> '' OR comment_b <> '')", "submitted_by = '', comment_a = '', comment_b = ''" ),
			array( 'Notes in debate histories', 'debate_events', "{$dc} AND note <> ''", "note = ''" ),
			array( 'Incident emails and details', 'issues', "{$dc} AND (reporter_email <> '' OR details <> '')", "reporter_email = '', details = ''" ),
			array( 'Invitations and meeting links', 'debates', "{$dbc} AND (invite_name <> '' OR invite_email <> '' OR meeting_url <> '')", "invite_name = '', invite_email = '', meeting_url = ''" ),
			array( 'Emails of people who introduced a school or judge', 'referrals', "{$rc} AND referrer_email <> ''", "referrer_email = ''" ),
		);
		if ( null === $school_ids ) {
			$ops[] = array( 'Nominations: who nominated, and the school addresses', 'nominations', "(nominator_email <> '' OR school_email <> '' OR school_email_hash <> '')", "nominator_name = '', nominator_email = '', org_name = '', school_email = '', school_email_hash = ''" );
		}
		if ( null === $school_ids ) {
			$ops[] = array( 'Personal addresses used to identify an organisation', 'partners', "party_key LIKE '%@%'", "party_key = CONCAT('deleted-', id)" );
		}
		$counts = array();
		foreach ( $ops as $op ) {
			list( $label, $table, $where, $set ) = $op;
			$t = self::t( $table );
			if ( $dry_run ) {
				$counts[ $label ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$t} WHERE {$where}" ); // phpcs:ignore WordPress.DB
			} else {
				$counts[ $label ] = (int) $wpdb->query( null === $set ? "DELETE FROM {$t} WHERE {$where}" : "UPDATE {$t} SET {$set} WHERE {$where}" ); // phpcs:ignore WordPress.DB
			}
		}
		if ( ! $dry_run ) {
			self::record( null === $school_ids ? 'end of campaign, everyone' : 'end of campaign, some schools', $counts );
		}
		return $counts;
	}

	/**
	 * The daily check: warn the programme team 14 days before, and delete the day after the last day of the campaign.
	 * The optional arguments are for tests.
	 *
	 * @param array<int,int>|null $scope limit the deletion to these schools (tests only)
	 * @return string what happened: '', 'warned' or 'deleted'
	 */
	public static function maybe_run( ?string $today = null, ?array $scope = null ): string {
		$date  = self::retention_date();
		$today = $today ?: wp_date( 'Y-m-d' );
		$warn  = gmdate( 'Y-m-d', strtotime( $date . ' -14 days' ) );
		if ( $today > $date && ! get_option( 'aiadn_retention_done_' . $date ) ) {
			$counts = self::end_of_campaign( $scope, false );
			update_option( 'aiadn_retention_done_' . $date, AIADN_Util::now(), false );
			$lines = array();
			foreach ( $counts as $label => $n ) {
				$lines[] = "{$label}: {$n}";
			}
			AIADN_Mailer::send_notice( AIADN_Util::team_email(), 'Personal details have been deleted', "The campaign ended on {$date}, and teacher, headteacher and judge contact details have been deleted, as promised. Anonymous totals, scores and results are kept.\n\n" . implode( "\n", $lines ) );
			return 'deleted';
		}
		if ( $today >= $warn && $today <= $date && ! get_option( 'aiadn_retention_warned_' . $date ) ) {
			update_option( 'aiadn_retention_warned_' . $date, AIADN_Util::now(), false );
			AIADN_Mailer::send_notice( AIADN_Util::team_email(), 'Personal details will be deleted after ' . $date, "Everyone's contact details (teachers, headteachers, judges and the people who introduced them) will be deleted automatically the day after {$date}. Anonymous totals, scores and results are kept.\n\nDownload any figures you want to keep as CSV from the programme team page first. You can preview exactly what will go in the Data retention section there." );
			return 'warned';
		}
		return '';
	}
}
