<?php
/**
 * Single template for a Timeline Entry (aiad_timeline CPT).
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
		$link_label   = get_post_meta( $post_id, '_aiad_timeline_link_label', true ) ?: __( 'Join the campaign', 'ai-awareness-day' );
		$video_url    = get_post_meta( $post_id, '_aiad_timeline_video_url', true );
		$linkedin_url = get_post_meta( $post_id, '_aiad_timeline_linkedin_url', true );

		$yt_id       = function_exists( 'aiad_youtube_video_id' ) ? aiad_youtube_video_id( $video_url ) : '';
		$video_embed = ! empty( $video_url ) && empty( $yt_id )
			? wp_oembed_get( $video_url, array( 'width' => 760 ) )
			: '';

		$linkedin_embed = '';
		if ( ! empty( $linkedin_url ) ) {
			$parsed = wp_parse_url( $linkedin_url );
			$host   = isset( $parsed['host'] ) ? strtolower( (string) $parsed['host'] ) : '';
			$path   = isset( $parsed['path'] ) ? (string) $parsed['path'] : '';
			$is_linkedin_host = ( 'linkedin.com' === $host || str_ends_with( $host, '.linkedin.com' ) );
			if ( $is_linkedin_host ) {
				if ( 0 === strpos( $path, '/embed/feed/update/' ) ) {
					$linkedin_embed = 'https://www.linkedin.com' . $path;
				} elseif ( preg_match( '#/feed/update/(urn:li:[^/]+)#', $path, $matches ) ) {
					$linkedin_embed = 'https://www.linkedin.com/embed/feed/update/' . $matches[1];
				}
			}
		}

		$show_video       = ( 'video' === $card_type || 'default' === $card_type ) && ( ! empty( $yt_id ) || ! empty( $video_embed ) );
		$show_linkedin    = 'linkedin' === $card_type && ! empty( $linkedin_embed );
		$cover_fallback   = get_post_meta( $post_id, '_aiad_timeline_cover_fallback', true );
		$cover_data       = function_exists( 'aiad_timeline_entry_cover_image_data' )
			? aiad_timeline_entry_cover_image_data( get_post(), 'hero' )
			: array(
				'url'           => get_the_post_thumbnail_url( $post_id, 'large' ) ?: '',
				'fit'           => 'cover',
				'focal_post_id' => $post_id,
			);
		$show_image       = ! $show_video && ! $show_linkedin && ! empty( $cover_data['url'] );
		$show_fallback    = ! $show_video && ! $show_linkedin && ! $show_image
			&& function_exists( 'aiad_timeline_cover_fallback_inner_html' );
		$has_media        = $show_video || $show_linkedin || $show_image || $show_fallback;
		$cover_fit_class  = ( $show_image && 'contain' === $cover_data['fit'] ) ? ' resource-activity-figure__img--fit-contain' : '';

		$badge_label = function_exists( 'aiad_timeline_featured_badge_label' )
			? aiad_timeline_featured_badge_label( get_post(), $pinned, $icon )
			: ucfirst( $icon );

		$raw_content  = (string) get_post_field( 'post_content', $post_id );
		$excerpt      = has_excerpt() ? get_the_excerpt() : '';
		$body_content = (string) apply_filters( 'the_content', $raw_content );
		if ( function_exists( 'aiad_timeline_single_split_tags_from_content' ) ) {
			$content_parts = aiad_timeline_single_split_tags_from_content( $body_content );
			$body_content  = isset( $content_parts['body'] ) ? (string) $content_parts['body'] : $body_content;
		}
		?>

		<article id="post-<?php the_ID(); ?>" <?php post_class( 'single-timeline-entry single-timeline-entry--stacked' ); ?>>
			<div class="single-timeline-entry__container">

				<!-- Stacked layout: badge → title → author + date → media → excerpt -->
				<span class="single-timeline-entry__badge single-timeline-entry__badge--outline single-timeline-entry__badge--<?php echo esc_attr( $pinned ? 'pinned' : $icon ); ?>">
					<?php echo esc_html( $badge_label ); ?>
				</span>
				<h1 class="single-timeline-entry__title"><?php echo esc_html( get_the_title() ); ?></h1>
				<div class="single-timeline-entry__meta-row">
					<?php
					$author_id = (int) get_post_field( 'post_author', $post_id );
					if ( $author_id ) :
						$author_name = get_the_author_meta( 'display_name', $author_id );
						$author_bio  = get_the_author_meta( 'description', $author_id );
						?>
						<div class="single-timeline-entry__author">
							<?php echo get_avatar( $author_id, 44, '', '', array( 'class' => 'single-timeline-entry__author-avatar' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<div class="single-timeline-entry__author-meta">
								<p class="single-timeline-entry__author-name"><?php echo esc_html( $author_name ); ?></p>
								<?php if ( ! empty( $author_bio ) ) : ?>
									<p class="single-timeline-entry__author-role"><?php echo esc_html( wp_trim_words( $author_bio, 14 ) ); ?></p>
								<?php endif; ?>
							</div>
						</div>
					<?php endif; ?>
					<?php
					$date_label = function_exists( 'aiad_timeline_human_date_label' )
						? aiad_timeline_human_date_label( $post_id )
						: get_the_date( 'j F Y' );
					$date_title = get_the_date( 'j F Y' );
					?>
					<time class="single-timeline-entry__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" title="<?php echo esc_attr( $date_title ); ?>">
						<?php echo esc_html( $date_label ); ?>
					</time>
				</div>

				<!-- Media -->
				<?php if ( $has_media ) : ?>
					<div class="single-timeline-entry__media">
						<?php if ( ! empty( $yt_id ) && function_exists( 'aiad_render_youtube_facade' ) ) : ?>
							<?php echo aiad_render_youtube_facade( $yt_id, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php elseif ( $show_linkedin ) : ?>
							<div class="single-timeline-entry__linkedin">
								<iframe src="<?php echo esc_url( $linkedin_embed ); ?>" height="570" width="100%" frameborder="0" allowfullscreen title="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy"></iframe>
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
							<?php
							$focal_post_id = isset( $cover_data['focal_post_id'] ) ? (int) $cover_data['focal_post_id'] : $post_id;
							$img_style     = function_exists( 'aiad_entry_figure_img_style_attr' )
								? aiad_entry_figure_img_style_attr( $focal_post_id, (string) $cover_data['fit'], 'single' )
								: '';
							?>
							<figure class="resource-activity-figure resource-activity-figure--timeline-single">
								<img
									class="resource-activity-figure__img<?php echo esc_attr( $cover_fit_class ); ?>"
									src="<?php echo esc_url( $cover_data['url'] ); ?>"
									alt=""
									loading="lazy"
									width="1200"
									height="630"
									<?php
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- style attr escaped in helper.
									echo $img_style ? trim( $img_style ) : '';
									?>
								/>
							</figure>
						<?php elseif ( $show_fallback ) : ?>
							<figure class="resource-activity-figure resource-activity-figure--timeline-fallback timeline-cover--<?php echo esc_attr( sanitize_html_class( $icon ) ); ?>">
								<?php
								echo aiad_timeline_cover_fallback_inner_html( $icon, (string) $cover_fallback, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?>
							</figure>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $excerpt ) ) : ?>
					<p class="single-timeline-entry__excerpt"><?php echo wp_kses_post( $excerpt ); ?></p>
				<?php endif; ?>

				<!-- Content -->
				<?php if ( '' !== trim( $body_content ) ) : ?>
					<div class="single-timeline-entry__content entry-content entry-content--timeline">
						<?php echo $body_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered via the_content. ?>
					</div>
				<?php endif; ?>

				<!-- CTA -->
				<?php if ( $link_url ) : ?>
					<div class="single-timeline-entry__cta">
						<a href="<?php echo esc_url( $link_url ); ?>" class="single-timeline-entry__cta-btn" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $link_label ); ?>
							<?php if ( function_exists( 'aiad_timeline_link_icon_svg' ) ) : ?>
								<span class="single-timeline-entry__cta-icon" aria-hidden="true"><?php echo aiad_timeline_link_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
						</a>
					</div>
				<?php endif; ?>

				<?php
				if ( function_exists( 'aiad_timeline_single_render_related' ) ) {
					aiad_timeline_single_render_related( $post_id );
				}
				?>

				<!-- Footer: back (left) + share (right) -->
				<div class="single-timeline-entry__footer">
					<a href="<?php echo esc_url( home_url( '/#timeline' ) ); ?>" class="single-timeline-entry__back">
						<?php if ( function_exists( 'aiad_back_icon_svg' ) ) : ?>
							<span aria-hidden="true"><?php echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php esc_html_e( 'Back to timeline', 'ai-awareness-day' ); ?>
					</a>
					<button type="button" class="single-timeline-entry__share" data-url="<?php echo esc_url( get_permalink() ); ?>" data-title="<?php echo esc_attr( get_the_title() ); ?>" aria-label="<?php esc_attr_e( 'Share this update', 'ai-awareness-day' ); ?>">
						<?php if ( function_exists( 'aiad_timeline_share_icon_svg' ) ) : ?>
							<span class="single-timeline-entry__share-icon" aria-hidden="true"><?php echo aiad_timeline_share_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<?php endif; ?>
						<?php esc_html_e( 'Share', 'ai-awareness-day' ); ?>
					</button>
				</div>

			</div>
		</article>

	<?php endwhile; ?>
</main>

<?php get_footer(); ?>
