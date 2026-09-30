<?php
// This file is generated. Do not modify it manually.
return array(
	'buzzwords' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/buzzwords',
		'version' => '0.1.0',
		'title' => 'AI Buzzwords',
		'category' => 'aiad',
		'icon' => 'book-alt',
		'description' => 'The 15 AI buzzwords every teacher should know, with an optional quiz. Same output as the [aiad_buzzwords] shortcode.',
		'keywords' => array(
			'buzzwords',
			'glossary',
			'teachers'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'hideIntro' => array(
				'type' => 'boolean',
				'default' => false
			),
			'quiz' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-buzzwords.css',
		'render' => 'file:./render.php'
	),
	'certificate-showcase' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/certificate-showcase',
		'version' => '0.1.0',
		'title' => 'Certificate Showcase',
		'category' => 'aiad',
		'icon' => 'awards',
		'description' => 'The AI Risk & Readiness Benchmark pitch and the certificate it earns. Same output as the [aiad_certificate_showcase] shortcode. Needs the benchmark plugin to draw the certificate.',
		'keywords' => array(
			'certificate',
			'benchmark',
			'audit'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'eyebrow' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'title' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'lead' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'ctaText' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'ctaUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'certEyebrow' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'certTitle' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'certLead' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'strand' => array(
				'type' => 'string',
				'default' => 'safe'
			),
			'tone' => array(
				'type' => 'string',
				'default' => ''
			),
			'sectionId' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/certificate-showcase.css',
		'render' => 'file:./render.php'
	),
	'computing-curriculum' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/computing-curriculum',
		'version' => '0.1.0',
		'title' => 'Computing Curriculum Challenge',
		'category' => 'aiad',
		'icon' => 'welcome-learn-more',
		'description' => 'Computing curriculum challenge for teachers. Same output as the [aiad_computing_curriculum] shortcode.',
		'keywords' => array(
			'computing',
			'curriculum',
			'challenge'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-computing-curriculum-challenge.css',
		'render' => 'file:./render.php'
	),
	'ict-curriculum' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/ict-curriculum',
		'version' => '0.1.0',
		'title' => 'Experience the Computing Curriculum',
		'category' => 'aiad',
		'icon' => 'desktop',
		'description' => 'Interactive computing curriculum walkthrough across the key stages. Same output as the [aiad_ict_curriculum] shortcode.',
		'keywords' => array(
			'ict',
			'computing',
			'curriculum'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-ict-curriculum.css',
		'render' => 'file:./render.php'
	),
	'llm-explainer' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/llm-explainer',
		'version' => '0.1.0',
		'title' => 'How an LLM Works',
		'category' => 'aiad',
		'icon' => 'lightbulb',
		'description' => 'Step-by-step explainer of how a large language model works. Same output as the [aiad_llm_explainer] shortcode.',
		'keywords' => array(
			'llm',
			'explainer',
			'language model'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'hideIntro' => array(
				'type' => 'boolean',
				'default' => false
			),
			'exploreUrl' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-llm-explainer.css',
		'render' => 'file:./render.php'
	),
	'llm-order-game' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/llm-order-game',
		'version' => '0.1.0',
		'title' => 'LLM Order Game',
		'category' => 'aiad',
		'icon' => 'randomize',
		'description' => 'Put the steps of how a language model answers in the right order. Same output as the [aiad_llm_order_game] shortcode.',
		'keywords' => array(
			'llm',
			'game',
			'order'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-llm-order-game.css',
		'render' => 'file:./render.php'
	),
	'misinformation-detector' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/misinformation-detector',
		'version' => '0.1.0',
		'title' => 'Misinformation Detector',
		'category' => 'aiad',
		'icon' => 'search',
		'description' => 'Spot the signs of AI-generated misinformation. Same output as the [aiad_misinformation_detector] shortcode.',
		'keywords' => array(
			'misinformation',
			'fake news',
			'detector'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'hideIntro' => array(
				'type' => 'string',
				'default' => 'auto'
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-misinformation-detector.css',
		'render' => 'file:./render.php'
	),
	'national-survey' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/national-survey',
		'version' => '0.1.0',
		'title' => 'National Survey',
		'category' => 'aiad',
		'icon' => 'feedback',
		'description' => 'The National AI Survey form. Same output as the [aiad_national_survey] shortcode.',
		'keywords' => array(
			'survey',
			'national'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/national-survey.css',
		'render' => 'file:./render.php'
	),
	'neu-ai-report' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/neu-ai-report',
		'version' => '0.1.0',
		'title' => 'NEU AI Report',
		'category' => 'aiad',
		'icon' => 'media-document',
		'description' => 'Unpacking the NEU AI report. Same output as the [aiad_neu_ai_report] shortcode.',
		'keywords' => array(
			'neu',
			'report'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'headline' => array(
				'type' => 'string',
				'default' => 'auto'
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-neu-ai-report.css',
		'render' => 'file:./render.php'
	),
	'risk-academy' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/risk-academy',
		'version' => '0.1.0',
		'title' => 'Schools\' AI Risk Academy',
		'category' => 'aiad',
		'icon' => 'shield',
		'description' => 'The Schools\' AI Risk Academy, section by section. Same output as the [aiad_risk_academy] shortcode.',
		'keywords' => array(
			'risk',
			'academy',
			'schools'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'hero' => array(
				'type' => 'boolean',
				'default' => true
			),
			'methodology' => array(
				'type' => 'boolean',
				'default' => true
			),
			'meter' => array(
				'type' => 'boolean',
				'default' => true
			),
			'curriculum' => array(
				'type' => 'boolean',
				'default' => true
			),
			'contributors' => array(
				'type' => 'boolean',
				'default' => true
			),
			'resources' => array(
				'type' => 'boolean',
				'default' => true
			),
			'sources' => array(
				'type' => 'boolean',
				'default' => true
			),
			'enrol' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => array(
			'file:../../../assets/css/components/sara-fonts.css',
			'file:../../../assets/css/components/schools-ai-risk-academy.css'
		),
		'render' => 'file:./render.php'
	),
	'risk-benchmark' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/risk-benchmark',
		'version' => '0.1.0',
		'title' => 'AI Risk & Readiness Benchmark',
		'category' => 'aiad',
		'icon' => 'chart-bar',
		'description' => 'The AI Risk & Readiness Benchmark audit. Same output as the [ai_risk_benchmark] shortcode; needs the AI Risk & Readiness Benchmark plugin.',
		'keywords' => array(
			'benchmark',
			'audit',
			'risk'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'school-dashboard' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/school-dashboard',
		'version' => '0.1.0',
		'title' => 'Benchmark School Dashboard',
		'category' => 'aiad',
		'icon' => 'building',
		'description' => 'A school\'s AI Risk & Readiness Benchmark results. Same output as the [ai_risk_school_dashboard] shortcode; needs the AI Risk & Readiness Benchmark plugin.',
		'keywords' => array(
			'benchmark',
			'school',
			'dashboard'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'school' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'speed-quiz' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/speed-quiz',
		'version' => '0.1.0',
		'title' => 'AI Speed Quiz',
		'category' => 'aiad',
		'icon' => 'clock',
		'description' => 'Timed AI quiz for teachers, with a leaderboard saved on the device. Same output as the [aiad_speed_quiz] shortcode.',
		'keywords' => array(
			'quiz',
			'teachers',
			'timed'
		),
		'textdomain' => 'aiad-core',
		'attributes' => array(
			'questions' => array(
				'type' => 'integer',
				'default' => 10
			),
			'seconds' => array(
				'type' => 'integer',
				'default' => 15
			),
			'bonus' => array(
				'type' => 'integer',
				'default' => 10
			),
			'points' => array(
				'type' => 'integer',
				'default' => 100
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:../../../assets/css/components/ai-speed-quiz.css',
		'render' => 'file:./render.php'
	)
);
