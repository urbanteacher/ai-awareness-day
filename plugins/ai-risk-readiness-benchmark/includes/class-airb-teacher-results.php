<?php
/**
 * Teacher results — strengths-first output, champion pathway, school progress.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds score-aware teacher result payloads for the results screen.
 */
class AIRB_Teacher_Results {

	/** Readiness % below which a domain may appear as a focus opportunity. */
	private const FOCUS_MAX = 75;

	/** Minimum teacher responses at a school before whole-school benchmark unlocks. */
	private const SCHOOL_RESPONSE_THRESHOLD = 20;

	/**
	 * Build the full teacher results payload.
	 *
	 * @param array<string, mixed> $results Scored results.
	 * @param string               $school  Optional school name from submission.
	 * @param array<string, mixed> $config  Plugin config.
	 * @param array<int, array<string, string>> $gap_products Matched gap products (lower performers).
	 * @return array<string, mixed>
	 */
	public static function build( array $results, string $school, array $config, array $gap_products = array() ): array {
		$cfg       = AIRB_Defaults::teacher_result_config();
		$tier      = self::performance_tier( $results );
		$strengths = self::detect_strengths( $results, $cfg );
		$opps      = self::detect_opportunities( $results, $cfg );
		$champion  = self::champion_pathway( $results, $cfg, $tier );
		$progress  = self::school_progress( $school );
		$gap       = $champion ? null : self::format_gap_recommendations( $results, $gap_products, $cfg );

		return array(
			'performance_tier'     => $tier,
			'performance_headline' => (string) ( $cfg['headlines'][ $tier ] ?? '' ),
			'strengths'            => $strengths,
			'opportunities'        => $opps,
			'focus_areas'          => self::focus_areas_from_opportunities( $opps, $cfg ),
			'champion_pathway'     => $champion,
			'gap_pathway'          => $gap,
			'suggested_resources'  => (array) ( $cfg['suggested_resources'] ?? array() ),
			'benchmark_summary'    => self::benchmark_summary( $results, $cfg ),
			'school_contribution'  => (array) ( $cfg['school_contribution'] ?? array() ),
			'school_impact'        => (array) ( $cfg['school_impact'] ?? array() ),
			'school_progress'      => $progress,
			'future_offer'         => self::future_offer( $progress, $cfg ),
			'next_steps'           => self::next_steps( $results, $champion, $gap, $opps, $cfg, $progress ),
			'ui'                   => AIRB_Teacher_Copy::resolve_ui( $results, $cfg ),
			'peer_benchmark'       => self::peer_benchmark( $results, $cfg ),
			'bias_health'          => AIRB_Components::bias_health(
				$results,
				$cfg,
				array(
					'role'     => 'teacher',
					'subtitle' => __( 'Fairness · protected characteristics · equality duty', 'ai-risk-benchmark' ),
					'callout'  => __( 'You have not yet built a consistent habit of checking AI outputs for bias or unfairness before they reach pupils. This is both a safeguarding concern and an equality duty risk.', 'ai-risk-benchmark' ),
				)
			),
		);
	}

	/**
	 * Map opportunities to leader-style focus area rows for the results UI.
	 *
	 * @param array<int, array<string, mixed>> $opps Opportunities.
	 * @return array<int, array<string, mixed>>
	 */
	private static function focus_areas_from_opportunities( array $opps, array $cfg ): array {
		$out = array();
		foreach ( $opps as $opp ) {
			$slug  = (string) ( $opp['slug'] ?? '' );
			$pct   = (int) ( $opp['pct'] ?? 0 );
			$label = (string) ( $opp['label'] ?? '' );
			if ( 'bias_equality' === $slug ) {
				$label = __( 'Bias & equality', 'ai-risk-benchmark' );
			} elseif ( 'assessment_integrity' === $slug ) {
				$label = __( 'Assessment design', 'ai-risk-benchmark' );
			}
			$block = AIRB_Teacher_Copy::focus_block( $slug, $pct, $cfg );
			$out[] = array(
				'slug'          => $slug,
				'label'         => $label,
				'pct'           => $pct,
				'summary'       => (string) ( $block['summary'] ?? '' ),
				'likely_impact' => (array) ( $block['likely_impact'] ?? array() ),
				'actions'       => (array) ( $block['actions'] ?? array() ),
			);
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $results Results.
	 */
	private static function performance_tier( array $results ): string {
		return AIRB_Scoring::readiness_band( (int) ( $results['alignment_score'] ?? 0 ) );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg ): array {
		return AIRB_Teacher_Copy::strength_statements( $results, $cfg );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function detect_opportunities( array $results, array $cfg ): array {
		$domains  = (array) ( $results['domain_scores'] ?? array() );
		$copy     = (array) ( $cfg['opportunity_copy'] ?? array() );
		$scored   = array();

		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$focus_slug = (string) $slug;
			$label      = (string) ( $dom['label'] ?? $slug );
			if ( 'assessment_integrity' === $focus_slug ) {
				$label = __( 'Assessment design', 'ai-risk-benchmark' );
			}
			$block = AIRB_Teacher_Copy::focus_block( $focus_slug, $pct, $cfg );
			if ( empty( $block['summary'] ) ) {
				$topic = (array) ( $copy[ $focus_slug ] ?? array() );
				if ( ! $topic ) {
					continue;
				}
				$block['summary'] = (string) ( $topic['summary'] ?? '' );
			}
			if ( empty( $block['summary'] ) ) {
				continue;
			}
			$scored[] = array(
				'slug'    => $focus_slug,
				'label'   => $label,
				'pct'     => $pct,
				'summary' => (string) $block['summary'],
				'detail'  => '',
			);
		}

		if ( array_key_exists( 'bias_readiness', $results ) && null !== $results['bias_readiness'] ) {
			$bias_pct = (int) $results['bias_readiness'];
			if ( $bias_pct < self::FOCUS_MAX ) {
				$bias_slug  = 'bias_equality';
				$bias_label = __( 'Bias & equality', 'ai-risk-benchmark' );
				$already_added = false;
				foreach ( $scored as $item ) {
					if ( $bias_slug === (string) ( $item['slug'] ?? '' ) ) {
						$already_added = true;
						break;
					}
				}
				if ( ! $already_added ) {
					$block      = AIRB_Teacher_Copy::focus_block( $bias_slug, $bias_pct, $cfg );
					if ( empty( $block['summary'] ) ) {
						$block = AIRB_Teacher_Copy::focus_block( 'bias_awareness', $bias_pct, $cfg );
					}
					if ( ! empty( $block['summary'] ) ) {
						$scored[] = array(
							'slug'    => $bias_slug,
							'label'   => $bias_label,
							'pct'     => $bias_pct,
							'summary' => (string) $block['summary'],
							'detail'  => '',
						);
					}
				}
			}
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $a['pct'] ?? 0 ) <=> ( $b['pct'] ?? 0 );
			}
		);

		return array_slice( $scored, 0, 3 );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @param string               $tier    Performance tier.
	 * @return array<string, mixed>|null
	 */
	private static function champion_pathway( array $results, array $cfg, string $tier ): ?array {
		$pathway = (array) ( $cfg['champion_pathway'] ?? array() );
		$eligible = in_array( $tier, array( 'leading', 'strong' ), true )
			&& (int) ( $results['alignment_score'] ?? 0 ) >= 80;

		if ( ! $eligible ) {
			return null;
		}

		return array(
			'title'             => (string) ( $pathway['title'] ?? '' ),
			'intro'             => (string) ( $pathway['intro'] ?? '' ),
			'roles'             => (array) ( $pathway['roles'] ?? array() ),
			'resources_heading' => (string) ( $pathway['resources_heading'] ?? '' ),
			'next_step_label'   => (string) ( $pathway['next_step_label'] ?? __( 'Recommended next step', 'ai-risk-benchmark' ) ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<string, mixed>
	 */
	private static function benchmark_summary( array $results, array $cfg ): array {
		$ho_domain = (array) ( $results['domain_scores']['human_oversight'] ?? array() );
		$oversight = (int) round( (float) ( $ho_domain['readiness_percentage'] ?? 0 ) );
		if ( $oversight < 1 ) {
			$oversight = (int) ( $results['human_oversight_ratio'] ?? 0 );
		}

		return array(
			'title'   => (string) ( $cfg['benchmark_summary_title'] ?? __( 'Teacher Benchmark — score recap', 'ai-risk-benchmark' ) ),
			'metrics' => array(
				array(
					'label' => __( 'Readiness score', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['alignment_score'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'AI risk score', 'ai-risk-benchmark' ),
					'value' => (int) round( (float) ( $results['overall_risk_percentage'] ?? 0 ) ) . '%',
				),
				array(
					'label' => __( 'AI Dependency Index', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['dependency_index'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
					'value' => $oversight . '%',
				),
				array(
					'label' => __( 'Benchmark rating', 'ai-risk-benchmark' ),
					'value' => (string) ( $results['readiness_level_label'] ?? '' ),
				),
			),
		);
	}

	/**
	 * @param string $school School name.
	 * @return array<string, mixed>
	 */
	private static function school_progress( string $school ): array {
		$school   = trim( $school );
		$count    = 0;
		$has_school = '' !== $school;

		if ( $has_school ) {
			$count = AIRB_Database::count_submissions(
				array(
					'role'   => 'teacher',
					'school' => $school,
				)
			);
		}

		return array(
			'has_school'              => $has_school,
			'teacher_responses'       => $count,
			'threshold'               => self::SCHOOL_RESPONSE_THRESHOLD,
			'whole_school_available'  => $has_school && $count >= self::SCHOOL_RESPONSE_THRESHOLD,
			'responses_remaining'     => $has_school ? max( 0, self::SCHOOL_RESPONSE_THRESHOLD - $count ) : self::SCHOOL_RESPONSE_THRESHOLD,
		);
	}

	/**
	 * @param array<string, mixed> $progress School progress data.
	 * @param array<string, mixed> $cfg      Teacher config.
	 * @return array<string, string>|null
	 */
	private static function future_offer( array $progress, array $cfg ): ?array {
		$offer = (array) ( $cfg['future_offer'] ?? array() );
		if ( empty( $offer['body'] ) ) {
			return null;
		}

		if ( ! empty( $progress['whole_school_available'] ) ) {
			return array(
				'heading' => (string) ( $offer['heading_unlocked'] ?? '' ),
				'body'    => (string) ( $offer['body_unlocked'] ?? $offer['body'] ),
			);
		}

		return array(
			'heading' => (string) ( $offer['heading'] ?? '' ),
			'body'    => (string) ( $offer['body'] ?? '' ),
		);
	}

	/**
	 * Build priority recommendations from behavioural risk (dependency + privacy).
	 *
	 * @param array<string, mixed>              $results  Scored results.
	 * @param array<int, array<string, string>> $products Stage-2 fallback products.
	 * @return array<int, string>
	 */
	private static function gap_recommendation_items( array $results, array $products ): array {
		$items   = array();
		$dep     = (int) ( $results['dependency_index'] ?? 0 );
		$privacy = (int) round(
			(float) ( ( $results['domain_scores']['privacy']['readiness_percentage'] ?? 100 ) )
		);

		if ( $dep >= 40 ) {
			$items[] = __( 'Verify Before You Trust Framework', 'ai-risk-benchmark' );
		}
		if ( $privacy < 70 ) {
			$items[] = __( 'AI Data Protection Checklist', 'ai-risk-benchmark' );
		}

		if ( ! $items ) {
			foreach ( $products as $product ) {
				$name = (string) ( $product['product'] ?? '' );
				if ( $name ) {
					$items[] = $name;
				}
			}
		}

		return array_values( array_unique( array_filter( $items ) ) );
	}

	/**
	 * @param array<string, mixed>              $results  Scored results.
	 * @param array<int, array<string, string>> $products Stage-2 gap products.
	 * @param array<string, mixed>              $cfg      Teacher config.
	 * @return array<string, mixed>|null
	 */
	private static function format_gap_recommendations( array $results, array $products, array $cfg ): ?array {
		$items = self::gap_recommendation_items( $results, $products );
		if ( ! $items ) {
			return null;
		}
		$gap = (array) ( $cfg['gap_pathway'] ?? array() );
		return array(
			'label' => (string) ( $gap['next_step_label'] ?? __( 'Recommended next step', 'ai-risk-benchmark' ) ),
			'intro' => (string) ( $gap['intro'] ?? '' ),
			'items' => array_slice( $items, 0, 2 ),
		);
	}

	/**
	 * Primary action + resource links for the results action zone.
	 *
	 * @param array<string, mixed>|null     $champion   Champion pathway.
	 * @param array<string, mixed>|null     $gap        Gap pathway.
	 * @param array<int, array<string, mixed>> $opps    Opportunities.
	 * @param array<string, mixed>          $cfg        Config.
	 * @param array<string, mixed>          $progress   School progress.
	 * @return array<string, mixed>
	 */
	private static function next_steps( array $results, ?array $champion, ?array $gap, array $opps, array $cfg, array $progress ): array {
		unset( $champion, $gap, $opps );

		$hero         = AIRB_Teacher_Copy::cta_hero( $results, $cfg );
		$links        = AIRB_Defaults::results_timeline_read_links( 'teacher' );
		$progress     = (array) $progress;
		$threshold    = (int) ( $progress['threshold'] ?? self::SCHOOL_RESPONSE_THRESHOLD );
		$n            = (int) ( $progress['teacher_responses'] ?? 0 );
		$rollout_copy = AIRB_Teacher_Copy::rollout_copy( $progress, $cfg );
		$rollout_note = (string) ( $rollout_copy['note'] ?? '' );

		return array(
			'hero_heading'              => (string) ( $cfg['hero_next_step_heading'] ?? __( 'Your next step', 'ai-risk-benchmark' ) ),
			'hero_next_step_heading_short' => (string) ( $cfg['hero_next_step_heading_short'] ?? __( 'Your next step', 'ai-risk-benchmark' ) ),
			'hero'                      => $hero,
			'resource_links'            => $links,
			'help_support_heading'      => (string) ( $cfg['help_support_heading'] ?? __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ) ),
			'help_support_heading_short'=> (string) ( $cfg['help_support_heading_short'] ?? __( 'Read more & tips', 'ai-risk-benchmark' ) ),
			'rollout'                   => array(
				'title'        => __( 'Whole-school teacher benchmark', 'ai-risk-benchmark' ),
				'intro'        => (string) ( $rollout_copy['intro'] ?? ( $cfg['rollout_intro'] ?? '' ) ),
				'intro_short'  => (string) ( $cfg['rollout_intro_short'] ?? '' ),
				'rollout_note' => $rollout_note,
				'threshold'    => $threshold,
				'counts'       => array( 'teacher' => $n ),
				'total'        => $n,
				'unlocked'     => ! empty( $progress['whole_school_available'] ),
				'remaining'    => (int) ( $progress['responses_remaining'] ?? max( 0, $threshold - $n ) ),
				'rollout_cta'  => (string) ( $cfg['rollout_rollout_cta'] ?? __( 'Encourage colleagues to take the benchmark', 'ai-risk-benchmark' ) ),
				'locked_items' => (array) ( $cfg['rollout_locked_items'] ?? array(
					array(
						'label'     => __( 'Colleague responses', 'ai-risk-benchmark' ),
						'count_key' => 'teacher',
					),
				) ),
				'progress'     => $progress,
			),
		);
	}

	/**
	 * National teacher benchmark comparison (live stats or reference fallback).
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function peer_benchmark( array $results, array $cfg ): array {
		$score    = (int) ( $results['alignment_score'] ?? 0 );
		$stats    = AIRB_Database::get_benchmark_stats( 'teacher', $score );
		$fallback = (array) ( $cfg['peer_benchmark_fallback'] ?? array() );

		if ( $stats ) {
			$average = (int) ( $stats['average'] ?? $stats['averages']['alignment_score'] ?? 55 );
			$top     = (int) ( $stats['top_quartile'] ?? 79 );

			return array(
				'your_score'          => $score,
				'average_score'       => $average,
				'top_quartile'        => $top,
				'sample_size'         => (int) ( $stats['sample_size'] ?? 0 ),
				'percentile'          => isset( $stats['percentile'] ) ? (int) $stats['percentile'] : null,
				'is_estimated'        => false,
				'gap_vs_average'      => $average - $score,
				'gap_vs_top_quartile' => $top - $score,
			);
		}

		$average = (int) ( $fallback['average'] ?? 55 );
		$top     = (int) ( $fallback['top_quartile'] ?? 79 );

		return array(
			'your_score'          => $score,
			'average_score'       => $average,
			'top_quartile'        => $top,
			'sample_size'         => 0,
			'percentile'          => null,
			'is_estimated'        => true,
			'gap_vs_average'      => $average - $score,
			'gap_vs_top_quartile' => $top - $score,
		);
	}
}
