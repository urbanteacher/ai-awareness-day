<?php
/**
 * Post-benchmark interest capture — replaces mailto CTAs with a scored enquiry form.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Interest form options and notification email.
 */
class AIRB_Interest {

	/**
	 * Roles that can submit the interest form.
	 *
	 * @return array<int, string>
	 */
	public static function supported_roles(): array {
		return array( 'teacher', 'leader', 'parent', 'student', 'support_staff' );
	}

	/**
	 * Anchor for the in-results interest form.
	 */
	public static function form_anchor( string $prefill = '' ): string {
		return AIRB_Defaults::interest_form_url( $prefill );
	}

	/**
	 * Map gateway / next-step keys to interest checkbox slugs.
	 *
	 * @return array<string, string>
	 */
	public static function gateway_prefill_map(): array {
		return array(
			'book_cpd'               => 'whole_school_cpd',
			'book_consultation'      => 'governance_review',
			'aad_day'                => 'ai_awareness_day',
			'policy_support'         => 'policy_support',
			'ai_awareness_day'       => 'ai_awareness_day',
			'governance_review'      => 'governance_review',
			'parent_sessions'        => 'parent_sessions',
			'parent_school_take_part' => 'parent_school_take_part',
			'student_share_school'   => 'student_share_school',
			'support_data_checklist' => 'support_data_checklist',
			'support_verification'   => 'support_verification_resources',
			'teacher_awareness'      => 'teacher_awareness',
			'teacher_champion'       => 'ai_awareness_day',
			'teacher_activity_day'   => 'teacher_activity_day',
			'teacher_learn_ai'       => 'teacher_learn_ai',
			'whole_school_cpd'       => 'whole_school_cpd',
			'whole_school_benchmark' => 'whole_school_benchmark',
			'support_cpd'            => 'support_staff_cpd',
		);
	}

	/**
	 * Interest options shown after benchmark results.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function option_catalog(): array {
		return array(
			// Staff — whole-school support.
			'whole_school_cpd' => array(
				'label'       => __( 'Whole-school staff CPD / training', 'ai-risk-benchmark' ),
				'description' => __( 'Targeted training on your highest-risk domains from this audit.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher', 'leader', 'support_staff' ),
			),
			'ai_awareness_day' => array(
				'label'       => __( 'Plan AI Awareness Day at our school', 'ai-risk-benchmark' ),
				'description' => __( 'Whole-school programme for leaders, staff, students and parents.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher', 'leader' ),
			),
			'governance_review' => array(
				'label'       => __( 'Governance review or free readiness consultation', 'ai-risk-benchmark' ),
				'description' => __( 'Walk through your scores, DfE alignment and recommended next steps.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher', 'leader' ),
			),
			'policy_support' => array(
				'label'       => __( 'AI policy support (DfE template & guidance)', 'ai-risk-benchmark' ),
				'description' => __( 'Help adapting the official DfE AI policy template to your school.', 'ai-risk-benchmark' ),
				'roles'       => array( 'leader', 'teacher' ),
			),
			'whole_school_benchmark' => array(
				'label'       => __( 'Roll out the benchmark to all staff, students & parents', 'ai-risk-benchmark' ),
				'description' => __( 'Build a complete whole-school AI readiness picture.', 'ai-risk-benchmark' ),
				'roles'       => array( 'leader', 'teacher', 'support_staff' ),
			),
			'parent_sessions' => array(
				'label'       => __( 'Parent awareness sessions', 'ai-risk-benchmark' ),
				'description' => __( 'Support families with AI safety, homework and online harm.', 'ai-risk-benchmark' ),
				'roles'       => array( 'leader', 'teacher' ),
			),
			'teacher_activity_day' => array(
				'label'       => __( 'Run an AI Awareness Day activity in my class', 'ai-risk-benchmark' ),
				'description' => __( 'Literacy display board materials and classroom activities aligned to the campaign.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher' ),
			),
			'teacher_awareness' => array(
				'label'       => __( 'Book an AI awareness session', 'ai-risk-benchmark' ),
				'description' => __( 'Foundational session covering safe adoption, verification and data protection basics.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher' ),
			),
			'teacher_champion' => array(
				'label'       => __( 'Join the AI Champion pathway', 'ai-risk-benchmark' ),
				'description' => __( 'Support colleagues, shape school policy and lead responsible AI practice.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher' ),
			),
			'teacher_learn_ai' => array(
				'label'       => __( 'Learn more about AI as a teacher', 'ai-risk-benchmark' ),
				'description' => __( 'Guidance, CPD and practical classroom support.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher' ),
			),
			'school_leader_staff_activity' => array(
				'label'       => __( 'Whole-staff AI Awareness Day activity', 'ai-risk-benchmark' ),
				'description' => __( 'Staff-wide sessions and planning support for your school.', 'ai-risk-benchmark' ),
				'roles'       => array( 'leader' ),
			),
			'school_leader_school_promote' => array(
				'label'       => __( 'Promote AI Awareness Day across our school', 'ai-risk-benchmark' ),
				'description' => __( 'Logos, communications and whole-school promotion kit.', 'ai-risk-benchmark' ),
				'roles'       => array( 'leader' ),
			),
			// Parent / carer.
			'parent_support_child' => array(
				'label'       => __( 'Support my child with AI at home', 'ai-risk-benchmark' ),
				'description' => __( 'Practical guidance on privacy, homework, deepfakes and healthy habits.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			'parent_learn_ai' => array(
				'label'       => __( 'Learn more about AI as a parent / carer', 'ai-risk-benchmark' ),
				'description' => __( 'Build confidence to guide your child\'s AI use safely.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			'parent_resources' => array(
				'label'       => __( 'Parent guides, webinars and safety resources', 'ai-risk-benchmark' ),
				'description' => __( 'Matched to the priority areas from your audit.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			'parent_school_take_part' => array(
				'label'       => __( 'Ask my child\'s school to take part in AI Awareness Day', 'ai-risk-benchmark' ),
				'description' => __( 'We can contact your school or support you to share your results.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			'parent_share_with_school' => array(
				'label'       => __( 'Help build my school\'s AI readiness picture', 'ai-risk-benchmark' ),
				'description' => __( 'When parents, students, teachers and leaders all complete the benchmark, schools see the whole picture — dependency, privacy, verification and training needs.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			'parent_ambassador' => array(
				'label'       => __( 'Become a parent ambassador at my school', 'ai-risk-benchmark' ),
				'description' => __( 'Help other families understand AI safety and encourage school-wide participation.', 'ai-risk-benchmark' ),
				'roles'       => array( 'parent' ),
			),
			// Student.
			'student_learn_ai' => array(
				'label'       => __( 'Learn more about using AI safely', 'ai-risk-benchmark' ),
				'description' => __( 'Resources on verification, privacy and honest study habits.', 'ai-risk-benchmark' ),
				'roles'       => array( 'student' ),
			),
			'student_share_school' => array(
				'label'       => __( 'Share my results with my school', 'ai-risk-benchmark' ),
				'description' => __( 'Help teachers understand how pupils are using AI.', 'ai-risk-benchmark' ),
				'roles'       => array( 'student' ),
			),
			'student_school_programme' => array(
				'label'       => __( 'Ask my school about AI Awareness Day for all pupils', 'ai-risk-benchmark' ),
				'description' => __( 'Whole-school benchmark and awareness activities.', 'ai-risk-benchmark' ),
				'roles'       => array( 'student' ),
			),
			'student_teacher_help' => array(
				'label'       => __( 'Talk to a teacher about my results', 'ai-risk-benchmark' ),
				'description' => __( 'Get support improving the areas flagged in your audit.', 'ai-risk-benchmark' ),
				'roles'       => array( 'student' ),
			),
			// Education support staff (reception, office, HR, finance, exams, data, IT).
			'support_data_checklist' => array(
				'label'       => __( 'Data protection checklist & compliance resources', 'ai-risk-benchmark' ),
				'description' => __( 'DfE-aligned guidance for handling pupil data, records and AI tools in admin roles.', 'ai-risk-benchmark' ),
				'roles'       => array( 'support_staff' ),
			),
			'support_verification_resources' => array(
				'label'       => __( 'Verification & oversight resources for support staff', 'ai-risk-benchmark' ),
				'description' => __( 'Practical checks when using AI for communications, reports, scheduling or records.', 'ai-risk-benchmark' ),
				'roles'       => array( 'support_staff' ),
			),
			'support_staff_cpd' => array(
				'label'       => __( 'Staff CPD for non-teaching roles', 'ai-risk-benchmark' ),
				'description' => __( 'Training focused on privacy, oversight and safe AI use in school operations.', 'ai-risk-benchmark' ),
				'roles'       => array( 'support_staff' ),
			),
			'support_school_rollout' => array(
				'label'       => __( 'Help my school run the benchmark for all roles', 'ai-risk-benchmark' ),
				'description' => __( 'Encourage teachers, leaders, students and parents to complete the audit.', 'ai-risk-benchmark' ),
				'roles'       => array( 'support_staff' ),
			),
			'further_information' => array(
				'label'       => __( 'Further information about AI Awareness Day', 'ai-risk-benchmark' ),
				'description' => __( 'General enquiry — we will respond by email.', 'ai-risk-benchmark' ),
				'roles'       => array( 'teacher', 'leader', 'parent', 'student', 'support_staff' ),
			),
		);
	}

	/**
	 * Options for a role (for front-end config).
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function options_for_role( string $role, string $tier = '' ): array {
		$role = sanitize_key( $role );
		$tier = sanitize_key( $tier );

		$role_order = array(
			'teacher' => array( 'teacher_awareness', 'teacher_activity_day', 'whole_school_cpd', 'whole_school_benchmark', 'ai_awareness_day' ),
			'leader'  => array( 'whole_school_benchmark', 'governance_review', 'whole_school_cpd', 'ai_awareness_day' ),
			'parent'  => array(
				'high'   => array( 'parent_share_with_school', 'parent_school_take_part', 'parent_ambassador' ),
				'medium' => array( 'parent_resources', 'parent_learn_ai', 'parent_share_with_school' ),
				'low'    => array( 'parent_learn_ai', 'parent_support_child', 'parent_school_take_part' ),
			),
			'student' => array(
				'student_share_school', 'student_school_programme', 'student_learn_ai',
			),
			'support_staff' => array(
				'support_data_checklist', 'support_verification_resources', 'support_staff_cpd', 'support_school_rollout', 'further_information',
			),
		);

		$label_overrides = array(
			'teacher' => array(
				'teacher_awareness'      => array(
					'label'       => __( 'Book an AI awareness session', 'ai-risk-benchmark' ),
					'description' => __( 'Foundational CPD covering safe adoption, verification and data protection basics.', 'ai-risk-benchmark' ),
				),
				'teacher_activity_day'   => array(
					'label'       => __( 'I want to create a literacy display board', 'ai-risk-benchmark' ),
					'description' => __( 'Printable materials and prompts to build an AI literacy display in your classroom or corridor.', 'ai-risk-benchmark' ),
				),
				'whole_school_cpd'       => array(
					'label'       => __( 'I want CPD', 'ai-risk-benchmark' ),
					'description' => __( 'Staff training focused on your highest-risk domains from this audit.', 'ai-risk-benchmark' ),
				),
				'whole_school_benchmark' => array(
					'label'       => __( 'I want my school to run the benchmark', 'ai-risk-benchmark' ),
					'description' => __( 'Roll out to staff, students, parents and leaders for a whole-school picture.', 'ai-risk-benchmark' ),
				),
				'ai_awareness_day'       => array(
					'label'       => __( 'Tell me about AI Awareness Day', 'ai-risk-benchmark' ),
					'description' => __( 'Whole-school programme for teachers, leaders, students and parents.', 'ai-risk-benchmark' ),
				),
			),
			'support_staff' => array(
				'support_data_checklist' => array(
					'label'       => __( 'Send me the data protection checklist', 'ai-risk-benchmark' ),
					'description' => __( 'Matched to privacy and data-handling gaps from your audit.', 'ai-risk-benchmark' ),
				),
				'support_verification_resources' => array(
					'label'       => __( 'Send verification & oversight resources', 'ai-risk-benchmark' ),
					'description' => __( 'Checklists for reviewing AI-generated communications, records and reports.', 'ai-risk-benchmark' ),
				),
				'support_staff_cpd' => array(
					'label'       => __( 'Book CPD for support staff', 'ai-risk-benchmark' ),
					'description' => __( 'Training for reception, office, HR, finance, exams, data and IT teams.', 'ai-risk-benchmark' ),
				),
				'support_school_rollout' => array(
					'label'       => __( 'Help roll out the benchmark at my school', 'ai-risk-benchmark' ),
					'description' => __( 'Materials to encourage all roles to complete the audit.', 'ai-risk-benchmark' ),
				),
			),
			'leader' => array(
				'whole_school_benchmark' => array(
					'label'       => __( 'Roll out the benchmark to all staff, students & parents', 'ai-risk-benchmark' ),
					'description' => __( 'Build a complete whole-school AI readiness picture.', 'ai-risk-benchmark' ),
				),
				'governance_review'      => array(
					'label'       => __( 'Book a governance review or consultation', 'ai-risk-benchmark' ),
					'description' => __( 'Walk through your scores, readiness alignment and recommended next steps.', 'ai-risk-benchmark' ),
				),
				'whole_school_cpd'       => array(
					'label'       => __( 'Book whole-school CPD', 'ai-risk-benchmark' ),
					'description' => __( 'Targeted training on your highest-risk domains from school-wide results.', 'ai-risk-benchmark' ),
				),
				'ai_awareness_day'       => array(
					'label'       => __( 'Plan AI Awareness Day at our school', 'ai-risk-benchmark' ),
					'description' => __( 'Whole-school programme for leaders, staff, students and parents.', 'ai-risk-benchmark' ),
				),
			),
		);

		if ( isset( $role_order[ $role ] ) ) {
			$catalog = self::option_catalog();
			$out     = array();
			$order   = $role_order[ $role ];
			if ( 'parent' === $role && $tier && isset( $order[ $tier ] ) ) {
				$order = (array) $order[ $tier ];
			} elseif ( 'parent' === $role ) {
				$order = (array) ( $order['medium'] ?? array() );
			}
			foreach ( $order as $slug ) {
				if ( ! isset( $catalog[ $slug ] ) ) {
					continue;
				}
				$def  = (array) $catalog[ $slug ];
				$item = array(
					'slug'        => $slug,
					'label'       => (string) ( $def['label'] ?? '' ),
					'description' => (string) ( $def['description'] ?? '' ),
				);
				if ( isset( $label_overrides[ $role ][ $slug ] ) ) {
					$item = array_merge( $item, $label_overrides[ $role ][ $slug ] );
				}
				$out[] = $item;
			}
			return $out;
		}

		$out = array();
		foreach ( self::option_catalog() as $slug => $def ) {
			$roles = (array) ( $def['roles'] ?? array() );
			if ( ! in_array( $role, $roles, true ) ) {
				continue;
			}
			$out[] = array(
				'slug'        => $slug,
				'label'       => (string) ( $def['label'] ?? '' ),
				'description' => (string) ( $def['description'] ?? '' ),
			);
		}
		return $out;
	}

	/**
	 * Stakeholder role options (staff interest form).
	 *
	 * @return array<string, string>
	 */
	public static function stakeholder_role_options(): array {
		return array(
			'teacher'        => __( 'Teacher', 'ai-risk-benchmark' ),
			'middle_leader'  => __( 'Middle Leader', 'ai-risk-benchmark' ),
			'senior_leader'  => __( 'Senior Leader', 'ai-risk-benchmark' ),
			'mat_leader'     => __( 'MAT Leader', 'ai-risk-benchmark' ),
			'governor'       => __( 'Governor', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Role-specific form copy.
	 *
	 * @return array<string, string>
	 */
	public static function form_labels( string $role = '' ): array {
		$role = sanitize_key( $role );
		$base = array(
			'score_label'  => __( 'Your readiness score', 'ai-risk-benchmark' ),
			'weak_label'   => __( 'Priority areas from your audit', 'ai-risk-benchmark' ),
			'interests'    => __( 'What would you like next?', 'ai-risk-benchmark' ),
			'message'      => __( 'Anything else we should know?', 'ai-risk-benchmark' ),
			'submit'       => __( 'Send my request', 'ai-risk-benchmark' ),
			'success'      => __( 'Thank you — we have received your request and will be in touch shortly.', 'ai-risk-benchmark' ),
			'name'         => __( 'Your name', 'ai-risk-benchmark' ),
			'email'        => __( 'Email address', 'ai-risk-benchmark' ),
			'school'       => __( 'School / trust name', 'ai-risk-benchmark' ),
			'child_school' => __( 'Child\'s school', 'ai-risk-benchmark' ),
			'email_hint'   => '',
		);

		switch ( $role ) {
			case 'parent':
				return array_merge(
					$base,
					array(
						'heading'      => __( 'Get support after your parent audit', 'ai-risk-benchmark' ),
						'intro'        => __( 'Your results are above. Tell us what you need — we will email you with parent guides, school engagement options or AI Awareness Day support.', 'ai-risk-benchmark' ),
						'score_label'  => __( 'Your parent readiness score', 'ai-risk-benchmark' ),
						'weak_label'   => __( 'Areas to focus on at home', 'ai-risk-benchmark' ),
						'child_school' => __( 'Child\'s school (optional)', 'ai-risk-benchmark' ),
					)
				);
			case 'student':
				return array_merge(
					$base,
					array(
						'heading'    => __( 'Share your learning with your school', 'ai-risk-benchmark' ),
						'intro'      => __( 'Your results help your school understand how students use AI. Choose if you want to share your results or learn more — we will not try to sell you anything.', 'ai-risk-benchmark' ),
						'score_label'=> __( 'Your learning score', 'ai-risk-benchmark' ),
						'weak_label' => __( 'Skills to improve', 'ai-risk-benchmark' ),
						'school'     => __( 'Your school (optional)', 'ai-risk-benchmark' ),
						'email_hint' => __( 'Use a parent or teacher email if you do not have your own.', 'ai-risk-benchmark' ),
					)
				);
			case 'leader':
				return array_merge(
					$base,
					array(
						'heading'          => __( 'Request support for your school', 'ai-risk-benchmark' ),
						'intro'            => __( 'Select what you need below — we will email you with next steps. Your benchmark scores are included automatically.', 'ai-risk-benchmark' ),
						'stakeholder_role' => __( 'Job title', 'ai-risk-benchmark' ),
					)
				);
			case 'teacher':
				return array_merge(
					$base,
					array(
						'heading'          => __( 'Tell us how AI Awareness Day can support you and your school', 'ai-risk-benchmark' ),
						'intro'            => __( 'Your audit results are above. Open “What happens after the audit?” for ideas, then tick what you need — we will email you with CPD, resources or whole-school options.', 'ai-risk-benchmark' ),
						'stakeholder_role' => __( 'Job title', 'ai-risk-benchmark' ),
					)
				);
			case 'support_staff':
				return array_merge(
					$base,
					array(
						'heading'     => __( 'Request support after your support-staff audit', 'ai-risk-benchmark' ),
						'intro'       => __( 'Your results are above. Tell us what you need — we will email you with checklists, CPD options or help rolling out the benchmark at your school.', 'ai-risk-benchmark' ),
						'score_label' => __( 'Your operational readiness score', 'ai-risk-benchmark' ),
						'weak_label'  => __( 'Priority areas from your audit', 'ai-risk-benchmark' ),
					)
				);
			default:
				return array_merge(
					$base,
					array(
						'heading' => __( 'Tell us how AI Awareness Day can support your school', 'ai-risk-benchmark' ),
						'intro'   => __( 'Select what you are interested in and we will email you with next steps.', 'ai-risk-benchmark' ),
					)
				);
		}
	}

	/**
	 * Which contact fields to render and whether email is required.
	 *
	 * @return array<string, mixed>
	 */
	public static function fields_for_role( string $role ): array {
		$role = sanitize_key( $role );
		switch ( $role ) {
			case 'parent':
				return array(
					'show_name'         => true,
					'show_email'        => true,
					'email_required'    => true,
					'show_school'       => false,
					'show_child_school' => true,
				);
			case 'student':
				return array(
					'show_name'         => true,
					'show_email'        => true,
					'email_required'    => true,
					'show_school'       => true,
					'show_child_school' => false,
				);
			default:
				return array(
					'show_name'              => true,
					'show_email'             => true,
					'email_required'         => true,
					'show_school'            => true,
					'show_child_school'      => false,
					'show_stakeholder_role'  => in_array( $role, array( 'teacher', 'leader' ), true ),
				);
		}
	}

	/**
	 * Static interest form shell for front-end fallback when AJAX omits the payload.
	 *
	 * @param string $role Role slug.
	 * @return array<string, mixed>|null
	 */
	public static function form_shell_for_frontend( string $role ): ?array {
		return self::build_form_payload(
			array(
				'alignment_score' => 0,
				'domain_scores'   => array(),
			),
			$role
		);
	}

	/**
	 * Build interest form payload for results JSON.
	 *
	 * @param array<string, mixed> $results Scored results.
	 * @return array<string, mixed>|null
	 */
	public static function build_form_payload( array $results, string $role ): ?array {
		$role = sanitize_key( $role );
		if ( ! in_array( $role, self::supported_roles(), true ) ) {
			return null;
		}

		$tier = '';
		if ( 'parent' === $role && ! empty( $results['parent_results']['journey_tier'] ) ) {
			$tier = sanitize_key( (string) $results['parent_results']['journey_tier'] );
		}

		$options = self::options_for_role( $role, $tier );
		if ( ! $options ) {
			return null;
		}

		$labels = self::form_labels( $role );
		if ( 'parent' === $role && $tier ) {
			$parent_cfg = AIRB_Defaults::parent_result_config();
			$intros     = (array) ( $parent_cfg['interest_intros'] ?? array() );
			if ( ! empty( $intros[ $tier ] ) ) {
				$labels['intro'] = (string) $intros[ $tier ];
			}
			if ( 'high' === $tier ) {
				$labels['heading'] = __( 'Help your school community', 'ai-risk-benchmark' );
				$labels['interests'] = __( 'How would you like to help?', 'ai-risk-benchmark' );
			}
		}

		return array(
			'labels'       => $labels,
			'fields'       => self::fields_for_role( $role ),
			'options'      => $options,
			'suggested'    => self::suggested_from_results( $results, $role ),
			'weak_domains' => self::weak_domain_labels( $results, $role ),
			'summary'      => self::results_summary( $results, $role ),
			'stakeholder_roles' => in_array( $role, array( 'teacher', 'leader' ), true ) ? self::stakeholder_role_options() : array(),
			'journey_tier' => $tier,
		);
	}

	/**
	 * Read-only summary lines for the form header.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array<string, mixed>
	 */
	public static function results_summary( array $results, string $role ): array {
		return array(
			'score'              => (int) ( $results['alignment_score'] ?? 0 ),
			'risk_level'         => sanitize_key( (string) ( $results['risk_level'] ?? '' ) ),
			'risk_level_label'   => (string) ( $results['risk_level_label'] ?? '' ),
			'readiness_label'    => (string) ( $results['readiness_level_label'] ?? '' ),
		);
	}

	/**
	 * Domain slug → interest slug maps per role.
	 *
	 * @return array<string, string>
	 */
	private static function domain_suggestion_map( string $role ): array {
		if ( 'parent' === $role ) {
			return array(
				'parent_awareness'      => 'parent_support_child',
				'home_ai_safety'        => 'parent_resources',
				'homework_oversight'    => 'parent_support_child',
				'parent_ai_dependency'  => 'parent_learn_ai',
				'school_partnership'    => 'parent_share_with_school',
				// Legacy display slugs (pre v1.30).
				'child_privacy_risk'    => 'parent_support_child',
				'homework_support_risk' => 'parent_support_child',
				'parent_confidence'     => 'parent_learn_ai',
			);
		}
		if ( 'student' === $role ) {
			return array(
				'safeguarding'         => 'student_learn_ai',
				'ai_literacy'          => 'student_learn_ai',
				'privacy'              => 'student_learn_ai',
				'assessment_integrity' => 'student_teacher_help',
				'human_oversight'      => 'student_learn_ai',
				'ai_dependency'        => 'student_teacher_help',
			);
		}
		if ( 'support_staff' === $role ) {
			return array(
				'privacy'         => 'support_data_checklist',
				'human_oversight' => 'support_verification_resources',
				'safe_adoption'   => 'support_verification_resources',
				'ai_literacy'     => 'support_staff_cpd',
				'ai_dependency'   => 'support_staff_cpd',
			);
		}
		if ( 'teacher' === $role ) {
			return array(
				'ai_literacy'          => 'teacher_activity_day',
				'human_oversight'      => 'teacher_activity_day',
				'privacy'              => 'teacher_activity_day',
				'assessment_integrity' => 'teacher_activity_day',
				'ai_dependency'        => 'whole_school_cpd',
				'safe_adoption'        => 'whole_school_cpd',
			);
		}
		return array(
			'governance'           => 'policy_support',
			'human_oversight'      => 'whole_school_cpd',
			'privacy'              => 'whole_school_cpd',
			'safeguarding'         => 'parent_sessions',
			'assessment_integrity' => 'whole_school_cpd',
			'governance_maturity'  => 'governance_review',
		);
	}

	/**
	 * Suggested pre-checked interests from weak benchmark domains.
	 *
	 * @param array<string, mixed> $results Scored results.
	 * @return array<int, string>
	 */
	public static function suggested_from_results( array $results, string $role ): array {
		$role      = sanitize_key( $role );
		$tier      = '';
		if ( 'parent' === $role && ! empty( $results['parent_results']['journey_tier'] ) ) {
			$tier = sanitize_key( (string) $results['parent_results']['journey_tier'] );
		}
		$suggested = array();
		$weak      = 70;
		$allowed   = array_column( self::options_for_role( $role, $tier ), 'slug' );
		$checks    = self::domain_suggestion_map( $role );
		$domains   = self::domain_source_for_role( $results, $role );

		foreach ( $checks as $domain => $interest ) {
			$dom = (array) ( $domains[ $domain ] ?? array() );
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$metric_type = (string) ( $dom['metric_type'] ?? 'readiness' );
			$weak_hit    = false;
			if ( 'risk' === $metric_type ) {
				$risk_pct = (int) round( (float) ( $dom['risk_percentage'] ?? 0 ) );
				$weak_hit = $risk_pct >= $weak;
			} else {
				$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 100 ) );
				$weak_hit  = $readiness < $weak;
			}
			if ( $weak_hit && in_array( $interest, $allowed, true ) ) {
				$suggested[] = $interest;
			}
		}

		if ( 'leader' === $role && in_array( 'whole_school_benchmark', $allowed, true ) ) {
			$suggested[] = 'whole_school_benchmark';
		}
		if ( 'parent' === $role ) {
			$score = (int) ( $results['alignment_score'] ?? 100 );
			if ( $score < $weak && in_array( 'parent_resources', $allowed, true ) ) {
				$suggested[] = 'parent_resources';
			}
			if ( in_array( 'parent_school_take_part', $allowed, true ) ) {
				$suggested[] = 'parent_school_take_part';
			}
		}
		if ( 'student' === $role ) {
			$score = (int) ( $results['alignment_score'] ?? 100 );
			if ( $score < $weak && in_array( 'student_learn_ai', $allowed, true ) ) {
				$suggested[] = 'student_learn_ai';
			}
			if ( in_array( 'student_share_school', $allowed, true ) ) {
				$suggested[] = 'student_share_school';
			}
		}
		if ( 'teacher' === $role && in_array( 'teacher_activity_day', $allowed, true ) && ! $suggested ) {
			$suggested[] = 'teacher_activity_day';
		}
		if ( 'support_staff' === $role ) {
			if ( ! $suggested && in_array( 'support_data_checklist', $allowed, true ) ) {
				$suggested[] = 'support_data_checklist';
			}
			if ( in_array( 'support_school_rollout', $allowed, true ) ) {
				$suggested[] = 'support_school_rollout';
			}
		}

		return array_values( array_unique( $suggested ) );
	}

	/**
	 * Domain scores keyed by slug for a role.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array<string, mixed>
	 */
	private static function domain_source_for_role( array $results, string $role ): array {
		if ( 'parent' === $role ) {
			return (array) ( $results['parent_display_domains'] ?? array() );
		}
		return (array) ( $results['domain_scores'] ?? array() );
	}

	/**
	 * Weak domain labels for summary line.
	 *
	 * @param array<string, mixed> $results Results.
	 * @return array<int, string>
	 */
	public static function weak_domain_labels( array $results, string $role = '' ): array {
		$labels  = array();
		$weak    = 70;
		$domains = self::domain_source_for_role( $results, sanitize_key( $role ) );

		foreach ( $domains as $dom ) {
			if ( ! is_array( $dom ) ) {
				continue;
			}
			if ( (int) ( $dom['questions_answered'] ?? 0 ) < 1 ) {
				continue;
			}
			$label = (string) ( $dom['label'] ?? '' );
			if ( 'risk' === (string) ( $dom['metric_type'] ?? '' ) ) {
				$risk_pct = (int) round( (float) ( $dom['risk_percentage'] ?? 0 ) );
				if ( $risk_pct < $weak ) {
					continue;
				}
				$labels[] = $label . ' (' . $risk_pct . '% risk)';
				continue;
			}
			$readiness = (int) round( (float) ( $dom['readiness_percentage'] ?? 100 ) );
			if ( $readiness >= $weak ) {
				continue;
			}
			$labels[] = $label . ' (' . $readiness . '%)';
		}
		return $labels;
	}

	/**
	 * Campaign inbox.
	 */
	public static function contact_email(): string {
		$email = '';
		if ( function_exists( 'get_theme_mod' ) ) {
			$email = sanitize_email( (string) get_theme_mod( 'aiad_contact_email', '' ) );
		}
		return $email ? $email : 'info@aiawarenessday.co.uk';
	}

	/**
	 * Send interest notification email.
	 *
	 * @param array<string, mixed> $data Submission data.
	 */
	public static function send_notification( array $data ): bool {
		$role_labels = AIRB_Defaults::roles();
		$role        = sanitize_key( (string) ( $data['role'] ?? '' ) );
		$role_label  = $role_labels[ $role ] ?? $role;
		$name        = sanitize_text_field( (string) ( $data['name'] ?? '' ) );
		$email       = sanitize_email( (string) ( $data['email'] ?? '' ) );
		$school      = sanitize_text_field( (string) ( $data['school'] ?? '' ) );
		$child_school = sanitize_text_field( (string) ( $data['child_school'] ?? '' ) );
		$message     = sanitize_textarea_field( (string) ( $data['message'] ?? '' ) );
		$score       = (int) ( $data['alignment_score'] ?? 0 );
		$risk        = sanitize_key( (string) ( $data['risk_level'] ?? '' ) );
		$risk_label  = sanitize_text_field( (string) ( $data['risk_level_label'] ?? '' ) );
		$readiness   = sanitize_text_field( (string) ( $data['readiness_level_label'] ?? '' ) );
		$year_group  = sanitize_key( (string) ( $data['year_group'] ?? '' ) );
		$submission  = (int) ( $data['submission_id'] ?? 0 );
		$interests   = array_map( 'sanitize_key', (array) ( $data['interests'] ?? array() ) );
		$role_options = array_column( self::options_for_role( $role ), 'label', 'slug' );
		$interest_lines = array();
		foreach ( $interests as $slug ) {
			if ( isset( $role_options[ $slug ] ) ) {
				$interest_lines[] = (string) $role_options[ $slug ];
			}
		}
		$stakeholder      = sanitize_text_field( substr( (string) ( $data['stakeholder_role'] ?? '' ), 0, 40 ) );
		$stakeholder_opts = self::stakeholder_role_options();

		$subject_name = $school ? $school : ( $child_school ? $child_school : ( $name ? $name : __( 'Unknown', 'ai-risk-benchmark' ) ) );
		$subject = sprintf(
			/* translators: 1: school or name, 2: role */
			__( '[AI Awareness Day] Benchmark support request — %1$s (%2$s)', 'ai-risk-benchmark' ),
			$subject_name,
			$role_label
		);

		$body  = "Benchmark support request\n\n";
		$body .= "Role: {$role_label}\n";
		if ( $stakeholder ) {
			$key = sanitize_key( $stakeholder );
			if ( isset( $stakeholder_opts[ $key ] ) ) {
				$body .= 'Position: ' . $stakeholder_opts[ $key ] . "\n";
			} else {
				$body .= 'Position: ' . $stakeholder . "\n";
			}
		}
		if ( $name ) {
			$body .= "Name: {$name}\n";
		}
		if ( $email ) {
			$body .= "Email: {$email}\n";
		}
		if ( $school ) {
			$body .= "School: {$school}\n";
		}
		if ( $child_school ) {
			$body .= "Child's school: {$child_school}\n";
		}
		if ( $year_group ) {
			$year_labels = array(
				'reception' => 'Reception',
				'year_1'    => 'Year 1',
				'year_2'    => 'Year 2',
				'year_3'    => 'Year 3',
				'year_4'    => 'Year 4',
				'year_5'    => 'Year 5',
				'year_6'    => 'Year 6',
				'year_7'    => 'Year 7',
				'year_8'    => 'Year 8',
				'year_9'    => 'Year 9',
				'year_10'   => 'Year 10',
				'year_11'   => 'Year 11',
				'year_12'   => 'Year 12',
				'year_13'   => 'Year 13',
			);
			$body .= 'Year group: ' . ( $year_labels[ $year_group ] ?? $year_group ) . "\n";
		}
		$body .= "Readiness score: {$score}%\n";
		if ( $readiness ) {
			$body .= "Readiness band: {$readiness}\n";
		}
		if ( $risk_label ) {
			$body .= "Risk level: {$risk_label}\n";
		} elseif ( $risk ) {
			$body .= "Risk level: {$risk}\n";
		}
		if ( $submission ) {
			$body .= "Submission ID: {$submission}\n";
		}
		if ( ! empty( $data['weak_domains'] ) && is_array( $data['weak_domains'] ) ) {
			$body .= "\nPriority areas:\n";
			foreach ( (array) $data['weak_domains'] as $line ) {
				$body .= '- ' . sanitize_text_field( (string) $line ) . "\n";
			}
		}
		if ( $interest_lines ) {
			$body .= "\nInterested in:\n";
			foreach ( $interest_lines as $line ) {
				$body .= '- ' . $line . "\n";
			}
		}
		if ( $message ) {
			$body .= "\nMessage:\n{$message}\n";
		}

		$body = AIRB_Hub_Interest::append_email_context( $body, $data );

		$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
		if ( $email ) {
			$reply_name = preg_replace( '/[\r\n]+/', ' ', $name ? $name : $role_label );
			$reply_name = trim( sanitize_text_field( (string) $reply_name ) );
			$email      = str_replace( array( "\r", "\n" ), '', $email );
			$headers[]  = 'Reply-To: ' . $reply_name . ' <' . $email . '>';
		}

		return wp_mail( self::contact_email(), $subject, $body, $headers );
	}
}
