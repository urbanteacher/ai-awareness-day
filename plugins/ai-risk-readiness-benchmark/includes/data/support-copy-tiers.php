<?php
/**
 * Tiered support staff results copy — loaded into support_result_config.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'profile_title' => __( 'Your results', 'ai-risk-benchmark' ),
	'copy_tiers'    => array(
		'readiness' => array(
			'emerging' => array(
				'signal'      => __( 'Action needed', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'There are important gaps in how AI is used in your operational role. Start with data protection and safeguarding awareness — your role puts you in contact with sensitive pupil information.', 'ai-risk-benchmark' ),
			),
			'developing' => array(
				'signal'      => __( 'Progress being made', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You have foundational AI awareness in place. Two areas need attention — particularly around data protection and safeguarding — where your role puts you in contact with sensitive pupil information.', 'ai-risk-benchmark' ),
			),
			'established' => array(
				'signal'      => __( 'Solid foundations', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You show solid operational AI awareness with a few areas to strengthen. Keep focusing on data handling and knowing when to escalate concerns.', 'ai-risk-benchmark' ),
			),
			'strong' => array(
				'signal'      => __( 'Strong position', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You demonstrate strong AI readiness for an operations and data-handling role. Sustain your habits as tools and school policies evolve.', 'ai-risk-benchmark' ),
			),
			'leading' => array(
				'signal'      => __( 'Leading practice', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You model responsible AI use in a support role — share your approach with colleagues and help build whole-school consistency.', 'ai-risk-benchmark' ),
			),
		),
	),
	'hero_metric_label'              => __( 'Overall readiness', 'ai-risk-benchmark' ),
	'metrics_section_heading'        => __( 'AI risk exposure', 'ai-risk-benchmark' ),
	'domains_section_heading'        => __( 'Readiness by domain', 'ai-risk-benchmark' ),
	'domains_section_heading_short'    => __( 'By domain', 'ai-risk-benchmark' ),
	'strengths_heading_short'          => __( 'Strengths', 'ai-risk-benchmark' ),
	'focus_section_heading'            => __( 'Priority focus areas — what to strengthen', 'ai-risk-benchmark' ),
	'focus_section_heading_short'      => __( 'Priority focus areas', 'ai-risk-benchmark' ),
	'rollout_section_heading'          => __( 'Your next unlock — whole-school picture', 'ai-risk-benchmark' ),
	'rollout_section_heading_short'    => __( 'Your next unlock', 'ai-risk-benchmark' ),
	'rollout_intro'                    => __( 'When enough staff complete the benchmark your SLT unlocks aggregated data — including how support staff as a group compare to teaching staff on key domains like data protection and safeguarding awareness.', 'ai-risk-benchmark' ),
	'rollout_intro_short'              => __( 'Unlock whole-school support staff insights when colleagues complete their audits.', 'ai-risk-benchmark' ),
	'rollout_rollout_cta'              => __( 'Encourage colleagues to take the benchmark', 'ai-risk-benchmark' ),
	'rollout_locked_items'             => array(
		array(
			'label'     => __( 'Support staff scores', 'ai-risk-benchmark' ),
			'count_key' => 'support',
		),
		array(
			'label'     => __( 'Staff vs teaching comparison', 'ai-risk-benchmark' ),
			'count_key' => 'teacher',
		),
	),
	'domain_rows' => array(
		array(
			'slug'  => 'safe_adoption',
			'label' => __( 'Safe adoption', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'ai_literacy',
			'label' => __( 'AI literacy', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'human_oversight',
			'label' => __( 'Human oversight', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'privacy',
			'label' => __( 'Privacy & data protection', 'ai-risk-benchmark' ),
		),
		array(
			'slug'   => 'safeguarding',
			'label'  => __( 'Safeguarding awareness', 'ai-risk-benchmark' ),
			'source' => 'support_display',
		),
	),
	'metric_signals' => array(
		'risk_exposure' => array(
			'high' => array(
				'signal'      => __( 'High exposure', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Several areas carry significant risk — prioritise data handling and safeguarding awareness before expanding AI use.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Moderate exposure', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Some areas carry risk — focus on data handling and safeguarding awareness first.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Lower exposure', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Your overall exposure risk is comparatively lower — maintain careful habits as AI use grows.', 'ai-risk-benchmark' ),
			),
		),
		'role_risk' => array(
			'high' => array(
				'signal'      => __( 'Higher in your role', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Support staff often handle sensitive data — your role makes data protection and safeguarding especially important.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Elevated in your role', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Your role involves sensitive information — strengthen data protection and reporting habits before using AI for operational tasks.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Well managed for your role', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are managing role-specific risks well — keep using approved tools and escalating concerns promptly.', 'ai-risk-benchmark' ),
			),
		),
	),
	'strength_tiers' => array(
		'safe_adoption' => array(
			'min'    => 70,
			'label'  => __( 'You assess AI tools before using them', 'ai-risk-benchmark' ),
			'detail' => __( 'Safe adoption {pct}% — you think before you use, which is the most important starting point.', 'ai-risk-benchmark' ),
		),
		'ai_literacy' => array(
			'min'    => 70,
			'label'  => __( 'You understand what AI tools can and can\'t do', 'ai-risk-benchmark' ),
			'detail' => __( 'AI literacy {pct}% — you have a solid grasp of AI limitations and don\'t over-trust outputs.', 'ai-risk-benchmark' ),
		),
		'human_oversight' => array(
			'min'    => 65,
			'label'  => __( 'You review AI outputs before acting on them', 'ai-risk-benchmark' ),
			'detail' => __( 'Human oversight {pct}% — you verify communications and information before they reach parents or colleagues.', 'ai-risk-benchmark' ),
		),
		'privacy' => array(
			'min'    => 70,
			'label'  => __( 'You protect sensitive data when using AI', 'ai-risk-benchmark' ),
			'detail' => __( 'Privacy & data protection {pct}% — you keep pupil and staff information out of unapproved AI tools.', 'ai-risk-benchmark' ),
		),
	),
	'focus_tiers' => array(
		'safeguarding' => array(
			'critical' => array(
				'severity'          => 'critical',
				'summary'           => __( 'Support staff often work directly with vulnerable pupils or hold sensitive information. AI-specific safeguarding risks — such as deepfakes, impersonation and AI-generated content — may not yet be part of your awareness.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'In your role this matters because', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'You may be a first point of contact for a pupil reporting an AI-related incident', 'ai-risk-benchmark' ),
					__( 'Deepfake images or AI-generated messages may be shared between pupils', 'ai-risk-benchmark' ),
					__( 'Knowing what to report and who to tell is part of your safeguarding duty', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Attend your school\'s next safeguarding update — check if it covers AI scenarios', 'ai-risk-benchmark' ),
					__( 'Know your DSL\'s name and when to refer a concern to them', 'ai-risk-benchmark' ),
					__( 'If a pupil mentions something AI-related that worries you, treat it as a safeguarding concern', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'          => 'moderate',
				'summary'           => __( 'Your awareness of AI-specific safeguarding risks could be stronger. Knowing how to recognise and report AI-enabled harm is part of your duty of care.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'In your role this matters because', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Pupils may raise AI-related concerns with support staff before teachers', 'ai-risk-benchmark' ),
					__( 'Deepfakes and impersonation are increasingly common in schools', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Ask your DSL whether AI scenarios are covered in your next safeguarding briefing', 'ai-risk-benchmark' ),
					__( 'If something AI-related worries you, report it — do not wait until you are certain', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'You have some safeguarding awareness — keep building confidence in recognising and escalating AI-related concerns.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Review your school\'s reporting route for online safety and AI-related incidents', 'ai-risk-benchmark' ),
					__( 'Share any AI concerns with your DSL promptly', 'ai-risk-benchmark' ),
				),
			),
		),
		'privacy' => array(
			'critical' => array(
				'severity'          => 'moderate',
				'summary'           => __( 'Support staff regularly handle pupil records, SEND information and sensitive communications. Entering any of this into a public AI tool — even to help draft a message — is a UK GDPR risk your school is responsible for.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Common situations to watch for', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Using AI to help write a letter or email that includes a pupil\'s name or details', 'ai-risk-benchmark' ),
					__( 'Uploading or pasting a document that contains pupil data into an AI tool', 'ai-risk-benchmark' ),
					__( 'Using a personal AI tool (not on the school\'s approved list) for work tasks', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Never enter pupil names, SEND details or sensitive data into a public AI tool', 'ai-risk-benchmark' ),
					__( 'Only use AI tools that appear on your school\'s approved list', 'ai-risk-benchmark' ),
					__( 'If you\'re unsure whether something is safe to share with AI, ask your line manager or DPO', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'          => 'moderate',
				'summary'           => __( 'Data protection habits need strengthening. Even small amounts of pupil information in AI tools can create GDPR exposure for your school.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Common situations to watch for', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Drafting parent communications that include identifiable pupil details', 'ai-risk-benchmark' ),
					__( 'Using AI on work devices without checking the approved tool list', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Check your school\'s approved AI tool list before each new use case', 'ai-risk-benchmark' ),
					__( 'Redact or anonymise information before using AI to help with drafting', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Your data protection awareness is developing — tighten habits around what enters AI tools.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Never paste pupil records or SEND information into public AI tools', 'ai-risk-benchmark' ),
					__( 'Ask your DPO or line manager if you are unsure about a specific task', 'ai-risk-benchmark' ),
				),
			),
		),
		'human_oversight' => array(
			'critical' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'AI-generated emails, letters and reports need careful review before they are sent or acted on.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Always read and edit AI drafts before sending to parents or external contacts', 'ai-risk-benchmark' ),
					__( 'Verify factual claims in AI-generated content against a trusted source', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Build a consistent habit of reviewing AI outputs — especially for communications that leave the school.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Treat every AI draft as a starting point, not a finished document', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Keep strengthening your verification habits as AI use grows in your role.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Pause before sending any AI-assisted communication and check names, facts and tone', 'ai-risk-benchmark' ),
				),
			),
		),
		'safe_adoption' => array(
			'critical' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Use only approved tools and know how to report AI-related data or safeguarding issues.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Check your school\'s approved AI tool list before using any new tool', 'ai-risk-benchmark' ),
					__( 'Know how to report AI-related data protection or safeguarding concerns', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Strengthen habits around approved tools and escalation routes.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Confirm approval status before using AI for a new task', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Keep using approved tools and reporting routes consistently.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Share any unapproved AI tool use with your line manager', 'ai-risk-benchmark' ),
				),
			),
		),
		'ai_literacy' => array(
			'critical' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Treat AI outputs as drafts that can be wrong — especially for factual or policy information.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Question AI outputs that sound authoritative but may be incorrect', 'ai-risk-benchmark' ),
					__( 'Do not rely on AI for statutory guidance or policy wording without verification', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Build confidence in recognising when AI may be inaccurate or inappropriate for your task.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Cross-check important facts before acting on AI-generated information', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'severity'  => 'moderate',
				'summary'   => __( 'Keep building your understanding of what AI can and cannot do reliably.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'When AI output surprises you, verify it before sharing or acting on it', 'ai-risk-benchmark' ),
				),
			),
		),
	),
	'cta_hero' => array(
		'key'                 => 'support_cpd',
		'pathway_kicker'      => __( 'Recommended next step', 'ai-risk-benchmark' ),
		'title'               => __( 'Targeted CPD — data protection & safeguarding', 'ai-risk-benchmark' ),
		'body'                => __( 'A focused session for support staff covering safe AI use in your specific role — including data handling, safeguarding responsibilities and what to do when something doesn\'t feel right.', 'ai-risk-benchmark' ),
		'deliverables'        => array(
			__( 'Data protection in your role', 'ai-risk-benchmark' ),
			__( 'AI safeguarding scenarios', 'ai-risk-benchmark' ),
			__( 'Approved tools guidance', 'ai-risk-benchmark' ),
			__( 'Reporting procedures', 'ai-risk-benchmark' ),
		),
		'cta_text'            => __( 'Book CPD session', 'ai-risk-benchmark' ),
		'secondary_cta_text'  => __( 'Request resources', 'ai-risk-benchmark' ),
		'secondary_key'       => 'support_resources',
	),
);
