<?php
/**
 * Support staff results — tiered UI copy resolved from scores.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves band-specific copy for support staff results screens.
 */
class AIRB_Support_Copy {

	/**
	 * Focus tier from domain readiness %.
	 */
	public static function focus_tier( int $pct ): string {
		if ( $pct <= 35 ) {
			return 'critical';
		}
		if ( $pct <= 55 ) {
			return 'moderate';
		}
		return 'developing';
	}

	/**
	 * Badge for a domain readiness score.
	 *
	 * @param int $pct Readiness 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function domain_badge( int $pct ): array {
		if ( $pct >= 65 ) {
			return array(
				'slug'  => 'good',
				'label' => __( 'Good', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 45 ) {
			return array(
				'slug'  => 'moderate',
				'label' => __( 'Needs work', 'ai-risk-benchmark' ),
			);
		}
		return array(
			'slug'  => 'critical',
			'label' => __( 'Critical', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Focus badge text including risk wording for low scores.
	 *
	 * @param int                  $pct   Readiness.
	 * @param array{slug:string,label:string} $badge Badge row.
	 */
	public static function focus_badge_text( int $pct, array $badge ): string {
		$slug = (string) ( $badge['slug'] ?? 'moderate' );
		if ( 'critical' === $slug ) {
			return __( 'Critical', 'ai-risk-benchmark' ) . ' · ' . $pct . '%';
		}
		if ( 'moderate' === $slug && $pct < 45 ) {
			return __( 'High risk', 'ai-risk-benchmark' ) . ' · ' . $pct . '%';
		}
		return (string) ( $badge['label'] ?? '' ) . ' · ' . $pct . '%';
	}

	/**
	 * Resolved hero copy.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = AIRB_Scoring::readiness_band( $score );
		$hero  = (array) ( ( $cfg['copy_tiers']['readiness'][ $band ] ?? array() ) );

		if ( empty( $hero['consequence'] ) ) {
			$hero['consequence'] = (string) ( $cfg['headlines'][ $band ] ?? '' );
		}

		return array(
			'hero' => self::signal_payload( $hero ),
			'band' => array(
				'slug'  => $band,
				'label' => AIRB_Scoring::readiness_band_label( $score ),
			),
		);
	}

	/**
	 * Risk and role-specific metric cards.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	public static function metric_signals( array $results, array $cfg ): array {
		$risk       = (int) round( (float) ( $results['overall_risk_percentage'] ?? 0 ) );
		$role_risk  = AIRB_Support_Results::role_specific_risk( $results );
		$risk_lvl   = (string) ( $results['risk_level'] ?? 'moderate' );
		$risk_tier  = self::risk_exposure_tier( $risk );
		$role_tier  = self::role_risk_tier( $role_risk );

		$risk_copy  = (array) ( ( $cfg['metric_signals']['risk_exposure'][ $risk_tier ] ?? array() ) );
		$role_copy  = (array) ( ( $cfg['metric_signals']['role_risk'][ $role_tier ] ?? array() ) );

		return array(
			'risk_exposure' => array(
				'value'       => $risk,
				'signal'      => (string) ( $risk_copy['signal'] ?? AIRB_Scoring::display_risk_label( $risk_lvl, (float) $risk ) ),
				'tone'        => (string) ( $risk_copy['tone'] ?? 'warning' ),
				'consequence' => (string) ( $risk_copy['consequence'] ?? '' ),
			),
			'role_risk' => array(
				'value'       => $role_risk,
				'signal'      => (string) ( $role_copy['signal'] ?? __( 'Higher in your role', 'ai-risk-benchmark' ) ),
				'tone'        => (string) ( $role_copy['tone'] ?? 'urgent' ),
				'consequence' => (string) ( $role_copy['consequence'] ?? '' ),
			),
		);
	}

	/**
	 * Structured strength rows.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function strength_statements( array $results, array $cfg ): array {
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$rules   = (array) ( $cfg['strength_tiers'] ?? array() );
		$rows    = array();

		foreach ( $rules as $slug => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$dom = (array) ( $domains[ $slug ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			$min = (int) ( $rule['min'] ?? 70 );
			if ( $pct < $min ) {
				continue;
			}
			$title = (string) ( $rule['label'] ?? '' );
			if ( ! $title ) {
				continue;
			}
			$detail_tpl = (string) ( $rule['detail'] ?? '' );
			$detail     = $detail_tpl ? str_replace( '{pct}', (string) $pct, $detail_tpl ) : '';
			$rows[]     = array(
				'slug'   => (string) $slug,
				'pct'    => $pct,
				'title'  => $title,
				'detail' => $detail,
			);
		}

		usort(
			$rows,
			static function ( $a, $b ) {
				return ( $b['pct'] ?? 0 ) <=> ( $a['pct'] ?? 0 );
			}
		);

		return array_slice( $rows, 0, 3 );
	}

	/**
	 * Focus block for a domain opportunity.
	 *
	 * @param array<string, mixed> $opp Opportunity row.
	 * @param array<string, mixed> $cfg Config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( array $opp, array $cfg ): array {
		$slug   = (string) ( $opp['focus_slug'] ?? $opp['slug'] ?? '' );
		$pct    = (int) ( $opp['pct'] ?? 0 );
		$tier   = self::focus_tier( $pct );
		$tiered = (array) ( ( $cfg['focus_tiers'][ $slug ] ?? array() )[ $tier ] ?? array() );

		return array(
			'summary'           => (string) ( $tiered['summary'] ?? $opp['summary'] ?? '' ),
			'challenge_heading' => (string) ( $tiered['challenge_heading'] ?? '' ),
			'challenge_body'    => (string) ( $tiered['challenge_body'] ?? '' ),
			'challenge_bullets' => (array) ( $tiered['challenge_bullets'] ?? array() ),
			'actions'           => (array) ( $tiered['actions'] ?? array() ),
			'severity'          => (string) ( $tiered['severity'] ?? ( 'critical' === $tier ? 'critical' : 'moderate' ) ),
			'tier'              => $tier,
		);
	}

	/**
	 * School rollout progress for support staff unlock card.
	 *
	 * @param string $school School name.
	 * @return array<string, mixed>
	 */
	public static function school_progress( string $school ): array {
		$school    = trim( $school );
		$threshold = 20;
		$support   = 0;
		$teacher   = 0;

		if ( '' !== $school ) {
			$support = AIRB_Database::count_submissions(
				array(
					'role'   => 'support_staff',
					'school' => $school,
				)
			);
			$teacher = AIRB_Database::count_submissions(
				array(
					'role'   => 'teacher',
					'school' => $school,
				)
			);
		}

		return array(
			'has_school'             => '' !== $school,
			'support_responses'      => $support,
			'teacher_responses'      => $teacher,
			'threshold'              => $threshold,
			'whole_school_available' => '' !== $school && $support >= $threshold,
			'comparison_available'   => '' !== $school && $support >= $threshold && $teacher >= $threshold,
		);
	}

	/**
	 * @param int $risk Risk %.
	 */
	private static function risk_exposure_tier( int $risk ): string {
		if ( $risk >= 55 ) {
			return 'high';
		}
		if ( $risk >= 35 ) {
			return 'moderate';
		}
		return 'low';
	}

	/**
	 * @param int $risk Role-specific risk %.
	 */
	private static function role_risk_tier( int $risk ): string {
		if ( $risk >= 60 ) {
			return 'high';
		}
		if ( $risk >= 40 ) {
			return 'moderate';
		}
		return 'low';
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
}
