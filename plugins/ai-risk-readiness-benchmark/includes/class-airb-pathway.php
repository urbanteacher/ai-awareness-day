<?php
/**
 * Audit pathway — answer-aware next steps and gateway CTAs.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Match role- and response-aware offers after an audit.
 */
class AIRB_Pathway {

	/**
	 * Offer type labels for UI badges.
	 *
	 * @return array<string, string>
	 */
	public static function offer_type_labels(): array {
		return array(
			'policy'        => __( 'Policy', 'ai-risk-benchmark' ),
			'template'      => __( 'Template', 'ai-risk-benchmark' ),
			'training'      => __( 'Training', 'ai-risk-benchmark' ),
			'cpd'           => __( 'CPD', 'ai-risk-benchmark' ),
			'consultation'  => __( 'Consultation', 'ai-risk-benchmark' ),
			'tracking'      => __( 'Track progress', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Match pathway offers for a completed audit.
	 *
	 * @param string               $role          Role slug.
	 * @param array<string, mixed> $answers       question_id => value.
	 * @param array<string, mixed> $domain_scores Domain scores.
	 * @param array<string, mixed> $results       Full scoring results.
	 * @param array<string, mixed> $config        Plugin config.
	 * @return array<int, array<string, mixed>>
	 */
	public static function match_next_steps( string $role, array $answers, array $domain_scores, array $results, array $config ): array {
		$offers  = (array) ( $config['pathway_offers'] ?? array() );
		$matched = array();

		foreach ( $offers as $offer ) {
			if ( ! self::role_matches( $role, $offer ) ) {
				continue;
			}
			if ( ! self::trigger_matches( $role, $answers, $domain_scores, $results, $offer ) ) {
				continue;
			}
			$matched[] = self::normalize_offer( $offer );
		}

		$recs = AIRB_Scoring::match_recommendations(
			$domain_scores,
			(array) ( $config['recommendations'] ?? array() ),
			$role
		);

		foreach ( $recs as $rec ) {
			$matched[] = self::normalize_offer(
				array_merge(
					$rec,
					array(
						'offer_type' => (string) ( $rec['offer_type'] ?? 'template' ),
						'priority'   => (int) ( $rec['priority'] ?? 50 ),
					)
				)
			);
		}

		$matched = self::dedupe_offers( $matched );
		usort(
			$matched,
			static function ( $a, $b ) {
				return ( (int) ( $b['priority'] ?? 0 ) ) <=> ( (int) ( $a['priority'] ?? 0 ) );
			}
		);

		return array_slice( $matched, 0, 5 );
	}

	/**
	 * Gateway block — audit as doorway into tracking, CPD and consultation.
	 *
	 * @param string               $role    Role slug.
	 * @param array<string, mixed> $config  Plugin config.
	 * @param string               $school School name from submission.
	 * @return array<string, mixed>|null
	 */
	public static function build_gateway( string $role, array $config, string $school = '' ): ?array {
		if ( ! in_array( $role, array( 'teacher', 'leader' ), true ) ) {
			return null;
		}

		$gateway = (array) ( $config['gateway'] ?? array() );
		if ( empty( $gateway['headline'] ) ) {
			return null;
		}

		$cards = array();
		foreach ( array( 'track_progress', 'book_cpd', 'book_consultation' ) as $key ) {
			$item = (array) ( $gateway[ $key ] ?? array() );
			if ( empty( $item['title'] ) ) {
				continue;
			}
			$url = (string) ( $item['cta_url'] ?? '' );
			if ( 'track_progress' === $key && $school ) {
				$url = add_query_arg( 'school', rawurlencode( $school ), $url ? $url : self::default_track_url() ) . '#airb-school-dashboard';
			}
			$cards[] = array(
				'key'      => $key,
				'title'    => (string) $item['title'],
				'body'     => (string) ( $item['body'] ?? '' ),
				'cta_text' => (string) ( $item['cta_text'] ?? '' ),
				'cta_url'  => $url,
			);
		}

		return array(
			'headline' => (string) $gateway['headline'],
			'intro'    => (string) ( $gateway['intro'] ?? '' ),
			'cards'    => $cards,
		);
	}

	/**
	 * Default school dashboard URL fragment.
	 */
	private static function default_track_url(): string {
		return home_url( '/' );
	}

	/**
	 * @param array<string, mixed> $offer Offer rule.
	 */
	private static function normalize_offer( array $offer ): array {
		$type = sanitize_key( (string) ( $offer['offer_type'] ?? 'template' ) );
		$labels = self::offer_type_labels();

		return array(
			'id'         => (string) ( $offer['id'] ?? sanitize_title( (string) ( $offer['title'] ?? 'offer' ) ) ),
			'offer_type' => $type,
			'type_label' => $labels[ $type ] ?? ucfirst( $type ),
			'priority'   => (int) ( $offer['priority'] ?? 50 ),
			'title'      => (string) ( $offer['title'] ?? '' ),
			'body'       => (string) ( $offer['body'] ?? '' ),
			'cta_text'   => (string) ( $offer['cta_text'] ?? $offer['title'] ?? '' ),
			'cta_url'    => (string) ( $offer['cta_url'] ?? '' ),
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $offers Offers.
	 * @return array<int, array<string, mixed>>
	 */
	private static function dedupe_offers( array $offers ): array {
		$seen = array();
		$out  = array();
		foreach ( $offers as $offer ) {
			$key = strtolower( (string) ( $offer['title'] ?? '' ) );
			if ( '' === $key || isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$out[]        = $offer;
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $offer Offer rule.
	 */
	private static function role_matches( string $role, array $offer ): bool {
		$roles = (array) ( $offer['roles'] ?? array() );
		return ! $roles || in_array( $role, $roles, true );
	}

	/**
	 * @param array<string, mixed> $offer Offer rule.
	 */
	private static function trigger_matches( string $role, array $answers, array $domain_scores, array $results, array $offer ): bool {
		$trigger = (string) ( $offer['trigger'] ?? 'domain_band' );

		if ( 'answer' === $trigger ) {
			$qid = (string) ( $offer['question_id'] ?? '' );
			if ( ! $qid ) {
				return false;
			}
			$answer = (string) ( $answers[ $qid ] ?? '' );
			if ( 'slider' === (string) ( $offer['question_type'] ?? '' ) ) {
				$pct = max( 0, min( 100, (int) $answer ) );
				$min = (int) ( $offer['slider_min'] ?? 0 );
				$max = (int) ( $offer['slider_max'] ?? 100 );
				return $pct >= $min && $pct <= $max;
			}
			$values = (array) ( $offer['answer_values'] ?? array() );
			if ( ! $values ) {
				return false;
			}
			return in_array( $answer, $values, true );
		}

		if ( 'metric' === $trigger ) {
			$metric = (string) ( $offer['metric'] ?? '' );
			$val    = (float) ( $results[ $metric ] ?? 0 );
			$min    = (float) ( $offer['metric_min'] ?? 0 );
			return $val >= $min;
		}

		$dom = (string) ( $offer['domain'] ?? '' );
		if ( ! $dom || ! isset( $domain_scores[ $dom ] ) ) {
			return false;
		}
		$band_rank = array( 'low' => 0, 'moderate' => 1, 'high' => 2, 'critical' => 3 );
		$min_band  = (string) ( $offer['min_band'] ?? 'high' );
		$actual    = (string) ( $domain_scores[ $dom ]['band'] ?? 'low' );
		return ( $band_rank[ $actual ] ?? 0 ) >= ( $band_rank[ $min_band ] ?? 2 );
	}
}
