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
            <h1 class="section-title schedule-archive__title"><?php esc_html_e( 'AI Awareness Day — full live schedule', 'ai-awareness-day' ); ?></h1>
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
                            $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'thumbnail' ) : '';
                            $aud_terms    = get_the_terms( $s->ID, 'session_audience' );
                            $aud_names    = ( $aud_terms && ! is_wp_error( $aud_terms ) )
                                ? implode( ', ', wp_list_pluck( $aud_terms, 'name' ) )
                                : '';
                            $aud_slugs    = $session_audience_map[ $s->ID ] ?? array();
                            $aud_data     = implode( ' ', $aud_slugs );
                        ?>
                            <tr class="aiad-schedule-filter-item" data-audience="<?php echo esc_attr( $aud_data ); ?>">
                                <td class="aiad-schedule-cell-time"><?php echo esc_html( $time_range ); ?></td>
                                <td>
                                    <a href="<?php echo esc_url( get_permalink( $s ) ); ?>">
                                        <?php echo esc_html( get_the_title( $s ) ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( $aud_names ); ?></td>
                                <td class="aiad-schedule-cell-provider">
                                    <?php if ( $partner_logo ) : ?>
                                        <img src="<?php echo esc_url( $partner_logo ); ?>" alt="" aria-hidden="true" />
                                    <?php endif; ?>
                                    <?php if ( $partner ) : ?>
                                        <span><?php echo esc_html( $partner->post_title ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html( $format ); ?></td>
                                <td>
                                    <?php if ( $reg_url ) : ?>
                                        <a class="aiad-schedule-table__cta" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener">
                                            <?php esc_html_e( 'Join', 'ai-awareness-day' ); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
