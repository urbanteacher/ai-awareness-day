<?php
/**
 * Single template for a Resource.
 *
 * @package AI_Awareness_Day
 */

get_header();
?>

<main id="main" role="main" class="single-resource">
    <section class="section pt-100">
        <?php while ( have_posts() ) : the_post();
                $types         = get_the_terms( get_the_ID(), 'resource_type' );
                $themes        = get_the_terms( get_the_ID(), 'resource_principle' );
                $durations     = get_the_terms( get_the_ID(), 'resource_duration' );
                $type_name     = $types && ! is_wp_error( $types ) ? $types[0]->name : '';
                $theme_name    = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
                $theme_slug    = $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '';
                // Map term slug to Customizer badge setting (normalize to lowercase)
                // Use same simple approach as display board images (which work reliably)
                $badge_slug = $theme_slug !== '' ? strtolower( $theme_slug ) : '';
                $theme_badge_id  = $badge_slug !== '' ? absint( get_theme_mod( 'aiad_badge_' . $badge_slug, 0 ) ) : 0;
                $theme_badge_src = $theme_badge_id ? wp_get_attachment_image_url( $theme_badge_id, 'thumbnail' ) : '';
                $duration_name = '';
                if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_duration_badge_label' ) ) {
                    $duration_name = aiad_duration_badge_label( $durations[0] );
                } elseif ( $durations && ! is_wp_error( $durations ) ) {
                    $duration_name = $durations[0]->name;
                }
                ?>
                <?php
                $activity_terms = get_the_terms( get_the_ID(), 'activity_type' );
                $activity_names = $activity_terms && ! is_wp_error( $activity_terms ) ? wp_list_pluck( $activity_terms, 'name' ) : array();
                $subtitle_meta  = get_post_meta( get_the_ID(), '_aiad_subtitle', true );
                $duration_str   = get_post_meta( get_the_ID(), '_aiad_duration', true );
                $level          = get_post_meta( get_the_ID(), '_aiad_level', true );
                $level_labels        = array( 'beginner' => __( 'Beginner', 'ai-awareness-day' ), 'intermediate' => __( 'Intermediate', 'ai-awareness-day' ), 'advanced' => __( 'Advanced', 'ai-awareness-day' ) );
                $preview_dl_url      = get_post_meta( get_the_ID(), '_aiad_download_url', true );
                $preview_ext         = $preview_dl_url ? strtolower( pathinfo( wp_parse_url( $preview_dl_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) ) : '';
                $pptx_embed_url      = ( $preview_ext === 'pptx' || $preview_ext === 'ppt' )
                    ? 'https://view.officeapps.live.com/op/embed.aspx?src=' . rawurlencode( $preview_dl_url )
                    : '';
                $preview_video_url   = (string) get_post_meta( get_the_ID(), '_aiad_preview_video_url', true );
                $preview_video_html  = ( $preview_video_url !== '' && function_exists( 'aiad_resource_preview_video_html' ) )
                    ? aiad_resource_preview_video_html( $preview_video_url )
                    : '';
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class( 'resource-activity-card' ); ?> <?php if ( $theme_slug ) : ?>data-theme="<?php echo esc_attr( $theme_slug ); ?>"<?php endif; ?>>
                    <header class="resource-activity-header">
                        <h1 class="resource-activity-title">
                            <?php if ( $theme_badge_src ) : ?>
                                <span class="principle-badge resource-activity-title-badge" aria-hidden="true">
                                    <img src="<?php echo esc_url( $theme_badge_src ); ?>" alt="" class="principle-badge__img" aria-hidden="true" />
                                </span>
                            <?php endif; ?>
                            <?php echo esc_html( get_the_title() ); ?>
                        </h1>
                        <?php
                        $overview = $subtitle_meta !== '' ? $subtitle_meta : ( has_excerpt() ? get_the_excerpt() : '' );
                        if ( $overview !== '' ) :
                            ?>
                            <p class="resource-activity-overview"><?php echo esc_html( $overview ); ?></p>
                        <?php endif; ?>
                        <div class="resource-activity-tags" role="list">
                            <?php if ( $type_name ) : ?>
                                <span class="resource-tag"><?php echo esc_html( $type_name ); ?></span>
                            <?php endif; ?>
                            <?php if ( $theme_name ) : ?>
                                <span class="resource-tag resource-tag--theme resource-tag--<?php echo esc_attr( $theme_slug ); ?>"><?php echo esc_html( $theme_name ); ?></span>
                            <?php endif; ?>
                            <?php if ( $duration_str !== '' ) : ?>
                                <span class="resource-tag"><?php echo esc_html( $duration_str ); ?></span>
                            <?php elseif ( $duration_name ) : ?>
                                <span class="resource-tag"><?php echo esc_html( $duration_name ); ?></span>
                            <?php endif; ?>
                            <?php if ( $level !== '' && isset( $level_labels[ $level ] ) ) : ?>
                                <span class="resource-tag resource-tag--level"><?php echo esc_html( $level_labels[ $level ] ); ?></span>
                            <?php endif; ?>
                            <?php foreach ( $activity_names as $act_name ) : ?>
                                <span class="resource-tag"><?php echo esc_html( $act_name ); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php if ( get_option( 'aiad_show_resource_stats', 0 ) ) : ?>
                            <div class="resource-activity-stats" aria-label="<?php esc_attr_e( 'Resource stats', 'ai-awareness-day' ); ?>">
                                <?php
                                $stat_downloads = absint( get_post_meta( get_the_ID(), '_aiad_download_count', true ) );
                                $stat_previews  = absint( get_post_meta( get_the_ID(), '_aiad_view_count', true ) );
                                ?>
                                <?php if ( $stat_downloads > 0 ) : ?>
                                    <span class="resource-stat">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <?php echo esc_html( number_format_i18n( $stat_downloads ) ); ?> <?php esc_html_e( 'downloads', 'ai-awareness-day' ); ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ( $stat_previews > 0 ) : ?>
                                    <span class="resource-stat">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        <?php echo esc_html( number_format_i18n( $stat_previews ) ); ?> <?php esc_html_e( 'views', 'ai-awareness-day' ); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </header>
                    <?php if ( has_post_thumbnail() ) : ?>
                        <figure class="resource-activity-figure">
                            <?php the_post_thumbnail( 'large', array( 'class' => 'resource-activity-figure__img' ) ); ?>
                        </figure>
                    <?php endif; ?>
                    <div class="entry-content entry-content--resource">
                        <?php the_content(); ?>
                    </div>
                    <?php
                    $resource_id = get_the_ID();
                    $key_definitions    = (array) get_post_meta( $resource_id, '_aiad_key_definitions', true );
                    $learning_obj       = get_post_meta( $resource_id, '_aiad_learning_objectives', true );
                    $instructions       = get_post_meta( $resource_id, '_aiad_instructions', true );
                    $learning_obj       = aiad_normalise_learning_objectives( $learning_obj );
                    $instructions       = aiad_normalise_instructions( $instructions );
                    $discussion_q       = get_post_meta( $resource_id, '_aiad_discussion_question', true );
                    $teacher_notes      = get_post_meta( $resource_id, '_aiad_teacher_notes', true );
                    $prep_raw           = get_post_meta( $resource_id, '_aiad_preparation', true );
                    $preparation        = is_array( $prep_raw ) ? array_values( array_filter( $prep_raw, function ( $v ) { return is_string( $v ) && trim( $v ) !== ''; } ) ) : array();
                    $differentiation    = (array) get_post_meta( $resource_id, '_aiad_differentiation', true );
                    $ext_raw            = get_post_meta( $resource_id, '_aiad_extensions', true );
                    $extensions         = is_array( $ext_raw ) ? array_values( array_filter( $ext_raw, function ( $e ) { return is_array( $e ) && trim( (string) ( isset( $e['activity'] ) ? $e['activity'] : '' ) ) !== ''; } ) ) : array();
                    $res_raw            = get_post_meta( $resource_id, '_aiad_resources', true );
                    $resources_list     = is_array( $res_raw ) ? array_values( array_filter( $res_raw, function ( $r ) { return is_array( $r ) && ( trim( (string) ( isset( $r['name'] ) ? $r['name'] : '' ) ) !== '' || trim( (string) ( isset( $r['url'] ) ? $r['url'] : '' ) ) !== '' ); } ) ) : array();
                    $has_sections = ! empty( $preparation ) || ! empty( $key_definitions ) || ! empty( $learning_obj ) || ! empty( $instructions ) || $discussion_q !== '' || $teacher_notes !== '' || ! empty( $differentiation['support'] ) || ! empty( $differentiation['stretch'] ) || ! empty( $differentiation['send'] ) || ! empty( $extensions ) || ! empty( $resources_list );
                    ?>
                    <?php if ( ! empty( $preparation ) ) : ?>
                        <div class="resource-sections resource-sections--rows resource-sections-row--prep-only">
                            <div class="resource-sections-row resource-sections-row--full">
                                <div class="resource-section-cell resource-section-cell--full">
                                    <section class="resource-section resource-section--preparation mt-2rem" aria-labelledby="section-preparation">
                                        <h2 id="section-preparation" class="resource-section__title">
                                            <span class="resource-section__icon resource-section__icon--list" aria-hidden="true"></span>
                                            <?php esc_html_e( 'Preparation', 'ai-awareness-day' ); ?>
                                        </h2>
                                        <ul class="resource-list resource-list--preparation">
                                            <?php foreach ( $preparation as $item ) : ?>
                                                <?php if ( is_string( $item ) && $item !== '' ) : ?>
                                                    <li><?php echo esc_html( $item ); ?></li>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </ul>
                                    </section>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="resource-sections resource-sections--rows" id="resource-content-sections">
                        <?php if ( $has_sections ) : ?>
                            <?php /* Row 1: Learning objectives | Instructions */ ?>
                            <div class="resource-sections-row resource-sections-row--objectives-instructions">
                                <div class="resource-section-cell resource-section-cell--left">
                                    <?php if ( ! empty( $learning_obj ) ) : ?>
                                        <section class="resource-section resource-section--objectives" aria-labelledby="section-objectives">
                                            <h2 id="section-objectives" class="resource-section__title">
                                                <span class="resource-section__icon resource-section__icon--target" aria-hidden="true"></span>
                                                <?php esc_html_e( 'Learning objectives', 'ai-awareness-day' ); ?>
                                            </h2>
                                            <ul class="resource-list resource-list--objectives">
                                                <?php foreach ( $learning_obj as $ob ) : ?>
                                                    <?php
                                                    $obj_text = is_array( $ob ) ? ( $ob['objective'] ?? '' ) : (string) $ob;
                                                    if ( $obj_text === '' ) {
                                                        continue;
                                                    }
                                                    ?>
                                                    <li><?php echo wp_kses_post( $obj_text ); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    <?php endif; ?>
                                </div>
                                <div class="resource-section-cell resource-section-cell--right">
                                    <?php if ( ! empty( $instructions ) ) : ?>
                                        <section class="resource-section resource-section--instructions" aria-labelledby="section-instructions">
                                            <h2 id="section-instructions" class="resource-section__title">
                                                <span class="resource-section__icon resource-section__icon--list" aria-hidden="true"></span>
                                                <?php esc_html_e( 'Instructions', 'ai-awareness-day' ); ?>
                                            </h2>
                                            <ol class="resource-list resource-list--ordered resource-list--instructions">
                                                <?php foreach ( $instructions as $step ) : ?>
                                                    <?php
                                                    $action   = is_array( $step ) ? ( $step['action'] ?? '' ) : (string) $step;
                                                    $duration = is_array( $step ) ? ( $step['duration'] ?? '' ) : '';
                                                    $res_ref  = is_array( $step ) ? ( $step['resource_ref'] ?? '' ) : '';
                                                    $stu_act  = is_array( $step ) ? ( $step['student_action'] ?? '' ) : '';
                                                    $tip      = is_array( $step ) ? ( $step['teacher_tip'] ?? '' ) : '';
                                                    if ( $action === '' ) {
                                                        continue;
                                                    }
                                                    ?>
                                                    <li class="resource-instruction-step">
                                                        <span class="resource-instruction-action"><?php echo wp_kses_post( $action ); ?></span>
                                                        <?php if ( $duration !== '' ) : ?>
                                                            <span class="resource-instruction-duration"><?php echo esc_html( $duration ); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ( $res_ref !== '' ) : ?>
                                                            <span class="resource-instruction-ref"><?php echo esc_html( $res_ref ); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ( $stu_act !== '' ) : ?>
                                                            <span class="resource-instruction-student"><?php echo esc_html( $stu_act ); ?></span>
                                                        <?php endif; ?>
                                                        <?php if ( $tip !== '' ) : ?>
                                                            <p class="resource-instruction-tip"><?php echo wp_kses_post( nl2br( $tip ) ); ?></p>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ol>
                                        </section>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php
                            $diff_support = isset( $differentiation['support'] ) ? $differentiation['support'] : '';
                            $diff_stretch = isset( $differentiation['stretch'] ) ? $differentiation['stretch'] : '';
                            $diff_send    = isset( $differentiation['send'] ) ? $differentiation['send'] : '';
                            $has_diff     = $diff_support !== '' || $diff_stretch !== '' || $diff_send !== '';
                            ?>
                            <?php /* Row 2: Key definitions | Differentiation */ ?>
                            <?php if ( ! empty( $key_definitions ) || $has_diff ) : ?>
                                <div class="resource-sections-row resource-sections-row--objectives-instructions">
                                    <div class="resource-section-cell resource-section-cell--left">
                                        <?php if ( ! empty( $key_definitions ) ) : ?>
                                            <section class="resource-section resource-section--definitions" aria-labelledby="section-definitions">
                                                <h2 id="section-definitions" class="resource-section__title">
                                                    <span class="resource-section__icon resource-section__icon--info" aria-hidden="true"></span>
                                                    <?php esc_html_e( 'Key definitions', 'ai-awareness-day' ); ?>
                                                </h2>
                                                <dl class="resource-definitions">
                                                    <?php foreach ( $key_definitions as $item ) : ?>
                                                        <?php if ( is_array( $item ) && ( isset( $item['term'] ) || isset( $item['definition'] ) ) ) : ?>
                                                            <?php
                                                            $term      = isset( $item['term'] ) ? $item['term'] : '';
                                                            $def       = isset( $item['definition'] ) ? $item['definition'] : '';
                                                            $ks_adapted = ! empty( $item['key_stage_adapted'] );
                                                            ?>
                                                            <?php if ( $term !== '' || $def !== '' ) : ?>
                                                                <div class="resource-definition">
                                                                    <?php if ( $term !== '' ) : ?>
                                                                        <dt><?php echo esc_html( $term ); ?><?php if ( $ks_adapted ) : ?> <span class="resource-badge resource-badge--ks-adapted" aria-label="<?php esc_attr_e( 'Key stage adapted', 'ai-awareness-day' ); ?>"><?php esc_html_e( 'Key stage adapted', 'ai-awareness-day' ); ?></span><?php endif; ?></dt>
                                                                    <?php endif; ?>
                                                                    <?php if ( $def !== '' ) : ?>
                                                                        <dd><?php echo wp_kses_post( nl2br( $def ) ); ?></dd>
                                                                    <?php endif; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </dl>
                                            </section>
                                        <?php endif; ?>
                                    </div>
                                    <div class="resource-section-cell resource-section-cell--right">
                                        <?php if ( $has_diff ) : ?>
                                            <section class="resource-section resource-section--differentiation" aria-labelledby="section-differentiation">
                                                <h2 id="section-differentiation" class="resource-section__title">
                                                    <span class="resource-section__icon resource-section__icon--lightbulb" aria-hidden="true"></span>
                                                    <?php esc_html_e( 'Differentiation', 'ai-awareness-day' ); ?>
                                                </h2>
                                                <?php if ( $diff_support !== '' ) : ?>
                                                    <p class="resource-diff-label"><?php esc_html_e( 'Support', 'ai-awareness-day' ); ?></p>
                                                    <p class="resource-diff-content"><?php echo wp_kses_post( nl2br( $diff_support ) ); ?></p>
                                                <?php endif; ?>
                                                <?php if ( $diff_stretch !== '' ) : ?>
                                                    <p class="resource-diff-label"><?php esc_html_e( 'Stretch', 'ai-awareness-day' ); ?></p>
                                                    <p class="resource-diff-content"><?php echo wp_kses_post( nl2br( $diff_stretch ) ); ?></p>
                                                <?php endif; ?>
                                                <?php if ( $diff_send !== '' ) : ?>
                                                    <p class="resource-diff-label"><?php esc_html_e( 'SEND', 'ai-awareness-day' ); ?></p>
                                                    <p class="resource-diff-content"><?php echo wp_kses_post( nl2br( $diff_send ) ); ?></p>
                                                <?php endif; ?>
                                            </section>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $extensions ) ) : ?>
                                <div class="resource-sections-row resource-sections-row--full">
                                    <div class="resource-section-cell resource-section-cell--full">
                                        <section class="resource-section resource-section--extensions" aria-labelledby="section-extensions">
                                            <h2 id="section-extensions" class="resource-section__title">
                                                <span class="resource-section__icon resource-section__icon--lightbulb" aria-hidden="true"></span>
                                                <?php esc_html_e( 'Extension activities', 'ai-awareness-day' ); ?>
                                            </h2>
                                            <ul class="resource-list resource-list--extensions">
                                                <?php foreach ( $extensions as $ext ) : ?>
                                                    <?php
                                                    $act = is_array( $ext ) ? ( $ext['activity'] ?? '' ) : '';
                                                    $typ = is_array( $ext ) && isset( $ext['type'] ) ? $ext['type'] : '';
                                                    if ( $act === '' ) {
                                                        continue;
                                                    }
                                                    $type_labels = array( 'homework' => __( 'Homework', 'ai-awareness-day' ), 'next_lesson' => __( 'Next lesson', 'ai-awareness-day' ), 'cross_curricular' => __( 'Cross-curricular', 'ai-awareness-day' ), 'independent' => __( 'Independent', 'ai-awareness-day' ) );
                                                    ?>
                                                    <li><?php echo wp_kses_post( $act ); ?><?php if ( $typ !== '' && isset( $type_labels[ $typ ] ) ) : ?> <span class="resource-badge"><?php echo esc_html( $type_labels[ $typ ] ); ?></span><?php endif; ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( $preview_video_html || $pptx_embed_url ) : ?>
                                <div class="resource-sections-row resource-sections-row--full">
                                    <div class="resource-section-cell resource-section-cell--full">
                                        <div class="resource-pptx-preview<?php echo $preview_video_html ? ' resource-pptx-preview--video' : ''; ?>" data-post-id="<?php echo esc_attr( (string) get_the_ID() ); ?>">
                                            <div class="resource-pptx-preview__toolbar">
                                                <span class="resource-pptx-preview__label">
                                                    <?php if ( $preview_video_html ) : ?>
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                                        <?php esc_html_e( 'Video preview', 'ai-awareness-day' ); ?>
                                                    <?php else : ?>
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                                                        <?php esc_html_e( 'Presentation preview', 'ai-awareness-day' ); ?>
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ( $preview_dl_url ) : ?>
                                                    <a href="<?php echo esc_url( $preview_dl_url ); ?>" class="resource-pptx-preview__download" download target="_blank" rel="noopener noreferrer">
                                                        <?php echo esc_html( function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $preview_dl_url ) : __( 'Download', 'ai-awareness-day' ) ); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <div class="resource-pptx-preview__frame-wrap<?php echo $preview_video_html ? ' resource-pptx-preview__frame-wrap--embed' : ''; ?>">
                                                <?php if ( $preview_video_html ) : ?>
                                                    <div class="resource-preview-embed">
                                                        <?php
                                                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML from wp_oembed_get() (trusted providers) or escaped <video> in aiad_resource_preview_video_html().
                                                        echo $preview_video_html;
                                                        ?>
                                                    </div>
                                                <?php else : ?>
                                                    <iframe
                                                        src="<?php echo esc_url( $pptx_embed_url ); ?>"
                                                        class="resource-pptx-preview__iframe"
                                                        frameborder="0"
                                                        allowfullscreen
                                                        title="<?php esc_attr_e( 'Presentation preview', 'ai-awareness-day' ); ?>"
                                                        loading="lazy"
                                                    ></iframe>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if ( ! empty( $resources_list ) ) : ?>
                                <div class="resource-sections-row resource-sections-row--full">
                                    <div class="resource-section-cell resource-section-cell--full">
                                        <section class="resource-section resource-section--resources" aria-labelledby="section-resources">
                                            <h2 id="section-resources" class="resource-section__title">
                                                <span class="resource-section__icon resource-section__icon--info" aria-hidden="true"></span>
                                                <?php esc_html_e( 'Resources', 'ai-awareness-day' ); ?>
                                            </h2>
                                            <ul class="resource-list resource-list--resources">
                                                <?php foreach ( $resources_list as $res ) : ?>
                                                    <?php
                                                    $rname = is_array( $res ) ? ( $res['name'] ?? '' ) : '';
                                                    $rtype = is_array( $res ) && isset( $res['type'] ) ? $res['type'] : '';
                                                    $rurl  = is_array( $res ) && isset( $res['url'] ) ? $res['url'] : '';
                                                    $r_type_labels = array( 'slides' => __( 'Slides', 'ai-awareness-day' ), 'worksheet' => __( 'Worksheet', 'ai-awareness-day' ), 'handout' => __( 'Handout', 'ai-awareness-day' ), 'video' => __( 'Video', 'ai-awareness-day' ), 'link' => __( 'Link', 'ai-awareness-day' ), 'other' => __( 'Other', 'ai-awareness-day' ) );
                                                    ?>
                                                    <li>
                                                        <?php if ( $rurl !== '' ) : ?><a href="<?php echo esc_url( $rurl ); ?>" target="_blank" rel="noopener"><?php endif; ?>
                                                        <?php echo $rname !== '' ? esc_html( $rname ) : esc_html__( 'Resource', 'ai-awareness-day' ); ?>
                                                        <?php if ( $rtype !== '' && isset( $r_type_labels[ $rtype ] ) ) : ?> <span class="resource-badge"><?php echo esc_html( $r_type_labels[ $rtype ] ); ?></span><?php endif; ?>
                                                        <?php if ( $rurl !== '' ) : ?></a><?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <p class="resource-sections-empty" style="color: var(--text-muted, #6b7280); font-size: 0.95rem;">
                                <?php esc_html_e( 'Add Preparation, Key definitions, Learning objectives, Instructions and Teacher notes in the "Resource content sections" box when editing this resource.', 'ai-awareness-day' ); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <?php
                    $download_url = get_post_meta( get_the_ID(), '_aiad_download_url', true );
                    $download_label = $download_url && function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $download_url ) : __( 'Download', 'ai-awareness-day' );
                    ?>
                    <footer class="resource-activity-footer">
                        <a href="<?php echo esc_url( get_post_type_archive_link( 'resource' ) ); ?>" class="resource-footer-btn resource-footer-btn--secondary resource-back-btn">
                            <span class="resource-back-btn__icon" aria-hidden="true">
                                <?php
                                if ( function_exists( 'aiad_back_icon_svg' ) ) {
                                    echo aiad_back_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                } else {
                                    echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
                                }
                                ?>
                            </span>
                            <span class="resource-back-btn__label"><?php esc_html_e( 'Back to Resources', 'ai-awareness-day' ); ?></span>
                        </a>
                        <?php if ( $download_url ) : ?>
                            <a href="<?php echo esc_url( $download_url ); ?>" class="resource-footer-btn resource-footer-btn--primary resource-download-link" data-resource-id="<?php echo esc_attr( (string) get_the_ID() ); ?>" download target="_blank" rel="noopener">
                                <?php echo esc_html( $download_label ); ?>
                            </a>
                        <?php endif; ?>
                        <button type="button"
                                class="resource-footer-btn resource-footer-btn--print resource-print-btn"
                                aria-label="<?php esc_attr_e( 'Print this resource', 'ai-awareness-day' ); ?>">
                            <span class="resource-print-btn__icon" aria-hidden="true">
                                <?php
                                if ( function_exists( 'aiad_print_icon_svg' ) ) {
                                    echo aiad_print_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                } else {
                                    echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>';
                                }
                                ?>
                            </span>
                            <span class="resource-print-btn__label"><?php esc_html_e( 'Print', 'ai-awareness-day' ); ?></span>
                        </button>
                        <?php
                        // Share button with pre-written message
                        $share_message = function_exists( 'aiad_get_share_message' ) ? aiad_get_share_message( 'resource', get_post() ) : '';
                        $share_url = get_permalink();
                        $share_title = get_the_title();
                        $key_stages_for_card = (array) get_post_meta( get_the_ID(), '_aiad_key_stage', true );
                        $theme_for_card = $theme_name ? $theme_name : '';
                        ?>
                        <button type="button"
                                class="resource-footer-btn resource-footer-btn--share resource-share-btn"
                                data-url="<?php echo esc_url( $share_url ); ?>"
                                data-title="<?php echo esc_attr( $share_title ); ?>"
                                data-text="<?php echo esc_attr( $share_message ); ?>"
                                aria-label="<?php esc_attr_e( 'Share this resource', 'ai-awareness-day' ); ?>">
                            <span class="resource-share-btn__icon" aria-hidden="true">
                                <?php
                                // Reuse timeline share icon SVG function if available, otherwise inline SVG
                                if ( function_exists( 'aiad_timeline_share_icon_svg' ) ) {
                                    echo aiad_timeline_share_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                } else {
                                    echo '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';
                                }
                                ?>
                            </span>
                            <span class="resource-share-btn__label"><?php esc_html_e( 'Share', 'ai-awareness-day' ); ?></span>
                        </button>
                        <button type="button"
                                class="resource-footer-btn resource-footer-btn--share resource-social-card-btn"
                                data-title="<?php echo esc_attr( $share_title ); ?>"
                                data-url="<?php echo esc_url( $share_url ); ?>"
                                data-theme="<?php echo esc_attr( $theme_for_card ); ?>"
                                data-key-stages="<?php echo esc_attr( implode( ', ', $key_stages_for_card ) ); ?>"
                                aria-label="<?php esc_attr_e( 'Generate share image', 'ai-awareness-day' ); ?>">
                            <span class="resource-share-btn__label"><?php esc_html_e( 'Generate share image', 'ai-awareness-day' ); ?></span>
                        </button>
                        <button type="button"
                                class="resource-footer-btn resource-footer-btn--secondary resource-bookmark-btn"
                                data-resource-id="<?php echo esc_attr((string) get_the_ID()); ?>"
                                data-resource-title="<?php echo esc_attr( $share_title ); ?>"
                                data-resource-url="<?php echo esc_url( $share_url ); ?>"
                                aria-pressed="false"
                                aria-label="<?php esc_attr_e('Save resource', 'ai-awareness-day'); ?>">
                            <?php esc_html_e('Save', 'ai-awareness-day'); ?>
                        </button>
                    </footer>
                </article>
            <?php endwhile; ?>
            <aside class="saved-resources-panel" data-saved-resources-panel hidden>
                <div class="saved-resources-panel__header">
                    <h2><?php esc_html_e( 'My saved resources', 'ai-awareness-day' ); ?></h2>
                    <button type="button" class="saved-resources-panel__close" data-saved-resources-close aria-label="<?php esc_attr_e( 'Close saved resources', 'ai-awareness-day' ); ?>">×</button>
                </div>
                <ul class="saved-resources-panel__list" data-saved-resources-list></ul>
            </aside>
    </section>
</main>

<?php get_footer(); ?>
