<?php
/**
 * Certificate evidence validation and quality scoring.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Evidence-backed certificate unlock rules.
 */
class AIRB_Certificate_Evidence {

	/** Minimum benchmark alignment score required to unlock. */
	const SCORE_THRESHOLD = 70;

	/** Minimum combined reflection length (characters). */
	const MIN_REFLECTION_CHARS = 120;

	/** Minimum self-declared action length (characters). */
	const MIN_SELF_DECLARED_CHARS = 40;

	/** Minimum quality score required to unlock. */
	const QUALITY_THRESHOLD = 70;

	/**
	 * AI Awareness Day theme slugs.
	 *
	 * @return string[]
	 */
	public static function theme_slugs(): array {
		return array( 'smart', 'creative', 'responsible', 'future', 'safe' );
	}

	/**
	 * Theme labels for UI.
	 *
	 * @return array<string, string>
	 */
	public static function theme_labels(): array {
		return array(
			'smart'       => __( 'Smart', 'ai-risk-benchmark' ),
			'creative'    => __( 'Creative', 'ai-risk-benchmark' ),
			'responsible' => __( 'Responsible', 'ai-risk-benchmark' ),
			'future'      => __( 'Future', 'ai-risk-benchmark' ),
			'safe'        => __( 'Safe', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Front-end unlock configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function front_config(): array {
		$themes = array();
		foreach ( self::theme_slugs() as $slug ) {
			$themes[] = array(
				'slug'  => $slug,
				'label' => self::theme_labels()[ $slug ] ?? ucfirst( $slug ),
			);
		}

		return array(
			'score_threshold'      => self::SCORE_THRESHOLD,
			'min_reflection_chars' => self::MIN_REFLECTION_CHARS,
			'quality_threshold'    => self::QUALITY_THRESHOLD,
			'themes'               => $themes,
			'tier_labels'          => array(
				'needs_more_detail'   => __( 'Needs more detail', 'ai-risk-benchmark' ),
				'likely_valid'        => __( 'Likely valid', 'ai-risk-benchmark' ),
				'strong_evidence'     => __( 'Strong evidence', 'ai-risk-benchmark' ),
				'needs_manual_review' => __( 'Needs manual review', 'ai-risk-benchmark' ),
			),
			'prompts'              => array(
				'action' => __( 'What did you do?', 'ai-risk-benchmark' ),
				'change' => __( 'What changed in your practice, lesson, discussion, or understanding?', 'ai-risk-benchmark' ),
				'link'   => __( 'Optional evidence link (lesson slide, activity, policy, CPD note, etc.)', 'ai-risk-benchmark' ),
			),
			'unlock_intro'         => __( 'Reach the benchmark score threshold and complete one of the evidence options below.', 'ai-risk-benchmark' ),
			'pathways'             => array(
				array(
					'key'   => 'self_declared',
					'label' => __( 'Self-declared action', 'ai-risk-benchmark' ),
					'hint'  => __( 'Choose a theme and describe what you did in at least 40 characters.', 'ai-risk-benchmark' ),
				),
				array(
					'key'   => 'structured_reflection',
					'label' => __( 'Structured reflection', 'ai-risk-benchmark' ),
					'hint'  => __( 'Concrete action verb plus at least 120 characters across your answers.', 'ai-risk-benchmark' ),
				),
				array(
					'key'   => 'evidence_link',
					'label' => __( 'Evidence link', 'ai-risk-benchmark' ),
					'hint'  => __( 'Add a link to a slide, activity, policy note, or CPD artefact.', 'ai-risk-benchmark' ),
				),
				array(
					'key'   => 'quality_validated',
					'label' => __( 'Quality-checked evidence', 'ai-risk-benchmark' ),
					'hint'  => __( 'Reach an evidence quality score of at least 70.', 'ai-risk-benchmark' ),
				),
			),
		);
	}

	/**
	 * Validate theme slug.
	 */
	public static function is_valid_theme( string $theme ): bool {
		return in_array( sanitize_key( $theme ), self::theme_slugs(), true );
	}

	/**
	 * Score submitted evidence.
	 *
	 * @param string $role   Benchmark role.
	 * @param string $theme  Theme slug.
	 * @param string $action What they did.
	 * @param string $change What changed.
	 * @param string $link            Optional evidence URL.
	 * @param int    $benchmark_score Saved benchmark alignment score.
	 * @return array<string, mixed>
	 */
	public static function assess( string $role, string $theme, string $action, string $change, string $link = '', int $benchmark_score = 0 ): array {
		$role   = AIRB_Certificate_Copy::normalize_role( $role );
		$theme  = sanitize_key( $theme );
		$action = trim( wp_strip_all_tags( $action ) );
		$change = trim( wp_strip_all_tags( $change ) );
		$link   = esc_url_raw( trim( $link ) );
		$combined = $action . ' ' . $change;

		$checks   = array();
		$messages = array();
		$score    = 0;

		$theme_ok = self::is_valid_theme( $theme );
		$checks['theme'] = $theme_ok;
		if ( $theme_ok ) {
			$score += 20;
		} else {
			$messages[] = __( 'Choose one AI Awareness Day theme.', 'ai-risk-benchmark' );
		}

		$verb_ok = self::has_concrete_action( $action );
		$checks['concrete_action'] = $verb_ok;
		if ( $verb_ok ) {
			$score += 25;
		} else {
			$messages[] = __( 'Describe a specific action with a clear activity (for example: taught, discussed, reviewed, planned, modelled).', 'ai-risk-benchmark' );
		}

		$reflection_len = strlen( $combined );
		$reflection_ok  = $reflection_len >= self::MIN_REFLECTION_CHARS;
		$checks['reflection_length'] = $reflection_ok;
		if ( $reflection_ok ) {
			$score += 20;
		} else {
			$messages[] = sprintf(
				/* translators: 1: minimum characters, 2: current count */
				__( 'Add more detail — at least %1$d characters across your answers (currently %2$d).', 'ai-risk-benchmark' ),
				self::MIN_REFLECTION_CHARS,
				$reflection_len
			);
		}

		$involved_ok = self::mentions_involvement( $combined );
		$checks['involvement'] = $involved_ok;
		if ( $involved_ok ) {
			$score += 15;
		} else {
			$messages[] = __( 'Say who was involved (for example: pupils, colleagues, parents, learners, or your class).', 'ai-risk-benchmark' );
		}

		$generic = self::is_generic( $combined );
		$checks['not_generic'] = ! $generic;
		if ( ! $generic ) {
			$score += 10;
		} else {
			$messages[] = __( 'Your answer looks too generic — add a specific example from your context.', 'ai-risk-benchmark' );
			$score = max( 0, $score - 15 );
		}

		$role_ok = self::matches_role( $role, $combined );
		$checks['role_match'] = $role_ok;
		if ( $role_ok ) {
			$score += 10;
		} else {
			$messages[] = __( 'Make the example clearly relevant to your benchmark role.', 'ai-risk-benchmark' );
		}

		$manual_review = false;
		if ( $link ) {
			$manual_review = true;
			$score         = min( 100, $score + 5 );
			$messages[]    = __( 'Evidence link received — this may be reviewed by AI Awareness Day.', 'ai-risk-benchmark' );
		}

		$score = max( 0, min( 100, $score ) );
		$tier  = self::tier_for_score( $score, $manual_review );

		$pathways = self::pathways( $theme_ok, $action, $combined, $link, $verb_ok, $reflection_ok, $score );
		$evidence_satisfied = $pathways['self_declared'] || $pathways['structured_reflection'] || $pathways['evidence_link'] || $pathways['quality_validated'];
		$score_eligible     = $benchmark_score >= self::SCORE_THRESHOLD;
		$can_unlock         = $score_eligible && $theme_ok && $evidence_satisfied;

		if ( ! $score_eligible ) {
			array_unshift(
				$messages,
				sprintf(
					/* translators: 1: required score, 2: current score */
					__( 'Benchmark score must be at least %1$d%% (currently %2$d%%).', 'ai-risk-benchmark' ),
					self::SCORE_THRESHOLD,
					max( 0, min( 100, $benchmark_score ) )
				)
			);
		} elseif ( ! $evidence_satisfied ) {
			$messages[] = __( 'Complete one evidence option: self-declared action, structured reflection, evidence link, or quality score of at least 70.', 'ai-risk-benchmark' );
		}

		return array(
			'quality_score'      => $score,
			'quality_tier'       => $tier,
			'tier_label'         => self::front_config()['tier_labels'][ $tier ] ?? $tier,
			'can_unlock'         => $can_unlock,
			'score_eligible'     => $score_eligible,
			'evidence_satisfied' => $evidence_satisfied,
			'pathways'           => $pathways,
			'checks'             => $checks,
			'messages'           => array_values( array_unique( $messages ) ),
			'reflection_chars'   => $reflection_len,
			'manual_review'      => $manual_review,
		);
	}

	/**
	 * Evidence pathway checks — unlock requires any one (plus theme + benchmark score).
	 *
	 * @return array<string, bool>
	 */
	private static function pathways( bool $theme_ok, string $action, string $combined, string $link, bool $verb_ok, bool $reflection_ok, int $quality_score ): array {
		return array(
			'self_declared'         => $theme_ok && strlen( $action ) >= self::MIN_SELF_DECLARED_CHARS && ! self::is_generic( $action ),
			'structured_reflection' => $theme_ok && $verb_ok && $reflection_ok,
			'evidence_link'         => $theme_ok && self::is_valid_evidence_link( $link ),
			'quality_validated'     => $theme_ok && $quality_score >= self::QUALITY_THRESHOLD,
		);
	}

	/**
	 * Whether a submitted evidence URL is usable.
	 */
	private static function is_valid_evidence_link( string $link ): bool {
		$link = esc_url_raw( trim( $link ) );
		if ( '' === $link ) {
			return false;
		}
		$scheme = wp_parse_url( $link, PHP_URL_SCHEME );
		return in_array( $scheme, array( 'http', 'https' ), true );
	}

	/**
	 * Map score to tier slug.
	 */
	private static function tier_for_score( int $score, bool $manual_review ): string {
		if ( $manual_review && $score >= self::QUALITY_THRESHOLD ) {
			return 'needs_manual_review';
		}
		if ( $score >= 85 ) {
			return 'strong_evidence';
		}
		if ( $score >= self::QUALITY_THRESHOLD ) {
			return 'likely_valid';
		}
		return 'needs_more_detail';
	}

	/**
	 * Whether text includes a concrete activity verb.
	 */
	private static function has_concrete_action( string $text ): bool {
		$text = strtolower( $text );
		if ( strlen( $text ) < 12 ) {
			return false;
		}

		$verbs = array(
			'taught', 'teach', 'led', 'lead', 'discussed', 'discuss', 'ran', 'run', 'created', 'create',
			'designed', 'design', 'updated', 'update', 'shared', 'share', 'introduced', 'introduce',
			'modelled', 'modeled', 'model', 'facilitated', 'facilitate', 'delivered', 'deliver',
			'implemented', 'implement', 'reviewed', 'review', 'checked', 'check', 'verified', 'verify',
			'adapted', 'adapt', 'planned', 'plan', 'organised', 'organized', 'organize', 'demonstrated',
			'demonstrate', 'practised', 'practiced', 'practice', 'applied', 'apply', 'completed', 'complete',
			'started', 'start', 'built', 'build', 'wrote', 'write', 'presented', 'present', 'coached',
			'coach', 'supported', 'support', 'guided', 'guide', 'explored', 'explore', 'trained', 'train',
			'attended', 'attend', 'published', 'publish', 'added', 'add', 'revised', 'revise', 'tested',
			'test', 'used', 'use', 'tried', 'try', 'asked', 'ask', 'explained', 'explain', 'reflected',
			'reflect', 'talked', 'talk', 'showed', 'show', 'helped', 'help', 'changed', 'change', 'improved',
			'improve', 'practised', 'practiced', 'rehearsed', 'rehearse', 'drafted', 'draft', 'circulated',
		);

		foreach ( $verbs as $verb ) {
			if ( preg_match( '/\b' . preg_quote( $verb, '/' ) . '\b/i', $text ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether reflection mentions people involved.
	 */
	private static function mentions_involvement( string $text ): bool {
		$needles = array(
			'pupil', 'student', 'learner', 'class', 'colleague', 'staff', 'parent', 'carer', 'team',
			'year ', 'children', 'child', 'family', 'form', 'tutor', 'department', 'slt', 'governor',
			'trust', 'school', 'office', 'reception', 'my class', 'our school', 'my child', 'young people',
		);
		$lower = strtolower( $text );
		foreach ( $needles as $needle ) {
			if ( str_contains( $lower, $needle ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Detect overly generic one-line claims.
	 */
	private static function is_generic( string $text ): bool {
		$lower = strtolower( preg_replace( '/\s+/', ' ', trim( $text ) ) );
		if ( strlen( $lower ) < 40 ) {
			return true;
		}

		$generic_only = array(
			'i used ai',
			'used chatgpt',
			'used ai tools',
			'completed the benchmark',
			'did ai awareness day',
			'participated in ai awareness day',
			'learned about ai',
			'explored ai',
		);

		foreach ( $generic_only as $phrase ) {
			if ( $lower === $phrase || $lower === $phrase . '.' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Basic role-context keyword check.
	 */
	private static function matches_role( string $role, string $text ): bool {
		$map = array(
			'teacher' => array( 'lesson', 'class', 'pupil', 'student', 'classroom', 'teaching', 'marking', 'subject', 'curriculum' ),
			'student' => array( 'study', 'homework', 'assignment', 'revision', 'learning', 'exam', 'coursework', 'school work' ),
			'parent'  => array( 'child', 'home', 'family', 'homework', 'conversation', 'carer', 'parent', 'kitchen table' ),
			'leader'  => array( 'staff', 'policy', 'governance', 'trust', 'slt', 'safeguarding', 'leadership', 'whole school' ),
			'support' => array( 'office', 'admin', 'data', 'reception', 'operations', 'hr', 'finance', 'records' ),
			'public'  => array( 'personal', 'privacy', 'online', 'family', 'myself', 'home', 'account', 'password' ),
		);

		$needles = $map[ $role ] ?? array();
		if ( ! $needles ) {
			return strlen( $text ) >= 40;
		}

		$lower = strtolower( $text );
		foreach ( $needles as $needle ) {
			if ( str_contains( $lower, $needle ) ) {
				return true;
			}
		}

		return false;
	}
}
