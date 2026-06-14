<?php
/**
 * Leader results — governance benchmark with executive summary and commercial next steps.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds school-leader-facing result payloads for the results screen.
 */
class AIRB_Leader_Results {

	private const STRENGTH_MIN          = 70;
	private const FOCUS_MAX             = 70;
	private const SCHOOL_TOTAL_THRESHOLD = 20;

	/**
	 * Build the full leader results payload.
	 *
	 * @param array<string, mixed> $results          Scored results.
	 * @param string               $school           School name from submission.
	 * @param array<string, string> $profile         School profile (phase, org_type).
	 * @param array<string, mixed> $config           Plugin config.
	 * @param array<string, mixed>|null $policy_support AI policy support offer (DfE template).
	 * @param array<string, mixed>|null $aad_promo   AI Awareness Day promo.
	 * @return array<string, mixed>
	 */
	public static function build(
		array $results,
		string $school,
		array $profile,
		array $config,
		?array $policy_support = null,
		?array $aad_promo = null
	): array {
		$cfg       = AIRB_Defaults::leader_result_config();
		$maturity  = self::maturity( $results, $cfg );
		$strengths = self::detect_strengths( $results, $cfg );
		$attention = self::detect_attention_areas( $results, $cfg );
		$focus     = self::focus_areas( $results, $cfg );
		$peer      = self::peer_benchmark( $results, $profile, $cfg );
		$progress  = self::school_rollout_counts( $school );

		return array(
			'executive_summary' => self::executive_summary( $results, $cfg, $maturity, $strengths, $attention, $focus ),
			'maturity'          => $maturity,
			'peer_benchmark'    => $peer,
			'focus_areas'       => $focus,
			'risk_heatmap'      => (array) ( $results['risk_heatmap'] ?? array() ),
			'next_steps'        => self::next_steps( $results, $config, $profile, $progress, $policy_support, $aad_promo, $cfg ),
			'school_rollout'    => $progress,
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 */
	private static function maturity( array $results, array $cfg ): array {
		$score  = (int) ( $results['alignment_score'] ?? 0 );
		$levels = (array) ( $cfg['maturity_levels'] ?? array() );
		$slug   = 'emerging';
		$label  = __( 'Emerging', 'ai-risk-benchmark' );

		foreach ( $levels as $level ) {
			$min = (int) ( $level['min'] ?? 0 );
			$max = (int) ( $level['max'] ?? 100 );
			if ( $score >= $min && $score <= $max ) {
				$slug  = (string) ( $level['slug'] ?? $slug );
				$label = (string) ( $level['label'] ?? $label );
				break;
			}
		}

		$descriptions = (array) ( $cfg['maturity_descriptions'] ?? array() );

		return array(
			'slug'        => $slug,
			'label'       => $label,
			'score'       => $score,
			'description' => (string) ( $descriptions[ $slug ] ?? '' ),
		);
	}

	/**
	 * @param array<string, mixed>             $results   Results.
	 * @param array<string, mixed>             $cfg       Config.
	 * @param array<string, mixed>             $maturity  Maturity block.
	 * @param array<int, string>               $strengths Strength labels.
	 * @param array<int, string>               $attention  Attention labels.
	 * @param array<int, array<string, mixed>> $focus     Focus areas.
	 * @return array<string, mixed>
	 */
	private static function executive_summary(
		array $results,
		array $cfg,
		array $maturity,
		array $strengths,
		array $attention,
		array $focus
	): array {
		$intros = (array) ( $cfg['executive_intros'] ?? array() );
		$intro  = (string) ( $intros[ $maturity['slug'] ?? 'established' ] ?? $intros['established'] ?? '' );

		$priority = (string) ( $cfg['default_priority_action'] ?? '' );
		if ( $focus ) {
			$first = $focus[0];
			$actions = (array) ( $cfg['priority_actions'] ?? array() );
			$slug    = (string) ( $first['slug'] ?? '' );
			if ( isset( $actions[ $slug ] ) ) {
				$priority = (string) $actions[ $slug ];
			}
		}

		return array(
			'title'            => (string) ( $cfg['executive_title'] ?? __( 'Executive Summary', 'ai-risk-benchmark' ) ),
			'intro'            => $intro,
			'strengths'        => $strengths,
			'attention_areas'  => $attention,
			'alignment_score'  => (int) ( $results['alignment_score'] ?? 0 ),
			'risk_level_label' => (string) ( $results['risk_level_label'] ?? '' ),
			'priority_action'  => $priority,
			'strengths_heading' => (string) ( $cfg['strengths_heading'] ?? __( 'Strengths include', 'ai-risk-benchmark' ) ),
			'attention_heading' => (string) ( $cfg['attention_heading'] ?? __( 'Areas requiring attention', 'ai-risk-benchmark' ) ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg ): array {
		$labels  = (array) ( $cfg['domain_labels'] ?? array() );
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$scored  = array();

		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::STRENGTH_MIN ) {
				$scored[] = array(
					'pct'   => $pct,
					'label' => (string) ( $labels[ $slug ] ?? $dom['label'] ?? $slug ),
				);
			}
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $b['pct'] ?? 0 ) <=> ( $a['pct'] ?? 0 );
			}
		);

		return array_values(
			array_map(
				static function ( $row ) {
					return (string) ( $row['label'] ?? '' );
				},
				array_slice( $scored, 0, 5 )
			)
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, string>
	 */
	private static function detect_attention_areas( array $results, array $cfg ): array {
		$labels  = (array) ( $cfg['domain_labels'] ?? array() );
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$scored  = array();

		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct < self::FOCUS_MAX ) {
				$scored[] = array(
					'pct'   => $pct,
					'label' => (string) ( $labels[ $slug ] ?? $dom['label'] ?? $slug ),
				);
			}
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $a['pct'] ?? 0 ) <=> ( $b['pct'] ?? 0 );
			}
		);

		return array_values(
			array_map(
				static function ( $row ) {
					return (string) ( $row['label'] ?? '' );
				},
				array_slice( $scored, 0, 5 )
			)
		);
	}

	/**
	 * Governance-focused focus areas with recommended actions.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	private static function focus_areas( array $results, array $cfg ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$copy    = (array) ( $cfg['focus_copy'] ?? array() );
		$labels  = (array) ( $cfg['domain_labels'] ?? array() );
		$scored  = array();

		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			if ( $pct >= self::FOCUS_MAX ) {
				continue;
			}
			$topic = (array) ( $copy[ $slug ] ?? array() );
			if ( ! $topic ) {
				continue;
			}
			$scored[] = array(
				'slug'    => (string) $slug,
				'label'   => (string) ( $labels[ $slug ] ?? $dom['label'] ?? $slug ),
				'pct'     => $pct,
				'summary' => (string) ( $topic['summary'] ?? '' ),
				'actions' => (array) ( $topic['actions'] ?? array() ),
			);
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $a['pct'] ?? 0 ) <=> ( $b['pct'] ?? 0 );
			}
		);

		return array_slice( $scored, 0, 4 );
	}

	/**
	 * Peer benchmark comparison by school phase.
	 *
	 * @param array<string, mixed>  $results Results.
	 * @param array<string, string> $profile Profile.
	 * @param array<string, mixed>  $cfg     Config.
	 * @return array<string, mixed>
	 */
	private static function peer_benchmark( array $results, array $profile, array $cfg ): array {
		$score  = (int) ( $results['alignment_score'] ?? 0 );
		$phase  = sanitize_key( (string) ( $profile['school_phase'] ?? '' ) );
		$stats  = AIRB_Database::get_peer_benchmark_stats( 'leader', $score, $phase );
		$labels = (array) ( $cfg['peer_phase_labels'] ?? array() );
		$phase_label = (string) ( $labels[ $phase ] ?? __( 'Similar Schools', 'ai-risk-benchmark' ) );

		if ( $stats ) {
			return array(
				'your_score'      => $score,
				'phase_label'     => $phase_label,
				'average_score'   => (int) ( $stats['average'] ?? 0 ),
				'top_quartile'    => (int) ( $stats['top_quartile'] ?? 0 ),
				'sample_size'     => (int) ( $stats['sample_size'] ?? 0 ),
				'percentile'      => isset( $stats['percentile'] ) ? (int) $stats['percentile'] : null,
				'is_estimated'    => ! empty( $stats['is_estimated'] ),
			);
		}

		$fallbacks = (array) ( $cfg['peer_benchmark_fallback'] ?? array() );
		$fallback  = (array) ( $fallbacks[ $phase ] ?? $fallbacks['default'] ?? array() );

		return array(
			'your_score'    => $score,
			'phase_label'   => $phase_label,
			'average_score' => (int) ( $fallback['average'] ?? 62 ),
			'top_quartile'  => (int) ( $fallback['top_quartile'] ?? 84 ),
			'sample_size'   => 0,
			'percentile'    => null,
			'is_estimated'  => true,
		);
	}

	/**
	 * Whole-school rollout progress by stakeholder role.
	 *
	 * @param string $school School name.
	 * @return array<string, mixed>
	 */
	public static function school_rollout_counts( string $school ): array {
		$school     = trim( $school );
		$has_school = '' !== $school;
		$counts     = array(
			'leader'  => 0,
			'teacher' => 0,
			'student' => 0,
			'parent'  => 0,
		);

		if ( $has_school ) {
			$counts = AIRB_Database::count_school_responses_by_role( $school );
		}

		$total = array_sum( $counts );

		return array(
			'has_school'   => $has_school,
			'counts'       => $counts,
			'total'        => $total,
			'threshold'    => self::SCHOOL_TOTAL_THRESHOLD,
			'is_unlocked'  => $has_school && $total >= self::SCHOOL_TOTAL_THRESHOLD,
			'remaining'    => $has_school ? max( 0, self::SCHOOL_TOTAL_THRESHOLD - $total ) : self::SCHOOL_TOTAL_THRESHOLD,
		);
	}

	/**
	 * Commercial next steps for school leaders.
	 *
	 * @param array<string, mixed>      $results     Results.
	 * @param array<string, mixed>      $config      Config.
	 * @param array<string, string>     $profile     Profile.
	 * @param array<string, mixed>      $progress    School rollout.
	 * @param array<string, mixed>|null $policy_support AI policy support offer (DfE template).
	 * @param array<string, mixed>|null $aad_promo   AAD offer.
	 * @param array<string, mixed>      $cfg         Leader config.
	 * @return array<string, mixed>
	 */
	private static function next_steps(
		array $results,
		array $config,
		array $profile,
		array $progress,
		?array $policy_support,
		?array $aad_promo,
		array $cfg
	): array {
		$cards   = array();
		$blocks  = (array) ( $cfg['next_step_blocks'] ?? array() );
		$services = (array) ( $config['services']['items'] ?? array() );
		$policy_url = AIRB_Defaults::dfe_url_using_ai();
		foreach ( $services as $item ) {
			if ( false !== stripos( (string) ( $item['label'] ?? '' ), 'AI Policy' ) ) {
				$policy_url = (string) ( $item['url'] ?? $policy_url );
				break;
			}
		}

		$policy_block = (array) ( $blocks['policy_support'] ?? array() );
		$cards[] = array(
			'key'      => 'policy_support',
			'title'    => (string) ( $policy_support['title'] ?? $policy_block['title'] ?? __( 'Develop your AI policy', 'ai-risk-benchmark' ) ),
			'body'     => (string) ( $policy_block['body'] ?? __( 'Adapt the official DfE AI policy template to your school. Contact the AI Awareness Day team if you need support.', 'ai-risk-benchmark' ) ),
			'cta_text' => (string) ( $policy_support['cta_text'] ?? $policy_block['cta_text'] ?? __( 'View DfE AI policy template', 'ai-risk-benchmark' ) ),
			'cta_url'  => (string) ( $policy_support['cta_url'] ?? $policy_url ),
		);

		$benchmark_block = (array) ( $blocks['whole_school_benchmark'] ?? array() );
		$cards[] = array(
			'key'      => 'whole_school_benchmark',
			'title'    => (string) ( $benchmark_block['title'] ?? __( 'Whole-School AI Benchmark', 'ai-risk-benchmark' ) ),
			'body'     => (string) ( $benchmark_block['body'] ?? __( 'Invite teachers, students and parents to complete the benchmark to build a complete risk profile.', 'ai-risk-benchmark' ) ),
			'counts'   => (array) ( $progress['counts'] ?? array() ),
			'threshold'=> (int) ( $progress['threshold'] ?? self::SCHOOL_TOTAL_THRESHOLD ),
			'unlocked' => ! empty( $progress['is_unlocked'] ),
			'remaining'=> (int) ( $progress['remaining'] ?? self::SCHOOL_TOTAL_THRESHOLD ),
		);

		$aad_block = (array) ( $blocks['ai_awareness_day'] ?? array() );
		$aad_cfg   = (array) ( $config['aad_2027'] ?? array() );
		$cards[] = array(
			'key'      => 'ai_awareness_day',
			'title'    => (string) ( $aad_promo['title'] ?? $aad_block['title'] ?? __( 'AI Awareness Day', 'ai-risk-benchmark' ) ),
			'body'     => (string) ( $aad_block['intro'] ?? __( 'A one-day programme for leaders, staff, students and parents.', 'ai-risk-benchmark' ) ),
			'topics'   => (array) ( $aad_block['topics'] ?? array() ),
			'cta_text' => (string) ( $aad_promo['cta_text'] ?? $aad_block['cta_text'] ?? __( 'Plan AI Awareness Day', 'ai-risk-benchmark' ) ),
			'cta_url'  => (string) ( $aad_promo['cta_url'] ?? $aad_block['cta_url'] ?? AIRB_Defaults::contact_page_url() ),
		);

		$gov_block = (array) ( $blocks['governance_review'] ?? array() );
		$cards[] = array(
			'key'       => 'governance_review',
			'title'     => (string) ( $gov_block['title'] ?? __( 'Governance Review', 'ai-risk-benchmark' ) ),
			'body'      => (string) ( $gov_block['body'] ?? __( 'Receive a detailed report with policy recommendations and a risk heat map.', 'ai-risk-benchmark' ) ),
			'deliverables' => (array) ( $gov_block['deliverables'] ?? array() ),
			'cta_text'  => (string) ( $gov_block['cta_text'] ?? __( 'Request Governance Review', 'ai-risk-benchmark' ) ),
			'cta_url'   => AIRB_Defaults::contact_page_url(),
		);

		return array(
			'title'    => (string) ( $cfg['next_steps_title'] ?? __( 'Next steps', 'ai-risk-benchmark' ) ),
			'subtitle' => (string) ( $cfg['next_steps_subtitle'] ?? __( 'Recommended for your school', 'ai-risk-benchmark' ) ),
			'intro'    => (string) ( $cfg['next_steps_intro'] ?? __( 'Based on your benchmark results:', 'ai-risk-benchmark' ) ),
			'cards'    => $cards,
		);
	}
}
