<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

// Soft-launch switch: Appearance > Customise > Hero Section > Homepage hero can bring back the previous hero.
if ( aiad_homepage_hero_is_previous() ) {
    include __DIR__ . '/section-hero-previous.php';
    return;
}

/* Until the conversation opens the portal (register, sign in, nominate) is not offered here: the button explains what
   the conversation is instead. aiad_portal_is_live() brings the portal links back on the opening day. */
$portal_live  = function_exists( 'aiad_portal_is_live' ) ? aiad_portal_is_live() : true;
$join_url     = $portal_live ? ( aiad_conversation_url( 'register' ) ?: '#contact' ) : aiad_national_conversation_page_url();
$sign_in_url  = $portal_live ? aiad_conversation_url( 'join' ) : '';
$nominate_url = $portal_live ? aiad_conversation_url( 'nominate' ) : '';
$countdown    = aiad_national_conversation_countdown();
$totals       = $portal_live ? aiad_national_conversation_totals() : null; // The live totals line starts on the opening day, like the portal.

?>
<section class="hero-section <?php echo esc_attr( $text_alignment_class ); ?>" id="hero" data-strand="safe">
    <div class="container">
        <div class="hero-title-block">
            <div class="hero-copy">
                <?php /* The lockup's own device, "2027" underlined, set as live text so it
                         can sit on the copy's left edge. The SVG lockup is right-anchored. */ ?>
                <p class="hero-eyebrow"><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?> <span class="hero-eyebrow__year">2027</span></p>
                <h1 class="hero-title">
                    <span class="hero-title__line"><?php esc_html_e( 'Keep Humans', 'ai-awareness-day' ); ?></span>
                    <span class="hero-title__line"><?php esc_html_e( 'in the Loop', 'ai-awareness-day' ); ?></span>
                </h1>
                <?php if ( function_exists( 'aiad_national_conversation_dates' ) ) : ?>
                <p class="hero-eyebrow-date"><?php echo esc_html( wp_date( 'l jS F Y', aiad_national_conversation_dates()['event']->getTimestamp() ) ); ?></p>
                <?php endif; ?>
                <p class="hero-subtitle"><strong><?php esc_html_e( 'Humans in the Loop should mean something tangible.', 'ai-awareness-day' ); ?></strong> <?php esc_html_e( 'In 2026 we encouraged AI literacy through lessons and display boards. In 2027 we want young people across the UK to question, discuss and debate the role AI should play in their lives and futures.', 'ai-awareness-day' ); ?></p>
                <div class="hero-cta">
                    <a href="#contact" class="hero-cta__btn hero-cta__btn--secondary"><?php esc_html_e( 'Get involved', 'ai-awareness-day' ); ?></a>
                    <a href="<?php echo esc_url( $join_url ); ?>" class="hero-cta__btn hero-cta__btn--primary"><?php esc_html_e( 'Join the National Conversation', 'ai-awareness-day' ); ?></a>
                </div>
                <?php if ( $nominate_url || $sign_in_url ) : ?>
                <p class="hero-cta-more">
                    <?php if ( $nominate_url ) : ?><a href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Nominate a school you work with', 'ai-awareness-day' ); ?></a><?php endif; ?>
                    <?php if ( $sign_in_url ) : ?><a href="<?php echo esc_url( $sign_in_url ); ?>"><?php esc_html_e( 'Already registered? Sign in', 'ai-awareness-day' ); ?></a><?php endif; ?>
                </p>
                <?php endif; ?>
                <?php if ( $countdown || $totals ) : ?>
                <?php /* Facts row: set on the strand ground under a rule, so it reads as
                         supporting detail rather than a second dark box beside the panel. */ ?>
                <div class="hero-facts">
                    <?php
                    if ( $countdown ) :
                        $days_until = max( 0, (int) floor( ( $countdown['ts_ms'] / 1000 - time() ) / DAY_IN_SECONDS ) );
                        ?>
                    <div class="hero-countdown-wrap">
                        <p class="hero-countdown__title" id="hero-countdown-title"><?php echo esc_html( $countdown['label'] ); ?></p>
                        <div class="hero-countdown" role="timer" aria-labelledby="hero-countdown-title" data-event-date="<?php echo esc_attr( $countdown['date'] ); ?>" data-event-ts="<?php echo esc_attr( (string) $countdown['ts_ms'] ); ?>">
                            <div class="hero-countdown__item"><span class="hero-countdown__value" data-unit="days"><?php echo esc_html( (string) $days_until ); ?></span><span class="hero-countdown__label"><?php esc_html_e( 'Days', 'ai-awareness-day' ); ?></span></div>
                            <div class="hero-countdown__item"><span class="hero-countdown__value" data-unit="hours">00</span><span class="hero-countdown__label"><?php esc_html_e( 'Hours', 'ai-awareness-day' ); ?></span></div>
                            <div class="hero-countdown__item"><span class="hero-countdown__value" data-unit="minutes">00</span><span class="hero-countdown__label"><?php esc_html_e( 'Minutes', 'ai-awareness-day' ); ?></span></div>
                            <div class="hero-countdown__item"><span class="hero-countdown__value" data-unit="seconds">00</span><span class="hero-countdown__label"><?php esc_html_e( 'Seconds', 'ai-awareness-day' ); ?></span></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ( $totals ) : ?>
                    <ul class="hero-totals">
                        <?php
                        $total_lines = array(
                            /* translators: %s: number of schools */
                            'schools'  => __( '%s schools', 'ai-awareness-day' ),
                            /* translators: %s: number of debates judged */
                            'debates'  => __( '%s debates judged', 'ai-awareness-day' ),
                            /* translators: %s: number of students */
                            'students' => __( '%s students taking part', 'ai-awareness-day' ),
                        );
                        foreach ( $total_lines as $key => $line ) :
                            ?>
                        <li><?php printf( esc_html( $line ), '<strong>' . esc_html( number_format_i18n( $totals[ $key ] ) ) . '</strong>' ); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <div class="hero-strand-feature" aria-label="<?php esc_attr_e( 'The five AI Awareness Day strands', 'ai-awareness-day' ); ?>">
                <?php /* The box is the way in to the National AI Conversation, so it carries the name. */ ?>
                <div class="hero-strand-feature__head">
                    <p class="hero-strand-feature__title"><?php esc_html_e( 'National AI Conversation 2027', 'ai-awareness-day' ); ?></p>
                    <?php if ( function_exists( 'aiad_national_conversation_dates' ) ) : ?>
                    <p class="hero-strand-feature__starts">
                        <?php
                        if ( $portal_live ) {
                            esc_html_e( 'Now open', 'ai-awareness-day' );
                        } else {
                            /* translators: %s: month and year the conversation opens, e.g. January 2027 */
                            printf( esc_html__( 'Starting %s', 'ai-awareness-day' ), esc_html( wp_date( 'F Y', aiad_national_conversation_dates()['opens']->getTimestamp() ) ) );
                        }
                        ?>
                    </p>
                    <?php endif; ?>
                    <?php /* The switcher leads, above the strand word it changes. */ ?>
                    <div class="hero-strand-feature__themes" role="navigation" aria-label="<?php esc_attr_e( 'Choose a strand', 'ai-awareness-day' ); ?>">
                        <?php foreach ( array( 'safe' => 'Safe', 'smart' => 'Smart', 'creative' => 'Creative', 'responsible' => 'Responsible', 'future' => 'Future' ) as $slug => $label ) : ?>
                            <a href="#themes" class="hero-strand-feature__theme<?php echo $slug === 'safe' ? ' is-active' : ''; ?>" data-strand-target="<?php echo esc_attr( $slug ); ?>" aria-current="<?php echo $slug === 'safe' ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="hero-strand-feature__stage" aria-live="off">
                    <span class="hero-strand-feature__mark" aria-hidden="true"></span>
                    <span class="hero-strand-feature__word">Safe</span>
                </div>
                <?php /* The box asks one thing: the motion to debate for the strand on show. main.js changes it with the strand. */ ?>
                <p class="hero-strand-feature__summary"><span class="hero-strand-feature__motion-label"><?php esc_html_e( 'Debate it', 'ai-awareness-day' ); ?></span> <span class="hero-strand-feature__motion-text"><?php esc_html_e( 'This house believes users, not companies, should control what AI remembers about them.', 'ai-awareness-day' ); ?></span></p>
            </div>
        </div>
    </div>
</section>
