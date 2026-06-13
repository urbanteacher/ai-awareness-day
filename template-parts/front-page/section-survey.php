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
<section class="section aiad-survey-banner" id="survey-cta" aria-label="<?php esc_attr_e( 'National survey', 'ai-awareness-day' ); ?>">
    <div class="container">
        <div class="aiad-survey-banner__panel fade-up">
            <div class="aiad-survey-banner__text">
                <span class="section-label aiad-survey-banner__eyebrow"><?php esc_html_e( 'Have your say', 'ai-awareness-day' ); ?></span>
                <h2 class="aiad-survey-banner__title">
                    <?php esc_html_e( 'AI Awareness Day 2026 has happened — now it’s time to hear your voice.', 'ai-awareness-day' ); ?>
                </h2>
                <p class="aiad-survey-banner__desc">
                    <?php esc_html_e( 'Tell us how it went for your school or organisation and help shape AI Awareness Day 2027. It takes around 3 minutes.', 'ai-awareness-day' ); ?>
                </p>
            </div>
            <div class="aiad-survey-banner__actions">
                <a class="aiad-survey-banner__cta" href="<?php echo esc_url( $aiad_survey_url ); ?>">
                    <?php esc_html_e( 'Take the 3-minute survey →', 'ai-awareness-day' ); ?>
                </a>
            </div>
        </div>
    </div>
</section>
