<?php
/**
 * Student results — tiered UI copy resolved from scores.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves band-specific copy for student results screens.
 */
class AIRB_Student_Copy {

	/**
	 * Focus tier from skill % (lower = weaker).
	 */
	public static function focus_tier( int $pct ): string {
		if ( $pct <= 40 ) {
			return 'emerging';
		}
		if ( $pct <= 60 ) {
			return 'developing';
		}
		return 'confident';
	}

	/**
	 * Bias & fairness card tier (0–100 readiness-style score).
	 */
	public static function bias_tier( int $score ): string {
		if ( $score < 25 ) {
			return 'critical';
		}
		if ( $score < 50 ) {
			return 'high';
		}
		if ( $score < 75 ) {
			return 'moderate';
		}
		return 'low';
	}

	/**
	 * Resolved hero copy for the student learning profile header.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Student config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = AIRB_Student_Results::skill_band( $score );
		$slug  = (string) ( $band['slug'] ?? 'beginning' );
		$hero  = (array) ( ( $cfg['copy_tiers']['readiness'][ $slug ] ?? array() ) );

		if ( empty( $hero['consequence'] ) ) {
			$hero['consequence'] = (string) ( $cfg['headlines'][ $slug ] ?? ( $cfg['headlines']['developing'] ?? '' ) );
		}

		$ui = array(
			'hero' => self::signal_payload( $hero ),
			'band' => $band,
		);

		if ( array_key_exists( 'bias_readiness', $results ) && null !== $results['bias_readiness'] ) {
			$bias_score = (int) $results['bias_readiness'];
			$bias_card  = self::tier_payload( 'bias', self::bias_tier( $bias_score ), $cfg );
			$threshold  = (int) ( $cfg['bias_health_callout_threshold'] ?? 50 );
			if ( $bias_score < $threshold && ! empty( $cfg['bias_health_callout'] ) ) {
				$bias_card['consequence'] = (string) $cfg['bias_health_callout'];
			}
			$ui['bias_card'] = $bias_card;
		}

		return $ui;
	}

	/**
	 * Structured strength rows for the results UI.
	 *
	 * @param array<string, mixed>             $results Results.
	 * @param array<int, array<string, mixed>> $metrics Learning metrics.
	 * @param array<string, mixed>             $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function strength_statements( array $results, array $metrics, array $cfg ): array {
		unset( $results );
		$rules   = (array) ( $cfg['strength_tiers'] ?? array() );
		$by_slug = array();
		foreach ( $metrics as $metric ) {
			$by_slug[ (string) ( $metric['slug'] ?? '' ) ] = $metric;
		}

		$scored = array();
		foreach ( $rules as $slug => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$metric = (array) ( $by_slug[ $slug ] ?? array() );
			$pct    = (int) ( $metric['value'] ?? 0 );
			$min    = (int) ( $rule['min'] ?? 76 );
			if ( $pct < $min ) {
				continue;
			}
			$title = (string) ( $rule['label'] ?? '' );
			if ( ! $title ) {
				continue;
			}
			$detail_tpl = (string) ( $rule['detail'] ?? '' );
			$detail     = $detail_tpl ? str_replace( '{pct}', (string) $pct, $detail_tpl ) : '';
			$scored[]   = array(
				'slug'  => (string) $slug,
				'pct'   => $pct,
				'title' => $title,
				'detail'=> $detail,
			);
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $b['pct'] ?? 0 ) <=> ( $a['pct'] ?? 0 );
			}
		);

		return array_slice( $scored, 0, 3 );
	}

	/**
	 * Focus card copy for a student opportunity topic.
	 *
	 * @param array<string, mixed> $opp Opportunity row.
	 * @param array<string, mixed> $cfg Config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( array $opp, array $cfg ): array {
		$slug   = (string) ( $opp['slug'] ?? '' );
		$pct    = (int) ( $opp['pct'] ?? 0 );
		$tier   = self::focus_tier( $pct );
		$tiers  = (array) ( $cfg['focus_tiers'][ $slug ] ?? array() );
		$tiered = (array) ( $tiers[ $tier ] ?? array() );
		if ( empty( $tiered ) ) {
			$tiered = self::nearest_focus_tier( $tiers, $tier );
		}

		$summary = (string) ( $tiered['summary'] ?? $opp['summary'] ?? '' );
		if ( ! $summary && ! empty( $opp['summary'] ) ) {
			$summary = (string) $opp['summary'];
		}

		return array(
			'summary'           => $summary,
			'challenge_heading' => (string) ( $tiered['challenge_heading'] ?? '' ),
			'challenge_body'    => (string) ( $tiered['challenge_body'] ?? '' ),
			'challenge_bullets' => (array) ( $tiered['challenge_bullets'] ?? array() ),
			'actions'           => (array) ( $tiered['actions'] ?? $opp['tips'] ?? array() ),
			'tier'              => $tier,
		);
	}

	/**
	 * Nearest authored focus tier when a topic's copy set doesn't cover
	 * every severity level `focus_tier()` can return (e.g. a topic with no
	 * "confident" tier authored for high-scoring students).
	 *
	 * @param array<string, mixed> $tiers     Topic's focus tiers keyed by severity slug.
	 * @param string               $requested Requested tier slug.
	 * @return array<string, mixed>
	 */
	private static function nearest_focus_tier( array $tiers, string $requested ): array {
		$order = array( 'emerging', 'developing', 'confident' );
		$idx   = array_search( $requested, $order, true );
		if ( false === $idx ) {
			return array();
		}
		for ( $offset = 1; $offset < count( $order ); $offset++ ) {
			foreach ( array( $idx + $offset, $idx - $offset ) as $candidate ) {
				if ( isset( $order[ $candidate ] ) && ! empty( $tiers[ $order[ $candidate ] ] ) ) {
					return (array) $tiers[ $order[ $candidate ] ];
				}
			}
		}
		return array();
	}

	/**
	 * School contribution progress for the share card.
	 *
	 * @param string $school School name.
	 * @return array<string, mixed>
	 */
	public static function school_progress( string $school ): array {
		$school     = trim( $school );
		$threshold  = 20;
		$count      = 0;
		$has_school = '' !== $school;

		if ( $has_school ) {
			$count = AIRB_Database::count_submissions(
				array(
					'role'   => 'student',
					'school' => $school,
				)
			);
		}

		return array(
			'has_school'             => $has_school,
			'student_responses'      => $count,
			'threshold'              => $threshold,
			'whole_school_available' => $has_school && $count >= $threshold,
			'responses_remaining'    => $has_school ? max( 0, $threshold - $count ) : $threshold,
		);
	}

	/**
	 * @param array<string, mixed> $block Raw block.
	 * @return array<string, mixed>
	 */
	private static function signal_payload( array $block ): array {
		return array(
			'signal'      => (string) ( $block['signal'] ?? '' ),
			'tone'        => (string) ( $block['tone'] ?? 'neutral' ),
			'consequence' => (string) ( $block['consequence'] ?? '' ),
		);
	}

	/**
	 * @param string               $family Tier family key in copy_tiers.
	 * @param string               $slug   Tier slug.
	 * @param array<string, mixed> $cfg    Student config.
	 * @return array<string, mixed>
	 */
	private static function tier_payload( string $family, string $slug, array $cfg ): array {
		$tiers = (array) ( $cfg['copy_tiers'][ $family ] ?? array() );
		$block = (array) ( $tiers[ $slug ] ?? array() );
		return $block ? self::signal_payload( $block ) : array(
			'signal'      => '',
			'tone'        => 'neutral',
			'consequence' => '',
		);
	}
}
