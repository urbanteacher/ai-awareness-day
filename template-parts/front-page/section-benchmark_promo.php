<?php
/**
 * Front page section: AI Risk & Readiness Benchmark promo.
 *
 * Bold feature band with role chips, placed right after the hero.
 * Resolves the benchmark URL via aiad_get_benchmark_url() so it works
 * whether the post was seeded or created manually in the editor.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$_bm_url = function_exists( 'aiad_get_benchmark_url' ) ? aiad_get_benchmark_url() : '';
if ( ! $_bm_url ) {
	return;
}
?>
<section class="aiad-bm-promo" id="benchmark" aria-label="<?php esc_attr_e( 'AI Risk &amp; Readiness Benchmark', 'ai-awareness-day' ); ?>">
	<div class="container">
		<div class="aiad-bm-promo__inner fade-up">

			<div class="aiad-bm-promo__body">
				<span class="aiad-bm-promo__eyebrow">
					<span class="aiad-bm-promo__pulse" aria-hidden="true"></span>
					<?php esc_html_e( 'New &middot; Free for UK Schools', 'ai-awareness-day' ); ?>
				</span>

				<h2 class="aiad-bm-promo__headline">
					<?php esc_html_e( 'How exposed is your school to AI risk?', 'ai-awareness-day' ); ?>
				</h2>

				<p class="aiad-bm-promo__desc">
					<?php esc_html_e( 'Take the AI Risk & Readiness Benchmark™ — the UK\'s first DfE-aligned audit. Get your AI Dependency Index™, Human Oversight Ratio™ and DfE Alignment Score in under 15 minutes. Completely free.', 'ai-awareness-day' ); ?>
				</p>

				<nav class="aiad-bm-promo__roles" aria-label="<?php esc_attr_e( 'Start the benchmark as', 'ai-awareness-day' ); ?>">
					<?php
					$_roles = array(
						'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
						'student'       => __( 'Student', 'ai-awareness-day' ),
						'parent'        => __( 'Parent / Carer', 'ai-awareness-day' ),
						'leader'        => __( 'School Leader', 'ai-awareness-day' ),
						'support_staff' => __( 'Support Staff', 'ai-awareness-day' ),
					);
					foreach ( $_roles as $_slug => $_label ) :
					?>
						<a href="<?php echo esc_url( $_bm_url ); ?>"
						   class="aiad-bm-promo__chip aiad-bm-promo__chip--<?php echo esc_attr( $_slug ); ?>">
							<?php echo esc_html( $_label ); ?>
						</a>
					<?php endforeach; ?>
				</nav>

				<a href="<?php echo esc_url( $_bm_url ); ?>" class="aiad-bm-promo__cta">
					<?php esc_html_e( 'Start the free audit', 'ai-awareness-day' ); ?>
					<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false">
						<path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			</div>

			<div class="aiad-bm-promo__tiles" aria-hidden="true">
				<div class="aiad-bm-promo__tile aiad-bm-promo__tile--a">
					<span class="aiad-bm-promo__tile-val">DfE</span>
					<span class="aiad-bm-promo__tile-lab"><?php esc_html_e( 'Aligned', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="aiad-bm-promo__tile aiad-bm-promo__tile--b">
					<span class="aiad-bm-promo__tile-val">15<small><?php esc_html_e( 'min', 'ai-awareness-day' ); ?></small></span>
					<span class="aiad-bm-promo__tile-lab"><?php esc_html_e( 'Audit', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="aiad-bm-promo__tile aiad-bm-promo__tile--c">
					<span class="aiad-bm-promo__tile-val"><?php esc_html_e( 'Free', 'ai-awareness-day' ); ?></span>
					<span class="aiad-bm-promo__tile-lab"><?php esc_html_e( 'For schools', 'ai-awareness-day' ); ?></span>
				</div>
				<div class="aiad-bm-promo__tile aiad-bm-promo__tile--d">
					<span class="aiad-bm-promo__tile-val">5</span>
					<span class="aiad-bm-promo__tile-lab"><?php esc_html_e( 'Roles', 'ai-awareness-day' ); ?></span>
				</div>
			</div>

		</div>
	</div>
</section>
