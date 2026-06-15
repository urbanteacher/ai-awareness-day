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
	private const DISPLAY_SCORE_MIN         = 5;
	private const FOCUS_AREA_MAX            = 4;

	/**
	 * Build the full student results payload.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param string               $school  Optional school name from submission.
	 * @return array<string, mixed>
	 */
	public static function build( array $results, string $school = '' ): array {
		$cfg                 = AIRB_Defaults::student_result_config();
		$metrics             = self::learning_metrics( $results );
		$score               = (int) ( $results['alignment_score'] ?? 0 );
		$skill_band          = self::skill_band( $score );
		$strength_items      = AIRB_Student_Copy::strength_statements( $results, $metrics, $cfg );
		$legacy_strengths    = self::detect_strengths( $results, $cfg, $metrics );
		$opps                = self::detect_opportunities( $results, $cfg, $metrics );
		$focus_areas         = self::build_focus_areas( $opps, $metrics, $cfg );
		$journey             = self::learning_journey( $score, $cfg, $metrics );
		$ui                  = AIRB_Student_Copy::resolve_ui( $results, $cfg );
		$school_progress     = AIRB_Student_Copy::school_progress( $school );

		$strength_titles = array();
		foreach ( $strength_items as $item ) {
			if ( ! empty( $item['title'] ) ) {
				$strength_titles[] = (string) $item['title'];
			}
		}
		if ( ! $strength_titles ) {
			$strength_titles = $legacy_strengths;
		}

		return array(
			'performance_tier'      => (string) ( $skill_band['slug'] ?? 'beginning' ),
			'performance_headline'  => (string) ( $ui['hero']['consequence'] ?? ( $cfg['headlines'][ $skill_band['slug'] ?? '' ] ?? '' ) ),
			'skill_band'            => $skill_band,
			'profile_title'         => (string) ( $cfg['profile_title'] ?? __( 'Your learning profile', 'ai-risk-benchmark' ) ),
			'ui'                    => $ui,
			'learning_metrics'      => $metrics,
			'bias_health'           => AIRB_Components::bias_health(
				$results,
				$cfg,
				array(
					'role'       => 'student',
					'score'      => self::display_score( (int) ( $results['bias_readiness'] ?? 0 ) ),
					'band_label' => (string) ( self::skill_band( self::display_score( (int) ( $results['bias_readiness'] ?? 0 ) ) )['label'] ?? '' ),
					'subtitle'   => __( 'Fairness · protected characteristics · online safety', 'ai-risk-benchmark' ),
					'callout'    => __( 'AI tools can produce unfair or stereotypical answers about groups of people. Learning to spot this helps you use AI more safely and fairly.', 'ai-risk-benchmark' ),
				)
			),
			'learner_type'          => self::learner_type( $results, $metrics, $cfg ),
			'learning_journey'      => $journey,
			'peer_benchmark'        => self::peer_benchmark( $results, $cfg ),
			'strengths'             => $strength_titles,
			'strength_items'        => $strength_items,
			'opportunities'         => $opps,
			'focus_areas'           => $focus_areas,
			'learning_challenge'    => (array) ( $cfg['learning_challenge'] ?? array() ),
			'weekly_challenge'      => (array) ( $cfg['weekly_challenge'] ?? array() ),
			'student_resources'     => (array) ( $cfg['student_resources'] ?? array() ),
			'school_contribution'   => (array) ( $cfg['school_contribution'] ?? array() ),
			'school_progress'       => $school_progress,
			'share_hint'            => (string) ( $cfg['share_hint'] ?? '' ),
			'next_steps'            => self::next_steps( $cfg ),
		);
	}

	/**
	 * Focus cards for the weakest student skill areas.
	 *
	 * @param array<int, array<string, mixed>> $opps    Opportunity rows.
	 * @param array<int, array<string, mixed>> $metrics Learning metrics.
	 * @param array<string, mixed>             $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function build_focus_areas( array $opps, array $metrics, array $cfg ): array {
		$label_map = (array) ( $cfg['focus_label_map'] ?? array() );
		$by_slug   = array();
		foreach ( $metrics as $metric ) {
			$by_slug[ (string) ( $metric['slug'] ?? '' ) ] = $metric;
		}

		usort(
			$opps,
			static function ( $a, $b ) {
				$a_pct = isset( $a['pct'] ) ? (int) $a['pct'] : 100;
				$b_pct = isset( $b['pct'] ) ? (int) $b['pct'] : 100;
				return $a_pct <=> $b_pct;
			}
		);

		$out = array();
		foreach ( array_slice( $opps, 0, self::FOCUS_AREA_MAX ) as $opp ) {
			$slug        = (string) ( $opp['slug'] ?? '' );
			$map         = (array) ( $label_map[ $slug ] ?? array() );
			$metric_slug = (string) ( $map['metric'] ?? '' );
			$metric      = (array) ( $by_slug[ $metric_slug ] ?? array() );
			$pct         = isset( $metric['value'] ) ? (int) $metric['value'] : ( isset( $opp['pct'] ) ? (int) $opp['pct'] : 0 );
			$band        = ! empty( $metric['skill_band'] ) ? (array) $metric['skill_band'] : self::skill_band( $pct );
			$label       = (string) ( $metric['label'] ?? $opp['label'] ?? '' );
			$block       = AIRB_Student_Copy::focus_block( $opp, $cfg );

			$out[] = array_merge(
				$block,
				array(
					'slug'       => $slug,
					'label'      => $label,
					'pct'        => $pct,
					'skill_band' => $band,
				)
			);
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $cfg Config.
	 * @return array<string, mixed>
	 */
	private static function next_steps( array $cfg ): array {
		$lc = (array) ( $cfg['learning_challenge'] ?? array() );
		$wc = (array) ( $cfg['weekly_challenge'] ?? array() );

		return array(
			'hero_heading' => (string) ( $cfg['hero_next_step_heading'] ?? __( 'Your next step', 'ai-risk-benchmark' ) ),
			'hero'         => array(
				'key'              => 'retake',
				'title'            => (string) ( $wc['title'] ?? $lc['title'] ?? __( 'Your next challenge', 'ai-risk-benchmark' ) ),
				'body'             => trim( (string) ( $wc['intro'] ?? $lc['intro'] ?? '' ) . ' ' . (string) ( $wc['retake_note'] ?? '' ) ),
				'understand_items' => (array) ( $wc['items'] ?? $lc['steps'] ?? array() ),
				'cta_text'         => (string) ( $cfg['retake_cta'] ?? __( 'Retake the benchmark', 'ai-risk-benchmark' ) ),
				'cta_url'          => AIRB_Defaults::benchmark_page_url(),
				'cta_type'         => 'link',
			),
			'resource_links'             => AIRB_Defaults::results_timeline_read_links( 'student' ),
			'help_support_heading'       => (string) ( $cfg['help_support_heading'] ?? __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ) ),
			'help_support_heading_short' => (string) ( $cfg['help_support_heading_short'] ?? __( 'Read more & tips', 'ai-risk-benchmark' ) ),
		);
	}

	/**
	 * Floor raw percentages so students never see a harsh 0%.
	 *
	 * @param int $raw Raw 0-100 score.
	 */
	public static function display_score( int $raw ): int {
		$raw = max( 0, min( 100, $raw ) );
		if ( $raw < self::DISPLAY_SCORE_MIN ) {
			return self::DISPLAY_SCORE_MIN;
		}
		return $raw;
	}

	/**
	 * Student-friendly skill band label (distinct from school readiness bands).
	 *
	 * @param int $score Display score 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function skill_band( int $score ): array {
		$score  = self::display_score( $score );
		$levels = AIRB_Defaults::student_journey_levels();

		foreach ( $levels as $level ) {
			if ( $score >= (int) $level['min'] && $score <= (int) $level['max'] ) {
				return array(
					'slug'  => (string) $level['slug'],
					'label' => (string) $level['label'],
				);
			}
		}

		return array(
			'slug'  => 'beginning',
			'label' => __( 'Needs attention', 'ai-risk-benchmark' ),
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
		$independent = self::display_score( max( 0, min( 100, 100 - $dependency ) ) );

		$metrics = array(
			array(
				'slug'       => 'independent_thinking',
				'label'      => __( 'Independent Thinking', 'ai-risk-benchmark' ),
				'value'      => $independent,
				'type'       => 'score',
				'skill_band' => self::skill_band( $independent ),
			),
			array(
				'slug'       => 'verification_skills',
				'label'      => __( 'Verification Skills', 'ai-risk-benchmark' ),
				'value'      => self::display_score( (int) round( (float) ( $domains['human_oversight']['readiness_percentage'] ?? 0 ) ) ),
				'type'       => 'score',
				'skill_band' => self::skill_band( (int) round( (float) ( $domains['human_oversight']['readiness_percentage'] ?? 0 ) ) ),
			),
			array(
				'slug'       => 'privacy_awareness',
				'label'      => __( 'Privacy Awareness', 'ai-risk-benchmark' ),
				'value'      => self::display_score( (int) round( (float) ( $domains['privacy']['readiness_percentage'] ?? 0 ) ) ),
				'type'       => 'score',
				'skill_band' => self::skill_band( (int) round( (float) ( $domains['privacy']['readiness_percentage'] ?? 0 ) ) ),
			),
			array(
				'slug'       => 'ai_literacy',
				'label'      => __( 'AI Literacy', 'ai-risk-benchmark' ),
				'value'      => self::display_score( (int) round( (float) ( $domains['ai_literacy']['readiness_percentage'] ?? 0 ) ) ),
				'type'       => 'score',
				'skill_band' => self::skill_band( (int) round( (float) ( $domains['ai_literacy']['readiness_percentage'] ?? 0 ) ) ),
			),
		);

		if ( array_key_exists( 'bias_readiness', $results ) && null !== $results['bias_readiness'] ) {
			$bias_score = self::display_score( (int) $results['bias_readiness'] );
			$metrics[]  = array(
				'slug'       => 'bias_fairness',
				'label'      => __( 'Bias & fairness awareness', 'ai-risk-benchmark' ),
				'value'      => $bias_score,
				'type'       => 'score',
				'skill_band' => self::skill_band( $bias_score ),
			);
		}

		return $metrics;
	}

	/**
	 * Overall readiness/risk from student-relevant domains only.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array{overall_risk:float,alignment_score:int,risk_level:string}
	 */
	public static function overall_from_student_domains( array $results ): array {
		$slugs       = array( 'ai_dependency', 'assessment_integrity', 'human_oversight', 'ai_literacy', 'privacy', 'safeguarding' );
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

		$overall_risk    = array_sum( $risk_values ) / count( $risk_values );
		$alignment_score = self::display_score( (int) round( 100 - $overall_risk ) );

		return array(
			'overall_risk'    => round( 100 - $alignment_score, 1 ),
			'alignment_score' => $alignment_score,
			'risk_level'      => AIRB_Scoring::risk_band( $overall_risk ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function peer_benchmark( array $results, array $cfg ): array {
		$score    = (int) ( $results['alignment_score'] ?? 0 );
		$stats    = AIRB_Database::get_benchmark_stats( 'student', $score );
		$fallback = (array) ( $cfg['peer_benchmark_fallback'] ?? array() );

		if ( $stats ) {
			return array(
				'your_score'    => $score,
				'average_score' => (int) ( $stats['average'] ?? $stats['averages']['alignment_score'] ?? 51 ),
				'top_quartile'  => (int) ( $stats['top_quartile'] ?? 78 ),
				'sample_size'   => (int) ( $stats['sample_size'] ?? 0 ),
				'percentile'    => isset( $stats['percentile'] ) ? (int) $stats['percentile'] : null,
				'is_estimated'  => false,
			);
		}

		return array(
			'your_score'    => $score,
			'average_score' => (int) ( $fallback['average'] ?? 51 ),
			'top_quartile'  => (int) ( $fallback['top_quartile'] ?? 78 ),
			'sample_size'   => 0,
			'percentile'    => null,
			'is_estimated'  => true,
		);
	}

	/**
	 * Progression path for gamified learning journey.
	 *
	 * @param int                              $score   Overall alignment.
	 * @param array<string, mixed>             $cfg     Config.
	 * @param array<int, array<string, mixed>> $metrics Learning metrics.
	 * @return array<string, mixed>
	 */
	private static function learning_journey( int $score, array $cfg, array $metrics ): array {
		$levels = (array) ( $cfg['journey_levels'] ?? array() );
		$current = self::skill_band( $score );
		$next    = null;
		$found   = false;

		foreach ( $levels as $level ) {
			$slug = (string) ( $level['slug'] ?? '' );
			if ( $found ) {
				$next = array(
					'slug'  => $slug,
					'label' => (string) ( $level['label'] ?? '' ),
				);
				break;
			}
			if ( $slug === $current['slug'] ) {
				$found = true;
			}
		}

		$focus = array();
		$by_slug = array();
		foreach ( $metrics as $metric ) {
			$by_slug[ (string) ( $metric['slug'] ?? '' ) ] = $metric;
		}
		$focus_map = (array) ( $cfg['journey_focus_map'] ?? array() );
		foreach ( $focus_map as $slug => $label ) {
			$value = (int) ( $by_slug[ $slug ]['value'] ?? 100 );
			if ( $value < 61 ) {
				$focus[] = (string) $label;
			}
		}
		if ( ! $focus ) {
			$focus = (array) ( $cfg['journey_focus_default'] ?? array() );
		}

		return array(
			'title'          => (string) ( $cfg['journey_title'] ?? __( 'Your AI Learning Journey', 'ai-risk-benchmark' ) ),
			'current_label'  => (string) $current['label'],
			'current_slug'   => (string) $current['slug'],
			'next_label'     => $next ? (string) $next['label'] : '',
			'next_slug'      => $next ? (string) $next['slug'] : '',
			'focus_heading'  => (string) ( $cfg['journey_focus_heading'] ?? __( 'To get there:', 'ai-risk-benchmark' ) ),
			'focus_items'    => array_slice( $focus, 0, 4 ),
			'retake_note'    => (string) ( $cfg['journey_retake_note'] ?? __( 'Retake the benchmark to see your progress.', 'ai-risk-benchmark' ) ),
		);
	}

	/**
	 * AI Learner Types™ profile from behavioural patterns.
	 *
	 * @param array<string, mixed>             $results Results.
	 * @param array<int, array<string, mixed>> $metrics Metrics.
	 * @param array<string, mixed>             $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function learner_type( array $results, array $metrics, array $cfg ): array {
		$types   = (array) ( $cfg['learner_types'] ?? array() );
		$by_slug = array();
		foreach ( $metrics as $metric ) {
			$by_slug[ (string) ( $metric['slug'] ?? '' ) ] = $metric;
		}

		$dependency  = (int) ( $results['dependency_index'] ?? 0 );
		$verify      = (int) ( $by_slug['verification_skills']['value'] ?? 0 );
		$literacy    = (int) ( $by_slug['ai_literacy']['value'] ?? 0 );
		$independent = (int) ( $by_slug['independent_thinking']['value'] ?? 0 );

		$slug = 'ai_assistant_user';
		if ( $dependency >= 55 ) {
			$slug = 'over_reliant';
		} elseif ( $dependency >= 40 && $literacy < 50 ) {
			$slug = 'early_explorer';
		} elseif ( $verify >= 55 && $dependency <= 45 ) {
			$slug = 'confident_checker';
		} elseif ( $independent >= 55 && $dependency <= 40 ) {
			$slug = 'ai_assistant_user';
		}

		$type = (array) ( $types[ $slug ] ?? $types['ai_assistant_user'] ?? array() );

		return array(
			'slug'         => $slug,
			'title'        => (string) ( $type['title'] ?? '' ),
			'brand'        => (string) ( $cfg['learner_types_brand'] ?? __( 'AI Learner Types', 'ai-risk-benchmark' ) ),
			'description'  => (string) ( $type['description'] ?? '' ),
			'focus_heading'=> (string) ( $type['focus_heading'] ?? __( 'Focus:', 'ai-risk-benchmark' ) ),
			'focus_items'  => (array) ( $type['focus_items'] ?? array() ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 */
	private static function performance_tier( array $results ): string {
		return AIRB_Scoring::readiness_band( (int) ( $results['alignment_score'] ?? 0 ) );
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
		unset( $metrics );
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
						$pct  = null === $pct ? self::display_score( max( 0, 100 - $dep ) ) : min( $pct, self::display_score( max( 0, 100 - $dep ) ) );
					}
					continue;
				}
				if ( 'bias_readiness' === $trigger ) {
					if ( ! array_key_exists( 'bias_readiness', $results ) || null === $results['bias_readiness'] ) {
						continue;
					}
					$readiness = self::display_score( (int) $results['bias_readiness'] );
					if ( $readiness < self::OPPORTUNITY_MAX ) {
						$show = true;
						$pct  = null === $pct ? $readiness : min( $pct, $readiness );
					}
					continue;
				}
				$dom = (array) ( $domains[ $trigger ] ?? array() );
				if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
					continue;
				}
				$readiness = self::display_score( (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) ) );
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
