<?php
/**
 * Reads a meeting's calendar file (.ics) from Teams, Google Meet or Zoom and takes out the join link.
 *
 * The host school creates the meeting on its own platform, downloads the calendar file, and uploads it
 * here. We keep ONLY the join link (and check the time matches the debate). The file itself is never
 * stored: a calendar file can list attendees' email addresses, and we have no need of them.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Meeting_Ics {

	const MAX_BYTES = 200000;

	/** How far the file's start time may differ from the debate's, before we say it is the wrong meeting. */
	const TOLERANCE = 1800; // 30 minutes.

	/** Hosts we recognise as meeting links, so a "learn more" link in the description is never mistaken for one. */
	const HOSTS = array( 'teams.microsoft.com', 'teams.live.com', 'meet.google.com', 'zoom.us', 'zoomgov.com', 'whereby.com', 'webex.com', 'gotomeet.me', 'goto.com', 'bluejeans.com', 'meet.jit.si', 'join.skype.com' );

	/** Windows time zone names, which Teams and Outlook use, mapped to ones PHP knows. */
	const ZONES = array(
		'GMT Standard Time'        => 'Europe/London',
		'Greenwich Standard Time'  => 'Atlantic/Reykjavik',
		'UTC'                      => 'UTC',
		'Romance Standard Time'    => 'Europe/Paris',
		'W. Europe Standard Time'  => 'Europe/Berlin',
		'Central Europe Standard Time' => 'Europe/Budapest',
		'Eastern Standard Time'    => 'America/New_York',
		'Pacific Standard Time'    => 'America/Los_Angeles',
	);

	/**
	 * @return array{url:string,start:?int,error:string} start is a Unix time, or null if the file does not say clearly
	 */
	public static function extract( string $text ): array {
		$fail = static fn( string $why ): array => array( 'url' => '', 'start' => null, 'error' => $why );

		$text = ltrim( $text, "\xEF\xBB\xBF \t\r\n" );
		if ( strlen( $text ) > self::MAX_BYTES || 0 !== stripos( $text, 'BEGIN:VCALENDAR' ) ) {
			return $fail( 'That does not look like a calendar file. Download the .ics file from your meeting invite and upload that, or paste the link instead.' );
		}

		// Lines may be folded onto the next line, starting with a space or tab.
		$text  = preg_replace( "/\r?\n[ \t]/", '', $text );
		$props = array();
		$in    = false;
		$depth = 0;
		foreach ( preg_split( "/\r?\n/", $text ) as $line ) {
			if ( 0 === stripos( $line, 'BEGIN:VEVENT' ) ) {
				$in = true;
				continue;
			}
			if ( 0 === stripos( $line, 'END:VEVENT' ) ) {
				break; // Only the first event.
			}
			if ( 0 === stripos( $line, 'BEGIN:VALARM' ) ) {
				++$depth;
			}
			if ( 0 === stripos( $line, 'END:VALARM' ) ) {
				--$depth;
				continue;
			}
			if ( ! $in || $depth > 0 ) {
				continue;
			}
			if ( preg_match( '/^([A-Za-z0-9-]+)((?:;[^:"]*(?:"[^"]*")?[^:"]*)*):(.*)$/', $line, $m ) ) {
				$name = strtoupper( $m[1] );
				if ( ! isset( $props[ $name ] ) ) {
					$props[ $name ] = array( 'params' => $m[2], 'value' => self::unescape( $m[3] ) );
				}
			}
		}
		if ( ! $props ) {
			return $fail( 'That calendar file has no event in it.' );
		}

		$url = self::find_url( $props );
		if ( '' === $url ) {
			return $fail( 'We could not find a meeting link in that file. Check it is the invite for your online meeting, or paste the link instead.' );
		}
		return array( 'url' => $url, 'start' => self::start_time( $props ), 'error' => '' );
	}

	private static function unescape( string $v ): string {
		return preg_replace_callback( '/\\\\(.)/s', static fn( $m ) => in_array( $m[1], array( 'n', 'N' ), true ) ? "\n" : $m[1], $v );
	}

	private static function is_meeting_host( string $url ): bool {
		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		foreach ( self::HOSTS as $h ) {
			if ( $host === $h || str_ends_with( $host, '.' . $h ) ) {
				return true;
			}
		}
		return false;
	}

	/** @return array<int,string> https addresses in a piece of text, trimmed of surrounding punctuation */
	private static function urls_in( string $text ): array {
		preg_match_all( '#https://[^\s<>"\'\)\]]+#i', $text, $m );
		return array_map( static fn( $u ) => rtrim( $u, '.,;:>' ), $m[0] );
	}

	private static function find_url( array $props ): string {
		$pick = static function ( string $candidate ): string {
			$clean = AIADN_Debates::clean_meeting_url( $candidate );
			return $clean;
		};

		// 1. Properties that exist to hold the join link.
		foreach ( array( 'X-MICROSOFT-SKYPETEAMSMEETINGURL', 'X-GOOGLE-CONFERENCE', 'URL' ) as $name ) {
			if ( isset( $props[ $name ] ) ) {
				foreach ( self::urls_in( $props[ $name ]['value'] ) as $u ) {
					if ( self::is_meeting_host( $u ) && '' !== $pick( $u ) ) {
						return $pick( $u );
					}
				}
			}
		}
		// 2. A meeting address in the location or, failing that, the description.
		foreach ( array( 'LOCATION', 'DESCRIPTION', 'X-ALT-DESC' ) as $name ) {
			if ( isset( $props[ $name ] ) ) {
				foreach ( self::urls_in( $props[ $name ]['value'] ) as $u ) {
					if ( self::is_meeting_host( $u ) && '' !== $pick( $u ) ) {
						return $pick( $u );
					}
				}
			}
		}
		// 3. An unfamiliar platform: only an address the host put in the location or URL field on purpose.
		foreach ( array( 'LOCATION', 'URL' ) as $name ) {
			if ( isset( $props[ $name ] ) ) {
				foreach ( self::urls_in( $props[ $name ]['value'] ) as $u ) {
					if ( '' !== $pick( $u ) ) {
						return $pick( $u );
					}
				}
			}
		}
		return '';
	}

	private static function start_time( array $props ): ?int {
		if ( empty( $props['DTSTART'] ) ) {
			return null;
		}
		$value = trim( $props['DTSTART']['value'] );
		$zone  = null;
		if ( str_ends_with( $value, 'Z' ) ) {
			$zone  = new DateTimeZone( 'UTC' );
			$value = substr( $value, 0, -1 );
		} elseif ( preg_match( '/TZID=("?)([^";:]+)\1/i', $props['DTSTART']['params'], $m ) ) {
			$name = self::ZONES[ $m[2] ] ?? $m[2];
			try {
				$zone = new DateTimeZone( $name );
			} catch ( Exception $e ) {
				return null; // A zone we do not know: do not guess.
			}
		}
		if ( ! $zone ) {
			return null; // A "floating" time: no way to tell when.
		}
		$dt = DateTimeImmutable::createFromFormat( 'Ymd\THis', $value, $zone );
		return $dt ? $dt->getTimestamp() : null;
	}
}
