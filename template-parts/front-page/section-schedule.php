<?php
/**
 * Front page section: AI Awareness Day live schedule spotlight.
 *
 * Shows three randomly chosen sessions (new mix on each page load). Full list,
 * audience filters, and join links live on the /schedule/ archive.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

if ( ! function_exists( 'aiad_get_live_sessions' ) ) {
    return;
}

$sessions = aiad_get_live_sessions( -1 );
if ( empty( $sessions ) ) {
    return;
}

$archive_url = get_post_type_archive_link( 'live_session' );
if ( ! $archive_url ) {
    $archive_url = home_url( '/schedule/' );
}

// Pick a single event date — use the earliest session start date.
$event_date_label = '';
$first_start      = (string) get_post_meta( $sessions[0]->ID, '_session_start_time', true );
if ( $first_start !== '' ) {
    try {
        $dt = new DateTime( $first_start, new DateTimeZone( 'Europe/London' ) );
        $event_date_label = $dt->format( 'l j F Y' );
    } catch ( \Exception $e ) {
        $event_date_label = '';
    }
}

$audience_data         = function_exists( 'aiad_get_schedule_audience_filter_data' )
    ? aiad_get_schedule_audience_filter_data( $sessions )
    : array(
        'session_audience_map' => array(),
        'audience_labels'      => array(),
    );
$session_audience_map = $audience_data['session_audience_map'];
$audience_labels      = $audience_data['audience_labels'];

$spotlight_pool = $sessions;
shuffle( $spotlight_pool );
$spotlight_sessions = array_slice( $spotlight_pool, 0, min( 3, count( $spotlight_pool ) ) );
?>
<section class="section section--alt aiad-schedule-row aiad-schedule-home" id="schedule">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'AI Awareness Day — 4th June', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Live Streams', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc">
                <?php
                esc_html_e(
                    'There are many ways to engage with AI Awareness Day. We have planned live streams across different age groups, themes, and topics.',
                    'ai-awareness-day'
                );
                ?>
            </p>
            <?php if ( $event_date_label ) : ?>
                <p class="section-meta">
                    <?php
                    echo esc_html(
                        sprintf(
                            /* translators: 1: formatted event date, 2: total number of published live sessions. */
                            __( '%1$s · %2$d sessions', 'ai-awareness-day' ),
                            $event_date_label,
                            count( $sessions )
                        )
                    );
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <ul class="aiad-schedule-cards fade-up" role="list">
            <?php
            foreach ( $spotlight_sessions as $s ) :
                $start        = (string) get_post_meta( $s->ID, '_session_start_time', true );
                $end          = (string) get_post_meta( $s->ID, '_session_end_time', true );
                $time_range   = aiad_format_session_time_range( $start, $end );
                $reg_url      = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                $partner_id   = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                $partner      = $partner_id ? get_post( $partner_id ) : null;
                $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';
                $slugs        = $session_audience_map[ $s->ID ] ?? array();
                $aud_bits     = array();
                foreach ( $slugs as $sl ) {
                    if ( preg_match( '/^ks[1-5]$/', $sl ) ) {
                        $aud_bits[] = strtoupper( $sl );
                    } elseif ( isset( $audience_labels[ $sl ] ) ) {
                        $aud_bits[] = $audience_labels[ $sl ];
                    }
                }
                $aud_line = $aud_bits ? implode( ' · ', $aud_bits ) : '';
                $ics_start = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
                $ics_end   = $end ? str_replace( array( '-', ':' ), '', $end ) . '00' : $ics_start;
                $permalink = get_permalink( $s );
                $title     = get_the_title( $s );
                $ics_aria  = sprintf(
                    /* translators: %s: session title */
                    __( 'Add “%s” to calendar', 'ai-awareness-day' ),
                    $title
                );
                $share_aria = sprintf(
                    /* translators: %s: session title */
                    __( 'Share “%s”', 'ai-awareness-day' ),
                    $title
                );
                ?>
            <li class="aiad-schedule-card"
                data-ics-title="<?php echo esc_attr( $title ); ?>"
                data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $s->post_content ?: '' ) ); ?>"
                data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                data-ics-url="<?php echo esc_attr( $reg_url ); ?>">
                <?php if ( $partner_logo ) : ?>
                    <div class="aiad-schedule-card__logo-wrap">
                        <img class="aiad-schedule-card__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" loading="lazy" decoding="async" />
                    </div>
                <?php endif; ?>
                <p class="aiad-schedule-card__time"><?php echo esc_html( $time_range ); ?></p>
                <h3 class="aiad-schedule-card__heading">
                    <a class="aiad-schedule-card__title" href="<?php echo esc_url( $permalink ); ?>">
                        <?php echo esc_html( $title ); ?>
                    </a>
                </h3>
                <?php if ( $aud_line !== '' ) : ?>
                    <p class="aiad-schedule-card__ks"><?php echo esc_html( $aud_line ); ?></p>
                <?php endif; ?>
                <?php if ( $partner ) : ?>
                    <p class="aiad-schedule-card__org-name"><?php echo esc_html( $partner->post_title ); ?></p>
                <?php else : ?>
                    <p class="aiad-schedule-card__org-name aiad-schedule-card__org-name--tbc"><?php esc_html_e( 'Organisation TBC', 'ai-awareness-day' ); ?></p>
                <?php endif; ?>
                <div class="aiad-schedule-card__actions">
                    <?php if ( $ics_start !== '' ) : ?>
                        <button type="button" class="aiad-schedule-card__ics" aria-label="<?php echo esc_attr( $ics_aria ); ?>">
                            <span aria-hidden="true">📅</span>
                            <span class="aiad-schedule-card__ics-label"><?php esc_html_e( 'Add to calendar', 'ai-awareness-day' ); ?></span>
                        </button>
                    <?php endif; ?>
                    <button type="button" class="aiad-schedule-card__share"
                        data-share-url="<?php echo esc_attr( $permalink ); ?>"
                        data-share-title="<?php echo esc_attr( $title ); ?>"
                        aria-label="<?php echo esc_attr( $share_aria ); ?>">
                        <span aria-hidden="true">↗</span>
                        <span class="aiad-schedule-card__share-label"><?php esc_html_e( 'Share', 'ai-awareness-day' ); ?></span>
                    </button>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="aiad-schedule-row__actions fade-up">
            <a class="aiad-schedule-row__cta" href="<?php echo esc_url( $archive_url ); ?>">
                <?php esc_html_e( 'View full schedule →', 'ai-awareness-day' ); ?>
            </a>
        </div>
    </div>
</section>
<?php
if ( function_exists( 'aiad_print_schedule_audience_filter_script' ) ) {
    aiad_print_schedule_audience_filter_script();
}
?>
