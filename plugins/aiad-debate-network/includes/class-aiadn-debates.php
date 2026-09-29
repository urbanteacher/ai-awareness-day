<?php
/**
 * Debates: the fixture between two schools, its judge, and everything that happens to it.
 *
 * Status flow:
 *   awaiting_opponent -> awaiting_b_approval -> matched -> proposed -> agreed -> ready
 *   (cancelled at any point after matching; declined by School B goes back to awaiting_opponent)
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Debates {

	const INVITE_TTL = 2592000; // 30 days.
	const JUDGE_TTL  = 5184000; // 60 days; the judge link stays usable until the debate is over.

	const STATUS_LABELS = array(
		'awaiting_opponent'   => 'Invite School B',
		'awaiting_b_approval' => 'Opponent being approved',
		'matched'             => 'Set the fixture',
		'proposed'            => 'Fixture proposed',
		'agreed'              => 'Waiting for the judge',
		'ready'               => 'Ready',
		'completed'           => 'Result in',
		'void'                => 'Void',
		'cancelled'           => 'Cancelled',
		'expired'             => 'Expired',
	);

	/* ------------------------------------------------------------------ */
	/* Reading                                                             */
	/* ------------------------------------------------------------------ */

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'debates' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function get_by_code( string $code ): ?array {
		global $wpdb;
		if ( '' === $code ) {
			return null;
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'debates' ) . ' WHERE code = %s', $code ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> Newest first. */
	public static function for_school( int $school_id ): array {
		global $wpdb;
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'debates' ) . ' WHERE school_a_id = %d OR school_b_id = %d ORDER BY id DESC', $school_id, $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	public static function involves( array $debate, int $school_id ): bool {
		return (int) $debate['school_a_id'] === $school_id || ( null !== $debate['school_b_id'] && (int) $debate['school_b_id'] === $school_id );
	}

	/** 'a', 'b' or '' for which side a school is on. */
	public static function side( array $debate, int $school_id ): string {
		if ( (int) $debate['school_a_id'] === $school_id ) {
			return 'a';
		}
		if ( null !== $debate['school_b_id'] && (int) $debate['school_b_id'] === $school_id ) {
			return 'b';
		}
		return '';
	}

	public static function other_school_id( array $debate, int $school_id ): int {
		return 'a' === self::side( $debate, $school_id ) ? (int) $debate['school_b_id'] : (int) $debate['school_a_id'];
	}

	/** The teacher to email for a side: whoever started or accepted the debate, else the school's lead. */
	public static function owner( array $debate, string $side ): ?array {
		$member_id = (int) ( 'a' === $side ? $debate['a_member_id'] : $debate['b_member_id'] );
		$school_id = (int) ( 'a' === $side ? $debate['school_a_id'] : ( $debate['school_b_id'] ?? 0 ) );
		$member    = $member_id ? AIADN_Schools::get_member( $member_id ) : null;
		return $member ?: ( $school_id ? AIADN_Schools::lead( $school_id ) : null );
	}

	public static function url( array $debate ): string {
		return AIADN_Front::url( 'debate', array( 'd' => $debate['code'] ) );
	}

	/** Where the debate is, as plain text: the venue, or for an online debate the host school's meeting link. */
	public static function where( array $debate ): string {
		if ( 'online' === $debate['format'] ) {
			return 'Online' . ( $debate['meeting_url'] ? ': ' . $debate['meeting_url'] : '' );
		}
		return 'In person' . ( $debate['venue'] ? ': ' . $debate['venue'] : '' );
	}

	/** The join button appears an hour before the start and stays until three hours after. */
	const JOIN_BEFORE = 3600;
	const JOIN_AFTER  = 10800;

	/** Is this an online debate that is on now (or about to be), with a link to join? */
	public static function is_live( array $debate ): bool {
		if ( 'online' !== $debate['format'] || ! $debate['meeting_url'] || ! $debate['starts_at'] || ! in_array( $debate['status'], array( 'agreed', 'ready' ), true ) ) {
			return false;
		}
		$start = strtotime( $debate['starts_at'] . ' UTC' );
		$now   = time();
		return $now >= $start - self::JOIN_BEFORE && $now <= $start + self::JOIN_AFTER;
	}

	/** Valid https meeting link, or ''. The host school pastes it, so it is checked hard: https only, no scripts. */
	public static function clean_meeting_url( string $url ): string {
		$url = trim( $url );
		if ( strlen( $url ) > 500 || preg_match( '/\s/', $url ) || ! preg_match( '#^https://#i', $url ) ) {
			return '';
		}
		$parts = wp_parse_url( $url );
		if ( empty( $parts['host'] ) || false === strpos( $parts['host'], '.' ) ) {
			return '';
		}
		return esc_url_raw( $url, array( 'https' ) );
	}

	/** The host school changes the meeting link: the other school and the judge are told. */
	public static function set_meeting_link( array $debate, string $url, int $by_school_id ): void {
		self::update( (int) $debate['id'], array( 'meeting_url' => $url ) );
		self::log( (int) $debate['id'], 'link_changed', $by_school_id );
		$debate = self::get( (int) $debate['id'] );
		$text   = "The meeting link for this online debate has changed. Please use the new one:\n\n" . self::summary( $debate ) . "\n\n" . self::url( $debate );
		self::notify_other( $debate, $by_school_id, 'The meeting link has changed', $text );
		AIADN_Calendar::send_update( $debate );
		$judge = (int) $debate['judge_id'] ? self::get_judge( (int) $debate['judge_id'] ) : null;
		if ( $judge && in_array( $judge['status'], array( 'invited', 'accepted' ), true ) ) {
			AIADN_Mailer::send_notice( $judge['email'], 'The meeting link has changed', "The link for the online debate you are judging has changed. Please use the new one:\n\n" . self::summary( $debate ) );
		}
	}

	/** One line describing the fixture, for emails. */
	public static function summary( array $debate ): string {
		$a     = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$b     = $debate['school_b_id'] ? AIADN_Schools::get( (int) $debate['school_b_id'] ) : null;
		$parts = array( ( $a['name'] ?? '' ) . ' v ' . ( $b['name'] ?? 'a school to be confirmed' ) );
		if ( $debate['starts_at'] ) {
			$parts[] = AIADN_Util::show( $debate['starts_at'] );
		}
		if ( $debate['theme'] ) {
			$parts[] = AIADN_Motions::THEMES[ $debate['theme'] ] ?? '';
		}
		if ( $debate['motion_text'] ) {
			$parts[] = 'Motion: "' . $debate['motion_text'] . '"';
		}
		if ( $debate['format'] ) {
			$parts[] = self::where( $debate );
		}
		return implode( "\n", array_filter( $parts ) );
	}

	/* ------------------------------------------------------------------ */
	/* Changing                                                            */
	/* ------------------------------------------------------------------ */

	public static function create( int $school_a_id, int $member_id ): array {
		global $wpdb;
		$table = AIADN_Database::table( 'debates' );
		for ( $i = 0; $i < 20; $i++ ) {
			$code = AIADN_Util::new_debate_code();
			if ( self::get_by_code( $code ) ) {
				continue;
			}
			$now = AIADN_Util::now();
			$ok  = $wpdb->insert( // phpcs:ignore WordPress.DB
				$table,
				array(
					'code'        => $code,
					'school_a_id' => $school_a_id,
					'a_member_id' => $member_id,
					'status'      => 'awaiting_opponent',
					'stage_at'    => $now,
					'created_at'  => $now,
					'updated_at'  => $now,
				)
			);
			if ( $ok ) {
				$debate = self::get( (int) $wpdb->insert_id );
				self::log( (int) $debate['id'], 'created', $school_a_id );
				return $debate;
			}
		}
		return array();
	}

	public static function update( int $id, array $fields ): void {
		global $wpdb;
		$fields['updated_at'] = AIADN_Util::now();
		if ( isset( $fields['status'] ) ) {
			// A change of stage restarts the reminder clock.
			$fields['stage_at']       = AIADN_Util::now();
			$fields['reminders_sent'] = 0;
		}
		$wpdb->update( AIADN_Database::table( 'debates' ), $fields, array( 'id' => $id ) ); // phpcs:ignore WordPress.DB
	}

	/** Change a value without counting it as progress: no new "last updated" time, no reminder-clock restart. */
	public static function update_quiet( int $id, array $fields ): void {
		global $wpdb;
		$wpdb->update( AIADN_Database::table( 'debates' ), $fields, array( 'id' => $id ) ); // phpcs:ignore WordPress.DB
	}

	public static function log( int $debate_id, string $event, int $school_id = 0, string $note = '' ): void {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'debate_events' ),
			array(
				'debate_id'  => $debate_id,
				'event'      => $event,
				'school_id'  => $school_id,
				'note'       => substr( $note, 0, 255 ),
				'created_at' => AIADN_Util::now(),
			)
		);
	}

	/** @return array<string,string> event => when it last happened (UTC) */
	public static function events( int $debate_id ): array {
		global $wpdb;
		$rows = (array) $wpdb->get_results( $wpdb->prepare( 'SELECT event, MAX(created_at) AS at FROM ' . AIADN_Database::table( 'debate_events' ) . ' WHERE debate_id = %d GROUP BY event', $debate_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		$out  = array();
		foreach ( $rows as $r ) {
			$out[ $r['event'] ] = $r['at'];
		}
		return $out;
	}

	/* ------------------------------------------------------------------ */
	/* Inviting School B                                                   */
	/* ------------------------------------------------------------------ */

	/** A fresh invitation link. Any earlier link stops working. */
	public static function new_invite_link( array $debate ): string {
		$token = AIADN_Auth::issue_token( (int) $debate['school_a_id'], 'debate_invite', self::INVITE_TTL, (int) $debate['id'] );
		return AIADN_Front::url( 'invite', array( 't' => $token ) );
	}

	public static function send_invite( array $debate, string $name, string $email ): bool {
		$school = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$link   = self::new_invite_link( $debate );
		self::update( (int) $debate['id'], array( 'invite_name' => $name, 'invite_email' => $email ) );
		self::log( (int) $debate['id'], 'invited', (int) $debate['school_a_id'], $email );
		return AIADN_Mailer::send_debate_invite( $email, $name, $school['name'], $debate['code'], $link );
	}

	/** Take the School B place, once. Returns false if someone else got there first. */
	public static function claim_slot( int $debate_id, int $school_b_id, int $member_b_id, string $new_status ): bool {
		global $wpdb;
		$done = $wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB
			'UPDATE ' . AIADN_Database::table( 'debates' ) . " SET school_b_id = %d, b_member_id = %d, status = %s, updated_at = %s, stage_at = %s, reminders_sent = 0 WHERE id = %d AND status = 'awaiting_opponent' AND school_a_id <> %d",
			$school_b_id,
			$member_b_id,
			$new_status,
			AIADN_Util::now(),
			AIADN_Util::now(),
			$debate_id,
			$school_b_id
		) );
		if ( 1 === (int) $done ) {
			self::log( $debate_id, 'opponent_joined', $school_b_id );
			return true;
		}
		return false;
	}

	/** School B's opponent slot goes back to open (School B declined, or never finished registering). */
	public static function reopen( array $debate, int $by_school_id, string $why ): void {
		self::update(
			(int) $debate['id'],
			array(
				'school_b_id' => null,
				'b_member_id' => 0,
				'status'      => 'awaiting_opponent',
				'starts_at'   => null,
				'proposed_by' => 0,
				'judge_id'    => 0,
			)
		);
		self::log( (int) $debate['id'], $why, $by_school_id );
	}

	/** A school has just been approved: any debate waiting on it can now match. */
	public static function on_school_approved( int $school_id ): void {
		global $wpdb;
		$ids = (array) $wpdb->get_col( $wpdb->prepare( 'SELECT id FROM ' . AIADN_Database::table( 'debates' ) . " WHERE school_b_id = %d AND status = 'awaiting_b_approval'", $school_id ) ); // phpcs:ignore WordPress.DB
		foreach ( $ids as $id ) {
			$debate = self::get( (int) $id );
			if ( $debate ) {
				self::complete_match( $debate );
			}
		}
	}

	public static function complete_match( array $debate ): void {
		self::update( (int) $debate['id'], array( 'status' => 'matched' ) );
		self::log( (int) $debate['id'], 'matched', (int) $debate['school_b_id'] );
		self::log( (int) $debate['id'], 'safeguarding_pack', 0 );
		$debate = self::get( (int) $debate['id'] );
		foreach ( array( 'a', 'b' ) as $side ) {
			$owner = self::owner( $debate, $side );
			$other = AIADN_Schools::get( self::other_school_id( $debate, (int) $debate[ 'a' === $side ? 'school_a_id' : 'school_b_id' ] ) );
			if ( $owner && $other ) {
				AIADN_Mailer::send_match( $owner['email'], $other['name'], $debate['code'], self::url( $debate ), 'a' === $side );
			}
		}
	}

	/* ------------------------------------------------------------------ */
	/* Fixture                                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * School A proposes the fixture and names the judge.
	 *
	 * @param array<string,string> $fx     age_group, theme, motion_key, motion_text, a_side, format, venue, starts_at (UTC)
	 * @param array<string,string> $judge  name, email, organisation, judge_type
	 */
	public static function propose( array $debate, array $fx, array $judge ): void {
		self::retire_pending_judge( $debate );
		$judge_id = self::create_judge( (int) $debate['id'], $judge );
		self::update(
			(int) $debate['id'],
			array_merge( $fx, array( 'status' => 'proposed', 'proposed_by' => (int) $debate['school_a_id'], 'judge_id' => $judge_id ) )
		);
		self::log( (int) $debate['id'], 'proposed', (int) $debate['school_a_id'] );
		$debate = self::get( (int) $debate['id'] );
		self::notify_other( $debate, (int) $debate['school_a_id'], 'Please review your debate', "A fixture has been proposed. Please review it and accept, suggest another date, or decline:\n\n" . self::summary( $debate ) . "\n\n" . self::url( $debate ) );
	}

	/** The other school suggests a different time and hands it back. */
	public static function suggest_date( array $debate, int $by_school_id, string $starts_at_utc ): void {
		self::update( (int) $debate['id'], array( 'starts_at' => $starts_at_utc, 'proposed_by' => $by_school_id ) );
		self::log( (int) $debate['id'], 'date_suggested', $by_school_id );
		$debate = self::get( (int) $debate['id'] );
		self::notify_other( $debate, $by_school_id, 'A new date has been suggested', "A different date has been suggested. Please review it:\n\n" . self::summary( $debate ) . "\n\n" . self::url( $debate ) );
	}

	/** Both schools have agreed the fixture: tell everyone and invite the judge. */
	public static function agree( array $debate, int $by_school_id ): void {
		self::update( (int) $debate['id'], array( 'status' => 'agreed' ) );
		self::log( (int) $debate['id'], 'agreed', $by_school_id );
		$debate = self::get( (int) $debate['id'] );
		foreach ( array( 'a', 'b' ) as $side ) {
			$owner = self::owner( $debate, $side );
			if ( $owner ) {
				AIADN_Mailer::send_notice( $owner['email'], 'Your debate is agreed', "The fixture is agreed:\n\n" . self::summary( $debate ) . "\n\nWe are inviting the judge now.\n\n" . self::url( $debate ) );
			}
		}
		$judge = self::get_judge( (int) $debate['judge_id'] );
		if ( $judge ) {
			self::invite_judge( $debate, $judge );
		}
		AIADN_Calendar::send_initial( $debate );
	}

	public static function cancel( array $debate, int $by_school_id ): void {
		self::update( (int) $debate['id'], array( 'status' => 'cancelled' ) );
		self::log( (int) $debate['id'], 'cancelled', $by_school_id );
		$debate = self::get( (int) $debate['id'] );
		self::notify_other( $debate, $by_school_id, 'A debate has been cancelled', "This debate has been cancelled:\n\n" . self::summary( $debate ) );
		AIADN_Calendar::send_cancel( $debate );
		$judge = self::get_judge( (int) $debate['judge_id'] );
		if ( $judge && in_array( $judge['status'], array( 'invited', 'accepted' ), true ) ) {
			AIADN_Mailer::send_notice( $judge['email'], 'The debate on ' . AIADN_Util::show( (string) $debate['starts_at'], 'j M' ) . ' is cancelled', "Thank you for offering to judge. This debate has been cancelled, so there is nothing more to do:\n\n" . self::summary( $debate ) );
		}
	}

	/** Email the owner of whichever side did NOT make the change. */
	public static function notify_other( array $debate, int $acting_school_id, string $subject, string $body ): void {
		$acting = self::side( $debate, $acting_school_id );
		$owner  = self::owner( $debate, 'a' === $acting ? 'b' : 'a' );
		if ( $owner ) {
			AIADN_Mailer::send_notice( $owner['email'], $subject, $body );
		}
	}

	/** @return array<string,bool> ticked items for a side */
	public static function checklist( array $debate, string $side ): array {
		$raw  = (string) ( 'a' === $side ? $debate['checklist_a'] : $debate['checklist_b'] );
		$data = json_decode( $raw, true );
		return is_array( $data ) ? $data : array();
	}

	public static function save_checklist( array $debate, string $side, array $ticks ): void {
		self::update( (int) $debate['id'], array( 'a' === $side ? 'checklist_a' : 'checklist_b' => wp_json_encode( $ticks ) ) );
	}

	/* ------------------------------------------------------------------ */
	/* Judges                                                              */
	/* ------------------------------------------------------------------ */

	public static function get_judge( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'judges' ) . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** @param array<string,string> $data name, email, organisation, judge_type */
	public static function create_judge( int $debate_id, array $data ): int {
		global $wpdb;
		$wpdb->insert( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'judges' ),
			array(
				'debate_id'    => $debate_id,
				'name'         => $data['name'],
				'email'        => $data['email'],
				'organisation' => $data['organisation'],
				'judge_type'   => $data['judge_type'],
				'status'       => 'pending',
				'created_at'   => AIADN_Util::now(),
			)
		);
		return (int) $wpdb->insert_id;
	}

	/** An earlier judge who was never invited is replaced, not left behind. */
	private static function retire_pending_judge( array $debate ): void {
		$old = (int) $debate['judge_id'] ? self::get_judge( (int) $debate['judge_id'] ) : null;
		if ( $old && in_array( $old['status'], array( 'pending', 'declined' ), true ) ) {
			global $wpdb;
			$wpdb->update( AIADN_Database::table( 'judges' ), array( 'status' => 'replaced' ), array( 'id' => (int) $old['id'] ) ); // phpcs:ignore WordPress.DB
		}
	}

	/** Replace the judge on an agreed fixture and invite the new one. */
	public static function change_judge( array $debate, array $judge, int $by_school_id ): void {
		$old = (int) $debate['judge_id'] ? self::get_judge( (int) $debate['judge_id'] ) : null;
		if ( $old && in_array( $old['status'], array( 'invited', 'accepted' ), true ) ) {
			AIADN_Mailer::send_notice( $old['email'], 'You are no longer needed as judge', "The schools have chosen a different judge for this debate, so there is nothing more to do:\n\n" . self::summary( $debate ) );
		}
		global $wpdb;
		if ( $old ) {
			AIADN_Calendar::send_cancel_to_judge( $debate, $old );
			$wpdb->update( AIADN_Database::table( 'judges' ), array( 'status' => 'replaced' ), array( 'id' => (int) $old['id'] ) ); // phpcs:ignore WordPress.DB
		}
		$judge_id = self::create_judge( (int) $debate['id'], $judge );
		self::update( (int) $debate['id'], array( 'judge_id' => $judge_id, 'status' => 'agreed' ) );
		self::log( (int) $debate['id'], 'judge_changed', $by_school_id );
		self::invite_judge( self::get( (int) $debate['id'] ), self::get_judge( $judge_id ) );
	}

	/** A fresh shortcut link for a judge. Any earlier link for them stops working. */
	public static function judge_link( array $debate, array $judge ): string {
		$token = AIADN_Auth::issue_token( (int) $debate['school_a_id'], 'judge', self::JUDGE_TTL, (int) $judge['id'] );
		return AIADN_Front::url( 'judge', array( 't' => $token ) );
	}

	/** Email the judge how to get in: school code + their email, or the shortcut link. */
	public static function invite_judge( array $debate, array $judge ): bool {
		global $wpdb;
		$wpdb->update( AIADN_Database::table( 'judges' ), array( 'status' => 'invited', 'invited_at' => AIADN_Util::now() ), array( 'id' => (int) $judge['id'] ) ); // phpcs:ignore WordPress.DB
		self::log( (int) $debate['id'], 'judge_invited', 0, $judge['email'] );
		$school = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$token  = AIADN_Auth::issue_token( (int) $debate['school_a_id'], 'judge', self::JUDGE_TTL, (int) $judge['id'] );
		return AIADN_Mailer::send_judge_invitation(
			$judge['email'],
			$judge['name'],
			self::summary( $debate ),
			(string) $school['code'],
			AIADN_Front::url( 'join', array( 'c' => $school['code'] ) ),
			AIADN_Front::url( 'judge', array( 't' => $token ) )
		);
	}

	public static function judge_respond( array $debate, array $judge, bool $accept, bool $name_public ): void {
		global $wpdb;
		$wpdb->update( // phpcs:ignore WordPress.DB
			AIADN_Database::table( 'judges' ),
			array(
				'status'       => $accept ? 'accepted' : 'declined',
				'ack'          => $accept ? 1 : 0,
				'name_public'  => ( $accept && $name_public ) ? 1 : 0,
				'responded_at' => AIADN_Util::now(),
			),
			array( 'id' => (int) $judge['id'] )
		);
		if ( $accept && 'agreed' === $debate['status'] ) {
			self::update( (int) $debate['id'], array( 'status' => 'ready' ) );
		}
		self::log( (int) $debate['id'], $accept ? 'judge_accepted' : 'judge_declined', 0 );
		$debate = self::get( (int) $debate['id'] );
		if ( $accept ) {
			AIADN_Calendar::send_to_judge( $debate, self::get_judge( (int) $judge['id'] ) );
		}
		$text   = $accept
			? $judge['name'] . " has accepted and will judge this debate:\n\n" . self::summary( $debate )
			: $judge['name'] . " can't make it. Please choose another judge from the debate page:\n\n" . self::summary( $debate );
		foreach ( array( 'a', 'b' ) as $side ) {
			$owner = self::owner( $debate, $side );
			if ( $owner ) {
				AIADN_Mailer::send_notice( $owner['email'], $accept ? 'Your judge has accepted' : 'Your judge cannot make it', $text . "\n\n" . self::url( $debate ) );
			}
		}
	}

	public static function judge_involves_school( array $judge, int $school_id ): bool {
		$debate = self::get( (int) $judge['debate_id'] );
		return $debate && self::involves( $debate, $school_id ) && ! in_array( $debate['status'], array( 'cancelled', 'expired' ), true );
	}

	/** The most recent live judge row for this email at a school, or null. */
	public static function judge_for_school_email( int $school_id, string $email ): ?array {
		$rows = self::judges_for_school_email( $school_id, $email );
		return $rows ? $rows[0] : null;
	}

	/** @return array<int,array<string,mixed>> */
	public static function judges_for_school_email( int $school_id, string $email ): array {
		global $wpdb;
		$j = AIADN_Database::table( 'judges' );
		$d = AIADN_Database::table( 'debates' );
		return (array) $wpdb->get_results( $wpdb->prepare( // phpcs:ignore WordPress.DB
			"SELECT j.* FROM {$j} j INNER JOIN {$d} d ON d.id = j.debate_id WHERE j.email = %s AND j.status IN ('invited','accepted') AND d.status NOT IN ('cancelled','expired') AND (d.school_a_id = %d OR d.school_b_id = %d) ORDER BY d.starts_at ASC, j.id DESC",
			$email,
			$school_id,
			$school_id
		), ARRAY_A );
	}

	/* ------------------------------------------------------------------ */
	/* Tracker (screen 8)                                                  */
	/* ------------------------------------------------------------------ */

	/**
	 * Every stage of the pipeline for one debate.
	 *
	 * @return array<int,array{label:string,state:string,detail:string}> state is done, now, todo or issue
	 */
	public static function tracker( array $debate, int $school_id = 0 ): array {
		$events  = self::events( (int) $debate['id'] );
		$status  = $debate['status'];
		$card    = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
		$held    = AIADN_Issues::is_held( (int) $debate['id'] );
		$cert    = $school_id ? AIADN_Certificates::get_for_school( $school_id ) : null;
		$prog    = $school_id ? AIADN_Certificates::progress( $school_id ) : null;
		$judge   = (int) $debate['judge_id'] ? self::get_judge( (int) $debate['judge_id'] ) : null;
		$when    = static fn( string $e ) => isset( $events[ $e ] ) ? AIADN_Util::show( $events[ $e ], 'j M' ) : '';
		$matched = in_array( $status, array( 'matched', 'proposed', 'agreed', 'ready' ), true );
		$agreed  = in_array( $status, array( 'agreed', 'ready' ), true );

		$stages = array(
			array( 'School lookup', true, $when( 'created' ) ),
			array( 'Teacher verification', true, $when( 'created' ) ),
			array( 'Debate ID', true, $debate['code'] ),
			array( 'Invite School B', ! in_array( $status, array( 'awaiting_opponent' ), true ), $when( 'opponent_joined' ) ?: $when( 'invited' ) ),
			array( 'Match', $matched, $when( 'matched' ) ),
			array( 'Safeguarding pack', $matched, $matched ? 'sent ' . $when( 'safeguarding_pack' ) : '' ),
			array( 'Fixture', $agreed, $agreed && $debate['starts_at'] ? AIADN_Util::show( $debate['starts_at'] ) : ( 'proposed' === $status ? 'waiting for agreement' : '' ) ),
			array( 'Judge', $judge && 'accepted' === $judge['status'], $judge ? ( 'accepted' === $judge['status'] ? $judge['name'] : ( 'declined' === $judge['status'] ? $judge['name'] . " can't make it" : ( 'invited' === $judge['status'] ? 'waiting for ' . $judge['name'] : '' ) ) ) : '' ),
			array( 'Scorecard', $card && 'submitted' === $card['status'], $card ? ( 'submitted' === $card['status'] ? $when( 'result_submitted' ) : 'draft saved' ) : ( 'ready' === $status ? 'opens ' . AIADN_Util::show( gmdate( 'Y-m-d H:i:s', AIADN_Scorecards::opens_at( $debate ) ), 'j M, H:i' ) : '' ) ),
			array( 'Result', 'completed' === $status && ! $held, $held ? 'issue logged, result on hold' : ( 'void' === $status ? 'void' : ( 'completed' === $status && $card ? $card['a_total'] . ' - ' . $card['b_total'] : '' ) ) ),
			array( 'Certificate', $cert && 'issued' === $cert['status'], $cert ? ( 'issued' === $cert['status'] ? 'issued' : 'withdrawn' ) : ( $prog ? $prog['have'] . ' of ' . $prog['need'] : '' ) ),
		);

		$out       = array();
		$found_now = false;
		foreach ( $stages as $stage ) {
			list( $label, $done, $detail ) = $stage;
			if ( ( $held || 'void' === $status ) && 'Result' === $label ) {
				$state     = 'issue';
				$found_now = true;
			} elseif ( in_array( $status, array( 'cancelled', 'expired' ), true ) && ! $done ) {
				$state = $found_now ? 'todo' : 'issue';
				$found_now = true;
				$detail = ! $detail && 'issue' === $state ? ( 'expired' === $status ? 'Expired' : 'Cancelled' ) : $detail;
			} elseif ( $done ) {
				$state = 'done';
			} elseif ( ! $found_now ) {
				$state     = 'now';
				$found_now = true;
			} else {
				$state = 'todo';
			}
			$out[] = array( 'label' => $label, 'state' => $state, 'detail' => $detail );
		}
		return $out;
	}
}
