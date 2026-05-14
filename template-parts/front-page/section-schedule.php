<?php
/**
 * Front page section: AI Awareness Day live schedule (compact preview).
 *
 * Renders a compact row above the timeline. Clicking the toggle expands an
 * inline list of all sessions; the "View full schedule" link goes to the
 * dedicated /schedule/ archive page.
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

$audience_terms = array();
foreach ( $sessions as $s ) {
    $terms = get_the_terms( $s->ID, 'session_audience' );
    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $t ) {
            $audience_terms[ $t->slug ] = $t->name;
        }
    }
}
?>
<section class="section section--alt aiad-schedule-row" id="schedule">
    <div class="container">
        <div class="aiad-schedule-row__head fade-up">
            <div class="aiad-schedule-row__meta">
                <span class="section-label"><?php esc_html_e( 'Live Sessions', 'ai-awareness-day' ); ?></span>
                <h2 class="aiad-schedule-row__title">
                    <?php
                    /* translators: 1: session count, 2: event date label. */
                    echo esc_html( sprintf(
                        _n( '%1$d live session%2$s', '%1$d live sessions%2$s', count( $sessions ), 'ai-awareness-day' ),
                        count( $sessions ),
                        $event_date_label ? ' — ' . $event_date_label : ''
                    ) );
                    ?>
                </h2>
                <?php if ( ! empty( $audience_terms ) ) : ?>
                    <p class="aiad-schedule-row__audiences">
                        <?php esc_html_e( 'For', 'ai-awareness-day' ); ?>
                        <?php echo esc_html( implode( ' · ', $audience_terms ) ); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="aiad-schedule-row__actions">
                <button type="button" class="aiad-schedule-row__toggle" aria-expanded="false" aria-controls="aiad-schedule-row__list">
                    <span class="aiad-schedule-row__toggle-label-show"><?php esc_html_e( 'Show schedule', 'ai-awareness-day' ); ?></span>
                    <span class="aiad-schedule-row__toggle-label-hide"><?php esc_html_e( 'Hide schedule', 'ai-awareness-day' ); ?></span>
                    <span class="aiad-schedule-row__chevron" aria-hidden="true">▾</span>
                </button>
                <a class="aiad-schedule-row__cta" href="<?php echo esc_url( $archive_url ); ?>">
                    <?php esc_html_e( 'View full schedule →', 'ai-awareness-day' ); ?>
                </a>
            </div>
        </div>

        <div class="aiad-schedule-row__list" id="aiad-schedule-row__list" hidden>
            <ol class="aiad-schedule-list">
                <?php foreach ( $sessions as $s ) :
                    $start      = (string) get_post_meta( $s->ID, '_session_start_time', true );
                    $end        = (string) get_post_meta( $s->ID, '_session_end_time', true );
                    $time_range = aiad_format_session_time_range( $start, $end );
                    $format     = (string) get_post_meta( $s->ID, '_session_format', true );
                    $reg_url    = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                    $partner_id = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                    $partner    = $partner_id ? get_post( $partner_id ) : null;
                    $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'thumbnail' ) : '';
                    $aud_terms  = get_the_terms( $s->ID, 'session_audience' );
                    $aud_names  = ( $aud_terms && ! is_wp_error( $aud_terms ) )
                        ? implode( ', ', wp_list_pluck( $aud_terms, 'name' ) )
                        : '';
                ?>
                    <li class="aiad-schedule-item">
                        <span class="aiad-schedule-item__time"><?php echo esc_html( $time_range ); ?></span>
                        <div class="aiad-schedule-item__body">
                            <a class="aiad-schedule-item__title" href="<?php echo esc_url( get_permalink( $s ) ); ?>">
                                <?php echo esc_html( get_the_title( $s ) ); ?>
                            </a>
                            <span class="aiad-schedule-item__audience"><?php echo esc_html( $aud_names ); ?></span>
                        </div>
                        <div class="aiad-schedule-item__provider">
                            <?php if ( $partner ) : ?>
                                <?php if ( $partner_logo ) : ?>
                                    <img class="aiad-schedule-item__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" aria-hidden="true" />
                                <?php endif; ?>
                                <span class="aiad-schedule-item__partner-name"><?php echo esc_html( $partner->post_title ); ?></span>
                            <?php endif; ?>
                        </div>
                        <span class="aiad-schedule-item__format"><?php echo esc_html( $format ); ?></span>
                        <?php if ( $reg_url ) : ?>
                            <a class="aiad-schedule-item__cta" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener">
                                <?php esc_html_e( 'Join →', 'ai-awareness-day' ); ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</section>

<script>
(function(){
    var toggle = document.querySelector('.aiad-schedule-row__toggle');
    var list   = document.getElementById('aiad-schedule-row__list');
    if ( ! toggle || ! list ) return;
    toggle.addEventListener('click', function(){
        var open = list.hasAttribute('hidden') ? false : true;
        if ( open ) {
            list.setAttribute('hidden', '');
            toggle.setAttribute('aria-expanded', 'false');
        } else {
            list.removeAttribute('hidden');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });
})();
</script>
