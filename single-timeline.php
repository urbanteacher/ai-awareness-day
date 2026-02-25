<?php
/**
 * Single template for a Timeline Entry (aiad_timeline CPT).
 *
 * Editorial layout inspired by template.html:
 * kicker → title → subtitle (excerpt) → separator → media → content → end mark → nav
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-timeline">
	<?php while ( have_posts() ) : the_post();

		$post_id      = get_the_ID();
		$icon         = get_post_meta( $post_id, '_aiad_timeline_icon', true ) ?: 'announcement';
		$pinned       = (bool) get_post_meta( $post_id, '_aiad_timeline_pinned', true );
		$card_type    = get_post_meta( $post_id, '_aiad_timeline_card_type', true ) ?: 'default';
		$link_url     = get_post_meta( $post_id, '_aiad_timeline_link_url', true );
		$link_label   = get_post_meta( $post_id, '_aiad_timeline_link_label', true ) ?: __( 'Learn more', 'ai-awareness-day' );
		$video_url    = get_post_meta( $post_id, '_aiad_timeline_video_url', true );
		$linkedin_url = get_post_meta( $post_id, '_aiad_timeline_linkedin_url', true );

		// YouTube: extract ID if present
		$yt_id       = function_exists( 'aiad_youtube_video_id' ) ? aiad_youtube_video_id( $video_url ) : '';
		// Non-YouTube video: fall back to oEmbed
		$video_embed = ! empty( $video_url ) && empty( $yt_id )
			? wp_oembed_get( $video_url, array( 'width' => 640 ) )
			: '';

		// Normalise LinkedIn URL to embed format
		$linkedin_embed = '';
		if ( ! empty( $linkedin_url ) ) {
			if ( strpos( $linkedin_url, '/embed/' ) !== false ) {
				$linkedin_embed = $linkedin_url;
			} elseif ( preg_match( '#linkedin\.com/feed/update/(urn:li:[^/]+)#', $linkedin_url, $matches ) ) {
				$linkedin_embed = 'https://www.linkedin.com/embed/feed/update/' . $matches[1];
			} else {
				$linkedin_embed = $linkedin_url;
			}
		}

		$show_video    = ( 'video' === $card_type || 'default' === $card_type ) && ( ! empty( $yt_id ) || ! empty( $video_embed ) );
		$show_linkedin = 'linkedin' === $card_type && ! empty( $linkedin_embed );
		$show_image    = ! $show_video && ! $show_linkedin && has_post_thumbnail();
		$has_media     = $show_video || $show_linkedin || $show_image;

		$badge_label = function_exists( 'aiad_timeline_featured_badge_label' )
			? aiad_timeline_featured_badge_label( get_post(), $pinned, $icon )
			: ucfirst( $icon );

		$content = get_the_content();
		$excerpt = has_excerpt() ? get_the_excerpt() : '';
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-timeline-entry' ); ?>>
			<div class="single-timeline-entry__container">

				<!-- Kicker: badge · date -->
				<div class="single-timeline-entry__kicker">
					<span class="single-timeline-entry__badge single-timeline-entry__badge--<?php echo esc_attr( $pinned ? 'pinned' : $icon ); ?>">
						<?php echo esc_html( $badge_label ); ?>
					</span>
					<span class="single-timeline-entry__kicker-sep" aria-hidden="true">·</span>
					<time class="single-timeline-entry__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
						<?php echo esc_html( get_the_date( 'j F Y' ) ); ?>
					</time>
				</div>

				<!-- Title -->
				<h1 class="single-timeline-entry__title"><?php the_title(); ?></h1>

				<!-- Subtitle: excerpt as italic lead text -->
				<?php if ( ! empty( $excerpt ) ) : ?>
					<div class="single-timeline-entry__subtitle"><?php echo wp_kses_post( $excerpt ); ?></div>
				<?php endif; ?>

				<!-- Separator line (byline role) -->
				<div class="single-timeline-entry__separator" role="separator"></div>

				<!-- Media -->
				<?php if ( $has_media ) : ?>
					<div class="single-timeline-entry__media">

						<?php if ( ! empty( $yt_id ) && function_exists( 'aiad_render_youtube_facade' ) ) : ?>
							<?php echo aiad_render_youtube_facade( $yt_id, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

						<?php elseif ( $show_linkedin ) : ?>
							<div class="single-timeline-entry__linkedin">
								<iframe
									src="<?php echo esc_url( $linkedin_embed ); ?>"
									height="570"
									width="100%"
									frameborder="0"
									allowfullscreen
									title="<?php echo esc_attr( get_the_title() ); ?>"
									loading="lazy"
								></iframe>
							</div>

						<?php elseif ( $show_video && ! empty( $video_embed ) ) : ?>
							<div class="single-timeline-entry__video">
								<?php
								if ( function_exists( 'aiad_timeline_oembed_allowed_html' ) ) {
									echo wp_kses( $video_embed, aiad_timeline_oembed_allowed_html() );
								}
								?>
							</div>

						<?php elseif ( $show_image ) : ?>
							<figure class="single-timeline-entry__figure">
								<?php the_post_thumbnail( 'large', array( 'class' => 'single-timeline-entry__figure-img' ) ); ?>
							</figure>

						<?php endif; ?>
					</div>
				<?php endif; ?>

				<!-- Body content with drop cap on first paragraph -->
				<?php if ( ! empty( trim( $content ) ) ) : ?>
					<div class="single-timeline-entry__content entry-content">
						<?php the_content(); ?>
					</div>
					<div class="single-timeline-entry__end-mark" aria-hidden="true">&#9672; &#9672; &#9672;</div>
				<?php endif; ?>

				<!-- CTA -->
				<?php if ( $link_url ) : ?>
					<div class="single-timeline-entry__cta">
						<a
							href="<?php echo esc_url( $link_url ); ?>"
							class="single-timeline-entry__cta-btn"
							target="_blank"
							rel="noopener noreferrer"
						>
							<?php echo esc_html( $link_label ); ?>
							<?php if ( function_exists( 'aiad_timeline_link_icon_svg' ) ) : ?>
								<span class="single-timeline-entry__cta-icon" aria-hidden="true">
									<?php echo aiad_timeline_link_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</span>
							<?php endif; ?>
						</a>
					</div>
				<?php endif; ?>

				<!-- Back + Share nav -->
				<nav class="single-timeline-entry__nav" aria-label="<?php esc_attr_e( 'Post navigation', 'ai-awareness-day' ); ?>">
					<a href="<?php echo esc_url( home_url( '/#timeline' ) ); ?>" class="single-timeline-entry__back">
						<?php if ( function_exists( 'aiad_back_icon_svg' ) ) : ?>
							<span class="single-timeline-entry__back-icon" aria-hidden="true">
								<?php echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						<?php endif; ?>
						<?php esc_html_e( 'Back to timeline', 'ai-awareness-day' ); ?>
					</a>
					<button
						type="button"
						class="single-timeline-entry__share"
						data-url="<?php echo esc_url( get_permalink() ); ?>"
						data-title="<?php echo esc_attr( get_the_title() ); ?>"
						aria-label="<?php esc_attr_e( 'Share this update', 'ai-awareness-day' ); ?>"
					>
						<?php if ( function_exists( 'aiad_timeline_share_icon_svg' ) ) : ?>
							<span class="single-timeline-entry__share-icon" aria-hidden="true">
								<?php echo aiad_timeline_share_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</span>
						<?php endif; ?>
						<?php esc_html_e( 'Share', 'ai-awareness-day' ); ?>
					</button>
				</nav>

			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
