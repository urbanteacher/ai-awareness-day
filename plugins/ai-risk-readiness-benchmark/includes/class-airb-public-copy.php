<?php
/**
 * Public benchmark results — tiered copy resolution.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves UI copy for the public role.
 */
class AIRB_Public_Copy {

	/**
	 * @param int $score Alignment score 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function safety_band( int $score ): array {
		$score  = max( 0, min( 100, $score ) );
		$levels = AIRB_Defaults::public_safety_levels();

		foreach ( $levels as $level ) {
			if ( $score >= (int) $level['min'] && $score <= (int) $level['max'] ) {
				return array(
					'slug'  => (string) $level['slug'],
					'label' => (string) $level['label'],
				);
			}
		}

		return array(
			'slug'  => 'take_care',
			'label' => __( 'Take care — some risks in your AI habits', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * @param int $pct Readiness percentage 0-100.
	 */
	public static function focus_tier( int $pct ): string {
		if ( $pct < 35 ) {
			return 'critical';
		}
		if ( $pct < 50 ) {
			return 'high';
		}
		return 'moderate';
	}

	/**
	 * Domain row badge labels matching the public dashboard spec.
	 *
	 * @param int $pct Readiness percentage 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function domain_badge( int $pct ): array {
		if ( $pct >= 75 ) {
			return array(
				'slug'  => 'good',
				'label' => __( 'Good', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 50 ) {
			return array(
				'slug'  => 'developing',
				'label' => __( 'Developing', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 35 ) {
			return array(
				'slug'  => 'attention',
				'label' => __( 'Needs work', 'ai-risk-benchmark' ),
			);
		}
		return array(
			'slug'  => 'risk',
			'label' => __( 'At risk', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Summary metric band label for the 3-card hero grid.
	 *
	 * @param string $slug Metric slug.
	 * @param int    $pct  Display percentage.
	 * @param string $mode readiness|risk.
	 * @return array{slug:string,label:string,note:string}
	 */
	public static function summary_metric_band( string $slug, int $pct, string $mode = 'readiness' ): array {
		$pct = max( 0, min( 100, $pct ) );

		if ( 'data_risk_exposure' === $slug || 'risk' === $mode ) {
			if ( $pct >= 70 ) {
				return array(
					'slug'  => 'risk',
					'label' => __( 'High exposure', 'ai-risk-benchmark' ),
					'note'  => __( 'Personal and work data entering AI tools.', 'ai-risk-benchmark' ),
				);
			}
			if ( $pct >= 45 ) {
				return array(
					'slug'  => 'attention',
					'label' => __( 'Moderate exposure', 'ai-risk-benchmark' ),
					'note'  => __( 'Some sensitive information may be reaching AI tools.', 'ai-risk-benchmark' ),
				);
			}
			return array(
				'slug'  => 'good',
				'label' => __( 'Lower exposure', 'ai-risk-benchmark' ),
				'note'  => __( 'You are cautious about what enters AI tools.', 'ai-risk-benchmark' ),
			);
		}

		if ( 'ai_dependency' === $slug ) {
			if ( $pct >= 70 ) {
				return array(
					'slug'  => 'attention',
					'label' => __( 'Moderate reliance', 'ai-risk-benchmark' ),
					'note'  => __( 'AI is becoming a first resort for some tasks.', 'ai-risk-benchmark' ),
				);
			}
			if ( $pct >= 45 ) {
				return array(
					'slug'  => 'developing',
					'label' => __( 'Balanced use', 'ai-risk-benchmark' ),
					'note'  => __( 'You use AI regularly but not for everything.', 'ai-risk-benchmark' ),
				);
			}
			return array(
				'slug'  => 'good',
				'label' => __( 'Light use', 'ai-risk-benchmark' ),
				'note'  => __( 'AI is a tool you reach for occasionally.', 'ai-risk-benchmark' ),
			);
		}

		if ( $pct >= 70 ) {
			return array(
				'slug'  => 'good',
				'label' => __( 'Mostly checking', 'ai-risk-benchmark' ),
				'note'  => __( 'You usually question what AI tells you.', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 45 ) {
			return array(
				'slug'  => 'developing',
				'label' => __( 'Sometimes checking', 'ai-risk-benchmark' ),
				'note'  => __( 'You verify important answers but not always.', 'ai-risk-benchmark' ),
			);
		}
		return array(
			'slug'  => 'risk',
			'label' => __( 'Rarely checking', 'ai-risk-benchmark' ),
			'note'  => __( 'You may be trusting AI outputs too readily.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * @param array<string, mixed> $area Focus area row.
	 * @param array<string, mixed> $cfg  Config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( array $area, array $cfg ): array {
		$slug   = (string) ( $area['focus_slug'] ?? $area['slug'] ?? '' );
		$pct    = (int) ( $area['pct'] ?? 0 );
		$tier   = self::focus_tier( $pct );
		$tiered = (array) ( ( $cfg['focus_tiers'][ $slug ] ?? array() )[ $tier ] ?? array() );
		if ( empty( $tiered ) && 'critical' !== $tier ) {
			$tiers  = (array) ( $cfg['focus_tiers'][ $slug ] ?? array() );
			$tiered = (array) ( $tiers['high'] ?? $tiers['moderate'] ?? array() );
		}

		return array(
			'summary'           => (string) ( $tiered['summary'] ?? $area['summary'] ?? '' ),
			'challenge_heading' => (string) ( $tiered['challenge_heading'] ?? '' ),
			'challenge_body'    => (string) ( $tiered['challenge_body'] ?? '' ),
			'challenge_bullets' => (array) ( $tiered['challenge_bullets'] ?? array() ),
			'likely_impact'     => (array) ( $tiered['likely_impact'] ?? $tiered['impact'] ?? $tiered['challenge_bullets'] ?? array() ),
			'actions'           => (array) ( $tiered['actions'] ?? array() ),
			'severity'          => (string) ( $tiered['severity'] ?? ( 'critical' === $tier ? 'risk' : 'attention' ) ),
			'tier'              => $tier,
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function strength_statements( array $results, array $cfg ): array {
		$display = (array) ( $results['public_display_domains'] ?? array() );
		$rules   = (array) ( $cfg['strength_tiers'] ?? array() );
		$rows    = array();

		$domain_map = array(
			'human_oversight'   => 'verification',
			'ai_literacy'       => 'verification',
			'privacy_awareness' => 'data_privacy',
		);

		foreach ( $rules as $slug => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$source = (string) ( $domain_map[ $slug ] ?? $slug );
			$dom    = (array) ( $display[ $source ] ?? array() );
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

		return array_slice( $rows, 0, 2 );
	}

	/**
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = self::safety_band( $score );
		$tiers = (array) ( $cfg['copy_tiers']['readiness'] ?? array() );
		$hero  = (array) ( $tiers[ $band['slug'] ] ?? array() );

		return array(
			'hero' => array(
				'signal'      => (string) ( $hero['signal'] ?? '' ),
				'tone'        => (string) ( $hero['tone'] ?? 'neutral' ),
				'consequence' => (string) ( $hero['consequence'] ?? '' ),
				'band_label'  => (string) ( $band['label'] ?? '' ),
			),
		);
	}
}
