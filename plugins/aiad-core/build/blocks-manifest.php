<?php
// This file is generated. Do not modify it manually.
return array(
	'breadcrumbs' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/breadcrumbs',
		'title' => 'Breadcrumbs',
		'category' => 'theme',
		'icon' => 'arrow-right-alt2',
		'description' => 'The breadcrumb trail below the header, when breadcrumbs are switched on in the Customizer.',
		'keywords' => array(
			'breadcrumbs',
			'navigation'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
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
	'campaign-embed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/campaign-embed',
		'title' => 'Campaign video',
		'category' => 'aiad-homepage',
		'icon' => 'video-alt3',
		'description' => 'The campaign\'s video (YouTube or Vimeo) or LinkedIn post, beside the campaign text.',
		'keywords' => array(
			'video',
			'embed',
			'campaign'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'url' => array(
				'type' => 'string',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
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
	'comments' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/comments',
		'title' => 'Comments',
		'category' => 'theme',
		'icon' => 'admin-comments',
		'description' => 'The post\'s comments and comment form (the theme\'s comments.php), when comments are open or there are some.',
		'keywords' => array(
			'comments',
			'discussion'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId',
			'postType'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
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
	'contact-form' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/contact-form',
		'title' => 'Get Involved form',
		'category' => 'aiad-homepage',
		'icon' => 'email',
		'description' => 'The Get Involved contact form. Messages go to the contact address in Settings → Campaign & contact.',
		'keywords' => array(
			'contact',
			'form',
			'get involved'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'download-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/download-card',
		'title' => 'Download card',
		'category' => 'theme',
		'icon' => 'download',
		'description' => 'A card with a preview (or, for a document, its type), a title, a description and a button that downloads a file, or opens a page. Used on the Assets Pack and the Press Release.',
		'keywords' => array(
			'download',
			'file',
			'asset',
			'logo',
			'card'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'kind' => array(
				'type' => 'string',
				'enum' => array(
					'download',
					'page'
				),
				'default' => 'download'
			),
			'href' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'filename' => array(
				'type' => 'string',
				'default' => ''
			),
			'preview' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'alt' => array(
				'type' => 'string',
				'default' => ''
			),
			'badge' => array(
				'type' => 'string',
				'default' => ''
			),
			'docLabel' => array(
				'type' => 'string',
				'default' => ''
			),
			'button' => array(
				'type' => 'string',
				'default' => '',
				'role' => 'content'
			),
			'title' => array(
				'type' => 'rich-text',
				'source' => 'rich-text',
				'selector' => 'h2',
				'role' => 'content'
			),
			'description' => array(
				'type' => 'rich-text',
				'source' => 'rich-text',
				'selector' => 'p',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false
		),
		'editorScript' => 'file:./index.js'
	),
	'footer-links' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/footer-links',
		'title' => 'Footer links',
		'category' => 'theme',
		'icon' => 'admin-links',
		'description' => 'The newsletter, press release, asset pack and implementation guide links. A link without an address shows as plain text.',
		'keywords' => array(
			'links',
			'downloads',
			'footer'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'footer-social' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/footer-social',
		'title' => 'Social icons',
		'category' => 'theme',
		'icon' => 'share',
		'description' => 'LinkedIn and Instagram icons, for the profiles set in the Customizer.',
		'keywords' => array(
			'social',
			'linkedin',
			'instagram',
			'footer'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'footer-widgets' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/footer-widgets',
		'title' => 'Footer widgets',
		'category' => 'theme',
		'icon' => 'welcome-widgets-menus',
		'description' => 'The Footer Content widget area (Appearance → Widgets), when it has widgets.',
		'keywords' => array(
			'widgets',
			'footer'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'hub-resource-badge' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/hub-resource-badge',
		'title' => 'Hub resource badge',
		'category' => 'aiad',
		'icon' => 'tag',
		'description' => 'The audience badge (Teacher resource, Student resource and so on) on a benchmark hub page.',
		'keywords' => array(
			'hub',
			'resource',
			'badge'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'hub-resource-excerpt' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/hub-resource-excerpt',
		'title' => 'Hub resource excerpt',
		'category' => 'aiad',
		'icon' => 'text',
		'description' => 'The page\'s own excerpt, when it has one. Unlike the Excerpt block, it never makes one up from the content.',
		'keywords' => array(
			'hub',
			'resource',
			'excerpt'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'hub-resource-footer' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/hub-resource-footer',
		'title' => 'Hub resource footer',
		'category' => 'aiad',
		'icon' => 'arrow-left-alt',
		'description' => 'What the benchmark plugin adds after a hub page\'s content, and the link back to the benchmark.',
		'keywords' => array(
			'hub',
			'resource',
			'footer'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
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
	'item' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/item',
		'title' => 'Styled list item',
		'category' => 'theme',
		'icon' => 'excerpt-view',
		'description' => 'One item of a styled list, holding any blocks.',
		'keywords' => array(
			'item',
			'card'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'tagName' => array(
				'type' => 'string',
				'enum' => array(
					'li',
					'div'
				),
				'default' => 'li'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false
		),
		'editorScript' => 'file:./index.js'
	),
	'lesson-plan' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/lesson-plan',
		'version' => '0.1.0',
		'title' => 'Lesson plan',
		'category' => 'aiad',
		'icon' => 'welcome-learn-more',
		'description' => 'The lesson\'s plan, edited where it reads: preparation, objectives, steps, the big question, adapting, the debate, key words and materials. Saved to the lesson, which the lesson page draws.',
		'keywords' => array(
			'lesson',
			'steps',
			'debate'
		),
		'textdomain' => 'aiad-core',
		'supports' => array(
			'html' => false,
			'inserter' => false,
			'multiple' => false,
			'reusable' => false,
			'customClassName' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css'
	),
	'linkedin-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/linkedin-card',
		'title' => 'LinkedIn card',
		'category' => 'aiad-homepage',
		'icon' => 'linkedin',
		'description' => 'A card linking to a LinkedIn post, in a section of its own. Nothing shows without an address.',
		'keywords' => array(
			'linkedin',
			'social',
			'post'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'url' => array(
				'type' => 'string',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'list' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/list',
		'title' => 'Styled list',
		'category' => 'theme',
		'icon' => 'editor-ul',
		'description' => 'A list whose items can hold any blocks (a heading and text, for example), styled by its class. Used on the National Conversation page.',
		'keywords' => array(
			'list',
			'cards',
			'steps'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'tagName' => array(
				'type' => 'string',
				'enum' => array(
					'ul',
					'ol',
					'dl',
					'p',
					'div',
					'nav'
				),
				'default' => 'ul'
			),
			'ariaLabel' => array(
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'ul,ol,dl,p,div,nav',
				'attribute' => 'aria-label'
			),
			'ariaLabelledby' => array(
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'ul,ol,dl,p,div,nav',
				'attribute' => 'aria-labelledby'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'anchor' => true
		),
		'editorScript' => 'file:./index.js'
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
	'nc-actions' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/nc-actions',
		'title' => 'National Conversation buttons',
		'category' => 'theme',
		'icon' => 'button',
		'description' => 'The National Conversation\'s buttons and links, which change on the day it opens: until then, when registration opens and the readiness check; from then, joining, signing in and nominating a school.',
		'keywords' => array(
			'national conversation',
			'register',
			'join',
			'buttons'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'variant' => array(
				'type' => 'string',
				'enum' => array(
					'hero',
					'cta',
					'nominate'
				),
				'default' => 'hero'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false
		),
		'editorScript' => 'file:./index.js',
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
	'partner-actions' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partner-actions',
		'title' => 'Partner actions',
		'category' => 'aiad',
		'icon' => 'external',
		'description' => 'The Visit website button (when the partner has a website) and the link back to the partners.',
		'keywords' => array(
			'partner',
			'profile'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partner-intro' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partner-intro',
		'title' => 'Partner intro',
		'category' => 'aiad',
		'icon' => 'text',
		'description' => 'The partner\'s profile intro, or its page content when it has no intro.',
		'keywords' => array(
			'partner',
			'profile'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partner-links' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partner-links',
		'title' => 'Partner resources',
		'category' => 'aiad',
		'icon' => 'admin-links',
		'description' => 'The partner\'s resource links, grouped by strand. Shows nothing when it has none.',
		'keywords' => array(
			'partner',
			'profile'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partner-logo' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partner-logo',
		'title' => 'Partner logo',
		'category' => 'aiad',
		'icon' => 'format-image',
		'description' => 'The partner\'s logo (its featured image), linking to its website when it has one. Shows nothing without a logo.',
		'keywords' => array(
			'partner',
			'profile'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partner-marquee' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partner-marquee',
		'title' => 'Partner logo strip',
		'category' => 'aiad-homepage',
		'icon' => 'images-alt2',
		'description' => 'The scrolling strip of partner logos. The logos come from Partners.',
		'keywords' => array(
			'partners',
			'logos',
			'marquee'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partners-directory' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partners-directory',
		'title' => 'Partners directory',
		'category' => 'theme',
		'icon' => 'groups',
		'description' => 'The Partner Type filter and every partner\'s logo, linking to its page. Partners are edited under Partners.',
		'keywords' => array(
			'partners',
			'directory',
			'filter'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'partners-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/partners-grid',
		'title' => 'Partner cards',
		'category' => 'aiad-homepage',
		'icon' => 'groups',
		'description' => 'The partner cards and the Show More Partners button. The cards come from Partners.',
		'keywords' => array(
			'partners',
			'reach',
			'cards'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'post-badge' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/post-badge',
		'title' => 'Featured badge',
		'category' => 'theme',
		'icon' => 'star-filled',
		'description' => 'Shows "Featured" above the title of a sticky post.',
		'keywords' => array(
			'featured',
			'sticky'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId',
			'postType'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'post-navigation' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/post-navigation',
		'title' => 'Previous and next posts',
		'category' => 'theme',
		'icon' => 'leftright',
		'description' => 'Links to the previous and next posts, as single.php printed them.',
		'keywords' => array(
			'previous',
			'next',
			'navigation'
		),
		'textdomain' => 'ai-awareness-day',
		'usesContext' => array(
			'postId',
			'postType'
		),
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'principle-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/principle-card',
		'title' => 'Principle card',
		'category' => 'aiad-homepage',
		'parent' => array(
			'aiad/principles-grid'
		),
		'icon' => 'star-filled',
		'description' => 'One strand of the principles: its icon, title and description. The card links to the activities.',
		'keywords' => array(
			'strand',
			'principle',
			'homepage'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'strand' => array(
				'type' => 'string',
				'enum' => array(
					'safe',
					'smart',
					'creative',
					'responsible',
					'future',
					'literacy'
				),
				'default' => 'safe'
			),
			'title' => array(
				'type' => 'string',
				'role' => 'content'
			),
			'text' => array(
				'type' => 'string',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'principles-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/principles-grid',
		'title' => 'Principles grid',
		'category' => 'aiad-homepage',
		'icon' => 'grid-view',
		'description' => 'The row of principle cards: a grid on wide screens, swiped sideways on phones.',
		'keywords' => array(
			'strands',
			'principles',
			'homepage'
		),
		'textdomain' => 'ai-awareness-day',
		'allowedBlocks' => array(
			'aiad/principle-card'
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'resource-tiles' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/resource-tiles',
		'title' => 'Resource tiles',
		'category' => 'aiad-homepage',
		'icon' => 'media-document',
		'description' => 'The resources picked in Appearance → Edit Homepage, as tiles, with a View all link. Free resources show nothing until some are picked; featured resources show the first three.',
		'keywords' => array(
			'resources',
			'tiles',
			'homepage'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'source' => array(
				'type' => 'string',
				'enum' => array(
					'free',
					'featured'
				),
				'default' => 'free'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
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
	'site-logo' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/site-logo',
		'title' => 'Site logo',
		'category' => 'theme',
		'icon' => 'admin-home',
		'description' => 'The AI Awareness Day 2027 lockup, linking to the homepage.',
		'keywords' => array(
			'logo',
			'header'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'site-navigation' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/site-navigation',
		'title' => 'Site menu',
		'category' => 'theme',
		'icon' => 'menu',
		'description' => 'The main menu (Appearance → Menus → Primary Navigation), with its toggle on small screens.',
		'keywords' => array(
			'menu',
			'navigation',
			'header'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
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
	),
	'strand-icon' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/strand-icon',
		'title' => 'Strand icon',
		'category' => 'theme',
		'icon' => 'marker',
		'description' => 'The mark of one of the five strands: Safe, Smart, Creative, Responsible or Future.',
		'keywords' => array(
			'strand',
			'theme',
			'icon'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'strand' => array(
				'type' => 'string',
				'enum' => array(
					'safe',
					'smart',
					'creative',
					'responsible',
					'future'
				),
				'default' => 'safe'
			),
			'size' => array(
				'type' => 'number',
				'default' => 32
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false,
			'customClassName' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'text' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/text',
		'title' => 'Styled text',
		'category' => 'theme',
		'icon' => 'editor-textcolor',
		'description' => 'A short piece of text in a styled list: a term and its description, a list item or a tag.',
		'keywords' => array(
			'term',
			'definition',
			'tag'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'tagName' => array(
				'type' => 'string',
				'enum' => array(
					'dt',
					'dd',
					'li',
					'span'
				),
				'default' => 'span'
			),
			'content' => array(
				'type' => 'rich-text',
				'source' => 'rich-text',
				'selector' => 'dt,dd,li,span',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false
		),
		'editorScript' => 'file:./index.js'
	),
	'timeline-feed' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/timeline-feed',
		'title' => 'Campaign updates feed',
		'category' => 'theme',
		'icon' => 'clock',
		'description' => 'The topic filters, the campaign updates and their page numbers. Updates are edited under Timeline.',
		'keywords' => array(
			'timeline',
			'updates',
			'news',
			'feed'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'className' => false,
			'customClassName' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'tools-filter' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/tools-filter',
		'title' => 'Tools category filter',
		'category' => 'aiad',
		'icon' => 'filter',
		'description' => 'The category buttons on the AI tools archive. Shows nothing until a tool has a category.',
		'keywords' => array(
			'tools',
			'filter'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'tools-groups' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/tools-groups',
		'title' => 'Tools by category',
		'category' => 'aiad',
		'icon' => 'list-view',
		'description' => 'The AI tools archive\'s tools, grouped by category (or in one list while there are no categories).',
		'keywords' => array(
			'tools',
			'groups'
		),
		'textdomain' => 'ai-awareness-day',
		'supports' => array(
			'html' => false,
			'multiple' => false,
			'reusable' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'zoom-image' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'aiad/zoom-image',
		'title' => 'Image that opens full size',
		'category' => 'theme',
		'icon' => 'search',
		'description' => 'An image that opens full size in a new tab, with a label over it. Used for the walkthrough\'s screenshots.',
		'keywords' => array(
			'image',
			'screenshot',
			'zoom'
		),
		'textdomain' => 'ai-awareness-day',
		'attributes' => array(
			'url' => array(
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'img',
				'attribute' => 'src',
				'role' => 'content'
			),
			'alt' => array(
				'type' => 'string',
				'source' => 'attribute',
				'selector' => 'img',
				'attribute' => 'alt',
				'default' => '',
				'role' => 'content'
			),
			'width' => array(
				'type' => 'number'
			),
			'height' => array(
				'type' => 'number'
			),
			'label' => array(
				'type' => 'rich-text',
				'source' => 'rich-text',
				'selector' => 'span',
				'role' => 'content'
			)
		),
		'supports' => array(
			'html' => false,
			'className' => false
		),
		'editorScript' => 'file:./index.js'
	)
);
