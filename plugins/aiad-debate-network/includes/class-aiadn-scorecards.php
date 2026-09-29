<?php
/**
 * The judge's scorecard: four criteria scored 1 to 5 for each school, a winner, comments, and the
 * room vote before and after. The judge's submission is the result, and it is final immediately
 * (decision 23.5). It only reopens if the schools' issue is resolved with "judge re-submits".
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Scorecards {

	const CRITERIA = array(
		'argument' => 'Argument',
		'evidence' => 'Evidence',
		'rebuttal' => 'Rebuttal',
		'delivery' => 'Delivery',
	);

	/** Scoring opens this long before the start, so a judge can arrive early and prepare. */
	const OPENS_BEFORE = 10800; // 3 hours.

	public static function get_for_debate( int $debate_id ): ?array {
		global $wpdb;
		$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . AIADN_Database::table( 'scorecards' ) . ' WHERE debate_id = %d', $debate_id ), ARRAY_A ); // phpcs:ignore WordPress.DB
		return $row ?: null;
	}

	public static function opens_at( array $debate ): int {
		return $debate['starts_at'] ? strtotime( $debate['starts_at'] . ' UTC' ) - self::OPENS_BEFORE : PHP_INT_MAX;
	}

	public static function is_open( array $debate ): bool {
		return 'ready' === $debate['status'] && self::opens_at( $debate ) <= time();
	}

	/** @return array<string,mixed> */
	public static function blank(): array {
		$data = array( 'students' => 0, 'winner' => '', 'comment_a' => '', 'comment_b' => '' );
		foreach ( array( 'vb', 'va' ) as $when ) {
			foreach ( array( 'agree', 'disagree', 'unsure' ) as $k ) {
				$data[ $when . '_' . $k ] = 0;
			}
		}
		foreach ( array( 'a', 'b' ) as $side ) {
			foreach ( array_keys( self::CRITERIA ) as $c ) {
				$data[ $side . '_' . $c ] = 0;
			}
		}
		return $data;
	}

	/**
	 * Turn whatever was posted into safe values. Never trusts the browser: numbers are clamped, the winner is a or b.
	 *
	 * @param array<string,string> $raw
	 * @return array<string,mixed>
	 */
	public static function clean( array $raw ): array {
		$data = self::blank();
		foreach ( $data as $key => $default ) {
			$value = isset( $raw[ $key ] ) ? (string) $raw[ $key ] : '';
			if ( 'winner' === $key ) {
				$data[ $key ] = in_array( $value, array( 'a', 'b' ), true ) ? $value : '';
			} elseif ( 'comment_a' === $key || 'comment_b' === $key ) {
				$data[ $key ] = function_exists( 'mb_substr' ) ? mb_substr( $value, 0, 1000 ) : substr( $value, 0, 1000 );
			} elseif ( preg_match( '/^[ab]_/', $key ) ) {
				$data[ $key ] = max( 0, min( 5, (int) $value ) );
			} else {
				$data[ $key ] = max( 0, min( 500, (int) $value ) );
			}
		}
		return $data;
	}

	/** @return array<string,string> errors keyed by field */
	public static function validate( array $data ): array {
		$errors = array();
		foreach ( array( 'a', 'b' ) as $side ) {
			foreach ( array_keys( self::CRITERIA ) as $c ) {
				if ( $data[ $side . '_' . $c ] < 1 ) {
					$errors[ $side . '_' . $c ] = 'Score each criterion from 1 to 5.';
				}
			}
		}
		if ( '' === $data['winner'] ) {
			$errors['winner'] = 'Choose the winner.';
		}
		if ( $data['students'] < 1 ) {
			$errors['students'] = 'Enter how many students took part.';
		}
		return $errors;
	}

	public static function totals( array $data ): array {
		$a = 0;
		$b = 0;
		foreach ( array_keys( self::CRITERIA ) as $c ) {
			$a += (int) $data[ 'a_' . $c ];
			$b += (int) $data[ 'b_' . $c ];
		}
		return array( $a, $b );
	}

	private static function save( array $debate, array $judge, array $data, string $status ): void {
		global $wpdb;
		$table = AIADN_Database::table( 'scorecards' );
		list( $a, $b ) = self::totals( $data );
		$now  = AIADN_Util::now();
		$row  = array_merge(
			$data,
			array(
				'debate_id'    => (int) $debate['id'],
				'judge_id'     => (int) $judge['id'],
				'status'       => $status,
				'a_total'      => $a,
				'b_total'      => $b,
				'submitted_by' => 'submitted' === $status ? $judge['email'] : '',
				'submitted_at' => 'submitted' === $status ? $now : null,
				'updated_at'   => $now,
			)
		);
		if ( self::get_for_debate( (int) $debate['id'] ) ) {
			$wpdb->update( $table, $row, array( 'debate_id' => (int) $debate['id'] ) ); // phpcs:ignore WordPress.DB
		} else {
			$row['created_at'] = $now;
			$wpdb->insert( $table, $row ); // phpcs:ignore WordPress.DB
		}
	}

	/** Save as the judge types. Changes nothing else. */
	public static function save_draft( array $debate, array $judge, array $data ): void {
		$existing = self::get_for_debate( (int) $debate['id'] );
		if ( $existing && 'submitted' === $existing['status'] ) {
			return;
		}
		self::save( $debate, $judge, $data, 'draft' );
	}

	/**
	 * Submit the result. Returns errors, or an empty array on success. Once done, the debate is completed and final.
	 *
	 * @return array<string,string>
	 */
	public static function submit( array $debate, array $judge, array $data ): array {
		if ( 'ready' !== $debate['status'] ) {
			return array( 'form' => 'This scorecard cannot be submitted now.' );
		}
		$errors = self::validate( $data );
		if ( $errors ) {
			return $errors;
		}
		self::save( $debate, $judge, $data, 'submitted' );
		AIADN_Debates::update( (int) $debate['id'], array( 'status' => 'completed' ) );
		AIADN_Debates::log( (int) $debate['id'], 'result_submitted', 0, $judge['email'] );
		AIADN_Results::on_result_submitted( AIADN_Debates::get( (int) $debate['id'] ) );
		return array();
	}

	/** The judge may correct the scores after the schools' issue was resolved as "judge re-submits". */
	public static function reopen( array $debate ): void {
		global $wpdb;
		$wpdb->update( AIADN_Database::table( 'scorecards' ), array( 'status' => 'draft', 'updated_at' => AIADN_Util::now() ), array( 'debate_id' => (int) $debate['id'] ) ); // phpcs:ignore WordPress.DB
		AIADN_Debates::update( (int) $debate['id'], array( 'status' => 'ready' ) );
		AIADN_Debates::log( (int) $debate['id'], 'result_reopened', 0 );
	}

	/** Which school won: its id, or 0 if there is no submitted result. */
	public static function winner_school_id( array $debate, ?array $card ): int {
		if ( ! $card || 'submitted' !== $card['status'] || ! in_array( $card['winner'], array( 'a', 'b' ), true ) ) {
			return 0;
		}
		return (int) ( 'a' === $card['winner'] ? $debate['school_a_id'] : $debate['school_b_id'] );
	}
}
