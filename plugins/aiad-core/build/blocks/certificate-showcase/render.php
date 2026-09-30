<?php
/**
 * Front-end output for aiad/certificate-showcase.
 *
 * Calls the shortcode function, so the block and [aiad_certificate_showcase] always produce the same markup. Empty
 * settings fall back to the showcase's default wording, as they do in the shortcode.
 *
 * @package AIAD_Core
 *
 * @var array<string, mixed> $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'aiad_certificate_showcase_shortcode' ) ) {
	return;
}

$aiad_core_showcase = aiad_certificate_showcase_shortcode(
	array(
		'eyebrow'      => (string) ( $attributes['eyebrow'] ?? '' ),
		'title'        => (string) ( $attributes['title'] ?? '' ),
		'lead'         => (string) ( $attributes['lead'] ?? '' ),
		'cta_text'     => (string) ( $attributes['ctaText'] ?? '' ),
		'cta_url'      => (string) ( $attributes['ctaUrl'] ?? '' ),
		'cert_eyebrow' => (string) ( $attributes['certEyebrow'] ?? '' ),
		'cert_title'   => (string) ( $attributes['certTitle'] ?? '' ),
		'cert_lead'    => (string) ( $attributes['certLead'] ?? '' ),
		'strand'       => in_array( $attributes['strand'] ?? 'safe', array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ? $attributes['strand'] : 'safe',
		'tone'         => in_array( $attributes['tone'] ?? '', array( '', 'ink', 'cream' ), true ) ? $attributes['tone'] : '',
		'section_id'   => (string) ( $attributes['sectionId'] ?? '' ),
	)
);

if ( '' === $aiad_core_showcase ) {
	return; // No benchmark plugin, no certificate art: the showcase draws nothing, as the shortcode does.
}
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo $aiad_core_showcase; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- the template escapes its own output. ?>
</div>
