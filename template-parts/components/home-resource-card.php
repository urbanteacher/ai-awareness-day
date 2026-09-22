<?php
/**
 * Homepage resource card, for use inside the loop.
 *
 * The homepage's three resource grids each carried their own copy of the old
 * pointed card. This is the one card they now share. It shows the photo as it
 * is, with no colour wash and no title laid over it. The strand sits in an ink
 * pill, which reads on any photo, and the title appears once, below. A resource
 * with no photo gets its strand's poster graphic instead of a flat dark box.
 *
 * The title link is the only link. Its ::after covers the whole card, so the
 * card is one click target and one tab stop, not two links to the same place.
 *
 * Args:
 *   link     string  Where the card goes. Defaults to the permalink.
 *   external bool    Opens in a new tab and says so to screen readers.
 *   track_id int     Adds data-featured-resource-id, which
 *                    assets/js/engagement-tracking.js counts clicks by.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$card_link     = ! empty( $args['link'] ) ? $args['link'] : get_permalink();
$card_external = ! empty( $args['external'] );
$card_track_id = ! empty( $args['track_id'] ) ? absint( $args['track_id'] ) : 0;

$card_strands = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
$card_themes  = get_the_terms( get_the_ID(), 'resource_principle' );
$card_theme   = ( $card_themes && ! is_wp_error( $card_themes ) ) ? $card_themes[0]->name : '';
$card_strand  = in_array( strtolower( $card_theme ), $card_strands, true ) ? strtolower( $card_theme ) : '';

// Format: shown only when set. The old cards printed "SLIDE" for anything
// without an activity type, which labelled games and tools as slides.
$card_formats = get_the_terms( get_the_ID(), 'activity_type' );
$card_format  = ( $card_formats && ! is_wp_error( $card_formats ) ) ? $card_formats[0]->name : '';

// Duration: the badge map first, then the two ways the term labels spell a time.
$card_duration  = '';
$card_durations = get_the_terms( get_the_ID(), 'resource_duration' );
if ( $card_durations && ! is_wp_error( $card_durations ) ) {
	$card_parts = function_exists( 'aiad_duration_badge_parts' ) ? aiad_duration_badge_parts( $card_durations[0] ) : null;
	$card_label = function_exists( 'aiad_resource_duration_term_labels' ) ? ( aiad_resource_duration_term_labels( $card_durations )[0] ?? '' ) : '';
	if ( $card_parts ) {
		$card_duration = $card_parts['time'];
	} elseif ( preg_match( '/\(([^)]+)\)/', $card_label, $m ) ) {
		$card_duration = trim( $m[1] );
	} elseif ( preg_match( '/(\d+(?:[\-–]\d+)?\s*min(?:ute)?s?)/i', $card_label, $m ) ) {
		$card_duration = $m[1];
	}
}
$card_meta = array_filter( array( $card_format, $card_duration ) );

$card_title = html_entity_decode( get_the_title(), ENT_QUOTES, 'UTF-8' );
?>
<article class="home-card fade-up<?php echo $card_strand ? ' home-card--' . esc_attr( $card_strand ) : ''; ?>">
	<div class="home-card__hero" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			// Decorative: the title below names the card.
			the_post_thumbnail( 'medium_large', array( 'class' => 'home-card__photo', 'alt' => '', 'loading' => 'lazy' ) );
		} else {
			echo aiad_strand_poster_svg( $card_strand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from fixed paths.
		}
		?>
		<?php if ( $card_strand ) : ?>
			<span class="home-card__strand"><?php echo aiad_strand_icon_svg( $card_strand, 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( strtoupper( $card_theme ) ); ?></span>
		<?php endif; ?>
	</div>

	<div class="home-card__body">
		<?php // Always present, even empty, so titles line up across a row. ?>
		<p class="home-card__meta"><?php echo esc_html( implode( ' · ', $card_meta ) ); ?></p>
		<h3 class="home-card__title">
			<a href="<?php echo esc_url( $card_link ); ?>"
				<?php if ( $card_track_id ) : ?>data-featured-resource-id="<?php echo esc_attr( (string) $card_track_id ); ?>"<?php endif; ?>
				<?php if ( $card_external ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
				<?php echo esc_html( $card_title ); ?>
				<?php if ( $card_strand ) : ?><span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: strand name */ __( '(%s strand)', 'ai-awareness-day' ), $card_theme ) ); ?></span><?php endif; ?>
				<?php if ( $card_external ) : ?><span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ai-awareness-day' ); ?></span><?php endif; ?>
			</a>
		</h3>
		<?php if ( has_excerpt() ) : ?>
			<p class="home-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</article>
