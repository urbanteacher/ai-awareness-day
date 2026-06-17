<?php
/**
 * Default benchmark configuration (questions, recommendations, copy).
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default config factory.
 */
class AIRB_Defaults {

	/**
	 * Campaign contact section on the homepage.
	 */
	/**
	 * In-results interest form anchor (replaces mailto / homepage contact for benchmark CTAs).
	 */
	public static function interest_form_url( string $prefill = '' ): string {
		$prefill = sanitize_key( $prefill );
		return '#airb-interest' . ( $prefill ? '?prefill=' . rawurlencode( $prefill ) : '' );
	}

	public static function contact_page_url(): string {
		return function_exists( 'home_url' ) ? home_url( '/#contact' ) : 'https://aiawarenessday.co.uk/#contact';
	}

	/**
	 * Contact section with benchmark attribution query args.
	 */
	public static function contact_tracking_url( string $role, string $ref ): string {
		$base = function_exists( 'home_url' ) ? home_url( '/' ) : 'https://aiawarenessday.co.uk/';
		return add_query_arg(
			array(
				'airb_role' => sanitize_key( $role ),
				'airb_ref'  => sanitize_key( $ref ),
			),
			$base
		) . '#contact';
	}

	/**
	 * Load role tier copy: legacy PHP arrays merged with JSON registry overlay.
	 *
	 * @param string $role teacher|leader|student|parent|support|public
	 * @return array<string, mixed>
	 */
	public static function role_tier_data( string $role ): array {
		$map = array(
			'teacher' => 'teacher-copy-tiers.php',
			'leader'  => 'leader-copy-tiers.php',
			'student' => 'student-copy-tiers.php',
			'parent'  => 'parent-copy-tiers.php',
			'support' => 'support-copy-tiers.php',
			'public'  => 'public-copy-tiers.php',
		);
		if ( ! isset( $map[ $role ] ) ) {
			return array();
		}

		$archive_file = ( defined( 'AIRB_ARCHIVE_DIR' ) ? AIRB_ARCHIVE_DIR : AIRB_PLUGIN_DIR . 'archive/' ) . 'includes/data/' . $map[ $role ];
		$legacy_file  = AIRB_PLUGIN_DIR . 'includes/data/' . $map[ $role ];
		$tier_file    = is_readable( $archive_file ) ? $archive_file : $legacy_file;
		if ( ! is_readable( $tier_file ) ) {
			return array();
		}

		/** @var array<string, mixed> $php */
		$php = require $tier_file;

		if ( ! class_exists( 'AIRB_Copy_Tiers', false ) || ! AIRB_Copy_Tiers::use_json_copy() ) {
			return $php;
		}

		$overlay = AIRB_Copy_Tiers::for_role( $role )->to_legacy_overlay();
		if ( empty( $overlay ) ) {
			return $php;
		}

		unset( $php['copy_tiers'], $php['focus_tiers'] );

		return array_replace_recursive( $php, $overlay );
	}

	/** DfE — support materials collection (modules, leadership toolkit, safe use guidance). */
	public static function dfe_url_using_ai(): string {
		return 'https://www.gov.uk/government/collections/using-ai-in-education-settings-support-materials';
	}

	/** DfE — generative AI overview (data protection, DPIAs, risk). */
	public static function dfe_url_generative_ai(): string {
		return 'https://www.gov.uk/government/publications/generative-artificial-intelligence-in-education';
	}

	/** DfE — product safety standards for generative AI tools. */
	public static function dfe_url_product_safety(): string {
		return 'https://www.gov.uk/government/publications/generative-ai-product-safety-standards';
	}

	/**
	 * All benchmark domains.
	 *
	 * @return array<string, string>
	 */
	public static function domains(): array {
		return array(
			'safe_adoption'         => __( 'Safe Adoption', 'ai-risk-benchmark' ),
			'human_oversight'       => __( 'Human Oversight', 'ai-risk-benchmark' ),
			'ai_dependency'         => __( 'Independent Practice', 'ai-risk-benchmark' ),
			'privacy'               => __( 'Privacy & Data Protection', 'ai-risk-benchmark' ),
			'safeguarding'          => __( 'Safeguarding', 'ai-risk-benchmark' ),
			'bias_equality'         => __( 'Bias & Equality', 'ai-risk-benchmark' ),
			'assessment_integrity'  => __( 'Assessment Integrity', 'ai-risk-benchmark' ),
			'ai_literacy'           => __( 'AI Literacy', 'ai-risk-benchmark' ),
			'governance'            => __( 'Governance', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Role labels.
	 *
	 * @return array<string, string>
	 */
	public static function roles(): array {
		return array(
			'teacher'       => __( 'Teacher', 'ai-risk-benchmark' ),
			'student'       => __( 'Student', 'ai-risk-benchmark' ),
			'parent'        => __( 'Parent / Carer', 'ai-risk-benchmark' ),
			'leader'        => __( 'School Leader', 'ai-risk-benchmark' ),
			'support_staff' => __( 'Education Support Staff', 'ai-risk-benchmark' ),
			'public'        => __( 'General Public', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Per-domain colour identity (bars, tags, heatmap accents).
	 *
	 * @return array<string, string>
	 */
	public static function domain_colors(): array {
		return array(
			'safe_adoption'        => '#1B6B8C',
			'human_oversight'      => '#15803d',
			'ai_dependency'        => '#c2410c',
			'privacy'              => '#7c3aed',
			'safeguarding'         => '#b91c1c',
			'bias_equality'        => '#be123c',
			'assessment_integrity' => '#a16207',
			'ai_literacy'          => '#2563eb',
			'governance'           => '#475569',
		);
	}

	/**
	 * Short remediation copy for weakest domains on the results screen.
	 *
	 * @return array<string, string>
	 */
	public static function domain_recommendations(): array {
		return array(
			'human_oversight'      => __( 'Build a habit of editing, verifying and challenging AI output before it is used.', 'ai-risk-benchmark' ),
			'ai_dependency'        => __( 'Practise core tasks without AI first; use it to refine, not to originate.', 'ai-risk-benchmark' ),
			'privacy'              => __( 'Never enter identifiable pupil, SEND or safeguarding data into public AI tools.', 'ai-risk-benchmark' ),
			'ai_literacy'          => __( 'Cover hallucinations, limitations and clear no-go situations in training.', 'ai-risk-benchmark' ),
			'safeguarding'         => __( 'Add AI and deepfake risks explicitly to safeguarding procedures.', 'ai-risk-benchmark' ),
			'bias_equality'        => __( 'Build a routine check for biased, stereotyped or unfair AI outputs.', 'ai-risk-benchmark' ),
			'assessment_integrity' => __( 'Review assessment design against JCQ and Ofqual AI guidance.', 'ai-risk-benchmark' ),
			'governance'           => __( 'Publish an AI policy, name a lead, and schedule an annual review.', 'ai-risk-benchmark' ),
			'safe_adoption'        => __( 'Maintain an approved tool list and structured staff training.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Readiness hero scale for teacher, leader and support (5 bands).
	 *
	 * @param string $role teacher|leader|support_staff
	 * @return array<int, array<string, mixed>>
	 */
	public static function readiness_hero_bands( string $role = 'leader' ): array {
		$role   = sanitize_key( $role );
		$band_1 = 'leader' === $role
			? __( 'Critical · Act now', 'ai-risk-benchmark' )
			: __( 'At risk · Act now', 'ai-risk-benchmark' );

		return array(
			array(
				'slug'        => 'emerging',
				'label'       => $band_1,
				'label_short' => 'leader' === $role ? __( 'Critical', 'ai-risk-benchmark' ) : __( 'At risk', 'ai-risk-benchmark' ),
				'min'         => 0,
				'max'         => 39,
				'tone'        => 'alarm',
			),
			array(
				'slug'        => 'developing',
				'label'       => __( 'Concern · Review needed', 'ai-risk-benchmark' ),
				'label_short' => __( 'Concern', 'ai-risk-benchmark' ),
				'min'         => 40,
				'max'         => 59,
				'tone'        => 'concern',
			),
			array(
				'slug'        => 'established',
				'label'       => __( 'Established', 'ai-risk-benchmark' ),
				'label_short' => __( 'Est.', 'ai-risk-benchmark' ),
				'min'         => 60,
				'max'         => 74,
			),
			array(
				'slug'        => 'strong',
				'label'       => __( 'Strong', 'ai-risk-benchmark' ),
				'label_short' => __( 'Str.', 'ai-risk-benchmark' ),
				'min'         => 75,
				'max'         => 89,
			),
			array(
				'slug'        => 'leading',
				'label'       => __( 'Leading', 'ai-risk-benchmark' ),
				'label_short' => __( 'Lead.', 'ai-risk-benchmark' ),
				'min'         => 90,
				'max'         => 100,
			),
		);
	}

	/**
	 * Student journey / hero scale (5 bands).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function student_journey_levels(): array {
		return array(
			array(
				'slug'        => 'beginning',
				'label'       => __( 'Needs attention', 'ai-risk-benchmark' ),
				'label_short' => __( 'Attention', 'ai-risk-benchmark' ),
				'min'         => 0,
				'max'         => 20,
				'tone'        => 'alarm',
			),
			array(
				'slug'        => 'developing',
				'label'       => __( 'Take care', 'ai-risk-benchmark' ),
				'label_short' => __( 'Take care', 'ai-risk-benchmark' ),
				'min'         => 21,
				'max'         => 40,
				'tone'        => 'concern',
			),
			array(
				'slug'        => 'emerging',
				'label'       => __( 'Aware', 'ai-risk-benchmark' ),
				'label_short' => __( 'Aware', 'ai-risk-benchmark' ),
				'min'         => 41,
				'max'         => 60,
			),
			array(
				'slug'        => 'confident',
				'label'       => __( 'Confident', 'ai-risk-benchmark' ),
				'label_short' => __( 'Conf.', 'ai-risk-benchmark' ),
				'min'         => 61,
				'max'         => 80,
			),
			array(
				'slug'        => 'advanced',
				'label'       => __( 'Advanced', 'ai-risk-benchmark' ),
				'label_short' => __( 'Adv.', 'ai-risk-benchmark' ),
				'min'         => 81,
				'max'         => 100,
			),
		);
	}

	/**
	 * Parent home-awareness hero scale (5 bands).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function parent_awareness_levels(): array {
		return array(
			array(
				'slug'        => 'just_starting',
				'label'       => __( 'Your child needs your help', 'ai-risk-benchmark' ),
				'label_short' => __( 'Needs help', 'ai-risk-benchmark' ),
				'min'         => 0,
				'max'         => 20,
				'tone'        => 'alarm',
			),
			array(
				'slug'        => 'developing',
				'label'       => __( 'Some gaps at home', 'ai-risk-benchmark' ),
				'label_short' => __( 'Gaps', 'ai-risk-benchmark' ),
				'min'         => 21,
				'max'         => 40,
				'tone'        => 'concern',
			),
			array(
				'slug'        => 'aware',
				'label'       => __( 'Aware', 'ai-risk-benchmark' ),
				'label_short' => __( 'Aware', 'ai-risk-benchmark' ),
				'min'         => 41,
				'max'         => 60,
			),
			array(
				'slug'        => 'confident',
				'label'       => __( 'Confident', 'ai-risk-benchmark' ),
				'label_short' => __( 'Conf.', 'ai-risk-benchmark' ),
				'min'         => 61,
				'max'         => 80,
			),
			array(
				'slug'        => 'well_prepared',
				'label'       => __( 'Well prepared', 'ai-risk-benchmark' ),
				'label_short' => __( 'Prepared', 'ai-risk-benchmark' ),
				'min'         => 81,
				'max'         => 100,
			),
		);
	}

	/**
	 * Parent / carer results — display domains, guidance copy and priority focus.
	 *
	 * @return array<string, mixed>
	 */
	public static function parent_result_config(): array {
		$base = array(
			'domain_weights' => array(
				'parent_awareness'      => 0.20,
				'home_ai_safety'        => 0.20,
				'online_risk_awareness' => 0.20,
				'homework_oversight'    => 0.20,
				'parent_ai_dependency'  => 0.20,
				'school_partnership'    => 0.20,
			),
			'display_domains' => array(
				'parent_awareness' => array(
					'label'       => __( 'Parent Awareness Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_child_uses', 'p_know_tools', 'p_child_unknown_use' ),
					'color'       => '#1B6B8C',
				),
				'home_ai_safety' => array(
					'label'       => __( 'Home AI Safety Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_no_share', 'p_home_ai_culture' ),
					'color'       => '#15803d',
				),
				'online_risk_awareness' => array(
					'label'       => __( 'Deepfake & online risk awareness', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_harm_response' ),
					'color'       => '#a32d2d',
				),
				'homework_oversight' => array(
					'label'       => __( 'Homework Oversight Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_explain_own_words', 'p_check_suspicion', 'p_hw_first_response' ),
					'color'       => '#a16207',
				),
				'parent_ai_dependency' => array(
					'label'       => __( 'Parent AI Dependency Score', 'ai-risk-benchmark' ),
					'metric_type' => 'risk',
					'questions'   => array( 'p_parent_ai_hw', 'p_parent_ai_comms' ),
					'color'       => '#b45309',
				),
				'school_partnership' => array(
					'label'       => __( 'School Partnership Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_school_expectations', 'p_school_discuss' ),
					'color'       => '#2563eb',
				),
			),
			'home_metrics' => array(
				array(
					'slug'     => 'parent_awareness',
					'source'   => 'parent_awareness',
					'label'    => __( 'Parent awareness', 'ai-risk-benchmark' ),
					'subtitle' => __( 'Know what AI tools are being used', 'ai-risk-benchmark' ),
					'icon'     => 'eye',
				),
				array(
					'slug'     => 'home_ai_safety',
					'source'   => 'home_ai_safety',
					'label'    => __( 'Home AI safety', 'ai-risk-benchmark' ),
					'subtitle' => __( 'Privacy and harm response at home', 'ai-risk-benchmark' ),
					'icon'     => 'home',
				),
				array(
					'slug'       => 'online_risk_awareness',
					'source'     => 'online_risk_awareness',
					'label'      => __( 'Deepfake awareness', 'ai-risk-benchmark' ),
					'subtitle'   => __( 'Fake images, voices and scam risks', 'ai-risk-benchmark' ),
					'icon'       => 'alert',
					'focus_slug' => 'online_risk_awareness',
				),
				array(
					'slug'     => 'homework_oversight',
					'source'   => 'homework_oversight',
					'label'    => __( 'Homework oversight', 'ai-risk-benchmark' ),
					'subtitle' => __( 'Explain AI-assisted work in own words', 'ai-risk-benchmark' ),
					'icon'     => 'book',
				),
				array(
					'slug'     => 'parent_ai_dependency',
					'source'   => 'parent_ai_dependency',
					'label'    => __( 'Parent AI use', 'ai-risk-benchmark' ),
					'subtitle' => __( 'Use AI to support, not replace thinking', 'ai-risk-benchmark' ),
					'icon'     => 'confidence',
				),
				array(
					'slug'     => 'school_partnership',
					'source'   => 'school_partnership',
					'label'    => __( 'School partnership', 'ai-risk-benchmark' ),
					'subtitle' => __( 'Know and ask about school expectations', 'ai-risk-benchmark' ),
					'icon'     => 'home',
				),
			),
			'band_summaries' => array(
				'emerging'    => __( 'There are important gaps in your awareness of how children use AI at home. Start with the guidance below and talk openly with your child about their AI use.', 'ai-risk-benchmark' ),
				'developing'  => __( 'This means you have some awareness of AI risks, but there are important areas where more guidance would help you support your child safely.', 'ai-risk-benchmark' ),
				'established' => __( 'You have a solid foundation for guiding your child\'s AI use. Keep conversations going and revisit the focus areas below as tools and risks evolve.', 'ai-risk-benchmark' ),
				'strong'      => __( 'You are well placed to support safe, honest and independent AI use at home. Share what works with your child\'s school to help others.', 'ai-risk-benchmark' ),
				'leading'     => __( 'You demonstrate strong AI awareness at home. Consider sharing your approach with your child\'s school and encouraging other families to take part.', 'ai-risk-benchmark' ),
			),
			'summary_title'           => __( 'Your summary', 'ai-risk-benchmark' ),
			'where_you_stand_heading' => __( 'Where you stand', 'ai-risk-benchmark' ),
			'hero_next_step_heading'  => __( 'Your next step', 'ai-risk-benchmark' ),
			'domains_accordion_title' => __( 'Your domain scores', 'ai-risk-benchmark' ),
			'focus_accordion_title'   => __( 'What to focus on', 'ai-risk-benchmark' ),
			'peer_benchmark_title' => __( 'Parent benchmark comparison', 'ai-risk-benchmark' ),
			'resource_links'       => self::results_timeline_read_links( 'parent' ),
			'peer_benchmark_fallback' => array(
				'average'      => 58,
				'top_quartile' => 82,
			),
			'advocate_title' => __( 'You\'re ahead of most parents', 'ai-risk-benchmark' ),
			'advocate_intro' => __( 'You demonstrate strong awareness of how AI is used at home. The next step is helping your school see the whole picture.', 'ai-risk-benchmark' ),
			'advocate_strength_labels' => array(
				'home_ai_safety'       => __( 'Home AI safety', 'ai-risk-benchmark' ),
				'homework_oversight'   => __( 'Homework oversight', 'ai-risk-benchmark' ),
				'parent_ai_dependency' => __( 'Balanced AI use', 'ai-risk-benchmark' ),
				'school_partnership'   => __( 'School partnership', 'ai-risk-benchmark' ),
				'parent_awareness'     => __( 'Parent awareness', 'ai-risk-benchmark' ),
			),
			'advocate_help_title' => __( 'Help your school', 'ai-risk-benchmark' ),
			'advocate_strengths_label' => __( 'You demonstrate strong awareness of:', 'ai-risk-benchmark' ),
			'advocate_help_items' => array(
				__( 'Share your results with your child\'s school', 'ai-risk-benchmark' ),
				__( 'Encourage other families to complete the parent audit', 'ai-risk-benchmark' ),
				__( 'Ask about AI Awareness Day at your school', 'ai-risk-benchmark' ),
			),
			'confidence_copy' => array(
				'title'           => __( 'Strengthening homework oversight', 'ai-risk-benchmark' ),
				'impact_heading'  => __( 'Families with weaker homework oversight often report:', 'ai-risk-benchmark' ),
				'impact_items'    => array(
					__( 'not knowing when their child uses AI for schoolwork', 'ai-risk-benchmark' ),
					__( 'using AI themselves as the first response when homework is hard', 'ai-risk-benchmark' ),
					__( 'not asking children to explain AI-assisted work in their own words', 'ai-risk-benchmark' ),
					__( 'uncertainty about what to do if they suspect AI-generated homework', 'ai-risk-benchmark' ),
				),
				'improve_heading' => __( 'Practical next steps:', 'ai-risk-benchmark' ),
				'improve_items'   => array(
					__( 'Ask your child to explain one AI-assisted answer without looking at the screen', 'ai-risk-benchmark' ),
					__( 'Agree when AI can help learning and when your child should try first', 'ai-risk-benchmark' ),
					__( 'Check your school\'s homework and AI expectations together', 'ai-risk-benchmark' ),
				),
			),
			'journey_next_steps' => array(
				'high' => array(
					'title'    => __( 'Help your school community', 'ai-risk-benchmark' ),
					'intro'    => __( 'Your results can help your school understand how AI-aware families are at home.', 'ai-risk-benchmark' ),
					'items'    => array(
						__( 'Share your results with your child\'s school', 'ai-risk-benchmark' ),
						__( 'Encourage your school to run the whole-school benchmark', 'ai-risk-benchmark' ),
						__( 'Find out about AI Awareness Day for families', 'ai-risk-benchmark' ),
					),
					'cta_key'  => 'parent_share_with_school',
					'cta_text' => __( 'Share results with school', 'ai-risk-benchmark' ),
				),
				'medium' => array(
					'title'    => __( 'Keep building your awareness', 'ai-risk-benchmark' ),
					'intro'    => __( 'You have a good foundation — these resources can help you support your child on the areas flagged above.', 'ai-risk-benchmark' ),
					'items'    => array(
						__( 'Read the Parent AI Safety Guide', 'ai-risk-benchmark' ),
						__( 'Join a parent webinar or awareness session', 'ai-risk-benchmark' ),
						__( 'Share your results with your child\'s school', 'ai-risk-benchmark' ),
					),
					'cta_key'  => 'parent_resources',
					'cta_text' => __( 'Get parent guides', 'ai-risk-benchmark' ),
				),
				'low' => array(
					'title'    => __( 'Start here', 'ai-risk-benchmark' ),
					'intro'    => __( 'These practical resources will help you understand AI at home and talk confidently with your child.', 'ai-risk-benchmark' ),
					'items'    => array(
						__( 'Parent AI Basics — getting started guide', 'ai-risk-benchmark' ),
						__( 'Simple conversations about AI safety at home', 'ai-risk-benchmark' ),
						__( 'Ask your school about parent support sessions', 'ai-risk-benchmark' ),
					),
					'cta_key'  => 'parent_learn_ai',
					'cta_text' => __( 'Get started guide', 'ai-risk-benchmark' ),
				),
			),
			'focus_topics' => array(
				array(
					'slug'  => 'home_ai_safety',
					'label' => __( 'Home AI safety', 'ai-risk-benchmark' ),
					'body'  => __( 'Talk about privacy, fake images and voice messages, and agree what your child should do if something online does not feel right.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'online_risk_awareness',
					'label' => __( 'Deepfake awareness', 'ai-risk-benchmark' ),
					'body'  => __( 'Talk about deepfakes, fake accounts and AI-generated voice messages. Agree that unexpected or worrying online content gets shown to an adult before anyone responds or shares it.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'homework_oversight',
					'label' => __( 'Homework oversight', 'ai-risk-benchmark' ),
					'body'  => __( 'Ask your child to explain AI-assisted work in their own words. AI should support learning — not replace their thinking or produce work to submit as their own.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'parent_ai_dependency',
					'label' => __( 'Parent AI use', 'ai-risk-benchmark' ),
					'body'  => __( 'Using AI to do homework for your child can hide gaps in learning. Model trying first, then using AI to check or explain — not to produce the answer.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'school_partnership',
					'label' => __( 'School partnership', 'ai-risk-benchmark' ),
					'body'  => __( 'Find out your school\'s expectations for AI and homework. Schools need to know how AI is used at home to support pupils fairly.', 'ai-risk-benchmark' ),
				),
			),
			'priority_focus' => array(
				'intro' => __( 'Based on your result, we recommend', 'ai-risk-benchmark' ),
				'title' => __( 'the Parent AI Safety Guide or a Parent AI Awareness Session.', 'ai-risk-benchmark' ),
				'body'  => '',
			),
			'scores_heading' => __( 'Your scores', 'ai-risk-benchmark' ),
			'exposure_areas' => array(
				__( 'Hidden or unknown AI use at home', 'ai-risk-benchmark' ),
				__( 'Parents using AI as a homework co-pilot', 'ai-risk-benchmark' ),
				__( 'Weak verification — not asking children to explain AI-assisted work', 'ai-risk-benchmark' ),
				__( 'Low awareness of school AI expectations', 'ai-risk-benchmark' ),
			),
			'share_hint' => __( 'These results are for you. Sharing them with your child\'s school can help build a whole-school picture of AI awareness across parents, students, teachers and leaders.', 'ai-risk-benchmark' ),
			'interest_intros' => array(
				'high'   => __( 'Your results show strong AI awareness at home. Tell us how you\'d like to help your school — share results, encourage participation or become a parent ambassador.', 'ai-risk-benchmark' ),
				'medium' => __( 'Choose the parent guides or sessions that would help you most. We can also help you share results with your child\'s school.', 'ai-risk-benchmark' ),
				'low'    => __( 'Choose the support that would help you most — we\'ll email practical guides to build your confidence at home.', 'ai-risk-benchmark' ),
			),
			'awareness_levels' => self::parent_awareness_levels(),
		);

		$tier_data = self::role_tier_data( 'parent' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * Public benchmark hero scale — At risk through Advanced.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function public_safety_levels(): array {
		return array(
			array(
				'slug'        => 'at_risk',
				'label'       => __( 'At risk — significant gaps in your AI habits', 'ai-risk-benchmark' ),
				'label_short' => __( 'At risk', 'ai-risk-benchmark' ),
				'min'         => 0,
				'max'         => 39,
				'tone'        => 'alarm',
			),
			array(
				'slug'        => 'take_care',
				'label'       => __( 'Take care — some risks in your AI habits', 'ai-risk-benchmark' ),
				'label_short' => __( 'Take care', 'ai-risk-benchmark' ),
				'min'         => 40,
				'max'         => 59,
				'tone'        => 'concern',
			),
			array(
				'slug'        => 'aware',
				'label'       => __( 'Aware — reasonable habits with room to improve', 'ai-risk-benchmark' ),
				'label_short' => __( 'Aware', 'ai-risk-benchmark' ),
				'min'         => 60,
				'max'         => 74,
			),
			array(
				'slug'        => 'confident',
				'label'       => __( 'Confident — strong AI safety habits', 'ai-risk-benchmark' ),
				'label_short' => __( 'Confident', 'ai-risk-benchmark' ),
				'min'         => 75,
				'max'         => 89,
			),
			array(
				'slug'        => 'advanced',
				'label'       => __( 'Advanced — leading personal AI practice', 'ai-risk-benchmark' ),
				'label_short' => __( 'Advanced', 'ai-risk-benchmark' ),
				'min'         => 90,
				'max'         => 100,
			),
		);
	}

	/**
	 * General public benchmark — personal AI use, verification, privacy, workplace and social.
	 *
	 * @return array<string, mixed>
	 */
	public static function public_result_config(): array {
		$base = array(
			'domain_weights' => array(
				'personal_ai_use'  => 0.20,
				'verification'     => 0.20,
				'data_privacy'     => 0.20,
				'workplace_ai'     => 0.20,
				'emotional_social' => 0.20,
			),
			'display_domains' => array(
				'personal_ai_use' => array(
					'label'       => __( 'Personal AI use habits', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'pub_use_frequency', 'pub_use_tasks', 'pub_use_trust', 'pub_use_dependency' ),
					'color'       => '#378ADD',
				),
				'verification' => array(
					'label'       => __( 'Verification & critical thinking', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'pub_verify_check', 'pub_verify_hallucination', 'pub_verify_bias', 'pub_verify_deepfake' ),
					'color'       => '#639922',
				),
				'data_privacy' => array(
					'label'       => __( 'Data & privacy', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'pub_data_sharing', 'pub_data_third_party', 'pub_data_others', 'pub_data_scam' ),
					'color'       => '#E24B4A',
				),
				'workplace_ai' => array(
					'label'       => __( 'Workplace AI', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'pub_work_policy', 'pub_work_data', 'pub_work_disclosure' ),
					'color'       => '#EF9F27',
				),
				'emotional_social' => array(
					'label'       => __( 'Emotional & social AI use', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'pub_social_advice', 'pub_social_relationship', 'pub_social_news' ),
					'color'       => '#378ADD',
				),
			),
			'summary_metrics' => array(
				array(
					'slug'   => 'ai_dependency',
					'source' => 'personal_ai_use',
					'label'  => __( 'AI dependency', 'ai-risk-benchmark' ),
					'mode'   => 'readiness',
				),
				array(
					'slug'   => 'verification_habit',
					'source' => 'verification',
					'label'  => __( 'Verification habit', 'ai-risk-benchmark' ),
					'mode'   => 'readiness',
				),
				array(
					'slug'   => 'data_risk_exposure',
					'source' => 'data_privacy',
					'label'  => __( 'Data risk exposure', 'ai-risk-benchmark' ),
					'mode'   => 'risk',
				),
			),
			'section_metrics' => array(
				array(
					'slug'     => 'personal_ai_use',
					'source'   => 'personal_ai_use',
					'label'    => __( 'Personal AI use', 'ai-risk-benchmark' ),
					'subtitle' => __( 'How you use AI day to day', 'ai-risk-benchmark' ),
					'icon'     => 'device',
				),
				array(
					'slug'     => 'verification',
					'source'   => 'verification',
					'label'    => __( 'Verification', 'ai-risk-benchmark' ),
					'subtitle' => __( 'How you check what AI tells you', 'ai-risk-benchmark' ),
					'icon'     => 'verify',
				),
				array(
					'slug'     => 'data_privacy',
					'source'   => 'data_privacy',
					'label'    => __( 'Data & privacy', 'ai-risk-benchmark' ),
					'subtitle' => __( 'What you share and protect', 'ai-risk-benchmark' ),
					'icon'     => 'shield',
				),
				array(
					'slug'     => 'workplace_ai',
					'source'   => 'workplace_ai',
					'label'    => __( 'Workplace AI', 'ai-risk-benchmark' ),
					'subtitle' => __( 'How AI intersects with your work', 'ai-risk-benchmark' ),
					'icon'     => 'briefcase',
				),
				array(
					'slug'     => 'emotional_social',
					'source'   => 'emotional_social',
					'label'    => __( 'Emotional & social', 'ai-risk-benchmark' ),
					'subtitle' => __( 'AI in personal life and relationships', 'ai-risk-benchmark' ),
					'icon'     => 'heart',
				),
			),
			'hero_bands' => self::public_safety_levels(),
			'focus_slug_map' => array(
				'verification' => 'deepfake_scam_awareness',
			),
			'focus_topics' => array(
				array(
					'slug'  => 'deepfake_scam_awareness',
					'label' => __( 'Deepfake & scam awareness', 'ai-risk-benchmark' ),
				),
			),
			'resource_links' => self::results_timeline_read_links( 'public' ),
		);

		$tier_data = self::role_tier_data( 'public' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * Teacher results — strengths-first copy, champion pathway and school progress.
	 *
	 * @return array<string, mixed>
	 */
	public static function teacher_result_config(): array {
		$base = array(
			'headlines' => array(
				'strong'      => __( 'You are demonstrating good AI governance behaviours — sustain consistency as tools evolve.', 'ai-risk-benchmark' ),
				'established' => __( 'You are demonstrating responsible AI practice in most areas. Focus now on the domains where your habits are still inconsistent.', 'ai-risk-benchmark' ),
				'developing'  => __( 'You have foundational awareness but some areas need strengthening. Targeted improvement in your weakest domains will make a meaningful difference.', 'ai-risk-benchmark' ),
				'emerging'    => __( 'Your AI practice has significant gaps. Focus on building safer habits before expanding your use of AI tools in the classroom.', 'ai-risk-benchmark' ),
				'leading'     => __( 'You are demonstrating exemplary AI practice. You are well placed to support colleagues and contribute to school AI policy.', 'ai-risk-benchmark' ),
			),
			'strength_labels' => array(
				'ai_literacy'     => __( 'Strong understanding of AI limitations', 'ai-risk-benchmark' ),
				'low_dependency'  => __( 'Low dependency on AI tools', 'ai-risk-benchmark' ),
				'privacy'         => __( 'Good privacy and data protection awareness', 'ai-risk-benchmark' ),
				'dfe_alignment'   => __( 'High alignment with DfE guidance', 'ai-risk-benchmark' ),
				'safe_adoption'   => __( 'Thoughtful assessment of AI tools before use', 'ai-risk-benchmark' ),
				'human_oversight' => __( 'Strong human oversight of AI outputs', 'ai-risk-benchmark' ),
			),
			'opportunity_copy' => array(
				'human_oversight' => array(
					'summary' => __( 'Continue reviewing, editing and challenging AI outputs before use.', 'ai-risk-benchmark' ),
					'detail'  => __( 'While your score is strong, increasing the proportion of AI-generated content you critically review will further reduce the risk of automation bias and over-reliance.', 'ai-risk-benchmark' ),
				),
				'ai_dependency' => array(
					'summary' => __( 'Practise core tasks without AI first; use it to refine, not to originate.', 'ai-risk-benchmark' ),
					'detail'  => __( 'Building a habit of attempting work independently before using AI helps maintain your professional judgement and modelling for pupils.', 'ai-risk-benchmark' ),
				),
				'privacy' => array(
					'summary' => __( 'Never enter identifiable pupil, SEND or safeguarding data into public AI tools.', 'ai-risk-benchmark' ),
					'detail'  => __( 'Review what information enters AI tools and when a data protection impact assessment may be required.', 'ai-risk-benchmark' ),
				),
				'ai_literacy' => array(
					'summary' => __( 'Cover hallucinations, limitations and clear no-go situations in your own practice and with pupils.', 'ai-risk-benchmark' ),
					'detail'  => __( 'Strong AI literacy helps you spot errors, explain risks clearly and model responsible use in the classroom.', 'ai-risk-benchmark' ),
				),
				'safe_adoption' => array(
					'summary' => __( 'Assess benefits versus risks before introducing new AI tools with pupils.', 'ai-risk-benchmark' ),
					'detail'  => __( 'A simple pre-use checklist — purpose, data, oversight and alternatives — reduces ad-hoc adoption.', 'ai-risk-benchmark' ),
				),
				'assessment_integrity' => array(
					'summary' => __( 'Redesign tasks so pupils must demonstrate thinking AI cannot simply generate.', 'ai-risk-benchmark' ),
					'detail'  => __( 'Assessment design is one of the strongest levers against undetected AI use — adapt tasks, not just policies.', 'ai-risk-benchmark' ),
				),
			),
			'metric_labels' => array(
				'readiness'  => __( 'Overall readiness', 'ai-risk-benchmark' ),
				'risk'       => __( 'AI exposure risk', 'ai-risk-benchmark' ),
				'dependency' => __( 'AI Dependency Index', 'ai-risk-benchmark' ),
				'bias'       => AIRB_Scoring::bias_readiness_label(),
			),
			'dependency_score_note' => __( 'Higher % means greater reliance on AI — this is a risk indicator.', 'ai-risk-benchmark' ),
			'metric_signals' => array(
				'readiness' => array(
					'emerging' => array(
						'signal'      => __( 'Action needed', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'There are important gaps in your AI awareness. Focus on the opportunities below to reduce risk.', 'ai-risk-benchmark' ),
					),
					'developing' => array(
						'signal'      => __( 'Needs focused action', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'You have a foundation to build on — strengthen verification and privacy habits before AI use scales.', 'ai-risk-benchmark' ),
					),
					'established' => array(
						'signal'      => __( 'Building foundations', 'ai-risk-benchmark' ),
						'tone'        => 'neutral',
						'consequence' => __( 'You are building solid AI awareness with a few areas to refine further.', 'ai-risk-benchmark' ),
					),
					'strong' => array(
						'signal'      => __( 'Strong position', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'You are demonstrating good AI governance behaviours — sustain consistency as tools evolve.', 'ai-risk-benchmark' ),
					),
					'leading' => array(
						'signal'      => __( 'Leading practice', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'You model responsible AI use — share your practice with colleagues.', 'ai-risk-benchmark' ),
					),
				),
				'risk' => array(
					'critical' => array(
						'signal'      => __( 'Critical exposure', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'Your behavioural AI risk is high — prioritise the focus areas below.', 'ai-risk-benchmark' ),
					),
					'high' => array(
						'signal'      => __( 'High exposure', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'Higher exposure than most teachers at this stage — address weak domains first.', 'ai-risk-benchmark' ),
					),
					'moderate' => array(
						'signal'      => __( 'Moderate exposure', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'Some exposure gaps remain — strengthen oversight before they become habits.', 'ai-risk-benchmark' ),
					),
					'low' => array(
						'signal'      => __( 'Lower exposure', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'Exposure risk is comparatively lower — keep verifying as AI use grows.', 'ai-risk-benchmark' ),
					),
				),
				'dependency' => array(
					'high' => array(
						'signal'      => __( 'High reliance', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'You may be over-relying on AI — practise core tasks without it first.', 'ai-risk-benchmark' ),
					),
					'moderate' => array(
						'signal'      => __( 'Moderate reliance', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'Build habits of independent work before reaching for AI tools.', 'ai-risk-benchmark' ),
					),
					'low' => array(
						'signal'      => __( 'Lower reliance', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'You maintain healthy independence from AI — model this for pupils.', 'ai-risk-benchmark' ),
					),
				),
			),
			'focus_section_heading'       => __( 'Priority focus areas — what to strengthen', 'ai-risk-benchmark' ),
			'focus_section_heading_short' => __( 'Priority focus areas', 'ai-risk-benchmark' ),
			'domains_section_heading'       => __( 'Readiness score — by domain', 'ai-risk-benchmark' ),
			'domains_section_heading_short' => __( 'By domain', 'ai-risk-benchmark' ),
			'strengths_heading_short'     => __( 'Strengths', 'ai-risk-benchmark' ),
			'focus_practice_heading'      => __( 'What this means in practice', 'ai-risk-benchmark' ),
			'focus_practice_heading_short'=> __( 'In practice this means', 'ai-risk-benchmark' ),
			'oversight_section_heading'       => __( 'Human oversight', 'ai-risk-benchmark' ),
			'oversight_section_heading_short' => __( 'Oversight', 'ai-risk-benchmark' ),
			'rollout_section_heading'       => __( 'Your next unlock — whole-school picture', 'ai-risk-benchmark' ),
			'rollout_section_heading_short' => __( 'Your next unlock', 'ai-risk-benchmark' ),
			'rollout_intro' => __( 'When enough colleagues at your school complete the teacher benchmark, your SLT unlocks aggregated school data — including dependency, oversight and literacy across staff.', 'ai-risk-benchmark' ),
			'rollout_intro_short' => __( 'Unlock your whole-school teacher picture when colleagues complete their audits.', 'ai-risk-benchmark' ),
			'rollout_rollout_cta' => __( 'Encourage colleagues to take the benchmark', 'ai-risk-benchmark' ),
			'help_support_heading'       => __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ),
			'help_support_heading_short' => __( 'Read more & tips', 'ai-risk-benchmark' ),
			'summary_title'           => __( 'Your summary', 'ai-risk-benchmark' ),
			'where_you_stand_heading' => __( 'Where you stand', 'ai-risk-benchmark' ),
			'hero_next_step_heading'  => __( 'Your next step', 'ai-risk-benchmark' ),
			'hero_next_step_heading_short' => __( 'Your next step', 'ai-risk-benchmark' ),
			'domains_accordion_title' => __( 'Domain breakdown', 'ai-risk-benchmark' ),
			'benchmark_accordion_title' => __( 'Teacher benchmark summary', 'ai-risk-benchmark' ),
			'strengths_heading'     => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
			'opportunities_heading' => __( 'Opportunities to strengthen further', 'ai-risk-benchmark' ),
			'share_hint'            => __( 'Share your results with your SLT or colleagues to help build your school\'s whole-school AI picture.', 'ai-risk-benchmark' ),
			'champion_pathway' => array(
				'title'             => __( 'AI Champion Pathway', 'ai-risk-benchmark' ),
				'intro'             => __( 'Based on your results, you demonstrate strong AI awareness and responsible usage behaviours. You may be well placed to:', 'ai-risk-benchmark' ),
				'roles'             => array(
					__( 'Support colleagues adopting AI safely', 'ai-risk-benchmark' ),
					__( 'Share effective verification practices', 'ai-risk-benchmark' ),
					__( 'Contribute to school AI policy development', 'ai-risk-benchmark' ),
					__( 'Participate in AI Awareness Day activities', 'ai-risk-benchmark' ),
				),
				'resources_heading' => __( 'Suggested resources', 'ai-risk-benchmark' ),
				'next_step_label'   => __( 'Recommended next step', 'ai-risk-benchmark' ),
			),
			'suggested_resources' => array(
				array(
					'label' => __( 'Teacher Verification Framework', 'ai-risk-benchmark' ),
					'url'   => self::contact_page_url(),
				),
				array(
					'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ),
					'url'   => 'https://aiawarenessday.co.uk/resources/',
				),
				array(
					'label' => __( 'DfE AI Guidance Update Briefing', 'ai-risk-benchmark' ),
					'url'   => self::dfe_url_generative_ai(),
				),
				array(
					'label' => __( 'AI Awareness Day Toolkit', 'ai-risk-benchmark' ),
					'url'   => 'https://aiawarenessday.co.uk/',
				),
			),
			'benchmark_summary_title' => __( 'Teacher Benchmark — score recap', 'ai-risk-benchmark' ),
			'school_contribution' => array(
				'heading' => __( 'School contribution', 'ai-risk-benchmark' ),
				'intro'   => __( 'Your result contributes to your school\'s:', 'ai-risk-benchmark' ),
				'items'   => array(
					__( 'DfE Alignment Score', 'ai-risk-benchmark' ),
					__( 'AI Dependency Index', 'ai-risk-benchmark' ),
					__( 'Human Oversight Benchmark', 'ai-risk-benchmark' ),
				),
				'closing' => __( 'Encourage colleagues to complete the assessment to build a more accurate whole-school picture.', 'ai-risk-benchmark' ),
			),
			'school_impact' => array(
				'heading' => __( 'School impact', 'ai-risk-benchmark' ),
				'intro'   => __( 'Your responses help build an anonymised picture of:', 'ai-risk-benchmark' ),
				'items'   => array(
					__( 'Teacher AI dependency', 'ai-risk-benchmark' ),
					__( 'Human oversight behaviours', 'ai-risk-benchmark' ),
					__( 'Privacy awareness', 'ai-risk-benchmark' ),
					__( 'AI literacy', 'ai-risk-benchmark' ),
				),
				'closing' => __( 'across your school community.', 'ai-risk-benchmark' ),
			),
			'school_progress' => array(
				'with_count'    => __( 'Your school currently has {n} teacher responses. A whole-school benchmark becomes available after {threshold} responses.', 'ai-risk-benchmark' ),
				'without_school'=> __( 'Add your school name when you retake the audit to track progress toward a whole-school benchmark.', 'ai-risk-benchmark' ),
				'unlocked'      => __( 'Your school has reached the threshold for a whole-school benchmark view.', 'ai-risk-benchmark' ),
			),
			'future_offer' => array(
				'heading'         => __( 'When your school benchmark is ready', 'ai-risk-benchmark' ),
				'body'            => __( 'Would you like a leadership report, policy review or AI Awareness Day based on your school\'s benchmark findings?', 'ai-risk-benchmark' ),
				'heading_unlocked'=> __( 'Whole-school benchmark available', 'ai-risk-benchmark' ),
				'body_unlocked'   => __( 'Your school has enough teacher responses for a meaningful benchmark. Would you like a leadership report, policy review or AI Awareness Day based on your school\'s findings?', 'ai-risk-benchmark' ),
			),
			'gap_pathway' => array(
				'next_step_label' => __( 'Recommended next step', 'ai-risk-benchmark' ),
				'intro'           => __( 'Based on your results, we recommend focusing on:', 'ai-risk-benchmark' ),
			),
			'hero_bands' => self::readiness_hero_bands( 'teacher' ),
		);

		$tier_data = self::role_tier_data( 'teacher' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * Education support staff results — operations, data and oversight focus.
	 *
	 * @return array<string, mixed>
	 */
	public static function support_result_config(): array {
		$base = array(
			'headlines' => array(
				'strong'      => __( 'You demonstrate strong AI readiness for an operations and data-handling role.', 'ai-risk-benchmark' ),
				'established' => __( 'You show solid awareness with a few operational areas to strengthen.', 'ai-risk-benchmark' ),
				'developing'  => __( 'You have a foundation to build on — focus on data protection, oversight and approved tools.', 'ai-risk-benchmark' ),
				'emerging'    => __( 'There are important gaps in how AI is used in your operational role. Start with the priority focus below.', 'ai-risk-benchmark' ),
				'leading'     => __( 'You model responsible AI use in a support role and are well placed to guide colleagues.', 'ai-risk-benchmark' ),
			),
			'strength_labels' => array(
				'privacy'         => __( 'Strong data protection awareness', 'ai-risk-benchmark' ),
				'human_oversight' => __( 'Strong verification of AI outputs', 'ai-risk-benchmark' ),
				'safe_adoption'   => __( 'Clear use of approved tools and reporting routes', 'ai-risk-benchmark' ),
				'ai_literacy'     => __( 'Good understanding of AI limitations', 'ai-risk-benchmark' ),
				'low_dependency'  => __( 'Balanced operational use of AI', 'ai-risk-benchmark' ),
			),
			'opportunity_copy' => array(
				'privacy' => array(
					'focus_label' => __( 'Privacy & data protection', 'ai-risk-benchmark' ),
					'summary'     => __( 'Never enter pupil, HR or safeguarding information into public AI tools without approval.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Support staff often handle the highest volumes of sensitive data — clear rules and habits reduce ICO and safeguarding risk.', 'ai-risk-benchmark' ),
				),
				'human_oversight' => array(
					'focus_label' => __( 'Human oversight', 'ai-risk-benchmark' ),
					'summary'     => __( 'Review and verify AI-generated emails, letters and reports before they are sent or acted on.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Automation bias is common in admin roles where AI saves time on communications.', 'ai-risk-benchmark' ),
				),
				'safe_adoption' => array(
					'focus_label' => __( 'Safe adoption', 'ai-risk-benchmark' ),
					'summary'     => __( 'Use only approved tools and know how to report AI-related data or safeguarding issues.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Schools and trusts need consistent adoption rules across reception, finance, HR and operations teams.', 'ai-risk-benchmark' ),
				),
				'ai_literacy' => array(
					'focus_label' => __( 'AI literacy', 'ai-risk-benchmark' ),
					'summary'     => __( 'Treat AI outputs as drafts that can be wrong — especially for factual or policy information.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Understanding limitations helps you challenge outputs before they reach parents, staff or regulators.', 'ai-risk-benchmark' ),
				),
				'ai_dependency' => array(
					'focus_label' => __( 'Operational dependency', 'ai-risk-benchmark' ),
					'summary'     => __( 'Attempt tasks yourself before using AI; avoid starting every email or letter with a prompt.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Healthy independence keeps judgement sharp when AI is unavailable or wrong.', 'ai-risk-benchmark' ),
				),
				'safeguarding' => array(
					'focus_label' => __( 'Safeguarding awareness', 'ai-risk-benchmark' ),
					'summary'     => __( 'Support staff often work directly with vulnerable pupils or hold sensitive information. AI-specific safeguarding risks may not yet be part of your awareness.', 'ai-risk-benchmark' ),
					'detail'      => __( 'Knowing how to recognise and report AI-enabled harm is part of your duty of care.', 'ai-risk-benchmark' ),
				),
			),
			'summary_title'             => __( 'Your summary', 'ai-risk-benchmark' ),
			'where_you_stand_heading'   => __( 'Where you stand', 'ai-risk-benchmark' ),
			'hero_next_step_heading'    => __( 'Recommended next step', 'ai-risk-benchmark' ),
			'benchmark_summary_title'   => __( 'Education Support Staff Benchmark — score recap', 'ai-risk-benchmark' ),
			'strengths_heading'         => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
			'opportunities_heading'     => __( 'Priority focus', 'ai-risk-benchmark' ),
			'share_hint'                => __( 'Share your results with your line manager or AI lead to help build a whole-school picture of operational AI use.', 'ai-risk-benchmark' ),
			'help_support_heading'       => __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ),
			'help_support_heading_short' => __( 'Read more & tips', 'ai-risk-benchmark' ),
			'default_gap_items'         => array(
				__( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ),
				__( 'Verify Before You Trust Framework', 'ai-risk-benchmark' ),
				__( 'AI Privacy Guide for Schools', 'ai-risk-benchmark' ),
			),
			'gap_pathway' => array(
				'next_step_label' => __( 'Recommended next step', 'ai-risk-benchmark' ),
				'intro'           => __( 'Based on your results, we recommend focusing on:', 'ai-risk-benchmark' ),
			),
			'hero_bands' => self::readiness_hero_bands( 'support_staff' ),
			'suggested_resources' => self::support_suggested_resources(),
		);

		$tier_data = self::role_tier_data( 'support' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * Student results — learning-coach copy, metrics and resources.
	 *
	 * @return array<string, mixed>
	 */
	public static function student_result_config(): array {
		$base = array(
			'profile_title' => __( 'Your learning profile', 'ai-risk-benchmark' ),
			'learner_types_brand' => __( 'AI Learner Types', 'ai-risk-benchmark' ),
			'peer_benchmark_title' => __( 'Students like you', 'ai-risk-benchmark' ),
			'peer_benchmark_fallback' => array(
				'average'      => 51,
				'top_quartile' => 78,
			),
			'journey_title' => __( 'Your AI Learning Journey', 'ai-risk-benchmark' ),
			'journey_focus_heading' => __( 'To get there:', 'ai-risk-benchmark' ),
			'journey_retake_note' => __( 'Progress unlocks when you retake the benchmark.', 'ai-risk-benchmark' ),
			'journey_levels' => self::student_journey_levels(),
			'journey_focus_map' => array(
				'verification_skills' => __( 'Improve verification', 'ai-risk-benchmark' ),
				'ai_literacy'       => __( 'Improve AI literacy', 'ai-risk-benchmark' ),
				'privacy_awareness' => __( 'Improve privacy awareness', 'ai-risk-benchmark' ),
				'independent_thinking' => __( 'Improve independent thinking', 'ai-risk-benchmark' ),
				'bias_fairness'     => __( 'Improve bias & fairness awareness', 'ai-risk-benchmark' ),
			),
			'journey_focus_default' => array(
				__( 'Improve verification', 'ai-risk-benchmark' ),
				__( 'Improve AI literacy', 'ai-risk-benchmark' ),
				__( 'Improve privacy awareness', 'ai-risk-benchmark' ),
			),
			'learner_types' => array(
				'early_explorer' => array(
					'title'          => __( 'The Early Explorer', 'ai-risk-benchmark' ),
					'description'    => __( 'You use AI regularly but often rely on it before attempting work yourself.', 'ai-risk-benchmark' ),
					'focus_heading'  => __( 'Focus:', 'ai-risk-benchmark' ),
					'focus_items'    => array(
						__( 'Independent Thinking', 'ai-risk-benchmark' ),
						__( 'Verification', 'ai-risk-benchmark' ),
					),
				),
				'confident_checker' => array(
					'title'          => __( 'The Confident Checker', 'ai-risk-benchmark' ),
					'description'    => __( 'You use AI carefully and usually verify answers before trusting them.', 'ai-risk-benchmark' ),
					'focus_heading'  => __( 'Focus:', 'ai-risk-benchmark' ),
					'focus_items'    => array(
						__( 'Advanced prompting', 'ai-risk-benchmark' ),
						__( 'Subject mastery', 'ai-risk-benchmark' ),
					),
				),
				'ai_assistant_user' => array(
					'title'          => __( 'The AI Assistant User', 'ai-risk-benchmark' ),
					'description'    => __( 'You use AI as a support tool while maintaining independence in your learning.', 'ai-risk-benchmark' ),
					'focus_heading'  => __( 'Focus:', 'ai-risk-benchmark' ),
					'focus_items'    => array(
						__( 'Critical thinking', 'ai-risk-benchmark' ),
						__( 'Privacy', 'ai-risk-benchmark' ),
					),
				),
				'over_reliant' => array(
					'title'          => __( 'The Over-Reliant User', 'ai-risk-benchmark' ),
					'description'    => __( 'AI is beginning to replace your own thinking — small habit changes can make a big difference.', 'ai-risk-benchmark' ),
					'focus_heading'  => __( 'Focus:', 'ai-risk-benchmark' ),
					'focus_items'    => array(
						__( 'Think First, Prompt Second™', 'ai-risk-benchmark' ),
					),
				),
			),
			'headlines'     => array(
				'strong'      => __( 'You are building strong AI learning habits — keep verifying, thinking independently and protecting your privacy.', 'ai-risk-benchmark' ),
				'established' => __( 'You show moderate reliance on AI and reasonable awareness. Small changes to how you use AI could strengthen your learning even further.', 'ai-risk-benchmark' ),
				'developing'  => __( 'You are learning how to use AI — the tips below will help you build confidence, independence and safer habits.', 'ai-risk-benchmark' ),
				'emerging'    => __( 'There is lots to learn about using AI well. Start with the guidance below — it is designed to help, not judge.', 'ai-risk-benchmark' ),
			),
			'strength_labels' => array(
				'ai_literacy'    => __( 'You understand how AI works', 'ai-risk-benchmark' ),
				'verification'   => __( 'You show some evidence of checking AI outputs', 'ai-risk-benchmark' ),
				'healthy_habits' => __( 'You are developing healthy AI habits', 'ai-risk-benchmark' ),
				'honest_work'    => __( 'You are thinking about honest use of AI in your work', 'ai-risk-benchmark' ),
			),
			'summary_title'           => __( 'Your summary', 'ai-risk-benchmark' ),
			'where_you_stand_heading' => __( 'Where you stand', 'ai-risk-benchmark' ),
			'hero_next_step_heading'  => __( 'Your next step', 'ai-risk-benchmark' ),
			'retake_cta'              => __( 'Retake the benchmark', 'ai-risk-benchmark' ),
			'journey_accordion_title' => __( 'Your AI learning journey', 'ai-risk-benchmark' ),
			'opportunities_accordion_title' => __( 'Opportunities to improve', 'ai-risk-benchmark' ),
			'strengths_heading'     => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
			'strengths_heading_short' => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
			'opportunities_heading' => __( 'Opportunities to improve', 'ai-risk-benchmark' ),
			'focus_label_map' => array(
				'think_first' => array( 'metric' => 'independent_thinking' ),
				'privacy'     => array( 'metric' => 'privacy_awareness' ),
				'verify'      => array( 'metric' => 'verification_skills' ),
				'fairness'    => array( 'metric' => 'bias_fairness' ),
			),
			'opportunity_topics'    => array(
				array(
					'slug'     => 'think_first',
					'label'    => __( 'Think first, prompt second', 'ai-risk-benchmark' ),
					'triggers' => array( 'ai_dependency', 'assessment_integrity', 'dependency_index' ),
					'summary'  => __( 'You may be relying on AI too early in the learning process.', 'ai-risk-benchmark' ),
					'detail'   => __( 'Try completing a first attempt yourself before asking AI for help. This helps build confidence, memory and independent thinking.', 'ai-risk-benchmark' ),
					'tips'     => array(),
				),
				array(
					'slug'     => 'verify',
					'label'    => __( 'Verify more often', 'ai-risk-benchmark' ),
					'triggers' => array( 'human_oversight' ),
					'summary'  => __( 'You already show some good verification habits.', 'ai-risk-benchmark' ),
					'detail'   => __( 'To improve further:', 'ai-risk-benchmark' ),
					'tips'     => array(
						__( 'Check AI answers against trusted sources', 'ai-risk-benchmark' ),
						__( 'Ask "How do you know?"', 'ai-risk-benchmark' ),
						__( 'Explain the answer in your own words', 'ai-risk-benchmark' ),
						__( 'If you cannot explain it yourself, you may not fully understand it yet.', 'ai-risk-benchmark' ),
					),
				),
				array(
					'slug'     => 'privacy',
					'label'    => __( 'Protect your privacy', 'ai-risk-benchmark' ),
					'triggers' => array( 'privacy' ),
					'summary'  => __( 'Be careful about sharing:', 'ai-risk-benchmark' ),
					'detail'   => __( 'Public AI tools do not need these details to help you learn.', 'ai-risk-benchmark' ),
					'tips'     => array(
						__( 'Full names', 'ai-risk-benchmark' ),
						__( 'School names', 'ai-risk-benchmark' ),
						__( 'Photos', 'ai-risk-benchmark' ),
						__( 'Addresses', 'ai-risk-benchmark' ),
						__( 'Personal information', 'ai-risk-benchmark' ),
					),
				),
				array(
					'slug'     => 'fairness',
					'label'    => __( 'Spot unfair AI answers', 'ai-risk-benchmark' ),
					'triggers' => array( 'bias_readiness' ),
					'summary'  => __( 'AI can produce unfair or stereotypical answers about groups of people.', 'ai-risk-benchmark' ),
					'detail'   => __( 'Learning to spot bias helps you use AI more safely and treat others with respect.', 'ai-risk-benchmark' ),
					'tips'     => array(
						__( 'Ask whether an answer could upset or exclude someone', 'ai-risk-benchmark' ),
						__( 'Watch for stereotypes about gender, ethnicity or disability', 'ai-risk-benchmark' ),
						__( 'Tell a teacher if AI content seems discriminatory', 'ai-risk-benchmark' ),
					),
				),
			),
			'learning_challenge' => array(
				'label'  => __( 'Recommended next step', 'ai-risk-benchmark' ),
				'title'  => __( 'AI Learning Skills Challenge', 'ai-risk-benchmark' ),
				'intro'  => __( 'This week:', 'ai-risk-benchmark' ),
				'steps'  => array(
					__( 'Complete one piece of work without AI.', 'ai-risk-benchmark' ),
					__( 'Use AI only after your first attempt.', 'ai-risk-benchmark' ),
					__( 'Compare your answer with the AI response.', 'ai-risk-benchmark' ),
					__( 'Identify one thing the AI got wrong.', 'ai-risk-benchmark' ),
				),
				'closing' => __( 'You\'ll strengthen both your subject knowledge and your AI skills.', 'ai-risk-benchmark' ),
			),
			'weekly_challenge' => array(
				'title'       => __( 'Your next challenge', 'ai-risk-benchmark' ),
				'intro'       => __( 'This week:', 'ai-risk-benchmark' ),
				'items'       => array(
					__( 'Complete one task without AI', 'ai-risk-benchmark' ),
					__( 'Verify three AI answers', 'ai-risk-benchmark' ),
					__( 'Spot one AI mistake', 'ai-risk-benchmark' ),
					__( 'Explain an answer in your own words', 'ai-risk-benchmark' ),
				),
				'retake_note' => __( 'Retake the benchmark to unlock your progress and see how you\'ve improved.', 'ai-risk-benchmark' ),
			),
			'help_support_heading'       => __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ),
			'help_support_heading_short' => __( 'Read more & tips', 'ai-risk-benchmark' ),
			'resources_heading' => __( 'Study resources', 'ai-risk-benchmark' ),
			'student_resources' => array(
				array(
					'label'       => __( 'Think First, Prompt Second — student guide', 'ai-risk-benchmark' ),
					'description' => __( 'How to use AI as a thinking tool, not a shortcut', 'ai-risk-benchmark' ),
					'icon'        => 'book',
					'url'         => self::hub_page_url( 'think-first-prompt-second' ),
				),
				array(
					'label'       => __( 'Staying safe online — AI edition', 'ai-risk-benchmark' ),
					'description' => __( 'What to share, what to keep private, and why it matters', 'ai-risk-benchmark' ),
					'icon'        => 'shield',
					'url'         => self::hub_page_url( 'student-ai-study-skills' ),
				),
				array(
					'label'       => __( 'How good are you at spotting AI mistakes?', 'ai-risk-benchmark' ),
					'description' => __( 'Interactive challenge — test your verification skills', 'ai-risk-benchmark' ),
					'icon'        => 'brain',
					'url'         => self::hub_page_url( 'how-to-check-ai-answers' ),
				),
			),
			'school_contribution' => array(
				'heading' => __( 'School contribution', 'ai-risk-benchmark' ),
				'body'    => __( 'Your anonymous responses help your school understand how students are really using AI — things like attempting work before using AI, verifying answers and protecting personal information. Leaders see patterns across the whole school, not individual names.', 'ai-risk-benchmark' ),
			),
			'share_hint' => __( 'These results are for you. You can share them with a teacher if you want help with any of the areas above.', 'ai-risk-benchmark' ),
		);

		$tier_data = self::role_tier_data( 'student' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * School leader results — governance benchmark, executive summary and commercial next steps.
	 *
	 * @return array<string, mixed>
	 */
	public static function leader_result_config(): array {
		$base = array(
			'executive_title' => __( 'Executive Summary', 'ai-risk-benchmark' ),
			'executive_intros' => array(
				'leading'     => __( 'Your school demonstrates strong foundations for safe, governed AI adoption with consistent practices across most domains.', 'ai-risk-benchmark' ),
				'strong'      => __( 'Your school shows strong AI readiness with embedded practices — focus now on sustaining consistency across all staff groups.', 'ai-risk-benchmark' ),
				'established'   => __( 'Your school demonstrates a solid foundation for safe AI adoption.', 'ai-risk-benchmark' ),
				'developing'    => __( 'Your school has started its AI journey with meaningful awareness in place, but governance and oversight need strengthening.', 'ai-risk-benchmark' ),
				'emerging'      => __( 'Your school is at an early stage of AI governance. Focused leadership action will reduce exposure and build staff confidence.', 'ai-risk-benchmark' ),
			),
			'metric_labels' => array(
				'readiness'  => __( 'Overall readiness', 'ai-risk-benchmark' ),
				'risk'       => __( 'AI exposure risk', 'ai-risk-benchmark' ),
				'governance' => __( 'Governance maturity', 'ai-risk-benchmark' ),
				'bias'       => AIRB_Scoring::bias_readiness_label(),
			),
			'risk_score_note' => __( 'Higher % means more exposure — this is not a positive score.', 'ai-risk-benchmark' ),
			'metric_signals' => array(
				'readiness' => array(
					'emerging' => array(
						'signal'      => __( 'Action needed', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'Your school has significant AI risk exposure. Without leadership action, staff are likely using AI tools without consistent safeguards, oversight, or policy guidance in place.', 'ai-risk-benchmark' ),
					),
					'developing' => array(
						'signal'      => __( 'Needs focused action', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'Awareness is growing, but governance and oversight need strengthening before AI use scales.', 'ai-risk-benchmark' ),
					),
					'established' => array(
						'signal'      => __( 'Building foundations', 'ai-risk-benchmark' ),
						'tone'        => 'neutral',
						'consequence' => __( 'Solid foundations are in place — embed consistent practice across all staff groups.', 'ai-risk-benchmark' ),
					),
					'strong' => array(
						'signal'      => __( 'Strong position', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'Strong readiness — focus on sustaining consistency as AI use evolves.', 'ai-risk-benchmark' ),
					),
					'leading' => array(
						'signal'      => __( 'Leading practice', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'Your school demonstrates mature, governed AI adoption — maintain oversight as tools change.', 'ai-risk-benchmark' ),
					),
				),
				'risk' => array(
					'critical' => array(
						'signal'      => __( 'Critical exposure', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'Behavioural AI risk is high across your school — prioritise leadership intervention now.', 'ai-risk-benchmark' ),
					),
					'high' => array(
						'signal'      => __( 'High exposure', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'This is your risk score — higher means more exposure. Most schools at this stage sit above 60%.', 'ai-risk-benchmark' ),
					),
					'moderate' => array(
						'signal'      => __( 'Moderate exposure', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'Some exposure gaps remain — address weak domains before they become entrenched.', 'ai-risk-benchmark' ),
					),
					'low' => array(
						'signal'      => __( 'Lower exposure', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'Exposure risk is comparatively lower — keep monitoring as AI use grows.', 'ai-risk-benchmark' ),
					),
				),
				'governance' => array(
					'emerging' => array(
						'signal'      => __( 'Critical governance gaps', 'ai-risk-benchmark' ),
						'tone'        => 'urgent',
						'consequence' => __( 'Formal policy, staff training and monitoring are not yet in place.', 'ai-risk-benchmark' ),
					),
					'developing' => array(
						'signal'      => __( 'Gaps in policy & practice', 'ai-risk-benchmark' ),
						'tone'        => 'warning',
						'consequence' => __( 'Initial awareness exists but staff practice and oversight are inconsistent across the school.', 'ai-risk-benchmark' ),
					),
					'established' => array(
						'signal'      => __( 'Foundations in place', 'ai-risk-benchmark' ),
						'tone'        => 'neutral',
						'consequence' => __( 'Policies and training exist — embed consistent governance across every team.', 'ai-risk-benchmark' ),
					),
					'leading' => array(
						'signal'      => __( 'Strong governance', 'ai-risk-benchmark' ),
						'tone'        => 'positive',
						'consequence' => __( 'Embedded governance and oversight — sustain monitoring as AI adoption grows.', 'ai-risk-benchmark' ),
					),
				),
			),
			'strengths_heading' => __( 'Strengths include', 'ai-risk-benchmark' ),
			'attention_heading' => __( 'Areas requiring attention', 'ai-risk-benchmark' ),
			'share_hint'        => __( 'Share these results with your governing body or leadership team to align on next steps.', 'ai-risk-benchmark' ),
			'domain_labels' => array(
				'ai_literacy'          => __( 'AI Literacy', 'ai-risk-benchmark' ),
				'privacy'              => __( 'Data Protection Awareness', 'ai-risk-benchmark' ),
				'assessment_integrity' => __( 'Assessment Controls', 'ai-risk-benchmark' ),
				'human_oversight'      => __( 'Human Oversight', 'ai-risk-benchmark' ),
				'safeguarding'         => __( 'Safeguarding', 'ai-risk-benchmark' ),
				'governance'           => __( 'Governance Consistency', 'ai-risk-benchmark' ),
				'safe_adoption'        => __( 'Safe Adoption', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'Independent Practice', 'ai-risk-benchmark' ),
			),
			'maturity_levels' => array(
				array( 'slug' => 'emerging', 'label' => __( 'Emerging', 'ai-risk-benchmark' ), 'min' => 0, 'max' => 25 ),
				array( 'slug' => 'developing', 'label' => __( 'Developing', 'ai-risk-benchmark' ), 'min' => 26, 'max' => 50 ),
				array( 'slug' => 'established', 'label' => __( 'Established', 'ai-risk-benchmark' ), 'min' => 51, 'max' => 75 ),
				array( 'slug' => 'leading', 'label' => __( 'Leading', 'ai-risk-benchmark' ), 'min' => 76, 'max' => 100 ),
			),
			'maturity_descriptions' => array(
				'emerging'    => __( 'Schools at this stage are beginning to recognise AI risks but typically lack formal policy, staff training and monitoring.', 'ai-risk-benchmark' ),
				'developing'  => __( 'Schools at this stage often have initial awareness and draft policies but inconsistent staff practice and limited oversight.', 'ai-risk-benchmark' ),
				'established' => __( 'Schools at this stage typically have policies and awareness training in place but have not yet embedded consistent AI governance and monitoring across all staff groups.', 'ai-risk-benchmark' ),
				'leading'     => __( 'Schools at this stage demonstrate embedded governance, consistent staff verification and proactive monitoring of AI adoption.', 'ai-risk-benchmark' ),
			),
			'maturity_heading' => AIRB_Scoring::governance_maturity_label(),
			'peer_benchmark_title' => __( 'Benchmark against similar schools', 'ai-risk-benchmark' ),
			'peer_phase_labels' => array(
				'primary'    => __( 'Average Primary School', 'ai-risk-benchmark' ),
				'secondary'  => __( 'Average Secondary School', 'ai-risk-benchmark' ),
				'college'    => __( 'Average College', 'ai-risk-benchmark' ),
				'university' => __( 'Average University', 'ai-risk-benchmark' ),
				'other'      => __( 'Average Organisation', 'ai-risk-benchmark' ),
				'default'    => __( 'Average School', 'ai-risk-benchmark' ),
			),
			'peer_benchmark_fallback' => array(
				'primary'    => array( 'average' => 58, 'top_quartile' => 80 ),
				'secondary'  => array( 'average' => 62, 'top_quartile' => 84 ),
				'college'    => array( 'average' => 61, 'top_quartile' => 83 ),
				'university' => array( 'average' => 61, 'top_quartile' => 83 ),
				'other'      => array( 'average' => 60, 'top_quartile' => 82 ),
				'default'    => array( 'average' => 62, 'top_quartile' => 84 ),
			),
			'focus_heading' => __( 'Priority focus areas', 'ai-risk-benchmark' ),
			'focus_section_heading' => __( 'Priority focus areas — what to fix and how', 'ai-risk-benchmark' ),
			'focus_section_heading_short' => __( 'Priority focus areas', 'ai-risk-benchmark' ),
			'focus_practice_heading' => __( 'What this means in practice', 'ai-risk-benchmark' ),
			'focus_practice_heading_short' => __( 'In practice this means', 'ai-risk-benchmark' ),
			'heatmap_section_heading' => __( 'Full risk picture', 'ai-risk-benchmark' ),
			'heatmap_card_title' => __( 'Risk heat map — all domains', 'ai-risk-benchmark' ),
			'heatmap_card_title_short' => __( 'Risk heat map', 'ai-risk-benchmark' ),
			'heatmap_card_help' => __( 'Showing risk exposure %. Higher = more risk in that area.', 'ai-risk-benchmark' ),
			'rollout_section_heading' => __( 'Your next unlock — whole-school picture', 'ai-risk-benchmark' ),
			'rollout_section_heading_short' => __( 'Your next unlock', 'ai-risk-benchmark' ),
			'rollout_intro' => __( 'You\'ve completed the leader audit. When your staff, students and parents complete theirs, you\'ll unlock aggregated school data — including teacher AI dependency, student verification skills and parent awareness gaps.', 'ai-risk-benchmark' ),
			'rollout_intro_short' => __( 'Get your whole-school picture when staff, students and parents complete their audits.', 'ai-risk-benchmark' ),
			'rollout_rollout_cta' => __( 'Roll out to your school community', 'ai-risk-benchmark' ),
			'help_support_heading' => __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ),
			'help_support_heading_short' => __( 'Read more & tips', 'ai-risk-benchmark' ),
			'bias_health_title' => AIRB_Scoring::bias_readiness_label(),
			'bias_health_subtitle' => __( 'Safeguarding · protected characteristics · PSED', 'ai-risk-benchmark' ),
			'bias_health_callout_threshold' => 50,
			'bias_health_callout' => __( 'Your school has not yet assessed whether AI tools could produce unfair or discriminatory outputs. This is a safeguarding and equality duty risk.', 'ai-risk-benchmark' ),
			'rollout_locked_items' => array(
				array(
					'label'     => __( 'Teacher AI dependency', 'ai-risk-benchmark' ),
					'count_key' => 'teacher',
				),
				array(
					'label'     => __( 'Student verification skills', 'ai-risk-benchmark' ),
					'count_key' => 'student',
				),
				array(
					'label'     => __( 'Parent awareness', 'ai-risk-benchmark' ),
					'count_key' => 'parent',
				),
				array(
					'label'     => __( 'School risk heatmap', 'ai-risk-benchmark' ),
					'count_key' => 'total',
				),
			),
			'where_you_stand_heading' => __( 'Where your school stands', 'ai-risk-benchmark' ),
			'focus_actions_label' => __( 'Recommended actions', 'ai-risk-benchmark' ),
			'likely_impact_intro' => __( 'Schools with scores at this level often experience:', 'ai-risk-benchmark' ),
			'hero_understand_label' => __( 'Understand:', 'ai-risk-benchmark' ),
			'focus_copy' => array(
				'human_oversight' => array(
					'summary' => __( 'Staff are using AI regularly but verification is inconsistent across departments. Without a school-wide standard, some staff are likely using AI output directly with pupils without checking it first.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'inconsistent staff practice across departments', 'ai-risk-benchmark' ),
						__( 'over-trust in AI outputs without verification', 'ai-risk-benchmark' ),
						__( 'uncertainty about appropriate classroom use', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Introduce a whole-school AI verification standard', 'ai-risk-benchmark' ),
						__( 'Embed the Verify Before You Trust framework in CPD', 'ai-risk-benchmark' ),
						__( 'Require sign-off on AI-generated materials before classroom use', 'ai-risk-benchmark' ),
					),
				),
				'governance' => array(
					'summary' => __( 'AI governance is not yet consistent across all staff groups and departments.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'ad hoc AI tool adoption without oversight', 'ai-risk-benchmark' ),
						__( 'unclear accountability for AI decisions', 'ai-risk-benchmark' ),
						__( 'policy that exists on paper but not in practice', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Publish or refresh your AI policy', 'ai-risk-benchmark' ),
						__( 'Name a senior AI lead with clear accountability', 'ai-risk-benchmark' ),
						__( 'Schedule an annual governance review', 'ai-risk-benchmark' ),
					),
				),
				'safeguarding' => array(
					'summary' => __( 'Your safeguarding procedures may not yet cover AI-specific harms. KCSIE expects DSLs to understand online risks — deepfakes, AI-enabled impersonation and synthetic content are now part of that landscape.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'Online safety procedures in place but AI-specific harms not covered', 'ai-risk-benchmark' ),
						__( 'DSL and pastoral teams may not recognise deepfake or AI impersonation incidents', 'ai-risk-benchmark' ),
						__( 'No AI scenario training means staff may not know how to respond or report', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Update your safeguarding policy to include AI-enabled harm', 'ai-risk-benchmark' ),
						__( 'Train your DSL and pastoral teams on deepfake risks', 'ai-risk-benchmark' ),
						__( 'Add AI scenarios to annual safeguarding training', 'ai-risk-benchmark' ),
					),
				),
				'assessment_integrity' => array(
					'summary' => __( 'Your assessment controls may not yet reflect current JCQ and Ofqual expectations. Schools at this level often have inconsistent rules across departments — some staff communicating AI boundaries clearly, others not at all.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Conduct a JCQ-aligned assessment review across departments', 'ai-risk-benchmark' ),
						__( 'Update malpractice and exam supervision procedures', 'ai-risk-benchmark' ),
						__( 'Communicate AI rules clearly and consistently to all pupils', 'ai-risk-benchmark' ),
					),
				),
				'privacy' => array(
					'summary' => __( 'Staff may not consistently understand what pupil data can enter AI tools. Without a DPIA and approved tool list, your school has no documented basis for lawful processing under UK GDPR.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'Staff choosing AI tools individually without data review', 'ai-risk-benchmark' ),
						__( 'Pupil names, SEND data or safeguarding notes potentially entering third-party AI', 'ai-risk-benchmark' ),
						__( 'No audit trail if an ICO complaint or subject access request is raised', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Complete the AI data protection checklist with your DPO', 'ai-risk-benchmark' ),
						__( 'Publish clear staff guidance on what data can enter AI tools', 'ai-risk-benchmark' ),
						__( 'Review your approved tool list against ICO expectations', 'ai-risk-benchmark' ),
					),
				),
				'ai_literacy' => array(
					'summary' => __( 'Staff AI literacy varies — not all colleagues understand limitations and verification needs.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'inconsistent staff practice', 'ai-risk-benchmark' ),
						__( 'over-trust in AI outputs', 'ai-risk-benchmark' ),
						__( 'uncertainty around appropriate use', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Deliver whole-staff AI literacy training', 'ai-risk-benchmark' ),
						__( 'Cover hallucinations, bias and no-go situations', 'ai-risk-benchmark' ),
						__( 'Build AI literacy into induction for new staff', 'ai-risk-benchmark' ),
					),
				),
				'safe_adoption' => array(
					'summary' => __( 'AI tools may be adopted ad hoc without consistent risk assessment.', 'ai-risk-benchmark' ),
					'likely_impact' => array(
						__( 'unapproved tools entering school workflows', 'ai-risk-benchmark' ),
						__( 'inconsistent data protection practice', 'ai-risk-benchmark' ),
						__( 'staff unsure which tools are permitted', 'ai-risk-benchmark' ),
					),
					'actions' => array(
						__( 'Maintain an approved AI tool list', 'ai-risk-benchmark' ),
						__( 'Require a simple pre-use risk check', 'ai-risk-benchmark' ),
						__( 'Review new tools with your DPO before rollout', 'ai-risk-benchmark' ),
					),
				),
				'ai_dependency' => array(
					'summary' => __( 'Staff and pupils may be relying on AI before independent thinking and verification.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Set expectations for independent work before AI use', 'ai-risk-benchmark' ),
						__( 'Monitor AI dependency through the whole-school benchmark', 'ai-risk-benchmark' ),
						__( 'Model balanced AI use in CPD and policy', 'ai-risk-benchmark' ),
					),
				),
			),
			'priority_actions' => array(
				'human_oversight'      => __( 'Strengthen whole-school governance and staff verification practices.', 'ai-risk-benchmark' ),
				'governance'           => __( 'Publish or refresh your AI policy and embed consistent governance.', 'ai-risk-benchmark' ),
				'safeguarding'         => __( 'Update safeguarding procedures for AI-specific risks.', 'ai-risk-benchmark' ),
				'assessment_integrity' => __( 'Conduct a JCQ-aligned assessment review.', 'ai-risk-benchmark' ),
				'privacy'              => __( 'Complete your AI data protection review with your DPO.', 'ai-risk-benchmark' ),
				'ai_literacy'          => __( 'Deliver whole-staff AI literacy and verification training.', 'ai-risk-benchmark' ),
				'safe_adoption'        => __( 'Establish an approved tool list and structured adoption process.', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'Reduce over-reliance on AI through policy and staff training.', 'ai-risk-benchmark' ),
			),
			'default_priority_action' => __( 'Strengthen whole-school governance and staff verification practices.', 'ai-risk-benchmark' ),
			'urgent_action_heading'   => __( 'Your single most urgent action', 'ai-risk-benchmark' ),
			'urgent_action_heading_short' => __( 'Your most urgent action', 'ai-risk-benchmark' ),
			'peer_comparison_label'   => __( 'How you compare to similar schools', 'ai-risk-benchmark' ),
			'peer_comparison_label_short' => __( 'How you compare', 'ai-risk-benchmark' ),
			'peer_phase_short'        => __( 'Avg school', 'ai-risk-benchmark' ),
			'peer_you_label'          => __( 'You', 'ai-risk-benchmark' ),
			'peer_gap_below_average'  => __( '{n} points below average', 'ai-risk-benchmark' ),
			'peer_gap_above_average'  => __( '{n} points above average', 'ai-risk-benchmark' ),
			'peer_gap_at_average'     => __( 'In line with average', 'ai-risk-benchmark' ),
			'peer_gap_below_top'      => __( '{n} points below top quartile', 'ai-risk-benchmark' ),
			'peer_gap_below_top_short' => __( '{n} below top quartile', 'ai-risk-benchmark' ),
			'peer_gap_above_top'      => __( '{n} points above top quartile', 'ai-risk-benchmark' ),
			'default_priority_rationale' => __( 'This is your highest-priority area based on your benchmark scores — address it before AI use scales across the school.', 'ai-risk-benchmark' ),
			'priority_rationales' => array(
				'privacy'              => __( 'Your data protection score is {pct}%. Staff may be entering pupil data into AI tools without a DPIA or approved tool list in place — this is your highest-priority legal and safeguarding risk.', 'ai-risk-benchmark' ),
				'safeguarding'         => __( 'Your safeguarding score is {pct}%. AI-specific risks such as deepfakes and impersonation may not yet be covered in your procedures — this needs DSL and leadership attention now.', 'ai-risk-benchmark' ),
				'governance'           => __( 'Your governance score is {pct}%. Policy may exist on paper but practice is inconsistent — without clear accountability, ad hoc AI adoption will continue.', 'ai-risk-benchmark' ),
				'human_oversight'      => __( 'Your human oversight score is {pct}%. Staff are using AI without consistent verification — unchecked outputs can reach pupils and parents.', 'ai-risk-benchmark' ),
				'assessment_integrity' => __( 'Your assessment controls score is {pct}%. JCQ and Ofqual expectations for AI use may not yet be reflected in your malpractice and supervision procedures.', 'ai-risk-benchmark' ),
				'ai_literacy'          => __( 'Your AI literacy score is {pct}%. Staff understanding of AI limitations varies — inconsistent practice increases safeguarding and data protection risk.', 'ai-risk-benchmark' ),
				'safe_adoption'        => __( 'Your safe adoption score is {pct}%. Tools may be entering school workflows without risk assessment or DPO review.', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'Your independent practice score is {pct}%. Staff and pupils may be relying on AI before thinking independently — this undermines learning and assessment integrity.', 'ai-risk-benchmark' ),
			),
			'next_steps_title'    => __( 'Next steps', 'ai-risk-benchmark' ),
			'next_steps_subtitle' => __( 'Recommended for your school', 'ai-risk-benchmark' ),
			'next_steps_intro'    => __( 'Based on your benchmark results:', 'ai-risk-benchmark' ),
			'next_step_blocks' => array(
				'policy_support' => array(
					'title'    => __( 'Develop your AI policy', 'ai-risk-benchmark' ),
					'body'     => __( 'Adapt the official DfE AI policy template to your school. Contact the AI Awareness Day team if you need support tailoring it.', 'ai-risk-benchmark' ),
					'cta_text' => __( 'View DfE AI policy template', 'ai-risk-benchmark' ),
				),
				'whole_school_benchmark' => array(
					'title' => __( 'Whole-School AI Benchmark', 'ai-risk-benchmark' ),
					'body'  => __( 'Invite teachers, students and parents to complete the benchmark to build a complete risk profile.', 'ai-risk-benchmark' ),
				),
				'ai_awareness_day' => array(
					'title'    => __( 'AI Awareness Day', 'ai-risk-benchmark' ),
					'intro'    => __( 'A one-day programme for leaders, staff, students and parents covering:', 'ai-risk-benchmark' ),
					'topics'   => array(
						__( 'Safe AI adoption', 'ai-risk-benchmark' ),
						__( 'Human oversight', 'ai-risk-benchmark' ),
						__( 'Deepfakes', 'ai-risk-benchmark' ),
						__( 'Privacy', 'ai-risk-benchmark' ),
						__( 'Assessment integrity', 'ai-risk-benchmark' ),
						__( 'DfE guidance', 'ai-risk-benchmark' ),
					),
					'cta_text' => __( 'Plan AI Awareness Day', 'ai-risk-benchmark' ),
					'cta_url'  => self::contact_page_url(),
				),
				'governance_review' => array(
					'title' => __( 'Governance review & readiness consultation', 'ai-risk-benchmark' ),
					'body'  => __( 'A structured review with an AI readiness specialist — covering where your risks sit, what to prioritise and what your school needs to move from Emerging to Established.', 'ai-risk-benchmark' ),
					'understand_items' => array(
						__( 'where your risks sit', 'ai-risk-benchmark' ),
						__( 'what action to prioritise', 'ai-risk-benchmark' ),
						__( 'what support is needed', 'ai-risk-benchmark' ),
					),
					'deliverables' => array(
						__( 'DfE alignment review', 'ai-risk-benchmark' ),
						__( 'Policy recommendations', 'ai-risk-benchmark' ),
						__( 'Risk heat map walkthrough', 'ai-risk-benchmark' ),
						__( 'Staff CPD planning', 'ai-risk-benchmark' ),
					),
					'cta_text' => __( 'Request governance review', 'ai-risk-benchmark' ),
					'cta_url'  => self::contact_page_url(),
				),
			),
			'hero_next_step_heading'      => __( 'Recommended next step', 'ai-risk-benchmark' ),
			'secondary_resources_heading' => __( 'Other resources', 'ai-risk-benchmark' ),
			'rollout_unlock_benefits' => array(
				__( 'Teacher AI dependency', 'ai-risk-benchmark' ),
				__( 'Student AI dependency', 'ai-risk-benchmark' ),
				__( 'Parent awareness', 'ai-risk-benchmark' ),
				__( 'Governance maturity', 'ai-risk-benchmark' ),
				__( 'Priority interventions', 'ai-risk-benchmark' ),
				__( 'School risk heatmap', 'ai-risk-benchmark' ),
			),
			'rollout_unlock_copy' => __( 'Unlocks after {threshold} responses from your school community.', 'ai-risk-benchmark' ),
			'heatmap_heading'     => __( 'Risk heat map', 'ai-risk-benchmark' ),
			'hero_bands'          => self::readiness_hero_bands( 'leader' ),
		);

		$tier_data = self::role_tier_data( 'leader' );

		return array_merge( $base, $tier_data );
	}

	/**
	 * Resolve a hub page URL from a path slug.
	 */
	public static function hub_page_url( string $path ): string {
		$path = trim( $path, '/' );
		if ( '' === $path ) {
			return function_exists( 'home_url' ) ? home_url( '/' ) : 'https://aiawarenessday.co.uk/';
		}
		if ( str_starts_with( $path, 'http' ) ) {
			return $path;
		}
		return function_exists( 'home_url' ) ? home_url( '/' . $path . '/' ) : 'https://aiawarenessday.co.uk/' . $path . '/';
	}

	/**
	 * Hub page URL with benchmark attribution query args.
	 *
	 * @param string $path Hub slug path.
	 * @param string $role Respondent role.
	 * @param string $ref  Weak pillar / domain reference slug.
	 */
	public static function hub_tracking_url( string $path, string $role, string $ref ): string {
		$url = self::hub_page_url( $path );
		if ( '' === trim( $path, '/' ) || str_starts_with( trim( $path ), 'http' ) ) {
			return $url;
		}
		return add_query_arg(
			array(
				'airb_role' => sanitize_key( $role ),
				'airb_ref'  => sanitize_key( $ref ),
			),
			$url
		);
	}

	/**
	 * Timeline slugs that may host the embedded benchmark (newest first).
	 *
	 * @return string[]
	 */
	public static function benchmark_page_slugs(): array {
		return array(
			'student-parent-teacher-or-school-leader-audit-your-ai-usage-with-our-ai-risk-readiness-benchmark',
			'ai-risk-readiness-benchmark',
		);
	}

	/**
	 * Canonical URL for the embedded benchmark (timeline entry when available).
	 */
	public static function benchmark_page_url(): string {
		if ( function_exists( 'get_page_by_path' ) ) {
			foreach ( self::benchmark_page_slugs() as $slug ) {
				$timeline = get_page_by_path( $slug, OBJECT, 'timeline' );
				if ( $timeline instanceof WP_Post ) {
					$url = get_permalink( $timeline );
					if ( is_string( $url ) && '' !== $url ) {
						return $url;
					}
				}
			}
		}
		return self::hub_page_url( '' ) . '#airb-benchmark';
	}

	/**
	 * Resolve one timeline entry for benchmark results "More to read" links.
	 *
	 * @param array{slug?: string, url?: string, label?: string} $item Slug and optional fallback URL/label.
	 * @return array{label: string, url: string}|null
	 */
	public static function timeline_read_link( array $item ): ?array {
		$label = (string) ( $item['label'] ?? '' );
		$url   = (string) ( $item['url'] ?? '' );
		$slug  = (string) ( $item['slug'] ?? '' );

		if ( '' !== $slug && function_exists( 'get_page_by_path' ) ) {
			$post = get_page_by_path( $slug, OBJECT, 'timeline' );
			if ( $post instanceof WP_Post ) {
				$permalink = get_permalink( $post );
				if ( is_string( $permalink ) && '' !== $permalink ) {
					$image = get_the_post_thumbnail_url( $post, 'thumbnail' );
					$title = wp_specialchars_decode( get_the_title( $post ), ENT_QUOTES );
					$title = html_entity_decode( $title, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
					return array(
						'label' => '' !== $label ? html_entity_decode( wp_specialchars_decode( $label, ENT_QUOTES ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : $title,
						'url'   => $permalink,
						'slug'  => $post->post_name,
						'image' => is_string( $image ) ? $image : '',
					);
				}
			}
		}

		if ( '' !== $url ) {
			return array(
				'label' => '' !== $label ? $label : $url,
				'url'   => $url,
				'image' => '',
			);
		}

		return null;
	}

	/**
	 * Hub intervention link with optional featured image for results cards.
	 *
	 * @param string $slug  Hub page slug.
	 * @param string $label Display label.
	 * @param string $role  Benchmark role for tracking.
	 * @param string $ref   Weak domain ref slug.
	 * @return array{label: string, url: string, image: string}
	 */
	public static function hub_resource_link( string $slug, string $label, string $role, string $ref = '' ): array {
		$image = '';
		if ( function_exists( 'get_page_by_path' ) ) {
			$page = get_page_by_path( $slug, OBJECT, 'page' );
			if ( $page instanceof WP_Post && has_post_thumbnail( $page ) ) {
				$thumb = get_the_post_thumbnail_url( $page, 'thumbnail' );
				if ( is_string( $thumb ) ) {
					$image = $thumb;
				}
			}
		}

		return array(
			'label' => $label,
			'url'   => self::hub_tracking_url( $slug, $role, $ref ),
			'image' => $image,
		);
	}

	/**
	 * Support staff results — curated hub resources (not generic homepage links).
	 *
	 * @return array<int, array{label: string, url: string, image: string}>
	 */
	public static function support_suggested_resources(): array {
		return array(
			self::hub_resource_link(
				'dfe-ai-compliance-checklist',
				__( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ),
				'support_staff',
				'privacy'
			),
			self::hub_resource_link(
				'teacher-ai-privacy-guide',
				__( 'AI Privacy Guide for Schools', 'ai-risk-benchmark' ),
				'support_staff',
				'privacy'
			),
			self::hub_resource_link(
				'teacher-ai-verification-framework',
				__( 'Verify Before You Trust Framework', 'ai-risk-benchmark' ),
				'support_staff',
				'human_oversight'
			),
			array(
				'label' => __( 'DfE Generative AI Guidance', 'ai-risk-benchmark' ),
				'url'   => self::dfe_url_generative_ai(),
				'image' => '',
				'external' => true,
			),
		);
	}

	/**
	 * Role-specific timeline articles shown after the results action zone.
	 *
	 * @param string $role teacher|student|parent|leader.
	 * @return array<int, array{label: string, url: string}>
	 */
	public static function results_timeline_read_links( string $role, int $seed = 0 ): array {
		$preferred = self::results_timeline_read_links_preferred( $role );
		if ( function_exists( 'aiad_timeline_benchmark_read_links' ) ) {
			$links = aiad_timeline_benchmark_read_links( $role, 4, $seed );
			if ( ! empty( $links ) ) {
				return self::merge_timeline_read_links( $preferred, $links );
			}
		}

		return self::merge_timeline_read_links( $preferred, self::results_timeline_read_links_fallback( $role ) );
	}

	/**
	 * Patch role-specific results payloads with seeded timeline read links.
	 *
	 * @param array<string, mixed> $results Results payload (by reference).
	 * @param string               $role    Benchmark role.
	 * @param int                  $seed    Stable shuffle seed.
	 */
	public static function patch_results_timeline_read_links( array &$results, string $role, int $seed ): void {
		$links = self::results_timeline_read_links( $role, $seed );
		if ( empty( $links ) ) {
			return;
		}

		$key = $role . '_results';
		if ( ! empty( $results[ $key ]['next_steps'] ) && is_array( $results[ $key ]['next_steps'] ) ) {
			$results[ $key ]['next_steps']['resource_links'] = $links;
		}

		if ( 'parent' === $role && ! empty( $results['parent_results'] ) && is_array( $results['parent_results'] ) ) {
			$results['parent_results']['resource_links'] = $links;
		}

		if ( 'public' === $role && ! empty( $results['public_results'] ) && is_array( $results['public_results'] ) ) {
			$results['public_results']['resource_links'] = $links;
		}
	}

	/**
	 * Hardcoded fallback when no audience-tagged timeline posts exist yet.
	 *
	 * @param string $role Benchmark role.
	 * @return array<int, array{label: string, url: string}>
	 */
	private static function results_timeline_read_links_fallback( string $role ): array {
		$role    = sanitize_key( $role );
		$presets = array(
			'student' => array(
				array( 'slug' => 'stop-asking-if-students-should-use-ai-start-asking-how-students-perspective' ),
				array( 'slug' => 'ai-mental-health-student-perspective-student-voice' ),
				array( 'slug' => 'the-future-of-ai-through-a-students-perspective' ),
			),
			'parent'  => array(
				array( 'slug' => 'parent-tips' ),
				array( 'slug' => 'bbc-bitesize-ai-awareness-day-teaching-resources' ),
				array( 'slug' => 'parent-zone' ),
			),
			'teacher' => array(
				array( 'slug' => 'misinformation-detector-teachers' ),
				array( 'slug' => '15-ai-buzzwords-teachers-2026' ),
				array( 'slug' => 'how-does-a-large-language-model-work' ),
			),
			'leader'  => array(
				array( 'slug' => 'ai-micro-credentials-and-short-courses' ),
				array(
					'slug' => '🔥🔥teachers-have-you-heard-of-ai-agents-yet🔥🔥',
					'url'  => home_url( '/timeline/%f0%9f%94%a5%f0%9f%94%a5teachers-have-you-heard-of-ai-agents-yet%f0%9f%94%a5%f0%9f%94%a5/' ),
				),
				array( 'slug' => 'beyond-the-holy-grail' ),
				array( 'slug' => 'misinformation-detector-teachers' ),
			),
			'support_staff' => array(),
		);

		$links = array();
		foreach ( (array) ( $presets[ $role ] ?? array() ) as $item ) {
			$link = self::timeline_read_link( $item );
			if ( null !== $link ) {
				$links[] = $link;
			}
		}

		return $links;
	}

	/**
	 * Preferred role resources that should appear even when timeline helper results exist.
	 *
	 * @param string $role Benchmark role.
	 * @return array<int, array{label: string, url: string}>
	 */
	private static function results_timeline_read_links_preferred( string $role ): array {
		$role    = sanitize_key( $role );
		$presets = array(
			'teacher' => array(
				array(
					'slug'  => 'ai-confidence',
					'label' => __( 'AI Confidence CPD microcredential', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'timeline/ai-confidence' ),
				),
				array(
					'slug'  => 'ai-micro-credentials-and-short-courses',
					'label' => __( 'AI Micro-Credentials and Short Courses for Students', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'timeline/ai-micro-credentials-and-short-courses' ),
				),
				array(
					'slug'  => '15-ai-buzzwords-teachers-2026',
					'label' => __( '15 AI Buzzwords Every Teacher Should Know in 2026', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'timeline/15-ai-buzzwords-teachers-2026' ),
				),
			),
			'parent' => array(
				array(
					'slug'  => 'parent-tips',
					'label' => __( 'Parent tips for AI at home', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'timeline/parent-tips' ),
				),
			),
		);

		$links = array();
		foreach ( (array) ( $presets[ $role ] ?? array() ) as $item ) {
			$link = self::timeline_read_link( $item );
			if ( null !== $link ) {
				$links[] = $link;
			}
		}
		return $links;
	}

	/**
	 * Merge resource links while preserving order and removing duplicate URLs/slugs.
	 *
	 * @param array<int, array<string, mixed>> $primary Primary links.
	 * @param array<int, array<string, mixed>> $secondary Secondary links.
	 * @return array<int, array<string, mixed>>
	 */
	private static function merge_timeline_read_links( array $primary, array $secondary ): array {
		$out  = array();
		$seen = array();
		foreach ( array_merge( $primary, $secondary ) as $link ) {
			if ( ! is_array( $link ) ) {
				continue;
			}
			$key = sanitize_key( (string) ( $link['slug'] ?? '' ) );
			if ( '' === $key ) {
				$key = md5( (string) ( $link['url'] ?? '' ) );
			}
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$out[] = $link;
		}
		return array_slice( $out, 0, 4 );
	}

	/**
	 * Level 2 guided improvement — pillar content mapped to hub pages.
	 *
	 * @return array<string, mixed>
	 */
	public static function improvement_hub_config(): array {
		$why = __( 'Why this matters', 'ai-risk-benchmark' );
		$improve = __( 'Improve your score', 'ai-risk-benchmark' );

		return array(
			'labels' => array(
				'heading' => __( 'Your priority next step', 'ai-risk-benchmark' ),
				'intro'   => __( 'Your benchmark flagged one area to strengthen first. Work through this resource, then retake the audit to measure improvement.', 'ai-risk-benchmark' ),
			),
			'resource_kinds' => array(
				'read'     => __( 'Read', 'ai-risk-benchmark' ),
				'watch'    => __( 'Watch', 'ai-risk-benchmark' ),
				'download' => __( 'Download', 'ai-risk-benchmark' ),
				'join'     => __( 'Join', 'ai-risk-benchmark' ),
			),
			'leader_consultation' => array(
				'title'    => __( 'Book a free 30-minute AI Readiness Review', 'ai-risk-benchmark' ),
				'intro'    => __( 'We\'ll walk through:', 'ai-risk-benchmark' ),
				'items'    => array(
					__( 'Your benchmark scores', 'ai-risk-benchmark' ),
					__( 'DfE alignment', 'ai-risk-benchmark' ),
					__( 'Governance gaps', 'ai-risk-benchmark' ),
					__( 'Staff readiness', 'ai-risk-benchmark' ),
					__( 'Recommended next steps', 'ai-risk-benchmark' ),
				),
				'closing'  => __( 'No obligation.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'Book your free review', 'ai-risk-benchmark' ),
				'cta_path' => 'contact',
			),
			'roles' => array(
				'teacher' => array(
					'domains' => array(
						'human_oversight' => array(
							'slug'         => 'human_oversight',
							'metric_label' => __( 'Human Oversight Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Teachers who rely heavily on AI without verification may increase the risk of:', 'ai-risk-benchmark' ),
							'why_risks'    => array(
								__( 'Inaccurate content', 'ai-risk-benchmark' ),
								__( 'Inappropriate examples', 'ai-risk-benchmark' ),
								__( 'Hallucinations', 'ai-risk-benchmark' ),
								__( 'Over-reliance', 'ai-risk-benchmark' ),
							),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Verify Before You Trust Framework', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
								array( 'kind' => 'download', 'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-lesson-planning-checklist' ),
								array( 'kind' => 'join', 'label' => __( 'AI Awareness Day Teacher Session', 'ai-risk-benchmark' ), 'path' => 'ai-awareness-day' ),
							),
						),
						'privacy' => array(
							'slug'         => 'privacy',
							'metric_label' => __( 'Privacy & Data Protection Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Entering pupil or SEND data into public AI tools can create data protection and safeguarding exposure.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Teacher AI Privacy Guide', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-privacy-guide' ),
								array( 'kind' => 'download', 'label' => __( 'Pupil Data Reminder Card', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-privacy-guide' ),
							),
						),
						'ai_literacy' => array(
							'slug'         => 'ai_literacy',
							'metric_label' => __( 'AI Literacy Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Strong AI literacy helps teachers spot errors, explain limits to pupils and model responsible use.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'DfE AI Guidance Briefing', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-assessment-guide' ),
								array( 'kind' => 'read', 'label' => __( 'How AI Gets Things Wrong', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
							),
						),
						'assessment_integrity' => array(
							'slug'         => 'assessment_integrity',
							'metric_label' => __( 'Assessment Awareness Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Unchecked AI use in planning and feedback can undermine assessment integrity and pupil learning.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Teacher AI Assessment Guide', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-assessment-guide' ),
								array( 'kind' => 'download', 'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-lesson-planning-checklist' ),
							),
						),
					),
					'metrics' => array(
						'ai_dependency' => array(
							'slug'         => 'ai_dependency',
							'score_type'   => 'dependency',
							'metric_label' => __( 'Independent Practice', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'High reliance on AI can reduce independent professional judgement and model over-reliance for pupils.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Think First, Prompt Second™ Framework', 'ai-risk-benchmark' ), 'path' => 'think-first-prompt-second' ),
							),
						),
					),
				),
				'student' => array(
					'domains' => array(
						'human_oversight' => array(
							'slug'         => 'verification',
							'metric_label' => __( 'Verification Skills Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'AI can sound confident even when it is wrong. Checking answers builds real understanding.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'How To Check AI Answers', 'ai-risk-benchmark' ), 'path' => 'how-to-check-ai-answers' ),
								array( 'kind' => 'download', 'label' => __( 'Verify Before You Trust Checklist', 'ai-risk-benchmark' ), 'path' => 'how-to-check-ai-answers' ),
							),
						),
						'privacy' => array(
							'slug'         => 'privacy',
							'metric_label' => __( 'Privacy Awareness Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Public AI tools do not need your name, school or personal photos to help you learn.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Student AI Privacy Guide', 'ai-risk-benchmark' ), 'path' => 'student-ai-privacy-guide' ),
							),
						),
						'assessment_integrity' => array(
							'slug'         => 'honesty',
							'metric_label' => __( 'Independent Thinking Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Using AI before attempting work yourself can reduce learning, confidence and retention.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Think First, Prompt Second Guide', 'ai-risk-benchmark' ), 'path' => 'think-first-prompt-second' ),
								array( 'kind' => 'join', 'label' => __( 'Student AI Study Skills Challenge', 'ai-risk-benchmark' ), 'path' => 'student-ai-study-skills' ),
							),
						),
					),
					'metrics' => array(
						'ai_dependency' => array(
							'slug'         => 'ai_dependency',
							'score_type'   => 'dependency',
							'metric_label' => __( 'AI Dependency Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Using AI before attempting work yourself can reduce learning, confidence and retention.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Think First, Prompt Second Guide', 'ai-risk-benchmark' ), 'path' => 'think-first-prompt-second' ),
								array( 'kind' => 'join', 'label' => __( 'Student AI Study Skills Challenge', 'ai-risk-benchmark' ), 'path' => 'student-ai-study-skills' ),
								array( 'kind' => 'read', 'label' => __( 'How To Spot When AI Is Wrong', 'ai-risk-benchmark' ), 'path' => 'how-to-check-ai-answers' ),
								array( 'kind' => 'join', 'label' => __( 'AI Awareness Day Student Resources', 'ai-risk-benchmark' ), 'path' => 'student-ai-study-skills' ),
							),
						),
					),
				),
				'parent' => array(
					'parent_domains' => array(
						'parent_awareness' => array(
							'slug'         => 'parent_awareness',
							'metric_label' => __( 'Parent Awareness Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Understanding how, how often and for what purpose your child uses AI at home is the first step to guiding them safely.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'read', 'label' => __( 'Talking To Children About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
							),
						),
						'home_ai_safety' => array(
							'slug'         => 'home_ai_safety',
							'metric_label' => __( 'Home AI Safety Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Many children use AI without open family discussion. Privacy rules and harm response plans matter as much as awareness.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'read', 'label' => __( 'Questions To Ask Your Child About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
								array( 'kind' => 'read', 'label' => __( 'Deepfake Awareness Briefing', 'ai-risk-benchmark' ), 'path' => 'parent-deepfake-awareness' ),
								array( 'kind' => 'join', 'label' => __( 'Parent Webinar', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
							),
						),
						'homework_oversight' => array(
							'slug'         => 'homework_oversight',
							'metric_label' => __( 'Homework Oversight Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Asking children to explain AI-assisted work in their own words is one of the strongest signals of healthy oversight at home.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Homework Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-homework-guide' ),
								array( 'kind' => 'read', 'label' => __( 'Questions To Ask Your Child About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
							),
						),
						'parent_ai_dependency' => array(
							'slug'         => 'parent_ai_dependency',
							'metric_label' => __( 'Parent AI Dependency Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'When parents use AI as a homework co-pilot, children may learn to outsource thinking rather than build understanding.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Homework Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-homework-guide' ),
							),
						),
						'school_partnership' => array(
							'slug'         => 'school_partnership',
							'metric_label' => __( 'School Partnership Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Schools need to know whether families understand AI homework rules — and parents need a clear route to raise concerns.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'join', 'label' => __( 'Parent Webinar', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
							),
						),
					),
				),
				'support_staff' => array(
					'domains' => array(
						'privacy' => array(
							'slug'         => 'privacy',
							'metric_label' => __( 'Data Protection Readiness', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Office, HR and operations staff often handle pupil and staff data — public AI tools are high risk without approval.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ), 'path' => 'dfe-ai-compliance-checklist' ),
								array( 'kind' => 'read', 'label' => __( 'AI Privacy Guide for Schools', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-privacy-guide' ),
							),
						),
						'human_oversight' => array(
							'slug'         => 'human_oversight',
							'metric_label' => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Emails, letters and reports generated by AI must be reviewed before they leave your organisation.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Verify Before You Trust Framework', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
							),
						),
						'safe_adoption' => array(
							'slug'         => 'safe_adoption',
							'metric_label' => __( 'Safe Adoption Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Approved tool lists and clear reporting routes protect schools and trusts from shadow AI use.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'School AI Governance Guide', 'ai-risk-benchmark' ), 'path' => 'school-ai-governance' ),
							),
						),
					),
					'metrics' => array(
						'ai_dependency' => array(
							'slug'         => 'operational_dependency',
							'score_type'   => 'dependency',
							'metric_label' => __( 'Operational Dependency Index', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'High reliance on AI for daily communications can hide gaps in judgement when tools are wrong or unavailable.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Think First, Prompt Second Framework', 'ai-risk-benchmark' ), 'path' => 'think-first-prompt-second' ),
							),
						),
					),
				),
				'leader' => array(
					'domains' => array(
						'governance' => array(
							'slug'         => 'governance',
							'metric_label' => __( 'Governance Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Without governance, schools cannot demonstrate responsible AI adoption to governors, parents or regulators.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'School AI Policy (DfE template & support)', 'ai-risk-benchmark' ), 'path' => 'ai-policy-generator' ),
								array( 'kind' => 'download', 'label' => __( 'Governance Checklist', 'ai-risk-benchmark' ), 'path' => 'school-ai-governance' ),
								array( 'kind' => 'read', 'label' => __( 'Leadership Toolkit', 'ai-risk-benchmark' ), 'path' => 'school-ai-governance' ),
								array( 'kind' => 'join', 'label' => __( 'Free Consultation', 'ai-risk-benchmark' ), 'path' => 'contact' ),
							),
						),
						'human_oversight' => array(
							'slug'         => 'human_oversight',
							'metric_label' => __( 'Staff Verification Readiness', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Inconsistent verification across staff increases the risk of inaccurate or inappropriate AI content reaching pupils.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Teacher Verification Framework', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
								array( 'kind' => 'join', 'label' => __( 'Staff Training — AI Awareness Day', 'ai-risk-benchmark' ), 'path' => 'ai-awareness-day' ),
							),
						),
						'safeguarding' => array(
							'slug'         => 'safeguarding',
							'metric_label' => __( 'Safeguarding Readiness Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'AI-enabled harm — including deepfakes and impersonation — should be explicitly covered in safeguarding procedures.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Safeguarding & AI Briefing', 'ai-risk-benchmark' ), 'path' => 'school-ai-governance' ),
								array( 'kind' => 'join', 'label' => __( 'AI Awareness Day', 'ai-risk-benchmark' ), 'path' => 'ai-awareness-day' ),
							),
						),
						'assessment_integrity' => array(
							'slug'         => 'assessment',
							'metric_label' => __( 'Assessment Integrity Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Assessment exposure without clear controls creates malpractice and fairness risks.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'JCQ-Aligned Assessment Review Guide', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-assessment-guide' ),
								array( 'kind' => 'download', 'label' => __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ), 'path' => 'dfe-ai-compliance-checklist' ),
							),
						),
						'privacy' => array(
							'slug'         => 'privacy',
							'metric_label' => __( 'Data Protection Readiness', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Schools must show how pupil data is protected when staff use AI tools.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'download', 'label' => __( 'DPIA / Data Protection Checklist', 'ai-risk-benchmark' ), 'path' => 'dfe-ai-compliance-checklist' ),
								array( 'kind' => 'read', 'label' => __( 'AI Risk Register Template', 'ai-risk-benchmark' ), 'path' => 'ai-risk-register' ),
							),
						),
					),
				),
			),
		);
	}

	/**
	 * Audience key for a hub page slug.
	 */
	public static function hub_audience_for_slug( string $slug ): string {
		$slug = sanitize_title( $slug );
		foreach ( self::hub_page_seed_rows() as $page ) {
			if ( (string) ( $page['slug'] ?? '' ) === $slug ) {
				return (string) ( $page['audience'] ?? 'all' );
			}
		}
		return 'all';
	}

	/**
	 * Badge label for a hub page audience.
	 */
	public static function hub_audience_badge_label( string $audience ): string {
		$labels = array(
			'teacher' => __( 'Teacher resource', 'ai-risk-benchmark' ),
			'student' => __( 'Student resource', 'ai-risk-benchmark' ),
			'parent'  => __( 'Parent resource', 'ai-risk-benchmark' ),
			'leader'  => __( 'Leadership resource', 'ai-risk-benchmark' ),
			'all'     => __( 'School resource', 'ai-risk-benchmark' ),
		);

		return $labels[ $audience ] ?? $labels['all'];
	}

	/**
	 * @return array<int, array<string, string>>
	 */
	private static function hub_page_seed_rows(): array {
		return array(
			array(
				'slug'    => 'teacher-ai-verification-framework',
				'title'   => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Verify Before You Trust™ — a DfE-aligned framework for checking AI outputs before classroom use.', 'ai-risk-benchmark' ),
				'audience'=> 'teacher',
			),
			array(
				'slug'    => 'teacher-ai-lesson-planning-checklist',
				'title'   => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Downloadable checklist for planning, verifying and using AI-generated teaching materials safely.', 'ai-risk-benchmark' ),
				'audience'=> 'teacher',
			),
			array(
				'slug'    => 'teacher-ai-privacy-guide',
				'title'   => __( 'Teacher AI Privacy Guide', 'ai-risk-benchmark' ),
				'excerpt' => __( 'What pupil data must never enter public AI tools — and what to do instead.', 'ai-risk-benchmark' ),
				'audience'=> 'teacher',
			),
			array(
				'slug'    => 'teacher-ai-assessment-guide',
				'title'   => __( 'Teacher AI Assessment Guide', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Assessment integrity, JCQ expectations and honest AI use in teaching.', 'ai-risk-benchmark' ),
				'audience'=> 'teacher',
			),
			array(
				'slug'    => 'student-ai-study-skills',
				'title'   => __( 'Student AI Study Skills', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Learn how to use AI to support your learning — not replace your thinking.', 'ai-risk-benchmark' ),
				'audience'=> 'student',
			),
			array(
				'slug'    => 'think-first-prompt-second',
				'title'   => __( 'Think First, Prompt Second', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Think First, Prompt Second™ — a student framework for using AI responsibly and protecting independent learning.', 'ai-risk-benchmark' ),
				'audience'=> 'student',
			),
			array(
				'slug'    => 'student-ai-privacy-guide',
				'title'   => __( 'Student AI Privacy Guide', 'ai-risk-benchmark' ),
				'excerpt' => __( 'What not to share with AI tools — names, photos, school details and more.', 'ai-risk-benchmark' ),
				'audience'=> 'student',
			),
			array(
				'slug'    => 'how-to-check-ai-answers',
				'title'   => __( 'How To Check AI Answers', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Spot when AI is wrong — and build real understanding.', 'ai-risk-benchmark' ),
				'audience'=> 'student',
			),
			array(
				'slug'    => 'parent-ai-safety',
				'title'   => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Helping your child use AI safely and responsibly at home.', 'ai-risk-benchmark' ),
				'audience'=> 'parent',
			),
			array(
				'slug'    => 'parent-ai-homework-guide',
				'title'   => __( 'Parent AI Homework Guide', 'ai-risk-benchmark' ),
				'excerpt' => __( 'AI should support homework — not replace your child\'s effort.', 'ai-risk-benchmark' ),
				'audience'=> 'parent',
			),
			array(
				'slug'    => 'parent-deepfake-awareness',
				'title'   => __( 'Parent Deepfake Awareness', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Deepfakes, fake images and AI-enabled harm — what parents need to know.', 'ai-risk-benchmark' ),
				'audience'=> 'parent',
			),
			array(
				'slug'    => 'talking-to-children-about-ai',
				'title'   => __( 'Talking To Children About AI', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Conversation starters and questions to ask your child about AI use.', 'ai-risk-benchmark' ),
				'audience'=> 'parent',
			),
			array(
				'slug'    => 'ai-policy-generator',
				'title'   => __( 'School AI Policy: DfE Templates & Support', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Adapt the official DfE AI policy template to your school, with support from the AI Awareness Day team.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'school-ai-governance',
				'title'   => __( 'School AI Governance', 'ai-risk-benchmark' ),
				'excerpt' => __( 'DfE-aligned governance checklist for leadership, policy, staff and pupils.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'dfe-ai-compliance-checklist',
				'title'   => __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ),
				'excerpt' => __( 'A practical review framework for school leaders — governance, safeguarding and readiness.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'ai-risk-register',
				'title'   => __( 'AI Risk Register', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Identify, assign ownership and track controls for AI-related risks.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'ai-awareness-day',
				'title'   => __( 'AI Awareness Day Framework', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Whole-school programme for leaders, staff, students and parents — built on your benchmark data.', 'ai-risk-benchmark' ),
				'audience'=> 'all',
			),
			array(
				'slug'    => 'annual-benchmark-review',
				'title'   => __( 'Annual AI Benchmark Review', 'ai-risk-benchmark' ),
				'excerpt' => __( 'For leaders: why to reassess annually, what good improvement looks like, and how to evidence progress.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'ai-champion-programme',
				'title'   => __( 'AI Champion Programme', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Build internal AI leadership — five levels from AI Aware to School AI Lead.', 'ai-risk-benchmark' ),
				'audience'=> 'teacher',
			),
			array(
				'slug'    => 'school-ai-maturity',
				'title'   => __( 'School AI Maturity Framework', 'ai-risk-benchmark' ),
				'excerpt' => __( 'For leaders: Emerging → Leading maturity bands linked to your alignment score and priority interventions.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'national-benchmark-report',
				'title'   => __( 'UK School AI Benchmark Report', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Anonymised national trends, risks and insights from participating UK schools.', 'ai-risk-benchmark' ),
				'audience'=> 'all',
			),
		);
	}

	/**
	 * Hub page definitions for one-time seeding.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function hub_page_definitions(): array {
		$benchmark_cta = __( 'Take the free AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' );
		$pages         = self::hub_page_seed_rows();

		$out = array();
		foreach ( $pages as $page ) {
			$excerpt = (string) ( $page['excerpt'] ?? '' );
			$out[]   = array(
				'slug'    => (string) $page['slug'],
				'title'   => (string) $page['title'],
				'excerpt' => $excerpt,
				'content' => AIRB_Hub_Content::for_slug( (string) $page['slug'], $excerpt, $benchmark_cta ),
			);
		}

		return $out;
	}

	/**
	 * @deprecated Use AIRB_Hub_Content::for_slug().
	 */
	private static function hub_page_content( string $title, string $excerpt, string $benchmark_cta ): string {
		unset( $title );
		return AIRB_Hub_Content::for_slug( '', $excerpt, $benchmark_cta );
	}

	/**
	 * Role picker card accents.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function role_meta(): array {
		return array(
			'teacher' => array( 'tint' => '#dcfce7', 'accent' => '#15803d' ),
			'student' => array( 'tint' => '#dbeafe', 'accent' => '#2563eb' ),
			'parent'  => array( 'tint' => '#f3e8ff', 'accent' => '#7c3aed' ),
			'leader'        => array( 'tint' => '#f1f5f9', 'accent' => '#475569' ),
			'support_staff' => array( 'tint' => '#fef3c7', 'accent' => '#b45309' ),
			'public'        => array( 'tint' => '#e6f1fb', 'accent' => '#185fa5' ),
		);
	}

	/**
	 * Weights for whole-school DfE alignment rollup.
	 *
	 * @return array<string, float>
	 */
	public static function role_weights(): array {
		return array(
			'teacher' => 1.2,
			'student' => 1.0,
			'parent'  => 0.9,
			'leader'        => 1.4,
			'support_staff' => 1.1,
			'public'        => 0,
		);
	}

	/**
	 * Full default configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function config(): array {
		return array(
			'version'             => 45,
			'framework'           => self::default_framework(),
			'domain_sources'      => self::default_domain_sources(),
			'positioning'         => self::default_positioning(),
			'domain_descriptions' => array(
				'safe_adoption'        => __( 'Does the school assess risks before introducing AI?', 'ai-risk-benchmark' ),
				'human_oversight'      => __( 'Are users reviewing, challenging and verifying AI outputs?', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'How reliant have users become on AI?', 'ai-risk-benchmark' ),
				'privacy'              => __( 'Are staff and students protecting personal information?', 'ai-risk-benchmark' ),
				'safeguarding'         => __( 'Are AI-related safeguarding risks understood and managed?', 'ai-risk-benchmark' ),
				'bias_equality'        => __( 'Are users checking AI outputs for bias, stereotypes and unfairness?', 'ai-risk-benchmark' ),
				'assessment_integrity' => __( 'Are assessments protected against inappropriate AI use?', 'ai-risk-benchmark' ),
				'ai_literacy'          => __( 'Do users understand AI capabilities and limitations?', 'ai-risk-benchmark' ),
				'governance'           => __( 'Does the school have policies, accountability and oversight?', 'ai-risk-benchmark' ),
			),
			'disclaimer'       => '',
			'intro'            => __( 'Choose your role and complete a 10–15 minute audit. You will receive your readiness score, signature metrics, tailored recommendations and optional whole-school tracking. No student personal data is collected.', 'ai-risk-benchmark' ),
			'role_benchmarks'  => self::default_role_benchmarks(),
			'signature_metrics'=> self::default_signature_metrics(),
			'after_audit'      => self::default_after_audit(),
			'services'         => self::default_services(),
			'aad_2027'         => self::default_aad_2027(),
			'consultation_cta' => array(
				'title' => __( 'Contact the AI Awareness Day team', 'ai-risk-benchmark' ),
				'url'   => self::contact_page_url(),
				'text'  => __( 'Get support with your results', 'ai-risk-benchmark' ),
			),
			'gateway'          => self::default_gateway(),
			'pathway_offers'   => self::default_pathway_offers(),
			'guidance_refs'    => array(
				array( 'label' => 'DfE Generative AI in Education', 'url' => self::dfe_url_generative_ai() ),
				array( 'label' => 'DfE Using AI in Education Settings', 'url' => self::dfe_url_using_ai() ),
				array( 'label' => 'DfE Product Safety Expectations', 'url' => self::dfe_url_product_safety() ),
				array( 'label' => 'Keeping Children Safe in Education (KCSIE)', 'url' => 'https://www.gov.uk/government/publications/keeping-children-safe-in-education--2' ),
				array( 'label' => 'ICO AI & Data Protection Guidance', 'url' => 'https://ico.org.uk/for-organisations/uk-gdpr-guidance-and-resources/artificial-intelligence/' ),
				array( 'label' => 'JCQ AI Assessment Guidance', 'url' => 'https://www.jcq.org.uk/exams-office/malpractice/' ),
				array( 'label' => 'Ofqual AI and Qualifications Guidance', 'url' => 'https://www.gov.uk/government/publications/regulating-artificial-intelligence-in-qualifications' ),
				array( 'label' => "Ofsted's Approach to AI", 'url' => 'https://www.gov.uk/government/publications/ofsteds-approach-to-ai' ),
				array( 'label' => 'AI Opportunities Action Plan', 'url' => 'https://www.gov.uk/government/publications/ai-opportunities-action-plan' ),
			),
			'questions'        => AIRB_Questions::all(),
			'recommendations'  => self::default_recommendations(),
		);
	}

	/**
	 * Platform framework identity.
	 *
	 * @return array<string, string>
	 */
	private static function default_framework(): array {
		return array(
			'product_name' => __( 'AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ),
			'subtitle'     => __( 'DfE-Aligned Assessment Framework for Schools', 'ai-risk-benchmark' ),
			'statement'    => __( 'A DfE-aligned benchmark that measures AI exposure, dependency, human oversight, governance and safe adoption across teachers, students, parents, leaders and support staff.', 'ai-risk-benchmark' ),
			'annual_note'  => __( 'An annual evidence layer for AI Awareness Day: schools can benchmark responsible AI behaviour and readiness against DfE, KCSIE, ICO, JCQ, Ofqual and Ofsted expectations.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Eight domains mapped to published guidance sources.
	 *
	 * @return array<string, string>
	 */
	private static function default_domain_sources(): array {
		return array(
			'safe_adoption'        => __( 'DfE Generative AI Guidance', 'ai-risk-benchmark' ),
			'human_oversight'      => __( 'DfE + Ofsted', 'ai-risk-benchmark' ),
			'ai_dependency'        => __( 'DfE Teacher-Led Learning Principle', 'ai-risk-benchmark' ),
			'privacy'              => __( 'ICO', 'ai-risk-benchmark' ),
			'safeguarding'         => __( 'KCSIE', 'ai-risk-benchmark' ),
			'bias_equality'        => __( 'Equality Act + DfE', 'ai-risk-benchmark' ),
			'assessment_integrity' => __( 'JCQ + Ofqual', 'ai-risk-benchmark' ),
			'ai_literacy'          => __( 'DfE', 'ai-risk-benchmark' ),
			'governance'           => __( 'DfE + Ofsted', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Pitch-deck positioning copy.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_positioning(): array {
		return array(
			'headline'          => __( 'AI Awareness Day AI Risk & Readiness Index™', 'ai-risk-benchmark' ),
			'tagline'           => __( 'Measuring how schools are actually using AI — not just whether they are aware of it.', 'ai-risk-benchmark' ),
			'problem'           => __( 'Most AI literacy initiatives help people understand AI. Schools also need to know how AI is affecting behaviour: human oversight, dependency, safeguarding, assessment integrity and governance maturity.', 'ai-risk-benchmark' ),
			'problem_questions' => array(
				__( 'Are our teachers becoming over-reliant on AI?', 'ai-risk-benchmark' ),
				__( 'Are students still developing independent thinking?', 'ai-risk-benchmark' ),
				__( 'Do parents understand the risks and opportunities?', 'ai-risk-benchmark' ),
				__( 'Are we compliant with DfE, ICO, KCSIE, JCQ and Ofqual guidance?', 'ai-risk-benchmark' ),
				__( 'Do we have the right governance, policies and safeguards in place?', 'ai-risk-benchmark' ),
				__( 'How do I know if we\'re doing enough?', 'ai-risk-benchmark' ),
			),
			'problem_closing'   => __( 'Resources and training can raise awareness. The harder, more valuable question is whether teachers, students, parents, leaders and support teams are using AI wisely in practice.', 'ai-risk-benchmark' ),
			'solution'          => __( 'AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ),
			'solution_detail'   => __( 'A free DfE-aligned assessment platform that can grow into a national AI readiness index for education, combining teacher AI dependency, student independent thinking, parent awareness and school governance maturity.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Per-role benchmark descriptions (pitch deck).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private static function default_role_benchmarks(): array {
		return array(
			'teacher' => array(
				'title'    => __( 'Teacher Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free online check — test your classroom AI habits', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'AI Dependency', 'ai-risk-benchmark' ),
					__( 'Human Oversight', 'ai-risk-benchmark' ),
					__( 'Verification Behaviour', 'ai-risk-benchmark' ),
					__( 'Privacy Awareness', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Teacher AI Risk Score', 'ai-risk-benchmark' ),
					__( 'Teacher AI Readiness Score', 'ai-risk-benchmark' ),
					__( 'AI Dependency Index', 'ai-risk-benchmark' ),
					__( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
				),
			),
			'student' => array(
				'title'    => __( 'Student Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free online check — test your learning and thinking skills', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'Learning Dependency', 'ai-risk-benchmark' ),
					__( 'Critical Thinking', 'ai-risk-benchmark' ),
					__( 'Verification Behaviour', 'ai-risk-benchmark' ),
					__( 'AI Literacy', 'ai-risk-benchmark' ),
					__( 'Safety Awareness', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Student Learning Risk Score', 'ai-risk-benchmark' ),
					__( 'Student AI Dependency Score', 'ai-risk-benchmark' ),
					__( 'Student AI Literacy Score', 'ai-risk-benchmark' ),
				),
			),
			'parent'  => array(
				'title'    => __( 'Parent Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free online check — test your awareness at home', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'Awareness of your child\'s AI use', 'ai-risk-benchmark' ),
					__( 'Home AI safety and culture', 'ai-risk-benchmark' ),
					__( 'Homework oversight behaviours', 'ai-risk-benchmark' ),
					__( 'Your own AI use at home', 'ai-risk-benchmark' ),
					__( 'Partnership with school', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Parent Awareness Score', 'ai-risk-benchmark' ),
					__( 'Home AI Safety Score', 'ai-risk-benchmark' ),
					__( 'Homework Oversight Score', 'ai-risk-benchmark' ),
					__( 'Parent AI Dependency Score', 'ai-risk-benchmark' ),
					__( 'School Partnership Score', 'ai-risk-benchmark' ),
				),
			),
			'leader'  => array(
				'title'    => __( 'School Leader Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free online check — test your school\'s AI readiness', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'Governance', 'ai-risk-benchmark' ),
					__( 'Safeguarding', 'ai-risk-benchmark' ),
					__( 'Compliance', 'ai-risk-benchmark' ),
					__( 'Staff Readiness', 'ai-risk-benchmark' ),
					__( 'Assessment Controls', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Governance Maturity Score', 'ai-risk-benchmark' ),
					__( 'Safeguarding Readiness Score', 'ai-risk-benchmark' ),
					__( 'DfE AI Alignment Score', 'ai-risk-benchmark' ),
				),
			),
			'support_staff' => array(
				'title'    => __( 'Education Support Staff Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free online check — test your day-to-day AI practice', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'AI literacy & limitations', 'ai-risk-benchmark' ),
					__( 'Human oversight of outputs', 'ai-risk-benchmark' ),
					__( 'Operational dependency', 'ai-risk-benchmark' ),
					__( 'Data protection', 'ai-risk-benchmark' ),
					__( 'Safe adoption & reporting', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Readiness Score', 'ai-risk-benchmark' ),
					__( 'Operational Dependency Index', 'ai-risk-benchmark' ),
					__( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
					__( 'Data Protection Readiness', 'ai-risk-benchmark' ),
				),
			),
			'public' => array(
				'title'    => __( 'Public AI Benchmark', 'ai-risk-benchmark' ),
				'tagline'  => __( 'Free check — how safely, critically and independently do you use AI?', 'ai-risk-benchmark' ),
				'measures' => array(
					__( 'Personal AI use', 'ai-risk-benchmark' ),
					__( 'Verification & critical thinking', 'ai-risk-benchmark' ),
					__( 'Data & privacy', 'ai-risk-benchmark' ),
					__( 'Workplace AI', 'ai-risk-benchmark' ),
					__( 'Emotional & social use', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Overall AI readiness', 'ai-risk-benchmark' ),
					__( 'Personal AI use score', 'ai-risk-benchmark' ),
					__( 'Verification score', 'ai-risk-benchmark' ),
					__( 'Data & privacy score', 'ai-risk-benchmark' ),
					__( 'Workplace AI score', 'ai-risk-benchmark' ),
				),
			),
		);
	}

	/**
	 * Signature metrics copy.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_signature_metrics(): array {
		return array(
			'dependency' => array(
				'title'   => __( 'AI Dependency Index', 'ai-risk-benchmark' ),
				'tagline' => __( 'Your signature measure — reliance, independent thinking and verification combined.', 'ai-risk-benchmark' ),
				'measures'=> array(
					__( 'Reliance on AI', 'ai-risk-benchmark' ),
					__( 'Independent thinking', 'ai-risk-benchmark' ),
					__( 'Verification behaviour', 'ai-risk-benchmark' ),
					__( 'Confidence versus competence', 'ai-risk-benchmark' ),
					__( 'Understanding of limitations', 'ai-risk-benchmark' ),
				),
			),
			'oversight'  => array(
				'title'   => __( 'Human Oversight Ratio', 'ai-risk-benchmark' ),
				'tagline' => __( 'The killer metric — what percentage of AI-generated output do you modify before using it?', 'ai-risk-benchmark' ),
				'bands'   => array(
					array( 'range' => '0–10%', 'label' => __( 'Critical reliance', 'ai-risk-benchmark' ) ),
					array( 'range' => '11–25%', 'label' => __( 'High reliance', 'ai-risk-benchmark' ) ),
					array( 'range' => '26–50%', 'label' => __( 'Moderate oversight', 'ai-risk-benchmark' ) ),
					array( 'range' => '51%+', 'label' => __( 'Strong human oversight', 'ai-risk-benchmark' ) ),
				),
				'measures'=> array(
					__( 'Percentage of AI output modified before use', 'ai-risk-benchmark' ),
					__( 'Frequency of verification', 'ai-risk-benchmark' ),
					__( 'Cross-referencing behaviour', 'ai-risk-benchmark' ),
					__( 'Accountability for decisions', 'ai-risk-benchmark' ),
				),
			),
			'footnote'   => __( 'These metrics become the benchmark schools can track annually.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Post-audit messaging.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_after_audit(): array {
		return array(
			'headline'  => __( 'What happens after the audit?', 'ai-risk-benchmark' ),
			'intro'     => __( 'The audit provides tailored, evidence-based recommendations — not hard sales.', 'ai-risk-benchmark' ),
			'principle' => __( 'No hard sales. Only evidence-based recommendations.', 'ai-risk-benchmark' ),
			'examples'  => array(
				array(
					'trigger' => __( 'Low Governance Score', 'ai-risk-benchmark' ),
					'offer'   => __( 'AI Governance Review', 'ai-risk-benchmark' ),
				),
				array(
					'trigger' => __( 'Low Privacy Score', 'ai-risk-benchmark' ),
					'offer'   => __( 'Data Protection Checklist', 'ai-risk-benchmark' ),
				),
				array(
					'trigger' => __( 'Low Assessment Integrity Score', 'ai-risk-benchmark' ),
					'offer'   => __( 'JCQ-Aligned Assessment Review Pack', 'ai-risk-benchmark' ),
				),
				array(
					'trigger' => __( 'Low Human Oversight Score', 'ai-risk-benchmark' ),
					'offer'   => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				),
			),
		);
	}

	/**
	 * Optional services surfaced after gaps are identified.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_services(): array {
		return array(
			'headline' => __( 'When you need more support', 'ai-risk-benchmark' ),
			'intro'    => __( 'The benchmark is free. Your report identifies gaps. Schools can then access:', 'ai-risk-benchmark' ),
			'items'    => array(
				array( 'label' => __( 'AI Policy Templates (DfE)', 'ai-risk-benchmark' ), 'url' => self::dfe_url_using_ai() ),
				array( 'label' => __( 'AI Governance Toolkit', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/resources/' ),
				array( 'label' => __( 'Teacher Training', 'ai-risk-benchmark' ), 'url' => self::contact_page_url() ),
				array( 'label' => __( 'Assessment Integrity Reviews', 'ai-risk-benchmark' ), 'url' => self::contact_page_url() ),
				array( 'label' => __( 'Parent Awareness Sessions', 'ai-risk-benchmark' ), 'url' => self::contact_page_url() ),
				array( 'label' => __( 'Annual Benchmark Reports', 'ai-risk-benchmark' ), 'url' => self::contact_page_url() ),
			),
		);
	}

	/**
	 * AI Awareness Day 2027 programme block.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_aad_2027(): array {
		return array(
			'enabled'  => true,
			'headline' => __( 'AI Awareness Day', 'ai-risk-benchmark' ),
			'intro'    => __( 'A school-wide AI awareness programme shaped by your benchmark results.', 'ai-risk-benchmark' ),
			'sessions' => array(
				array(
					'time'  => __( 'Morning — Teachers', 'ai-risk-benchmark' ),
					'topics'=> array(
						__( 'Safe AI Use', 'ai-risk-benchmark' ),
						__( 'Human Oversight', 'ai-risk-benchmark' ),
						__( 'DfE Guidance', 'ai-risk-benchmark' ),
						__( 'Privacy', 'ai-risk-benchmark' ),
					),
				),
				array(
					'time'  => __( 'Morning — Leaders', 'ai-risk-benchmark' ),
					'topics'=> array(
						__( 'Governance', 'ai-risk-benchmark' ),
						__( 'Risk Management', 'ai-risk-benchmark' ),
						__( 'Policy Development', 'ai-risk-benchmark' ),
					),
				),
				array(
					'time'  => __( 'Afternoon — Students', 'ai-risk-benchmark' ),
					'topics'=> array(
						__( 'AI Literacy', 'ai-risk-benchmark' ),
						__( 'Critical Thinking', 'ai-risk-benchmark' ),
						__( 'Verification Skills', 'ai-risk-benchmark' ),
						__( 'Deepfake Awareness', 'ai-risk-benchmark' ),
					),
				),
				array(
					'time'  => __( 'Evening — Parents', 'ai-risk-benchmark' ),
					'topics'=> array(
						__( 'AI at Home', 'ai-risk-benchmark' ),
						__( 'Homework Support', 'ai-risk-benchmark' ),
						__( 'Online Safety', 'ai-risk-benchmark' ),
						__( 'Digital Parenting', 'ai-risk-benchmark' ),
					),
				),
			),
			'cta_text' => __( 'Plan your AI Awareness Day', 'ai-risk-benchmark' ),
			'cta_url'  => self::contact_page_url(),
		);
	}

	private static function default_recommendations(): array {
		return array(
			array(
				'roles'       => array( 'leader', 'teacher' ),
				'offer_type'  => 'template',
				'priority'    => 70,
				'domain'      => 'governance',
				'min_band'    => 'high',
				'title'       => __( 'Develop your AI policy', 'ai-risk-benchmark' ),
				'body'        => __( 'Your governance score suggests policy foundations are not yet secure. Adapt the official DfE AI policy template into a whole-school AI use policy aligned to DfE expectations.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'View DfE AI policy template', 'ai-risk-benchmark' ),
				'cta_url'     => self::dfe_url_using_ai(),
			),
			array(
				'roles'       => array( 'leader' ),
				'offer_type'  => 'consultation',
				'priority'    => 85,
				'domain'      => 'governance',
				'min_band'    => 'critical',
				'title'       => __( 'AI Governance Toolkit', 'ai-risk-benchmark' ),
				'body'        => __( 'Critical governance gaps increase whole-school exposure. Access the toolkit or book a review with named ownership, training and annual review cycles.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'AI Governance Toolkit', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
			array(
				'roles'       => array( 'teacher', 'leader' ),
				'offer_type'  => 'training',
				'priority'    => 75,
				'domain'      => 'human_oversight',
				'min_band'    => 'high',
				'title'       => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'body'        => __( 'Low human oversight suggests staff may be deferring to AI outputs. Build a verify-before-you-trust habit across departments.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Staff verification training', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
			array(
				'roles'       => array( 'teacher' ),
				'offer_type'  => 'cpd',
				'priority'    => 55,
				'domain'      => 'human_oversight',
				'min_band'    => 'moderate',
				'title'       => __( 'AI Awareness Day classroom pack', 'ai-risk-benchmark' ),
				'body'        => __( 'Use National AI Awareness Day resources to normalise healthy scepticism and classroom-ready AI literacy.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Explore AI Awareness Day', 'ai-risk-benchmark' ),
				'cta_url'     => 'https://aiawarenessday.co.uk/',
			),
			array(
				'roles'       => array( 'leader', 'teacher' ),
				'offer_type'  => 'template',
				'priority'    => 72,
				'domain'      => 'privacy',
				'min_band'    => 'high',
				'title'       => __( 'AI Data Protection Checklist', 'ai-risk-benchmark' ),
				'body'        => __( 'Privacy risk is elevated. Review what data enters AI tools and when a DPIA is required under UK GDPR.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Get the checklist', 'ai-risk-benchmark' ),
				'cta_url'     => self::dfe_url_generative_ai(),
			),
			array(
				'roles'       => array( 'leader' ),
				'offer_type'  => 'consultation',
				'priority'    => 80,
				'domain'      => 'privacy',
				'min_band'    => 'critical',
				'title'       => __( 'DPIA review support', 'ai-risk-benchmark' ),
				'body'        => __( 'Work with your DPO to complete or refresh DPIAs for pupil-facing AI tools — we can help scope the review.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Book DPIA support', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
			array(
				'roles'       => array( 'leader', 'teacher' ),
				'offer_type'  => 'template',
				'priority'    => 68,
				'domain'      => 'assessment_integrity',
				'min_band'    => 'high',
				'title'       => __( 'JCQ-Aligned Assessment Review Pack', 'ai-risk-benchmark' ),
				'body'        => __( 'Assessment exposure appears high. Review supervision, device access and JCQ malpractice expectations.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'JCQ-Aligned Assessment Review Pack', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
			array(
				'roles'       => array( 'leader' ),
				'offer_type'  => 'cpd',
				'priority'    => 60,
				'domain'      => 'ai_literacy',
				'min_band'    => 'high',
				'title'       => __( 'Parent AI Safety session', 'ai-risk-benchmark' ),
				'body'        => __( 'Parent and pupil AI literacy gaps widen home-school risk. A parent-facing session on tools, deepfakes and verification helps close the loop.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Book parent webinar', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
			array(
				'roles'       => array( 'leader' ),
				'offer_type'  => 'policy',
				'priority'    => 65,
				'domain'      => 'safeguarding',
				'min_band'    => 'moderate',
				'title'       => __( 'Safeguarding & AI policy addendum', 'ai-risk-benchmark' ),
				'body'        => __( 'Embed AI-enabled risks (deepfakes, grooming vectors, harmful content) in your KCSIE-aligned procedures.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'Safeguarding policy support', 'ai-risk-benchmark' ),
				'cta_url'     => self::contact_page_url(),
			),
		);
	}

	/**
	 * Answer-triggered pathway offers (teachers & leaders).
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function default_pathway_offers(): array {
		$offer = static function ( array $args ): array {
			return array_merge(
				array(
					'trigger'  => 'answer',
					'priority' => 60,
				),
				$args
			);
		};

		return array(
			// Leader — direct policy gaps.
			$offer(
				array(
					'id'            => 'leader_no_policy',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_policy',
					'answer_values' => array( 'no', 'draft', 'published' ),
					'offer_type'    => 'policy',
					'priority'      => 95,
					'title'         => __( 'You need a published AI policy', 'ai-risk-benchmark' ),
					'body'          => __( 'Your audit shows the school does not yet have a reviewed AI policy. Start with the official DfE AI policy template and adapt it to your context — contact the AI Awareness Day team if you need support.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'View DfE AI policy template', 'ai-risk-benchmark' ),
					'cta_url'       => self::dfe_url_using_ai(),
				)
			),
			$offer(
				array(
					'id'            => 'leader_no_ai_lead',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_ai_lead',
					'answer_values' => array( 'no', 'planned' ),
					'offer_type'    => 'consultation',
					'priority'      => 88,
					'title'         => __( 'Assign an AI lead', 'ai-risk-benchmark' ),
					'body'          => __( 'DfE guidance expects clear ownership. A short consultation can help you define the AI lead role, reporting lines and review cadence.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book leadership session', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'leader_staff_training',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_staff_training',
					'answer_values' => array( 'none', 'under_50', 'fifty_75' ),
					'offer_type'    => 'cpd',
					'priority'      => 90,
					'title'         => __( 'Staff AI risk CPD required', 'ai-risk-benchmark' ),
					'body'          => __( 'Staff are not yet consistently trained on verification and AI risks. Book whole-staff CPD aligned to your benchmark gaps.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book staff CPD', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'leader_dp_gap',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_dp_review',
					'answer_values' => array( 'no', 'planned', 'started' ),
					'offer_type'    => 'consultation',
					'priority'      => 82,
					'title'         => __( 'Data protection review needed', 'ai-risk-benchmark' ),
					'body'          => __( 'AI tool use should be mapped against UK GDPR. We can help you scope DPIAs and acceptable-use rules for staff.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book data protection review', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'leader_safeguarding_gap',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_safeguarding',
					'answer_values' => array( 'no', 'review', 'partial' ),
					'offer_type'    => 'policy',
					'priority'      => 78,
					'title'         => __( 'Update safeguarding for AI risks', 'ai-risk-benchmark' ),
					'body'          => __( 'AI-related safeguarding should be explicit in your procedures. We provide a policy addendum and staff briefing outline.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Safeguarding policy addendum', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			// Teacher — training & practice gaps.
			$offer(
				array(
					'id'            => 'teacher_dependency',
					'roles'         => array( 'teacher' ),
					'question_id'   => 't_without_ai',
					'answer_values' => array( 'difficult', 'no' ),
					'offer_type'    => 'training',
					'priority'      => 92,
					'title'         => __( 'Reduce AI dependency in teaching', 'ai-risk-benchmark' ),
					'body'          => __( 'Your responses suggest high reliance on AI. Training on independent planning, verification habits and when not to use AI will strengthen classroom practice.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book teacher training', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'teacher_low_modify',
					'roles'         => array( 'teacher' ),
					'question_id'   => 't_modify_pct',
					'question_type' => 'slider',
					'slider_min'    => 0,
					'slider_max'    => 25,
					'offer_type'    => 'training',
					'priority'      => 91,
					'title'         => __( 'Verification training recommended', 'ai-risk-benchmark' ),
					'body'          => __( 'You rarely modify AI outputs before use. The Teacher Verification Framework builds a practical check-before-you-share workflow.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Verification framework training', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'teacher_pupil_data',
					'roles'         => array( 'teacher' ),
					'question_id'   => 't_pupil_data',
					'answer_values' => array( 'yes', 'unsure' ),
					'offer_type'    => 'training',
					'priority'      => 86,
					'title'         => __( 'Pupil data & AI training', 'ai-risk-benchmark' ),
					'body'          => __( 'Entering pupil data into AI tools creates GDPR and safeguarding exposure. Staff training and a clear acceptable-use policy are essential.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book data-safe AI training', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			$offer(
				array(
					'id'            => 'teacher_literacy_gap',
					'roles'         => array( 'teacher' ),
					'question_id'   => 't_hallucinations',
					'answer_values' => array( 'limited', 'basic' ),
					'offer_type'    => 'cpd',
					'priority'      => 74,
					'title'         => __( 'AI literacy CPD for staff', 'ai-risk-benchmark' ),
					'body'          => __( 'Understanding hallucinations and limitations is foundational. CPD covers classroom-ready explanations pupils can apply.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book AI literacy CPD', 'ai-risk-benchmark' ),
					'cta_url'       => self::contact_page_url(),
				)
			),
			array(
				'id'         => 'teacher_assessment_gap',
				'roles'      => array( 'teacher' ),
				'trigger'    => 'domain_band',
				'domain'     => 'human_oversight',
				'min_band'   => 'high',
				'offer_type' => 'template',
				'priority'   => 70,
				'title'      => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'body'       => __( 'Human oversight scores suggest verification habits need strengthening across your practice.', 'ai-risk-benchmark' ),
				'cta_text'   => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'cta_url'    => self::contact_page_url(),
			),
			// Metric triggers.
			array(
				'id'         => 'high_dependency_leader',
				'roles'      => array( 'leader' ),
				'trigger'    => 'metric',
				'metric'     => 'dependency_index',
				'metric_min' => 55,
				'offer_type' => 'consultation',
				'priority'   => 76,
				'title'      => __( 'Whole-school dependency review', 'ai-risk-benchmark' ),
				'body'       => __( 'Benchmark signals elevated AI dependency. A consultation helps you plan culture, policy and CPD to restore independent thinking.', 'ai-risk-benchmark' ),
				'cta_text'   => __( 'Book dependency review', 'ai-risk-benchmark' ),
				'cta_url'    => self::contact_page_url(),
			),
		);
	}

	/**
	 * Gateway copy — audit as doorway to tracking, CPD and consultation.
	 *
	 * @return array<string, mixed>
	 */
	private static function default_gateway(): array {
		return array(
			'headline'          => __( 'What happens after the audit?', 'ai-risk-benchmark' ),
			'intro'             => __( 'Your audit is the gateway — not the end point. Re-audit annually to track progress, book CPD for your highest-risk domains, and access support when gaps need closing.', 'ai-risk-benchmark' ),
			'track_progress'    => array(
				'title'    => __( 'Track progress over time', 'ai-risk-benchmark' ),
				'body'     => __( 'Save your school name, then re-run the benchmark each term. Your school-wide dashboard shows whether readiness is improving across teachers, students, parents and leaders.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'View school dashboard', 'ai-risk-benchmark' ),
				'cta_url'  => '',
			),
			'book_cpd'          => array(
				'title'    => __( 'Book targeted CPD', 'ai-risk-benchmark' ),
				'body'     => __( 'Turn your highest-risk domains into practical staff training — verification, data protection, safeguarding and assessment integrity.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'Explore CPD options', 'ai-risk-benchmark' ),
				'cta_url'  => self::interest_form_url( 'whole_school_cpd' ),
			),
			'book_consultation' => array(
				'title'    => __( 'Book a free consultation', 'ai-risk-benchmark' ),
				'body'     => __( 'Need an AI policy, governance review or whole-school plan? We help schools move from benchmark results to confident, DfE-aligned action.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'Discuss your results', 'ai-risk-benchmark' ),
				'cta_url'  => self::interest_form_url( 'governance_review' ),
			),
		);
	}
}
