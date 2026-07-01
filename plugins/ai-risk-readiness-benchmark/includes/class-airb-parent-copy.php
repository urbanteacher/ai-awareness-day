<?php
/**
 * Parent results — tiered UI copy resolved from scores.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves band-specific copy for parent results screens.
 */
class AIRB_Parent_Copy {

	/**
	 * Focus tier from metric % (lower = weaker).
	 */
	public static function focus_tier( int $pct ): string {
		if ( $pct <= 40 ) {
			return 'low';
		}
		if ( $pct <= 60 ) {
			return 'attention';
		}
		return 'developing';
	}

	/**
	 * Badge label for a home metric score.
	 *
	 * @param int $pct Readiness 0-100.
	 * @return array{slug:string,label:string}
	 */
	public static function metric_badge( int $pct ): array {
		if ( $pct >= 65 ) {
			return array(
				'slug'  => 'good',
				'label' => __( 'Good', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 50 ) {
			return array(
				'slug'  => 'developing',
				'label' => __( 'Building', 'ai-risk-benchmark' ),
			);
		}
		if ( $pct >= 35 ) {
			return array(
				'slug'  => 'attention',
				'label' => __( 'Needs attention', 'ai-risk-benchmark' ),
			);
		}
		return array(
			'slug'  => 'risk',
			'label' => __( 'Low awareness', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Resolved hero copy for the parent home AI picture header.
	 *
	 * @param array<string, mixed> $results Results.
	 * @param array<string, mixed> $cfg     Parent config.
	 * @return array<string, mixed>
	 */
	public static function resolve_ui( array $results, array $cfg ): array {
		$score = (int) ( $results['alignment_score'] ?? 0 );
		$band  = AIRB_Parent_Results::awareness_band( $score );
		$slug  = (string) ( $band['slug'] ?? 'developing' );
		$hero  = (array) ( ( $cfg['copy_tiers']['readiness'][ $slug ] ?? array() ) );

		if ( empty( $hero['consequence'] ) ) {
			$hero['consequence'] = (string) ( $cfg['band_summaries'][ AIRB_Scoring::readiness_band( $score ) ] ?? '' );
		}

		return array(
			'hero' => self::signal_payload( $hero ),
			'band' => $band,
		);
	}

	/**
	 * Focus card copy for a parent topic.
	 *
	 * @param array<string, mixed> $area Focus area row.
	 * @param array<string, mixed> $cfg  Config.
	 * @return array<string, mixed>
	 */
	public static function focus_block( array $area, array $cfg ): array {
		$slug   = (string) ( $area['focus_slug'] ?? $area['slug'] ?? '' );
		$pct    = (int) ( $area['pct'] ?? 0 );
		$tier   = self::focus_tier( $pct );
		$tiers  = (array) ( $cfg['focus_tiers'][ $slug ] ?? array() );
		$tiered = (array) ( $tiers[ $tier ] ?? array() );
		if ( empty( $tiered ) ) {
			$tiered = self::nearest_focus_tier( $tiers, $tier );
		}

		return array(
			'summary'           => (string) ( $tiered['summary'] ?? $area['summary'] ?? '' ),
			'challenge_heading' => (string) ( $tiered['challenge_heading'] ?? '' ),
			'challenge_body'    => (string) ( $tiered['challenge_body'] ?? '' ),
			'challenge_bullets' => (array) ( $tiered['challenge_bullets'] ?? array() ),
			'likely_impact'     => (array) ( $tiered['likely_impact'] ?? $tiered['impact'] ?? $tiered['challenge_bullets'] ?? array() ),
			'actions'           => (array) ( $tiered['actions'] ?? $area['actions'] ?? array() ),
			'tier'              => $tier,
			'severity'          => (string) ( $tiered['severity'] ?? ( 'low' === $tier ? 'risk' : 'attention' ) ),
		);
	}

	/**
	 * Nearest authored focus tier when a topic's copy set doesn't cover
	 * every severity level `focus_tier()` can return (e.g. a topic with no
	 * "developing" tier authored for high-scoring parents).
	 *
	 * @param array<string, mixed> $tiers     Topic's focus tiers keyed by severity slug.
	 * @param string               $requested Requested tier slug.
	 * @return array<string, mixed>
	 */
	private static function nearest_focus_tier( array $tiers, string $requested ): array {
		$order = array( 'low', 'attention', 'developing' );
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
	 * Conversation starters — optionally prioritise topics linked to weak metrics.
	 *
	 * @param array<int, array<string, mixed>> $focus_areas Weak focus rows.
	 * @param array<string, mixed>             $cfg         Config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function conversation_starters( array $focus_areas, array $cfg ): array {
		$all     = (array) ( $cfg['conversation_starters'] ?? array() );
		$by_slug = array();
		foreach ( $all as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$slug = (string) ( $item['topic'] ?? '' );
			if ( $slug ) {
				$by_slug[ $slug ] = $item;
			}
		}

		$picked  = array();
		$slugs   = array();
		foreach ( $focus_areas as $area ) {
			$slug = (string) ( $area['focus_slug'] ?? $area['slug'] ?? '' );
			if ( $slug && isset( $by_slug[ $slug ] ) ) {
				$picked[] = $by_slug[ $slug ];
				$slugs[]  = $slug;
			}
		}

		foreach ( $all as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$slug = (string) ( $item['topic'] ?? '' );
			if ( $slug && in_array( $slug, $slugs, true ) ) {
				continue;
			}
			$picked[] = $item;
			if ( count( $picked ) >= 4 ) {
				break;
			}
		}

		return array_slice( $picked, 0, 4 );
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
