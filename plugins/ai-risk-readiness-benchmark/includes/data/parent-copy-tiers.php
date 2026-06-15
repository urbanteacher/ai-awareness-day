<?php
/**
 * Tiered parent results copy — loaded into parent_result_config.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'profile_title' => __( 'Your home AI picture', 'ai-risk-benchmark' ),
	'copy_tiers'    => array(
		'readiness' => array(
			'just_starting' => array(
				'signal'      => __( 'Just starting', 'ai-risk-benchmark' ),
				'tone'        => 'urgent',
				'consequence' => __( 'There are important gaps in your awareness of how children use AI at home. Start with the guidance below and talk openly with your child about their AI use.', 'ai-risk-benchmark' ),
			),
			'developing' => array(
				'signal'      => __( 'Building awareness', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You have a reasonable awareness of how your child uses AI but some important areas need attention at home — particularly homework boundaries and online safety risks.', 'ai-risk-benchmark' ),
			),
			'aware' => array(
				'signal'      => __( 'Growing awareness', 'ai-risk-benchmark' ),
				'tone'        => 'warning',
				'consequence' => __( 'You have some awareness of AI risks, but there are still areas where more guidance would help you support your child safely.', 'ai-risk-benchmark' ),
			),
			'confident' => array(
				'signal'      => __( 'Confident', 'ai-risk-benchmark' ),
				'tone'        => 'neutral',
				'consequence' => __( 'You have a solid foundation for guiding your child\'s AI use. Keep conversations going and revisit the focus areas below as tools and risks evolve.', 'ai-risk-benchmark' ),
			),
			'well_prepared' => array(
				'signal'      => __( 'Well prepared', 'ai-risk-benchmark' ),
				'tone'        => 'positive',
				'consequence' => __( 'You are well placed to support safe, honest and independent AI use at home. Share what works with your child\'s school to help other families.', 'ai-risk-benchmark' ),
			),
		),
	),
	'hero_metric_label'            => __( 'Overall home AI awareness', 'ai-risk-benchmark' ),
	'metrics_section_heading'      => __( 'Your 5 home safety scores', 'ai-risk-benchmark' ),
	'metrics_section_heading_short'=> __( '5 home safety scores', 'ai-risk-benchmark' ),
	'focus_section_heading'        => __( 'Focus topics — what to tackle at home', 'ai-risk-benchmark' ),
	'focus_section_heading_short'  => __( 'Focus topics', 'ai-risk-benchmark' ),
	'conversation_section_heading' => __( 'Conversation starters — talk to your child tonight', 'ai-risk-benchmark' ),
	'conversation_section_heading_short' => __( 'Conversation starters', 'ai-risk-benchmark' ),
	'conversation_section_intro' => __( 'These are simple questions that open up a real conversation about how your child uses AI — no technical knowledge needed.', 'ai-risk-benchmark' ),
	'share_section_kicker'         => __( 'Share with your school', 'ai-risk-benchmark' ),
	'share_section_title'          => __( 'Help your school support your child better', 'ai-risk-benchmark' ),
	'share_section_body'           => __( 'When parents complete the benchmark, schools can see where home AI awareness needs support. Your results are anonymous — only the aggregate picture is shared with school leaders.', 'ai-risk-benchmark' ),
	'share_cta_primary'            => __( 'Share with school', 'ai-risk-benchmark' ),
	'share_cta_secondary'          => __( 'Parent safety guide', 'ai-risk-benchmark' ),
	'help_support_heading'         => __( 'Further reading for parents', 'ai-risk-benchmark' ),
	'help_support_heading_short'   => __( 'Further reading', 'ai-risk-benchmark' ),
	'home_metrics' => array(
		array(
			'slug'       => 'parent_awareness',
			'label'      => __( 'Parent awareness', 'ai-risk-benchmark' ),
			'subtitle'   => __( 'How well you know what AI tools your child uses', 'ai-risk-benchmark' ),
			'icon'       => 'eye',
			'source'     => 'parent_awareness',
		),
		array(
			'slug'       => 'home_ai_safety',
			'label'      => __( 'Home AI safety', 'ai-risk-benchmark' ),
			'subtitle'   => __( 'Conversations and boundaries around AI at home', 'ai-risk-benchmark' ),
			'icon'       => 'home',
			'source'     => 'home_ai_safety',
		),
		array(
			'slug'       => 'homework_oversight',
			'label'      => __( 'Homework support', 'ai-risk-benchmark' ),
			'subtitle'   => __( 'Whether AI use in homework is clear and supervised', 'ai-risk-benchmark' ),
			'icon'       => 'book',
			'source'     => 'homework_oversight',
		),
		array(
			'slug'       => 'online_risk_awareness',
			'label'      => __( 'Deepfake & online risk awareness', 'ai-risk-benchmark' ),
			'subtitle'   => __( 'Awareness of AI-generated images, videos and scams', 'ai-risk-benchmark' ),
			'icon'       => 'alert',
			'source'     => 'online_risk_awareness',
			'focus_slug' => 'online_risk',
		),
		array(
			'slug'       => 'parent_confidence',
			'label'      => __( 'Your confidence with AI', 'ai-risk-benchmark' ),
			'subtitle'   => __( 'How equipped you feel to guide your child', 'ai-risk-benchmark' ),
			'icon'       => 'confidence',
			'source'     => 'school_partnership',
		),
	),
	'focus_slug_map' => array(
		'online_risk_awareness' => 'online_risk',
		'homework_oversight'    => 'homework_boundaries',
		'parent_ai_dependency'  => 'parent_ai_use',
		'home_ai_safety'        => 'home_safety',
		'school_partnership'    => 'school_partnership',
	),
	'focus_tiers' => array(
		'online_risk' => array(
			'low' => array(
				'severity'  => 'risk',
				'summary'   => __( 'Deepfakes are AI-generated images, videos or audio that look real but are fake. They can be used to bully, manipulate or scam — and young people are increasingly targeted. Most parents aren\'t yet aware of this risk.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What your child may encounter', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'Fake images or videos of themselves or friends shared online', 'ai-risk-benchmark' ),
					__( 'AI-generated messages pretending to be from someone they know', 'ai-risk-benchmark' ),
					__( 'Scam content that looks or sounds convincingly real', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Talk to your child about what deepfakes are and show them an example', 'ai-risk-benchmark' ),
					__( 'Agree that if something looks wrong they come to you first, not respond', 'ai-risk-benchmark' ),
					__( 'Know your school\'s reporting process for online safety incidents', 'ai-risk-benchmark' ),
				),
			),
			'attention' => array(
				'severity'  => 'attention',
				'summary'   => __( 'Your awareness of deepfakes and AI-generated harm could be stronger. Talk with your child about what to do if something online does not feel right.', 'ai-risk-benchmark' ),
				'challenge_heading' => __( 'What to remember', 'ai-risk-benchmark' ),
				'challenge_bullets' => array(
					__( 'AI can create convincing fake images, voices and messages', 'ai-risk-benchmark' ),
					__( 'Young people may not recognise synthetic content immediately', 'ai-risk-benchmark' ),
				),
				'actions' => array(
					__( 'Ask your child if they have seen anything online that looked fake', 'ai-risk-benchmark' ),
					__( 'Agree a simple rule: come to you before sharing or responding', 'ai-risk-benchmark' ),
				),
			),
		),
		'homework_boundaries' => array(
			'low' => array(
				'severity' => 'attention',
				'summary'  => __( 'It may not be clear at home what AI your child can and can\'t use for schoolwork. Without a shared understanding, AI can become a shortcut that gets in the way of your child\'s learning — and could break school rules.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Ask your child what AI rules their school has for homework', 'ai-risk-benchmark' ),
					__( 'Agree a simple rule at home — try yourself first, then use AI to check', 'ai-risk-benchmark' ),
					__( 'If unsure what\'s allowed, contact the school for their AI guidance', 'ai-risk-benchmark' ),
				),
			),
			'attention' => array(
				'severity' => 'attention',
				'summary'  => __( 'Homework oversight at home could be stronger. Clear boundaries help your child learn with AI rather than rely on it.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Ask your child to explain one AI-assisted answer in their own words', 'ai-risk-benchmark' ),
					__( 'Check your school\'s homework and AI expectations together', 'ai-risk-benchmark' ),
				),
			),
		),
		'parent_ai_use' => array(
			'low' => array(
				'severity' => 'attention',
				'summary'  => __( 'Using AI to do homework for your child can hide gaps in learning. Model trying first, then using AI to check or explain — not to produce the answer.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Try supporting without AI first when homework is hard', 'ai-risk-benchmark' ),
					__( 'Use AI to explain concepts, not to write submissions for your child', 'ai-risk-benchmark' ),
				),
			),
		),
		'home_safety' => array(
			'attention' => array(
				'severity' => 'attention',
				'summary'  => __( 'Talk about privacy, fake images and voice messages, and agree what your child should do if something online does not feel right.', 'ai-risk-benchmark' ),
				'actions' => array(
					__( 'Discuss what personal information should never be shared with AI tools', 'ai-risk-benchmark' ),
					__( 'Agree who your child should tell if something online feels wrong', 'ai-risk-benchmark' ),
				),
			),
		),
	),
	'conversation_starters' => array(
		array(
			'topic'    => 'general',
			'question' => __( '"What AI tools do you use — and what do you use them for?"', 'ai-risk-benchmark' ),
			'hint'     => __( 'Opens up visibility without making them feel interrogated.', 'ai-risk-benchmark' ),
		),
		array(
			'topic'    => 'online_risk',
			'question' => __( '"Have you ever seen something online that looked real but might have been fake?"', 'ai-risk-benchmark' ),
			'hint'     => __( 'Introduces deepfakes naturally through their own experience.', 'ai-risk-benchmark' ),
		),
		array(
			'topic'    => 'homework_boundaries',
			'question' => __( '"Do you know what your school says about using AI for homework?"', 'ai-risk-benchmark' ),
			'hint'     => __( 'Checks whether school rules are understood at home.', 'ai-risk-benchmark' ),
		),
		array(
			'topic'    => 'general',
			'question' => __( '"If something felt wrong online, would you know what to do?"', 'ai-risk-benchmark' ),
			'hint'     => __( 'Builds a habit of coming to you when something doesn\'t feel right.', 'ai-risk-benchmark' ),
		),
	),
);
