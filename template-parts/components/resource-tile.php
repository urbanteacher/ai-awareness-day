<?php
/**
 * Resource card, for use inside the loop.
 *
 * The homepage's three resource grids, the /resources library and the featured
 * archive each carried their own copy of the old pointed card, and
 * resource-filters.js kept a fifth. This is the one card they all now share. The
 * archives' AJAX filter renders it too (aiad_ajax_filter_resources() returns its
 * HTML), so a filtered page cannot drift from the first one. It shows the photo as it
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

// The hand-written excerpt, else the first 30 words, as the library always did.
$card_summary = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content( '' ) ), 30 );
?>
<article class="resource-tile fade-up<?php echo $card_strand ? ' resource-tile--' . esc_attr( $card_strand ) : ''; ?>">
	<div class="resource-tile__hero" aria-hidden="true">
		<?php
		if ( has_post_thumbnail() ) {
			// Decorative: the title below names the card.
			the_post_thumbnail( 'medium_large', array( 'class' => 'resource-tile__photo', 'alt' => '', 'loading' => 'lazy' ) );
		} else {
			echo aiad_strand_poster_svg( $card_strand ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from fixed paths.
		}
		?>
		<?php if ( $card_strand ) : ?>
			<span class="resource-tile__strand"><?php echo aiad_strand_icon_svg( $card_strand, 14 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo esc_html( strtoupper( $card_theme ) ); ?></span>
		<?php endif; ?>
	</div>

	<div class="resource-tile__body">
		<?php
		// Always present, even empty, so titles line up across a row. The strand
		// word only shows in the compact phone layout, where the hero is too small
		// for its pill; resource-tiles.css hides it at other widths.
		?>
		<?php // One line on purpose: whitespace between the spans would print as a stray space before the dot. ?>
		<p class="resource-tile__meta"><?php if ( $card_strand ) : ?><span class="resource-tile__meta-strand"><?php echo esc_html( $card_theme ); ?></span><?php endif; ?><?php if ( $card_meta ) : ?><span class="resource-tile__meta-info"><?php echo esc_html( implode( ' · ', $card_meta ) ); ?></span><?php endif; ?></p>
		<h3 class="resource-tile__title">
			<a href="<?php echo esc_url( $card_link ); ?>"
				<?php if ( $card_track_id ) : ?>data-featured-resource-id="<?php echo esc_attr( (string) $card_track_id ); ?>"<?php endif; ?>
				<?php if ( $card_external ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
				<?php echo esc_html( $card_title ); ?>
				<?php if ( $card_strand ) : ?><span class="screen-reader-text"><?php echo esc_html( sprintf( /* translators: %s: strand name */ __( '(%s strand)', 'ai-awareness-day' ), $card_theme ) ); ?></span><?php endif; ?>
				<?php if ( $card_external ) : ?><span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'ai-awareness-day' ); ?></span><?php endif; ?>
			</a>
		</h3>
		<?php if ( $card_summary ) : ?>
			<p class="resource-tile__excerpt"><?php echo esc_html( $card_summary ); ?></p>
		<?php endif; ?>
	</div>
</article>
