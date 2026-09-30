<?php
/**
 * Certificate showcase block.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$a            = is_array( $args ) ? $args : array();
$section_id   = (string) ( $a['section_id'] ?? '' );
$eyebrow      = (string) ( $a['eyebrow'] ?? '' );
$title        = (string) ( $a['title'] ?? '' );
$lead         = (string) ( $a['lead'] ?? '' );
$roles        = (array) ( $a['roles'] ?? array() );
$cta_url      = (string) ( $a['cta_url'] ?? '' );
$cta_text     = (string) ( $a['cta_text'] ?? '' );
$cert_eyebrow = (string) ( $a['cert_eyebrow'] ?? '' );
$cert_title   = (string) ( $a['cert_title'] ?? '' );
$cert_lead    = (string) ( $a['cert_lead'] ?? '' );
$steps        = (array) ( $a['steps'] ?? array() );
$strand       = (string) ( $a['strand'] ?? 'safe' );
$tone         = 'ink' === ( $a['tone'] ?? 'cream' ) ? 'ink' : 'cream';
$heading_id   = 'cert-showcase-title';
?>
<section
	<?php echo '' !== $section_id ? 'id="' . esc_attr( $section_id ) . '"' : ''; ?>
	class="cert-showcase cert-showcase--<?php echo esc_attr( $tone ); ?>"
	aria-labelledby="<?php echo esc_attr( $heading_id ); ?>"
>
	<div class="container">
		<div class="cert-showcase__grid">
			<div class="cert-showcase__copy">
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="cert-showcase__eyebrow">
						<span class="cert-showcase__dot" aria-hidden="true"></span>
						<?php echo esc_html( $eyebrow ); ?>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $title ) : ?>
					<h2 class="cert-showcase__title" id="<?php echo esc_attr( $heading_id ); ?>"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>

				<?php if ( '' !== $lead ) : ?>
					<p class="cert-showcase__lead"><?php echo esc_html( $lead ); ?></p>
				<?php endif; ?>

				<?php
				/*
				 * Named rather than linked: six buttons cost more room than
				 * they earn next to the artwork, and the audit asks for the
				 * role on its own first screen anyway.
				 */
				$role_labels = array();
				foreach ( $roles as $role ) {
					if ( ! empty( $role['label'] ) ) {
						$role_labels[] = (string) $role['label'];
					}
				}
				?>
				<?php if ( ! empty( $role_labels ) ) : ?>
					<p class="cert-showcase__roles-note">
						<?php echo esc_html( implode( '  ·  ', $role_labels ) ); ?>
					</p>
				<?php endif; ?>

				<?php if ( '' !== $cta_url && '' !== $cta_text ) : ?>
					<p class="cert-showcase__cta">
						<a class="cert-showcase__btn" href="<?php echo esc_url( $cta_url ); ?>">
							<?php echo esc_html( $cta_text ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<?php // Filled and drawn by the benchmark plugin's renderer on load. ?>
			<figure class="cert-showcase__art">
				<div data-airb-cert-showcase-art data-cert-theme="<?php echo esc_attr( $strand ); ?>"></div>
				<figcaption class="cert-showcase__caption">
					<?php esc_html_e( 'Example — your name and strand appear on the certificate you earn.', 'ai-awareness-day' ); ?>
				</figcaption>
			</figure>
		</div>

		<?php if ( '' !== $cert_title || ! empty( $steps ) ) : ?>
			<div class="cert-showcase__earn">
				<div class="cert-showcase__earn-head">
					<?php if ( '' !== $cert_eyebrow ) : ?>
						<p class="cert-showcase__eyebrow"><?php echo esc_html( $cert_eyebrow ); ?></p>
					<?php endif; ?>
					<?php if ( '' !== $cert_title ) : ?>
						<h3 class="cert-showcase__earn-title"><?php echo esc_html( $cert_title ); ?></h3>
					<?php endif; ?>
					<?php if ( '' !== $cert_lead ) : ?>
						<p class="cert-showcase__earn-lead"><?php echo esc_html( $cert_lead ); ?></p>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $steps ) ) : ?>
					<ol class="cert-showcase__steps">
						<?php foreach ( $steps as $step ) : ?>
							<li><?php echo esc_html( (string) $step ); ?></li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
