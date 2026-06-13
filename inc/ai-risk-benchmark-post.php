<?php
/**
 * AI Risk & Readiness Benchmark — launch article timeline entry.
 *
 * Seeds a timeline post containing the launch blog article plus the embedded
 * benchmark tool ([ai_risk_benchmark], provided by the bundled plugin).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical slug for the benchmark launch timeline entry.
 */
function aiad_risk_benchmark_post_slug(): string {
	return 'ai-risk-readiness-benchmark';
}

/**
 * Timeline headline / post title.
 */
function aiad_risk_benchmark_get_headline(): string {
	return __( 'The Hidden Threat in School AI Adoption: It’s Not the Tech, It’s the Dependency', 'ai-awareness-day' );
}

/**
 * Short excerpt used in timeline cards and SEO description.
 */
function aiad_risk_benchmark_get_excerpt(): string {
	return __( 'Most AI audits only measure adoption — the tech, the policies, the paperwork. They miss exposure: how dependent your teachers and students are becoming. Meet the UK’s first DfE-aligned AI Risk & Readiness Benchmark™, free for schools.', 'ai-awareness-day' );
}

/**
 * Gutenberg block content for the launch article (article + embedded tool).
 */
function aiad_risk_benchmark_get_post_content(): string {
	$blocks = array();

	$blocks[] = '<!-- wp:paragraph --><p>Every school leader is currently being flooded with AI guidance. Regulators like the DfE, ICO, KCSIE, JCQ and Ofqual have made it clear: schools must adopt AI safely. In response, schools are rushing to roll out AI policies and staff training.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:paragraph --><p>But as leadership teams tick these compliance boxes, a critical question remains unanswered: <strong>do you actually know how AI is changing human behaviour in your school community?</strong></p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:paragraph --><p>Most existing AI audits only measure <em>adoption</em> — whether you have the tech, the policies and the infrastructure. They completely miss <em>exposure</em> — how dependent your teachers and students are becoming on these tools, and where your actual risks lie.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">The blind spots in standard AI audits</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>Traditional readiness frameworks focus heavily on technology and paperwork. They ask questions like “Do you have an AI policy?” or “Do you provide AI training?” While those are important compliance steps, they create a false sense of security. They don’t address the real, day-to-day risks across your entire school community:</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:list --><ul class="wp-block-list">'
		. '<li><strong>Teachers:</strong> Are they entering sensitive pupil data into unapproved tools? Are they blindly trusting AI-generated lesson plans without verifying the outputs?</li>'
		. '<li><strong>Students:</strong> Is AI assisting their homework, or is it completely replacing critical thinking? Could they still complete the work without it?</li>'
		. '<li><strong>Parents:</strong> Do they understand how their children use AI at home? Are they aware of deepfakes, algorithmic bias and privacy risks?</li>'
		. '<li><strong>Leaders:</strong> Do you have a clear, data-driven view of your compliance with DfE, ICO and KCSIE guidelines?</li>'
		. '</ul><!-- /wp:list -->';

	$blocks[] = '<!-- wp:paragraph --><p>If you only measure technology adoption, you are blind to behavioural risk.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">Shifting from “AI readiness” to “AI dependency”</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>To safely navigate the AI era, schools need to measure human behaviour, not just software deployment. The core issue isn’t whether your school allows AI, but how exposed it is to the risks of that AI.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:table --><figure class="wp-block-table"><table>'
		. '<thead><tr><th>Traditional AI audits</th><th>The behavioural risk approach</th></tr></thead>'
		. '<tbody>'
		. '<tr><td>Do you have an AI policy?</td><td>Do staff actually follow it?</td></tr>'
		. '<tr><td>Do you provide training?</td><td>Has training reduced risky behaviour?</td></tr>'
		. '<tr><td>Do you allow AI tools?</td><td>How dependent are people becoming on them?</td></tr>'
		. '<tr><td>Are safeguards documented?</td><td>Are people actively bypassing safeguards?</td></tr>'
		. '<tr><td>Is governance in place?</td><td>Where is data exposure actually occurring?</td></tr>'
		. '</tbody></table></figure><!-- /wp:table -->';

	$blocks[] = '<!-- wp:paragraph --><p>By focusing on behavioural risk, leaders can identify exactly where confidence outpaces competence — and target interventions where they are needed most.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">Introducing the AI Risk &amp; Readiness Benchmark™</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>To help schools bridge this data gap, we have launched the <strong>AI Risk &amp; Readiness Benchmark™</strong> — and it is completely free for UK schools. This is the UK’s first DfE-aligned assessment platform built specifically to measure AI dependency, risk and governance maturity across your entire school community.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:list --><ul class="wp-block-list">'
		. '<li><strong>Teacher Benchmark</strong> — reliance, data entry, verification</li>'
		. '<li><strong>Student Benchmark</strong> — critical thinking, prompt literacy</li>'
		. '<li><strong>Parent Benchmark</strong> — safety awareness, home usage</li>'
		. '<li><strong>Leader Benchmark</strong> — compliance, governance, policy</li>'
		. '</ul><!-- /wp:list -->';

	$blocks[] = '<!-- wp:paragraph --><p>Instead of a generic checklist, the platform gathers anonymous insights from teachers, students, parents and leaders to generate two unique data points for your school:</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:list {"ordered":true} --><ol class="wp-block-list">'
		. '<li><strong>The AI Dependency Index™</strong> — measures reliance, human oversight, verification habits and privacy behaviours across all four audiences.</li>'
		. '<li><strong>The DfE Alignment Score</strong> — a clear picture of your compliance standing against current DfE, ICO, KCSIE, JCQ and Ofqual guidance.</li>'
		. '</ol><!-- /wp:list -->';

	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">Audit your school for free</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>Don’t wait for a compliance failure or a safeguarding incident to find out where your risks are. Move beyond basic adoption checklists and discover your school’s actual AI exposure — start the benchmark below.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:shortcode -->[ai_risk_benchmark]<!-- /wp:shortcode -->';

	return implode( "\n\n", $blocks );
}

/**
 * Apply timeline meta for the benchmark launch entry.
 */
function aiad_set_risk_benchmark_timeline_meta( int $post_id ): void {
	update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $post_id, '_aiad_timeline_icon', 'shield' );
	update_post_meta( $post_id, '_aiad_timeline_auto_type', '' );
	update_post_meta( $post_id, '_aiad_timeline_related_id', 0 );
}

/**
 * Create the benchmark launch timeline entry if missing.
 *
 * @return int Post ID or 0.
 */
function aiad_create_risk_benchmark_timeline_entry(): int {
	$slug = aiad_risk_benchmark_post_slug();

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => aiad_risk_benchmark_get_headline(),
			'post_excerpt' => aiad_risk_benchmark_get_excerpt(),
			'post_content' => aiad_risk_benchmark_get_post_content(),
			'post_author'  => 1,
		),
		true
	);

	if ( ! $post_id || is_wp_error( $post_id ) ) {
		return 0;
	}

	aiad_set_risk_benchmark_timeline_meta( (int) $post_id );
	return (int) $post_id;
}

/**
 * Seed timeline entry (once).
 */
function aiad_seed_risk_benchmark_timeline_entry(): void {
	$existing = get_page_by_path( aiad_risk_benchmark_post_slug(), OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		update_option( 'aiad_risk_benchmark_timeline_seeded', 'yes' );
		return;
	}

	if ( get_option( 'aiad_risk_benchmark_timeline_seeded' ) === 'yes' ) {
		// Flag set but post missing (e.g. live deploy before plugin was active) — allow re-seed.
		delete_option( 'aiad_risk_benchmark_timeline_seeded' );
	}

	// Only seed when the benchmark plugin (shortcode provider) is active.
	if ( ! shortcode_exists( 'ai_risk_benchmark' ) ) {
		return;
	}

	if ( aiad_create_risk_benchmark_timeline_entry() ) {
		update_option( 'aiad_risk_benchmark_timeline_seeded', 'yes' );
		set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
	}
}
add_action( 'init', 'aiad_seed_risk_benchmark_timeline_entry', 33 );
