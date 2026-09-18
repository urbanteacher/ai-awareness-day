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
                    <h1 class="hero-date">
                        <span class="hero-date__line"><?php esc_html_e( 'Keep Humans in', 'ai-awareness-day' ); ?></span>
                        <span class="hero-date__line hero-date__line--loop"><?php esc_html_e( 'the Loop', 'ai-awareness-day' ); ?></span>
                    </h1>
                    <span class="hero-brand__mark" aria-hidden="true"></span>
                </div>
                <p class="hero-subtitle"><strong><?php esc_html_e( 'We are back for 2027.', 'ai-awareness-day' ); ?></strong> <?php esc_html_e( 'A nationwide day for schools, students, and parents to explore AI together.', 'ai-awareness-day' ); ?></p>
                <div class="hero-cta">
                    <a href="#contact" class="hero-cta__btn hero-cta__btn--primary"><?php esc_html_e( 'Get involved', 'ai-awareness-day' ); ?></a>
                    <a href="<?php echo esc_url( aiad_get_benchmark_start_url() ); ?>" class="hero-cta__btn hero-cta__btn--secondary"><?php esc_html_e( 'Check your AI readiness', 'ai-awareness-day' ); ?></a>
                </div>
            </div>
            <div class="hero-strand-feature" aria-label="<?php esc_attr_e( 'The five AI Awareness Day strands', 'ai-awareness-day' ); ?>">
                <?php /* The switcher leads, above the strand word it changes. Reordered
                         in the markup rather than with CSS order so reading order and
                         tab order follow what is on screen. */ ?>
                <div class="hero-strand-feature__themes" role="navigation" aria-label="<?php esc_attr_e( 'Choose a strand', 'ai-awareness-day' ); ?>">
                    <?php foreach ( array( 'safe' => 'Safe', 'smart' => 'Smart', 'creative' => 'Creative', 'responsible' => 'Responsible', 'future' => 'Future' ) as $slug => $label ) : ?>
                        <a href="#themes" class="hero-strand-feature__theme<?php echo $slug === 'safe' ? ' is-active' : ''; ?>" data-strand-target="<?php echo esc_attr( $slug ); ?>" aria-current="<?php echo $slug === 'safe' ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></a>
                    <?php endforeach; ?>
                </div>
                <div class="hero-strand-feature__stage" aria-live="off">
                    <span class="hero-strand-feature__word">Safe</span>
                    <?php /* Decorative twin of .hero-brand__mark. On narrow screens the mark
                             sits beside the strand word instead of the hero title, where it
                             was forcing the title column wider than the viewport. */ ?>
                    <span class="hero-strand-feature__mark" aria-hidden="true"></span>
                </div>
                <p class="hero-strand-feature__summary"><?php esc_html_e( 'Would you tell an AI your secret?', 'ai-awareness-day' ); ?></p>
            </div>
        </div>
    </div>
</section>
