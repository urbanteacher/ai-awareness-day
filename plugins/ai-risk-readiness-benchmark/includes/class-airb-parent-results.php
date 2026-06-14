<?php
/**
 * Parent / carer results — tiered journeys, benchmarking and school advocacy.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds parent-facing result payloads.
 */
class AIRB_Parent_Results {

	private const HIGH_SCORE_THRESHOLD      = 85;
	private const LOW_SCORE_THRESHOLD       = 40;
	private const CONFIDENCE_FOCUS_MAX      = 75;
	private const STRENGTH_READINESS_MIN    = 70;

	/**
	 * @param array<string, mixed> $results Scored results (incl. parent_display_domains).
	 * @return array<string, mixed>
	 */
	public static function build( array $results ): array {
		$cfg   = AIRB_Defaults::parent_result_config();
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$tier  = self::journey_tier( $score );

		return array(
			'journey_tier'          => $tier,
			'suppress_improvement'  => 'high' === $tier,
			'peer_benchmark'        => self::peer_benchmark( $results, $cfg ),
			'advocate'              => 'high' === $tier ? self::advocate_block( $results, $cfg ) : null,
			'confidence'            => self::confidence_block( $results, $cfg ),
			'next_steps'            => self::next_steps( $tier, $cfg ),
		);
	}

	/**
	 * @param int $score Alignment score 0-100.
	 */
	public static function journey_tier( int $score ): string {
		if ( $score >= self::HIGH_SCORE_THRESHOLD ) {
			return 'high';
		}
		if ( $score < self::LOW_SCORE_THRESHOLD ) {
			return 'low';
		}
		return 'medium';
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function peer_benchmark( array $results, array $cfg ): array {
		$score    = (int) ( $results['alignment_score'] ?? 0 );
		$stats    = AIRB_Database::get_benchmark_stats( 'parent', $score );
		$fallback = (array) ( $cfg['peer_benchmark_fallback'] ?? array() );

		if ( $stats ) {
			return array(
				'your_score'    => $score,
				'average_score' => (int) ( $stats['average'] ?? $stats['averages']['alignment_score'] ?? 58 ),
				'top_quartile'  => (int) ( $stats['top_quartile'] ?? 82 ),
				'sample_size'   => (int) ( $stats['sample_size'] ?? 0 ),
				'percentile'    => isset( $stats['percentile'] ) ? (int) $stats['percentile'] : null,
				'is_estimated'  => false,
			);
		}

		return array(
			'your_score'    => $score,
			'average_score' => (int) ( $fallback['average'] ?? 58 ),
			'top_quartile'  => (int) ( $fallback['top_quartile'] ?? 82 ),
			'sample_size'   => 0,
			'percentile'    => null,
			'is_estimated'  => true,
		);
	}

	/**
	 * High-scorer advocacy block.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function advocate_block( array $results, array $cfg ): array {
		$display   = (array) ( $results['parent_display_domains'] ?? array() );
		$labels    = (array) ( $cfg['advocate_strength_labels'] ?? array() );
		$strengths = array();

		foreach ( $labels as $slug => $label ) {
			$dom = (array) ( $display[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$is_risk    = ( 'risk' === ( $dom['metric_type'] ?? 'score' ) );
			$readiness  = (int) round( (float) ( $is_risk ? ( 100 - ( $dom['risk_percentage'] ?? 0 ) ) : ( $dom['readiness_percentage'] ?? 0 ) ) );
			if ( $readiness >= self::STRENGTH_READINESS_MIN ) {
				$strengths[] = (string) $label;
			}
		}

		return array(
			'title'       => (string) ( $cfg['advocate_title'] ?? __( 'You\'re ahead of most parents', 'ai-risk-benchmark' ) ),
			'intro'       => (string) ( $cfg['advocate_intro'] ?? __( 'You demonstrate strong awareness of AI use at home. Help your school build a whole-community picture.', 'ai-risk-benchmark' ) ),
			'strengths'   => $strengths,
			'help_title'  => (string) ( $cfg['advocate_help_title'] ?? __( 'Help your school', 'ai-risk-benchmark' ) ),
			'help_items'  => (array) ( $cfg['advocate_help_items'] ?? array() ),
		);
	}

	/**
	 * Confidence guidance when score is below threshold.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>|null
	 */
	private static function confidence_block( array $results, array $cfg ): ?array {
		$display = (array) ( $results['parent_display_domains'] ?? array() );
		$dom     = (array) ( $display['parent_confidence'] ?? array() );
		if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
			return null;
		}

		$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
		if ( $readiness >= self::CONFIDENCE_FOCUS_MAX ) {
			return null;
		}

		$copy = (array) ( $cfg['confidence_copy'] ?? array() );

		return array(
			'title'          => (string) ( $copy['title'] ?? __( 'Building your confidence', 'ai-risk-benchmark' ) ),
			'score'          => $readiness,
			'impact_heading' => (string) ( $copy['impact_heading'] ?? __( 'Parents with lower confidence often report:', 'ai-risk-benchmark' ) ),
			'impact_items'   => (array) ( $copy['impact_items'] ?? array() ),
			'improve_heading'=> (string) ( $copy['improve_heading'] ?? __( 'To improve confidence:', 'ai-risk-benchmark' ) ),
			'improve_items'  => (array) ( $copy['improve_items'] ?? array() ),
		);
	}

	/**
	 * Tier-specific non-commercial next steps.
	 *
	 * @param string               $tier Journey tier.
	 * @param array<string, mixed> $cfg  Config.
	 * @return array<string, mixed>
	 */
	private static function next_steps( string $tier, array $cfg ): array {
		$blocks = (array) ( $cfg['journey_next_steps'] ?? array() );
		$block  = (array) ( $blocks[ $tier ] ?? $blocks['medium'] ?? array() );

		return array(
			'title'    => (string) ( $block['title'] ?? __( 'Suggested next steps', 'ai-risk-benchmark' ) ),
			'intro'    => (string) ( $block['intro'] ?? '' ),
			'items'    => (array) ( $block['items'] ?? array() ),
			'cta_key'  => (string) ( $block['cta_key'] ?? '' ),
			'cta_text' => (string) ( $block['cta_text'] ?? '' ),
		);
	}
}
