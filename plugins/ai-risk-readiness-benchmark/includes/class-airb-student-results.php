<?php
/**
 * Student results — learning-coach output with student-appropriate guidance.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds student-facing result payloads for the results screen.
 */
class AIRB_Student_Results {

	private const STRENGTH_LITERACY_MIN     = 75;
	private const STRENGTH_VERIFICATION_MIN = 45;
	private const OPPORTUNITY_MAX           = 85;
	private const HABITS_ALIGNMENT_MIN      = 45;

	/**
	 * Build the full student results payload.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array<string, mixed>
	 */
	public static function build( array $results ): array {
		$cfg       = AIRB_Defaults::student_result_config();
		$metrics   = self::learning_metrics( $results );
		$tier      = self::performance_tier( $results );
		$strengths = self::detect_strengths( $results, $cfg, $metrics );
		$opps      = self::detect_opportunities( $results, $cfg, $metrics );

		return array(
			'performance_tier'     => $tier,
			'performance_headline' => (string) ( $cfg['headlines'][ $tier ] ?? '' ),
			'profile_title'        => (string) ( $cfg['profile_title'] ?? __( 'Your AI Learning Profile', 'ai-risk-benchmark' ) ),
			'learning_metrics'     => $metrics,
			'strengths'            => $strengths,
			'opportunities'        => $opps,
			'learning_challenge'   => (array) ( $cfg['learning_challenge'] ?? array() ),
			'student_resources'    => (array) ( $cfg['student_resources'] ?? array() ),
			'school_contribution'  => (array) ( $cfg['school_contribution'] ?? array() ),
			'share_hint'           => (string) ( $cfg['share_hint'] ?? '' ),
		);
	}

	/**
	 * Student learning profile metrics (not DfE governance domains).
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array<int, array<string, mixed>>
	 */
	public static function learning_metrics( array $results ): array {
		$domains     = (array) ( $results['domain_scores'] ?? array() );
		$dependency  = (int) ( $results['dependency_index'] ?? 0 );
		$independent = max( 0, min( 100, 100 - $dependency ) );

		return array(
			array(
				'slug'  => 'independent_thinking',
				'label' => __( 'Independent Thinking', 'ai-risk-benchmark' ),
				'value' => $independent,
				'type'  => 'score',
			),
			array(
				'slug'  => 'ai_dependency',
				'label' => __( 'AI Dependency', 'ai-risk-benchmark' ),
				'value' => $dependency,
				'type'  => 'risk',
			),
			array(
				'slug'  => 'verification_skills',
				'label' => __( 'Verification Skills', 'ai-risk-benchmark' ),
				'value' => (int) round( (float) ( $domains['human_oversight']['readiness_percentage'] ?? 0 ) ),
				'type'  => 'score',
			),
			array(
				'slug'  => 'privacy_awareness',
				'label' => __( 'Privacy Awareness', 'ai-risk-benchmark' ),
				'value' => (int) round( (float) ( $domains['privacy']['readiness_percentage'] ?? 0 ) ),
				'type'  => 'score',
			),
			array(
				'slug'  => 'ai_literacy',
				'label' => __( 'AI Literacy', 'ai-risk-benchmark' ),
				'value' => (int) round( (float) ( $domains['ai_literacy']['readiness_percentage'] ?? 0 ) ),
				'type'  => 'score',
			),
		);
	}

	/**
	 * Overall readiness/risk from student-relevant domains only.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array{overall_risk:float,alignment_score:int,risk_level:string}
	 */
	public static function overall_from_student_domains( array $results ): array {
		$slugs       = array( 'ai_dependency', 'assessment_integrity', 'human_oversight', 'ai_literacy', 'privacy' );
		$domains     = (array) ( $results['domain_scores'] ?? array() );
		$risk_values = array();

		foreach ( $slugs as $slug ) {
			$dom = (array) ( $domains[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$risk_values[] = (float) ( $dom['risk_percentage'] ?? 0 );
		}

		if ( ! $risk_values ) {
			return array(
				'overall_risk'    => 0.0,
				'alignment_score' => 0,
				'risk_level'      => 'low',
			);
		}

		$overall_risk = array_sum( $risk_values ) / count( $risk_values );

		return array(
			'overall_risk'    => round( $overall_risk, 1 ),
			'alignment_score' => (int) round( 100 - $overall_risk ),
			'risk_level'      => AIRB_Scoring::risk_band( $overall_risk ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 */
	private static function performance_tier( array $results ): string {
		$alignment = (int) ( $results['alignment_score'] ?? 0 );

		if ( $alignment >= 76 ) {
			return 'strong';
		}
		if ( $alignment >= 51 ) {
			return 'established';
		}
		if ( $alignment >= 26 ) {
			return 'developing';
		}
		return 'emerging';
	}

	/**
	 * @param array<string, mixed>             $results Results.
	 * @param array<string, mixed>             $cfg     Config.
	 * @param array<int, array<string, mixed>> $metrics Metrics.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg, array $metrics ): array {
		$labels  = (array) ( $cfg['strength_labels'] ?? array() );
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$out     = array();
		$by_slug = array();

		foreach ( $metrics as $metric ) {
			$by_slug[ (string) ( $metric['slug'] ?? '' ) ] = $metric;
		}

		if ( (int) ( $by_slug['ai_literacy']['value'] ?? 0 ) >= self::STRENGTH_LITERACY_MIN ) {
			$out[] = (string) ( $labels['ai_literacy'] ?? '' );
		}
		if ( (int) ( $by_slug['verification_skills']['value'] ?? 0 ) >= self::STRENGTH_VERIFICATION_MIN ) {
			$out[] = (string) ( $labels['verification'] ?? '' );
		}
		if ( (int) ( $results['alignment_score'] ?? 0 ) >= self::HABITS_ALIGNMENT_MIN ) {
			$out[] = (string) ( $labels['healthy_habits'] ?? '' );
		}
		if ( (int) ( $by_slug['independent_thinking']['value'] ?? 0 ) >= self::STRENGTH_VERIFICATION_MIN
			&& (int) ( $domains['assessment_integrity']['readiness_percentage'] ?? 0 ) >= self::STRENGTH_VERIFICATION_MIN ) {
			$out[] = (string) ( $labels['honest_work'] ?? '' );
		}

		return array_values( array_filter( array_unique( $out ) ) );
	}

	/**
	 * @param array<string, mixed>             $results Results.
	 * @param array<string, mixed>             $cfg     Config.
	 * @param array<int, array<string, mixed>> $metrics Metrics.
	 * @return array<int, array<string, mixed>>
	 */
	private static function detect_opportunities( array $results, array $cfg, array $metrics ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$topics  = (array) ( $cfg['opportunity_topics'] ?? array() );
		$out     = array();

		foreach ( $topics as $topic ) {
			$triggers = (array) ( $topic['triggers'] ?? array() );
			$show     = false;
			$pct      = null;

			foreach ( $triggers as $trigger ) {
				if ( 'dependency_index' === $trigger ) {
					$dep = (int) ( $results['dependency_index'] ?? 0 );
					if ( $dep > ( 100 - self::OPPORTUNITY_MAX ) ) {
						$show = true;
						$pct  = null === $pct ? max( 0, 100 - $dep ) : min( $pct, max( 0, 100 - $dep ) );
					}
					continue;
				}
				$dom = (array) ( $domains[ $trigger ] ?? array() );
				if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
					continue;
				}
				$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
				if ( $readiness < self::OPPORTUNITY_MAX ) {
					$show = true;
					$pct  = null === $pct ? $readiness : min( $pct, $readiness );
				}
			}

			if ( ! $show ) {
				continue;
			}

			$out[] = array(
				'slug'    => (string) ( $topic['slug'] ?? '' ),
				'label'   => (string) ( $topic['label'] ?? '' ),
				'pct'     => $pct,
				'summary' => (string) ( $topic['summary'] ?? '' ),
				'detail'  => (string) ( $topic['detail'] ?? '' ),
				'tips'    => (array) ( $topic['tips'] ?? array() ),
			);
		}

		return $out;
	}
}
