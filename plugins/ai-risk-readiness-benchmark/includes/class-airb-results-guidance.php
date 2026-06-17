<?php
/**
 * Shared focus guidance — per-question weak factors and role thresholds.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds per-answer improvement lines for focus cards across roles.
 */
class AIRB_Results_Guidance {

	/** Minimum answer risk score (0–3) treated as needing improvement. */
	private const WEAK_ANSWER_MIN = 2;

	/**
	 * Readiness % below which focus cards expand with per-question factors.
	 *
	 * @param string $role Role slug.
	 */
	public static function focus_max_for_role( string $role ): int {
		$map = array(
			'public'        => 50,
			'teacher'       => 75,
			'student'       => 70,
			'parent'        => 75,
			'leader'        => 70,
			'support_staff' => 75,
		);

		return (int) ( $map[ sanitize_key( $role ) ] ?? 70 );
	}

	/**
	 * Question IDs for a scoring domain from the benchmark question bank.
	 *
	 * @param array<string, mixed> $config      Plugin config.
	 * @param string               $domain_slug Domain slug.
	 * @param string               $role        Role slug.
	 * @return array<int, string>
	 */
	public static function question_ids_for_domain( array $config, string $domain_slug, string $role = '' ): array {
		$out = array();
		foreach ( (array) ( $config['questions'] ?? array() ) as $question ) {
			if ( ! is_array( $question ) ) {
				continue;
			}
			if ( (string) ( $question['domain'] ?? '' ) !== $domain_slug ) {
				continue;
			}
			if ( $role && sanitize_key( $role ) !== sanitize_key( (string) ( $question['role'] ?? '' ) ) ) {
				continue;
			}
			$qid = (string) ( $question['id'] ?? '' );
			if ( $qid ) {
				$out[] = $qid;
			}
		}

		return $out;
	}

	/**
	 * Question IDs listed on a display-domain definition.
	 *
	 * @param array<string, mixed> $display_domains Slug => def map.
	 * @param string               $domain_slug     Domain slug.
	 * @return array<int, string>
	 */
	public static function question_ids_from_display_def( array $display_domains, string $domain_slug ): array {
		$def = (array) ( $display_domains[ $domain_slug ] ?? array() );

		return array_values(
			array_filter(
				array_map(
					static function ( $qid ) {
						return (string) $qid;
					},
					(array) ( $def['questions'] ?? array() )
				)
			)
		);
	}

	/**
	 * Weak-answer improvement lines for a set of question IDs.
	 *
	 * @param array<int, string>   $question_ids  Question IDs.
	 * @param array<string, mixed> $answers       Answer map.
	 * @param array<string, mixed> $config        Plugin config.
	 * @param array<string, string> $improvements Optional qid => copy map.
	 * @return array<int, string>
	 */
	public static function weak_factors_for_questions(
		array $question_ids,
		array $answers,
		array $config,
		array $improvements = array()
	): array {
		if ( ! $question_ids || ! $answers ) {
			return array();
		}

		if ( ! $improvements ) {
			$improvements = self::question_improvements();
		}

		$questions_by_id = array();
		foreach ( (array) ( $config['questions'] ?? array() ) as $question ) {
			if ( ! is_array( $question ) ) {
				continue;
			}
			$qid = (string) ( $question['id'] ?? '' );
			if ( $qid ) {
				$questions_by_id[ $qid ] = $question;
			}
		}

		$out = array();
		foreach ( $question_ids as $qid ) {
			$qid = (string) $qid;
			if ( ! isset( $answers[ $qid ], $questions_by_id[ $qid ] ) ) {
				continue;
			}
			$score = AIRB_Scoring::score_answer( $questions_by_id[ $qid ], $answers[ $qid ] );
			if ( $score < self::WEAK_ANSWER_MIN ) {
				continue;
			}
			$text = (string) ( $improvements[ $qid ] ?? '' );
			if ( ! $text ) {
				$text = (string) ( $questions_by_id[ $qid ]['text'] ?? '' );
			}
			if ( $text ) {
				$out[] = $text;
			}
		}

		return $out;
	}

	/**
	 * Merge weak per-question factors into a focus block when the score is below the role threshold.
	 *
	 * @param array<string, mixed> $block         Focus block from role copy.
	 * @param int                  $pct           Readiness %.
	 * @param string               $role          Role slug.
	 * @param array<int, string>   $domain_slugs  Domain slugs to scan for weak answers.
	 * @param array<string, mixed> $answers       Answer map.
	 * @param array<string, mixed> $config        Plugin config.
	 * @param array<string, mixed> $display_domains Optional display-domain defs (parent/public).
	 * @return array<string, mixed>
	 */
	public static function enrich_focus_block(
		array $block,
		int $pct,
		string $role,
		array $domain_slugs,
		array $answers,
		array $config,
		array $display_domains = array()
	): array {
		$focus_max = self::focus_max_for_role( $role );
		if ( $pct >= $focus_max || ! $answers || ! $domain_slugs ) {
			return $block;
		}

		$factors = array();
		foreach ( $domain_slugs as $slug ) {
			$slug  = (string) $slug;
			$qids  = self::question_ids_from_display_def( $display_domains, $slug );
			if ( ! $qids ) {
				$qids = self::question_ids_for_domain( $config, $slug, $role );
			}
			$factors = array_merge( $factors, self::weak_factors_for_questions( $qids, $answers, $config ) );
		}

		$factors = array_values( array_unique( array_filter( $factors ) ) );
		if ( ! $factors ) {
			return $block;
		}

		$block['likely_impact']     = $factors;
		$block['challenge_bullets'] = $factors;
		if ( empty( $block['challenge_heading'] ) ) {
			$block['challenge_heading'] = __( 'Areas to improve in this domain', 'ai-risk-benchmark' );
		}

		return $block;
	}

	/**
	 * @return array<string, string>
	 */
	public static function question_improvements(): array {
		$roles = array( 'public', 'parent', 'teacher', 'student', 'leader', 'support' );
		$out   = array();
		foreach ( $roles as $role ) {
			$method = $role . '_result_config';
			if ( ! method_exists( AIRB_Defaults::class, $method ) ) {
				continue;
			}
			$cfg = (array) AIRB_Defaults::$method();
			$out = array_merge( $out, (array) ( $cfg['question_improvements'] ?? array() ) );
		}

		return $out;
	}
}
