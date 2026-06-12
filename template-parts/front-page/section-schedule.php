<?php
/**
 * Front page section: Upcoming Events.
 *
 * Shows the next three upcoming events (ordered by start date ascending).
 * Falls back to the three most recent events if none are in the future.
 * Full list lives on the /events/ archive.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

if ( ! function_exists( 'aiad_get_live_sessions' ) ) {
    return;
}

$all_events = aiad_get_live_sessions( -1 );
if ( empty( $all_events ) ) {
    return;
}

// Sort all events by start time ascending.
usort( $all_events, function ( WP_Post $a, WP_Post $b ): int {
    $ta = (string) get_post_meta( $a->ID, '_session_start_time', true );
    $tb = (string) get_post_meta( $b->ID, '_session_start_time', true );
    return strcmp( $ta, $tb );
} );

$now = current_time( 'mysql' );

// Prefer upcoming events; fall back to the last three if all are past.
$upcoming = array_values( array_filter( $all_events, function ( WP_Post $e ) use ( $now ): bool {
    $start = (string) get_post_meta( $e->ID, '_session_start_time', true );
    return $start === '' || $start >= $now;
} ) );

$display_events = ! empty( $upcoming )
    ? array_slice( $upcoming, 0, 3 )
    : array_slice( array_reverse( $all_events ), 0, 3 );

$archive_url = get_post_type_archive_link( 'live_session' ) ?: home_url( '/events/' );

$audience_data        = function_exists( 'aiad_get_schedule_audience_filter_data' )
    ? aiad_get_schedule_audience_filter_data( $all_events )
    : array( 'session_audience_map' => array(), 'audience_labels' => array() );
$session_audience_map = $audience_data['session_audience_map'];
$audience_labels      = $audience_data['audience_labels'];

$event_type_labels = array(
    'online'    => __( 'Online', 'ai-awareness-day' ),
    'in_person' => __( 'In person', 'ai-awareness-day' ),
    'hybrid'    => __( 'Hybrid', 'ai-awareness-day' ),
);

$has_upcoming = ! empty( $upcoming );
?>
<section class="section section--alt aiad-schedule-row aiad-schedule-home" id="events">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'What\'s On', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Upcoming Events', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc">
                <?php
                esc_html_e(
                    'From live webinars and in-school workshops to conferences and CPD sessions — find events designed to bring AI education to life for students, teachers, and parents.',
                    'ai-awareness-day'
                );
                ?>
            </p>
            <?php if ( ! $has_upcoming ) : ?>
                <p class="section-meta"><?php esc_html_e( 'Showing most recent events — new dates coming soon.', 'ai-awareness-day' ); ?></p>
            <?php elseif ( count( $upcoming ) > 3 ) : ?>
                <p class="section-meta">
                    <?php
                    echo esc_html( sprintf(
                        /* translators: %d: number of upcoming events */
                        _n( '%d upcoming event', '%d upcoming events', count( $upcoming ), 'ai-awareness-day' ),
                        count( $upcoming )
                    ) );
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <ul class="aiad-schedule-cards fade-up" role="list">
            <?php
            foreach ( $display_events as $s ) :
                $start        = (string) get_post_meta( $s->ID, '_session_start_time', true );
                $end          = (string) get_post_meta( $s->ID, '_session_end_time', true );
                $event_type   = (string) get_post_meta( $s->ID, '_session_event_type', true );
                $location     = (string) get_post_meta( $s->ID, '_session_location', true );
                $format_label = (string) get_post_meta( $s->ID, '_session_format', true );
                $reg_url      = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                $partner_id   = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                $partner      = $partner_id ? get_post( $partner_id ) : null;
                $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';
                $permalink    = get_permalink( $s );
                $title        = get_the_title( $s );

                // Date / time display
                $date_line = '';
                if ( $start !== '' ) {
                    try {
                        $dt_start  = new DateTime( $start, new DateTimeZone( 'Europe/London' ) );
                        $date_line = $dt_start->format( 'l j F Y' );
                        $time_range = aiad_format_session_time_range( $start, $end );
                        if ( $time_range ) {
                            $date_line .= ' · ' . $time_range;
                        }
                    } catch ( \Exception $e ) {
                        $date_line = '';
                    }
                }

                // Audience
                $slugs    = $session_audience_map[ $s->ID ] ?? array();
                $aud_bits = array();
                foreach ( $slugs as $sl ) {
                    if ( preg_match( '/^ks[1-5]$/', $sl ) ) {
                        $aud_bits[] = strtoupper( $sl );
                    } elseif ( isset( $audience_labels[ $sl ] ) ) {
                        $aud_bits[] = $audience_labels[ $sl ];
                    }
                }
                $aud_line = $aud_bits ? implode( ' · ', $aud_bits ) : '';

                // ICS data
                $ics_start = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
                $ics_end   = $end   ? str_replace( array( '-', ':' ), '', $end )   . '00' : $ics_start;

                // Event type badge
                $type_label = $event_type_labels[ $event_type ] ?? '';

                $ics_aria = sprintf(
                    /* translators: %s: event title */
                    __( 'Add "%s" to calendar', 'ai-awareness-day' ),
                    $title
                );
                $share_aria = sprintf(
                    /* translators: %s: event title */
                    __( 'Share "%s"', 'ai-awareness-day' ),
                    $title
                );
                ?>
            <li class="aiad-schedule-card"
                data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>"
                data-ics-title="<?php echo esc_attr( $title ); ?>"
                data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $s->post_content ?: '' ) ); ?>"
                data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                data-ics-url="<?php echo esc_attr( $reg_url ?: $permalink ); ?>">

                <?php if ( $partner_logo ) : ?>
                    <div class="aiad-schedule-card__logo-wrap">
                        <img class="aiad-schedule-card__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" loading="lazy" decoding="async" />
                    </div>
                <?php endif; ?>

                <?php if ( $type_label !== '' ) : ?>
                    <span class="aiad-schedule-card__type-badge aiad-schedule-card__type-badge--<?php echo esc_attr( $event_type ); ?>">
                        <?php echo esc_html( $type_label ); ?>
                    </span>
                <?php endif; ?>

                <?php if ( $date_line !== '' ) : ?>
                    <p class="aiad-schedule-card__time"><?php echo esc_html( $date_line ); ?></p>
                <?php endif; ?>

                <h3 class="aiad-schedule-card__heading">
                    <a class="aiad-schedule-card__title" href="<?php echo esc_url( $permalink ); ?>">
                        <?php echo esc_html( $title ); ?>
                    </a>
                </h3>

                <?php if ( $aud_line !== '' ) : ?>
                    <p class="aiad-schedule-card__ks"><?php echo esc_html( $aud_line ); ?></p>
                <?php endif; ?>

                <?php if ( $location !== '' ) : ?>
                    <p class="aiad-schedule-card__location">📍 <?php echo esc_html( $location ); ?></p>
                <?php elseif ( $format_label !== '' ) : ?>
                    <p class="aiad-schedule-card__location"><?php echo esc_html( $format_label ); ?></p>
                <?php endif; ?>

                <?php if ( $partner ) : ?>
                    <p class="aiad-schedule-card__org-name"><?php echo esc_html( $partner->post_title ); ?></p>
                <?php endif; ?>

                <div class="aiad-schedule-card__actions">
                    <?php if ( $reg_url !== '' ) : ?>
                        <a class="aiad-schedule-card__join aiad-schedule-card__link--action"
                           href="<?php echo esc_url( $reg_url ); ?>"
                           data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>"
                           target="_blank" rel="noopener">
                            <?php echo $event_type === 'in_person' ? esc_html__( 'Register', 'ai-awareness-day' ) : esc_html__( 'Join', 'ai-awareness-day' ); ?>
                        </a>
                    <?php endif; ?>
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
                <?php esc_html_e( 'View all events →', 'ai-awareness-day' ); ?>
            </a>
        </div>
    </div>
</section>

<?php
// Add event type badge styles inline (avoids a new CSS file for two rules).
?>
<style>
.aiad-schedule-card__type-badge {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 4px;
    margin-bottom: 6px;
    background: var(--wp--preset--color--primary, #0070c0);
    color: #fff;
}
.aiad-schedule-card__type-badge--in_person {
    background: #059669;
}
.aiad-schedule-card__type-badge--hybrid {
    background: #7c3aed;
}
.aiad-schedule-card__location {
    font-size: 0.875rem;
    color: var(--wp--preset--color--mid-grey, #6b7280);
    margin: 4px 0 0;
}
</style>
<?php
if ( function_exists( 'aiad_print_schedule_audience_filter_script' ) ) {
    aiad_print_schedule_audience_filter_script();
}
