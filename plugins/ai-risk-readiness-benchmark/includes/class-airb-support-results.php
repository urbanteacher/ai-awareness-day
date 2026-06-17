<?php
/**
 * Education support staff results — operations-focused metrics and pathways.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds support-staff-facing result payloads.
 */
class AIRB_Support_Results {

	private const FOCUS_MAX       = 75;
	private const FOCUS_AREA_MAX  = 4;

	/**
	 * @param array<string, mixed> $results Scored results.
	 * @param array<string, mixed> $config  Plugin config.
	 * @param string               $school  Optional school name from submission.
	 * @param array<string, mixed> $answers Answer map.
	 * @return array<string, mixed>
	 */
	public static function build( array $results, array $config, string $school = '', array $answers = array() ): array {
		$cfg            = AIRB_Defaults::support_result_config();
		$tier           = AIRB_Scoring::readiness_band( (int) ( $results['alignment_score'] ?? 0 ) );
		$strengths      = self::detect_strengths( $results, $cfg );
		$opps           = self::detect_opportunities( $results, $cfg );
		$gap            = self::format_gap_recommendations( $results, $cfg );
		$progress       = AIRB_Support_Copy::school_progress( $school );
		$domain_rows    = self::domain_rows( $results, $cfg );
		$strength_items = AIRB_Support_Copy::strength_statements( $results, $cfg );
		$focus_areas    = self::focus_areas_from_opportunities( $opps, $cfg, $answers, $config );

		$ho_domain = (array) ( $results['domain_scores']['human_oversight'] ?? array() );
		$ho_pct    = (int) round( (float) ( $ho_domain['readiness_percentage'] ?? 0 ) );
		if ( $ho_pct < 1 ) {
			$ho_pct = (int) ( $results['human_oversight_ratio'] ?? 0 );
		}

		$data_protection = (int) round(
			(float) ( $results['domain_scores']['privacy']['readiness_percentage'] ?? 0 )
		);

		return array(
			'performance_tier'             => $tier,
			'performance_headline'         => (string) ( $cfg['headlines'][ $tier ] ?? '' ),
			'ui'                           => AIRB_Support_Copy::resolve_ui( $results, $cfg ),
			'metric_signals'               => AIRB_Support_Copy::metric_signals( $results, $cfg ),
			'domain_rows'                  => $domain_rows,
			'strengths'                    => $strengths,
			'strength_items'               => $strength_items,
			'opportunities'                => $opps,
			'focus_areas'                  => $focus_areas,
			'priority_focus'               => array_slice(
				array_map(
					static function ( $area ) {
						return (string) ( $area['label'] ?? '' );
					},
					$focus_areas
				),
				0,
				self::FOCUS_AREA_MAX
			),
			'gap_pathway'                  => $gap,
			'operational_dependency_index' => (int) ( $results['dependency_index'] ?? 0 ),
			'human_oversight_ratio'        => $ho_pct,
			'data_protection_readiness'    => $data_protection,
			'role_specific_risk'           => self::role_specific_risk( $results ),
			'school_progress'              => $progress,
			'benchmark_summary'            => self::benchmark_summary( $results, $cfg, $ho_pct, $data_protection ),
			'suggested_resources'          => AIRB_Defaults::support_suggested_resources(),
			'next_steps'                   => self::next_steps( $gap, $opps, $cfg, $progress ),
			'share_hint'                   => (string) ( $cfg['share_hint'] ?? '' ),
		);
	}

	/**
	 * Role-specific risk — highest exposure among privacy and safeguarding.
	 *
	 * @param array<string, mixed> $results Results.
	 */
	public static function role_specific_risk( array $results ): int {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$display = (array) ( $results['support_display_domains'] ?? array() );
		$risks   = array();

		if ( isset( $domains['privacy'] ) ) {
			$risks[] = (int) round( (float) ( $domains['privacy']['risk_percentage'] ?? 0 ) );
		}
		if ( isset( $display['safeguarding'] ) ) {
			$risks[] = (int) round( (float) ( $display['safeguarding']['risk_percentage'] ?? 0 ) );
		}

		if ( ! $risks ) {
			return (int) round( (float) ( $results['overall_risk_percentage'] ?? 0 ) );
		}

		return max( $risks );
	}

	/**
	 * Domain readiness rows for the results screen.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function domain_rows( array $results, array $cfg ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$display = (array) ( $results['support_display_domains'] ?? array() );
		$defs    = (array) ( $cfg['domain_rows'] ?? array() );
		$rows    = array();

		foreach ( $defs as $def ) {
			if ( ! is_array( $def ) ) {
				continue;
			}
			$slug   = (string) ( $def['slug'] ?? '' );
			$source = (string) ( $def['source'] ?? 'domain' );
			$dom    = 'support_display' === $source
				? (array) ( $display[ $slug ] ?? array() )
				: (array) ( $domains[ $slug ] ?? array() );

			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}

			$pct   = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			$badge = AIRB_Support_Copy::domain_badge( $pct );
			$rows[] = array(
				'slug'  => $slug,
				'label' => (string) ( $def['label'] ?? $dom['label'] ?? $slug ),
				'pct'   => $pct,
				'badge' => $badge,
			);
		}

		return $rows;
	}

	/**
	 * @param array<int, array<string, mixed>> $opps Opportunities.
	 * @param array<string, mixed>             $cfg  Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function focus_areas_from_opportunities( array $opps, array $cfg, array $answers = array(), array $config = array() ): array {
		$out = array();
		foreach ( array_slice( $opps, 0, self::FOCUS_AREA_MAX ) as $opp ) {
			$slug  = (string) ( $opp['focus_slug'] ?? $opp['slug'] ?? '' );
			$pct   = (int) ( $opp['pct'] ?? 0 );
			$block = AIRB_Support_Copy::focus_block( $opp, $cfg );
			$block = AIRB_Results_Guidance::enrich_focus_block( $block, $pct, 'support_staff', array( $slug ), $answers, $config );
			$impact = (array) ( $block['likely_impact'] ?? $block['challenge_bullets'] ?? array() );
			$badge = AIRB_Support_Copy::domain_badge( $pct );
			$out[] = array(
				'slug'              => $slug,
				'label'             => (string) ( $opp['label'] ?? '' ),
				'pct'               => $pct,
				'summary'           => (string) ( $block['summary'] ?? '' ),
				'challenge_heading' => (string) ( $block['challenge_heading'] ?? '' ),
				'challenge_bullets' => (array) ( $block['challenge_bullets'] ?? array() ),
				'likely_impact'     => $impact,
				'actions'           => (array) ( $block['actions'] ?? array() ),
				'severity'          => (string) ( $block['severity'] ?? 'moderate' ),
				'badge_text'        => AIRB_Support_Copy::focus_badge_text( $pct, $badge ),
			);
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg ): array {
		$items = AIRB_Support_Copy::strength_statements( $results, $cfg );
		return array_values(
			array_filter(
				array_map(
					static function ( $item ) {
						return (string) ( $item['title'] ?? '' );
					},
					$items
				)
			)
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function detect_opportunities( array $results, array $cfg ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$display = (array) ( $results['support_display_domains'] ?? array() );
		$copy    = (array) ( $cfg['opportunity_copy'] ?? array() );
		$scored  = array();

		$candidates = array(
			array( 'slug' => 'safeguarding', 'dom' => (array) ( $display['safeguarding'] ?? array() ), 'focus_slug' => 'safeguarding' ),
			array( 'slug' => 'privacy', 'dom' => (array) ( $domains['privacy'] ?? array() ), 'focus_slug' => 'privacy' ),
			array( 'slug' => 'human_oversight', 'dom' => (array) ( $domains['human_oversight'] ?? array() ), 'focus_slug' => 'human_oversight' ),
			array( 'slug' => 'safe_adoption', 'dom' => (array) ( $domains['safe_adoption'] ?? array() ), 'focus_slug' => 'safe_adoption' ),
			array( 'slug' => 'ai_literacy', 'dom' => (array) ( $domains['ai_literacy'] ?? array() ), 'focus_slug' => 'ai_literacy' ),
		);

		foreach ( $candidates as $row ) {
			$slug = (string) ( $row['slug'] ?? '' );
			$dom  = (array) ( $row['dom'] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$topic = (array) ( $copy[ $slug ] ?? array() );
			$focus = (string) ( $row['focus_slug'] ?? $slug );
			$scored[] = array(
				'slug'       => $slug,
				'focus_slug' => $focus,
				'label'      => (string) ( $topic['focus_label'] ?? $dom['label'] ?? $slug ),
				'pct'        => $pct,
				'summary'    => (string) ( $topic['summary'] ?? '' ),
				'detail'     => (string) ( $topic['detail'] ?? '' ),
			);
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $a['pct'] ?? 0 ) <=> ( $b['pct'] ?? 0 );
			}
		);

		return $scored;
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, string>
	 */
	private static function gap_recommendation_items( array $results, array $cfg ): array {
		$items   = array();
		$privacy = (int) round( (float) ( $results['domain_scores']['privacy']['readiness_percentage'] ?? 100 ) );
		$ho      = (int) round( (float) ( $results['domain_scores']['human_oversight']['readiness_percentage'] ?? 100 ) );
		$safe    = (int) round( (float) ( $results['domain_scores']['safe_adoption']['readiness_percentage'] ?? 100 ) );

		if ( $privacy < 70 ) {
			$items[] = __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' );
		}
		if ( $ho < 70 ) {
			$items[] = __( 'Verify Before You Trust Framework', 'ai-risk-benchmark' );
		}
		if ( $safe < 70 ) {
			$items[] = __( 'AI Privacy Guide for Schools', 'ai-risk-benchmark' );
		}

		if ( ! $items ) {
			foreach ( (array) ( $cfg['default_gap_items'] ?? array() ) as $item ) {
				$items[] = (string) $item;
			}
		}

		return array_values( array_unique( array_filter( $items ) ) );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>|null
	 */
	private static function format_gap_recommendations( array $results, array $cfg ): ?array {
		$items = self::gap_recommendation_items( $results, $cfg );
		if ( ! $items ) {
			return null;
		}
		$gap = (array) ( $cfg['gap_pathway'] ?? array() );
		return array(
			'label' => (string) ( $gap['next_step_label'] ?? __( 'Recommended next step', 'ai-risk-benchmark' ) ),
			'intro' => (string) ( $gap['intro'] ?? '' ),
			'items' => array_slice( $items, 0, 3 ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @param int                  $ho_pct  Human oversight readiness.
	 * @param int                  $dp_pct  Data protection readiness.
	 * @return array<string, mixed>
	 */
	private static function benchmark_summary( array $results, array $cfg, int $ho_pct, int $dp_pct ): array {
		return array(
			'title'   => (string) ( $cfg['benchmark_summary_title'] ?? __( 'Support staff benchmark — score recap', 'ai-risk-benchmark' ) ),
			'metrics' => array(
				array(
					'label' => __( 'Readiness score', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['alignment_score'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'Operational Dependency Index', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['dependency_index'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
					'value' => $ho_pct . '%',
				),
				array(
					'label' => __( 'Data Protection Readiness', 'ai-risk-benchmark' ),
					'value' => $dp_pct . '%',
				),
				array(
					'label' => __( 'Benchmark rating', 'ai-risk-benchmark' ),
					'value' => (string) ( $results['readiness_level_label'] ?? '' ),
				),
			),
		);
	}

	/**
	 * @param array<string, mixed>|null        $gap      Gap pathway.
	 * @param array<int, array<string, mixed>>   $opps     Opportunities.
	 * @param array<string, mixed>               $cfg      Config.
	 * @param array<string, mixed>               $progress School progress.
	 * @return array<string, mixed>
	 */
	private static function next_steps( ?array $gap, array $opps, array $cfg, array $progress ): array {
		unset( $gap, $opps );

		$hero      = (array) ( $cfg['cta_hero'] ?? array() );
		$threshold = (int) ( $progress['threshold'] ?? 20 );
		$support_n = (int) ( $progress['support_responses'] ?? 0 );
		$teacher_n = (int) ( $progress['teacher_responses'] ?? 0 );

		return array(
			'hero_heading'                 => (string) ( $cfg['hero_next_step_heading'] ?? __( 'Recommended next step', 'ai-risk-benchmark' ) ),
			'hero'                         => $hero,
			'resource_links'               => AIRB_Defaults::results_timeline_read_links( 'support_staff' ),
			'hub_resources'                => AIRB_Defaults::support_suggested_resources(),
			'hub_heading'                  => __( 'Useful resources', 'ai-risk-benchmark' ),
			'timeline_heading'             => __( 'Further reading and support articles', 'ai-risk-benchmark' ),
			'help_support_heading'         => (string) ( $cfg['help_support_heading'] ?? __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ) ),
			'help_support_heading_short'   => (string) ( $cfg['help_support_heading_short'] ?? __( 'Read more & tips', 'ai-risk-benchmark' ) ),
			'rollout'                      => array(
				'intro'        => (string) ( $cfg['rollout_intro'] ?? '' ),
				'intro_short'  => (string) ( $cfg['rollout_intro_short'] ?? '' ),
				'threshold'    => $threshold,
				'counts'       => array(
					'support' => $support_n,
					'teacher' => $teacher_n,
				),
				'total'        => $support_n,
				'unlocked'     => ! empty( $progress['whole_school_available'] ),
				'remaining'    => max( 0, $threshold - $support_n ),
				'rollout_cta'  => (string) ( $cfg['rollout_rollout_cta'] ?? __( 'Encourage colleagues to take the benchmark', 'ai-risk-benchmark' ) ),
				'locked_items' => (array) ( $cfg['rollout_locked_items'] ?? array() ),
				'progress'     => $progress,
			),
		);
	}
}
