<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$hero_marquee = function_exists( 'aiad_get_hero_partner_marquee_entries' )
    ? aiad_get_hero_partner_marquee_entries()
    : array();
?>
<?php
/*
 * The partner strip sits above the hero, on cream, rather than inside it.
 * Partner logos are third-party uploads of mixed provenance — several carry a
 * background baked into the artwork — so they never sat well on a strand
 * ground, whatever treatment was applied to them.
 */
if ( ! empty( $hero_marquee ) ) :
    $marquee_count = count( $hero_marquee );
    // Slower when more logos so the strip does not feel frantic.
    $marquee_secs = (int) min( 90, max( 28, $marquee_count * 5 ) );
    ?>
<section class="partner-strip">
    <div class="hero-partner-marquee" role="region"
        aria-label="<?php esc_attr_e( 'Partner organisations', 'ai-awareness-day' ); ?>">
        <div class="hero-partner-marquee__viewport">
            <div class="hero-partner-marquee__track"
                style="<?php echo esc_attr( '--hero-marquee-duration: ' . $marquee_secs . 's' ); ?>">
                <?php foreach ( array( 1, 2 ) as $_dup ) : ?>
                <ul class="hero-partner-marquee__list">
                    <?php foreach ( $hero_marquee as $row ) : ?>
                    <li class="hero-partner-marquee__item">
                        <a class="hero-partner-marquee__link" href="<?php echo esc_url( $row['href'] ); ?>" data-partner-id="<?php echo esc_attr( (string) $row['id'] ); ?>">
                            <img class="hero-partner-marquee__img"
                                src="<?php echo esc_url( $row['img'] ); ?>"
                                alt="<?php echo esc_attr( $row['title'] ); ?>"
                                width="240"
                                height="120"
                                loading="lazy"
                                decoding="async" />
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
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
                    <a href="#contact" class="hero-cta__btn hero-cta__btn--primary"><?php esc_html_e( 'Bring AiAd27 to your school', 'ai-awareness-day' ); ?></a>
                    <a href="<?php echo esc_url( aiad_get_benchmark_start_url() ); ?>" class="hero-cta__btn hero-cta__btn--secondary"><?php esc_html_e( 'Check your AI readiness', 'ai-awareness-day' ); ?></a>
                </div>
            </div>
            <div class="hero-strand-feature" aria-label="<?php esc_attr_e( 'The five AI Awareness Day strands', 'ai-awareness-day' ); ?>">
                <div class="hero-strand-feature__stage" aria-live="off">
                    <span class="hero-strand-feature__word">Safe</span>
                    <?php /* Decorative twin of .hero-brand__mark. On narrow screens the mark
                             sits beside the strand word instead of the hero title, where it
                             was forcing the title column wider than the viewport. */ ?>
                    <span class="hero-strand-feature__mark" aria-hidden="true"></span>
                </div>
                <p class="hero-strand-feature__summary"><?php esc_html_e( 'Would you tell an AI your secret?', 'ai-awareness-day' ); ?></p>
                <div class="hero-strand-feature__themes" role="navigation" aria-label="<?php esc_attr_e( 'Choose a strand', 'ai-awareness-day' ); ?>">
                    <?php foreach ( array( 'safe' => 'Safe', 'smart' => 'Smart', 'creative' => 'Creative', 'responsible' => 'Responsible', 'future' => 'Future' ) as $slug => $label ) : ?>
                        <a href="#themes" class="hero-strand-feature__theme<?php echo $slug === 'safe' ? ' is-active' : ''; ?>" data-strand-target="<?php echo esc_attr( $slug ); ?>" aria-current="<?php echo $slug === 'safe' ? 'true' : 'false'; ?>"><?php echo esc_html( $label ); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
