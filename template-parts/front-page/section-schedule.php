<?php
/**
 * Front page section: AI Awareness Day live schedule.
 *
 * Renders a section above the timeline with audience filter tabs that
 * filter the inline session list. "View full schedule" links to the
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

// Build per-session audience map + audience tab counts.
$session_audience_map = array(); // session_id => [slugs]
$audience_counts      = array(); // slug => count
$audience_labels      = array(); // slug => name
foreach ( $sessions as $s ) {
    $terms = get_the_terms( $s->ID, 'session_audience' );
    $slugs = array();
    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $t ) {
            $slugs[]                       = $t->slug;
            $audience_labels[ $t->slug ]   = $t->name;
            $audience_counts[ $t->slug ] = ( $audience_counts[ $t->slug ] ?? 0 ) + 1;
        }
    }
    $session_audience_map[ $s->ID ] = $slugs;
}

// Preferred audience tab order.
$preferred_order = array( 'ks1', 'ks2', 'ks3', 'ks4', 'ks5', 'teachers', 'all' );
uksort( $audience_labels, function ( $a, $b ) use ( $preferred_order ) {
    $ai = array_search( $a, $preferred_order, true );
    $bi = array_search( $b, $preferred_order, true );
    $ai = $ai === false ? 99 : $ai;
    $bi = $bi === false ? 99 : $bi;
    return $ai - $bi;
} );
?>
<section class="section section--alt aiad-schedule-row" id="schedule">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'Live Sessions', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title">
                <?php
                /* translators: 1: session count, 2: event date label. */
                echo esc_html( sprintf(
                    _n( '%1$d live session%2$s', '%1$d live sessions%2$s', count( $sessions ), 'ai-awareness-day' ),
                    count( $sessions ),
                    $event_date_label ? ' — ' . $event_date_label : ''
                ) );
                ?>
            </h2>
            <p class="section-desc"><?php esc_html_e( 'Pick your audience to see only the sessions for them.', 'ai-awareness-day' ); ?></p>
        </div>

        <div class="aiad-schedule-tabs fade-up" role="tablist" aria-label="<?php esc_attr_e( 'Filter sessions by audience', 'ai-awareness-day' ); ?>">
            <button type="button" class="aiad-schedule-tab is-active" role="tab" aria-selected="true" data-audience="all">
                <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
                <span class="aiad-schedule-tab__count"><?php echo esc_html( count( $sessions ) ); ?></span>
            </button>
            <?php foreach ( $audience_labels as $slug => $name ) : ?>
                <button type="button" class="aiad-schedule-tab" role="tab" aria-selected="false" data-audience="<?php echo esc_attr( $slug ); ?>">
                    <?php echo esc_html( $name ); ?>
                    <span class="aiad-schedule-tab__count"><?php echo esc_html( $audience_counts[ $slug ] ); ?></span>
                </button>
            <?php endforeach; ?>
            <button type="button" class="aiad-schedule-tab is-disabled" disabled aria-disabled="true">
                <?php esc_html_e( 'Parents', 'ai-awareness-day' ); ?>
                <span class="aiad-schedule-tab__badge"><?php esc_html_e( 'Coming soon', 'ai-awareness-day' ); ?></span>
            </button>
        </div>

        <div class="aiad-schedule-row__list" id="aiad-schedule-row__list">
            <div class="aiad-schedule-list-head" role="row">
                <span class="aiad-schedule-list-head__cell"><?php esc_html_e( 'Time', 'ai-awareness-day' ); ?></span>
                <span class="aiad-schedule-list-head__cell"><?php esc_html_e( 'Session', 'ai-awareness-day' ); ?></span>
                <span class="aiad-schedule-list-head__cell"><?php esc_html_e( 'Organisation', 'ai-awareness-day' ); ?></span>
                <span class="aiad-schedule-list-head__cell"><?php esc_html_e( 'Links', 'ai-awareness-day' ); ?></span>
                <span class="aiad-schedule-list-head__cell"><?php esc_html_e( 'Add to calendar', 'ai-awareness-day' ); ?></span>
            </div>
            <ol class="aiad-schedule-list">
                <?php foreach ( $sessions as $s ) :
                    $start        = (string) get_post_meta( $s->ID, '_session_start_time', true );
                    $end          = (string) get_post_meta( $s->ID, '_session_end_time', true );
                    $time_range   = aiad_format_session_time_range( $start, $end );
                    $format       = (string) get_post_meta( $s->ID, '_session_format', true );
                    $reg_url      = (string) get_post_meta( $s->ID, '_session_registration_url', true );
                    $partner_id   = (int) get_post_meta( $s->ID, '_session_partner_id', true );
                    $partner      = $partner_id ? get_post( $partner_id ) : null;
                    $partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';
                    $slugs        = $session_audience_map[ $s->ID ] ?? array();
                    $aud_names    = array();
                    foreach ( $slugs as $sl ) {
                        if ( isset( $audience_labels[ $sl ] ) ) {
                            $aud_names[] = $audience_labels[ $sl ];
                        }
                    }
                    // ISO datetime strings stored as YYYY-MM-DDTHH:MM (local Europe/London).
                    $ics_start = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
                    $ics_end   = $end   ? str_replace( array( '-', ':' ), '', $end )   . '00' : $ics_start;
                ?>
                    <li class="aiad-schedule-item" data-audience="<?php echo esc_attr( implode( ' ', $slugs ) ); ?>"
                        data-ics-title="<?php echo esc_attr( get_the_title( $s ) ); ?>"
                        data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $s->post_content ?: '' ) ); ?>"
                        data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                        data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                        data-ics-url="<?php echo esc_attr( $reg_url ); ?>">
                        <span class="aiad-schedule-item__time"><?php echo esc_html( $time_range ); ?></span>
                        <div class="aiad-schedule-item__body">
                            <a class="aiad-schedule-item__title" href="<?php echo esc_url( get_permalink( $s ) ); ?>">
                                <?php echo esc_html( get_the_title( $s ) ); ?>
                            </a>
                            <span class="aiad-schedule-item__audience"><?php echo esc_html( implode( ', ', $aud_names ) ); ?></span>
                            <span class="aiad-schedule-item__format"><?php echo esc_html( $format ); ?></span>
                        </div>
                        <div class="aiad-schedule-item__provider">
                            <?php if ( $partner ) : ?>
                                <?php if ( $partner_logo ) : ?>
                                    <span class="aiad-schedule-item__logo-wrap">
                                        <img class="aiad-schedule-item__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" aria-hidden="true" />
                                    </span>
                                <?php endif; ?>
                                <span class="aiad-schedule-item__partner-name"><?php echo esc_html( $partner->post_title ); ?></span>
                            <?php else : ?>
                                <span class="aiad-schedule-item__partner-name aiad-schedule-item__partner-name--tbc"><?php esc_html_e( 'TBC', 'ai-awareness-day' ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="aiad-schedule-item__links">
                            <?php if ( $reg_url ) : ?>
                                <a class="aiad-schedule-item__cta" href="<?php echo esc_url( $reg_url ); ?>" target="_blank" rel="noopener">
                                    <?php esc_html_e( 'Join →', 'ai-awareness-day' ); ?>
                                </a>
                            <?php else : ?>
                                <span class="aiad-schedule-item__cta aiad-schedule-item__cta--disabled"><?php esc_html_e( 'Soon', 'ai-awareness-day' ); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="aiad-schedule-item__calendar">
                            <button type="button" class="aiad-schedule-item__ics" aria-label="<?php esc_attr_e( 'Add to calendar', 'ai-awareness-day' ); ?>">
                                <span aria-hidden="true">📅</span>
                                <span class="aiad-schedule-item__ics-label"><?php esc_html_e( 'Add', 'ai-awareness-day' ); ?></span>
                            </button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
            <p class="aiad-schedule-row__empty" hidden><?php esc_html_e( 'No sessions for this audience.', 'ai-awareness-day' ); ?></p>
        </div>

        <div class="aiad-schedule-row__actions fade-up">
            <a class="aiad-schedule-row__cta" href="<?php echo esc_url( $archive_url ); ?>">
                <?php esc_html_e( 'View full schedule →', 'ai-awareness-day' ); ?>
            </a>
        </div>
    </div>
</section>

<script>
(function(){
    var tabs  = document.querySelectorAll('#schedule .aiad-schedule-tab');
    var items = document.querySelectorAll('#schedule .aiad-schedule-item');
    var empty = document.querySelector('#schedule .aiad-schedule-row__empty');

    // Audience filtering
    tabs.forEach(function( tab ){
        if ( tab.disabled ) return;
        tab.addEventListener('click', function(){
            var target = tab.getAttribute('data-audience');
            tabs.forEach(function( t ){
                if ( t.disabled ) return;
                var active = t === tab;
                t.classList.toggle('is-active', active);
                t.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            var visibleCount = 0;
            items.forEach(function( li ){
                var slugs = (li.getAttribute('data-audience') || '').split(' ');
                var show  = target === 'all' || slugs.indexOf(target) !== -1;
                li.hidden = ! show;
                if ( show ) visibleCount++;
            });
            if ( empty ) empty.hidden = visibleCount > 0;
        });
    });

    // Add to calendar (.ics download)
    function pad( n ){ return String(n).padStart(2, '0'); }
    function nowStamp(){
        var d = new Date();
        return d.getUTCFullYear() + pad(d.getUTCMonth()+1) + pad(d.getUTCDate())
             + 'T' + pad(d.getUTCHours()) + pad(d.getUTCMinutes()) + pad(d.getUTCSeconds()) + 'Z';
    }
    function escapeICS( s ){
        return String(s || '').replace(/\\/g,'\\\\').replace(/\n/g,'\\n').replace(/,/g,'\\,').replace(/;/g,'\\;');
    }
    document.querySelectorAll('#schedule .aiad-schedule-item__ics').forEach(function( btn ){
        btn.addEventListener('click', function(){
            var li     = btn.closest('.aiad-schedule-item');
            var title  = li.getAttribute('data-ics-title') || 'AI Awareness Day session';
            var desc   = li.getAttribute('data-ics-desc')  || '';
            var start  = li.getAttribute('data-ics-start') || '';
            var end    = li.getAttribute('data-ics-end')   || start;
            var url    = li.getAttribute('data-ics-url')   || '';
            if ( ! start ) return;
            var ics = [
                'BEGIN:VCALENDAR',
                'VERSION:2.0',
                'PRODID:-//AI Awareness Day//Schedule//EN',
                'CALSCALE:GREGORIAN',
                'METHOD:PUBLISH',
                'BEGIN:VEVENT',
                'UID:' + start + '-' + Math.random().toString(36).slice(2) + '@aiawarenessday',
                'DTSTAMP:' + nowStamp(),
                'DTSTART;TZID=Europe/London:' + start,
                'DTEND;TZID=Europe/London:' + end,
                'SUMMARY:' + escapeICS(title),
                'DESCRIPTION:' + escapeICS(desc + (url ? '\n\nJoin: ' + url : '')),
                url ? 'URL:' + url : '',
                'END:VEVENT',
                'END:VCALENDAR'
            ].filter(Boolean).join('\r\n');
            var blob = new Blob([ics], { type: 'text/calendar;charset=utf-8' });
            var a    = document.createElement('a');
            a.href     = URL.createObjectURL(blob);
            a.download = title.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.ics';
            document.body.appendChild(a);
            a.click();
            setTimeout(function(){ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
        });
    });
})();
</script>
