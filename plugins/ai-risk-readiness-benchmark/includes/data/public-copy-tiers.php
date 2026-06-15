<?php
/**
 * Tiered public benchmark results copy.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'profile_title' => __( 'Your AI safety profile', 'ai-risk-benchmark' ),
	'copy_tiers'    => array(
		'readiness' => array(
			'at_risk' => array(
				'signal'      => __( 'Action needed', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'Your AI habits carry significant risk across several areas. Start with the priority focus areas below — small changes to how you verify, protect data and use AI at work can make a big difference.', 'ai-risk-benchmark' ),
			),
			'take_care' => array(
				'signal'      => __( 'Take care', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You use AI regularly but some of your habits could leave you exposed — particularly around data privacy, scams and workplace use. Small changes in how you use AI will make a big difference.', 'ai-risk-benchmark' ),
			),
			'aware' => array(
				'signal'      => __( 'Aware', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You show reasonable AI awareness with room to strengthen verification and privacy habits as tools evolve.', 'ai-risk-benchmark' ),
			),
			'confident' => array(
				'signal'      => __( 'Confident', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You demonstrate responsible AI use in most areas. Keep verifying outputs and protecting personal data as AI becomes more embedded in daily life.', 'ai-risk-benchmark' ),
			),
			'advanced' => array(
				'signal'      => __( 'Advanced', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You use AI critically, protect your data and understand its limits. Share what works with colleagues, family and friends.', 'ai-risk-benchmark' ),
			),
		),
	),
	'hero_metric_label'               => __( 'Overall AI safety score', 'ai-risk-benchmark' ),
	'domains_section_heading'         => __( 'Your scores — 5 domains', 'ai-risk-benchmark' ),
	'domains_section_heading_short'   => __( '5 domains', 'ai-risk-benchmark' ),
	'strengths_heading'               => __( 'What you\'re doing well', 'ai-risk-benchmark' ),
	'focus_section_heading'             => __( 'Priority focus areas', 'ai-risk-benchmark' ),
	'focus_section_heading_short'       => __( 'Priority focus', 'ai-risk-benchmark' ),
	'share_section_kicker'              => __( 'Share your results', 'ai-risk-benchmark' ),
	'share_section_title'               => __( 'Most people don\'t know how they really use AI', 'ai-risk-benchmark' ),
	'share_section_body'                => __( 'You just found out. Share your score and help people around you understand their own AI habits — the benchmark takes 3 minutes and is free for everyone.', 'ai-risk-benchmark' ),
	'share_preview_label'               => __( 'Your shareable result', 'ai-risk-benchmark' ),
	'share_cta_primary'                 => __( 'Share on social', 'ai-risk-benchmark' ),
	'share_cta_retake'                  => __( 'Retake the benchmark', 'ai-risk-benchmark' ),
	'share_cta_guide'                   => __( 'Get your safety guide', 'ai-risk-benchmark' ),
	'help_support_heading'              => __( 'Further reading', 'ai-risk-benchmark' ),
	'help_support_heading_short'        => __( 'Read more', 'ai-risk-benchmark' ),
	'strength_tiers' => array(
		'human_oversight' => array(
			'min'    => 65,
			'label'  => __( 'You question what AI tells you', 'ai-risk-benchmark' ),
			'detail' => __( 'Verification {pct}% — you don\'t just accept AI outputs at face value, which is the most important habit to have.', 'ai-risk-benchmark' ),
		),
		'ai_literacy' => array(
			'min'    => 60,
			'label'  => __( 'You\'re aware AI can be wrong', 'ai-risk-benchmark' ),
			'detail' => __( 'You know AI tools make mistakes and factor that into how you use them — that puts you ahead of most people.', 'ai-risk-benchmark' ),
		),
		'privacy_awareness' => array(
			'min'    => 70,
			'label'  => __( 'You protect personal data', 'ai-risk-benchmark' ),
			'detail' => __( 'Data & privacy {pct}% — you treat AI tools as shared spaces and avoid sharing sensitive information.', 'ai-risk-benchmark' ),
		),
	),
	'focus_slug_map' => array(
		'data_privacy'     => 'data_privacy',
		'verification'     => 'deepfake_awareness',
		'workplace_ai'     => 'workplace_ai',
		'personal_ai_use'  => 'personal_ai_use',
		'emotional_social' => 'emotional_social',
	),
	'focus_tiers' => array(
		'data_privacy' => array(
			'critical' => array(
				'severity'          => 'risk',
				'summary'           => __( 'Personal information — yours and other people\'s — is entering public AI tools. Most people don\'t realise these conversations are stored, may be reviewed by staff, and can be used to train future AI models.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What this means for you', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Your name, address, health details or finances may be stored on third-party servers', 'ai-risk-benchmark' ),
					__( 'Information about friends, family or colleagues shared without their knowledge or consent', 'ai-risk-benchmark' ),
					__( 'No way to delete what you\'ve already shared once it\'s been processed', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Treat AI tools like a public space — never share anything you wouldn\'t say out loud', 'ai-risk-benchmark' ),
					__( 'Turn off chat history in your AI tool settings where possible', 'ai-risk-benchmark' ),
					__( 'Never share another person\'s details with AI without their knowledge', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'attention',
				'summary'   => __( 'Some personal or work-related information may be entering AI tools without enough caution. Tighten what you share before habits become hard to change.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Review what you have shared with AI tools in the past month', 'ai-risk-benchmark' ),
					__( 'Use separate accounts or privacy settings where your tool allows it', 'ai-risk-benchmark' ),
				),
			),
		),
		'deepfake_awareness' => array(
			'critical' => array(
				'severity'  => 'risk',
				'summary'   => __( 'AI is now being used to create convincing fake voices, videos and messages — including scam calls that sound like family members, fake invoices and fraudulent job offers. Most people cannot reliably spot them.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'If you receive an unexpected call, message or request that feels urgent — pause and verify independently', 'ai-risk-benchmark' ),
					__( 'Agree a safe word with close family members to verify identity in suspicious calls', 'ai-risk-benchmark' ),
					__( 'Report AI-generated scam content to Action Fraud (UK) or your national equivalent', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'attention',
				'summary'   => __( 'Your awareness of AI-generated fakes and scams could be stronger. Build a habit of pausing before you trust urgent messages or unfamiliar voices.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Search for recent examples of AI voice scams to see how convincing they can be', 'ai-risk-benchmark' ),
					__( 'Verify unexpected requests through a separate channel — not the one that contacted you', 'ai-risk-benchmark' ),
				),
			),
		),
		'workplace_ai' => array(
			'critical' => array(
				'severity'  => 'risk',
				'summary'   => __( 'Work data — including client information and confidential documents — may be entering personal AI tools without your employer\'s knowledge. This creates legal, reputational and professional risk.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Find out if your employer has an AI policy and follow it', 'ai-risk-benchmark' ),
					__( 'Never paste confidential client or company data into a public AI tool', 'ai-risk-benchmark' ),
					__( 'If AI contributed significantly to your work output, consider disclosing it', 'ai-risk-benchmark' ),
				),
			),
			'moderate' => array(
				'severity'  => 'attention',
				'summary'   => __( 'Your workplace AI habits need attention. Clear boundaries around confidential data and disclosure protect you and your employer.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Ask your manager or HR whether AI use is permitted for your role', 'ai-risk-benchmark' ),
					__( 'Use employer-approved tools for work tasks where they exist', 'ai-risk-benchmark' ),
				),
			),
		),
		'personal_ai_use' => array(
			'moderate' => array(
				'severity'  => 'attention',
				'summary'   => __( 'Build habits of verifying before you act, and notice when you reach for AI before thinking for yourself.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Try completing one task without AI before asking for help', 'ai-risk-benchmark' ),
					__( 'Notice which tasks you automatically hand to AI — are they the right ones?', 'ai-risk-benchmark' ),
				),
			),
		),
		'emotional_social' => array(
			'moderate' => array(
				'severity'  => 'attention',
				'summary'   => __( 'AI is a tool, not a counsellor or companion. Be cautious using it for personal decisions, relationships or as your main news source.', 'ai-risk-benchmark' ),
				'actions'   => array(
					__( 'Talk to a real person for important personal or health decisions', 'ai-risk-benchmark' ),
					__( 'Cross-check news or advice from AI with trusted human sources', 'ai-risk-benchmark' ),
				),
			),
		),
	),
	'focus_topics' => array(
		array(
			'slug'  => 'data_privacy',
			'label' => __( 'Data & privacy habits', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'deepfake_awareness',
			'label' => __( 'Deepfake & scam awareness', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'workplace_ai',
			'label' => __( 'Workplace AI use', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'personal_ai_use',
			'label' => __( 'Personal AI use', 'ai-risk-benchmark' ),
		),
		array(
			'slug'  => 'emotional_social',
			'label' => __( 'Emotional & social use', 'ai-risk-benchmark' ),
		),
	),
);
