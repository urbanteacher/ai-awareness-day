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

	private const STRENGTH_MIN = 85;

	/**
	 * @param array<string, mixed> $results Scored results.
	 * @param array<string, mixed> $config  Plugin config.
	 * @return array<string, mixed>
	 */
	public static function build( array $results, array $config ): array {
		$cfg       = AIRB_Defaults::support_result_config();
		$tier      = AIRB_Scoring::readiness_band( (int) ( $results['alignment_score'] ?? 0 ) );
		$strengths = self::detect_strengths( $results, $cfg );
		$opps      = self::detect_opportunities( $results, $cfg );
		$gap       = self::format_gap_recommendations( $results, $cfg );

		$ho_domain = (array) ( $results['domain_scores']['human_oversight'] ?? array() );
		$ho_pct    = (int) round( (float) ( $ho_domain['readiness_percentage'] ?? 0 ) );
		if ( $ho_pct < 1 ) {
			$ho_pct = (int) ( $results['human_oversight_ratio'] ?? 0 );
		}

		$data_protection = (int) round(
			(float) ( $results['domain_scores']['privacy']['readiness_percentage'] ?? 0 )
		);

		return array(
			'performance_tier'              => $tier,
			'performance_headline'          => (string) ( $cfg['headlines'][ $tier ] ?? '' ),
			'strengths'                     => $strengths,
			'opportunities'                 => $opps,
			'priority_focus'                => array_slice(
				array_map(
					static function ( $opp ) {
						return (string) ( $opp['label'] ?? '' );
					},
					$opps
				),
				0,
				3
			),
			'gap_pathway'                   => $gap,
			'operational_dependency_index'  => (int) ( $results['dependency_index'] ?? 0 ),
			'human_oversight_ratio'         => $ho_pct,
			'data_protection_readiness'     => $data_protection,
			'benchmark_summary'             => self::benchmark_summary( $results, $cfg, $ho_pct, $data_protection ),
			'suggested_resources'           => AIRB_Defaults::support_suggested_resources(),
			'next_steps'                    => self::next_steps( $gap, $opps, $cfg ),
			'share_hint'                    => (string) ( $cfg['share_hint'] ?? '' ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg ): array {
		$labels  = (array) ( $cfg['strength_labels'] ?? array() );
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$out     = array();

		foreach ( array( 'privacy', 'human_oversight', 'safe_adoption', 'ai_literacy' ) as $slug ) {
			$pct = (int) round( (float) ( $domains[ $slug ]['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::STRENGTH_MIN && ! empty( $labels[ $slug ] ) ) {
				$out[] = (string) $labels[ $slug ];
			}
		}
		if ( (int) ( $results['dependency_index'] ?? 100 ) <= 35 && ! empty( $labels['low_dependency'] ) ) {
			$out[] = (string) $labels['low_dependency'];
		}

		return array_values( array_filter( $out ) );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function detect_opportunities( array $results, array $cfg ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$copy    = (array) ( $cfg['opportunity_copy'] ?? array() );
		$scored  = array();

		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::STRENGTH_MIN ) {
				continue;
			}
			$topic = (array) ( $copy[ $slug ] ?? array() );
			if ( ! $topic ) {
				continue;
			}
			$scored[] = array(
				'slug'    => (string) $slug,
				'label'   => (string) ( $topic['focus_label'] ?? $dom['label'] ?? $slug ),
				'pct'     => $pct,
				'summary' => (string) ( $topic['summary'] ?? '' ),
				'detail'  => (string) ( $topic['detail'] ?? '' ),
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
	 * @param array<string, mixed>|null     $gap  Gap pathway.
	 * @param array<int, array<string, mixed>> $opps Opportunities.
	 * @param array<string, mixed>          $cfg  Config.
	 * @return array<string, mixed>
	 */
	private static function next_steps( ?array $gap, array $opps, array $cfg ): array {
		$resources = AIRB_Defaults::support_suggested_resources();
		if ( $gap && ! empty( $gap['items'] ) ) {
			$hero = array(
				'key'              => 'support_data_checklist',
				'title'            => (string) ( $gap['items'][0] ?? __( 'Strengthen your AI practice', 'ai-risk-benchmark' ) ),
				'body'             => (string) ( $gap['intro'] ?? '' ),
				'understand_items' => (array) ( $gap['items'] ?? array() ),
				'cta_text'         => __( 'Request support', 'ai-risk-benchmark' ),
			);
		} else {
			$first = $opps[0] ?? array();
			$hero  = array(
				'key'              => 'support_data_checklist',
				'title'            => (string) ( $first['label'] ?? __( 'Build your AI practice', 'ai-risk-benchmark' ) ),
				'body'             => (string) ( $first['summary'] ?? __( 'Focus on the areas below to reduce operational and data risk.', 'ai-risk-benchmark' ) ),
				'understand_items' => array(),
				'cta_text'         => __( 'Request support', 'ai-risk-benchmark' ),
			);
		}

		return array(
			'hero_heading'   => (string) ( $cfg['hero_next_step_heading'] ?? __( 'Your next step', 'ai-risk-benchmark' ) ),
			'hero'           => $hero,
			'resource_links' => AIRB_Defaults::results_timeline_read_links( 'support_staff' ),
			'hub_resources'  => $resources,
			'hub_heading'    => __( 'Useful resources', 'ai-risk-benchmark' ),
			'timeline_heading' => __( 'Further reading and support articles', 'ai-risk-benchmark' ),
		);
	}
}
