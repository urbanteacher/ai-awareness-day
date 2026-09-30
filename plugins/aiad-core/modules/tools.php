<?php
/**
 * Interactive tools module: AI buzzwords, LLM explainer and order game, speed quiz, computing curriculum challenge,
 * ICT curriculum, misinformation detector, NEU AI report, and the Schools' AI Risk Academy. Each registers its
 * shortcode, assets and timeline seed.
 *
 * Moved from the theme's inc/ folder. The theme loads this file from its bundled copy of the plugin when the plugin
 * isn't active, so this is the only copy. Their CSS and JS (and the Risk Academy's template) still load from the
 * theme until each tool's block takes them over.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_TOOLS', __FILE__ );

// Same order the theme loaded them in: the speed quiz and order game call the explainer and buzzwords.
require_once __DIR__ . '/tools/ai-buzzwords.php';
require_once __DIR__ . '/tools/ai-llm-explainer.php';
require_once __DIR__ . '/tools/ai-llm-order-game.php';
require_once __DIR__ . '/tools/ai-speed-quiz.php';
require_once __DIR__ . '/tools/ai-computing-curriculum-challenge.php';
require_once __DIR__ . '/tools/ai-ict-curriculum.php';
require_once __DIR__ . '/tools/ai-misinformation-detector.php';
require_once __DIR__ . '/tools/ai-neu-ai-report-data.php';
require_once __DIR__ . '/tools/ai-neu-ai-report.php';
require_once __DIR__ . '/tools/schools-ai-risk-academy.php';
