<?php
/**
 * Front page section: National Survey call-to-action.
 *
 * Post-event prompt — AI Awareness Day has happened, so now we invite schools
 * and organisations to share their experience. Only renders while the survey
 * page is published, so it self-removes when the page is unpublished.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : '';

// Resolve the published national survey page URL.
$aiad_survey_url = '';
$aiad_survey_pid = (int) get_option( 'aiad_survey_page_created' );
if ( $aiad_survey_pid && 'publish' === get_post_status( $aiad_survey_pid ) ) {
    $aiad_survey_url = (string) get_permalink( $aiad_survey_pid );
} else {
    $aiad_survey_page = get_page_by_path( 'national-survey-2026' );
    if ( $aiad_survey_page && 'publish' === $aiad_survey_page->post_status ) {
        $aiad_survey_url = (string) get_permalink( $aiad_survey_page );
    }
}

if ( $aiad_survey_url === '' ) {
    return;
}
?>
<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="survey-cta" aria-label="<?php esc_attr_e( 'National survey', 'ai-awareness-day' ); ?>">
    <div class="container">
        <div class="campaign-split campaign-split--single">
            <div class="campaign-content fade-up">
                <span class="section-label"><?php esc_html_e( 'Have your say', 'ai-awareness-day' ); ?></span>
                <h2 class="section-title">
                    <?php esc_html_e( 'AI Awareness Day 2026 has happened — now it’s time to hear your voice.', 'ai-awareness-day' ); ?>
                </h2>
                <p class="section-desc">
                    <?php esc_html_e( 'Tell us how it went for your school or organisation and help shape AI Awareness Day 2027. It takes around 3 minutes.', 'ai-awareness-day' ); ?>
                </p>
                <div class="hero-cta survey-cta">
                    <a class="hero-cta__btn hero-cta__btn--primary" href="<?php echo esc_url( $aiad_survey_url ); ?>">
                        <?php esc_html_e( 'Take the 3-minute survey', 'ai-awareness-day' ); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
