<?php
/**
 * Shortcode shell markup.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$config            = AIRB_Config::get();
$framework         = (array) ( $config['framework'] ?? array() );
$domain_sources    = (array) ( $config['domain_sources'] ?? array() );
$positioning       = (array) ( $config['positioning'] ?? array() );
$domains           = AIRB_Defaults::domains();
$domain_desc       = (array) ( $config['domain_descriptions'] ?? array() );
$guidance          = (array) ( $config['guidance_refs'] ?? array() );
$roles             = AIRB_Defaults::roles();
$role_benchmarks   = (array) ( $config['role_benchmarks'] ?? array() );
$signature_metrics = (array) ( $config['signature_metrics'] ?? array() );
$after_audit       = (array) ( $config['after_audit'] ?? array() );
$services          = (array) ( $config['services'] ?? array() );
$aad               = (array) ( $config['aad_2027'] ?? array() );
$problem_questions = (array) ( $positioning['problem_questions'] ?? array() );
?>
<div class="airb" id="airb-benchmark" data-airb-root aria-label="<?php esc_attr_e( 'AI Risk & Readiness Benchmark', 'ai-risk-benchmark' ); ?>">

	<div class="airb__intro">
		<p class="airb__eyebrow"><?php esc_html_e( 'DfE-aligned · England · Free educational self-assessment', 'ai-risk-benchmark' ); ?></p>
		<?php if ( ! empty( $framework['product_name'] ) ) : ?>
			<p class="airb__brand airb__brand--hero"><?php echo esc_html( (string) $framework['product_name'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $framework['subtitle'] ) ) : ?>
			<p class="airb__framework-sub"><?php echo esc_html( (string) $framework['subtitle'] ); ?></p>
		<?php endif; ?>
		<h2 class="airb__title"><?php echo esc_html( (string) ( $positioning['headline'] ?? __( 'AI Risk & Readiness Benchmark', 'ai-risk-benchmark' ) ) ); ?></h2>
		<?php if ( ! empty( $framework['statement'] ) ) : ?>
			<p class="airb__lead airb__lead--framework"><?php echo esc_html( (string) $framework['statement'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $config['disclaimer'] ) ) : ?>
			<p class="airb__disclaimer"><?php echo esc_html( (string) $config['disclaimer'] ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $positioning['tagline'] ) ) : ?>
			<p class="airb__tagline"><?php echo esc_html( (string) $positioning['tagline'] ); ?></p>
		<?php endif; ?>
	</div>

	<section class="airb__deck" id="airb-deck" data-deck aria-roledescription="carousel" aria-label="<?php esc_attr_e( 'How it works', 'ai-risk-benchmark' ); ?>" tabindex="0">
		<div class="airb__deck-head">
			<h3 class="airb__section-kicker"><?php esc_html_e( 'How it works', 'ai-risk-benchmark' ); ?></h3>
			<p class="airb__muted"><?php esc_html_e( 'A quick tour — swipe or use the arrows.', 'ai-risk-benchmark' ); ?></p>
		</div>

		<div class="airb__deck-viewport">
			<div class="airb__deck-track" data-deck-track>

				<?php if ( ! empty( $positioning['problem'] ) || ! empty( $positioning['solution'] ) ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'The challenge', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<?php if ( ! empty( $positioning['problem'] ) ) : ?>
							<h4 class="airb__slide-title"><?php esc_html_e( 'The problem', 'ai-risk-benchmark' ); ?></h4>
							<p class="airb__lead"><?php echo esc_html( (string) $positioning['problem'] ); ?></p>
							<?php if ( $problem_questions ) : ?>
								<ul class="airb__problem-list">
									<?php foreach ( $problem_questions as $question ) : ?>
										<li><?php echo esc_html( (string) $question ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( ! empty( $positioning['problem_closing'] ) ) : ?>
								<p class="airb__lead"><?php echo esc_html( (string) $positioning['problem_closing'] ); ?></p>
							<?php endif; ?>
						<?php endif; ?>
						<?php if ( ! empty( $positioning['solution'] ) ) : ?>
							<p class="airb__brand airb__slide-solution"><?php echo esc_html( (string) $positioning['solution'] ); ?></p>
							<?php if ( ! empty( $positioning['solution_detail'] ) ) : ?>
								<p class="airb__lead airb__lead--solution"><?php echo esc_html( (string) $positioning['solution_detail'] ); ?></p>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( $roles ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'Four stakeholder groups', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php esc_html_e( 'Four stakeholder groups', 'ai-risk-benchmark' ); ?></h4>
						<p class="airb__muted"><?php esc_html_e( 'Each group completes a tailored audit, scored against the same eight domains.', 'ai-risk-benchmark' ); ?></p>
						<div class="airb__stakeholder-grid">
							<?php foreach ( $roles as $slug => $label ) : ?>
								<span class="airb__stakeholder"><?php echo esc_html( $label ); ?></span>
							<?php endforeach; ?>
						</div>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( $domains ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'The eight benchmark domains', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php esc_html_e( 'Eight DfE-aligned domains', 'ai-risk-benchmark' ); ?></h4>
						<div class="airb__domain-grid">
							<?php
							$i = 1;
							foreach ( $domains as $slug => $label ) :
								?>
								<div class="airb__domain-card">
									<strong><span class="airb__domain-num"><?php echo esc_html( (string) $i ); ?>.</span> <?php echo esc_html( $label ); ?></strong>
									<?php if ( ! empty( $domain_desc[ $slug ] ) ) : ?>
										<p><?php echo esc_html( (string) $domain_desc[ $slug ] ); ?></p>
									<?php endif; ?>
								</div>
								<?php
								++$i;
							endforeach;
							?>
						</div>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( $domains && $domain_sources ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'Benchmark sources', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php esc_html_e( 'Benchmarked against published guidance', 'ai-risk-benchmark' ); ?></h4>
						<table class="airb__sources-table">
							<thead>
								<tr>
									<th scope="col"><?php esc_html_e( 'Domain', 'ai-risk-benchmark' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Benchmark source', 'ai-risk-benchmark' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $domains as $slug => $label ) : ?>
									<?php if ( empty( $domain_sources[ $slug ] ) ) { continue; } ?>
									<tr>
										<td><?php echo esc_html( $label ); ?></td>
										<td><?php echo esc_html( (string) $domain_sources[ $slug ] ); ?></td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<?php if ( ! empty( $framework['annual_note'] ) ) : ?>
							<p class="airb__muted airb__annual-note"><?php echo esc_html( (string) $framework['annual_note'] ); ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( ! empty( $signature_metrics['dependency'] ) || ! empty( $signature_metrics['oversight'] ) ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'Signature metrics', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php esc_html_e( 'The signature metrics', 'ai-risk-benchmark' ); ?></h4>
						<div class="airb__signature-grid">
							<?php foreach ( array( 'dependency', 'oversight' ) as $metric_key ) : ?>
								<?php
								$metric = (array) ( $signature_metrics[ $metric_key ] ?? array() );
								if ( empty( $metric['title'] ) ) {
									continue;
								}
								?>
								<div class="airb__signature-card">
									<h5><?php echo esc_html( (string) $metric['title'] ); ?></h5>
									<?php if ( ! empty( $metric['tagline'] ) ) : ?>
										<p class="airb__signature-tagline"><?php echo esc_html( (string) $metric['tagline'] ); ?></p>
									<?php endif; ?>
									<?php if ( ! empty( $metric['bands'] ) ) : ?>
										<ul class="airb__oversight-bands">
											<?php foreach ( (array) $metric['bands'] as $band ) : ?>
												<li><strong><?php echo esc_html( (string) ( $band['range'] ?? '' ) ); ?></strong> — <?php echo esc_html( (string) ( $band['label'] ?? '' ) ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
									<?php if ( ! empty( $metric['measures'] ) ) : ?>
										<ul>
											<?php foreach ( (array) $metric['measures'] as $item ) : ?>
												<li><?php echo esc_html( (string) $item ); ?></li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
						<?php if ( ! empty( $signature_metrics['footnote'] ) ) : ?>
							<p class="airb__muted airb__signature-foot"><?php echo esc_html( (string) $signature_metrics['footnote'] ); ?></p>
						<?php endif; ?>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( $guidance ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'Aligned to UK guidance', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php esc_html_e( 'Aligned to published UK guidance', 'ai-risk-benchmark' ); ?></h4>
						<div class="airb__guidance-chips">
							<?php foreach ( $guidance as $ref ) : ?>
								<?php if ( empty( $ref['url'] ) ) { continue; } ?>
								<a class="airb__chip" href="<?php echo esc_url( (string) $ref['url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( (string) ( $ref['label'] ?? '' ) ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				</article>
				<?php endif; ?>

				<?php if ( ! empty( $after_audit['headline'] ) || ! empty( $services['items'] ) ) : ?>
				<article class="airb__slide" role="group" aria-roledescription="slide" aria-label="<?php esc_attr_e( 'After your audit', 'ai-risk-benchmark' ); ?>">
					<div class="airb__slide-inner">
						<h4 class="airb__slide-title"><?php echo esc_html( (string) ( $after_audit['headline'] ?? __( 'After your audit', 'ai-risk-benchmark' ) ) ); ?></h4>
						<?php if ( ! empty( $after_audit['intro'] ) ) : ?>
							<p class="airb__lead"><?php echo esc_html( (string) $after_audit['intro'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $after_audit['examples'] ) ) : ?>
							<ul class="airb__after-examples">
								<?php foreach ( (array) $after_audit['examples'] as $ex ) : ?>
									<li><strong><?php echo esc_html( (string) ( $ex['trigger'] ?? '' ) ); ?>:</strong> <?php echo esc_html( (string) ( $ex['offer'] ?? '' ) ); ?></li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
						<?php if ( ! empty( $after_audit['principle'] ) ) : ?>
							<p class="airb__principle"><?php echo esc_html( (string) $after_audit['principle'] ); ?></p>
						<?php endif; ?>
						<?php if ( ! empty( $services['items'] ) ) : ?>
							<p class="airb__muted airb__services-head"><?php echo esc_html( (string) ( $services['headline'] ?? __( 'When you need more support', 'ai-risk-benchmark' ) ) ); ?></p>
							<ul class="airb__services-list">
								<?php foreach ( (array) $services['items'] as $item ) : ?>
									<li>
										<?php if ( ! empty( $item['url'] ) ) : ?>
											<a href="<?php echo esc_url( (string) $item['url'] ); ?>"><?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?></a>
										<?php else : ?>
											<?php echo esc_html( (string) ( $item['label'] ?? '' ) ); ?>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</article>
				<?php endif; ?>

			</div>
		</div>

		<div class="airb__deck-controls">
			<button type="button" class="airb__deck-btn" data-deck-prev aria-label="<?php esc_attr_e( 'Previous slide', 'ai-risk-benchmark' ); ?>">
				<span aria-hidden="true">&#8249;</span>
			</button>
			<div class="airb__deck-dots" data-deck-dots role="tablist" aria-label="<?php esc_attr_e( 'Slides', 'ai-risk-benchmark' ); ?>"></div>
			<button type="button" class="airb__deck-btn" data-deck-next aria-label="<?php esc_attr_e( 'Next slide', 'ai-risk-benchmark' ); ?>">
				<span aria-hidden="true">&#8250;</span>
			</button>
			<span class="airb__deck-counter" data-deck-counter aria-live="polite"></span>
		</div>
	</section>

	<div class="airb__progress" id="airb-progress" hidden>
		<div class="airb__stepper" id="airb-stepper" role="list" aria-hidden="true"></div>
		<p class="airb__progress-label" id="airb-progress-label" aria-live="polite"></p>
	</div>

	<div class="airb__screen" id="airb-screen-role"></div>
	<div class="airb__screen" id="airb-screen-audit" hidden></div>
	<div class="airb__screen" id="airb-screen-contact" hidden></div>
	<div class="airb__screen" id="airb-screen-results" hidden></div>

	<div class="airb__nav" id="airb-nav" hidden>
		<button type="button" class="airb__btn airb__btn--ghost" id="airb-back" hidden><?php esc_html_e( 'Back', 'ai-risk-benchmark' ); ?></button>
		<button type="button" class="airb__btn airb__btn--primary" id="airb-next"><?php esc_html_e( 'Next', 'ai-risk-benchmark' ); ?></button>
	</div>

	<div class="airb__error" id="airb-error" role="alert" hidden></div>

	<div class="airb__print-host" id="airb-print-host" hidden aria-hidden="true"></div>

	<footer class="airb__footer">
		<div class="airb__footer-main">
			<p class="airb__footer-brand"><?php echo esc_html( (string) ( $framework['product_name'] ?? __( 'AI Risk & Readiness Benchmark™', 'ai-risk-benchmark' ) ) ); ?></p>
			<?php if ( ! empty( $config['disclaimer'] ) ) : ?>
				<p class="airb__footer-disclaimer"><?php echo esc_html( (string) $config['disclaimer'] ); ?></p>
			<?php endif; ?>
		</div>
		<p class="airb__credit">
			<?php esc_html_e( 'Produced by', 'ai-risk-benchmark' ); ?>
			<strong><?php esc_html_e( 'AI Awareness Day', 'ai-risk-benchmark' ); ?></strong>
			&middot;
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( wp_parse_url( home_url(), PHP_URL_HOST ) ?: 'aiawarenessday.co.uk' ); ?></a>
		</p>
	</footer>
</div>
