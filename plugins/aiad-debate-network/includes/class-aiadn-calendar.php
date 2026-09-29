<?php
/**
 * Calendar invites (.ics), so a debate is booked in everyone's diary.
 *
 * The host school ticks a box when proposing. Once both schools agree, an invite goes to the host
 * teacher, the other school's teacher and both schools' senior leaders. The judge's goes out when they
 * accept. If an online debate's link changes, everyone's entry is updated; if the debate is cancelled,
 * it is marked cancelled.
 *
 * The events use METHOD:PUBLISH with a stable UID and a rising SEQUENCE, so calendars update the same
 * entry instead of adding a new one. Times are in UTC (with a Z), so every calendar shows local time.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AIADN_Calendar {

	/** A debate is booked for 90 minutes. */
	const DURATION = 5400;

	/** The .ics text for a debate. */
	public static function build( array $debate, string $status = 'CONFIRMED', int $sequence = 0 ): string {
		$start = strtotime( $debate['starts_at'] . ' UTC' );
		$host  = wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.co.uk';
		$a     = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$b     = $debate['school_b_id'] ? AIADN_Schools::get( (int) $debate['school_b_id'] ) : null;
		$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
		$online = 'online' === $debate['format'];

		$desc  = 'Theme: ' . ( AIADN_Motions::THEMES[ $debate['theme'] ] ?? '' ) . "\n";
		$desc .= 'Motion: ' . $debate['motion_text'] . "\n";
		if ( $judge ) {
			$desc .= 'Judge: ' . $judge['name'] . "\n";
		}
		$desc .= $online ? 'Online. Hosted by ' . ( $a['name'] ?? '' ) . ', who will admit you to the meeting.' . "\n" : '';
		$desc .= 'Organised through the National AI Conversation, AI Awareness Day.';

		$lines = array(
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//AI Awareness Day//National AI Conversation//EN',
			'CALSCALE:GREGORIAN',
			'METHOD:PUBLISH',
			'BEGIN:VEVENT',
			'UID:' . $debate['code'] . '@' . $host,
			'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ),
			'DTSTART:' . gmdate( 'Ymd\THis\Z', $start ),
			'DTEND:' . gmdate( 'Ymd\THis\Z', $start + self::DURATION ),
			'SEQUENCE:' . $sequence,
			'STATUS:' . $status,
			'SUMMARY:' . self::text( 'AI debate: ' . ( $a['name'] ?? '' ) . ' v ' . ( $b['name'] ?? '' ) ),
			'DESCRIPTION:' . self::text( $desc ),
			'LOCATION:' . self::text( AIADN_Debates::place( $debate ) ),
		);
		if ( $online && $debate['meeting_url'] ) {
			$lines[] = 'URL:' . $debate['meeting_url'];
		}
		if ( 'CANCELLED' !== $status ) {
			array_push( $lines, 'BEGIN:VALARM', 'TRIGGER:-PT1H', 'ACTION:DISPLAY', 'DESCRIPTION:AI debate in one hour', 'END:VALARM' );
		}
		array_push( $lines, 'END:VEVENT', 'END:VCALENDAR' );
		return implode( "\r\n", array_map( array( __CLASS__, 'fold' ), $lines ) ) . "\r\n";
	}

	/** iCalendar text values: escape backslashes, semicolons, commas and newlines. */
	private static function text( string $value ): string {
		return str_replace( array( '\\', ';', ',', "\r\n", "\n", "\r" ), array( '\\\\', '\\;', '\\,', '\\n', '\\n', '\\n' ), $value );
	}

	/** Lines are folded at 75 bytes, on a character boundary. */
	private static function fold( string $line ): string {
		if ( strlen( $line ) <= 75 ) {
			return $line;
		}
		$out    = '';
		$offset = 0;
		$limit  = 75;
		while ( $offset < strlen( $line ) ) {
			$chunk   = function_exists( 'mb_strcut' ) ? mb_strcut( $line, $offset, $limit, 'UTF-8' ) : substr( $line, $offset, $limit );
			$out    .= ( '' === $out ? '' : "\r\n " ) . $chunk;
			$offset += strlen( $chunk );
			$limit   = 74; // The leading space of a continuation line counts.
		}
		return $out;
	}

	/** The .ics file as a download, with the headers a browser needs. Exits. */
	public static function download( array $debate ): void {
		$ics = self::build( $debate, 'CONFIRMED', (int) $debate['ics_sequence'] );
		nocache_headers();
		header( 'Content-Type: text/calendar; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="ai-debate-' . $debate['code'] . '.ics"' );
		header( 'X-Content-Type-Options: nosniff' );
		echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput -- a calendar file, escaped per RFC 5545 in build().
		exit;
	}

	/** Teachers and senior leaders at both schools. The judge is added separately. */
	public static function school_recipients( array $debate ): array {
		$emails = array();
		foreach ( array( 'a', 'b' ) as $side ) {
			$owner = AIADN_Debates::owner( $debate, $side );
			if ( $owner ) {
				$emails[] = $owner['email'];
			}
			$school_id = (int) ( 'a' === $side ? $debate['school_a_id'] : ( $debate['school_b_id'] ?? 0 ) );
			if ( $school_id ) {
				$emails = array_merge( $emails, AIADN_Schools::slt_emails( $school_id ) );
			}
		}
		return array_values( array_unique( array_filter( array_map( 'strtolower', $emails ) ) ) );
	}

	private static function was_sent( int $debate_id ): bool {
		return isset( AIADN_Debates::events( $debate_id )['calendar_sent'] );
	}

	private static function deliver( array $debate, array $emails, string $status, int $sequence, string $subject, string $intro ): void {
		$ics      = self::build( $debate, $status, $sequence );
		$filename = 'ai-debate-' . $debate['code'] . '.ics';
		$body     = $intro . "\n\n" . AIADN_Debates::summary( $debate ) . "\n\nOpen the attached file to add it to your calendar. If your calendar already has this debate, the entry is updated instead of added twice.";
		foreach ( $emails as $to ) {
			AIADN_Mailer::send_calendar( $to, $subject, $body, $ics, $filename );
		}
	}

	private static function next_sequence( array $debate ): int {
		$next = (int) $debate['ics_sequence'] + 1;
		AIADN_Debates::update_quiet( (int) $debate['id'], array( 'ics_sequence' => $next ) );
		return $next;
	}

	/** Both schools have agreed: invite the teachers and senior leaders. */
	public static function send_initial( array $debate ): void {
		if ( ! (int) $debate['send_calendar'] || ! $debate['starts_at'] ) {
			return;
		}
		self::deliver( $debate, self::school_recipients( $debate ), 'CONFIRMED', (int) $debate['ics_sequence'], 'Calendar invite: your AI debate on ' . AIADN_Util::show( $debate['starts_at'], 'j M' ), 'Your debate is agreed. Here is a calendar invite so it is booked in your diary.' );
		AIADN_Debates::log( (int) $debate['id'], 'calendar_sent', 0 );
	}

	/** The judge has accepted: their invite. */
	public static function send_to_judge( array $debate, array $judge ): void {
		if ( ! (int) $debate['send_calendar'] || ! $debate['starts_at'] ) {
			return;
		}
		self::deliver( $debate, array( strtolower( $judge['email'] ) ), 'CONFIRMED', (int) $debate['ics_sequence'], 'Calendar invite: judging on ' . AIADN_Util::show( $debate['starts_at'], 'j M' ), 'Thank you for agreeing to judge. Here is a calendar invite so it is booked in your diary.' );
	}

	/** The meeting link changed: update everyone who already has the entry. */
	public static function send_update( array $debate ): void {
		if ( ! (int) $debate['send_calendar'] || ! self::was_sent( (int) $debate['id'] ) ) {
			return;
		}
		$seq    = self::next_sequence( $debate );
		$debate = AIADN_Debates::get( (int) $debate['id'] );
		$emails = self::school_recipients( $debate );
		$judge  = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
		if ( $judge && 'accepted' === $judge['status'] ) {
			$emails[] = strtolower( $judge['email'] );
		}
		self::deliver( $debate, array_values( array_unique( $emails ) ), 'CONFIRMED', $seq, 'Updated calendar invite: the meeting link has changed', 'The details of this debate have changed. Your calendar entry will update.' );
	}

	/** The debate is cancelled: mark it cancelled for everyone who has the entry. */
	public static function send_cancel( array $debate ): void {
		if ( ! (int) $debate['send_calendar'] || ! self::was_sent( (int) $debate['id'] ) ) {
			return;
		}
		$seq    = self::next_sequence( $debate );
		$debate = AIADN_Debates::get( (int) $debate['id'] );
		$emails = self::school_recipients( $debate );
		$judge  = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
		if ( $judge && 'accepted' === $judge['status'] ) {
			$emails[] = strtolower( $judge['email'] );
		}
		self::deliver( $debate, array_values( array_unique( $emails ) ), 'CANCELLED', $seq, 'Cancelled: AI debate on ' . AIADN_Util::show( $debate['starts_at'], 'j M' ), 'This debate has been cancelled. Open the attachment to remove it from your calendar.' );
	}

	/** A judge who had accepted is replaced: cancel their copy only. */
	public static function send_cancel_to_judge( array $debate, array $judge ): void {
		if ( ! (int) $debate['send_calendar'] || 'accepted' !== $judge['status'] ) {
			return;
		}
		self::deliver( $debate, array( strtolower( $judge['email'] ) ), 'CANCELLED', (int) $debate['ics_sequence'] + 1, 'Cancelled: judging on ' . AIADN_Util::show( $debate['starts_at'], 'j M' ), 'The schools have chosen a different judge, so there is nothing more to do. Open the attachment to remove it from your calendar.' );
	}
}
