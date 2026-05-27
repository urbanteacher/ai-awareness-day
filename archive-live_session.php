<?php
/**
 * Archive template: AI Awareness Day full live-session schedule.
 *
 * @package AI_Awareness_Day
 */
get_header();

$sessions = function_exists( 'aiad_get_live_sessions' ) ? aiad_get_live_sessions( -1 ) : array();

$event_date_label = '';
if ( ! empty( $sessions ) ) {
    $first_start = (string) get_post_meta( $sessions[0]->ID, '_session_start_time', true );
    if ( $first_start !== '' ) {
        try {
            $dt = new DateTime( $first_start, new DateTimeZone( 'Europe/London' ) );
            $event_date_label = $dt->format( 'l j F Y' );
        } catch ( \Exception $e ) {
            $event_date_label = '';
        }
    }
}

$session_audience_map = array();
$audience_counts      = array();
$audience_labels      = array();
if ( ! empty( $sessions ) && function_exists( 'aiad_get_schedule_audience_filter_data' ) ) {
    $audience_data        = aiad_get_schedule_audience_filter_data( $sessions );
    $session_audience_map = $audience_data['session_audience_map'];
    $audience_counts      = $audience_data['audience_counts'];
    $audience_labels      = $audience_data['audience_labels'];
}
?>
<main id="main" role="main" class="container-width-standard schedule-archive">
    <section class="section aiad-schedule-filter-root">
        <div class="container">
            <span class="section-label"><?php esc_html_e( 'Live Sessions', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title schedule-archive__title">
                <?php esc_html_e( 'AI Awareness Day — Full', 'ai-awareness-day' ); ?>
                <span class="schedule-archive__title-flash"><?php esc_html_e( 'Live', 'ai-awareness-day' ); ?></span>
                <?php esc_html_e( 'Schedule', 'ai-awareness-day' ); ?>
            </h1>
            <?php if ( $event_date_label ) : ?>
                <p class="section-desc"><?php echo esc_html( $event_date_label ); ?></p>
            <?php endif; ?>

            <?php if ( empty( $sessions ) ) : ?>
                <p><?php esc_html_e( 'Sessions will be announced shortly.', 'ai-awareness-day' ); ?></p>
            <?php else : ?>
                <?php
                if ( function_exists( 'aiad_render_schedule_audience_tabs' ) ) {
                    aiad_render_schedule_audience_tabs( $audience_labels, $audience_counts, count( $sessions ) );
                }
                ?>
                <table class="aiad-schedule-table" aria-label="<?php esc_attr_e( 'Schedule of live sessions', 'ai-awareness-day' ); ?>">
                    <colgroup>
                        <col class="aiad-schedule-col aiad-schedule-col--time">
                        <col class="aiad-schedule-col aiad-schedule-col--session">
                        <col class="aiad-schedule-col aiad-schedule-col--audience">
                        <col class="aiad-schedule-col aiad-schedule-col--provider">
                        <col class="aiad-schedule-col aiad-schedule-col--format">
                        <col class="aiad-schedule-col aiad-schedule-col--actions">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col"><?php esc_html_e( 'Time', 'ai-awareness-day' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Session', 'ai-awareness-day' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Audience', 'ai-awareness-day' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Provider', 'ai-awareness-day' ); ?></th>
                            <th scope="col"><?php esc_html_e( 'Format', 'ai-awareness-day' ); ?></th>
                            <th scope="col"><span class="screen-reader-text"><?php esc_html_e( 'Join', 'ai-awareness-day' ); ?></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $sessions as $s ) :
                            $start        = (string) get_post_meta( $s->ID, '_session_start_time', true );
                            $end          = (string) get_post_meta( $s->ID, '_session_end_time', true );
                            $time_range   = aiad_format_session_time_range( $start, $end );
                            $format       = (string) get_post_meta( $s->ID, '_session_format', true );
                            $reg_url      = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                            $partner_id   = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                            $partner      = $partner_id ? get_post( $partner_id ) : null;
                            $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';
                            $aud_terms    = get_the_terms( $s->ID, 'session_audience' );
                            $aud_names    = ( $aud_terms && ! is_wp_error( $aud_terms ) )
                                ? implode( ', ', wp_list_pluck( $aud_terms, 'name' ) )
                                : '';
                            $aud_slugs    = $session_audience_map[ $s->ID ] ?? array();
                            $aud_data     = implode( ' ', $aud_slugs );
                            $title        = get_the_title( $s );
                            $permalink    = get_permalink( $s );
                            $ics_start    = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
                            $ics_end      = $end   ? str_replace( array( '-', ':' ), '', $end )   . '00' : $ics_start;
                            $ics_url      = home_url( '/session-ics/' . $s->ID . '/' );
                        ?>
                            <tr class="aiad-schedule-filter-item"
                                data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>"
                                data-audience="<?php echo esc_attr( $aud_data ); ?>"
                                data-ics-title="<?php echo esc_attr( $title ); ?>"
                                data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $s->post_content ?: '' ) ); ?>"
                                data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                                data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                                data-ics-url="<?php echo esc_attr( $reg_url ); ?>">
                                <td class="aiad-schedule-cell-time"><?php echo esc_html( $time_range ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( $permalink ); ?>">
                                        <?php echo esc_html( $title ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $aud_names ); ?></td>
                                <td class="aiad-schedule-cell-provider">
                                    <div class="aiad-schedule-cell-provider__inner">
                                        <?php if ( $partner_logo ) : ?>
                                            <span class="aiad-schedule-cell-provider__logo">
                                                <img src="<?php echo esc_url( $partner_logo ); ?>" alt="" aria-hidden="true" />
                                            </span>
                                        <?php endif; ?>
                                        <?php if ( $partner ) : ?>
                                            <span class="aiad-schedule-cell-provider__name"><?php echo esc_html( $partner->post_title ); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="aiad-schedule-cell-format"><?php echo esc_html( $format ); ?></td>
                                <td class="aiad-schedule-cell-actions">
                                    <div class="aiad-schedule-cell-actions__inner">
                                        <?php if ( $reg_url ) : ?>
                                            <a class="aiad-schedule-table__cta" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener" data-session-id="<?php echo esc_attr( (string) $s->ID ); ?>">
                                                <?php esc_html_e( 'Join', 'ai-awareness-day' ); ?>
                                            </a>
                                        <?php else : ?>
                                            <span class="aiad-schedule-table__cta aiad-schedule-table__cta--soon"><?php esc_html_e( 'Soon', 'ai-awareness-day' ); ?></span>
                                        <?php endif; ?>
                                        <?php if ( $ics_start ) : ?>
                                            <a class="aiad-schedule-table__ics"
                                               href="<?php echo esc_url( $ics_url ); ?>"
                                               aria-label="<?php echo esc_attr( sprintf( __( 'Add "%s" to calendar', 'ai-awareness-day' ), $title ) ); ?>">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="aiad-schedule-table__share"
                                            data-share-url="<?php echo esc_attr( $permalink ); ?>"
                                            data-share-title="<?php echo esc_attr( $title ); ?>"
                                            aria-label="<?php echo esc_attr( sprintf( __( 'Share "%s"', 'ai-awareness-day' ), $title ) ); ?>">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="aiad-schedule-row__empty" hidden><?php esc_html_e( 'No sessions for this audience.', 'ai-awareness-day' ); ?></p>

                <div class="aiad-schedule-footer">
                    <p class="aiad-schedule-footer__label"><?php esc_html_e( 'Share this schedule', 'ai-awareness-day' ); ?></p>
                    <div class="aiad-schedule-share-bar" role="region" aria-label="<?php esc_attr_e( 'Share this schedule', 'ai-awareness-day' ); ?>">
                        <button type="button" class="aiad-schedule-share-bar__btn aiad-schedule-share-bar__btn--native" hidden>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                            <?php esc_html_e( 'Share', 'ai-awareness-day' ); ?>
                        </button>
                        <button type="button" class="aiad-schedule-share-bar__btn aiad-schedule-share-bar__btn--copy">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                            <?php esc_html_e( 'Copy link', 'ai-awareness-day' ); ?>
                        </button>
                        <a class="aiad-schedule-share-bar__btn aiad-schedule-share-bar__btn--x"
                           href="https://x.com/intent/tweet?url=<?php echo rawurlencode( get_post_type_archive_link( 'live_session' ) ?: home_url( '/schedule/' ) ); ?>&text=<?php echo rawurlencode( 'AI Awareness Day — full live schedule' ); ?>"
                           target="_blank" rel="noopener">X</a>
                        <a class="aiad-schedule-share-bar__btn aiad-schedule-share-bar__btn--linkedin"
                           href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo rawurlencode( get_post_type_archive_link( 'live_session' ) ?: home_url( '/schedule/' ) ); ?>"
                           target="_blank" rel="noopener">LinkedIn</a>
                        <a class="aiad-schedule-share-bar__btn aiad-schedule-share-bar__btn--facebook"
                           href="https://www.facebook.com/sharer/sharer.php?u=<?php echo rawurlencode( get_post_type_archive_link( 'live_session' ) ?: home_url( '/schedule/' ) ); ?>"
                           target="_blank" rel="noopener">Facebook</a>
                        <span class="aiad-schedule-share-bar__status" aria-live="polite"></span>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </section>
</main>
<?php
if ( ! empty( $sessions ) && function_exists( 'aiad_print_schedule_audience_filter_script' ) ) {
    aiad_print_schedule_audience_filter_script();
}
get_footer();
