<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

?>
<section class="hero-section <?php echo esc_attr( $text_alignment_class ); ?>" id="hero" data-strand="safe">
    <div class="container">
        <div class="hero-title-block">
            <div class="hero-copy">
                <div class="hero-brand">
                    <img class="hero-lockup" src="<?php echo esc_url( AIAD_URI . '/assets/brand/aiad27/aiad27-lockup-wordmark.svg' ); ?>" alt="AI Awareness Day 2027" width="300" height="36" />
                    <?php /* Two versions, shown one at a time by breakpoint (CSS display,
                             not JS). Desktop keeps the two-line "Keep Humans in / the Loop".
                             Two lines on mobile cost more vertical space than the phrase is
                             worth there, so mobile gets the whole thing on one line at a
                             smaller size instead of a different line break. */ ?>
                    <h1 class="hero-date">
                        <span class="hero-date__lines hero-date__lines--desktop">
                            <span class="hero-date__line"><?php esc_html_e( 'Keep Humans in', 'ai-awareness-day' ); ?></span>
                            <span class="hero-date__line hero-date__line--loop"><?php esc_html_e( 'the Loop', 'ai-awareness-day' ); ?></span>
                        </span>
                        <span class="hero-date__lines hero-date__lines--mobile">
                            <span class="hero-date__line hero-date__line--single"><?php esc_html_e( 'Keep Humans in the Loop', 'ai-awareness-day' ); ?></span>
                        </span>
                    </h1>
                    <span class="hero-brand__mark" aria-hidden="true"></span>
                </div>
                <p class="hero-subtitle"><strong><?php esc_html_e( 'We are back for 2027.', 'ai-awareness-day' ); ?></strong> <?php esc_html_e( 'A nationwide day for schools, students, and parents to explore AI together.', 'ai-awareness-day' ); ?></p>
                <?php
                $join_url     = aiad_conversation_url( 'register' ) ?: '#contact';
                $sign_in_url  = aiad_conversation_url( 'join' );
                $nominate_url = aiad_conversation_url( 'nominate' );
                ?>
                <div class="hero-cta">
                    <a href="<?php echo esc_url( $join_url ); ?>" class="hero-cta__btn hero-cta__btn--primary"><?php esc_html_e( 'Join the National Conversation', 'ai-awareness-day' ); ?></a>
                    <a href="<?php echo esc_url( aiad_get_benchmark_start_url() ); ?>" class="hero-cta__btn hero-cta__btn--secondary"><?php esc_html_e( 'Check your AI readiness', 'ai-awareness-day' ); ?></a>
                </div>
                <?php if ( $nominate_url || $sign_in_url ) : ?>
                <p class="hero-cta-more">
                    <?php if ( $nominate_url ) : ?><a href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Nominate a school you work with', 'ai-awareness-day' ); ?></a><?php endif; ?>
                    <?php if ( $nominate_url && $sign_in_url ) : ?> &middot; <?php endif; ?>
                    <?php if ( $sign_in_url ) : ?><a href="<?php echo esc_url( $sign_in_url ); ?>"><?php esc_html_e( 'Already registered? Sign in', 'ai-awareness-day' ); ?></a><?php endif; ?>
                </p>
                <?php endif; ?>
                <?php
                $countdown = aiad_national_conversation_countdown();
                $totals    = aiad_national_conversation_totals();
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
                <p class="hero-totals"><?php
                    printf(
                        /* translators: 1: schools, 2: debates judged, 3: students */
                        esc_html__( '%1$s schools · %2$s debates judged · %3$s students taking part', 'ai-awareness-day' ),
                        '<strong>' . esc_html( number_format_i18n( $totals['schools'] ) ) . '</strong>',
                        '<strong>' . esc_html( number_format_i18n( $totals['debates'] ) ) . '</strong>',
                        '<strong>' . esc_html( number_format_i18n( $totals['students'] ) ) . '</strong>'
                    );
                ?></p>
                <?php endif; ?>
            </div>
            <div class="hero-strand-feature" aria-label="<?php esc_attr_e( 'The five AI Awareness Day strands', 'ai-awareness-day' ); ?>">
                <?php /* The box is the way in to the National AI Conversation, so it carries the name. */ ?>
                <p class="hero-strand-feature__title"><?php esc_html_e( 'National AI Conversation 2027', 'ai-awareness-day' ); ?></p>
                <?php /* The switcher leads, above the strand word it changes. Reordered
                         in the markup rather than with CSS order so reading order and
                         tab order follow what is on screen. */ ?>
                <div class="hero-strand-feature__themes" role="navigation" aria-label="<?php esc_attr_e( 'Choose a strand', 'ai-awareness-day' ); ?>">
                    <?php foreach ( array( 'safe' => 'Safe', 'smart' => 'Smart', 'creative' => 'Creative', 'responsible' => 'Responsible', 'future' => 'Future' ) as $slug => $label ) : ?>
                        <a href="#themes" class="hero-strand-feature__theme<?php echo $slug === 'safe' ? ' is-active' : ''; ?>" data-strand-target="<?php echo esc_attr( $slug ); ?>" aria-current="<?php echo $slug === 'safe' ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="hero-strand-feature__stage" aria-live="off">
                    <?php /* Decorative twin of .hero-brand__mark. On narrow screens the mark
                             sits beside the strand word instead of the hero title, where it
                             was forcing the title column wider than the viewport. Icon first,
                             word second — the reading order of "mark, then its name". */ ?>
                    <span class="hero-strand-feature__mark" aria-hidden="true"></span>
                    <span class="hero-strand-feature__word">Safe</span>
                </div>
                <?php /* The box asks one thing: the motion to debate for the strand on show. main.js changes it with the strand. */ ?>
                <p class="hero-strand-feature__summary"><span class="hero-strand-feature__motion-label"><?php esc_html_e( 'Debate it', 'ai-awareness-day' ); ?></span> <span class="hero-strand-feature__motion-text"><?php esc_html_e( 'This house believes users, not companies, should control what AI remembers about them.', 'ai-awareness-day' ); ?></span></p>
            </div>
        </div>
    </div>
</section>
