<?php
/**
 * Front page section: YouTube / Video
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}
$defaults        = aiad_get_customizer_defaults();
$youtube_url    = get_theme_mod( 'aiad_youtube_url', $defaults['aiad_youtube_url'] );
$youtube_title  = get_theme_mod( 'aiad_youtube_title', $defaults['aiad_youtube_title'] );
$youtube_video_id = function_exists( 'aiad_youtube_video_id' ) ? aiad_youtube_video_id( $youtube_url ) : '';
if ( ! $youtube_video_id ) {
    return;
}
?>
<section class="section section--alt <?php echo esc_attr( $text_alignment_class ); ?>" id="youtube">
    <div class="container">
        <div class="youtube-header fade-up">
            <?php if ( $youtube_title ) : ?>
                <span class="section-label"><?php esc_html_e( 'Video', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title"><?php echo esc_html( $youtube_title ); ?></h2>
            <?php endif; ?>
        </div>
        <div class="video-wrapper video-wrapper--left">
            <iframe
                src="https://www.youtube.com/embed/<?php echo esc_attr( $youtube_video_id ); ?>?rel=0&modestbranding=1&autoplay=1&mute=1"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen loading="lazy" width="560" height="315"
                title="<?php echo esc_attr( $youtube_title ?: __( 'YouTube video', 'ai-awareness-day' ) ); ?>"></iframe>
        </div>
    </div>
</section>
