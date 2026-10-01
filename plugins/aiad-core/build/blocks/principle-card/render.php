<?php
/**
 * Principle card: the same markup as a card in template-parts/front-page/section-principles.php. The whole card is
 * a link to the activities, except the closing "literacy" card, which shows the literacy logo.
 *
 * An empty title or description shows the site's wording for that strand (aiad_principle_card_wording()).
 *
 * @package AI_Awareness_Day
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

$aiad_order  = array_flip( array_keys( aiad_principle_cards() ) );
$aiad_strand = isset( $aiad_order[ $attributes['strand'] ?? '' ] ) ? $attributes['strand'] : 'safe';
$aiad_site   = aiad_principle_card_wording( $aiad_strand );
$aiad_title  = trim( (string) ( $attributes['title'] ?? '' ) );
$aiad_text   = trim( (string) ( $attributes['text'] ?? '' ) );
$aiad_title  = '' !== $aiad_title ? wp_kses_post( $aiad_title ) : esc_html( $aiad_site[0] );
$aiad_text   = '' !== $aiad_text ? wp_kses_post( $aiad_text ) : esc_html( $aiad_site[1] );
$aiad_class  = 'fade-up stagger-' . ( $aiad_order[ $aiad_strand ] + 1 );

if ( 'literacy' === $aiad_strand ) :
	$aiad_logo = aiad_get_logo_image_url( aiad_get_literacy_logo_attachment_id(), 'medium' );
	?>
<div class="ai-literacy-box principle-card <?php echo esc_attr( $aiad_class ); ?>">
	<div class="principle-badge">
		<?php if ( $aiad_logo ) : ?>
			<img src="<?php echo esc_url( $aiad_logo ); ?>" alt="" aria-hidden="true" class="principle-badge__img" onerror="this.classList.add('is-broken');" />
		<?php else : ?>
			<div class="principle-badge__placeholder" aria-hidden="true">
				<span class="principle-badge__placeholder-text"><?php esc_html_e( 'AI', 'ai-awareness-day' ); ?></span>
			</div>
		<?php endif; ?>
	</div>
	<h3><?php echo $aiad_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></h3>
	<p class="section-desc"><?php echo $aiad_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
</div>
	<?php
else :
	/* translators: %s: strand name e.g. Safe */
	$aiad_label = sprintf( __( 'Explore %s activities', 'ai-awareness-day' ), wp_strip_all_tags( $aiad_title ) );
	?>
<a href="#themes" class="principle-card principle-card--<?php echo esc_attr( $aiad_strand . ' ' . $aiad_class ); ?>" aria-label="<?php echo esc_attr( $aiad_label ); ?>">
	<div class="principle-badge">
		<img src="<?php echo esc_url( aiad_strand_icon_uri( $aiad_strand ) ); ?>" alt="" aria-hidden="true" class="principle-badge__img" />
	</div>
	<h3><?php echo $aiad_title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></h3>
	<p class="section-desc"><?php echo $aiad_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></p>
</a>
	<?php
endif;
