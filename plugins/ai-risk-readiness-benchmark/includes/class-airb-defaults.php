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

	/** DfE — operational handbook (policies, acceptable use). */
	public static function dfe_url_using_ai(): string {
		return 'https://www.gov.uk/government/publications/using-ai-in-education-settings';
	}

	/** DfE — generative AI overview (data protection, DPIAs, risk). */
	public static function dfe_url_generative_ai(): string {
		return 'https://www.gov.uk/government/publications/generative-artificial-intelligence-ai-in-education';
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
			'ai_dependency'         => __( 'AI Dependency', 'ai-risk-benchmark' ),
			'privacy'               => __( 'Privacy & Data Protection', 'ai-risk-benchmark' ),
			'safeguarding'          => __( 'Safeguarding', 'ai-risk-benchmark' ),
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
			'teacher' => __( 'Teacher', 'ai-risk-benchmark' ),
			'student' => __( 'Student', 'ai-risk-benchmark' ),
			'parent'  => __( 'Parent / Carer', 'ai-risk-benchmark' ),
			'leader'  => __( 'School Leader', 'ai-risk-benchmark' ),
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
			'assessment_integrity' => __( 'Review assessment design against JCQ and Ofqual AI guidance.', 'ai-risk-benchmark' ),
			'governance'           => __( 'Publish an AI policy, name a lead, and schedule an annual review.', 'ai-risk-benchmark' ),
			'safe_adoption'        => __( 'Maintain an approved tool list and structured staff training.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Parent / carer results — display domains, guidance copy and priority focus.
	 *
	 * @return array<string, mixed>
	 */
	public static function parent_result_config(): array {
		return array(
			'display_domains' => array(
				'parent_awareness' => array(
					'label'       => __( 'Parent Awareness Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_child_uses', 'p_know_tools' ),
					'color'       => '#1B6B8C',
				),
				'home_ai_safety' => array(
					'label'       => __( 'Home AI Safety Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_discuss_use', 'p_deepfakes' ),
					'color'       => '#15803d',
				),
				'child_privacy_risk' => array(
					'label'       => __( 'Child Privacy Risk Score', 'ai-risk-benchmark' ),
					'metric_type' => 'risk',
					'questions'   => array( 'p_no_share' ),
					'color'       => '#7c3aed',
				),
				'homework_support_risk' => array(
					'label'       => __( 'Homework Support Risk Score', 'ai-risk-benchmark' ),
					'metric_type' => 'risk',
					'questions'   => array( 'p_cheating', 'p_spot_ai_hw' ),
					'color'       => '#a16207',
				),
				'parent_confidence' => array(
					'label'       => __( 'Parent Confidence Score', 'ai-risk-benchmark' ),
					'metric_type' => 'score',
					'questions'   => array( 'p_equipped' ),
					'color'       => '#2563eb',
				),
			),
			'band_summaries' => array(
				'emerging'    => __( 'There are important gaps in your awareness of how children use AI at home. Start with the guidance below and talk openly with your child about their AI use.', 'ai-risk-benchmark' ),
				'developing'  => __( 'This means you have some awareness of AI risks, but there are important areas where more guidance would help you support your child safely.', 'ai-risk-benchmark' ),
				'established' => __( 'You have a solid foundation for guiding your child\'s AI use. Keep conversations going and revisit the focus areas below as tools and risks evolve.', 'ai-risk-benchmark' ),
				'strong'      => __( 'You are well placed to support safe, honest and independent AI use at home. Share what works with your child\'s school to help others.', 'ai-risk-benchmark' ),
			),
			'focus_topics' => array(
				array(
					'slug'  => 'child_privacy_risk',
					'label' => __( 'Child privacy', 'ai-risk-benchmark' ),
					'body'  => __( 'Make sure your child does not enter their full name, school name, address, images, personal details, SEND information or safeguarding concerns into public AI tools.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'home_ai_safety',
					'label' => __( 'Home AI safety', 'ai-risk-benchmark' ),
					'body'  => __( 'Talk to your child about AI-generated images, fake profiles, deepfakes, bullying, impersonation and pressure to share private content.', 'ai-risk-benchmark' ),
				),
				array(
					'slug'  => 'homework_support_risk',
					'label' => __( 'Homework support', 'ai-risk-benchmark' ),
					'body'  => __( 'AI should help your child understand, practise and check work — not replace their own thinking or produce work to submit as their own.', 'ai-risk-benchmark' ),
				),
			),
			'priority_focus' => array(
				'intro' => __( 'Based on your result, we recommend', 'ai-risk-benchmark' ),
				'title' => __( 'the Parent AI Safety Guide or a Parent AI Awareness Session.', 'ai-risk-benchmark' ),
				'body'  => '',
			),
			'scores_heading' => __( 'Your scores', 'ai-risk-benchmark' ),
			'exposure_areas' => array(
				__( 'Privacy and personal data', 'ai-risk-benchmark' ),
				__( 'Deepfakes and AI-enabled harm', 'ai-risk-benchmark' ),
				__( 'Homework becoming AI replacement rather than learning support', 'ai-risk-benchmark' ),
			),
			'share_hint' => __( 'These results are for you. Sharing them with your child\'s school can help build a whole-school picture of AI awareness across parents, students, teachers and leaders.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * Teacher results — strengths-first copy, champion pathway and school progress.
	 *
	 * @return array<string, mixed>
	 */
	public static function teacher_result_config(): array {
		return array(
			'headlines' => array(
				'strong'      => __( 'You are demonstrating good AI governance behaviours.', 'ai-risk-benchmark' ),
				'established' => __( 'You are building solid AI awareness with a few areas to refine further.', 'ai-risk-benchmark' ),
				'developing'  => __( 'You have a foundation to build on — the guidance below will help strengthen your AI practice.', 'ai-risk-benchmark' ),
				'emerging'    => __( 'There are important gaps in your AI awareness. Focus on the opportunities below to reduce risk.', 'ai-risk-benchmark' ),
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
			),
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
					'url'   => 'https://aiawarenessday.co.uk/contact/',
				),
				array(
					'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ),
					'url'   => 'https://aiawarenessday.co.uk/resources/',
				),
				array(
					'label' => __( 'DfE AI Guidance Update Briefing', 'ai-risk-benchmark' ),
					'url'   => 'https://www.gov.uk/government/publications/generative-artificial-intelligence-ai-in-education',
				),
				array(
					'label' => __( 'AI Awareness Day Toolkit', 'ai-risk-benchmark' ),
					'url'   => 'https://aiawarenessday.co.uk/',
				),
			),
			'benchmark_summary_title' => __( 'Teacher Benchmark Summary', 'ai-risk-benchmark' ),
			'school_contribution' => array(
				'heading' => __( 'School contribution', 'ai-risk-benchmark' ),
				'intro'   => __( 'Your result contributes to your school\'s:', 'ai-risk-benchmark' ),
				'items'   => array(
					__( 'DfE Alignment Score', 'ai-risk-benchmark' ),
					__( 'AI Dependency Index™', 'ai-risk-benchmark' ),
					__( 'Human Oversight Benchmark™', 'ai-risk-benchmark' ),
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
		);
	}

	/**
	 * Student results — learning-coach copy, metrics and resources.
	 *
	 * @return array<string, mixed>
	 */
	public static function student_result_config(): array {
		return array(
			'profile_title' => __( 'Your AI Learning Profile', 'ai-risk-benchmark' ),
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
			'strengths_heading'     => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
			'opportunities_heading' => __( 'Opportunities to improve', 'ai-risk-benchmark' ),
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
			'resources_heading' => __( 'Free student resources', 'ai-risk-benchmark' ),
			'student_resources' => array(
				array(
					'label' => __( 'AI Study Skills Guide', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'student-ai-study-skills' ),
				),
				array(
					'label' => __( 'Think First, Prompt Second Framework', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'think-first-prompt-second' ),
				),
				array(
					'label' => __( 'Verify Before You Trust Checklist', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'how-to-check-ai-answers' ),
				),
				array(
					'label' => __( 'AI Awareness Day Student Challenge', 'ai-risk-benchmark' ),
					'url'   => self::hub_page_url( 'ai-awareness-day' ),
				),
			),
			'school_contribution' => array(
				'heading' => __( 'School contribution', 'ai-risk-benchmark' ),
				'body'    => __( 'Your anonymous responses help your school understand how students are really using AI — things like attempting work before using AI, verifying answers and protecting personal information. Leaders see patterns across the whole school, not individual names.', 'ai-risk-benchmark' ),
			),
			'share_hint' => __( 'These results are for you. You can share them with a teacher if you want help with any of the areas above.', 'ai-risk-benchmark' ),
		);
	}

	/**
	 * School leader results — governance benchmark, executive summary and commercial next steps.
	 *
	 * @return array<string, mixed>
	 */
	public static function leader_result_config(): array {
		return array(
			'executive_title' => __( 'Executive Summary', 'ai-risk-benchmark' ),
			'executive_intros' => array(
				'leading'     => __( 'Your school demonstrates strong foundations for safe, governed AI adoption with consistent practices across most domains.', 'ai-risk-benchmark' ),
				'established' => __( 'Your school demonstrates a solid foundation for safe AI adoption.', 'ai-risk-benchmark' ),
				'developing'  => __( 'Your school has started its AI journey with meaningful awareness in place, but governance and oversight need strengthening.', 'ai-risk-benchmark' ),
				'emerging'    => __( 'Your school is at an early stage of AI governance. Focused leadership action will reduce exposure and build staff confidence.', 'ai-risk-benchmark' ),
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
				'ai_dependency'        => __( 'AI Dependency', 'ai-risk-benchmark' ),
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
			'maturity_heading' => __( 'Governance maturity', 'ai-risk-benchmark' ),
			'peer_benchmark_title' => __( 'Benchmark against similar schools', 'ai-risk-benchmark' ),
			'peer_phase_labels' => array(
				'primary'     => __( 'Average Primary School', 'ai-risk-benchmark' ),
				'secondary'   => __( 'Average Secondary School', 'ai-risk-benchmark' ),
				'all_through' => __( 'Average All-through School', 'ai-risk-benchmark' ),
				'default'     => __( 'Average School', 'ai-risk-benchmark' ),
			),
			'peer_benchmark_fallback' => array(
				'primary'     => array( 'average' => 58, 'top_quartile' => 80 ),
				'secondary'   => array( 'average' => 62, 'top_quartile' => 84 ),
				'all_through' => array( 'average' => 60, 'top_quartile' => 82 ),
				'default'     => array( 'average' => 62, 'top_quartile' => 84 ),
			),
			'focus_heading' => __( 'Priority focus areas', 'ai-risk-benchmark' ),
			'focus_actions_label' => __( 'Recommended actions', 'ai-risk-benchmark' ),
			'focus_copy' => array(
				'human_oversight' => array(
					'summary' => __( 'Staff report using AI regularly but verification practices are inconsistent.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Introduce a verification framework', 'ai-risk-benchmark' ),
						__( 'Embed "Verify Before You Trust"', 'ai-risk-benchmark' ),
						__( 'Review AI-generated materials before classroom use', 'ai-risk-benchmark' ),
					),
				),
				'governance' => array(
					'summary' => __( 'AI governance is not yet consistent across all staff groups and departments.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Publish or refresh your AI policy', 'ai-risk-benchmark' ),
						__( 'Name a senior AI lead with clear accountability', 'ai-risk-benchmark' ),
						__( 'Schedule an annual governance review', 'ai-risk-benchmark' ),
					),
				),
				'safeguarding' => array(
					'summary' => __( 'Safeguarding procedures may not yet fully cover AI-specific risks such as deepfakes and impersonation.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Update safeguarding policy for AI-enabled harm', 'ai-risk-benchmark' ),
						__( 'Train DSL and pastoral teams on deepfake risks', 'ai-risk-benchmark' ),
						__( 'Add AI scenarios to safeguarding training', 'ai-risk-benchmark' ),
					),
				),
				'assessment_integrity' => array(
					'summary' => __( 'Assessment controls may not yet reflect current JCQ and Ofqual expectations for AI use.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Conduct a JCQ-aligned assessment review', 'ai-risk-benchmark' ),
						__( 'Update malpractice and supervision procedures', 'ai-risk-benchmark' ),
						__( 'Communicate assessment rules clearly to pupils', 'ai-risk-benchmark' ),
					),
				),
				'privacy' => array(
					'summary' => __( 'Staff may not consistently understand what pupil data can enter AI tools.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Complete the AI Data Protection Checklist with your DPO', 'ai-risk-benchmark' ),
						__( 'Publish clear staff guidance on data in AI tools', 'ai-risk-benchmark' ),
						__( 'Review approved tool list against ICO expectations', 'ai-risk-benchmark' ),
					),
				),
				'ai_literacy' => array(
					'summary' => __( 'Staff AI literacy varies — not all colleagues understand limitations and verification needs.', 'ai-risk-benchmark' ),
					'actions' => array(
						__( 'Deliver whole-staff AI literacy training', 'ai-risk-benchmark' ),
						__( 'Cover hallucinations, bias and no-go situations', 'ai-risk-benchmark' ),
						__( 'Build AI literacy into induction for new staff', 'ai-risk-benchmark' ),
					),
				),
				'safe_adoption' => array(
					'summary' => __( 'AI tools may be adopted ad hoc without consistent risk assessment.', 'ai-risk-benchmark' ),
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
				'privacy'              => __( 'Complete your AI data protection review with the DPO.', 'ai-risk-benchmark' ),
				'ai_literacy'          => __( 'Deliver whole-staff AI literacy and verification training.', 'ai-risk-benchmark' ),
				'safe_adoption'        => __( 'Establish an approved tool list and structured adoption process.', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'Reduce over-reliance on AI through policy and staff training.', 'ai-risk-benchmark' ),
			),
			'default_priority_action' => __( 'Strengthen whole-school governance and staff verification practices.', 'ai-risk-benchmark' ),
			'next_steps_title'    => __( 'Next steps', 'ai-risk-benchmark' ),
			'next_steps_subtitle' => __( 'Recommended for your school', 'ai-risk-benchmark' ),
			'next_steps_intro'    => __( 'Based on your benchmark results:', 'ai-risk-benchmark' ),
			'next_step_blocks' => array(
				'policy_generator' => array(
					'title'    => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
					'body'     => __( 'Create or refresh your school AI policy aligned to DfE guidance.', 'ai-risk-benchmark' ),
					'cta_text' => __( 'Start AI Policy Generator', 'ai-risk-benchmark' ),
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
					'cta_url'  => 'https://aiawarenessday.co.uk/contact/',
				),
				'governance_review' => array(
					'title' => __( 'Governance Review', 'ai-risk-benchmark' ),
					'body'  => __( 'Receive a detailed report with:', 'ai-risk-benchmark' ),
					'deliverables' => array(
						__( 'DfE Alignment Score', 'ai-risk-benchmark' ),
						__( 'AI Dependency Index™', 'ai-risk-benchmark' ),
						__( 'Human Oversight Benchmark™', 'ai-risk-benchmark' ),
						__( 'Policy recommendations', 'ai-risk-benchmark' ),
						__( 'Risk heat map', 'ai-risk-benchmark' ),
					),
					'cta_text' => __( 'Request Governance Review', 'ai-risk-benchmark' ),
					'cta_url'  => 'https://aiawarenessday.co.uk/contact/',
				),
			),
			'rollout_unlock_copy' => __( 'Whole-school benchmarking unlocks after {threshold}+ responses.', 'ai-risk-benchmark' ),
			'heatmap_heading'     => __( 'Risk heat map', 'ai-risk-benchmark' ),
		);
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
	 * Canonical URL for the embedded benchmark (timeline entry when available).
	 */
	public static function benchmark_page_url(): string {
		if ( function_exists( 'get_page_by_path' ) ) {
			$timeline = get_page_by_path( 'ai-risk-readiness-benchmark', OBJECT, 'timeline' );
			if ( $timeline instanceof WP_Post ) {
				$url = get_permalink( $timeline );
				if ( is_string( $url ) && '' !== $url ) {
					return $url;
				}
			}
		}
		return self::hub_page_url( '' ) . '#airb-benchmark';
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
				'heading' => __( 'Learn how to improve this score', 'ai-risk-benchmark' ),
				'intro'   => __( 'Your benchmark is a starting point. Use these free resources to strengthen the areas where you scored lowest — then retake the audit to track progress.', 'ai-risk-benchmark' ),
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
								array( 'kind' => 'watch', 'label' => __( '10-minute Teacher Verification Masterclass', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
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
								array( 'kind' => 'watch', 'label' => __( 'AI Limitations Explained', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-assessment-guide' ),
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
							'metric_label' => __( 'AI Dependency Index™', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'High dependency can reduce independent professional judgement and model over-reliance for pupils.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Verify Before You Trust Framework', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-verification-framework' ),
								array( 'kind' => 'download', 'label' => __( 'AI Lesson Planning Checklist', 'ai-risk-benchmark' ), 'path' => 'teacher-ai-lesson-planning-checklist' ),
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
						'home_ai_safety' => array(
							'slug'         => 'home_ai_safety',
							'metric_label' => __( 'Home AI Safety Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Many children use AI without discussing it with parents.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'read', 'label' => __( 'Questions To Ask Your Child About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
								array( 'kind' => 'read', 'label' => __( 'Deepfake Awareness Briefing', 'ai-risk-benchmark' ), 'path' => 'parent-deepfake-awareness' ),
								array( 'kind' => 'join', 'label' => __( 'Parent Webinar', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
							),
						),
						'parent_awareness' => array(
							'slug'         => 'parent_awareness',
							'metric_label' => __( 'Parent Awareness Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Understanding how your child uses AI at home is the first step to guiding them safely.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'read', 'label' => __( 'Talking To Children About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
							),
						),
						'child_privacy_risk' => array(
							'slug'         => 'child_privacy',
							'metric_label' => __( 'Child Privacy Risk Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Children may share names, photos and school details with AI tools without realising the risks.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
							),
						),
						'homework_support_risk' => array(
							'slug'         => 'homework',
							'metric_label' => __( 'Homework Support Risk Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'AI should support learning — not replace your child\'s own thinking or effort.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Homework Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-homework-guide' ),
								array( 'kind' => 'read', 'label' => __( 'Questions To Ask Your Child About AI', 'ai-risk-benchmark' ), 'path' => 'talking-to-children-about-ai' ),
							),
						),
						'parent_confidence' => array(
							'slug'         => 'confidence',
							'metric_label' => __( 'Parent Confidence Score', 'ai-risk-benchmark' ),
							'why_heading'  => $why,
							'why_body'     => __( 'Confidence grows with simple conversations, clear boundaries and knowing who to ask at school.', 'ai-risk-benchmark' ),
							'why_risks'    => array(),
							'actions_heading' => $improve,
							'resources'    => array(
								array( 'kind' => 'read', 'label' => __( 'Parent AI Safety Guide', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
								array( 'kind' => 'join', 'label' => __( 'Parent Webinar', 'ai-risk-benchmark' ), 'path' => 'parent-ai-safety' ),
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
								array( 'kind' => 'read', 'label' => __( 'AI Policy Generator', 'ai-risk-benchmark' ), 'path' => 'ai-policy-generator' ),
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
	 * Hub page definitions for one-time seeding.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function hub_page_definitions(): array {
		$benchmark_cta = __( 'Take the free AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' );
		$pages         = array(
			array(
				'slug'    => 'teacher-ai-verification-framework',
				'title'   => __( 'Teacher AI Verification Framework', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Verify Before You Trust — a practical framework for checking AI outputs before classroom use.', 'ai-risk-benchmark' ),
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
				'excerpt' => __( 'A simple framework for attempting work yourself before asking AI for help.', 'ai-risk-benchmark' ),
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
				'excerpt' => __( 'Support your child\'s safe and healthy use of AI at home.', 'ai-risk-benchmark' ),
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
				'title'   => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Create or refresh your school AI policy aligned to DfE guidance.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'school-ai-governance',
				'title'   => __( 'School AI Governance', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Governance checklist, leadership toolkit and accountability frameworks.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'dfe-ai-compliance-checklist',
				'title'   => __( 'DfE AI Compliance Checklist', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Track alignment with DfE, ICO, KCSIE and assessment guidance.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'ai-risk-register',
				'title'   => __( 'AI Risk Register', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Template for recording and reviewing AI risks across your school.', 'ai-risk-benchmark' ),
				'audience'=> 'leader',
			),
			array(
				'slug'    => 'ai-awareness-day',
				'title'   => __( 'AI Awareness Day', 'ai-risk-benchmark' ),
				'excerpt' => __( 'Whole-school programme for leaders, staff, students and parents.', 'ai-risk-benchmark' ),
				'audience'=> 'all',
			),
		);

		$out = array();
		foreach ( $pages as $page ) {
			$excerpt = (string) ( $page['excerpt'] ?? '' );
			$out[]   = array(
				'slug'    => (string) $page['slug'],
				'title'   => (string) $page['title'],
				'excerpt' => $excerpt,
				'content' => self::hub_page_content( (string) $page['title'], $excerpt, $benchmark_cta ),
			);
		}

		return $out;
	}

	/**
	 * Default Gutenberg content for a hub page.
	 */
	private static function hub_page_content( string $title, string $excerpt, string $benchmark_cta ): string {
		$blocks   = array();
		$blocks[] = '<!-- wp:paragraph --><p>' . esc_html( $excerpt ) . '</p><!-- /wp:paragraph -->';
		$blocks[] = '<!-- wp:paragraph --><p>' . esc_html__( 'This resource supports the AI Risk & Readiness Benchmark™ — use it after your audit to improve the areas where you scored lowest.', 'ai-risk-benchmark' ) . '</p><!-- /wp:paragraph -->';
		$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'What you\'ll find here', 'ai-risk-benchmark' ) . '</h2><!-- /wp:heading -->';
		$blocks[] = '<!-- wp:list --><ul class="wp-block-list">'
			. '<li>' . esc_html__( 'Practical guidance aligned to UK school expectations', 'ai-risk-benchmark' ) . '</li>'
			. '<li>' . esc_html__( 'Downloadable tools and checklists', 'ai-risk-benchmark' ) . '</li>'
			. '<li>' . esc_html__( 'Links to AI Awareness Day sessions and support', 'ai-risk-benchmark' ) . '</li>'
			. '</ul><!-- /wp:list -->';
		$blocks[] = '<!-- wp:paragraph --><p><em>' . esc_html__( 'Content on this page can be expanded in the WordPress editor — frameworks, videos and downloads can be added here.', 'ai-risk-benchmark' ) . '</em></p><!-- /wp:paragraph -->';
		$blocks[] = '<!-- wp:paragraph --><p><a href="' . esc_url( self::benchmark_page_url() ) . '">' . esc_html( $benchmark_cta ) . '</a></p><!-- /wp:paragraph -->';

		return implode( "\n\n", $blocks );
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
			'leader'  => array( 'tint' => '#f1f5f9', 'accent' => '#475569' ),
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
			'leader'  => 1.4,
		);
	}

	/**
	 * Full default configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function config(): array {
		return array(
			'version'             => 8,
			'framework'           => self::default_framework(),
			'domain_sources'      => self::default_domain_sources(),
			'positioning'         => self::default_positioning(),
			'domain_descriptions' => array(
				'safe_adoption'        => __( 'Does the school assess risks before introducing AI?', 'ai-risk-benchmark' ),
				'human_oversight'      => __( 'Are users reviewing, challenging and verifying AI outputs?', 'ai-risk-benchmark' ),
				'ai_dependency'        => __( 'How reliant have users become on AI?', 'ai-risk-benchmark' ),
				'privacy'              => __( 'Are staff and students protecting personal information?', 'ai-risk-benchmark' ),
				'safeguarding'         => __( 'Are AI-related safeguarding risks understood and managed?', 'ai-risk-benchmark' ),
				'assessment_integrity' => __( 'Are assessments protected against inappropriate AI use?', 'ai-risk-benchmark' ),
				'ai_literacy'          => __( 'Do users understand AI capabilities and limitations?', 'ai-risk-benchmark' ),
				'governance'           => __( 'Does the school have policies, accountability and oversight?', 'ai-risk-benchmark' ),
			),
			'disclaimer'       => '',
			'intro'            => __( 'Choose your role and complete a 10–15 minute audit. You will receive your DfE Alignment Score, signature metrics, a risk heat map, tailored recommendations and optional whole-school tracking. No student personal data is collected.', 'ai-risk-benchmark' ),
			'role_benchmarks'  => self::default_role_benchmarks(),
			'signature_metrics'=> self::default_signature_metrics(),
			'after_audit'      => self::default_after_audit(),
			'services'         => self::default_services(),
			'aad_2027'         => self::default_aad_2027(),
			'consultation_cta' => array(
				'title' => __( 'Contact the AI Awareness Day team', 'ai-risk-benchmark' ),
				'url'   => 'https://aiawarenessday.co.uk/contact/',
				'text'  => __( 'Get support with your results', 'ai-risk-benchmark' ),
			),
			'gateway'          => self::default_gateway(),
			'pathway_offers'   => self::default_pathway_offers(),
			'guidance_refs'    => array(
				array( 'label' => 'DfE Generative AI in Education', 'url' => 'https://www.gov.uk/government/publications/generative-artificial-intelligence-ai-in-education' ),
				array( 'label' => 'DfE Using AI in Education Settings', 'url' => 'https://www.gov.uk/government/publications/using-ai-in-education-settings' ),
				array( 'label' => 'DfE Product Safety Expectations', 'url' => 'https://www.gov.uk/government/publications/generative-artificial-intelligence-ai-product-safety-expectations' ),
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
			'statement'    => __( 'A DfE-aligned benchmark that measures AI exposure, dependency, governance and safe adoption across Teachers, Students, Parents and School Leaders.', 'ai-risk-benchmark' ),
			'annual_note'  => __( 'An annual benchmark schools can use to evidence responsible AI adoption against DfE, KCSIE, ICO, JCQ, Ofqual and Ofsted expectations.', 'ai-risk-benchmark' ),
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
			'headline'          => __( 'AI Risk & Readiness Benchmark for Schools', 'ai-risk-benchmark' ),
			'tagline'           => __( 'Helping schools adopt AI safely, responsibly and with confidence.', 'ai-risk-benchmark' ),
			'problem'           => __( 'Schools are under increasing pressure to adopt AI safely, but most guidance tells schools what they should do rather than helping them understand their actual level of exposure.', 'ai-risk-benchmark' ),
			'problem_questions' => array(
				__( 'Are our teachers becoming over-reliant on AI?', 'ai-risk-benchmark' ),
				__( 'Are students still developing independent thinking?', 'ai-risk-benchmark' ),
				__( 'Do parents understand the risks and opportunities?', 'ai-risk-benchmark' ),
				__( 'Are we compliant with DfE, ICO, KCSIE, JCQ and Ofqual guidance?', 'ai-risk-benchmark' ),
				__( 'Do we have the right governance, policies and safeguards in place?', 'ai-risk-benchmark' ),
				__( 'How do I know if we\'re doing enough?', 'ai-risk-benchmark' ),
			),
			'problem_closing'   => __( 'Most existing solutions focus on AI adoption. Very few measure AI dependency, human oversight, behavioural risk and governance maturity across the whole school community.', 'ai-risk-benchmark' ),
			'solution'          => __( 'AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ),
			'solution_detail'   => __( 'A free DfE-aligned assessment platform that measures AI exposure, dependency, governance and safe adoption across teachers, students, parents and school leaders.', 'ai-risk-benchmark' ),
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
					__( 'AI Dependency Index™', 'ai-risk-benchmark' ),
					__( 'Human Oversight Ratio™', 'ai-risk-benchmark' ),
				),
			),
			'student' => array(
				'title'    => __( 'Student Benchmark', 'ai-risk-benchmark' ),
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
				'measures' => array(
					__( 'Awareness of your child\'s AI use', 'ai-risk-benchmark' ),
					__( 'Safety conversations at home', 'ai-risk-benchmark' ),
					__( 'Child privacy exposure', 'ai-risk-benchmark' ),
					__( 'Homework integrity', 'ai-risk-benchmark' ),
					__( 'Confidence to guide', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Parent Awareness Score', 'ai-risk-benchmark' ),
					__( 'Home AI Safety Score', 'ai-risk-benchmark' ),
					__( 'Child Privacy Risk Score', 'ai-risk-benchmark' ),
					__( 'Homework Support Risk Score', 'ai-risk-benchmark' ),
					__( 'Parent Confidence Score', 'ai-risk-benchmark' ),
				),
			),
			'leader'  => array(
				'title'    => __( 'School Leader Benchmark', 'ai-risk-benchmark' ),
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
				'title'   => __( 'AI Dependency Index™', 'ai-risk-benchmark' ),
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
				'title'   => __( 'Human Oversight Ratio™', 'ai-risk-benchmark' ),
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
				array( 'label' => __( 'AI Policy Generator', 'ai-risk-benchmark' ), 'url' => self::dfe_url_using_ai() ),
				array( 'label' => __( 'AI Governance Toolkit', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/resources/' ),
				array( 'label' => __( 'Teacher Training', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/contact/' ),
				array( 'label' => __( 'Assessment Integrity Reviews', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/contact/' ),
				array( 'label' => __( 'Parent Awareness Sessions', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/contact/' ),
				array( 'label' => __( 'Annual Benchmark Reports', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/contact/' ),
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
			'headline' => __( 'AI Awareness Day™', 'ai-risk-benchmark' ),
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
			'cta_url'  => 'https://aiawarenessday.co.uk/contact/',
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
				'title'       => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
				'body'        => __( 'Your governance score suggests policy foundations are not yet secure. Start with a whole-school AI use policy aligned to DfE expectations.', 'ai-risk-benchmark' ),
				'cta_text'    => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/contact/',
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
					'answer_values' => array( 'no', 'informal', 'draft' ),
					'offer_type'    => 'policy',
					'priority'      => 95,
					'title'         => __( 'You need a published AI policy', 'ai-risk-benchmark' ),
					'body'          => __( 'Your audit shows the school does not yet have a reviewed AI policy. Start with our DfE-aligned AI Policy Generator and adapt it to your context.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'AI Policy Generator', 'ai-risk-benchmark' ),
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
				)
			),
			$offer(
				array(
					'id'            => 'leader_staff_training',
					'roles'         => array( 'leader' ),
					'question_id'   => 'l_staff_training',
					'answer_values' => array( 'no', 'planned', 'some' ),
					'offer_type'    => 'cpd',
					'priority'      => 90,
					'title'         => __( 'Staff AI risk CPD required', 'ai-risk-benchmark' ),
					'body'          => __( 'Staff are not yet consistently trained on verification and AI risks. Book whole-staff CPD aligned to your benchmark gaps.', 'ai-risk-benchmark' ),
					'cta_text'      => __( 'Book staff CPD', 'ai-risk-benchmark' ),
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'    => 'https://aiawarenessday.co.uk/contact/',
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
				'cta_url'    => 'https://aiawarenessday.co.uk/contact/',
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
				'body'     => __( 'Save your school name and consent, then re-run the benchmark each term. Your school-wide dashboard shows whether readiness is improving across teachers, students, parents and leaders.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'View school dashboard', 'ai-risk-benchmark' ),
				'cta_url'  => '',
			),
			'book_cpd'          => array(
				'title'    => __( 'Book targeted CPD', 'ai-risk-benchmark' ),
				'body'     => __( 'Turn your highest-risk domains into practical staff training — verification, data protection, safeguarding and assessment integrity.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'Explore CPD options', 'ai-risk-benchmark' ),
				'cta_url'  => 'https://aiawarenessday.co.uk/contact/',
			),
			'book_consultation' => array(
				'title'    => __( 'Book a free consultation', 'ai-risk-benchmark' ),
				'body'     => __( 'Need an AI policy, governance review or whole-school plan? We help schools move from benchmark results to confident, DfE-aligned action.', 'ai-risk-benchmark' ),
				'cta_text' => __( 'Discuss your results', 'ai-risk-benchmark' ),
				'cta_url'  => 'https://aiawarenessday.co.uk/contact/',
			),
		);
	}
}
