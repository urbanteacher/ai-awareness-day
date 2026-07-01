<?php
/**
 * School leader results — tiered UI copy resolved from scores.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves band-specific copy for leader results screens.
 */
class AIRB_Leader_Copy {

	/**
	 * Focus area severity tier from readiness %.
	 */
	public static function focus_tier( int $pct ): string {
		if ( $pct <= 25 ) {
			return 'critical';
		}
		if ( $pct <= 49 ) {
			return 'high';
		}
		if ( $pct <= 69 ) {
			return 'moderate';
		}
		return 'low';
	}

	/**
	 * Governance maturity card tier (0–100 readiness-style score).
	 */
	/**
	 * Bias & equality readiness card tier (0–100 readiness-style score).
	 */
	public static function bias_tier( int $score ): string {
		if ( $score < 25 ) {
			return 'critical';
		}
		if ( $score < 50 ) {
			return 'high';
		}
		if ( $score < 75 ) {
			return 'moderate';
		}
		return 'low';
	}

	public static function governance_tier( int $score ): string {
		if ( $score <= 29 ) {
			return 'not_in_place';
		}
		if ( $score <= 49 ) {
			return 'gaps';
		}
		if ( $score <= 64 ) {
			return 'partial';
		}
		if ( $score <= 79 ) {
			return 'mostly';
		}
		return 'full';
	}

	/**
	 * AI risk exposure tier (higher = worse).
	 */
	public static function risk_tier( int $risk ): string {
		if ( $risk >= 80 ) {
			return 'critical';
		}
		if ( $risk >= 60 ) {
			return 'high';
		}
		if ( $risk >= 40 ) {
			return 'moderate';
		}
		if ( $risk >= 20 ) {
			return 'low';
		}
		return 'minimal';
	}

	/**
	 * Rollout community-response tier.
	 */
	public static function rollout_tier( int $total ): string {
		if ( $total <= 0 ) {
			return 'none';
		}
		if ( $total < 10 ) {
			return 'early';
		}
		if ( $total < 20 ) {
			return 'nearly';
		}
		if ( $total < 50 ) {
			return 'unlocked';
		}
		return 'representative';
	}

	/**
	 * Readiness band slug for CTA selection.
	 */
	public static function readiness_cta_band( int $score ): string {
		if ( $score >= 90 ) {
			return 'leading';
		}
		if ( $score >= 75 ) {
			return 'strong';
		}
		if ( $score >= 60 ) {
			return 'established';
		}
		if ( $score >= 40 ) {
			return 'developing';
		}
		return 'emerging';
	}

	/**
	 * Resolved headline metrics for the results hero and supporting cards.
	 *
	 * @param array<string, mixed> $results Scored results.
	 * @param array<string, mixed> $cfg     Leader config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$readiness   = (int) ( $results['alignment_score'] ?? 0 );
		$risk        = (int) round( (float) ( $results['overall_risk_percentage'] ?? 0 ) );
		$gov         = (int) ( $results['governance_maturity'] ?? 0 );
		$readiness_band = AIRB_Scoring::readiness_band( $readiness );

		$signals = (array) ( $cfg['metric_signals'] ?? array() );
		$hero    = (array) ( $signals['readiness'][ $readiness_band ] ?? array() );

		$ui = array(
			'hero'            => self::signal_payload( $hero ),
			'risk_card'       => self::tier_payload( 'risk', self::risk_tier( $risk ), $cfg ),
			'governance_card' => self::tier_payload( 'governance', self::governance_tier( $gov ), $cfg ),
		);

		if ( array_key_exists( 'bias_readiness', $results ) && null !== $results['bias_readiness'] ) {
			$bias_score = (int) $results['bias_readiness'];
			$bias_card  = self::tier_payload( 'bias', self::bias_tier( $bias_score ), $cfg );
			$threshold  = (int) ( $cfg['bias_health_callout_threshold'] ?? 50 );
			if ( $bias_score < $threshold && ! empty( $cfg['bias_health_callout'] ) ) {
				$bias_card['consequence'] = (string) $cfg['bias_health_callout'];
			}
			$ui['bias_card'] = $bias_card;
		}

		return $ui;
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
	 * @param array<string, mixed> $cfg    Leader config.
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

	/**
	 * Focus area copy for a domain at a given readiness %.
	 *
	 * @param string               $slug Domain slug.
	 * @param int                  $pct  Readiness %.
	 * @param array<string, mixed> $cfg  Leader config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( string $slug, int $pct, array $cfg ): array {
		$tier     = self::focus_tier( $pct );
		$tiers    = (array) ( $cfg['focus_tiers'][ $slug ] ?? array() );
		$tiered   = (array) ( $tiers[ $tier ] ?? array() );
		if ( empty( $tiered ) ) {
			$tiered = self::nearest_focus_tier( $tiers, $tier );
		}
		$fallback = (array) ( $cfg['focus_copy'][ $slug ] ?? array() );

		$summary = (string) ( $tiered['summary'] ?? $fallback['summary'] ?? '' );
		$impact  = (array) ( $tiered['likely_impact'] ?? ( 'critical' === $tier ? ( $fallback['likely_impact'] ?? array() ) : array() ) );
		$actions = (array) ( $tiered['actions'] ?? $fallback['actions'] ?? array() );

		return array(
			'summary'       => $summary,
			'likely_impact' => $impact,
			'actions'       => $actions,
			'tier'          => $tier,
		);
	}

	/**
	 * Nearest authored focus tier when a domain's copy set doesn't cover
	 * every severity level `focus_tier()` can return (e.g. a domain with no
	 * "low" tier authored for high-scoring responses).
	 *
	 * @param array<string, mixed> $tiers        Domain's focus tiers keyed by severity slug.
	 * @param string               $requested Requested tier slug.
	 * @return array<string, mixed>
	 */
	private static function nearest_focus_tier( array $tiers, string $requested ): array {
		$order = array( 'critical', 'high', 'moderate', 'low' );
		$idx   = array_search( $requested, $order, true );
		if ( false === $idx ) {
			return array();
		}
		for ( $offset = 1; $offset < count( $order ); $offset++ ) {
			foreach ( array( $idx + $offset, $idx - $offset ) as $candidate ) {
				if ( isset( $order[ $candidate ] ) && ! empty( $tiers[ $order[ $candidate ] ] ) ) {
					return (array) $tiers[ $order[ $candidate ] ];
				}
			}
		}
		return array();
	}

	/**
	 * Urgent action title + rationale from domain scores.
	 *
	 * @param array<string, mixed>             $results Scored results.
	 * @param array<int, array<string, mixed>> $focus   Focus areas.
	 * @param array<string, mixed>             $cfg     Leader config.
	 * @return array<string, mixed>
	 */
	public static function priority_detail( array $results, array $focus, array $cfg ): array {
		$scenarios = (array) ( $cfg['priority_scenarios'] ?? array() );
		$domains   = (array) ( $results['domain_scores'] ?? array() );
		$default   = (array) ( $scenarios['default'] ?? array() );
		$fallback_title = (string) ( $cfg['default_priority_action'] ?? '' );
		$fallback_rat   = (string) ( $cfg['default_priority_rationale'] ?? '' );

		$domain_pcts = array();
		foreach ( $domains as $slug => $dom ) {
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$domain_pcts[ (string) $slug ] = (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
		}

		if ( $domain_pcts && min( $domain_pcts ) > 60 ) {
			return self::scenario_row( (array) ( $scenarios['all_strong'] ?? $default ), $domain_pcts, $fallback_title, $fallback_rat );
		}

		$privacy_pct      = $domain_pcts['privacy'] ?? null;
		$safeguarding_pct = $domain_pcts['safeguarding'] ?? null;
		if ( 0 === $privacy_pct && 0 === $safeguarding_pct ) {
			return self::scenario_row( (array) ( $scenarios['privacy_safeguarding_critical'] ?? $default ), $domain_pcts, $fallback_title, $fallback_rat );
		}
		if ( 0 === $privacy_pct ) {
			return self::scenario_row( (array) ( $scenarios['privacy_critical'] ?? $default ), $domain_pcts, $fallback_title, $fallback_rat, 'privacy' );
		}
		if ( 0 === $safeguarding_pct ) {
			return self::scenario_row( (array) ( $scenarios['safeguarding_critical'] ?? $default ), $domain_pcts, $fallback_title, $fallback_rat, 'safeguarding' );
		}

		$checks = array(
			array( 'slug' => 'governance', 'max' => 29, 'key' => 'governance_critical' ),
			array( 'slug' => 'human_oversight', 'max' => 32, 'key' => 'human_oversight_high' ),
			array( 'slug' => 'assessment_integrity', 'max' => 32, 'key' => 'assessment_high' ),
			array( 'slug' => 'ai_literacy', 'max' => 32, 'key' => 'ai_literacy_high' ),
		);
		foreach ( $checks as $check ) {
			$slug = (string) $check['slug'];
			if ( isset( $domain_pcts[ $slug ] ) && $domain_pcts[ $slug ] <= (int) $check['max'] ) {
				return self::scenario_row( (array) ( $scenarios[ $check['key'] ] ?? $default ), $domain_pcts, $fallback_title, $fallback_rat, $slug );
			}
		}

		if ( $focus ) {
			$first = $focus[0];
			$slug  = (string) ( $first['slug'] ?? '' );
			$pct   = (int) ( $first['pct'] ?? 0 );
			$actions = (array) ( $cfg['priority_actions'] ?? array() );
			$rationales = (array) ( $cfg['priority_rationales'] ?? array() );
			$title = (string) ( $actions[ $slug ] ?? $fallback_title );
			$rat_tpl = (string) ( $rationales[ $slug ] ?? '' );
			$rationale = $rat_tpl
				? str_replace( array( '{pct}', '{domain}' ), array( (string) $pct, (string) ( $first['label'] ?? '' ) ), $rat_tpl )
				: (string) ( $first['summary'] ?? $fallback_rat );
			return array(
				'title'        => $title,
				'rationale'    => $rationale,
				'domain_slug'  => $slug,
				'domain_label' => (string) ( $first['label'] ?? '' ),
				'domain_pct'   => $pct,
			);
		}

		return self::scenario_row( $default, $domain_pcts, $fallback_title, $fallback_rat );
	}

	/**
	 * @param array<string, mixed>        $scenario Scenario block.
	 * @param array<string, int>          $pcts     Domain readiness percentages.
	 * @param string                      $fallback_title Fallback title.
	 * @param string                      $fallback_rat   Fallback rationale.
	 * @param string|null                 $pct_key        Domain slug for {pct} replacement.
	 * @return array<string, mixed>
	 */
	private static function scenario_row( array $scenario, array $pcts, string $fallback_title, string $fallback_rat, ?string $pct_key = null ): array {
		$title = (string) ( $scenario['title'] ?? $fallback_title );
		$rat   = (string) ( $scenario['rationale'] ?? $fallback_rat );
		$pct   = ( null !== $pct_key && isset( $pcts[ $pct_key ] ) ) ? (int) $pcts[ $pct_key ] : 0;
		$rat   = str_replace( '{pct}', (string) $pct, $rat );
		return array(
			'title'     => $title,
			'rationale' => $rat,
		);
	}

	/**
	 * Rollout intro + note for community response tier.
	 *
	 * @param array<string, mixed> $progress School rollout progress.
	 * @param array<string, mixed> $cfg      Leader config.
	 * @return array<string, string>
	 */
	public static function rollout_copy( array $progress, array $cfg ): array {
		$total  = (int) ( $progress['total'] ?? 0 );
		$tier   = self::rollout_tier( $total );
		$blocks = (array) ( $cfg['rollout_tiers'] ?? array() );
		$block  = (array) ( $blocks[ $tier ] ?? $blocks['none'] ?? array() );
		$intro  = (string) ( $block['intro'] ?? ( $cfg['rollout_intro'] ?? '' ) );
		$note   = (string) ( $block['note'] ?? '' );
		$threshold = (int) ( $progress['threshold'] ?? 20 );
		$remaining = (int) ( $progress['remaining'] ?? max( 0, $threshold - $total ) );
		$note = str_replace(
			array( '{total}', '{threshold}', '{remaining}' ),
			array( (string) $total, (string) $threshold, (string) $remaining ),
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
	 * @param array<string, mixed> $cfg     Leader config.
	 * @return array<string, mixed>
	 */
	public static function cta_hero( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = self::readiness_cta_band( $score );
		$tiers = (array) ( $cfg['readiness_cta_tiers'] ?? array() );
		$block = (array) ( $tiers[ $band ] ?? $tiers['emerging'] ?? array() );
		$gov   = (array) ( $cfg['next_step_blocks']['governance_review'] ?? array() );

		return array(
			'key'          => (string) ( $block['key'] ?? 'governance_review' ),
			'title'        => (string) ( $block['title'] ?? ( $gov['title'] ?? '' ) ),
			'body'         => (string) ( $block['body'] ?? ( $gov['body'] ?? '' ) ),
			'deliverables' => (array) ( $block['deliverables'] ?? ( $gov['deliverables'] ?? array() ) ),
			'cta_text'     => (string) ( $block['cta_text'] ?? ( $gov['cta_text'] ?? __( 'Request support', 'ai-risk-benchmark' ) ) ),
			'cta_url'      => AIRB_Defaults::interest_form_url( (string) ( $block['key'] ?? 'governance_review' ) ),
		);
	}
}
