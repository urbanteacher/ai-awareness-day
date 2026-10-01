<?php
/**
 * Front-end output for aiad/partner-links: the partner's resource links grouped by strand.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 * @var string               $content    Block content.
 * @var WP_Block             $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$aiad_partner_id = aiad_partner_profile_post_id( $block );
$aiad_grouped    = $aiad_partner_id ? aiad_partner_profile_links( $aiad_partner_id ) : array();
if ( empty( $aiad_grouped ) ) {
	return;
}

$aiad_theme_labels = aiad_partner_profile_theme_labels();
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__resources', 'aria-label' => __( 'Partner resources', 'ai-awareness-day' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h2 class="partner-bio__resources-title"><?php esc_html_e( 'Partner resources', 'ai-awareness-day' ); ?></h2>
	<div class="partner-bio__resources-grid">
		<?php foreach ( $aiad_grouped as $aiad_slug => $aiad_links ) : ?>
			<section class="partner-bio__theme">
				<h3 class="partner-bio__theme-title">
					<span class="partner-bio__theme-pill partner-bio__theme-pill--<?php echo esc_attr( $aiad_slug ); ?>"><?php echo esc_html( $aiad_theme_labels[ $aiad_slug ] ); ?></span>
				</h3>
				<ul class="partner-bio__link-list">
					<?php foreach ( $aiad_links as $aiad_link ) : ?>
						<li class="partner-bio__link-item">
							<a class="partner-bio__link" href="<?php echo esc_url( $aiad_link['url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<span class="partner-bio__link-title"><?php echo esc_html( $aiad_link['title'] ); ?></span>
								<?php if ( $aiad_link['duration'] ) : ?>
									<span class="partner-bio__link-duration"><?php echo esc_html( $aiad_link['duration'] ); ?></span>
								<?php endif; ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</section>
		<?php endforeach; ?>
	</div>
</div>
