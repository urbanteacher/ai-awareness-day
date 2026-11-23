<?php
/**
 * Tiered teacher results copy — loaded into teacher_result_config.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'copy_tiers' => array(
		'readiness' => array(
			'emerging' => array(
				'signal'      => __( 'Action needed', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Your AI practice has significant gaps. Focus on building safer habits before expanding your use of AI tools in the classroom.', 'ai-risk-benchmark' ),
			),
			'developing' => array(
				'signal'      => __( 'Building good habits', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You have foundational awareness but some areas need strengthening. Targeted improvement in your weakest domains will make a meaningful difference.', 'ai-risk-benchmark' ),
			),
			'established' => array(
				'signal'      => __( 'On the right track', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You are demonstrating responsible AI practice in most areas. Focus now on the domains where your habits are still inconsistent.', 'ai-risk-benchmark' ),
			),
			'strong' => array(
				'signal'      => __( 'Strong position', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are demonstrating good AI governance behaviours — sustain consistency as tools evolve.', 'ai-risk-benchmark' ),
			),
			'leading' => array(
				'signal'      => __( 'Sector benchmark', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are demonstrating exemplary AI practice. You are well placed to support colleagues and contribute to school AI policy.', 'ai-risk-benchmark' ),
			),
		),
		'risk' => array(
			'critical' => array(
				'signal'      => __( 'Critical exposure', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Your AI use carries significant risk across multiple domains. Review your practice in each priority area below before using AI tools with pupils.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'High exposure', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Your exposure risk is elevated. Inconsistent verification or data habits are likely contributing — focus on your two weakest domains first.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Moderate exposure', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Some areas of your practice carry risk. Targeted improvement in your priority domains will reduce your overall exposure meaningfully.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Lower exposure', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Exposure risk is comparatively lower — keep verifying as AI use grows.', 'ai-risk-benchmark' ),
			),
			'minimal' => array(
				'signal'      => __( 'Minimal exposure', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Your AI practice is well governed across all domains. Maintain your current habits and stay current as tools evolve.', 'ai-risk-benchmark' ),
			),
		),
		'bias' => array(
			'critical' => array(
				'signal'      => __( 'Urgent equality risk', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'You have not assessed whether AI outputs could be unfair or discriminatory before they reach pupils. This is a safeguarding and equality duty gap.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'Equality duty risk', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Your awareness of AI bias is limited and you do not consistently check outputs for unfairness before using them with pupils.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Building awareness', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You understand that AI can be biased but do not yet check every output routinely. Build bias spot-checks into your verification habit.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Strong awareness', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You understand AI bias risks and check outputs for unfairness before they reach pupils. Keep this current as tools and classroom use evolve.', 'ai-risk-benchmark' ),
			),
		),
		'dependency' => array(
			'high' => array(
				'signal'      => __( 'High reliance', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'You are frequently reaching for AI before attempting tasks independently. This limits your professional judgement and pupils may be modelling the same habit.', 'ai-risk-benchmark' ),
			),
			'moderate_high' => array(
				'signal'      => __( 'Moderate-high reliance', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'AI is becoming a first resort in several areas. Build habits of independent thinking before reaching for AI tools.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Moderate reliance', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'Build habits of independent work before reaching for AI tools.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Low reliance', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are generally attempting tasks independently before using AI. Keep this habit as AI tools become more capable and accessible.', 'ai-risk-benchmark' ),
			),
			'minimal' => array(
				'signal'      => __( 'Minimal reliance', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You demonstrate strong independent practice. AI is used purposefully and as a complement to your own judgement, not a replacement.', 'ai-risk-benchmark' ),
			),
		),
		'oversight' => array(
			'critical' => array(
				'signal'      => __( 'Critical reliance', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'You are using most AI output without meaningful review. Content reaching pupils may contain errors, bias or inappropriate material that has not been checked.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'High reliance', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'A significant portion of AI output is going unreviewed. Inconsistent checking means errors are likely reaching pupils or colleagues.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Moderate oversight', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You are reviewing most AI output but some content is going unchecked. Aim to review everything before it reaches pupils.', 'ai-risk-benchmark' ),
			),
			'strong' => array(
				'signal'      => __( 'Strong oversight', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'Share of AI output reviewed or changed before use. Below 26% signals reliance without meaningful human review.', 'ai-risk-benchmark' ),
			),
			'exemplary' => array(
				'signal'      => __( 'Exemplary oversight', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are reviewing and adapting almost all AI output before use. This is the standard all teachers should aim for — share your approach with colleagues.', 'ai-risk-benchmark' ),
			),
		),
	),
	'domain_descriptions' => array(
		'safe_adoption'          => __( 'Whether AI tools are assessed for appropriateness and safety before use with pupils or colleagues.', 'ai-risk-benchmark' ),
		'human_oversight'      => __( 'How consistently AI outputs are checked, cross-referenced and modified before reaching pupils.', 'ai-risk-benchmark' ),
		'ai_dependency'        => __( 'How often you attempt tasks without AI first, and your ability to work effectively without AI support.', 'ai-risk-benchmark' ),
		'privacy'              => __( 'Whether identifiable pupil data, SEND information or safeguarding details are kept out of public AI tools.', 'ai-risk-benchmark' ),
		'safeguarding'         => __( 'Awareness of AI bias, unfair outputs, deepfakes and synthetic harms — and how to respond.', 'ai-risk-benchmark' ),
		'assessment_integrity' => __( 'Understanding of JCQ and school rules around AI use in assessed work, and consistent application of those rules.', 'ai-risk-benchmark' ),
		'ai_literacy'          => __( 'Understanding of how AI tools work, where they fail, and what their outputs can and cannot be trusted for.', 'ai-risk-benchmark' ),
		'governance'           => __( 'Awareness of school AI policy, approved tools and your responsibilities as a staff member.', 'ai-risk-benchmark' ),
	),
	'strength_tiers' => array(
		'ai_literacy'          => array(
			'min'          => 76,
			'label'        => __( 'Strong understanding of AI limitations', 'ai-risk-benchmark' ),
			'detail'       => __( 'AI literacy {pct}% — you understand where AI tools fail and how to apply that knowledge in practice.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'AI literacy', 'ai-risk-benchmark' ),
		),
		'safe_adoption'        => array(
			'min'          => 100,
			'label'        => __( 'Thoughtful assessment of AI tools before use', 'ai-risk-benchmark' ),
			'detail'       => __( 'Safe adoption {pct}% — you assess tools for appropriateness before introducing them to pupils or colleagues.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Safe adoption', 'ai-risk-benchmark' ),
		),
		'human_oversight'      => array(
			'min'          => 76,
			'label'        => __( 'Strong human oversight of AI outputs', 'ai-risk-benchmark' ),
			'detail'       => __( 'Human Oversight Ratio {pct}% — almost all AI output is reviewed or modified before it reaches pupils.', 'ai-risk-benchmark' ),
			'metric'       => 'oversight',
		),
		'ai_dependency'        => array(
			'min'          => 76,
			'label'        => __( 'Consistent independent thinking before reaching for AI', 'ai-risk-benchmark' ),
			'detail'       => __( 'Independent practice {pct}% — you attempt tasks yourself before reaching for AI tools.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Independent practice', 'ai-risk-benchmark' ),
		),
		'privacy'              => array(
			'min'          => 76,
			'label'        => __( 'Strong awareness of pupil data risks in AI tools', 'ai-risk-benchmark' ),
			'detail'       => __( 'Privacy & data protection {pct}% — you keep identifiable pupil data out of public AI tools.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Privacy & data protection', 'ai-risk-benchmark' ),
		),
		'safeguarding'         => array(
			'min'          => 76,
			'label'        => __( 'Good awareness of AI-enabled safeguarding risks', 'ai-risk-benchmark' ),
			'detail'       => __( 'Safeguarding {pct}% — you understand AI-specific online safety risks and how to respond.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Safeguarding', 'ai-risk-benchmark' ),
		),
		'assessment_integrity' => array(
			'min'          => 100,
			'label'        => __( 'Clear understanding of AI rules in assessed work', 'ai-risk-benchmark' ),
			'detail'       => __( 'Assessment integrity {pct}% — pupils know what AI use is and is not permitted in your subject.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Assessment integrity', 'ai-risk-benchmark' ),
		),
		'governance'           => array(
			'min'          => 76,
			'label'        => __( 'Good awareness of school AI policy and your responsibilities', 'ai-risk-benchmark' ),
			'detail'       => __( 'Governance {pct}% — you follow school AI policy and approved tools.', 'ai-risk-benchmark' ),
			'domain_label' => __( 'Governance', 'ai-risk-benchmark' ),
		),
	),
	'oversight_metric_note' => __( 'Share of AI output reviewed or changed before use.', 'ai-risk-benchmark' ),
	'oversight_card_suffix' => __( 'you are well above this threshold.', 'ai-risk-benchmark' ),
	'peer_benchmark_title' => __( 'Teachers like you', 'ai-risk-benchmark' ),
	'peer_benchmark_fallback' => array(
		'average'      => 55,
		'top_quartile' => 79,
	),
	'peer_comparison_label'       => __( 'How you compare to other teachers', 'ai-risk-benchmark' ),
	'peer_comparison_label_short' => __( 'How you compare', 'ai-risk-benchmark' ),
	'peer_you_label'              => __( 'You', 'ai-risk-benchmark' ),
	'peer_average_label'          => __( 'National average', 'ai-risk-benchmark' ),
	'peer_average_label_short'    => __( 'Nat. avg', 'ai-risk-benchmark' ),
	'peer_gap_below_average'      => __( '{n} points below average', 'ai-risk-benchmark' ),
	'peer_gap_above_average'      => __( '{n} points above average', 'ai-risk-benchmark' ),
	'peer_gap_at_average'         => __( 'In line with average', 'ai-risk-benchmark' ),
	'peer_gap_below_top'          => __( '{n} points below top quartile', 'ai-risk-benchmark' ),
	'peer_gap_below_top_short'    => __( '{n} below top quartile', 'ai-risk-benchmark' ),
	'peer_gap_above_top'          => __( '{n} points above top quartile', 'ai-risk-benchmark' ),
	'peer_gap_at_top'             => __( 'In line with top quartile', 'ai-risk-benchmark' ),
	'rollout_locked_items' => array(
		array(
			'label'     => __( 'Staff dependency scores', 'ai-risk-benchmark' ),
			'count_key' => 'teacher',
		),
		array(
			'label'     => __( 'Oversight ratios by dept', 'ai-risk-benchmark' ),
			'count_key' => 'teacher',
		),
		array(
			'label'     => __( 'AI literacy across staff', 'ai-risk-benchmark' ),
			'count_key' => 'teacher',
		),
		array(
			'label'     => __( 'School risk heatmap', 'ai-risk-benchmark' ),
			'count_key' => 'teacher',
		),
	),
	'focus_actions_heading' => __( 'Actions', 'ai-risk-benchmark' ),
	'focus_tiers' => array(
		'ai_dependency' => array(
			'critical' => array(
				'summary' => __( 'You are frequently using AI before attempting tasks independently. This limits your professional judgement and may be modelling unhealthy AI habits for pupils.', 'ai-risk-benchmark' ),
				'likely_impact' => array(
					__( 'Core tasks being delegated to AI rather than attempted first', 'ai-risk-benchmark' ),
					__( 'Reduced ability to work confidently without AI support', 'ai-risk-benchmark' ),
					__( 'Risk of over-reliance spreading to pupil practice', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Attempt all planning and marking tasks independently before using AI', 'ai-risk-benchmark' ),
					__( 'Use AI to refine your first draft, not to produce it', 'ai-risk-benchmark' ),
					__( 'Set one AI-free day per week to rebuild independent habits', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'AI is becoming a first resort in some areas. Build habits of independent work before reaching for AI tools.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Practise core tasks without AI first — use it to refine, not to originate', 'ai-risk-benchmark' ),
					__( 'Notice which tasks you reach for AI on immediately and attempt those first independently', 'ai-risk-benchmark' ),
					__( 'Track your AI-free output over a fortnight', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Independent practice is reasonable but inconsistent. Some tasks are being handed to AI that would benefit from your own thinking first.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Practise core tasks without AI first — use it to refine, not to originate', 'ai-risk-benchmark' ),
					__( 'Identify the two or three tasks you most rely on AI for and build independence there first', 'ai-risk-benchmark' ),
				),
			),
		),
		'privacy' => array(
			'critical' => array(
				'summary' => __( 'Identifiable pupil data may be entering public AI tools. This is a UK GDPR risk and a safeguarding concern — pupil names, SEND information and pastoral notes must never enter a public AI system.', 'ai-risk-benchmark' ),
				'likely_impact' => array(
					__( 'Pupil names or details entered into ChatGPT or similar tools', 'ai-risk-benchmark' ),
					__( 'SEND or safeguarding information potentially stored on third-party servers', 'ai-risk-benchmark' ),
					__( 'No clear personal rule for what data is and is not safe to enter', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Stop entering any identifiable pupil data into public AI tools immediately', 'ai-risk-benchmark' ),
					__( 'Check your school\'s approved tool list and use only those', 'ai-risk-benchmark' ),
					__( 'Ask your DPO what data handling rules apply to AI tools you currently use', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'Data protection awareness is inconsistent. You may be entering some pupil information into AI tools without being certain it is safe to do so.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Never enter identifiable pupil, SEND or safeguarding data into public AI tools', 'ai-risk-benchmark' ),
					__( 'Review which tools you use against your school\'s approved list', 'ai-risk-benchmark' ),
					__( 'If unsure whether a tool is safe, ask your DPO before using it', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'You have reasonable data awareness but some uncertainty remains about what is and is not safe to enter into AI tools.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Never enter identifiable pupil, SEND or safeguarding data into public AI tools', 'ai-risk-benchmark' ),
					__( 'Confirm your school\'s approved tool list is up to date and follow it consistently', 'ai-risk-benchmark' ),
				),
			),
		),
		'human_oversight' => array(
			'critical' => array(
				'summary' => __( 'Most AI output is going directly to pupils or colleagues without meaningful review. Errors, bias or inappropriate content are likely reaching your classroom unchecked.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Review every piece of AI-generated content before sharing it with pupils', 'ai-risk-benchmark' ),
					__( 'Use the Verify Before You Trust framework as a daily habit', 'ai-risk-benchmark' ),
					__( 'Flag any AI errors you catch to colleagues so the whole department learns', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'A significant portion of AI output is going unreviewed. Inconsistent checking means errors are likely reaching pupils or colleagues.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Set a personal rule — nothing AI-generated goes to pupils without at least one read-through', 'ai-risk-benchmark' ),
					__( 'Cross-reference AI factual claims with a trusted source', 'ai-risk-benchmark' ),
					__( 'Modify AI output so it reflects your own voice and judgement', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'You are reviewing most AI output but some content is going unchecked. Aim to review everything before it reaches pupils.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Build a habit of reading all AI output before sharing', 'ai-risk-benchmark' ),
					__( 'Pay particular attention to factual claims and pupil-specific content', 'ai-risk-benchmark' ),
					__( 'Use the Verify Before You Trust checklist as a quick sense-check', 'ai-risk-benchmark' ),
				),
			),
		),
		'assessment_integrity' => array(
			'critical' => array(
				'summary' => __( 'AI rules in assessed work may not be clearly understood or consistently applied. Pupils may be unclear on what is and is not acceptable — which creates malpractice risk.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Read your school\'s current AI policy on assessed work', 'ai-risk-benchmark' ),
					__( 'Communicate AI boundaries clearly to all classes before the next assessment', 'ai-risk-benchmark' ),
					__( 'Check your practice against current JCQ guidance', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'Assessment boundaries are understood in principle but not always applied consistently across all year groups or subjects.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Clarify your school\'s AI rules for all assessed work you set', 'ai-risk-benchmark' ),
					__( 'Brief pupils explicitly on what AI use is and is not permitted in your subject', 'ai-risk-benchmark' ),
					__( 'Update your malpractice awareness in line with current JCQ guidance', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'Assessment integrity is reasonable but some inconsistency remains in how AI rules are communicated to pupils.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Make AI boundaries explicit at the start of every assessed task', 'ai-risk-benchmark' ),
					__( 'Ensure your practice is consistent with your department\'s approach', 'ai-risk-benchmark' ),
					__( 'Stay current with JCQ guidance updates', 'ai-risk-benchmark' ),
				),
			),
		),
		'safeguarding' => array(
			'critical' => array(
				'summary' => __( 'You may not be aware of AI-specific safeguarding risks such as deepfakes, AI-generated images of pupils or AI-enabled impersonation. These are now part of the online safety landscape all teachers need to understand.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Complete your school\'s online safety training if it covers AI scenarios', 'ai-risk-benchmark' ),
					__( 'Speak to your DSL about what to do if a pupil reports a deepfake incident', 'ai-risk-benchmark' ),
					__( 'Know the signs that a pupil may be experiencing AI-enabled harm', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'General online safety awareness is in place but AI-specific scenarios may not yet be fully understood.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Familiarise yourself with deepfake and AI impersonation risks', 'ai-risk-benchmark' ),
					__( 'Know your school\'s reporting process for AI-related safeguarding concerns', 'ai-risk-benchmark' ),
					__( 'Attend any available DSL-led briefing on AI and online safety', 'ai-risk-benchmark' ),
				),
			),
		),
		'ai_literacy' => array(
			'critical' => array(
				'summary' => __( 'Understanding of how AI tools work and where they fail appears limited. Without this foundation, safe and effective AI use is difficult to sustain.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Complete a short AI literacy course this term', 'ai-risk-benchmark' ),
					__( 'Read your school\'s AI guidance if available', 'ai-risk-benchmark' ),
					__( 'Explore how large language models work and where they commonly produce errors', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'You have basic AI awareness but gaps in understanding how tools fail, hallucinate or reflect bias.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Learn the common failure modes of AI tools — hallucination, bias, confident errors', 'ai-risk-benchmark' ),
					__( 'Apply this knowledge when verifying AI outputs', 'ai-risk-benchmark' ),
					__( 'Share what you learn with colleagues', 'ai-risk-benchmark' ),
				),
			),
		),
		'bias_equality' => array(
			'critical' => array(
				'summary' => __( 'You have not yet considered whether AI outputs could be unfair or discriminatory before they reach pupils. This is a safeguarding and equality duty risk.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Learn how AI can reflect bias against protected characteristics', 'ai-risk-benchmark' ),
					__( 'Add a bias spot-check to your AI verification routine', 'ai-risk-benchmark' ),
					__( 'Speak to your DSL or equality lead about AI fairness in the classroom', 'ai-risk-benchmark' ),
				),
			),
			'high' => array(
				'summary' => __( 'You have limited awareness of AI bias and do not consistently check outputs for unfairness before sharing them with pupils.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Before using AI content with pupils, ask who could be disadvantaged or stereotyped', 'ai-risk-benchmark' ),
					__( 'Review your school\'s equality and safeguarding guidance on AI', 'ai-risk-benchmark' ),
					__( 'Model checking AI outputs aloud so pupils see the habit', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'summary' => __( 'You understand AI can be biased but checks are not yet a consistent habit in your classroom practice.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Build a simple bias checklist into your verify-before-use routine', 'ai-risk-benchmark' ),
					__( 'Discuss fairness in AI outputs during a lesson or tutor time', 'ai-risk-benchmark' ),
				),
			),
		),
	),
	'bias_health_title' => __( 'Bias & equality readiness', 'ai-risk-benchmark' ),
	'bias_health_subtitle' => __( 'Fairness · protected characteristics · equality duty', 'ai-risk-benchmark' ),
	'bias_health_callout_threshold' => 50,
	'bias_health_callout' => __( 'You have not yet built a consistent habit of checking AI outputs for bias or unfairness before they reach pupils. This is both a safeguarding concern and an equality duty risk.', 'ai-risk-benchmark' ),
	'rollout_tiers' => array(
		'none' => array(
			'intro' => __( 'When enough colleagues at your school complete the teacher benchmark, your SLT unlocks aggregated school data — including dependency, oversight and literacy across staff.', 'ai-risk-benchmark' ),
			'note'  => __( 'Add your school name when you retake the audit to track progress toward a whole-school benchmark.', 'ai-risk-benchmark' ),
		),
		'early' => array(
			'intro' => __( 'Your school is getting started. Encourage more colleagues to complete the benchmark — aggregated data unlocks at 20 responses and gives your SLT a full picture of staff AI readiness.', 'ai-risk-benchmark' ),
			'note'  => __( '{total} colleague responses so far. {remaining} needed to unlock.', 'ai-risk-benchmark' ),
		),
		'nearly' => array(
			'intro' => __( 'Nearly there. A few more colleagues and your SLT will unlock the whole-school benchmark — including averaged dependency, oversight and literacy scores across your staff.', 'ai-risk-benchmark' ),
			'note'  => __( '{total} responses so far. Almost at {threshold}.', 'ai-risk-benchmark' ),
		),
		'unlocked' => array(
			'intro' => __( 'Your school\'s whole-school benchmark is now live. Your SLT can view aggregated staff data including AI dependency, human oversight ratios and domain readiness across all teachers.', 'ai-risk-benchmark' ),
			'note'  => '',
		),
	),
	'readiness_cta_tiers' => array(
		'emerging' => array(
			'key'          => 'teacher_awareness',
			'title'        => __( 'AI awareness session', 'ai-risk-benchmark' ),
			'body'         => __( 'Build the foundational knowledge and habits your practice needs. A focused session covering safe adoption, verification and data protection basics.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'Safe adoption fundamentals', 'ai-risk-benchmark' ),
				__( 'Verify Before You Trust introduction', 'ai-risk-benchmark' ),
				__( 'Data protection in AI tools', 'ai-risk-benchmark' ),
				__( 'Assessment integrity basics', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Book an awareness session', 'ai-risk-benchmark' ),
		),
		'developing' => array(
			'key'          => 'whole_school_cpd',
			'title'        => __( 'Targeted CPD on your priority domains', 'ai-risk-benchmark' ),
			'body'         => __( 'Your results show specific gaps. A targeted CPD session will address your two or three weakest domains with practical classroom strategies.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'Domain-specific workshop', 'ai-risk-benchmark' ),
				__( 'Practical verification techniques', 'ai-risk-benchmark' ),
				__( 'Classroom AI habit-building', 'ai-risk-benchmark' ),
				__( 'Assessment integrity update', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Book targeted CPD', 'ai-risk-benchmark' ),
		),
		'established' => array(
			'key'          => 'teacher_activity_day',
			'title'        => __( 'Classroom resources aligned to your results', 'ai-risk-benchmark' ),
			'body'         => __( 'Your practice is solid — supplement it with resources matched to your audit. Lesson activities, prompts and materials aligned to your priority domains.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'Domain-aligned classroom resources', 'ai-risk-benchmark' ),
				__( 'AI literacy activities for pupils', 'ai-risk-benchmark' ),
				__( 'Verification prompt cards', 'ai-risk-benchmark' ),
				__( 'Assessment guidance summary', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Request classroom resources', 'ai-risk-benchmark' ),
		),
		'strong' => array(
			'key'          => 'teacher_champion',
			'title'        => __( 'AI Champion pathway', 'ai-risk-benchmark' ),
			'body'         => __( 'Your results show strong AI awareness and responsible practice. You may be well placed to support colleagues, shape school policy and lead from within.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'Support colleagues adopting AI safely', 'ai-risk-benchmark' ),
				__( 'Share effective verification practices across your department', 'ai-risk-benchmark' ),
				__( 'Contribute to school AI policy development', 'ai-risk-benchmark' ),
				__( 'Participate in AI Awareness Day activities', 'ai-risk-benchmark' ),
			),
			'cta_text'            => __( 'Start the champion pathway', 'ai-risk-benchmark' ),
			'secondary_key'       => 'teacher_activity_day',
			'secondary_cta_text'  => __( 'Request classroom resources', 'ai-risk-benchmark' ),
			'pathway_kicker'      => __( 'Recommended pathway', 'ai-risk-benchmark' ),
		),
		'leading' => array(
			'key'          => 'leading_teachers',
			'title'        => __( 'Lead and share your practice', 'ai-risk-benchmark' ),
			'body'         => __( 'Your results place you among the strongest AI practitioners in the sector. Share your approach, support colleagues and contribute to the national benchmark.', 'ai-risk-benchmark' ),
			'deliverables' => array(
				__( 'National benchmark contribution', 'ai-risk-benchmark' ),
				__( 'Case study feature', 'ai-risk-benchmark' ),
				__( 'Champion cohort leadership', 'ai-risk-benchmark' ),
				__( 'Sector peer network', 'ai-risk-benchmark' ),
			),
			'cta_text'     => __( 'Join the leading teachers cohort', 'ai-risk-benchmark' ),
		),
	),
);
