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

	/** Readiness % at or above which a domain counts as a strength. */
	private const STRENGTH_MIN = 85;

	/** Dependency index at or below which low-dependency is a strength. */
	private const DEPENDENCY_STRENGTH_MAX = 25;

	/** Alignment at or above which DfE alignment is listed as a strength. */
	private const ALIGNMENT_STRENGTH_MIN = 85;

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
		$gap       = $champion ? null : self::format_gap_products( $gap_products, $cfg );

		return array(
			'performance_tier'     => $tier,
			'performance_headline' => (string) ( $cfg['headlines'][ $tier ] ?? '' ),
			'strengths'            => $strengths,
			'opportunities'        => $opps,
			'champion_pathway'     => $champion,
			'gap_pathway'          => $gap,
			'suggested_resources'  => (array) ( $cfg['suggested_resources'] ?? array() ),
			'benchmark_summary'    => self::benchmark_summary( $results, $cfg ),
			'school_contribution'  => (array) ( $cfg['school_contribution'] ?? array() ),
			'school_impact'        => (array) ( $cfg['school_impact'] ?? array() ),
			'school_progress'      => $progress,
			'future_offer'         => self::future_offer( $progress, $cfg ),
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 */
	private static function performance_tier( array $results ): string {
		$alignment = (int) ( $results['alignment_score'] ?? 0 );
		$risk      = (float) ( $results['overall_risk_percentage'] ?? 100 );

		if ( $alignment >= 75 && $risk <= 30 ) {
			return 'strong';
		}
		if ( $alignment >= 60 ) {
			return 'established';
		}
		if ( $alignment >= 45 ) {
			return 'developing';
		}
		return 'emerging';
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<int, string>
	 */
	private static function detect_strengths( array $results, array $cfg ): array {
		$labels   = (array) ( $cfg['strength_labels'] ?? array() );
		$domains  = (array) ( $results['domain_scores'] ?? array() );
		$out      = array();

		if ( (int) ( $domains['ai_literacy']['readiness_percentage'] ?? 0 ) >= self::STRENGTH_MIN ) {
			$out[] = (string) ( $labels['ai_literacy'] ?? '' );
		}
		if ( (int) ( $results['dependency_index'] ?? 100 ) <= self::DEPENDENCY_STRENGTH_MAX ) {
			$out[] = (string) ( $labels['low_dependency'] ?? '' );
		}
		if ( (int) ( $domains['privacy']['readiness_percentage'] ?? 0 ) >= self::STRENGTH_MIN ) {
			$out[] = (string) ( $labels['privacy'] ?? '' );
		}
		if ( (int) ( $results['alignment_score'] ?? 0 ) >= self::ALIGNMENT_STRENGTH_MIN ) {
			$out[] = (string) ( $labels['dfe_alignment'] ?? '' );
		}
		if ( (int) ( $domains['safe_adoption']['readiness_percentage'] ?? 0 ) >= self::STRENGTH_MIN ) {
			$out[] = (string) ( $labels['safe_adoption'] ?? '' );
		}
		if ( (int) ( $domains['human_oversight']['readiness_percentage'] ?? 0 ) >= self::STRENGTH_MIN ) {
			$out[] = (string) ( $labels['human_oversight'] ?? '' );
		}

		return array_values( array_filter( $out ) );
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
			if ( $pct >= self::STRENGTH_MIN ) {
				continue;
			}
			$topic = (array) ( $copy[ $slug ] ?? array() );
			if ( ! $topic ) {
				continue;
			}
			$scored[] = array(
				'slug'    => (string) $slug,
				'label'   => (string) ( $dom['label'] ?? $slug ),
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
		$eligible = in_array( $tier, array( 'strong', 'established' ), true )
			&& (int) ( $results['alignment_score'] ?? 0 ) >= 75;

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
		$oversight = (int) ( $results['human_oversight_readiness'] ?? 0 );
		if ( $oversight < 1 && isset( $results['human_oversight_ratio'] ) ) {
			$oversight = (int) $results['human_oversight_ratio'];
		}

		return array(
			'title'   => (string) ( $cfg['benchmark_summary_title'] ?? __( 'Teacher Benchmark Summary', 'ai-risk-benchmark' ) ),
			'metrics' => array(
				array(
					'label' => __( 'DfE Alignment', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['alignment_score'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'AI Dependency Index™', 'ai-risk-benchmark' ),
					'value' => (int) ( $results['dependency_index'] ?? 0 ) . '%',
				),
				array(
					'label' => __( 'Human Oversight Ratio™', 'ai-risk-benchmark' ),
					'value' => $oversight . '%',
				),
				array(
					'label' => __( 'Risk Level', 'ai-risk-benchmark' ),
					'value' => (string) ( $results['risk_level_label'] ?? '' ),
				),
				array(
					'label' => __( 'Benchmark Rating', 'ai-risk-benchmark' ),
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
	 * @param array<int, array<string, string>> $products Stage-2 gap products.
	 * @param array<string, mixed>              $cfg      Teacher config.
	 * @return array<string, mixed>|null
	 */
	private static function format_gap_products( array $products, array $cfg ): ?array {
		if ( ! $products ) {
			return null;
		}
		$gap = (array) ( $cfg['gap_pathway'] ?? array() );
		$items = array();
		foreach ( $products as $product ) {
			$items[] = (string) ( $product['product'] ?? '' );
		}
		$items = array_values( array_filter( $items ) );
		if ( ! $items ) {
			return null;
		}
		return array(
			'label' => (string) ( $gap['next_step_label'] ?? __( 'Recommended next step', 'ai-risk-benchmark' ) ),
			'intro' => (string) ( $gap['intro'] ?? '' ),
			'items' => $items,
		);
	}
}
