<?php
/**
 * Find a Debate: a school with no opponent publishes a Debate Request, and another approved school asks to
 * take it up (section 10 of the brief).
 *
 * The host school chooses which school to accept, because it is the one taking responsibility for hosting.
 * Once accepted, the normal debate flow starts. Only school names, a broad area, and the details of the
 * request are shown to other schools, and only to signed-in teachers. No teacher name, no email, nothing
 * about students.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Find {

	const FORMATS = array( 'either' => 'Either', 'in_person' => 'In person', 'online' => 'Online' );
	const HOST    = array( 'either' => 'Either', 'yes' => 'We can host', 'no' => 'We would like to be hosted' );
	/** Who can see the request: schools in the host's own region, or any school. (Stored in req_travel.) */
	const TRAVEL  = array( 'region' => 'Schools in my region', 'anywhere' => 'Any school' );

	/** A school may have this many requests open at once, and this many of its own asks waiting. */
	const MAX_OPEN    = 3;
	const MAX_PENDING = 5;

	private static function table(): string {
		return AIADN_Database::table( 'debate_requests' );
	}

	/* ------------------------------------------------------------------ */
	/* Publishing                                                          */
	/* ------------------------------------------------------------------ */

	public static function count_open( int $school_id ): int {
		global $wpdb;
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . AIADN_Database::table( 'debates' ) . " WHERE school_a_id = %d AND status = 'awaiting_opponent' AND open_request = 1", $school_id ) ); // phpcs:ignore WordPress.DB
	}

	/**
	 * Publish a debate's request. Returns an error message, or '' on success.
	 *
	 * @param array<string,string> $f age_group, theme, dates, format, host, travel
	 */
	public static function publish( array $debate, array $f ): string {
		if ( 'awaiting_opponent' !== $debate['status'] ) {
			return 'This debate already has an opponent.';
		}
		if ( ! isset( AIADN_Motions::AGES[ $f['age_group'] ] ) ) {
			return 'Choose an age group.';
		}
		if ( ! isset( AIADN_Motions::THEMES[ $f['theme'] ] ) ) {
			return 'Choose a theme.';
		}
		if ( ! isset( self::FORMATS[ $f['format'] ] ) || ! isset( self::HOST[ $f['host'] ] ) || ! isset( self::TRAVEL[ $f['travel'] ] ) ) {
			return 'Choose an option for format, hosting and travel.';
		}
		$dates = mb_substr( trim( $f['dates'] ), 0, 200 );
		if ( strlen( $dates ) < 3 ) {
			return 'Say when suits, for example "any Tuesday in February".';
		}
		if ( ! (int) $debate['open_request'] && self::count_open( (int) $debate['school_a_id'] ) >= self::MAX_OPEN ) {
			return 'You already have ' . self::MAX_OPEN . ' open requests. Take one down, or wait for a match, before adding another.';
		}
		AIADN_Debates::update_quiet(
			(int) $debate['id'],
			array(
				'age_group'    => $f['age_group'],
				'theme'        => $f['theme'],
				'req_dates'    => $dates,
				'req_format'   => $f['format'],
				'req_host'     => $f['host'],
				'req_travel'   => $f['travel'],
				'open_request' => 1,
				'opened_at'    => AIADN_Util::now(),
			)
		);
		AIADN_Debates::log( (int) $debate['id'], 'request_published', (int) $debate['school_a_id'] );
		return '';
	}

	/** Take the request down. Anyone waiting is told it is no longer available. */
	public static function unpublish( array $debate ): void {
		AIADN_Debates::update_quiet( (int) $debate['id'], array( 'open_request' => 0 ) );
		AIADN_Debates::log( (int) $debate['id'], 'request_unpublished', (int) $debate['school_a_id'] );
		self::close_pending( (int) $debate['id'], 0, 'This request has been taken down.' );
	}

	/* ------------------------------------------------------------------ */
	/* The board                                                           */
	/* ------------------------------------------------------------------ */

	/**
	 * Open requests from other schools, newest first.
	 *
	 * @param array<string,string> $filters age, theme, format, region, all (any non-empty: show every region)
	 * @return array<int,array<string,mixed>> each with 'debate', 'school', 'region', 'near' (same area as the viewer), 'asked' (this school's request status or '')
	 */
	public static function board( int $viewer_school_id, array $filters = array() ): array {
		global $wpdb;
		$d     = AIADN_Database::table( 'debates' );
		$where = "d.status = 'awaiting_opponent' AND d.open_request = 1 AND d.school_a_id <> %d";
		$args  = array( $viewer_school_id );
		if ( ! empty( $filters['age'] ) && isset( AIADN_Motions::AGES[ $filters['age'] ] ) ) {
			$where .= ' AND d.age_group = %s';
			$args[] = $filters['age'];
		}
		if ( ! empty( $filters['theme'] ) && isset( AIADN_Motions::THEMES[ $filters['theme'] ] ) ) {
			$where .= ' AND d.theme = %s';
			$args[] = $filters['theme'];
		}
		if ( ! empty( $filters['format'] ) && isset( AIADN_Motions::FORMATS[ $filters['format'] ] ) ) {
			$where .= " AND d.req_format IN ('either', %s)";
			$args[] = $filters['format'];
		}
		$rows = (array) $wpdb->get_results( $wpdb->prepare( "SELECT d.* FROM {$d} d WHERE {$where} ORDER BY d.opened_at DESC, d.id DESC LIMIT 100", ...$args ), ARRAY_A ); // phpcs:ignore WordPress.DB
		$viewer = AIADN_Schools::get( $viewer_school_id );
		$home   = $viewer ? AIADN_Regions::for_postcode( (string) $viewer['postcode'] ) : '';
		$want   = (string) ( $filters['region'] ?? '' );
		$all    = ! empty( $filters['all'] );
		$out    = array();
		foreach ( $rows as $debate ) {
			$school = AIADN_Schools::get( (int) $debate['school_a_id'] );
			if ( ! $school || 'approved' !== $school['status'] ) {
				continue;
			}
			// The area is worked out from the postcode each time. It is never typed in, so it cannot be wrong or out of date.
			$region = AIADN_Regions::for_postcode( (string) $school['postcode'] );
			if ( '' !== $want && $region !== $want ) {
				continue;
			}
			$near = '' !== $home && AIADN_Regions::UNKNOWN !== $region && $region === $home;
			// A request for the host's own region is only seen from that region. An old 'none' counts as the same.
			if ( 'anywhere' !== $debate['req_travel'] && ! $near ) {
				continue;
			}
			// "In my region" is the default view; "Everywhere" shows every request this school may see. An online-only
			// request open to any school needs no travelling, so it shows in both.
			$online = 'online' === $debate['req_format'];
			if ( ! $all && ! $near && ! $online ) {
				continue;
			}
			$mine  = self::request_for( (int) $debate['id'], $viewer_school_id );
			$out[] = array( 'debate' => $debate, 'school' => $school, 'region' => $region, 'near' => $near, 'asked' => $mine ? $mine['status'] : '' );
		}
		// Schools in the viewer's own area first, otherwise newest first.
		$order = array_keys( $out );
		usort( $order, static fn( $a, $b ) => (int) $out[ $b ]['near'] <=> (int) $out[ $a ]['near'] ?: $a <=> $b );
		return array_map( static fn( $i ) => $out[ $i ], $order );
	}

	/* ------------------------------------------------------------------ */
	/* Asking, and deciding                                                */
	/* ------------------------------------------------------------------ */

	public static function get( int $id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE id = %d', $id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function request_for( int $debate_id, int $school_id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE debate_id = %d AND school_id = %d', $debate_id, $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** @return array<int,array<string,mixed>> */
	public static function requests_for_debate( int $debate_id, string $status = 'pending' ): array {
		global $wpdb;
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . ' WHERE debate_id = %d AND status = %s ORDER BY id ASC', $debate_id, $status ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/** @return array<int,array<string,mixed>> this school's own asks that are still waiting */
	public static function pending_for_school( int $school_id ): array {
		global $wpdb;
		return (array) $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . self::table() . " WHERE school_id = %d AND status = 'pending' ORDER BY id DESC", $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/** A school asks to take up an open request. Returns an error message, or '' on success. */
	public static function ask( array $debate, int $school_id, int $member_id ): string {
		global $wpdb;
		if ( 'awaiting_opponent' !== $debate['status'] || ! (int) $debate['open_request'] || (int) $debate['school_a_id'] === $school_id ) {
			return 'That request is no longer available.';
		}
		if ( ! AIADN_Util::allow( 'findask|' . $school_id, 20, DAY_IN_SECONDS ) ) {
			return 'Too many requests today. Please try again tomorrow.';
		}
		$existing = self::request_for( (int) $debate['id'], $school_id );
		if ( $existing && 'withdrawn' !== $existing['status'] ) {
			return 'You have already asked about this one.';
		}
		if ( count( self::pending_for_school( $school_id ) ) >= self::MAX_PENDING ) {
			return 'You have ' . self::MAX_PENDING . ' requests waiting. Withdraw one, or wait for an answer, before asking another.';
		}
		if ( $existing ) { // Asking again after withdrawing.
			$wpdb->update( self::table(), array( 'status' => 'pending', 'member_id' => $member_id, 'decided_at' => null ), array( 'id' => (int) $existing['id'] ) ); // phpcs:ignore WordPress.DB
		} else {
			$wpdb->insert( self::table(), array( 'debate_id' => (int) $debate['id'], 'school_id' => $school_id, 'member_id' => $member_id, 'status' => 'pending', 'created_at' => AIADN_Util::now() ) ); // phpcs:ignore WordPress.DB
		}
		AIADN_Debates::log( (int) $debate['id'], 'request_received', $school_id );
		$us    = AIADN_Schools::get( $school_id );
		$owner = AIADN_Debates::owner( $debate, 'a' );
		if ( $owner && $us ) {
			AIADN_Mailer::send_notice( $owner['email'], $us['name'] . ' would like to debate you', "{$us['name']} has asked to take up your Debate Request ({$debate['code']}) on Join the conversation.\n\nYou choose who you debate. Open the debate to accept or decline. You can see their school name and area, and nothing else about them.\n\n" . AIADN_Debates::url( $debate ) );
		}
		return '';
	}

	public static function withdraw( array $request, int $school_id ): void {
		global $wpdb;
		if ( (int) $request['school_id'] !== $school_id || 'pending' !== $request['status'] ) {
			return;
		}
		$wpdb->update( self::table(), array( 'status' => 'withdrawn', 'decided_at' => AIADN_Util::now() ), array( 'id' => (int) $request['id'] ) ); // phpcs:ignore WordPress.DB
	}

	/** The host accepts or declines a school's ask. */
	public static function decide( array $request, array $debate, bool $accept ): bool {
		global $wpdb;
		if ( 'pending' !== $request['status'] || (int) $request['debate_id'] !== (int) $debate['id'] ) {
			return false;
		}
		$requester = AIADN_Schools::get( (int) $request['school_id'] );
		$member    = AIADN_Schools::get_member( (int) $request['member_id'] );
		$host      = AIADN_Schools::get( (int) $debate['school_a_id'] );
		if ( ! $requester || ! $host ) {
			return false;
		}
		if ( ! $accept ) {
			$wpdb->update( self::table(), array( 'status' => 'declined', 'decided_at' => AIADN_Util::now() ), array( 'id' => (int) $request['id'] ) ); // phpcs:ignore WordPress.DB
			if ( $member ) {
				AIADN_Mailer::send_notice( $member['email'], 'About your request to debate ' . $host['name'], "{$host['name']} has chosen not to go ahead with your request this time.\n\nThere are more requests waiting on Join the conversation:\n\n" . AIADN_Front::url( 'find' ) );
			}
			return true;
		}
		// Mark this ask accepted before the debate is claimed, so the others are the ones that get closed.
		$wpdb->update( self::table(), array( 'status' => 'accepted', 'decided_at' => AIADN_Util::now() ), array( 'id' => (int) $request['id'] ) ); // phpcs:ignore WordPress.DB
		if ( ! AIADN_Debates::claim_slot( (int) $debate['id'], (int) $request['school_id'], (int) $request['member_id'], 'matched' ) ) {
			$wpdb->update( self::table(), array( 'status' => 'declined' ), array( 'id' => (int) $request['id'] ) ); // phpcs:ignore WordPress.DB
			return false;
		}
		AIADN_Debates::complete_match( AIADN_Debates::get( (int) $debate['id'] ) );
		return true;
	}

	/** Someone now has the place (by request, or by an invitation): take the request down and tell the rest. */
	public static function on_matched( int $debate_id, int $school_b_id ): void {
		global $wpdb;
		$wpdb->update( AIADN_Database::table( 'debates' ), array( 'open_request' => 0 ), array( 'id' => $debate_id ) ); // phpcs:ignore WordPress.DB
		self::close_pending( $debate_id, $school_b_id, 'Another school has taken this one.' );
	}

	/** Tell every school still waiting that it is no longer available. $except is a school that got the place. */
	private static function close_pending( int $debate_id, int $except, string $why ): void {
		global $wpdb;
		$host = null;
		$deb  = AIADN_Debates::get( $debate_id );
		if ( $deb ) {
			$host = AIADN_Schools::get( (int) $deb['school_a_id'] );
		}
		foreach ( self::requests_for_debate( $debate_id, 'pending' ) as $r ) {
			if ( (int) $r['school_id'] === $except ) {
				continue;
			}
			$wpdb->update( self::table(), array( 'status' => 'declined', 'decided_at' => AIADN_Util::now() ), array( 'id' => (int) $r['id'] ) ); // phpcs:ignore WordPress.DB
			$member = AIADN_Schools::get_member( (int) $r['member_id'] );
			if ( $member ) {
				AIADN_Mailer::send_notice( $member['email'], 'About your request to debate ' . ( $host['name'] ?? 'a school' ), $why . "\n\nThere are more requests waiting on Join the conversation:\n\n" . AIADN_Front::url( 'find' ) );
			}
		}
	}

	/** Counts for the programme team. */
	public static function summary(): array {
		global $wpdb;
		$d   = AIADN_Database::table( 'debates' );
		$out = array(
			'open'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$d} WHERE status = 'awaiting_opponent' AND open_request = 1" ), // phpcs:ignore WordPress.DB
			'pending' => 0,
			'accepted' => 0,
			'declined' => 0,
			'withdrawn' => 0,
		);
		foreach ( (array) $wpdb->get_results( 'SELECT status, COUNT(*) AS n FROM ' . self::table() . ' GROUP BY status', ARRAY_A ) as $r ) { // phpcs:ignore WordPress.DB
			$out[ $r['status'] ] = (int) $r['n'];
		}
		return $out;
	}
}
