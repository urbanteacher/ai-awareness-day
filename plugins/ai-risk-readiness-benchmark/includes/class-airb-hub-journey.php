<?php
/**
 * Intervention hub improvement journeys — action checklists, pathways and resource ladders.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Turns hub resource pages into structured readiness mini-centres.
 */
class AIRB_Hub_Journey {

	/**
	 * Static journey config for a hub page (merged with benchmark context on the client).
	 *
	 * @return array<string, mixed>
	 */
	public static function static_config( string $page_slug, string $role, string $page_title ): array {
		$role = sanitize_key( $role );

		return array(
			'journey_title'       => self::journey_title_for_role( $role ),
			'next_step_heading'   => __( 'Your next step', 'ai-risk-benchmark' ),
			'next_step_intro'     => __( 'Based on your benchmark:', 'ai-risk-benchmark' ),
			'actions_heading'     => __( 'Recommended actions', 'ai-risk-benchmark' ),
			'support_heading'     => __( 'Need further support?', 'ai-risk-benchmark' ),
			'support_intro'       => __( 'Contact AI Awareness Day if you would like guidance, parent sessions or whole-school support.', 'ai-risk-benchmark' ),
			'ladder_heading'      => __( 'Continue learning', 'ai-risk-benchmark' ),
			'actions'             => self::page_actions( $page_slug, $role, $page_title ),
			'pathway'             => self::pathway_steps( $page_slug, $role, $page_title ),
			'resource_ladder'     => self::resource_ladder( $role, $page_slug ),
			'leading_behaviors'   => self::leading_behaviors( $role ),
			'school_participation'=> self::school_participation( $role ),
			'benchmark_url'       => AIRB_Defaults::benchmark_page_url(),
		);
	}

	/**
	 * Personalised journey context from benchmark results.
	 *
	 * @param array<string, mixed>|null $results Scored submission.
	 * @return array<string, mixed>
	 */
	public static function context_from_results( string $page_slug, string $role, ?array $results, string $page_title = '' ): array {
		$role  = sanitize_key( $role );
		$score = is_array( $results ) ? (int) ( $results['alignment_score'] ?? 0 ) : 0;
		$band  = $score > 0 ? AIRB_Scoring::readiness_band( $score ) : '';

		$focus_label = '';
		$focus_score = null;
		if ( is_array( $results ) ) {
			$weak = AIRB_Interest::weak_domain_labels( $results, $role );
			if ( $weak ) {
				$parsed      = self::parse_weak_label( (string) $weak[0] );
				$focus_label = (string) $parsed['label'];
				$focus_score = $parsed['score'];
				if ( null === $focus_score ) {
					$focus_score = self::focus_score_for_label( $results, $role, $focus_label );
				}
			}
		}

		$journey_tier = '';
		if ( 'parent' === $role && is_array( $results ) ) {
			$journey_tier = AIRB_Parent_Results::journey_tier( $score );
		}

		$show_leading = $score >= 75 || in_array( $band, array( 'strong', 'leading' ), true );

		$pathway = self::pathway_steps( $page_slug, $role, $page_title );
		foreach ( $pathway as $i => $step ) {
			if ( 'focus_improved' === ( $step['id'] ?? '' ) && $focus_label ) {
				$pathway[ $i ]['label'] = sprintf(
					/* translators: %s: focus area name */
					__( '%s improved', 'ai-risk-benchmark' ),
					$focus_label
				);
			}
		}

		return array(
			'has_benchmark'     => $score > 0,
			'alignment_score'   => $score,
			'readiness_band'    => $band,
			'readiness_label'   => $band ? AIRB_Scoring::readiness_band_label( $score ) : '',
			'focus_label'       => $focus_label,
			'focus_score'       => $focus_score,
			'journey_tier'      => $journey_tier,
			'show_leading'      => $show_leading,
			'pathway'           => $pathway,
		);
	}

	/**
	 * @return array{label:string,score:?int}
	 */
	private static function parse_weak_label( string $label ): array {
		if ( preg_match( '/^(.+?)\s*\((\d+)%/', $label, $matches ) ) {
			return array(
				'label' => trim( (string) $matches[1] ),
				'score' => (int) $matches[2],
			);
		}

		return array(
			'label' => $label,
			'score' => null,
		);
	}

	/**
	 * @param array<string, mixed> $results Results.
	 */
	private static function focus_score_for_label( array $results, string $role, string $label ): ?int {
		if ( 'parent' === $role ) {
			$display = (array) ( $results['parent_display_domains'] ?? array() );
			$cfg     = AIRB_Defaults::parent_result_config();
			$domains = (array) ( $cfg['display_domains'] ?? array() );
			foreach ( $domains as $slug => $def ) {
				if ( (string) ( $def['label'] ?? '' ) !== $label ) {
					continue;
				}
				$dom = (array) ( $display[ $slug ] ?? array() );
				if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
					return null;
				}
				$is_risk = ( 'risk' === ( $dom['metric_type'] ?? 'score' ) );
				return (int) round( (float) ( $is_risk ? ( 100 - ( $dom['risk_percentage'] ?? 0 ) ) : ( $dom['readiness_percentage'] ?? 0 ) ) );
			}
		}

		$domains = (array) ( $results['domain_scores'] ?? array() );
		foreach ( $domains as $dom ) {
			$dom = (array) $dom;
			if ( (string) ( $dom['label'] ?? '' ) === $label ) {
				return (int) round( (float) ( $dom['readiness_percentage'] ?? 0 ) );
			}
		}

		return null;
	}

	private static function journey_title_for_role( string $role ): string {
		$titles = array(
			'parent'  => __( 'AI Awareness Journey', 'ai-risk-benchmark' ),
			'teacher' => __( 'AI Readiness Journey', 'ai-risk-benchmark' ),
			'student' => __( 'AI Learning Journey', 'ai-risk-benchmark' ),
			'leader'  => __( 'School AI Readiness Journey', 'ai-risk-benchmark' ),
		);
		return $titles[ $role ] ?? __( 'AI Awareness Journey', 'ai-risk-benchmark' );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function resource_ladder( string $role, string $current_slug ): array {
		$ladders = array(
			'parent'  => array(
				array( 'slug' => 'parent-ai-safety', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'parent-deepfake-awareness', 'label' => __( 'Parent Deepfake Awareness', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'parent-ai-homework-guide', 'label' => __( 'Supporting Homework in the AI Era', 'ai-risk-benchmark' ) ),
			),
			'teacher' => array(
				array( 'slug' => 'teacher-ai-verification-framework', 'label' => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'teacher-ai-privacy-guide', 'label' => __( 'Teacher AI Privacy Guide', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'teacher-ai-assessment-guide', 'label' => __( 'Teacher AI Assessment Guide', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'teacher-ai-lesson-planning-checklist', 'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ) ),
			),
			'student' => array(
				array( 'slug' => 'student-ai-study-skills', 'label' => __( 'Student AI Study Skills', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'think-first-prompt-second', 'label' => __( 'Think First, Prompt Second', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'how-to-check-ai-answers', 'label' => __( 'Verify Before You Trust Checklist', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'student-ai-privacy-guide', 'label' => __( 'Student AI Privacy Guide', 'ai-risk-benchmark' ) ),
			),
			'leader'  => array(
				array( 'slug' => 'school-ai-governance', 'label' => __( 'School AI Governance', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'school-ai-maturity', 'label' => __( 'School AI Maturity', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'whole-school-ai-benchmark', 'label' => __( 'Whole-School AI Benchmark', 'ai-risk-benchmark' ) ),
				array( 'slug' => 'ai-awareness-day', 'label' => __( 'AI Awareness Day', 'ai-risk-benchmark' ) ),
			),
		);

		$items = $ladders[ $role ] ?? array();
		$out   = array();
		foreach ( $items as $item ) {
			$slug = (string) ( $item['slug'] ?? '' );
			$out[] = array(
				'slug'    => $slug,
				'label'   => (string) ( $item['label'] ?? '' ),
				'url'     => AIRB_Defaults::hub_page_url( $slug ),
				'current' => $slug === $current_slug,
			);
		}
		return $out;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function page_actions( string $page_slug, string $role, string $page_title ): array {
		$overrides = array(
			'parent-ai-safety' => array(
				array(
					'id'    => 'read_guide',
					'label' => __( 'Read this guide', 'ai-risk-benchmark' ),
					'auto'  => true,
				),
				array(
					'id'    => 'download_onepager',
					'label' => __( 'Download the Parent AI Safety one-pager', 'ai-risk-benchmark' ),
				),
				array(
					'id'    => 'family_conversation',
					'label' => __( 'Have one AI conversation with your child this week', 'ai-risk-benchmark' ),
				),
				array(
					'id'    => 'retake_benchmark',
					'label' => __( 'Retake the benchmark in 30 days', 'ai-risk-benchmark' ),
					'type'  => 'link',
				),
			),
			'parent-deepfake-awareness' => array(
				array( 'id' => 'read_guide', 'label' => __( 'Read this guide', 'ai-risk-benchmark' ), 'auto' => true ),
				array( 'id' => 'discuss_deepfakes', 'label' => __( 'Discuss deepfakes and AI-generated harm with your child', 'ai-risk-benchmark' ) ),
				array( 'id' => 'retake_benchmark', 'label' => __( 'Retake the benchmark in 30 days', 'ai-risk-benchmark' ), 'type' => 'link' ),
			),
			'parent-ai-homework-guide' => array(
				array( 'id' => 'read_guide', 'label' => __( 'Read this guide', 'ai-risk-benchmark' ), 'auto' => true ),
				array( 'id' => 'homework_conversation', 'label' => __( 'Ask: did AI help you think, or do the thinking for you?', 'ai-risk-benchmark' ) ),
				array( 'id' => 'retake_benchmark', 'label' => __( 'Retake the benchmark in 30 days', 'ai-risk-benchmark' ), 'type' => 'link' ),
			),
		);

		if ( isset( $overrides[ $page_slug ] ) ) {
			return self::with_benchmark_links( $overrides[ $page_slug ] );
		}

		return self::default_actions( $role, $page_title );
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	private static function default_actions( string $role, string $page_title ): array {
		$actions = array(
			array(
				'id'    => 'read_guide',
				'label' => $page_title
					? sprintf(
						/* translators: %s: page title */
						__( 'Read %s', 'ai-risk-benchmark' ),
						$page_title
					)
					: __( 'Read this guide', 'ai-risk-benchmark' ),
				'auto'  => true,
			),
			array(
				'id'    => 'download_tool',
				'label' => __( 'Download the resource from this page', 'ai-risk-benchmark' ),
			),
		);

		switch ( $role ) {
			case 'parent':
				$actions[] = array(
					'id'    => 'family_conversation',
					'label' => __( 'Have one AI conversation at home this week', 'ai-risk-benchmark' ),
				);
				break;
			case 'teacher':
				$actions[] = array(
					'id'    => 'apply_classroom',
					'label' => __( 'Apply one idea from this page in your next lesson', 'ai-risk-benchmark' ),
				);
				break;
			case 'student':
				$actions[] = array(
					'id'    => 'weekly_challenge',
					'label' => __( 'Complete one task without AI this week', 'ai-risk-benchmark' ),
				);
				break;
			case 'leader':
				$actions[] = array(
					'id'    => 'share_slt',
					'label' => __( 'Share one insight from this page with your SLT or governors', 'ai-risk-benchmark' ),
				);
				break;
		}

		$actions[] = array(
			'id'    => 'retake_benchmark',
			'label' => __( 'Retake the benchmark to track improvement', 'ai-risk-benchmark' ),
			'type'  => 'link',
		);

		return self::with_benchmark_links( $actions );
	}

	/**
	 * @param array<int, array<string, mixed>> $actions Actions.
	 * @return array<int, array<string, mixed>>
	 */
	private static function with_benchmark_links( array $actions ): array {
		$url = AIRB_Defaults::benchmark_page_url();
		foreach ( $actions as $i => $action ) {
			if ( 'link' === ( $action['type'] ?? '' ) && 'retake_benchmark' === ( $action['id'] ?? '' ) ) {
				$actions[ $i ]['url'] = $url;
			}
		}
		return $actions;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function pathway_steps( string $page_slug, string $role, string $page_title ): array {
		$resource_label = $page_title ?: __( 'This resource', 'ai-risk-benchmark' );

		$middle = array(
			'parent'  => array( 'id' => 'conversation', 'label' => __( 'One family AI conversation', 'ai-risk-benchmark' ) ),
			'teacher' => array( 'id' => 'apply', 'label' => __( 'Apply in the classroom', 'ai-risk-benchmark' ) ),
			'student' => array( 'id' => 'challenge', 'label' => __( 'Complete the weekly challenge', 'ai-risk-benchmark' ) ),
			'leader'  => array( 'id' => 'share', 'label' => __( 'Share with SLT or governors', 'ai-risk-benchmark' ) ),
		);

		$mid = $middle[ $role ] ?? $middle['parent'];

		return array(
			array(
				'id'    => 'benchmark',
				'label' => __( 'Benchmark completed', 'ai-risk-benchmark' ),
				'auto'  => 'benchmark',
			),
			array(
				'id'    => 'resource',
				'label' => $resource_label,
				'auto'  => true,
			),
			$mid,
			array(
				'id'    => 'focus_improved',
				'label' => __( 'Focus area improved', 'ai-risk-benchmark' ),
			),
			array(
				'id'    => 'retake',
				'label' => __( 'Retake benchmark', 'ai-risk-benchmark' ),
				'type'  => 'link',
				'url'   => AIRB_Defaults::benchmark_page_url(),
			),
		);
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function leading_behaviors( string $role ): ?array {
		$blocks = array(
			'parent' => array(
				'title'     => __( 'What leading parents typically do', 'ai-risk-benchmark' ),
				'min_score' => 75,
				'items'     => array(
					__( 'Talk about AI regularly', 'ai-risk-benchmark' ),
					__( 'Know which tools their child uses', 'ai-risk-benchmark' ),
					__( 'Discuss privacy and what not to share', 'ai-risk-benchmark' ),
					__( 'Encourage verification before trusting AI answers', 'ai-risk-benchmark' ),
					__( 'Focus on learning rather than shortcuts', 'ai-risk-benchmark' ),
				),
			),
			'teacher' => array(
				'title'     => __( 'What leading teachers typically do', 'ai-risk-benchmark' ),
				'min_score' => 75,
				'items'     => array(
					__( 'Verify AI outputs before classroom use', 'ai-risk-benchmark' ),
					__( 'Model honest AI use for pupils', 'ai-risk-benchmark' ),
					__( 'Protect pupil data in AI tools', 'ai-risk-benchmark' ),
					__( 'Design tasks AI cannot simply complete', 'ai-risk-benchmark' ),
				),
			),
			'student' => array(
				'title'     => __( 'What confident learners typically do', 'ai-risk-benchmark' ),
				'min_score' => 60,
				'items'     => array(
					__( 'Attempt work before using AI', 'ai-risk-benchmark' ),
					__( 'Verify answers against trusted sources', 'ai-risk-benchmark' ),
					__( 'Protect personal information', 'ai-risk-benchmark' ),
					__( 'Use AI to explain — not to replace thinking', 'ai-risk-benchmark' ),
				),
			),
			'leader' => array(
				'title'     => __( 'What leading schools typically do', 'ai-risk-benchmark' ),
				'min_score' => 75,
				'items'     => array(
					__( 'Benchmark all four stakeholder groups', 'ai-risk-benchmark' ),
					__( 'Maintain clear AI policies and oversight', 'ai-risk-benchmark' ),
					__( 'Train staff on verification and safeguarding', 'ai-risk-benchmark' ),
					__( 'Review AI readiness annually', 'ai-risk-benchmark' ),
				),
			),
		);

		return $blocks[ $role ] ?? null;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function school_participation( string $role ): array {
		$share_interest = array(
			'parent'  => 'parent_share_with_school',
			'teacher' => 'whole_school_benchmark',
			'student' => 'student_share_school',
			'leader'  => 'whole_school_benchmark',
		);

		return array(
			'title'       => __( 'Help build your school\'s AI readiness picture', 'ai-risk-benchmark' ),
			'intro'       => __( 'When parents, students, teachers and leaders all complete the benchmark, schools can identify:', 'ai-risk-benchmark' ),
			'items'       => array(
				__( 'AI dependency', 'ai-risk-benchmark' ),
				__( 'Privacy risks', 'ai-risk-benchmark' ),
				__( 'Verification habits', 'ai-risk-benchmark' ),
				__( 'Safeguarding awareness', 'ai-risk-benchmark' ),
				__( 'Training needs', 'ai-risk-benchmark' ),
			),
			'cta_label'   => __( 'Share my results with my school', 'ai-risk-benchmark' ),
			'cta_prefill' => $share_interest[ $role ] ?? 'whole_school_benchmark',
		);
	}
}
