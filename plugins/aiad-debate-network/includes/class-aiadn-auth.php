<?php
/**
 * Codes, tokens, class PINs and the signed-in session.
 *
 * No WordPress users and no passwords. A session is a signed cookie.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Auth {

	const CODE_TTL        = 900;        // 15 minutes.
	const CODE_MAX_TRIES  = 5;
	const SLT_TOKEN_TTL   = 1209600;    // 14 days.
	const PIN_TTL         = 10800;      // 3 hours: "expires after the lesson".
	const SESSION_TTL     = 2592000;    // 30 days for adults.
	const JUDGE_TTL       = 86400;      // 24 hours: long enough to save mid-debate.
	const STUDENT_TTL     = 10800;      // 3 hours for students.
	const COOKIE          = 'aiadn_session';

	/* ------------------------------------------------------------------ */
	/* Six-digit email codes                                               */
	/* ------------------------------------------------------------------ */

	/**
	 * Create a code for someone. Returns array( ref, code ). Earlier unused codes for the same person are cancelled.
	 */
	public static function issue_code( int $school_id, string $email, string $purpose, string $role_hint = '' ): array {
		global $wpdb;
		$table = AIADN_Database::table( 'login_codes' );

		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET used_at = %s WHERE school_id = %d AND email = %s AND purpose = %s AND used_at IS NULL", AIADN_Util::now(), $school_id, $email, $purpose ) ); // phpcs:ignore WordPress.DB

		$code = AIADN_Util::six_digit_code();
		$ref  = bin2hex( random_bytes( 16 ) );
		$wpdb->insert( // phpcs:ignore WordPress.DB
			$table,
			array(
				'ref'        => $ref,
				'school_id'  => $school_id,
				'email'      => $email,
				'purpose'    => $purpose,
				'role_hint'  => $role_hint,
				'code_hash'  => AIADN_Util::hash_secret( $ref . '|' . $code ),
				'expires_at' => AIADN_Util::in_seconds( self::CODE_TTL ),
				'created_at' => AIADN_Util::now(),
			)
		);
		return array( $ref, $code );
	}

	/** A reference that looks real but matches nothing, so "no such person" is indistinguishable from "code sent". */
	public static function decoy_ref(): string {
		return bin2hex( random_bytes( 16 ) );
	}

	/**
	 * Check a code. Returns the code row on success, or null. Wrong guesses are counted; five and it is dead.
	 */
	public static function verify_code( string $ref, string $code ): ?array {
		global $wpdb;
		$table = AIADN_Database::table( 'login_codes' );
		$ref   = preg_replace( '/[^a-f0-9]/', '', strtolower( $ref ) );
		$code  = preg_replace( '/\D/', '', $code );
		if ( 32 !== strlen( $ref ) || 6 !== strlen( $code ) ) {
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE ref = %s", $ref ), ARRAY_A ); // phpcs:ignore WordPress.DB
		if ( ! $row || null !== $row['used_at'] || $row['expires_at'] < AIADN_Util::now() || (int) $row['attempts'] >= self::CODE_MAX_TRIES ) {
			return null;
		}

		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET attempts = attempts + 1 WHERE id = %d", (int) $row['id'] ) ); // phpcs:ignore WordPress.DB

		if ( ! hash_equals( (string) $row['code_hash'], AIADN_Util::hash_secret( $ref . '|' . $code ) ) ) {
			return null;
		}

		// Conditional update: two browsers submitting the same code cannot both win.
		$claimed = $wpdb->query( $wpdb->prepare( "UPDATE {$table} SET used_at = %s WHERE id = %d AND used_at IS NULL", AIADN_Util::now(), (int) $row['id'] ) ); // phpcs:ignore WordPress.DB
		return 1 === (int) $claimed ? $row : null;
	}

	/* ------------------------------------------------------------------ */
	/* Link tokens                                                         */
	/* ------------------------------------------------------------------ */

	/** Make a new token for a kind of link. Any earlier unused token of that kind for the school is cancelled. */
	public static function issue_token( int $school_id, string $kind, int $ttl, int $ref_id = 0 ): string {
		global $wpdb;
		$table = AIADN_Database::table( 'tokens' );
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET used_at = %s WHERE school_id = %d AND kind = %s AND ref_id = %d AND used_at IS NULL", AIADN_Util::now(), $school_id, $kind, $ref_id ) ); // phpcs:ignore WordPress.DB

		$token = AIADN_Util::new_token();
		$wpdb->insert( // phpcs:ignore WordPress.DB
			$table,
			array(
				'school_id'  => $school_id,
				'kind'       => $kind,
				'ref_id'     => $ref_id,
				'token_hash' => AIADN_Util::hash_secret( $token ),
				'hint'       => substr( $token, -4 ),
				'expires_at' => AIADN_Util::in_seconds( $ttl ),
				'created_at' => AIADN_Util::now(),
			)
		);
		return $token;
	}

	/** Look a token up WITHOUT using it. Opening a link must never have side effects (mail scanners open links). */
	public static function find_token( string $token, string $kind ): ?array {
		global $wpdb;
		$token = preg_replace( '/[^a-f0-9]/', '', strtolower( $token ) );
		if ( 40 !== strlen( $token ) ) {
			return null;
		}
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'tokens' ) . ' WHERE token_hash = %s AND kind = %s', AIADN_Util::hash_secret( $token ), $kind ), ARRAY_A ); // phpcs:ignore WordPress.DB
		if ( ! $row || null !== $row['used_at'] || $row['expires_at'] < AIADN_Util::now() ) {
			return null;
		}
		return $row;
	}

	/** Use a token. True only for the one request that wins. */
	public static function consume_token( int $token_id ): bool {
		global $wpdb;
		$done = $wpdb->query( $wpdb->prepare( 'UPDATE ' . AIADN_Database::table( 'tokens' ) . ' SET used_at = %s WHERE id = %d AND used_at IS NULL', AIADN_Util::now(), $token_id ) ); // phpcs:ignore WordPress.DB
		return 1 === (int) $done;
	}

	/* ------------------------------------------------------------------ */
	/* Class PINs                                                          */
	/* ------------------------------------------------------------------ */

	/** @return array{pin:string,expires_at:string}|null */
	public static function current_pin( int $school_id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT pin, expires_at FROM ' . AIADN_Database::table( 'class_pins' ) . ' WHERE school_id = %d AND expires_at > %s ORDER BY id DESC LIMIT 1', $school_id, AIADN_Util::now() ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	/** Start a new class PIN. The old one stops working straight away. */
	public static function new_pin( int $school_id, int $member_id ): array {
		global $wpdb;
		$table = AIADN_Database::table( 'class_pins' );
		$wpdb->query( $wpdb->prepare( "UPDATE {$table} SET expires_at = %s WHERE school_id = %d AND expires_at > %s", AIADN_Util::now(), $school_id, AIADN_Util::now() ) ); // phpcs:ignore WordPress.DB

		$pin     = AIADN_Util::four_digit_pin();
		$expires = AIADN_Util::in_seconds( self::PIN_TTL );
		$wpdb->insert( // phpcs:ignore WordPress.DB
			$table,
			array(
				'school_id'  => $school_id,
				'pin'        => $pin,
				'created_by' => $member_id,
				'expires_at' => $expires,
				'created_at' => AIADN_Util::now(),
			)
		);
		return array( 'pin' => $pin, 'expires_at' => $expires );
	}

	public static function pin_matches( int $school_id, string $pin ): bool {
		$current = self::current_pin( $school_id );
		return $current && hash_equals( $current['pin'], preg_replace( '/\D/', '', $pin ) );
	}

	/* ------------------------------------------------------------------ */
	/* Session (signed cookie)                                             */
	/* ------------------------------------------------------------------ */

	/**
	 * Sign someone in. $kind is 'member' (teacher/lead/slt) or 'student'.
	 *
	 * @param array<string,mixed> $data member_id (0 for students), school_id, role.
	 */
	public static function start_session( array $data, int $ttl ): void {
		$payload = array(
			'm'   => (int) $data['member_id'],
			's'   => (int) $data['school_id'],
			'r'   => (string) $data['role'],
			'exp' => time() + $ttl,
		);
		$body   = rtrim( strtr( base64_encode( wp_json_encode( $payload ) ), '+/', '-_' ), '=' );
		$value  = $body . '.' . hash_hmac( 'sha256', $body, wp_salt( 'auth' ) );
		$cookie = array(
			'expires'  => time() + $ttl,
			'path'     => '/',
			'secure'   => is_ssl(),
			'httponly' => true,
			'samesite' => 'Lax',
		);
		if ( ! headers_sent() ) {
			setcookie( self::COOKIE, $value, $cookie );
		}
		$_COOKIE[ self::COOKIE ] = $value;
	}

	public static function end_session(): void {
		if ( ! headers_sent() ) {
			setcookie( self::COOKIE, '', array( 'expires' => time() - 3600, 'path' => '/', 'secure' => is_ssl(), 'httponly' => true, 'samesite' => 'Lax' ) );
		}
		unset( $_COOKIE[ self::COOKIE ] );
	}

	/**
	 * The signed-in person, re-checked against the database, or null.
	 *
	 * @return array{member_id:int,school_id:int,role:string,school:array,member:?array}|null
	 */
	public static function current(): ?array {
		static $cache = false;
		if ( false !== $cache ) {
			return $cache;
		}
		$cache = null;
		$raw   = isset( $_COOKIE[ self::COOKIE ] ) ? (string) wp_unslash( $_COOKIE[ self::COOKIE ] ) : ''; // phpcs:ignore WordPress.Security
		if ( '' === $raw || 1 !== substr_count( $raw, '.' ) ) {
			return null;
		}
		list( $body, $sig ) = explode( '.', $raw, 2 );
		if ( ! hash_equals( hash_hmac( 'sha256', $body, wp_salt( 'auth' ) ), $sig ) ) {
			return null;
		}
		$payload = json_decode( (string) base64_decode( strtr( $body, '-_', '+/' ) ), true );
		if ( ! is_array( $payload ) || (int) ( $payload['exp'] ?? 0 ) < time() ) {
			return null;
		}
		$school = AIADN_Schools::get( (int) $payload['s'] );
		if ( ! $school ) {
			return null;
		}
		$member = null;
		if ( 'judge' === $payload['r'] ) {
			// A judge session is tied to one judge row; it must still involve this school.
			$member = AIADN_Debates::get_judge( (int) $payload['m'] );
			if ( ! $member || ! AIADN_Debates::judge_involves_school( $member, (int) $school['id'] ) || ! in_array( $member['status'], array( 'invited', 'accepted' ), true ) ) {
				return null;
			}
		} elseif ( 'student' !== $payload['r'] ) {
			$member = AIADN_Schools::get_member( (int) $payload['m'] );
			// The role in the cookie must still be the person's role, so handing over the lead role ends old sessions.
			if ( ! $member || (int) $member['school_id'] !== (int) $school['id'] || $member['role'] !== $payload['r'] ) {
				return null;
			}
		}
		$cache = array(
			'member_id' => (int) $payload['m'],
			'school_id' => (int) $school['id'],
			'role'      => (string) $payload['r'],
			'school'    => $school,
			'member'    => $member,
		);
		return $cache;
	}

	/** A token that ties a form to the signed-in session, for actions that change things. */
	public static function csrf(): string {
		$raw = isset( $_COOKIE[ self::COOKIE ] ) ? (string) wp_unslash( $_COOKIE[ self::COOKIE ] ) : ''; // phpcs:ignore WordPress.Security
		return hash_hmac( 'sha256', 'csrf|' . $raw, wp_salt( 'nonce' ) );
	}

	public static function csrf_ok( string $given ): bool {
		return '' !== $given && hash_equals( self::csrf(), $given );
	}
}
