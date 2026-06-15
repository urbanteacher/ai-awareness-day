<?php
/**
 * Tiered leader results copy — loaded into leader_result_config.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'copy_tiers' => array(
		'governance' => array(
			'not_in_place' => array(
				'signal'      => __( 'Not yet in place', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Initial awareness and draft policies exist but staff practice is inconsistent and oversight is limited.', 'ai-risk-benchmark' ),
			),
			'gaps' => array(
				'signal'      => __( 'Gaps in policy & practice', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Initial awareness exists but staff practice and oversight are inconsistent across the school.', 'ai-risk-benchmark' ),
			),
			'partial' => array(
				'signal'      => __( 'Partially embedded', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Policy exists but is not consistently followed or understood by all staff. Some departments are ahead of others.', 'ai-risk-benchmark' ),
			),
			'mostly' => array(
				'signal'      => __( 'Mostly embedded', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'Good governance structures are in place. Focus now on closing gaps in practice and ensuring annual review cycles are met.', 'ai-risk-benchmark' ),
			),
			'full' => array(
				'signal'      => __( 'Fully embedded', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Governance is strong, regularly reviewed and well understood across the school.', 'ai-risk-benchmark' ),
			),
		),
		'risk' => array(
			'critical' => array(
				'signal'      => __( 'Critical exposure', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Your school has very high AI risk across multiple domains. Immediate leadership action is required before risk materialises into a safeguarding or compliance incident.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'High exposure', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Significant risk across key domains. Most schools at Emerging stage sit in this range. Focused action on your top two domains will have the biggest impact.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Moderate exposure', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Some domains are well managed but others carry meaningful risk. Targeted improvement in your weakest areas will move your overall score significantly.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Low exposure', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'Your school is managing AI risk well across most areas. Remaining exposure is concentrated in one or two domains — review these specifically.', 'ai-risk-benchmark' ),
			),
			'minimal' => array(
				'signal'      => __( 'Minimal exposure', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'AI risk is well controlled across all domains. Maintain your current standards and schedule an annual review to stay ahead as tools evolve.', 'ai-risk-benchmark' ),
			),
		),
		'bias' => array(
			'critical' => array(
				'signal'      => __( 'Urgent equality risk', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Staff have not assessed whether AI tools could produce unfair or discriminatory outputs affecting pupils. This is a safeguarding, PSED and KCSIE accountability gap.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'Equality duty risk', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Awareness of AI bias is limited and there is no consistent process to check outputs for unfairness before they affect pupils.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Building awareness', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Some staff understand AI bias risks but practice is not yet consistent. Embed bias checks into your safeguarding and equality procedures.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Strong awareness', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Staff understand AI bias risks and have processes to spot unfair outputs. Keep this current as tools and pupil use evolve.', 'ai-risk-benchmark' ),
			),
		),
	),
	'focus_tiers' => array(
		'privacy' => array(
			'critical' => array(
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
			'high' => array(
				'summary' => __( 'Data protection awareness is inconsistent across the school. Some staff understand the risks but practice varies by department.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Audit which AI tools are currently in use across the school', 'ai-risk-benchmark' ),
					__( 'Clarify data handling rules with your DPO', 'ai-risk-benchmark' ),
					__( 'Update your approved tool guidance and share with all staff', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Most staff understand data protection basics but gaps remain in documentation or approved tool coverage.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Complete or refresh your DPIA for pupil-facing AI tools', 'ai-risk-benchmark' ),
					__( 'Confirm your approved tool list is current and accessible to all staff', 'ai-risk-benchmark' ),
				),
			),
		),
		'safeguarding' => array(
			'critical' => array(
				'summary' => __( 'Safeguarding procedures may not yet cover AI-specific harms. KCSIE expects DSLs to understand deepfakes, AI-enabled impersonation and synthetic content as part of online safety provision.', 'ai-risk-benchmark' ),
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
			'high' => array(
				'summary' => __( 'Safeguarding covers some AI risks but not consistently. DSL awareness may be limited to general online safety rather than AI-specific scenarios.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Review your safeguarding policy against current KCSIE AI guidance', 'ai-risk-benchmark' ),
					__( 'Brief your DSL on deepfake and impersonation risks', 'ai-risk-benchmark' ),
					__( 'Add at least one AI scenario to your next safeguarding training session', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Core AI safeguarding risks are covered. Focus on keeping pace as new AI-enabled harms emerge and ensuring the whole pastoral team is briefed.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Schedule an annual review of AI content in your safeguarding policy', 'ai-risk-benchmark' ),
					__( 'Confirm all pastoral staff have received AI scenario training', 'ai-risk-benchmark' ),
				),
			),
		),
		'bias_equality' => array(
			'critical' => array(
				'summary' => __( 'Your school has not yet assessed whether AI tools could produce unfair or discriminatory outputs affecting protected characteristics — a PSED and safeguarding gap.', 'ai-risk-benchmark' ),
				'likely_impact' => array(
					__( 'Discriminatory or stereotyped AI outputs may reach pupils unchecked', 'ai-risk-benchmark' ),
					__( 'Staff may not recognise bias as a safeguarding or equality issue', 'ai-risk-benchmark' ),
					__( 'No documented process for reviewing AI outputs for fairness', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Brief SLT and DSL on AI bias as a safeguarding and equality risk', 'ai-risk-benchmark' ),
					__( 'Add bias checks to your AI use guidance for staff', 'ai-risk-benchmark' ),
					__( 'Include protected characteristics in your next AI staff training session', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'Bias awareness is emerging but staff lack consistent guidance on spotting unfair AI outputs before they affect pupils.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Publish clear staff guidance on checking AI outputs for bias', 'ai-risk-benchmark' ),
					__( 'Train pastoral and teaching staff on discriminatory AI outputs', 'ai-risk-benchmark' ),
					__( 'Link AI bias to your equality and safeguarding policies', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Most staff understand AI can reflect bias but checks are not yet embedded in everyday practice.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Embed bias spot-checks into your AI verification standard', 'ai-risk-benchmark' ),
					__( 'Review whether pupil-facing AI tools have been assessed for fairness', 'ai-risk-benchmark' ),
				),
			),
			'low' => array(
				'summary' => __( 'Bias awareness is strong. Maintain training as new tools are adopted and review practice annually.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Include AI bias in induction for new staff', 'ai-risk-benchmark' ),
					__( 'Schedule an annual review of equality implications in your AI policy', 'ai-risk-benchmark' ),
				),
			),
		),
		'human_oversight' => array(
			'high' => array(
				'summary' => __( 'Staff are using AI regularly but verification is inconsistent across departments. Some staff are likely passing AI output to pupils without checking it first.', 'ai-risk-benchmark' ),
				'likely_impact' => array(
					__( 'Inconsistent staff practice across departments', 'ai-risk-benchmark' ),
					__( 'Over-trust in AI outputs without verification', 'ai-risk-benchmark' ),
					__( 'Uncertainty about appropriate classroom use', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Introduce a whole-school AI verification standard', 'ai-risk-benchmark' ),
					__( 'Embed the Verify Before You Trust framework in CPD', 'ai-risk-benchmark' ),
					__( 'Require sign-off on AI-generated materials before classroom use', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Most staff verify AI outputs but practice is not yet consistent across all departments or year groups.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Audit verification practice across departments', 'ai-risk-benchmark' ),
					__( 'Share the Verify Before You Trust framework with remaining staff', 'ai-risk-benchmark' ),
					__( 'Add AI verification to your next CPD cycle', 'ai-risk-benchmark' ),
				),
			),
			'low' => array(
				'summary' => __( 'Staff are generally verifying AI outputs before use. Focus on maintaining standards and supporting newer staff to adopt consistent practice.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Include AI verification in induction for new staff', 'ai-risk-benchmark' ),
					__( 'Review practice annually as part of your AI governance cycle', 'ai-risk-benchmark' ),
				),
			),
		),
		'assessment_integrity' => array(
			'high' => array(
				'summary' => __( 'Assessment controls may not yet reflect current JCQ and Ofqual expectations. AI rules are likely inconsistent across departments — some staff communicating boundaries clearly, others not at all.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Conduct a JCQ-aligned assessment review across departments', 'ai-risk-benchmark' ),
					__( 'Update malpractice and exam supervision procedures', 'ai-risk-benchmark' ),
					__( 'Communicate AI rules clearly and consistently to all pupils', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Some departments have updated assessment controls but practice is not consistent school-wide.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Identify which departments have not yet reviewed their AI assessment guidance', 'ai-risk-benchmark' ),
					__( 'Share a standard template for communicating AI rules to pupils', 'ai-risk-benchmark' ),
					__( 'Confirm malpractice procedures are up to date', 'ai-risk-benchmark' ),
				),
			),
			'low' => array(
				'summary' => __( 'Assessment controls are broadly aligned with JCQ expectations. Maintain awareness of guidance updates and communicate any changes to staff and pupils promptly.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Schedule an annual JCQ alignment check', 'ai-risk-benchmark' ),
					__( 'Confirm all new staff are briefed on current AI assessment rules', 'ai-risk-benchmark' ),
				),
			),
		),
		'governance' => array(
			'critical' => array(
				'summary' => __( 'No consistent policy, AI lead or oversight process exists. Staff and pupils have no clear standard for AI use and the school has no documented position on AI governance.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Appoint an AI lead from your SLT', 'ai-risk-benchmark' ),
					__( 'Begin drafting a school AI policy using the DfE framework', 'ai-risk-benchmark' ),
					__( 'Set a date for your first formal AI governance review', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'Some governance structures are in place but they are not consistently applied. Policy may exist on paper without reaching staff practice.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Review whether your current AI policy is understood and followed by staff', 'ai-risk-benchmark' ),
					__( 'Assign ownership of AI governance to a named SLT member', 'ai-risk-benchmark' ),
					__( 'Set a calendar date for annual AI policy review', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Governance foundations are in place. Focus on embedding practice and ensuring all staff know what the policy requires of them.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Communicate your AI policy clearly to all staff', 'ai-risk-benchmark' ),
					__( 'Include AI governance in your next staff briefing cycle', 'ai-risk-benchmark' ),
					__( 'Confirm governor awareness of your current AI position', 'ai-risk-benchmark' ),
				),
			),
		),
	),
	'priority_scenarios' => array(
		'privacy_critical' => array(
			'title'     => __( 'Complete your AI data protection review with your DPO', 'ai-risk-benchmark' ),
			'rationale' => __( 'Your data protection score is {pct}%. Pupil data may be entering AI tools without a DPIA or approved tool list in place — this is your highest-priority legal and safeguarding risk right now.', 'ai-risk-benchmark' ),
		),
		'safeguarding_critical' => array(
			'title'     => __( 'Update your safeguarding policy to cover AI-enabled harm', 'ai-risk-benchmark' ),
			'rationale' => __( 'Your safeguarding score is {pct}%. DSL and pastoral teams may not be equipped to recognise or respond to deepfake or AI-enabled incidents — this needs to be addressed before your next KCSIE review.', 'ai-risk-benchmark' ),
		),
		'privacy_safeguarding_critical' => array(
			'title'     => __( 'Meet with your DPO and DSL this week', 'ai-risk-benchmark' ),
			'rationale' => __( 'Both data protection and safeguarding are at critical level. These are your two highest-priority legal and pastoral risks and should be addressed together as a matter of urgency.', 'ai-risk-benchmark' ),
		),
		'governance_critical' => array(
			'title'     => __( 'Appoint an AI lead and begin drafting your school AI policy', 'ai-risk-benchmark' ),
			'rationale' => __( 'Your governance score is {pct}%. Without an AI lead or policy in place, there is no consistent standard for how staff and pupils use AI across the school.', 'ai-risk-benchmark' ),
		),
		'human_oversight_high' => array(
			'title'     => __( 'Introduce a whole-school AI verification standard', 'ai-risk-benchmark' ),
			'rationale' => __( 'Staff are using AI regularly but there is no consistent check before outputs reach pupils. Some departments are likely passing AI-generated content directly into lessons without review.', 'ai-risk-benchmark' ),
		),
		'assessment_high' => array(
			'title'     => __( 'Conduct a JCQ-aligned assessment review before the next exam season', 'ai-risk-benchmark' ),
			'rationale' => __( 'Your assessment controls may not yet reflect current JCQ and Ofqual expectations. Inconsistent rules across departments put your school at risk of malpractice incidents.', 'ai-risk-benchmark' ),
		),
		'ai_literacy_high' => array(
			'title'     => __( 'Schedule an AI awareness session for all staff this term', 'ai-risk-benchmark' ),
			'rationale' => __( 'Staff confidence with AI tools is low. Without a shared understanding of how AI works and where it fails, verification and oversight practices are unlikely to improve on their own.', 'ai-risk-benchmark' ),
		),
		'all_strong' => array(
			'title'     => __( 'Share your results with governors and schedule your next annual review', 'ai-risk-benchmark' ),
			'rationale' => __( 'Your school is performing well across all domains. The next step is embedding AI governance into your annual review cycle and reporting to governors.', 'ai-risk-benchmark' ),
		),
		'default' => array(
			'title'     => __( 'Strengthen whole-school governance and staff verification practices', 'ai-risk-benchmark' ),
			'rationale' => __( 'This is your highest-priority area based on your benchmark scores — address it before AI use scales across the school.', 'ai-risk-benchmark' ),
		),
	),
	'rollout_tiers' => array(
		'none' => array(
			'intro' => __( 'You\'ve completed the leader audit. Share the benchmark with your staff, students and parents to unlock your whole-school picture — including teacher AI dependency, student verification skills and parent awareness gaps.', 'ai-risk-benchmark' ),
			'note'  => __( 'Unlocks after {threshold} responses from your school community.', 'ai-risk-benchmark' ),
		),
		'early' => array(
			'intro' => __( 'You\'re getting started. Keep sharing — your whole-school data unlocks at 20 responses and will show you where the real gaps are across all groups.', 'ai-risk-benchmark' ),
			'note'  => __( '{total} responses so far. {remaining} needed to unlock.', 'ai-risk-benchmark' ),
		),
		'nearly' => array(
			'intro' => __( 'Nearly there. A few more responses and your aggregated school data will be ready — including a full risk heatmap across all roles.', 'ai-risk-benchmark' ),
			'note'  => __( '{total} responses so far. Almost at {threshold}.', 'ai-risk-benchmark' ),
		),
		'unlocked' => array(
			'intro' => __( 'Your whole-school benchmark is now live. You have early data across your community — encourage more responses for a more representative picture.', 'ai-risk-benchmark' ),
			'note'  => '',
		),
		'representative' => array(
			'intro' => __( 'Your whole-school benchmark is statistically representative. Use this data in your governor report and annual AI review.', 'ai-risk-benchmark' ),
			'note'  => '',
		),
	),
	'readiness_cta_tiers' => array(
		'emerging' => array(
			'key'          => 'governance_review',
			'title'        => __( 'Governance review & readiness consultation', 'ai-risk-benchmark' ),
			'body'         => __( 'A structured review with an AI readiness specialist — covering where your risks sit, what to prioritise and what your school needs to move from Emerging to Established.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'DfE alignment review', 'ai-risk-benchmark' ),
				__( 'Policy recommendations', 'ai-risk-benchmark' ),
				__( 'Risk heat map walkthrough', 'ai-risk-benchmark' ),
				__( 'Staff CPD planning', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Request governance review', 'ai-risk-benchmark' ),
		),
		'developing' => array(
			'key'          => 'policy_session',
			'title'        => __( 'School AI policy & governance build', 'ai-risk-benchmark' ),
			'body'         => __( 'You have awareness but need structured governance. This session will produce a draft school AI policy, an approved tool list and a 90-day action plan for your SLT.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'AI policy draft', 'ai-risk-benchmark' ),
				__( 'Approved tool list', 'ai-risk-benchmark' ),
				__( '90-day SLT action plan', 'ai-risk-benchmark' ),
				__( 'Governor briefing template', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Book a policy session', 'ai-risk-benchmark' ),
		),
		'established' => array(
			'key'          => 'whole_school_cpd',
			'title'        => __( 'Whole-school CPD programme', 'ai-risk-benchmark' ),
			'body'         => __( 'Your governance is in place — now build staff confidence and consistency. A targeted CPD programme based on your highest-risk domains across the whole school.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'Domain-targeted CPD sessions', 'ai-risk-benchmark' ),
				__( 'Verify Before You Trust staff training', 'ai-risk-benchmark' ),
				__( 'Assessment integrity workshop', 'ai-risk-benchmark' ),
				__( 'AI literacy programme', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Book whole-school CPD', 'ai-risk-benchmark' ),
		),
		'strong' => array(
			'key'          => 'ai_champion',
			'title'        => __( 'AI Champion programme', 'ai-risk-benchmark' ),
			'body'         => __( 'Your school is ready to build internal expertise. The AI Champion Programme develops teacher leads who can sustain AI readiness from within.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'AI Champion cohort training', 'ai-risk-benchmark' ),
				__( 'Internal audit toolkit', 'ai-risk-benchmark' ),
				__( 'Governor reporting template', 'ai-risk-benchmark' ),
				__( 'Annual review framework', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Start the champion programme', 'ai-risk-benchmark' ),
		),
		'leading' => array(
			'key'          => 'national_benchmark',
			'title'        => __( 'National benchmark & case study', 'ai-risk-benchmark' ),
			'body'         => __( 'Your school is operating at the highest level. Contribute to the UK School AI Benchmark 2027 and share your approach with the wider sector.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'National benchmark submission', 'ai-risk-benchmark' ),
				__( 'Case study feature', 'ai-risk-benchmark' ),
				__( 'Sector leadership profile', 'ai-risk-benchmark' ),
				__( 'Annual Leaders Cohort invitation', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Join the leading schools cohort', 'ai-risk-benchmark' ),
		),
	),
);
