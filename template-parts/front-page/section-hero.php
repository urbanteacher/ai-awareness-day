<?php
/**
 * Front page section: Hero
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$text_alignment_class = isset( $args['text_alignment_class'] ) ? (string) $args['text_alignment_class'] : aiad_get_text_alignment_class();

/* Until the conversation opens the portal (register, sign in, nominate) is not offered here: the button explains what
   the conversation is instead. aiad_portal_is_live() brings the portal links back on the opening day. */
$portal_live  = function_exists( 'aiad_portal_is_live' ) ? aiad_portal_is_live() : true;
$join_url     = $portal_live ? ( aiad_conversation_url( 'register' ) ?: '#contact' ) : aiad_national_conversation_page_url();
$sign_in_url  = $portal_live ? aiad_conversation_url( 'join' ) : '';
$nominate_url = $portal_live ? aiad_conversation_url( 'nominate' ) : '';
$countdown    = aiad_national_conversation_countdown();
$totals       = $portal_live ? aiad_national_conversation_totals() : null; // The live totals line starts on the opening day, like the portal.
$nc_page_url  = function_exists( 'aiad_national_conversation_page_url' ) ? aiad_national_conversation_page_url() : home_url( '/national-conversation/' );
$nc_dates     = function_exists( 'aiad_national_conversation_dates' ) ? aiad_national_conversation_dates() : null;
$library_url  = get_post_type_archive_link( 'resource' ) ?: home_url( '/resources/' );

?>
<?php
/* Two offers, kept apart (archive/design-kit/home-hero-campaign-vs-conversation.html, option B): the hero is AI
   Awareness Day, the campaign, in the Day's purple, with its date, countdown and buttons. The National AI Conversation
   is its flagship programme, in a dark lane of its own straight after the hero, with its own dates and buttons in its
   own blue. */
?>
<section class="hero-section hero-section--day <?php echo esc_attr( $text_alignment_class ); ?>" id="hero" data-strand="creative">
    <div class="container">
        <div class="hero-title-block">
            <div class="hero-copy">
                <?php /* The lockup's own device, "2027" underlined, set as live text so it
                         can sit on the copy's left edge. The SVG lockup is right-anchored. */ ?>
                <p class="hero-eyebrow"><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?> <span class="hero-eyebrow__year">2027</span></p>
                <h1 class="hero-title">
                    <span class="hero-title__line"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_title_1' ) ); ?></span>
                    <span class="hero-title__line"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_title_2' ) ); ?></span>
                </h1>
                <?php if ( function_exists( 'aiad_national_conversation_dates' ) ) : ?>
                <p class="hero-eyebrow-date"><?php echo esc_html( wp_date( 'l jS F Y', aiad_national_conversation_dates()['event']->getTimestamp() ) ); ?></p>
                <?php endif; ?>
                <?php /* The Day's buttons only. The Conversation's are in its panel below. */ ?>
                <div class="hero-cta">
                    <a href="<?php echo esc_url( aiad_hero27_text( 'aiad_hero27_involved_url' ) ); ?>" class="hero-cta__btn hero-cta__btn--primary"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_involved_label' ) ); ?></a>
                    <a href="<?php echo esc_url( $library_url ); ?>" class="hero-cta__btn hero-cta__btn--secondary"><?php esc_html_e( 'Free resources', 'ai-awareness-day' ); ?></a>
                </div>
                <?php if ( $countdown ) : ?>
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
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php if ( $nc_dates ) : ?>
<?php /* The Conversation's own lane, straight after the hero: a full-width dark band, so it reads as a programme of
         its own rather than part of the Day's hero. */ ?>
<section class="flagship-lane" id="national-conversation" aria-labelledby="hero-flagship-title">
    <div class="container">
        <div class="hero-flagship">
            <?php /* When it starts, from the opening date; "Now open" once schools can register. */ ?>
            <p class="hero-flagship__tag">
                <?php
                if ( $portal_live ) {
                    esc_html_e( 'Now open', 'ai-awareness-day' );
                } else {
                    /* translators: %s: month and year the conversation opens, e.g. January 2027 */
                    printf( esc_html__( 'Starting %s', 'ai-awareness-day' ), esc_html( wp_date( 'F Y', $nc_dates['opens']->getTimestamp() ) ) );
                }
                ?>
            </p>
            <div class="hero-flagship__main">
                <h2 class="hero-flagship__title" id="hero-flagship-title"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_panel_title' ) ); ?></h2>
                <?php /* The hero's intro (Edit Homepage: Intro, bold opening line and the rest of the paragraph) is about the
                         Conversation, so it is set here rather than in the Day's hero. */ ?>
                <p class="hero-flagship__lead"><strong><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_lead' ) ); ?></strong> <?php echo esc_html( aiad_hero27_text( 'aiad_hero27_body' ) ); ?></p>
                <p class="hero-flagship__actions">
                    <a href="<?php echo esc_url( $join_url ); ?>" class="hero-flagship__btn"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_join_label' ) ); ?></a>
                    <?php if ( $portal_live ) : ?>
                    <a href="<?php echo esc_url( $nc_page_url ); ?>" class="hero-flagship__btn hero-flagship__btn--outline"><?php esc_html_e( 'How it works', 'ai-awareness-day' ); ?></a>
                    <?php endif; ?>
                </p>
                <?php if ( $nominate_url || $sign_in_url ) : ?>
                <p class="hero-cta-more">
                    <?php if ( $nominate_url ) : ?><a href="<?php echo esc_url( $nominate_url ); ?>"><?php esc_html_e( 'Nominate a school you work with', 'ai-awareness-day' ); ?></a><?php endif; ?>
                    <?php if ( $sign_in_url ) : ?><a href="<?php echo esc_url( $sign_in_url ); ?>"><?php esc_html_e( 'Already registered? Sign in', 'ai-awareness-day' ); ?></a><?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            <?php /* Speech bubbles, as on the oracy announcement's cover: the conversation itself. Decorative. */ ?>
            <div class="hero-flagship__talk" aria-hidden="true">
                <svg viewBox="0 14 420 246" focusable="false">
                    <g class="hero-flagship__bubble hero-flagship__bubble--1"><path d="M28 70h150a22 22 0 0 1 22 22v62a22 22 0 0 1-22 22H78l-22 22v-22H28a22 22 0 0 1-22-22V92a22 22 0 0 1 22-22z"/><circle cx="68" cy="123" r="8"/><circle cx="103" cy="123" r="8"/><circle cx="138" cy="123" r="8"/></g>
                    <g class="hero-flagship__bubble hero-flagship__bubble--2"><path d="M168 14h110a20 20 0 0 1 20 20v46a20 20 0 0 1-20 20h-74l-20 18v-18h-16a20 20 0 0 1-20-20V34a20 20 0 0 1 20-20z"/><circle cx="196" cy="57" r="7"/><circle cx="223" cy="57" r="7"/><circle cx="250" cy="57" r="7"/></g>
                    <g class="hero-flagship__bubble hero-flagship__bubble--3"><path d="M236 118h158a22 22 0 0 1 22 22v62a22 22 0 0 1-22 22h-14v22l-22-22H236a22 22 0 0 1-22-22v-62a22 22 0 0 1 22-22z"/><circle cx="276" cy="171" r="8"/><circle cx="315" cy="171" r="8"/><circle cx="354" cy="171" r="8"/></g>
                    <g class="hero-flagship__bubble hero-flagship__bubble--4"><path d="M330 20h62a18 18 0 0 1 18 18v32a18 18 0 0 1-18 18h-6v16l-16-16h-40a18 18 0 0 1-18-18V38a18 18 0 0 1 18-18z"/><circle cx="345" cy="54" r="5.5"/><circle cx="361" cy="54" r="5.5"/><circle cx="377" cy="54" r="5.5"/></g>
                </svg>
            </div>
            <div class="hero-flagship__side">
                <ol class="hero-flagship__steps">
                    <li><b><?php echo esc_html( wp_date( 'j F', $nc_dates['opens']->getTimestamp() ) ); ?></b> <span><?php esc_html_e( 'Schools register and classroom conversations start', 'ai-awareness-day' ); ?></span></li>
                    <?php /* translators: 1: month the conversation opens, 2: month of AI Awareness Day, e.g. January to April */ ?>
                    <li><b><?php echo esc_html( sprintf( __( '%1$s to %2$s', 'ai-awareness-day' ), wp_date( 'F', $nc_dates['opens']->getTimestamp() ), wp_date( 'F', $nc_dates['event']->getTimestamp() ) ) ); ?></b> <span><?php esc_html_e( 'Debates within and between schools', 'ai-awareness-day' ); ?></span></li>
                    <li><b><?php echo esc_html( wp_date( 'j F', $nc_dates['event']->getTimestamp() ) ); ?></b> <span><?php esc_html_e( 'AI Awareness Day: schools across the UK take part', 'ai-awareness-day' ); ?></span></li>
                </ol>
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
            <?php /* The five themes the Conversation debates. Each opens its theme's pop-up (aiad_theme_resources_dialog(),
                     drawn at the foot of the homepage); the link to the library filtered to the theme is the way in without it. */ ?>
            <div class="hero-flagship__themes">
                <p class="hero-flagship__themes-label" id="hero-flagship-themes"><?php esc_html_e( 'Five themes to debate', 'ai-awareness-day' ); ?></p>
                <ul aria-labelledby="hero-flagship-themes">
                    <?php foreach ( aiad_hero27_strand_questions() as $theme_slug => $theme ) : ?>
                    <?php /* Each theme's question to debate (Edit Homepage: Question: Safe, and so on). */ ?>
                    <li><a class="hero-flagship__theme hero-flagship__theme--<?php echo esc_attr( $theme_slug ); ?>" href="<?php echo esc_url( add_query_arg( 'principle', $theme_slug, $library_url ) ); ?>" data-ncp-dialog="ncp-theme-resources-<?php echo esc_attr( $theme_slug ); ?>"><img src="<?php echo esc_url( aiad_strand_icon_uri( $theme_slug ) ); ?>" alt="" width="40" height="40" /><span class="hero-flagship__theme-name"><?php echo esc_html( $theme['name'] ); ?></span><span class="hero-flagship__theme-q"><?php echo esc_html( aiad_hero27_text( 'aiad_hero27_q_' . $theme_slug ) ); ?></span></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>
