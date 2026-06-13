<?php
/**
 * Front page section: AI Awareness Day "What's On" spotlight.
 *
 * Upcoming sessions only on the homepage (three random spotlight cards).
 * Past sessions are not listed here or on /schedule/.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

if ( ! function_exists( 'aiad_get_live_sessions' ) ) {
    return;
}

$all_sessions = aiad_get_live_sessions( -1 );
$sessions     = function_exists( 'aiad_filter_sessions_upcoming' )
    ? aiad_filter_sessions_upcoming( $all_sessions )
    : $all_sessions;
if ( empty( $sessions ) ) {
    return;
}

$archive_url = get_post_type_archive_link( 'live_session' );
if ( ! $archive_url ) {
    $archive_url = home_url( '/schedule/' );
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
            <span class="section-label"><?php esc_html_e( 'AI Awareness Day', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'What’s On?', 'ai-awareness-day' ); ?></h2>
            <p class="section-desc">
                <?php
                esc_html_e(
                    'Upcoming live sessions, CPD activities, events, and conferences across different age groups, themes, and topics.',
                    'ai-awareness-day'
                );
                ?>
            </p>
        </div>

        <ul class="aiad-schedule-cards fade-up" role="list">
            <?php
            foreach ( $spotlight_sessions as $s ) :
                $start        = (string) get_post_meta( $s->ID, '_session_start_time', true );
                $end          = (string) get_post_meta( $s->ID, '_session_end_time', true );
                $date_label   = function_exists( 'aiad_format_session_date' ) ? aiad_format_session_date( $start ) : '';
                $time_range   = aiad_format_session_time_range( $start, $end );
                $is_past      = function_exists( 'aiad_session_is_past' ) && aiad_session_is_past( $s->ID );
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
                data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>"
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
                <?php if ( $date_label !== '' ) : ?>
                    <p class="aiad-schedule-card__date"><?php echo esc_html( $date_label ); ?></p>
                <?php endif; ?>
                <?php if ( $time_range !== '' ) : ?>
                    <p class="aiad-schedule-card__time"><?php echo esc_html( $time_range ); ?></p>
                <?php endif; ?>
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
                    <?php if ( function_exists( 'aiad_session_show_join_link' ) && aiad_session_show_join_link( $s->ID ) ) : ?>
                        <a class="aiad-schedule-card__join aiad-schedule-card__link--action"
                           href="<?php echo esc_url( $reg_url ); ?>"
                           data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>"
                           target="_blank" rel="noopener">
                            <?php echo esc_html( aiad_session_cta_label( $s->ID ) ); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ( $ics_start !== '' && ! $is_past ) : ?>
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
                <?php esc_html_e( 'Latest events, conferences, CPD &amp; courses — get ready for AI Awareness Day 2027 →', 'ai-awareness-day' ); ?>
            </a>
        </div>
    </div>
</section>
<?php
if ( function_exists( 'aiad_print_schedule_audience_filter_script' ) ) {
    aiad_print_schedule_audience_filter_script();
}
?>
