<?php
/**
 * Front-end output for aiad/partner-intro: the profile intro, or the partner's page content when there is no intro.
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

$aiad_partner_intro = (string) get_post_meta( $aiad_partner_id, '_partner_profile_intro', true );
if ( '' !== $aiad_partner_intro ) {
	?>
	<p <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__intro' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( $aiad_partner_intro ); ?></p>
	<?php
	return;
}

$aiad_partner_body = (string) get_post_field( 'post_content', $aiad_partner_id );
if ( '' === trim( $aiad_partner_body ) ) {
	return;
}
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'partner-bio__content entry-content' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo apply_filters( 'the_content', $aiad_partner_body ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core's own content filter. ?>
</div>
