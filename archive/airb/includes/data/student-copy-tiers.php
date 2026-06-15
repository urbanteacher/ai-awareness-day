<?php
/**
 * Tiered student results copy — loaded into student_result_config.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'copy_tiers' => array(
		'readiness' => array(
			'beginning' => array(
				'signal'      => __( 'Focus here first', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'There is lots to learn about using AI well. Start with the guidance below — it is designed to help, not judge.', 'ai-risk-benchmark' ),
			),
			'developing' => array(
				'signal'      => __( 'Keep building', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You are learning how to use AI — the tips below will help you build confidence, independence and safer habits.', 'ai-risk-benchmark' ),
			),
			'emerging' => array(
				'signal'      => __( 'Growing awareness', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You show moderate reliance on AI and reasonable awareness. Small changes to how you use AI could strengthen your learning even further.', 'ai-risk-benchmark' ),
			),
			'confident' => array(
				'signal'      => __( 'Good awareness', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You have good AI awareness and healthy habits in most areas. Focus on the skills below where you can still grow.', 'ai-risk-benchmark' ),
			),
			'advanced' => array(
				'signal'      => __( 'Strong skills', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are building strong AI learning habits — keep verifying, thinking independently and protecting your privacy.', 'ai-risk-benchmark' ),
			),
		),
		'bias' => array(
			'critical' => array(
				'signal'      => __( 'Important gap', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'AI tools can produce unfair or stereotypical answers about groups of people. Learning to spot this is an important part of using AI safely.', 'ai-risk-benchmark' ),
			),
			'high' => array(
				'signal'      => __( 'Needs attention', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You may not yet check whether AI answers are fair to everyone. This is a skill worth building — unfair AI outputs can harm people.', 'ai-risk-benchmark' ),
			),
			'moderate' => array(
				'signal'      => __( 'Building awareness', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You have some awareness of AI bias but do not always check for unfair answers. Keep practising — it gets easier.', 'ai-risk-benchmark' ),
			),
			'low' => array(
				'signal'      => __( 'Good awareness', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You understand that AI can be unfair and think about this when you use it. Keep checking as you encounter new tools and topics.', 'ai-risk-benchmark' ),
			),
		),
	),
	'metric_labels' => array(
		'bias' => __( 'Bias & fairness awareness', 'ai-risk-benchmark' ),
	),
	'bias_health_title' => __( 'Bias & fairness awareness', 'ai-risk-benchmark' ),
	'bias_health_subtitle' => __( 'Fairness · protected characteristics · online safety', 'ai-risk-benchmark' ),
	'bias_health_callout_threshold' => 50,
	'bias_health_callout' => __( 'AI tools can produce unfair or stereotypical answers about groups of people. Learning to spot this helps you use AI more safely and fairly.', 'ai-risk-benchmark' ),
	'hero_metric_label' => __( 'Overall AI skills level', 'ai-risk-benchmark' ),
	'skills_section_heading'       => __( 'Your learning profile — core skills', 'ai-risk-benchmark' ),
	'skills_section_heading_short' => __( 'Core skills', 'ai-risk-benchmark' ),
	'bias_section_heading'         => __( 'Bias & fairness awareness', 'ai-risk-benchmark' ),
	'bias_section_heading_short'   => __( 'Bias & fairness', 'ai-risk-benchmark' ),
	'focus_section_heading'       => __( 'Where to improve — areas to focus on', 'ai-risk-benchmark' ),
	'focus_section_heading_short'   => __( 'Where to improve', 'ai-risk-benchmark' ),
	'resources_section_heading'       => __( 'Study resources', 'ai-risk-benchmark' ),
	'resources_section_heading_short' => __( 'Study resources', 'ai-risk-benchmark' ),
	'resources_section_intro' => __( 'Based on your results, these are the areas most worth exploring next.', 'ai-risk-benchmark' ),
	'help_support_heading'       => __( 'Further reading and tips to guide you', 'ai-risk-benchmark' ),
	'help_support_heading_short' => __( 'Read more & tips', 'ai-risk-benchmark' ),
	'peer_comparison_label'       => __( 'How you compare to other students', 'ai-risk-benchmark' ),
	'peer_comparison_label_short' => __( 'How you compare', 'ai-risk-benchmark' ),
	'peer_you_label'              => __( 'You', 'ai-risk-benchmark' ),
	'peer_gap_below_average'      => __( '{n} points below average', 'ai-risk-benchmark' ),
	'peer_gap_above_average'      => __( '{n} points above average', 'ai-risk-benchmark' ),
	'peer_gap_at_average'         => __( 'In line with average', 'ai-risk-benchmark' ),
	'peer_gap_below_top'          => __( '{n} points below top quartile', 'ai-risk-benchmark' ),
	'peer_gap_below_top_short'    => __( '{n} below top quartile', 'ai-risk-benchmark' ),
	'peer_gap_above_top'          => __( '{n} points above top quartile', 'ai-risk-benchmark' ),
	'peer_gap_at_top'             => __( 'In line with top quartile', 'ai-risk-benchmark' ),
	'share_section_kicker'  => __( 'Share your results', 'ai-risk-benchmark' ),
	'share_section_title'   => __( 'Help your school understand how students use AI', 'ai-risk-benchmark' ),
	'share_section_body'    => __( 'When enough students at your school complete the benchmark, your teachers and leaders can see how AI is being used — and get you better support. Add your school name to contribute.', 'ai-risk-benchmark' ),
	'share_count_label'     => __( 'Student responses', 'ai-risk-benchmark' ),
	'share_unlock_label'    => __( 'Unlocks when ready', 'ai-risk-benchmark' ),
	'share_unlock_value'    => __( 'Student AI report', 'ai-risk-benchmark' ),
	'share_cta_primary'     => __( 'Add your school', 'ai-risk-benchmark' ),
	'share_cta_secondary'   => __( 'Retake the benchmark', 'ai-risk-benchmark' ),
	'retake_at_risk_threshold' => 35,
	'retake_at_risk_heading' => __( 'Needs attention — build your skills first', 'ai-risk-benchmark' ),
	'retake_at_risk_body'    => __( 'You scored below 35%, which puts you in the at-risk band. Explore the articles and study resources above before you retake — they are chosen to help you improve the areas that matter most.', 'ai-risk-benchmark' ),
	'retake_body_default'    => __( 'When you are ready, retake the benchmark to see how your AI skills have improved.', 'ai-risk-benchmark' ),
	'strength_tiers' => array(
		'verification_skills' => array(
			'min'    => 76,
			'label'  => __( 'You check AI answers before trusting them', 'ai-risk-benchmark' ),
			'detail' => __( 'Verification skills {pct}% — you question AI outputs and look for mistakes rather than accepting them at face value.', 'ai-risk-benchmark' ),
		),
		'ai_literacy' => array(
			'min'    => 76,
			'label'  => __( 'You understand what AI can and can\'t do', 'ai-risk-benchmark' ),
			'detail' => __( 'AI literacy {pct}% — you know AI tools make mistakes, can be biased and don\'t actually understand what they\'re producing.', 'ai-risk-benchmark' ),
		),
		'independent_thinking' => array(
			'min'    => 76,
			'label'  => __( 'You try tasks yourself before using AI', 'ai-risk-benchmark' ),
			'detail' => __( 'Independent thinking {pct}% — you give yourself a real chance to learn before reaching for AI.', 'ai-risk-benchmark' ),
		),
		'privacy_awareness' => array(
			'min'    => 76,
			'label'  => __( 'You think about what is safe to share with AI', 'ai-risk-benchmark' ),
			'detail' => __( 'Privacy awareness {pct}% — you keep personal details out of public AI tools.', 'ai-risk-benchmark' ),
		),
		'bias_fairness' => array(
			'min'    => 76,
			'label'  => __( 'You think about whether AI answers are fair', 'ai-risk-benchmark' ),
			'detail' => __( 'Bias & fairness awareness {pct}% — you know AI can be unfair and check for stereotypical or harmful answers.', 'ai-risk-benchmark' ),
		),
	),
	'focus_tiers' => array(
		'think_first' => array(
			'emerging' => array(
				'summary' => __( 'You may be using AI before giving yourself a real chance to think. The best students use AI to check and improve their thinking — not to replace it. Your ideas are the part AI can\'t do for you.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Your learning challenge', 'ai-risk-benchmark' ),
				'challenge_body'    => __( 'Before you open an AI tool, spend five minutes writing down your own answer or ideas first. Use AI to challenge your thinking — not to give you something to copy.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Try the task yourself first — even if your answer feels incomplete', 'ai-risk-benchmark' ),
					__( 'Use AI to check or improve what you wrote, not to write it for you', 'ai-risk-benchmark' ),
					__( 'Ask your teacher to explain anything you don\'t understand rather than asking AI', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'summary' => __( 'You sometimes reach for AI before attempting work yourself. Build a habit of trying first — then use AI to strengthen your thinking.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Your learning challenge', 'ai-risk-benchmark' ),
				'challenge_body'    => __( 'Complete your first attempt without AI, then compare your answer with what AI suggests.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Try the task yourself first — even if your answer feels incomplete', 'ai-risk-benchmark' ),
					__( 'Use AI to check or improve what you wrote, not to write it for you', 'ai-risk-benchmark' ),
				),
			),
		),
		'privacy' => array(
			'emerging' => array(
				'summary' => __( 'You may not always think about what information is safe to share with AI tools. Anything you type into a public AI tool could be stored and used by the company that runs it.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What to remember', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Never share your full name, school, address or photos', 'ai-risk-benchmark' ),
					__( 'Never share information about other people without their permission', 'ai-risk-benchmark' ),
					__( 'Treat AI tools like a public space — don\'t say anything you wouldn\'t say out loud', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Keep personal details out of AI tools — use "a student" not your name', 'ai-risk-benchmark' ),
					__( 'Only use AI tools your school has approved', 'ai-risk-benchmark' ),
					__( 'If something feels wrong or uncomfortable, tell a teacher', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'summary' => __( 'You have some privacy awareness but may still share more than you should. Be careful about personal details in public AI tools.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What to remember', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Never share your full name, school, address or photos', 'ai-risk-benchmark' ),
					__( 'Never share information about other people without their permission', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Keep personal details out of AI tools — use "a student" not your name', 'ai-risk-benchmark' ),
					__( 'Only use AI tools your school has approved', 'ai-risk-benchmark' ),
				),
			),
		),
		'verify' => array(
			'emerging' => array(
				'summary' => __( 'Checking AI answers before you trust them is a skill worth building. AI can sound confident even when it is wrong.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Your learning challenge', 'ai-risk-benchmark' ),
				'challenge_body'    => __( 'Pick one AI answer this week and verify it with a textbook, teacher or trusted website before using it.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Check AI answers against trusted sources', 'ai-risk-benchmark' ),
					__( 'Explain the answer in your own words', 'ai-risk-benchmark' ),
					__( 'If you cannot explain it yourself, you may not fully understand it yet', 'ai-risk-benchmark' ),
				),
			),
		),
		'fairness' => array(
			'emerging' => array(
				'summary' => __( 'AI tools can produce answers that are unfair or stereotypical about groups of people. Learning to spot this helps you use AI more safely — and helps protect others.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What to watch for', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Stereotypes about gender, ethnicity, religion or disability', 'ai-risk-benchmark' ),
					__( 'Answers that treat one group as "better" or "worse"', 'ai-risk-benchmark' ),
					__( 'Content that could upset or exclude someone', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Ask: "Could this answer upset or exclude someone?"', 'ai-risk-benchmark' ),
					__( 'Compare AI answers with what you learn in school about fairness and respect', 'ai-risk-benchmark' ),
					__( 'Tell a teacher if you see AI content that seems discriminatory', 'ai-risk-benchmark' ),
				),
			),
			'developing' => array(
				'summary' => __( 'You are starting to think about fairness in AI answers. Keep practising — checking for bias becomes a habit over time.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'Your learning challenge', 'ai-risk-benchmark' ),
				'challenge_body'    => __( 'Next time you use AI, pause and ask whether the answer could be unfair to any group of people before you use it.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Question answers that sound like stereotypes', 'ai-risk-benchmark' ),
					__( 'Use trusted sources to check facts about people and cultures', 'ai-risk-benchmark' ),
				),
			),
		),
	),
);
