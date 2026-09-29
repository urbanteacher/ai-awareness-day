<?php
/**
 * Reminders and expiry (decision 23.6).
 *
 * When the next step in a debate is overdue, the person who has to act is reminded after 3 days and
 * again after 7. After 14 days with no progress the debate is marked Expired and the schools are told
 * they can start a new one.
 *
 * A "ready" debate is judged from its start time, not from when it became ready, so nobody is chased
 * before the debate has happened.
 *
 * WP-Cron only runs when someone visits the site. On the live site, have the server call wp-cron.php
 * every few minutes so reminders go out on time.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Reminders {

	const FIRST  = 259200;  // 3 days.
	const SECOND = 604800;  // 7 days.
	const EXPIRE = 1209600; // 14 days.
	const HOOK   = 'aiadn_reminders';

	const OPEN_STATUSES = array( 'awaiting_opponent', 'awaiting_b_approval', 'matched', 'proposed', 'agreed', 'ready' );

	public static function register(): void {
		add_action( self::HOOK, array( __CLASS__, 'run' ) );
		add_action( 'init', array( __CLASS__, 'schedule' ) );
	}

	public static function schedule(): void {
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_event( time() + 300, 'hourly', self::HOOK );
		}
	}

	public static function unschedule(): void {
		wp_clear_scheduled_hook( self::HOOK );
	}

	/**
	 * Check every open debate. Safe to run as often as you like: each reminder is sent once.
	 *
	 * @param int|null   $now  Unix time, for testing.
	 * @param array<int> $only Debate ids to limit the run to, for testing.
	 * @return array{reminded:int,expired:int}
	 */
	public static function run( ?int $now = null, array $only = array() ): array {
		global $wpdb;
		$now    = $now ?? time();
		$table  = AIADN_Database::table( 'debates' );
		$list   = "'" . implode( "','", self::OPEN_STATUSES ) . "'";
		$where  = $only ? ' AND id IN (' . implode( ',', array_map( 'intval', $only ) ) . ')' : '';
		$rows   = (array) $wpdb->get_results( "SELECT * FROM {$table} WHERE status IN ({$list}){$where}", ARRAY_A ); // phpcs:ignore WordPress.DB
		$result = array( 'reminded' => 0, 'expired' => 0 );

		foreach ( $rows as $debate ) {
			$since = strtotime( ( $debate['stage_at'] ?: $debate['updated_at'] ) . ' UTC' );
			if ( 'ready' === $debate['status'] && $debate['starts_at'] ) {
				$since = max( $since, strtotime( $debate['starts_at'] . ' UTC' ) );
			}
			$elapsed = $now - $since;
			$sent    = (int) $debate['reminders_sent'];

			if ( $elapsed >= self::EXPIRE ) {
				self::expire( $debate );
				++$result['expired'];
			} elseif ( $elapsed >= self::SECOND && $sent < 2 ) {
				self::remind( $debate, 2 );
				++$result['reminded'];
			} elseif ( $elapsed >= self::FIRST && $sent < 1 ) {
				self::remind( $debate, 1 );
				++$result['reminded'];
			}
		}
		return $result;
	}

	private static function set_sent( int $debate_id, int $n ): void {
		global $wpdb;
		// Deliberately not AIADN_Debates::update(): a reminder is not progress and must not restart the clock.
		$wpdb->update( AIADN_Database::table( 'debates' ), array( 'reminders_sent' => $n ), array( 'id' => $debate_id ) ); // phpcs:ignore WordPress.DB
	}

	private static function remind( array $debate, int $which ): void {
		self::set_sent( (int) $debate['id'], $which );
		AIADN_Debates::log( (int) $debate['id'], 'reminder_' . $which, 0 );

		$url      = AIADN_Debates::url( $debate );
		$deadline = AIADN_Util::show( gmdate( 'Y-m-d H:i:s', self::deadline( $debate ) ), 'j M Y' );
		$tail     = "\n\n{$url}\n\nIf nothing happens by {$deadline}, this debate will close and you can start a new one.";
		$status   = $debate['status'];

		switch ( $status ) {
			case 'awaiting_opponent':
				self::to( AIADN_Debates::owner( $debate, 'a' ), 'Nobody has accepted your debate yet', "Your debate {$debate['code']} is still waiting for another school. Send the invitation again, or share a fresh link:" . $tail );
				break;

			case 'awaiting_b_approval':
				$school = AIADN_Schools::get( (int) $debate['school_b_id'] );
				$lead   = $school ? AIADN_Schools::lead( (int) $school['id'] ) : null;
				if ( $school && $lead ) {
					// A fresh approval link, because the first may have expired.
					AIADN_Front::send_slt_email( $school, $lead );
					self::to( $lead, 'Your school is waiting for approval', "{$school['name']} is waiting for headteacher approval before its debate can go ahead. We have emailed {$school['slt_email']} a fresh approval link." . $tail );
				}
				break;

			case 'matched':
				self::to( AIADN_Debates::owner( $debate, 'a' ), 'Time to propose your debate', "You are matched for debate {$debate['code']}. The next step is yours: propose the date, theme, motion and judge." . $tail );
				break;

			case 'proposed':
				$side = (int) $debate['proposed_by'] === (int) $debate['school_a_id'] ? 'b' : 'a';
				self::to( AIADN_Debates::owner( $debate, $side ), 'Please review the proposed debate', "A fixture is waiting for your reply. Please accept it, suggest another date, or decline:\n\n" . AIADN_Debates::summary( $debate ) . $tail );
				break;

			case 'agreed':
				$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
				if ( $judge && 'invited' === $judge['status'] ) {
					AIADN_Debates::invite_judge( $debate, $judge ); // A fresh invitation email.
				}
				foreach ( array( 'a', 'b' ) as $side ) {
					self::to( AIADN_Debates::owner( $debate, $side ), 'Your judge has not replied yet', ( $judge ? $judge['name'] : 'The judge' ) . " has not replied to the invitation. We have sent it again. You can also choose another judge from the debate page." . $tail );
				}
				break;

			case 'ready':
				$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
				if ( $judge ) {
					$link = AIADN_Debates::judge_link( $debate, $judge );
					AIADN_Mailer::send_notice( $judge['email'], 'Please submit the scores for your debate', "Thank you again for judging. We have not received the scores yet. Please open the scorecard and submit them:\n\n{$link}\n\nIf you have any problem, tell the schools. They can help." );
				}
				foreach ( array( 'a', 'b' ) as $side ) {
					self::to( AIADN_Debates::owner( $debate, $side ), 'The scores have not been submitted yet', "The judge has not submitted the scores for debate {$debate['code']}. We have reminded them." . $tail );
				}
				break;
		}
	}

	private static function to( ?array $person, string $subject, string $body ): void {
		if ( $person ) {
			AIADN_Mailer::send_notice( $person['email'], $subject, $body );
		}
	}

	/** When a debate at this stage will close, for the reminder text. */
	private static function deadline( array $debate ): int {
		$since = strtotime( ( $debate['stage_at'] ?: $debate['updated_at'] ) . ' UTC' );
		if ( 'ready' === $debate['status'] && $debate['starts_at'] ) {
			$since = max( $since, strtotime( $debate['starts_at'] . ' UTC' ) );
		}
		return $since + self::EXPIRE;
	}

	private static function expire( array $debate ): void {
		AIADN_Debates::update( (int) $debate['id'], array( 'status' => 'expired' ) );
		AIADN_Debates::log( (int) $debate['id'], 'expired', 0, $debate['status'] );
		$debate = AIADN_Debates::get( (int) $debate['id'] );
		foreach ( array( 'a', 'b' ) as $side ) {
			if ( 'b' === $side && ! $debate['school_b_id'] ) {
				continue;
			}
			self::to(
				AIADN_Debates::owner( $debate, $side ),
				'Your debate has closed',
				"Debate {$debate['code']} has closed because nothing happened for 14 days. Nothing is lost: you can start a new debate from your school page any time, and invite the same school or a different one.\n\n" . AIADN_Front::url( 'school' )
			);
		}
	}
}
