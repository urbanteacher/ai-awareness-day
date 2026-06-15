<?php
/**
 * Homepage benchmark promo — between display board and activities.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$benchmark_url = aiad_get_benchmark_start_url();
$roles         = aiad_benchmark_promo_roles();
?>
<section id="benchmark-audit" class="benchmark-promo" aria-label="<?php esc_attr_e( 'AI Risk & Readiness Benchmark', 'ai-awareness-day' ); ?>">
	<div class="container">
		<div class="campaign-split survey-split benchmark-split fade-up">
			<div class="campaign-content">
				<p class="benchmark-promo__eyebrow">
					<span class="benchmark-promo__dot" aria-hidden="true"></span>
					<?php esc_html_e( 'New · Free for UK schools', 'ai-awareness-day' ); ?>
				</p>
				<h2 class="benchmark-promo__title">
					<?php esc_html_e( "Assess your school's AI usage", 'ai-awareness-day' ); ?>
				</h2>
				<p class="benchmark-promo__desc">
					<?php esc_html_e( 'Take the AI Risk & Readiness Benchmark™ — a free interactive audit to measure adoption, dependency and readiness across your whole school community.', 'ai-awareness-day' ); ?>
				</p>

				<?php if ( ! empty( $roles ) ) : ?>
					<div class="benchmark-promo__roles" role="list" aria-label="<?php esc_attr_e( 'Choose your role', 'ai-awareness-day' ); ?>">
						<?php foreach ( $roles as $role ) : ?>
							<a
								href="<?php echo esc_url( $role['url'] ); ?>"
								class="benchmark-promo__role"
								role="listitem"
							>
								<?php echo esc_html( $role['label'] ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="survey-cta-col">
				<div class="hero-cta survey-cta">
					<a class="hero-cta__btn hero-cta__btn--primary hero-cta__btn--pointed" href="<?php echo esc_url( $benchmark_url ); ?>">
						<?php esc_html_e( 'Start the free audit', 'ai-awareness-day' ); ?>
					</a>
				</div>
			</div>
		</div>
	</div>
</section>
