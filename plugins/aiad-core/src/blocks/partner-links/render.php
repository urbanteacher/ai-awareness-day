<?php
/**
 * Front-end output for aiad/partner-links: the partner's resource links, one plain list.
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

// One plain list: the strand each link was filed under is for the editor, not shown here.
$aiad_links = array_merge( ...array_values( $aiad_grouped ) );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__resources', 'aria-label' => __( 'Partner resources', 'ai-awareness-day' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h2 class="partner-bio__resources-title"><?php esc_html_e( 'Partner resources', 'ai-awareness-day' ); ?></h2>
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
</div>
