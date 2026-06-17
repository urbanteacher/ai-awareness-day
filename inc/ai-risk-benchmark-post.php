<?php
/**
 * AI Risk & Readiness Benchmark — launch article timeline entry.
 *
 * Seeds a timeline post containing the launch blog article plus the embedded
 * benchmark tool ([ai_risk_benchmark], provided by the bundled plugin).
 *
 * Reference: import/ai-risk-benchmark-post-content.html
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
 * Timeline excerpt shown on the single timeline entry.
 */
function aiad_risk_benchmark_get_excerpt(): string {
	return __( 'During the build-up to AI Awareness Day 2026, we received many enquiries about AI in schools. This article introduces our free AI Risk & Readiness Benchmark™ — a practical audit to measure adoption, dependency and readiness for you and your school.', 'ai-awareness-day' );
}

/**
 * Gutenberg block content for the launch article (article + embedded tool).
 */
function aiad_get_risk_benchmark_timeline_content(): string {
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

	// ── School-wide dashboard ────────────────────────────────────────────
	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">The school-wide dashboard</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>Individual audits are useful — but the real power comes when teachers, students, parents and leaders have all taken part. Results are aggregated into a single whole-school picture, so leadership can see exactly where confidence outpaces competence. Here is what that looks like for an example school:</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:list --><ul class="wp-block-list">'
		. '<li><strong>Teachers:</strong> 71%</li>'
		. '<li><strong>Students:</strong> 58%</li>'
		. '<li><strong>Parents:</strong> 49%</li>'
		. '<li><strong>Leadership:</strong> 82%</li>'
		. '</ul><!-- /wp:list -->';

	$blocks[] = '<!-- wp:paragraph --><p><strong>Overall DfE AI Alignment Score: 65%</strong> &nbsp;·&nbsp; <strong>Risk level: Medium–High</strong></p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:paragraph --><p><strong>Key exposure areas:</strong> Teacher Dependency · Output Verification · Governance Maturity</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:table --><figure class="wp-block-table"><table>'
		. '<thead><tr><th>Risk area</th><th>Score</th></tr></thead>'
		. '<tbody>'
		. '<tr><td>Dependency</td><td>High</td></tr>'
		. '<tr><td>Oversight</td><td>Medium</td></tr>'
		. '<tr><td>Governance</td><td>Low</td></tr>'
		. '<tr><td>Privacy</td><td>Low</td></tr>'
		. '<tr><td>Safeguarding</td><td>Medium</td></tr>'
		. '</tbody></table></figure><!-- /wp:table -->';

	// ── The killer metric ────────────────────────────────────────────────
	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">The killer metric: the Human Oversight Ratio™</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>If you measured only one thing, measure this. The Human Oversight Ratio™ asks a single, revealing question: <em>what percentage of AI-generated output do you modify before using it?</em> It is the clearest signal of whether AI is a tool people think with — or a shortcut they think instead of.</p><!-- /wp:paragraph -->';

	$blocks[] = '<!-- wp:table --><figure class="wp-block-table"><table>'
		. '<thead><tr><th>Modified before use</th><th>What it means</th></tr></thead>'
		. '<tbody>'
		. '<tr><td>0–10%</td><td>Critical reliance</td></tr>'
		. '<tr><td>11–25%</td><td>High reliance</td></tr>'
		. '<tr><td>26–50%</td><td>Moderate oversight</td></tr>'
		. '<tr><td>51%+</td><td>Strong oversight</td></tr>'
		. '</tbody></table></figure><!-- /wp:table -->';

	// ── Free audit CTA (the live tool, at the bottom) ───────────────────
	$blocks[] = '<!-- wp:heading --><h2 class="wp-block-heading">' . esc_html__( 'Audit yourself or your school for free', 'ai-awareness-day' ) . '</h2><!-- /wp:heading -->';

	$blocks[] = '<!-- wp:paragraph --><p>' . esc_html__( 'Don’t wait for a compliance failure or a safeguarding incident to find out where your risks are. Move beyond basic adoption checklists and discover your actual AI exposure — teachers, students, parents and leaders can all start the benchmark below.', 'ai-awareness-day' ) . '</p><!-- /wp:paragraph -->';

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
	$slug  = aiad_risk_benchmark_post_slug();
	$title = aiad_risk_benchmark_get_headline();

	$existing = get_page_by_path( $slug, OBJECT, 'timeline' );
	if ( $existing instanceof WP_Post ) {
		return (int) $existing->ID;
	}

	$post_id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_excerpt' => aiad_risk_benchmark_get_excerpt(),
			'post_content' => aiad_get_risk_benchmark_timeline_content(),
			'post_status'  => 'publish',
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
 * One-time seed: benchmark launch as a timeline entry (same pattern as buzzwords / NEU / Risk Academy).
 */
function aiad_seed_risk_benchmark_timeline_entry(): void {
	$slug  = aiad_risk_benchmark_post_slug();
	$title = aiad_risk_benchmark_get_headline();

	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_risk_benchmark_timeline_seeded', 'yes' );
		return;
	}

	if ( function_exists( 'aiad_get_post_by_title' ) ) {
		$by_title = aiad_get_post_by_title( $title, 'timeline' );
		if ( $by_title instanceof WP_Post ) {
			if ( $slug !== $by_title->post_name ) {
				wp_update_post(
					array(
						'ID'        => (int) $by_title->ID,
						'post_name' => $slug,
					),
					true
				);
				set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
			}
			aiad_set_risk_benchmark_timeline_meta( (int) $by_title->ID );
			update_option( 'aiad_risk_benchmark_timeline_seeded', 'yes' );
			return;
		}
	}

	if ( get_option( 'aiad_risk_benchmark_timeline_seeded' ) === 'yes' ) {
		delete_option( 'aiad_risk_benchmark_timeline_seeded' );
	}

	if ( aiad_create_risk_benchmark_timeline_entry() ) {
		update_option( 'aiad_risk_benchmark_timeline_seeded', 'yes' );
		set_transient( 'aiad_flush_rewrites', 1, MINUTE_IN_SECONDS );
	}
}
add_action( 'init', 'aiad_seed_risk_benchmark_timeline_entry', 33 );

/**
 * One-time backfill: give the benchmark entry a proper excerpt if it predates
 * the excerpt being added to the seeder.
 */
function aiad_backfill_risk_benchmark_excerpt(): void {
	if ( get_option( 'aiad_risk_benchmark_excerpt_backfilled' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_risk_benchmark_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	if ( '' === trim( (string) $post->post_excerpt ) ) {
		wp_update_post(
			array(
				'ID'           => (int) $post->ID,
				'post_excerpt' => aiad_risk_benchmark_get_excerpt(),
			),
			true
		);
	}

	update_option( 'aiad_risk_benchmark_excerpt_backfilled', 'yes' );
}
add_action( 'init', 'aiad_backfill_risk_benchmark_excerpt', 34 );

/**
 * One-time backfill: replace the legacy benchmark excerpt on the live article.
 */
function aiad_backfill_risk_benchmark_excerpt_v2(): void {
	if ( get_option( 'aiad_risk_benchmark_excerpt_v2_backfilled' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_risk_benchmark_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$legacy = 'Most AI audits only measure adoption — the tech, the policies, the paperwork. They miss exposure: how dependent your teachers and students are becoming. Meet the UK’s first DfE-aligned AI Risk & Readiness Benchmark™, free for schools.';
	$legacy_ascii = "Most AI audits only measure adoption — the tech, the policies, the paperwork. They miss exposure: how dependent your teachers and students are becoming. Meet the UK's first DfE-aligned AI Risk & Readiness Benchmark™, free for schools.";
	$current = trim( (string) $post->post_excerpt );

	if ( $current === $legacy || $current === $legacy_ascii || $current === '' ) {
		wp_update_post(
			array(
				'ID'           => (int) $post->ID,
				'post_excerpt' => aiad_risk_benchmark_get_excerpt(),
			),
			true
		);
	}

	update_option( 'aiad_risk_benchmark_excerpt_v2_backfilled', 'yes' );
}
add_action( 'init', 'aiad_backfill_risk_benchmark_excerpt_v2', 34 );

/**
 * One-time backfill: update the audit CTA heading on the live benchmark article.
 */
function aiad_backfill_risk_benchmark_audit_heading(): void {
	if ( get_option( 'aiad_risk_benchmark_audit_heading_backfilled' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_risk_benchmark_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$content = (string) $post->post_content;
	$old     = 'Audit your school for free';
	$new     = __( 'Audit yourself or your school for free', 'ai-awareness-day' );

	if ( str_contains( $content, $old ) ) {
		$content = str_replace( $old, $new, $content );
		$content = str_replace(
			'discover your school’s actual AI exposure — try the benchmark below.',
			__( 'discover your actual AI exposure — teachers, students, parents and leaders can all start the benchmark below.', 'ai-awareness-day' ),
			$content
		);
		$content = str_replace(
			"discover your school's actual AI exposure — try the benchmark below.",
			__( 'discover your actual AI exposure — teachers, students, parents and leaders can all start the benchmark below.', 'ai-awareness-day' ),
			$content
		);
		$content = str_replace(
			'discover your school’s actual AI exposure — start the benchmark below.',
			__( 'discover your actual AI exposure — teachers, students, parents and leaders can all start the benchmark below.', 'ai-awareness-day' ),
			$content
		);

		wp_update_post(
			array(
				'ID'           => (int) $post->ID,
				'post_content' => $content,
			),
			true
		);
	}

	update_option( 'aiad_risk_benchmark_audit_heading_backfilled', 'yes' );
}
add_action( 'init', 'aiad_backfill_risk_benchmark_audit_heading', 35 );

/**
 * One-time backfill: remove the school dashboard shortcode from the launch article.
 */
function aiad_backfill_risk_benchmark_remove_school_dashboard(): void {
	if ( get_option( 'aiad_risk_benchmark_school_dashboard_removed' ) === 'yes' ) {
		return;
	}

	$post = get_page_by_path( aiad_risk_benchmark_post_slug(), OBJECT, 'timeline' );
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$content = (string) $post->post_content;
	$changed = false;

	$patterns = array(
		'/<!--\s*wp:shortcode\s*-->\s*\[ai_risk_school_dashboard[^\]]*\]\s*<!--\s*\/wp:shortcode\s*-->\s*/i',
		'/\[ai_risk_school_dashboard[^\]]*\]\s*/i',
	);

	foreach ( $patterns as $pattern ) {
		$updated = preg_replace( $pattern, '', $content );
		if ( is_string( $updated ) && $updated !== $content ) {
			$content = $updated;
			$changed = true;
		}
	}

	$intro = '<!-- wp:paragraph --><p>Once your school has completed audits across all four groups, your live dashboard appears here:</p><!-- /wp:paragraph -->';
	if ( str_contains( $content, $intro ) ) {
		$content = str_replace( $intro, '', $content );
		$changed = true;
	}

	if ( $changed ) {
		wp_update_post(
			array(
				'ID'           => (int) $post->ID,
				'post_content' => $content,
			),
			true
		);
	}

	update_option( 'aiad_risk_benchmark_school_dashboard_removed', 'yes' );
}
add_action( 'init', 'aiad_backfill_risk_benchmark_remove_school_dashboard', 36 );
