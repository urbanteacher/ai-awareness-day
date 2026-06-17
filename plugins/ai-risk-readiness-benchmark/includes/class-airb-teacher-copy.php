<?php
/**
 * Teacher results — tiered UI copy resolved from scores.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves band-specific copy for teacher results screens.
 */
class AIRB_Teacher_Copy {

	/**
	 * Focus area severity tier from readiness %.
	 */
	public static function focus_tier( int $pct ): string {
		return AIRB_Leader_Copy::focus_tier( $pct );
	}

	/**
	 * AI Dependency Index tier (higher = more reliance).
	 */
	public static function dependency_tier( int $dep ): string {
		if ( $dep >= 80 ) {
			return 'high';
		}
		if ( $dep >= 60 ) {
			return 'moderate_high';
		}
		if ( $dep >= 40 ) {
			return 'moderate';
		}
		if ( $dep >= 20 ) {
			return 'low';
		}
		return 'minimal';
	}

	/**
	 * Human Oversight Ratio tier.
	 */
	public static function oversight_tier( int $pct ): string {
		if ( $pct <= 25 ) {
			return 'critical';
		}
		if ( $pct <= 50 ) {
			return 'high';
		}
		if ( $pct <= 75 ) {
			return 'moderate';
		}
		if ( $pct <= 89 ) {
			return 'strong';
		}
		return 'exemplary';
	}

	/**
	 * Teacher rollout tier from colleague response count.
	 */
	public static function rollout_tier( int $count, bool $has_school ): string {
		if ( ! $has_school || $count <= 0 ) {
			return 'none';
		}
		if ( $count < 10 ) {
			return 'early';
		}
		if ( $count < 20 ) {
			return 'nearly';
		}
		return 'unlocked';
	}

	/**
	 * Readiness band slug for CTA selection.
	 */
	public static function readiness_cta_band( int $score ): string {
		return AIRB_Leader_Copy::readiness_cta_band( $score );
	}

	/**
	 * Resolved headline metrics for the teacher results hero and supporting cards.
	 *
	 * @param array<string, mixed> $results Scored results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$readiness = (int) ( $results['alignment_score'] ?? 0 );
		$risk      = (int) round( (float) ( $results['overall_risk_percentage'] ?? 0 ) );
		$dep       = (int) ( $results['dependency_index'] ?? 0 );
		$ho_pct    = self::oversight_pct( $results );
		$band      = AIRB_Scoring::readiness_band( $readiness );

		$hero = (array) ( ( $cfg['copy_tiers']['readiness'][ $band ] ?? array() ) );
		if ( ! $hero ) {
			$signals = (array) ( $cfg['metric_signals'] ?? array() );
			$hero    = (array) ( $signals['readiness'][ $band ] ?? array() );
		}

		if ( empty( $hero['consequence'] ) ) {
			$hero['consequence'] = (string) ( $cfg['headlines'][ $band ] ?? '' );
		}

		$ui = array(
			'hero'            => self::signal_payload( $hero ),
			'risk_card'       => self::tier_payload( 'risk', AIRB_Leader_Copy::risk_tier( $risk ), $cfg ),
			'dependency_card' => self::tier_payload( 'dependency', self::dependency_tier( $dep ), $cfg ),
			'oversight'       => self::oversight_payload( $ho_pct, $cfg ),
		);

		if ( array_key_exists( 'bias_readiness', $results ) && null !== $results['bias_readiness'] ) {
			$bias_score = (int) $results['bias_readiness'];
			$bias_card  = self::tier_payload( 'bias', AIRB_Leader_Copy::bias_tier( $bias_score ), $cfg );
			$threshold  = (int) ( $cfg['bias_health_callout_threshold'] ?? 50 );
			if ( $bias_score < $threshold && ! empty( $cfg['bias_health_callout'] ) ) {
				$bias_card['consequence'] = (string) $cfg['bias_health_callout'];
			}
			$ui['bias_card'] = $bias_card;
		}

		return $ui;
	}

	/**
	 * Human oversight % for gauge and copy.
	 *
	 * @param array<string, mixed> $results Scored results.
	 */
	public static function oversight_pct( array $results ): int {
		$ho_domain = (array) ( $results['domain_scores']['human_oversight'] ?? array() );
		$oversight = (int) round( (float) ( $ho_domain['readiness_percentage'] ?? 0 ) );
		if ( $oversight < 1 ) {
			$oversight = (int) ( $results['human_oversight_ratio'] ?? 0 );
		}
		return $oversight;
	}

	/**
	 * @param int                  $pct Oversight %.
	 * @param array<string, mixed> $cfg Teacher config.
	 * @return array<string, mixed>
	 */
	public static function oversight_payload( int $pct, array $cfg ): array {
		$tier  = self::oversight_tier( $pct );
		$block = self::tier_payload( 'oversight', $tier, $cfg );
		if ( empty( $block['signal'] ) ) {
			$block['signal'] = AIRB_Scoring::human_oversight_label( $pct );
		}
		$block['pct']  = $pct;
		$block['tier'] = $tier;
		return $block;
	}

	/**
	 * Focus area copy for a domain at a given readiness %.
	 *
	 * @param string               $slug Domain slug.
	 * @param int                  $pct  Readiness %.
	 * @param array<string, mixed> $cfg  Teacher config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( string $slug, int $pct, array $cfg ): array {
		if ( class_exists( 'AIRB_Copy_Tiers', false ) && AIRB_Copy_Tiers::use_json_copy() ) {
			$tiers = AIRB_Copy_Tiers::for_role( 'teacher' );
			$focus = $tiers->domain_focus( $slug, $pct );
			if ( empty( $focus['summary'] ) && 'ai_dependency' === $slug ) {
				$focus = $tiers->domain_focus( 'independent_practice', $pct );
			}
			if ( empty( $focus['summary'] ) && 'bias_equality' === $slug ) {
				$focus = $tiers->domain_focus( 'bias_awareness', $pct );
			}
			if ( ! empty( $focus['summary'] ) ) {
				return array(
					'summary'       => (string) $focus['summary'],
					'likely_impact' => array_values( array_filter( (array) ( $focus['impact'] ?? array() ) ) ),
					'actions'       => (array) ( $focus['actions'] ?? array() ),
					'tier'          => (string) ( $focus['severity'] ?? self::focus_tier( $pct ) ),
				);
			}
		}

		$tier     = self::focus_tier( $pct );
		$tiered   = (array) ( ( $cfg['focus_tiers'][ $slug ] ?? array() )[ $tier ] ?? array() );
		$fallback = (array) ( $cfg['opportunity_copy'][ $slug ] ?? array() );

		$summary = (string) ( $tiered['summary'] ?? $fallback['summary'] ?? '' );
		$impact  = (array) ( $tiered['likely_impact'] ?? array() );
		if ( ! $impact && 'critical' === $tier && ! empty( $fallback['detail'] ) ) {
			$impact = array( (string) $fallback['detail'] );
		}
		$actions = (array) ( $tiered['actions'] ?? array() );

		return array(
			'summary'       => $summary,
			'likely_impact' => array_values( array_filter( $impact ) ),
			'actions'       => $actions,
			'tier'          => $tier,
		);
	}

	/**
	 * Top strength statements from highest-scoring domains.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function strength_statements( array $results, array $cfg ): array {
		$rules   = (array) ( $cfg['strength_tiers'] ?? array() );
		$domains = (array) ( $results['domain_scores'] ?? array() );
		$ho_pct  = self::oversight_pct( $results );
		$scored  = array();

		foreach ( $rules as $slug => $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$metric = (string) ( $rule['metric'] ?? '' );
			if ( 'oversight' === $metric ) {
				$pct = $ho_pct;
				if ( $pct < (int) ( $rule['min'] ?? 76 ) ) {
					continue;
				}
			} else {
				$dom = (array) ( $domains[ $slug ] ?? array() );
				if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
					continue;
				}
				$pct = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
				if ( $pct < (int) ( $rule['min'] ?? 76 ) ) {
					continue;
				}
			}
			$title = (string) ( $rule['label'] ?? '' );
			if ( ! $title ) {
				continue;
			}
			$detail_tpl = (string) ( $rule['detail'] ?? '' );
			$detail     = $detail_tpl ? str_replace( '{pct}', (string) $pct, $detail_tpl ) : '';
			$scored[]   = array(
				'slug'  => (string) $slug,
				'pct'   => $pct,
				'title' => $title,
				'detail'=> $detail,
			);
		}

		usort(
			$scored,
			static function ( $a, $b ) {
				return ( $b['pct'] ?? 0 ) <=> ( $a['pct'] ?? 0 );
			}
		);

		return array_slice( $scored, 0, 3 );
	}

	/**
	 * Rollout intro + note for teacher colleague response tier.
	 *
	 * @param array<string, mixed> $progress School progress.
	 * @param array<string, mixed> $cfg      Teacher config.
	 * @return array<string, string>
	 */
	public static function rollout_copy( array $progress, array $cfg ): array {
		$count      = (int) ( $progress['teacher_responses'] ?? 0 );
		$has_school = ! empty( $progress['has_school'] );
		$tier       = self::rollout_tier( $count, $has_school );
		$blocks     = (array) ( $cfg['rollout_tiers'] ?? array() );
		$block      = (array) ( $blocks[ $tier ] ?? $blocks['none'] ?? array() );
		$intro      = (string) ( $block['intro'] ?? ( $cfg['rollout_intro'] ?? '' ) );
		$note       = (string) ( $block['note'] ?? '' );
		$threshold  = (int) ( $progress['threshold'] ?? 20 );
		$remaining  = (int) ( $progress['responses_remaining'] ?? max( 0, $threshold - $count ) );

		$note = str_replace(
			array( '{n}', '{total}', '{threshold}', '{remaining}' ),
			array( (string) $count, (string) $count, (string) $threshold, (string) $remaining ),
			$note
		);

		return array(
			'intro' => $intro,
			'note'  => $note,
			'tier'  => $tier,
		);
	}

	/**
	 * Readiness-band CTA hero block.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Teacher config.
	 * @return array<string, mixed>
	 */
	public static function cta_hero( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = self::readiness_cta_band( $score );
		$tiers = (array) ( $cfg['readiness_cta_tiers'] ?? array() );
		$block = (array) ( $tiers[ $band ] ?? $tiers['emerging'] ?? array() );

		return array(
			'key'                 => (string) ( $block['key'] ?? 'whole_school_cpd' ),
			'title'               => (string) ( $block['title'] ?? '' ),
			'body'                => (string) ( $block['body'] ?? '' ),
			'deliverables'        => (array) ( $block['deliverables'] ?? array() ),
			'cta_text'            => (string) ( $block['cta_text'] ?? __( 'Request support', 'ai-risk-benchmark' ) ),
			'secondary_key'       => (string) ( $block['secondary_key'] ?? '' ),
			'secondary_cta_text'  => (string) ( $block['secondary_cta_text'] ?? '' ),
			'pathway_kicker'      => (string) ( $block['pathway_kicker'] ?? '' ),
		);
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

	/**
	 * @param string               $family Tier family key in copy_tiers.
	 * @param string               $slug   Tier slug.
	 * @param array<string, mixed> $cfg    Teacher config.
	 * @return array<string, mixed>
	 */
	private static function tier_payload( string $family, string $slug, array $cfg ): array {
		$tiers = (array) ( $cfg['copy_tiers'][ $family ] ?? array() );
		$block = (array) ( $tiers[ $slug ] ?? array() );
		if ( $block ) {
			return self::signal_payload( $block );
		}
		$legacy = (array) ( ( $cfg['metric_signals'][ $family ] ?? array() ) );
		if ( isset( $legacy[ $slug ] ) ) {
			return self::signal_payload( (array) $legacy[ $slug ] );
		}
		return array(
			'signal'      => '',
			'tone'        => 'neutral',
			'consequence' => '',
		);
	}
}
