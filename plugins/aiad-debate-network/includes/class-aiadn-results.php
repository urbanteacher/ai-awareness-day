<?php
/**
 * Results: which debates count, what a school has achieved, what is shown publicly.
 *
 * A debate COUNTS when its result has been submitted (status completed) and no issue is open on it.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Results {

	/** Averages are only shown once at least this many debates in an age group have been scored. */
	const MIN_SAMPLE = 5;

	/** SQL fragment: completed, and no open issue. */
	private static function counting_where( string $alias = 'd' ): string {
		$issues = AIADN_Database::table( 'issues' );
		return "{$alias}.status = 'completed' AND NOT EXISTS (SELECT 1 FROM {$issues} i WHERE i.debate_id = {$alias}.id AND i.status = 'reported')";
	}

	/** @return array<int,array<string,mixed>> the school's counting debates, oldest first */
	public static function counting_debates( int $school_id ): array {
		global $wpdb;
		$d = AIADN_Database::table( 'debates' );
		return (array) $wpdb->get_results( $wpdb->prepare( "SELECT d.* FROM {$d} d WHERE (d.school_a_id = %d OR d.school_b_id = %d) AND " . self::counting_where() . ' ORDER BY d.starts_at ASC, d.id ASC', $school_id, $school_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
	}

	/** A result has been submitted: tell both schools, and see whether a certificate is earned. */
	public static function on_result_submitted( array $debate ): void {
		$card = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
		if ( ! $card ) {
			return;
		}
		$a      = AIADN_Schools::get( (int) $debate['school_a_id'] );
		$b      = AIADN_Schools::get( (int) $debate['school_b_id'] );
		$winner = 'a' === $card['winner'] ? $a['name'] : $b['name'];
		$body   = "The result is in for debate {$debate['code']}:\n\n{$a['name']} {$card['a_total']}  -  {$card['b_total']} {$b['name']}\nWinner: {$winner}\n\nIt is final unless a school reports a problem. See the full result, and the judge's comments for your school:\n\n" . AIADN_Debates::url( $debate );
		foreach ( array( 'a', 'b' ) as $side ) {
			$owner = AIADN_Debates::owner( $debate, $side );
			if ( $owner ) {
				AIADN_Mailer::send_notice( $owner['email'], 'The result is in: ' . $winner . ' won', $body );
			}
		}
		AIADN_Certificates::recalc( (int) $debate['school_a_id'] );
		AIADN_Certificates::recalc( (int) $debate['school_b_id'] );
		AIADN_Referrals::on_result( $debate );
	}

	/**
	 * Numbers for the school's results page.
	 *
	 * @return array<string,mixed>
	 */
	public static function summary( int $school_id ): array {
		$debates = self::counting_debates( $school_id );
		$rows    = array();
		$won     = 0;
		$students = 0;
		$opponents = array();
		$themes    = array();
		foreach ( array_reverse( $debates ) as $d ) {
			$card     = AIADN_Scorecards::get_for_debate( (int) $d['id'] );
			$side     = AIADN_Debates::side( $d, $school_id );
			$opp_id   = AIADN_Debates::other_school_id( $d, $school_id );
			$opp      = AIADN_Schools::get( $opp_id );
			$is_win   = $card && $card['winner'] === $side;
			$won     += $is_win ? 1 : 0;
			$students += $card ? (int) $card['students'] : 0;
			$opponents[ $opp_id ] = true;
			$themes[ $d['theme'] ] = true;
			$rows[]   = array( 'debate' => $d, 'card' => $card, 'side' => $side, 'opponent' => $opp ? $opp['name'] : '', 'won' => $is_win );
		}
		return array(
			'count'     => count( $debates ),
			'won'       => $won,
			'students'  => $students,
			'schools'   => count( $opponents ),
			'themes'    => count( $themes ),
			'rows'      => $rows,
		);
	}

	/**
	 * The average score for each criterion across every counting debate in an age group, or null if too few.
	 *
	 * @return array<string,float>|null
	 */
	public static function averages( string $age_group ): ?array {
		global $wpdb;
		$d = AIADN_Database::table( 'debates' );
		$c = AIADN_Database::table( 'scorecards' );
		$row = $wpdb->get_row( $wpdb->prepare( // phpcs:ignore WordPress.DB
			"SELECT COUNT(*) AS n,
				AVG(c.a_argument + c.b_argument) / 2 AS argument, AVG(c.a_evidence + c.b_evidence) / 2 AS evidence,
				AVG(c.a_rebuttal + c.b_rebuttal) / 2 AS rebuttal, AVG(c.a_delivery + c.b_delivery) / 2 AS delivery
			FROM {$d} d INNER JOIN {$c} c ON c.debate_id = d.id AND c.status = 'submitted'
			WHERE d.age_group = %s AND " . self::counting_where(),
			$age_group
		), ARRAY_A );
		if ( ! $row || (int) $row['n'] < self::MIN_SAMPLE ) {
			return null;
		}
		return array_map( 'floatval', array_intersect_key( $row, AIADN_Scorecards::CRITERIA ) );
	}

	/**
	 * Debates for the public results page. No teacher or student details, ever.
	 *
	 * @param array<string,string> $filters theme, age
	 * @return array<int,array<string,mixed>>
	 */
	public static function public_list( array $filters, int $limit = 100 ): array {
		global $wpdb;
		$d     = AIADN_Database::table( 'debates' );
		$where = self::counting_where();
		$args  = array();
		if ( ! empty( $filters['theme'] ) && isset( AIADN_Motions::THEMES[ $filters['theme'] ] ) ) {
			$where .= ' AND d.theme = %s';
			$args[] = $filters['theme'];
		}
		if ( ! empty( $filters['age'] ) && isset( AIADN_Motions::AGES[ $filters['age'] ] ) ) {
			$where .= ' AND d.age_group = %s';
			$args[] = $filters['age'];
		}
		$sql  = "SELECT d.* FROM {$d} d WHERE {$where} ORDER BY d.starts_at DESC, d.id DESC LIMIT " . (int) $limit;
		$rows = $args ? $wpdb->get_results( $wpdb->prepare( $sql, ...$args ), ARRAY_A ) : $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB
		$out  = array();
		foreach ( (array) $rows as $debate ) {
			$card = AIADN_Scorecards::get_for_debate( (int) $debate['id'] );
			$a    = AIADN_Schools::get( (int) $debate['school_a_id'] );
			$b    = AIADN_Schools::get( (int) $debate['school_b_id'] );
			if ( ! $card || ! $a || ! $b ) {
				continue;
			}
			$judge = (int) $debate['judge_id'] ? AIADN_Debates::get_judge( (int) $debate['judge_id'] ) : null;
			$out[] = array(
				'debate'  => $debate,
				'a'       => $a['name'],
				'b'       => $b['name'],
				'winner'  => 'a' === $card['winner'] ? $a['name'] : $b['name'],
				'judge'   => ( $judge && (int) $judge['name_public'] ) ? $judge['name'] : '',
			);
		}
		return $out;
	}

	public static function has_rated( int $debate_id, int $school_id, string $who ): bool {
		global $wpdb;
		return (bool) $wpdb->get_var( $wpdb->prepare( 'SELECT id FROM ' . AIADN_Database::table( 'ratings' ) . ' WHERE debate_id = %d AND school_id = %d AND who = %s', $debate_id, $school_id, $who ) ); // phpcs:ignore WordPress.DB
	}

	public static function save_rating( int $debate_id, int $school_id, string $who, int $rating ): void {
		global $wpdb;
		if ( $rating < 1 || $rating > 5 || self::has_rated( $debate_id, $school_id, $who ) ) {
			return;
		}
		$wpdb->insert( AIADN_Database::table( 'ratings' ), array( 'debate_id' => $debate_id, 'school_id' => $school_id, 'who' => $who, 'rating' => $rating, 'created_at' => AIADN_Util::now() ) ); // phpcs:ignore WordPress.DB
	}
}
