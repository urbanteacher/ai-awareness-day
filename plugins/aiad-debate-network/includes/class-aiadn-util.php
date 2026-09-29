<?php
/**
 * Small shared helpers: random codes, hashing, rate limiting.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Util {

	/** No 0/O or 1/I so codes survive being read aloud or copied from a whiteboard. */
	const CODE_ALPHABET = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

	public static function random_string( int $length, string $alphabet = self::CODE_ALPHABET ): string {
		$max = strlen( $alphabet ) - 1;
		$out = '';
		for ( $i = 0; $i < $length; $i++ ) {
			$out .= $alphabet[ random_int( 0, $max ) ];
		}
		return $out;
	}

	/** A new school code, e.g. SCH-3F9A2. Uniqueness is checked by the caller. */
	public static function new_school_code(): string {
		return 'SCH-' . self::random_string( 5 );
	}

	/**
	 * Turn whatever a person typed into a school code, or '' if it cannot be one.
	 * Accepts "sch 3f9a2", "SCH3F9A2", "3f9a2".
	 */
	public static function normalise_school_code( string $input ): string {
		$clean = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $input ) );
		if ( str_starts_with( $clean, 'SCH' ) ) {
			$clean = substr( $clean, 3 );
		}
		if ( ! preg_match( '/^[' . self::CODE_ALPHABET . ']{5}$/', $clean ) ) {
			return '';
		}
		return 'SCH-' . $clean;
	}

	public static function six_digit_code(): string {
		return str_pad( (string) random_int( 0, 999999 ), 6, '0', STR_PAD_LEFT );
	}

	public static function four_digit_pin(): string {
		return str_pad( (string) random_int( 0, 9999 ), 4, '0', STR_PAD_LEFT );
	}

	/** 160-bit random token for links. Only its hash is stored. */
	public static function new_token(): string {
		return bin2hex( random_bytes( 20 ) );
	}

	public static function hash_secret( string $value ): string {
		return hash_hmac( 'sha256', $value, wp_salt( 'auth' ) );
	}

	public static function now(): string {
		return gmdate( 'Y-m-d H:i:s' );
	}

	public static function in_seconds( int $seconds ): string {
		return gmdate( 'Y-m-d H:i:s', time() + $seconds );
	}

	/** A datetime-local value typed in the site's time zone, as UTC for storage (or null if it is not a date). */
	public static function local_to_utc( string $local ): ?string {
		foreach ( array( 'Y-m-d\TH:i', 'Y-m-d\TH:i:s' ) as $format ) {
			$dt     = DateTimeImmutable::createFromFormat( $format, $local, wp_timezone() );
			$errors = DateTimeImmutable::getLastErrors();
			$clean  = false === $errors || 0 === ( $errors['warning_count'] + $errors['error_count'] );
			if ( $dt && $clean ) {
				return $dt->setTimezone( new DateTimeZone( 'UTC' ) )->format( 'Y-m-d H:i:s' );
			}
		}
		return null;
	}

	/** A stored UTC time, shown in the site's time zone. */
	public static function show( string $utc, string $format = 'D j M Y, H:i' ): string {
		return wp_date( $format, strtotime( $utc . ' UTC' ) );
	}

	/** A stored UTC time as a datetime-local input value. */
	public static function to_input( string $utc ): string {
		return wp_date( 'Y-m-d\TH:i', strtotime( $utc . ' UTC' ) );
	}

	public static function new_debate_code(): string {
		return 'AID-' . self::random_string( 5 );
	}

	/** Turn what someone typed into a Debate ID, or '' if it cannot be one. */
	public static function normalise_debate_code( string $input ): string {
		$clean = strtoupper( preg_replace( '/[^A-Za-z0-9]/', '', $input ) );
		if ( str_starts_with( $clean, 'AID' ) ) {
			$clean = substr( $clean, 3 );
		}
		return preg_match( '/^[' . self::CODE_ALPHABET . ']{5}$/', $clean ) ? 'AID-' . $clean : '';
	}

	/** Where issue reports are copied. Set AIADN_TEAM_EMAIL in wp-config.php; falls back to the site admin. */
	public static function team_email(): string {
		if ( defined( 'AIADN_TEAM_EMAIL' ) && is_email( AIADN_TEAM_EMAIL ) ) {
			return AIADN_TEAM_EMAIL;
		}
		return (string) get_option( 'admin_email' );
	}

	public static function normalise_email( string $email ): string {
		return strtolower( trim( $email ) );
	}

	public static function email_domain( string $email ): string {
		$at = strrpos( $email, '@' );
		return false === $at ? '' : strtolower( substr( $email, $at + 1 ) );
	}

	public static function mask_email( string $email ): string {
		$at = strrpos( $email, '@' );
		if ( false === $at ) {
			return '';
		}
		$local = substr( $email, 0, $at );
		return substr( $local, 0, min( 3, max( 1, strlen( $local ) - 1 ) ) ) . '***' . substr( $email, $at );
	}

	/** Lowercase letters and digits only, for spotting the same school entered twice. */
	public static function key( string $value ): string {
		return preg_replace( '/[^a-z0-9]/', '', strtolower( $value ) );
	}

	/** Personal email domains: never used to decide that someone "belongs" to a school. */
	public static function is_free_mail_domain( string $domain ): bool {
		$free = array(
			'gmail.com', 'googlemail.com', 'outlook.com', 'hotmail.com', 'hotmail.co.uk', 'live.com', 'live.co.uk',
			'yahoo.com', 'yahoo.co.uk', 'icloud.com', 'me.com', 'msn.com', 'aol.com', 'btinternet.com', 'sky.com',
			'proton.me', 'protonmail.com', 'talktalk.net', 'virginmedia.com',
		);
		return in_array( $domain, $free, true );
	}

	public static function client_ip(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return (string) apply_filters( 'aiadn_client_ip', $ip );
	}

	/**
	 * Count an attempt against a bucket. Returns false once the limit is reached.
	 *
	 * @param string $bucket  Anything that identifies what is being limited.
	 * @param int    $max     Attempts allowed in the window.
	 * @param int    $window  Window in seconds.
	 */
	public static function allow( string $bucket, int $max, int $window ): bool {
		$key  = 'aiadn_rl_' . md5( $bucket );
		$data = get_transient( $key );
		if ( ! is_array( $data ) ) {
			$data = array( 0, time() );
		}
		if ( $data[0] >= $max ) {
			return false;
		}
		++$data[0];
		$remaining = max( 1, $window - ( time() - (int) $data[1] ) );
		set_transient( $key, $data, $remaining );
		return true;
	}
}
