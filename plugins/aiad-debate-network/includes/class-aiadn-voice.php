<?php
/**
 * Student Voice: five statements, one for each theme. Agree, not sure or disagree.
 *
 * Anonymous by design (decision 23.9): no names, no accounts, and no personal data stored at all.
 * A school only sees results once at least 10 students have answered, so no individual can be picked out.
 *
 * The statements are drafts for someone who knows children's surveys to review before national use.
 *
 * @package AIADN
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AIADN_Voice {

	/** Results stay hidden until this many students have answered. */
	const MIN_RESPONSES = 10;

	/** No more than this many answers per class PIN, so one PIN cannot flood a school's results. */
	const MAX_PER_PIN = 500;

	/**
	 * One statement for each theme, plus a second under RESPONSIBLE about the energy and water AI uses:
	 * children raised it unprompted, and sustainability is part of that theme.
	 */
	const QUESTIONS = array(
		'safe'        => array( 'theme' => 'safe', 'label' => 'SAFE', 'text' => 'I would tell an AI a secret.' ),
		'smart'       => array( 'theme' => 'smart', 'label' => 'SMART', 'text' => 'Using AI helps me learn better.' ),
		'creative'    => array( 'theme' => 'creative', 'label' => 'CREATIVE', 'text' => 'Something made with AI can still be my own work.' ),
		'responsible' => array( 'theme' => 'responsible', 'label' => 'RESPONSIBLE', 'text' => 'If an AI gets something wrong, the person who used it is to blame.' ),
		'planet'      => array( 'theme' => 'responsible', 'label' => 'RESPONSIBLE: energy and water', 'text' => 'AI is worth the energy and water it uses.' ),
		'future'      => array( 'theme' => 'future', 'label' => 'FUTURE', 'text' => 'Young people should have a say in how AI is used.' ),
	);

	const ANSWERS = array(
		1 => 'Agree',
		2 => 'Not sure',
		3 => 'Disagree',
	);

	const YEAR_GROUPS = array(
		'y1_4'   => 'Years 1 to 4',
		'y5_6'   => 'Years 5 and 6',
		'y7_9'   => 'Years 7 to 9',
		'y10_11' => 'Years 10 and 11',
		'post16' => 'Post-16',
		'skip'   => 'Prefer not to say',
	);

	/** Which age pathway a year group belongs to. "Prefer not to say" belongs to none. */
	const PATHWAYS = array(
		'y1_4'   => 'primary',
		'y5_6'   => 'primary',
		'y7_9'   => 'secondary',
		'y10_11' => 'secondary',
		'post16' => 'post16',
	);

	public static function pathway( string $year_group ): string {
		return self::PATHWAYS[ $year_group ] ?? '';
	}

	const PHASES = array(
		'general' => 'A general survey',
		'before'  => 'Before a debate',
		'after'   => 'After a debate',
	);

	/**
	 * Store one student's answers. Returns an error message, or '' on success.
	 *
	 * @param array<string,int> $answers theme => 1..3
	 */
	public static function submit( array $pin, string $year_group, array $answers ): string {
		global $wpdb;
		if ( ! isset( self::YEAR_GROUPS[ $year_group ] ) ) {
			return 'Choose your year group.';
		}
		$row = array(
			'school_id'  => (int) $pin['school_id'],
			'pin_id'     => (int) $pin['id'],
			'debate_id'  => (int) $pin['debate_id'],
			'phase'      => $pin['purpose'],
			'year_group' => $year_group,
			'created_at' => AIADN_Util::now(),
		);
		foreach ( array_keys( self::QUESTIONS ) as $key ) {
			$value = isset( $answers[ $key ] ) ? (int) $answers[ $key ] : 0;
			if ( ! isset( self::ANSWERS[ $value ] ) ) {
				return 'Please answer every question.';
			}
			$row[ 'q_' . $key ] = $value;
		}
		$count = (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM ' . AIADN_Database::table( 'voice' ) . ' WHERE pin_id = %d', (int) $pin['id'] ) ); // phpcs:ignore WordPress.DB
		if ( $count >= self::MAX_PER_PIN ) {
			return 'This PIN has reached its limit. Ask your teacher for a new one.';
		}
		$wpdb->insert( AIADN_Database::table( 'voice' ), $row ); // phpcs:ignore WordPress.DB
		return '';
	}

	public static function count( int $school_id, string $phase = '', int $debate_id = 0 ): int {
		global $wpdb;
		$sql  = 'SELECT COUNT(*) FROM ' . AIADN_Database::table( 'voice' ) . ' WHERE school_id = %d';
		$args = array( $school_id );
		if ( '' !== $phase ) {
			$sql   .= ' AND phase = %s';
			$args[] = $phase;
		}
		if ( $debate_id ) {
			$sql   .= ' AND debate_id = %d';
			$args[] = $debate_id;
		}
		return (int) $wpdb->get_var( $wpdb->prepare( $sql, ...$args ) ); // phpcs:ignore WordPress.DB
	}

	/**
	 * The percentages for each question, or null if fewer than the minimum have answered.
	 *
	 * A question is only shown once at least the minimum number of students have ANSWERED it, and
	 * the percentages are of those who did. Answers collected before a question existed are left out.
	 *
	 * @return array<string,array{agree:int,unsure:int,disagree:int}|null>|null
	 */
	public static function results( int $school_id, string $phase = '', int $debate_id = 0 ): ?array {
		global $wpdb;
		$n = self::count( $school_id, $phase, $debate_id );
		if ( $n < self::MIN_RESPONSES ) {
			return null;
		}
		$table = AIADN_Database::table( 'voice' );
		$out   = array();
		foreach ( array_keys( self::QUESTIONS ) as $theme ) {
			$col   = 'q_' . $theme;
			$where = 'school_id = %d';
			$args  = array( $school_id );
			if ( '' !== $phase ) {
				$where .= ' AND phase = %s';
				$args[] = $phase;
			}
			if ( $debate_id ) {
				$where .= ' AND debate_id = %d';
				$args[] = $debate_id;
			}
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT SUM({$col} = 1) AS a, SUM({$col} = 2) AS u, SUM({$col} = 3) AS d FROM {$table} WHERE {$where}", ...$args ), ARRAY_A ); // phpcs:ignore WordPress.DB
			$answered = (int) $row['a'] + (int) $row['u'] + (int) $row['d'];
			if ( $answered < self::MIN_RESPONSES ) {
				$out[ $theme ] = null;
				continue;
			}
			$out[ $theme ] = array(
				'agree'    => (int) round( 100 * (int) $row['a'] / $answered ),
				'unsure'   => (int) round( 100 * (int) $row['u'] / $answered ),
				'disagree' => (int) round( 100 * (int) $row['d'] / $answered ),
			);
		}
		return $out;
	}

	/** The theme where students disagree with each other most: agree and disagree are closest. */
	public static function biggest_split( array $results ): string {
		$best   = '';
		$spread = PHP_INT_MAX;
		foreach ( $results as $theme => $r ) {
			if ( ! $r ) {
				continue;
			}
			$gap = abs( $r['agree'] - $r['disagree'] ) - min( $r['agree'], $r['disagree'] ) / 100;
			if ( $gap < $spread ) {
				$spread = $gap;
				$best   = $theme;
			}
		}
		return $best;
	}

	/**
	 * Debates where both a "before" and an "after" survey have enough answers, so movement can be shown.
	 *
	 * @return array<int,array{debate:array,before:array,after:array}>
	 */
	public static function movement( int $school_id ): array {
		global $wpdb;
		$ids = (array) $wpdb->get_col( $wpdb->prepare( 'SELECT DISTINCT debate_id FROM ' . AIADN_Database::table( 'voice' ) . " WHERE school_id = %d AND debate_id > 0 AND phase IN ('before','after')", $school_id ) ); // phpcs:ignore WordPress.DB
		$out = array();
		foreach ( $ids as $id ) {
			$before = self::results( $school_id, 'before', (int) $id );
			$after  = self::results( $school_id, 'after', (int) $id );
			$debate = AIADN_Debates::get( (int) $id );
			if ( $before && $after && $debate ) {
				$out[] = array( 'debate' => $debate, 'before' => $before, 'after' => $after );
			}
		}
		return $out;
	}
}
