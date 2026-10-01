<?php
/**
 * Front-end output for aiad/partner-actions: the Visit website button and the link back to the partners.
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
if ( ! $aiad_partner_id ) {
	return;
}

$aiad_partner_url = (string) get_post_meta( $aiad_partner_id, '_partner_url', true );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__actions' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $aiad_partner_url ) : ?>
		<a href="<?php echo esc_url( $aiad_partner_url ); ?>" class="btn-submit partner-bio__cta" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Visit website', 'ai-awareness-day' ); ?>
		</a>
	<?php endif; ?>
	<a href="<?php echo esc_url( get_post_type_archive_link( 'partner' ) ); ?>" class="partner-bio__back">
		<?php esc_html_e( 'Back to Partners', 'ai-awareness-day' ); ?>
	</a>
</div>
