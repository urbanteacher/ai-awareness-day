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
	 * Full default configuration.
	 *
	 * @return array<string, mixed>
	 */
	public static function config(): array {
		return array(
			'version'             => 4,
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
			'disclaimer'       => __( 'This tool is an educational self-assessment aligned to DfE, ICO, KCSIE, JCQ, Ofqual and Ofsted guidance for schools in England. It is not legal advice and does not replace safeguarding, data protection or legal counsel. It does not imply official endorsement.', 'ai-risk-benchmark' ),
			'intro'            => __( 'Choose your role and complete a 10–15 minute audit. You will receive your DfE Alignment Score, signature metrics, a risk heat map, tailored recommendations and optional whole-school tracking. No student personal data is collected.', 'ai-risk-benchmark' ),
			'role_benchmarks'  => self::default_role_benchmarks(),
			'signature_metrics'=> self::default_signature_metrics(),
			'after_audit'      => self::default_after_audit(),
			'services'         => self::default_services(),
			'aad_2027'         => self::default_aad_2027(),
			'consultation_cta' => array(
				'title' => __( 'Book a free consultation', 'ai-risk-benchmark' ),
				'url'   => 'https://aiawarenessday.co.uk/contact/',
				'text'  => __( 'Discuss your results with our team', 'ai-risk-benchmark' ),
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
				array( 'label' => "Ofsted's Approach to AI", 'url' => 'https://www.gov.uk/government/publications/ofsteds-approach-to-artificial-intelligence-ai-in-education' ),
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
			'headline'          => __( "The UK's First DfE-Aligned AI Risk & Readiness Benchmark for Schools", 'ai-risk-benchmark' ),
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
					__( 'Awareness', 'ai-risk-benchmark' ),
					__( 'Digital Safety', 'ai-risk-benchmark' ),
					__( 'Home Oversight', 'ai-risk-benchmark' ),
					__( 'AI Confidence', 'ai-risk-benchmark' ),
				),
				'outputs'  => array(
					__( 'Parent AI Awareness Score', 'ai-risk-benchmark' ),
					__( 'Parent Digital Safety Score', 'ai-risk-benchmark' ),
					__( 'Parent Readiness Score', 'ai-risk-benchmark' ),
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
				array( 'label' => __( 'AI Policy Generator', 'ai-risk-benchmark' ), 'url' => 'https://aiawarenessday.co.uk/resources/' ),
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
				'cta_url'     => 'https://aiawarenessday.co.uk/resources/',
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
				'cta_url'     => 'https://aiawarenessday.co.uk/resources/',
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
					'cta_url'       => 'https://aiawarenessday.co.uk/resources/',
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
