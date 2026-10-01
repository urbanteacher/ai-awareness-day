<?php
/**
 * Single template for a Resource, laid out as a lesson plan a teacher can
 * read top to bottom: what it is, what to get ready, the steps on a running
 * clock with the video beside them, the question to put on the board, then
 * the reference material. Helpers live in inc/resource-lesson.php.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="rl">
	<?php
	while ( have_posts() ) :
		the_post();
		$resource_id = get_the_ID();

		// ---- Classification ----
		$themes     = get_the_terms( $resource_id, 'resource_principle' );
		$theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
		$theme_slug = $themes && ! is_wp_error( $themes ) ? strtolower( $themes[0]->slug ) : '';
		$strand     = in_array( $theme_slug, array( 'safe', 'smart', 'creative', 'responsible', 'future' ), true ) ? $theme_slug : 'safe';

		$durations       = get_the_terms( $resource_id, 'resource_duration' );
		$duration_labels = ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_resource_duration_term_labels' ) )
			? aiad_resource_duration_term_labels( $durations )
			: array();
		$activity_terms  = get_the_terms( $resource_id, 'activity_type' );
		$activity_names  = $activity_terms && ! is_wp_error( $activity_terms ) ? wp_list_pluck( $activity_terms, 'name' ) : array();
		$ks_options      = function_exists( 'aiad_key_stage_options' ) ? aiad_key_stage_options() : array();
		$key_stages      = function_exists( 'aiad_get_resource_key_stages' ) ? aiad_get_resource_key_stages( $resource_id ) : array();
		$key_stage_names = array_map(
			static function ( string $slug ) use ( $ks_options ): string {
				return $ks_options[ $slug ] ?? strtoupper( $slug );
			},
			$key_stages
		);
		$level        = (string) get_post_meta( $resource_id, '_aiad_level', true );
		$level_labels = array(
			'beginner'     => __( 'Beginner', 'ai-awareness-day' ),
			'intermediate' => __( 'Intermediate', 'ai-awareness-day' ),
			'advanced'     => __( 'Advanced', 'ai-awareness-day' ),
		);
		$subtitle = (string) get_post_meta( $resource_id, '_aiad_subtitle', true );
		$overview = '' !== $subtitle ? $subtitle : ( has_excerpt() ? get_the_excerpt() : '' );

		// ---- Media ----
		$download_url      = (string) get_post_meta( $resource_id, '_aiad_download_url', true );
		$download_label    = function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $download_url ) : __( 'Download', 'ai-awareness-day' );
		$download_ext      = $download_url ? strtolower( pathinfo( (string) wp_parse_url( $download_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';
		$pptx_embed_url    = in_array( $download_ext, array( 'pptx', 'ppt' ), true )
			? 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode( $download_url )
			: '';
		$video_url         = (string) get_post_meta( $resource_id, '_aiad_preview_video_url', true );
		$video_html        = ( '' !== $video_url && function_exists( 'aiad_resource_preview_video_html' ) )
			? aiad_resource_embed_with_api( aiad_resource_preview_video_html( $video_url ) )
			: '';
		$is_youtube        = '' !== $video_url && (bool) preg_match( '#(youtube\.com|youtu\.be)/#', $video_url );

		// ---- Lesson content ----
		$preparation = array_values(
			array_filter(
				(array) get_post_meta( $resource_id, '_aiad_preparation', true ),
				static function ( $v ) {
					return is_string( $v ) && '' !== trim( $v );
				}
			)
		);
		$teacher_notes = trim( (string) get_post_meta( $resource_id, '_aiad_teacher_notes', true ) );
		$objectives    = array_values(
			array_filter(
				array_map(
					static function ( $ob ) {
						return trim( is_array( $ob ) ? (string) ( $ob['objective'] ?? '' ) : (string) $ob );
					},
					aiad_normalise_learning_objectives( get_post_meta( $resource_id, '_aiad_learning_objectives', true ) )
				),
				'strlen'
			)
		);
		$lesson       = aiad_resource_lesson_steps( aiad_normalise_instructions( get_post_meta( $resource_id, '_aiad_instructions', true ) ) );
		$steps        = $lesson['steps'];
		$lesson_time  = $lesson['total'] > 0 ? aiad_resource_length_label( $lesson['total'] ) : (string) get_post_meta( $resource_id, '_aiad_duration', true );
		$question     = trim( (string) get_post_meta( $resource_id, '_aiad_discussion_question', true ) );
		$diff_raw     = (array) get_post_meta( $resource_id, '_aiad_differentiation', true );
		$adaptations  = array_filter(
			array(
				'support' => array( __( 'Support', 'ai-awareness-day' ), __( 'For pupils who need more help', 'ai-awareness-day' ), trim( (string) ( $diff_raw['support'] ?? '' ) ) ),
				'stretch' => array( __( 'Stretch', 'ai-awareness-day' ), __( 'For pupils ready to go further', 'ai-awareness-day' ), trim( (string) ( $diff_raw['stretch'] ?? '' ) ) ),
				'send'    => array( __( 'SEND', 'ai-awareness-day' ), __( 'For additional needs', 'ai-awareness-day' ), trim( (string) ( $diff_raw['send'] ?? '' ) ) ),
			),
			static function ( $row ) {
				return '' !== $row[2];
			}
		);
		// The National AI Conversation card: the editor's own advice, or a
		// general way in that leans on the big question when there is one.
		$debate = trim( (string) get_post_meta( $resource_id, '_aiad_debate', true ) );
		if ( '' === $debate ) {
			$debate = '' !== $question
				? __( 'Turn it into a motion: reword the big question as "This house believes…". Split the class for and against, give sides five minutes to prepare, hear two speakers each, then take a free vote.', 'ai-awareness-day' )
				: __( 'Turn it into a motion: take the most contested idea from the lesson and word it as "This house believes…". Split the class for and against, hear two speakers each, then take a free vote.', 'ai-awareness-day' );
		}
		$debate_setup = aiad_resource_debate( $resource_id, $strand, $key_stages );
		$adaptations['debate'] = array( __( 'Debate it', 'ai-awareness-day' ), __( 'For the National AI Conversation', 'ai-awareness-day' ), $debate );

		$definitions = array_values(
			array_filter(
				(array) get_post_meta( $resource_id, '_aiad_key_definitions', true ),
				static function ( $d ) {
					return is_array( $d ) && ( '' !== trim( (string) ( $d['term'] ?? '' ) ) || '' !== trim( (string) ( $d['definition'] ?? '' ) ) );
				}
			)
		);
		$extensions = array_values(
			array_filter(
				(array) get_post_meta( $resource_id, '_aiad_extensions', true ),
				static function ( $e ) {
					return is_array( $e ) && '' !== trim( (string) ( $e['activity'] ?? '' ) );
				}
			)
		);
		$materials = array_values(
			array_filter(
				(array) get_post_meta( $resource_id, '_aiad_resources', true ),
				static function ( $r ) {
					return is_array( $r ) && ( '' !== trim( (string) ( $r['name'] ?? '' ) ) || '' !== trim( (string) ( $r['url'] ?? '' ) ) );
				}
			)
		);
		$extension_types = array(
			'homework'         => __( 'Homework', 'ai-awareness-day' ),
			'next_lesson'      => __( 'Next lesson', 'ai-awareness-day' ),
			'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ),
			'independent'      => __( 'Independent', 'ai-awareness-day' ),
		);
		$material_types = array(
			'slides'    => __( 'Slides', 'ai-awareness-day' ),
			'worksheet' => __( 'Worksheet', 'ai-awareness-day' ),
			'handout'   => __( 'Handout', 'ai-awareness-day' ),
			'video'     => __( 'Video', 'ai-awareness-day' ),
			'link'      => __( 'Link', 'ai-awareness-day' ),
			'other'     => __( 'Other', 'ai-awareness-day' ),
		);

		$body_content = trim( (string) get_the_content() );

		// The contents list follows the sections that will actually render.
		$toc = array_filter(
			array(
				'rl-prepare'    => ( $preparation || '' !== $teacher_notes ) ? __( 'Before you teach', 'ai-awareness-day' ) : '',
				'rl-objectives' => $objectives ? __( 'Learning objectives', 'ai-awareness-day' ) : '',
				'rl-lesson'     => $steps ? __( 'The lesson', 'ai-awareness-day' ) : '',
				'rl-question'   => '' !== $question ? __( 'Big question', 'ai-awareness-day' ) : '',
				'rl-adapt'      => $adaptations ? __( 'Adapting the lesson', 'ai-awareness-day' ) : '',
				'rl-debate'     => $debate_setup['ages'] ? __( 'Set up the debate', 'ai-awareness-day' ) : '',
				'rl-words'      => $definitions ? __( 'Key words', 'ai-awareness-day' ) : '',
				'rl-further'    => $extensions ? __( 'Take it further', 'ai-awareness-day' ) : '',
				'rl-materials'  => $materials ? __( 'Materials', 'ai-awareness-day' ) : '',
			)
		);

		$facts = array_filter(
			array(
				__( 'Lesson time', 'ai-awareness-day' ) => $lesson_time,
				__( 'Fits', 'ai-awareness-day' )        => implode( ', ', $duration_labels ),
				__( 'Key stage', 'ai-awareness-day' )   => implode( ', ', $key_stage_names ),
				__( 'Format', 'ai-awareness-day' )      => implode( ', ', $activity_names ),
				__( 'Level', 'ai-awareness-day' )       => $level_labels[ $level ] ?? '',
			),
			'strlen'
		);

		$share_message = function_exists( 'aiad_get_share_message' ) ? aiad_get_share_message( 'resource', get_post() ) : '';
		$archive_url   = get_post_type_archive_link( 'resource' ) ?: home_url( '/resources/' );
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'rl-article' ); ?> data-strand="<?php echo esc_attr( $strand ); ?>">

			<header class="rl-hero">
				<div class="container rl-hero__inner">
					<p class="rl-eyebrow">
						<a href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'Classroom resources', 'ai-awareness-day' ); ?></a>
						<?php if ( $theme_name ) : ?>
							<span aria-hidden="true">/</span>
							<a href="<?php echo esc_url( add_query_arg( 'principle', $theme_slug, $archive_url ) ); ?>"><?php echo esc_html( $theme_name ); ?></a>
						<?php endif; ?>
					</p>
					<h1 class="rl-title"><?php the_title(); ?></h1>
					<?php if ( '' !== $overview ) : ?>
						<p class="rl-lead"><?php echo wptexturize( esc_html( $overview ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<?php endif; ?>

					<?php if ( $facts ) : ?>
						<dl class="rl-facts">
							<?php foreach ( $facts as $label => $value ) : ?>
								<div>
									<dt><?php echo esc_html( $label ); ?></dt>
									<dd><?php echo esc_html( $value ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					<?php endif; ?>

					<div class="rl-actions">
						<?php if ( $download_url ) : ?>
							<a href="<?php echo esc_url( $download_url ); ?>" class="rl-btn rl-btn--primary" data-resource-id="<?php echo esc_attr( (string) $resource_id ); ?>" download target="_blank" rel="noopener">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square" aria-hidden="true"><path d="M12 3v12m-5-5 5 5 5-5M4 20h16"/></svg>
								<?php echo esc_html( $download_label ); ?>
							</a>
						<?php endif; ?>
						<?php if ( $is_youtube ) : ?>
							<a href="<?php echo esc_url( $video_url ); ?>" class="rl-btn" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Watch on YouTube', 'ai-awareness-day' ); ?>
							</a>
						<?php endif; ?>
						<button type="button" class="rl-btn resource-print-btn">
							<?php esc_html_e( 'Print lesson plan', 'ai-awareness-day' ); ?>
						</button>
						<button type="button" class="rl-btn resource-share-btn"
							data-url="<?php echo esc_url( get_permalink() ); ?>"
							data-title="<?php echo esc_attr( get_the_title() ); ?>"
							data-text="<?php echo esc_attr( $share_message ); ?>"
							aria-label="<?php esc_attr_e( 'Share this resource', 'ai-awareness-day' ); ?>">
							<span class="rl-btn__label"><?php esc_html_e( 'Share', 'ai-awareness-day' ); ?></span>
						</button>
						<button type="button" class="rl-btn resource-social-card-btn"
							data-title="<?php echo esc_attr( get_the_title() ); ?>"
							data-url="<?php echo esc_url( get_permalink() ); ?>"
							data-theme="<?php echo esc_attr( $theme_name ); ?>"
							data-key-stages="<?php echo esc_attr( implode( ', ', $key_stage_names ) ); ?>">
							<?php esc_html_e( 'Share image', 'ai-awareness-day' ); ?>
						</button>
					</div>
				</div>
			</header>

			<div class="container rl-layout">

				<aside class="rl-aside" aria-label="<?php esc_attr_e( 'Lesson media and contents', 'ai-awareness-day' ); ?>">
					<div class="rl-aside__sticky">
						<?php if ( $video_html || $pptx_embed_url ) : ?>
							<figure class="rl-media<?php echo $video_html ? ' rl-media--video' : ' rl-media--slides'; ?>" data-rl-media>
								<div class="rl-media__frame">
									<?php if ( $video_html ) : ?>
										<?php
										// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- oEmbed HTML from a trusted provider, or the escaped <video> from aiad_resource_preview_video_html().
										echo $video_html;
										?>
									<?php else : ?>
										<iframe src="<?php echo esc_url( $pptx_embed_url ); ?>" title="<?php esc_attr_e( 'Presentation preview', 'ai-awareness-day' ); ?>" loading="lazy" allowfullscreen></iframe>
									<?php endif; ?>
								</div>
								<figcaption class="rl-media__caption">
									<?php
									echo $video_html
										? esc_html__( 'The video for this lesson. Video references in the steps jump to the right moment.', 'ai-awareness-day' )
										: esc_html__( 'Slide preview', 'ai-awareness-day' );
									?>
								</figcaption>
							</figure>
						<?php elseif ( has_post_thumbnail() ) : ?>
							<figure class="rl-media rl-media--image">
								<?php the_post_thumbnail( 'large' ); ?>
							</figure>
						<?php endif; ?>

						<?php if ( count( $toc ) > 1 ) : ?>
							<nav class="rl-toc" aria-label="<?php esc_attr_e( 'On this page', 'ai-awareness-day' ); ?>">
								<p class="rl-toc__title"><?php esc_html_e( 'On this page', 'ai-awareness-day' ); ?></p>
								<ol>
									<?php foreach ( $toc as $anchor => $label ) : ?>
										<li><a href="#<?php echo esc_attr( $anchor ); ?>"><?php echo esc_html( $label ); ?></a></li>
									<?php endforeach; ?>
								</ol>
							</nav>
						<?php endif; ?>
					</div>
				</aside>

				<div class="rl-main">

					<?php if ( '' !== $body_content ) : ?>
						<div class="rl-intro entry-content">
							<?php the_content(); ?>
						</div>
					<?php endif; ?>

					<?php if ( $preparation || '' !== $teacher_notes ) : ?>
						<section class="rl-section" id="rl-prepare" aria-labelledby="rl-prepare-h">
							<h2 id="rl-prepare-h"><?php esc_html_e( 'Before you teach', 'ai-awareness-day' ); ?></h2>
							<?php if ( $preparation ) : ?>
								<h3 class="rl-subhead"><?php esc_html_e( 'Get ready', 'ai-awareness-day' ); ?></h3>
								<ul class="rl-checklist">
									<?php foreach ( $preparation as $item ) : ?>
										<li><?php echo wptexturize( esc_html( $item ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
							<?php if ( '' !== $teacher_notes ) : ?>
								<div class="rl-notes">
									<h3 class="rl-notes__title"><?php esc_html_e( 'Teacher notes', 'ai-awareness-day' ); ?></h3>
									<?php echo aiad_resource_rich_text( $teacher_notes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
								</div>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<?php if ( $objectives ) : ?>
						<section class="rl-section" id="rl-objectives" aria-labelledby="rl-objectives-h">
							<h2 id="rl-objectives-h"><?php esc_html_e( 'Learning objectives', 'ai-awareness-day' ); ?></h2>
							<ol class="rl-objectives">
								<?php foreach ( $objectives as $objective ) : ?>
									<li><span><?php echo wptexturize( wp_kses_post( $objective ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span></li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>

					<?php if ( $steps ) : ?>
						<section class="rl-section" id="rl-lesson" aria-labelledby="rl-lesson-h">
							<div class="rl-section__head">
								<h2 id="rl-lesson-h"><?php esc_html_e( 'The lesson', 'ai-awareness-day' ); ?></h2>
								<p class="rl-section__meta">
									<?php
									echo esc_html(
										sprintf(
											/* translators: %d: number of steps. */
											_n( '%d step', '%d steps', count( $steps ), 'ai-awareness-day' ),
											count( $steps )
										)
									);
									if ( $lesson['total'] > 0 ) {
										echo ' &middot; ' . esc_html( aiad_resource_length_label( $lesson['total'] ) );
									}
									?>
								</p>
							</div>
							<ol class="rl-steps">
								<?php foreach ( $steps as $i => $step ) : ?>
									<?php $seek = ( $video_html && '' !== $step['ref'] ) ? aiad_resource_ref_seek( $step['ref'] ) : null; ?>
									<li class="rl-step">
										<div class="rl-step__rail">
											<span class="rl-step__num" aria-hidden="true"><?php echo esc_html( (string) ( $i + 1 ) ); ?></span>
											<?php if ( null !== $step['start'] ) : ?>
												<span class="rl-step__clock">
													<span class="screen-reader-text"><?php esc_html_e( 'Starts at', 'ai-awareness-day' ); ?></span>
													<?php echo esc_html( aiad_resource_clock( (int) $step['start'] ) ); ?>
												</span>
											<?php endif; ?>
										</div>
										<div class="rl-step__body">
											<p class="rl-step__action"><?php echo wptexturize( wp_kses_post( $step['action'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
											<?php if ( '' !== $step['duration'] || '' !== $step['students'] || '' !== $step['ref'] ) : ?>
												<ul class="rl-step__meta">
													<?php if ( '' !== $step['duration'] ) : ?>
														<li class="rl-tag rl-tag--time"><?php echo esc_html( $step['duration'] ); ?></li>
													<?php endif; ?>
													<?php if ( '' !== $step['students'] ) : ?>
														<li class="rl-tag"><span class="rl-tag__key"><?php esc_html_e( 'Pupils', 'ai-awareness-day' ); ?></span> <?php echo wptexturize( esc_html( $step['students'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
													<?php endif; ?>
													<?php if ( '' !== $step['ref'] ) : ?>
														<li class="rl-tag-wrap">
															<?php if ( null !== $seek ) : ?>
																<button type="button" class="rl-tag rl-tag--seek" data-rl-seek="<?php echo esc_attr( (string) $seek ); ?>">
																	<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6 4l15 8-15 8z"/></svg>
																	<?php echo esc_html( $step['ref'] ); ?>
																</button>
															<?php else : ?>
																<span class="rl-tag"><?php echo esc_html( $step['ref'] ); ?></span>
															<?php endif; ?>
														</li>
													<?php endif; ?>
												</ul>
											<?php endif; ?>
											<?php if ( '' !== $step['tip'] ) : ?>
												<div class="rl-step__tip">
													<p class="rl-step__tip-label"><?php esc_html_e( 'Teacher tip', 'ai-awareness-day' ); ?></p>
													<?php echo aiad_resource_rich_text( $step['tip'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
												</div>
											<?php endif; ?>
										</div>
									</li>
								<?php endforeach; ?>
							</ol>
						</section>
					<?php endif; ?>

					<?php if ( '' !== $question ) : ?>
						<section class="rl-section rl-question" id="rl-question" aria-labelledby="rl-question-h">
							<div class="rl-question__panel" data-rl-question tabindex="-1">
								<h2 id="rl-question-h" class="rl-question__label"><?php esc_html_e( 'Big question', 'ai-awareness-day' ); ?></h2>
								<p class="rl-question__text"><?php echo wptexturize( esc_html( $question ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
								<button type="button" class="rl-question__show" data-rl-project hidden>
									<?php esc_html_e( 'Show on the board', 'ai-awareness-day' ); ?>
								</button>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( $adaptations ) : ?>
						<section class="rl-section" id="rl-adapt" aria-labelledby="rl-adapt-h">
							<h2 id="rl-adapt-h"><?php esc_html_e( 'Adapting the lesson', 'ai-awareness-day' ); ?></h2>
							<div class="rl-adapt">
								<?php foreach ( $adaptations as $key => $row ) : ?>
									<div class="rl-adapt__card rl-adapt__card--<?php echo esc_attr( $key ); ?>">
										<?php if ( 'debate' === $key ) : ?>
											<p class="rl-adapt__badge"><?php esc_html_e( 'In every lesson', 'ai-awareness-day' ); ?></p>
										<?php endif; ?>
										<h3><?php echo esc_html( $row[0] ); ?></h3>
										<p class="rl-adapt__for"><?php echo esc_html( $row[1] ); ?></p>
										<?php echo aiad_resource_rich_text( $row[2] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
										<?php if ( 'debate' === $key && $debate_setup['ages'] ) : ?>
											<p class="rl-adapt__link">
												<a href="#rl-debate"><?php esc_html_e( 'Set up the debate', 'ai-awareness-day' ); ?></a>
											</p>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</section>
					<?php endif; ?>

					<?php if ( $debate_setup['ages'] ) : ?>
						<section class="rl-section rl-debate" id="rl-debate" aria-labelledby="rl-debate-h">
							<p class="rl-debate__badge"><?php esc_html_e( 'National AI Conversation', 'ai-awareness-day' ); ?></p>
							<h2 id="rl-debate-h"><?php esc_html_e( 'Set up the debate', 'ai-awareness-day' ); ?></h2>
							<p class="rl-debate__intro">
								<?php esc_html_e( 'The same topic, pitched for each age group. Pick your pupils\' pathway: the motion, a prompt to get them going, and three points each way to start their preparation.', 'ai-awareness-day' ); ?>
							</p>

							<?php if ( count( $debate_setup['ages'] ) > 1 ) : ?>
								<div class="rl-debate__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Age group', 'ai-awareness-day' ); ?>" data-rl-tabs hidden>
									<?php foreach ( $debate_setup['ages'] as $age => $set ) : ?>
										<button type="button" role="tab" id="rl-debate-tab-<?php echo esc_attr( $age ); ?>"
											aria-controls="rl-debate-<?php echo esc_attr( $age ); ?>"
											aria-selected="<?php echo $age === $debate_setup['default'] ? 'true' : 'false'; ?>"
											tabindex="<?php echo $age === $debate_setup['default'] ? '0' : '-1'; ?>">
											<span class="rl-debate__tab-name"><?php echo esc_html( $set['name'] ); ?></span>
											<span class="rl-debate__tab-years"><?php echo esc_html( $set['years'] ); ?></span>
										</button>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>

							<?php foreach ( $debate_setup['ages'] as $age => $set ) : ?>
								<div class="rl-debate__panel" id="rl-debate-<?php echo esc_attr( $age ); ?>" role="tabpanel"
									aria-labelledby="rl-debate-tab-<?php echo esc_attr( $age ); ?>"
									data-default="<?php echo $age === $debate_setup['default'] ? 'true' : 'false'; ?>">
									<h3 class="rl-debate__age"><?php echo esc_html( $set['name'] . ' · ' . $set['years'] ); ?></h3>

									<div class="rl-debate__motion">
										<p class="rl-debate__label"><?php esc_html_e( 'The motion', 'ai-awareness-day' ); ?></p>
										<p class="rl-debate__motion-text"><?php echo wptexturize( esc_html( $set['motion'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
										<?php if ( '' !== $set['prompt'] ) : ?>
											<?php echo aiad_resource_rich_text( $set['prompt'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
										<?php endif; ?>
									</div>

									<?php if ( $set['for'] || $set['against'] ) : ?>
										<div class="rl-debate__sides">
											<?php
											foreach ( array(
												'for'     => __( 'For', 'ai-awareness-day' ),
												'against' => __( 'Against', 'ai-awareness-day' ),
											) as $side => $side_label ) :
												if ( ! $set[ $side ] ) {
													continue;
												}
												?>
												<div class="rl-debate__side rl-debate__side--<?php echo esc_attr( $side ); ?>">
													<h4><?php echo esc_html( $side_label ); ?></h4>
													<ul>
														<?php foreach ( $set[ $side ] as $point ) : ?>
															<li><?php echo wptexturize( esc_html( $point ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></li>
														<?php endforeach; ?>
													</ul>
												</div>
											<?php endforeach; ?>
										</div>
									<?php else : ?>
										<p class="rl-debate__build"><?php esc_html_e( 'Before anyone speaks, have each side list three reasons and one piece of evidence from the lesson.', 'ai-awareness-day' ); ?></p>
									<?php endif; ?>

									<?php if ( '' !== $set['take'] || '' !== $set['day'] ) : ?>
										<dl class="rl-debate__run">
											<?php if ( '' !== $set['take'] ) : ?>
												<div>
													<dt><?php esc_html_e( 'In class', 'ai-awareness-day' ); ?></dt>
													<dd><?php echo wptexturize( esc_html( $set['take'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
												</div>
											<?php endif; ?>
											<?php if ( '' !== $set['day'] ) : ?>
												<div>
													<dt><?php esc_html_e( 'As a full debate', 'ai-awareness-day' ); ?></dt>
													<dd><?php echo wptexturize( esc_html( $set['day'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></dd>
												</div>
											<?php endif; ?>
										</dl>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>

							<?php if ( function_exists( 'aiad_national_conversation_page_url' ) ) : ?>
								<p class="rl-adapt__link rl-debate__more">
									<a href="<?php echo esc_url( aiad_national_conversation_page_url() ); ?>"><?php esc_html_e( 'Take it further with the National AI Conversation: school debates and judged debates with other schools', 'ai-awareness-day' ); ?></a>
								</p>
							<?php endif; ?>
						</section>
					<?php endif; ?>

					<?php if ( $definitions ) : ?>
						<section class="rl-section" id="rl-words" aria-labelledby="rl-words-h">
							<h2 id="rl-words-h"><?php esc_html_e( 'Key words', 'ai-awareness-day' ); ?></h2>
							<dl class="rl-words">
								<?php foreach ( $definitions as $def ) : ?>
									<div>
										<dt>
											<?php echo esc_html( (string) ( $def['term'] ?? '' ) ); ?>
											<?php if ( ! empty( $def['key_stage_adapted'] ) ) : ?>
												<span class="rl-words__adapted"><?php esc_html_e( 'Simplified for key stage', 'ai-awareness-day' ); ?></span>
											<?php endif; ?>
										</dt>
										<dd><?php echo aiad_resource_rich_text( (string) ( $def['definition'] ?? '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?></dd>
									</div>
								<?php endforeach; ?>
							</dl>
						</section>
					<?php endif; ?>

					<?php if ( $extensions ) : ?>
						<section class="rl-section" id="rl-further" aria-labelledby="rl-further-h">
							<h2 id="rl-further-h"><?php esc_html_e( 'Take it further', 'ai-awareness-day' ); ?></h2>
							<ul class="rl-rows">
								<?php foreach ( $extensions as $ext ) : ?>
									<?php $type = (string) ( $ext['type'] ?? '' ); ?>
									<li>
										<?php if ( isset( $extension_types[ $type ] ) ) : ?>
											<span class="rl-rows__type"><?php echo esc_html( $extension_types[ $type ] ); ?></span>
										<?php endif; ?>
										<span class="rl-rows__text"><?php echo wptexturize( wp_kses_post( (string) $ext['activity'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<?php if ( $materials ) : ?>
						<section class="rl-section" id="rl-materials" aria-labelledby="rl-materials-h">
							<h2 id="rl-materials-h"><?php esc_html_e( 'Materials', 'ai-awareness-day' ); ?></h2>
							<ul class="rl-rows">
								<?php foreach ( $materials as $mat ) : ?>
									<?php
									$type = (string) ( $mat['type'] ?? '' );
									$name = trim( (string) ( $mat['name'] ?? '' ) );
									$url  = trim( (string) ( $mat['url'] ?? '' ) );
									?>
									<li>
										<?php if ( isset( $material_types[ $type ] ) ) : ?>
											<span class="rl-rows__type"><?php echo esc_html( $material_types[ $type ] ); ?></span>
										<?php endif; ?>
										<span class="rl-rows__text">
											<?php if ( '' !== $url ) : ?>
												<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( '' !== $name ? $name : $url ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $name ); ?>
											<?php endif; ?>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						</section>
					<?php endif; ?>

					<?php if ( ! $toc && '' === $body_content && current_user_can( 'edit_post', $resource_id ) ) : ?>
						<p class="rl-empty">
							<?php esc_html_e( 'Only editors see this: add Preparation, Learning objectives, Instructions and Teacher notes in the "Resource content sections" box when editing this resource.', 'ai-awareness-day' ); ?>
						</p>
					<?php endif; ?>

					<p class="rl-back">
						<a href="<?php echo esc_url( $archive_url ); ?>">
							<?php if ( function_exists( 'aiad_back_icon_svg' ) ) : ?>
								<span aria-hidden="true"><?php echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
							<?php esc_html_e( 'All classroom resources', 'ai-awareness-day' ); ?>
						</a>
					</p>
				</div>
			</div>
		</article>
	<?php endwhile; ?>
</main>

<?php
get_footer();
