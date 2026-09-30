<?php
/**
 * Benchmark content module: seeds and backfills for the AI Risk & Readiness Benchmark launch article timeline entry,
 * its excerpt helper, draft timeline copies of the benchmark hub pages, and the benchmark start URL / role link
 * helpers used by the homepage promo, the certificate showcase and the National Conversation page. The benchmark itself is the bundled
 * ai-risk-readiness-benchmark plugin.
 *
 * Moved from the theme's inc/ folder. The theme loads this file from its bundled copy of the plugin when the plugin
 * isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_BENCHMARK_CONTENT', __FILE__ );

// Same order the theme loaded them in.
require_once __DIR__ . '/benchmark-content/ai-risk-benchmark-post.php';
require_once __DIR__ . '/benchmark-content/benchmark-promo.php';
require_once __DIR__ . '/benchmark-content/airb-hub-timeline-seed.php';
