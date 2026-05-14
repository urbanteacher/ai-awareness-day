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
            $dt               = new DateTime( $first_start, new DateTimeZone( 'Europe/London' ) );
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
            <span class="section-label"><?php esc_html_e( 'AI Awareness Day — 4th June', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title schedule-archive__title"><?php esc_html_e( 'Full live schedule', 'ai-awareness-day' ); ?></h1>
            <?php if ( $event_date_label ) : ?>
                <p class="section-desc"><?php echo esc_html( $event_date_label . ' · ' . count( $sessions ) . ' sessions' ); ?></p>
            <?php endif; ?>

            <?php if ( empty( $sessions ) ) : ?>
                <p><?php esc_html_e( 'Sessions will be announced shortly.', 'ai-awareness-day' ); ?></p>
            <?php else : ?>

                <?php
                if ( function_exists( 'aiad_render_schedule_audience_tabs' ) ) {
                    aiad_render_schedule_audience_tabs( $audience_labels, $audience_counts, count( $sessions ) );
                }
                ?>

                <ul class="aiad-session-list" role="list" aria-label="<?php esc_attr_e( 'Schedule of live sessions', 'ai-awareness-day' ); ?>">
                    <?php foreach ( $sessions as $s ) :
                        $start      = (string) get_post_meta( $s->ID, '_session_start_time', true );
                        $end        = (string) get_post_meta( $s->ID, '_session_end_time', true );
                        $time_range = aiad_format_session_time_range( $start, $end );
                        $format     = (string) get_post_meta( $s->ID, '_session_format', true );
                        $reg_url    = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                        $partner_id = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                        $partner    = $partner_id ? get_post( $partner_id ) : null;
                        $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';
                        $aud_terms  = get_the_terms( $s->ID, 'session_audience' );
                        $aud_names  = ( $aud_terms && ! is_wp_error( $aud_terms ) )
                            ? wp_list_pluck( $aud_terms, 'name' )
                            : array();
                        $aud_slugs  = $session_audience_map[ $s->ID ] ?? array();
                        $aud_data   = implode( ' ', $aud_slugs );

                        $ics_start  = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
                        $ics_end    = $end   ? str_replace( array( '-', ':' ), '', $end )   . '00' : $ics_start;
                        $title      = get_the_title( $s );
                        $content    = $s->post_content ?? '';
                    ?>
                    <li class="aiad-session-row aiad-schedule-filter-item"
                        data-audience="<?php echo esc_attr( $aud_data ); ?>"
                        data-ics-title="<?php echo esc_attr( $title ); ?>"
                        data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $content ) ); ?>"
                        data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                        data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                        data-ics-url="<?php echo esc_attr( $reg_url ); ?>">

                        <div class="aiad-session-row__time">
                            <?php echo esc_html( $time_range ); ?>
                        </div>

                        <div class="aiad-session-row__main">
                            <a class="aiad-session-row__title" href="<?php echo esc_url( get_permalink( $s ) ); ?>">
                                <?php echo esc_html( $title ); ?>
                            </a>
                            <div class="aiad-session-row__tags">
                                <?php foreach ( $aud_names as $name ) : ?>
                                    <span class="session-tag"><?php echo esc_html( $name ); ?></span>
                                <?php endforeach; ?>
                                <?php if ( $format !== '' ) : ?>
                                    <span class="session-tag session-tag--format"><?php echo esc_html( $format ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ( $partner || $partner_logo ) : ?>
                        <div class="aiad-session-row__provider">
                            <?php if ( $partner_logo ) : ?>
                                <span class="aiad-session-row__logo-wrap">
                                    <img class="aiad-session-row__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" loading="lazy" decoding="async" />
                                </span>
                            <?php endif; ?>
                            <?php if ( $partner ) : ?>
                                <span class="aiad-session-row__org"><?php echo esc_html( $partner->post_title ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <div class="aiad-session-row__actions">
                            <?php if ( $reg_url ) : ?>
                                <a class="aiad-session-row__join" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Join', 'ai-awareness-day' ); ?>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                </a>
                            <?php else : ?>
                                <span class="aiad-session-row__join aiad-session-row__join--soon"><?php esc_html_e( 'Soon', 'ai-awareness-day' ); ?></span>
                            <?php endif; ?>
                            <?php if ( $ics_start !== '' ) : ?>
                                <button type="button" class="aiad-session-row__ics" aria-label="<?php echo esc_attr( sprintf( __( 'Add "%s" to calendar', 'ai-awareness-day' ), $title ) ); ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </button>
                            <?php endif; ?>
                        </div>

                    </li>
                    <?php endforeach; ?>
                </ul>

                <p class="aiad-schedule-row__empty" hidden><?php esc_html_e( 'No sessions for this audience.', 'ai-awareness-day' ); ?></p>

            <?php endif; ?>
        </div>
    </section>
</main>
<?php
if ( ! empty( $sessions ) && function_exists( 'aiad_print_schedule_audience_filter_script' ) ) {
    aiad_print_schedule_audience_filter_script();
}
get_footer();
