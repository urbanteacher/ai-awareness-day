<?php
/**
 * Front-end output for aiad/partner-logo: the partner's featured image, linking to its website when it has one.
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
if ( ! $aiad_partner_id || ! has_post_thumbnail( $aiad_partner_id ) ) {
	return;
}

$aiad_partner_url  = (string) get_post_meta( $aiad_partner_id, '_partner_url', true );
$aiad_partner_logo = get_the_post_thumbnail( $aiad_partner_id, 'medium', array( 'class' => 'partner-bio__logo-img' ) );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__logo' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $aiad_partner_url ) : ?>
		<a href="<?php echo esc_url( $aiad_partner_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo $aiad_partner_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
	<?php else : ?>
		<?php echo $aiad_partner_logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
