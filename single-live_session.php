<?php
/**
 * Single template: live_session.
 *
 * @package AI_Awareness_Day
 */
get_header();

if ( ! have_posts() ) {
    get_footer();
    return;
}

the_post();

$post_id    = get_the_ID();
$title      = get_the_title();
$content    = get_the_content();
$permalink  = get_permalink();

$start      = (string) get_post_meta( $post_id, '_session_start_time', true );
$end        = (string) get_post_meta( $post_id, '_session_end_time', true );
$time_range = function_exists( 'aiad_format_session_time_range' ) ? aiad_format_session_time_range( $start, $end ) : '';
$date_label = function_exists( 'aiad_format_session_date' ) ? aiad_format_session_date( $start ) : '';
$is_past    = function_exists( 'aiad_session_is_past' ) && aiad_session_is_past( $post_id );
$format     = (string) get_post_meta( $post_id, '_session_format', true );
$reg_url    = (string) get_post_meta( $post_id, '_session_registration_url', true );
$partner_id = (int) get_post_meta( $post_id, '_session_partner_id', true );
$partner    = $partner_id ? get_post( $partner_id ) : null;
$partner_logo = $partner_id ? get_the_post_thumbnail_url( $partner_id, 'medium' ) : '';

$aud_terms  = get_the_terms( $post_id, 'session_audience' );
$aud_names  = ( $aud_terms && ! is_wp_error( $aud_terms ) )
    ? wp_list_pluck( $aud_terms, 'name' )
    : array();

$ics_start  = $start ? str_replace( array( '-', ':' ), '', $start ) . '00' : '';
$ics_end    = $end   ? str_replace( array( '-', ':' ), '', $end )   . '00' : $ics_start;

$archive_url = get_post_type_archive_link( 'live_session' ) ?: home_url( '/events/' );

$share_aria = sprintf(
    /* translators: %s: session title */
    __( 'Share "%s"', 'ai-awareness-day' ),
    $title
);
?>
<main id="main" role="main" class="single-live-session">
    <section class="section pt-100">
        <div class="container container--narrow">

            <a class="session-single__back" href="<?php echo esc_url( $archive_url ); ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <?php esc_html_e( 'All events', 'ai-awareness-day' ); ?>
            </a>

            <article class="session-single"
                data-session-id="<?php echo esc_attr( (string) $post_id ); ?>"
                data-ics-title="<?php echo esc_attr( $title ); ?>"
                data-ics-desc="<?php echo esc_attr( wp_strip_all_tags( $content ?: '' ) ); ?>"
                data-ics-start="<?php echo esc_attr( $ics_start ); ?>"
                data-ics-end="<?php echo esc_attr( $ics_end ); ?>"
                data-ics-url="<?php echo esc_attr( $reg_url ); ?>">

                <header class="session-single__header">
                    <?php if ( $date_label !== '' || $time_range !== '' ) : ?>
                        <div class="session-single__when">
                            <?php if ( $date_label !== '' ) : ?>
                                <p class="session-single__date"><?php echo esc_html( $date_label ); ?></p>
                            <?php endif; ?>
                            <?php if ( $time_range !== '' ) : ?>
                                <p class="session-single__time"><?php echo esc_html( $time_range ); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="session-single__title"><?php echo esc_html( $title ); ?></h1>

                    <?php if ( ! empty( $aud_names ) || $format !== '' ) : ?>
                        <div class="session-single__tags">
                            <?php foreach ( $aud_names as $name ) : ?>
                                <span class="session-tag"><?php echo esc_html( $name ); ?></span>
                            <?php endforeach; ?>
                            <?php if ( $format !== '' ) : ?>
                                <span class="session-tag session-tag--format"><?php echo esc_html( $format ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if ( $content !== '' ) : ?>
                    <div class="session-single__body entry-content">
                        <?php echo wp_kses_post( wpautop( $content ) ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( $partner ) : ?>
                    <div class="session-single__provider">
                        <?php if ( $partner_logo ) : ?>
                            <span class="session-single__logo-wrap">
                                <img class="session-single__logo" src="<?php echo esc_url( $partner_logo ); ?>" alt="" loading="lazy" decoding="async" />
                            </span>
                        <?php endif; ?>
                        <div>
                            <span class="session-single__provider-label"><?php esc_html_e( 'Delivered by', 'ai-awareness-day' ); ?></span>
                            <span class="session-single__provider-name"><?php echo esc_html( $partner->post_title ); ?></span>
                        </div>
                    </div>
                <?php endif; ?>

                <footer class="session-single__actions">
                    <?php
                    $action_link = function_exists( 'aiad_render_session_action_link' )
                        ? aiad_render_session_action_link( $post_id, $reg_url, 'session-single' )
                        : '';
					if ( $action_link ) :
						echo $action_link; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					elseif ( ! $is_past ) :
                    ?>
                        <span class="session-single__btn session-single__btn--soon"><?php esc_html_e( 'Join link coming soon', 'ai-awareness-day' ); ?></span>
                    <?php endif; ?>

                    <?php if ( $ics_start !== '' && ! $is_past ) : ?>
                        <button type="button" class="session-single__btn session-single__btn--secondary session-single__ics"
                            aria-label="<?php echo esc_attr( sprintf( __( 'Add "%s" to calendar', 'ai-awareness-day' ), $title ) ); ?>">
                            <span aria-hidden="true">📅</span>
                            <?php esc_html_e( 'Add to calendar', 'ai-awareness-day' ); ?>
                        </button>
                    <?php endif; ?>

                    <button type="button" class="session-single__btn session-single__btn--secondary session-single__share"
                        data-share-url="<?php echo esc_attr( $permalink ); ?>"
                        data-share-title="<?php echo esc_attr( $title ); ?>"
                        aria-label="<?php echo esc_attr( $share_aria ); ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        <?php esc_html_e( 'Share', 'ai-awareness-day' ); ?>
                    </button>
                </footer>

            </article>

        </div>
    </section>
</main>
<script>
(function(){
    var article = document.querySelector('.session-single');
    if ( ! article ) return;

    // ICS download
    var icsBtn = article.querySelector('.session-single__ics');
    if ( icsBtn ) {
        icsBtn.addEventListener('click', function(){
            var title = article.dataset.icsTitle || '';
            var desc  = article.dataset.icsDesc  || '';
            var start = article.dataset.icsStart || '';
            var end   = article.dataset.icsEnd   || start;
            var url   = article.dataset.icsUrl   || '';
            if ( ! start ) return;
            function pad(n){ return n < 10 ? '0' + n : '' + n; }
            function nowStamp(){
                var d = new Date();
                return d.getFullYear() + pad(d.getMonth()+1) + pad(d.getDate()) + 'T' + pad(d.getHours()) + pad(d.getMinutes()) + pad(d.getSeconds()) + 'Z';
            }
            function escICS(s){ return (s||'').replace(/\\/g,'\\\\').replace(/;/g,'\\;').replace(/,/g,'\\,').replace(/\n/g,'\\n'); }
            var ics = [
                'BEGIN:VCALENDAR','VERSION:2.0','PRODID:-//AI Awareness Day//EN','CALSCALE:GREGORIAN','METHOD:PUBLISH',
                'BEGIN:VEVENT',
                'UID:' + start + '-' + Math.random().toString(36).slice(2) + '@aiawarenessday.co.uk',
                'DTSTAMP:' + nowStamp(),
                'DTSTART;TZID=Europe/London:' + start,
                'DTEND;TZID=Europe/London:' + end,
                'SUMMARY:' + escICS(title),
                'DESCRIPTION:' + escICS(desc + (url ? '\n\nJoin: ' + url : '')),
                url ? 'URL:' + url : '',
                'END:VEVENT','END:VCALENDAR'
            ].filter(Boolean).join('\r\n');
            var blob = new Blob([ics], { type: 'text/calendar;charset=utf-8' });
            var a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = title.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.ics';
            document.body.appendChild(a); a.click();
            setTimeout(function(){ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
        });
    }

    // Share
    var shareBtn = article.querySelector('.session-single__share');
    if ( shareBtn ) {
        var copiedMsg = <?php echo wp_json_encode( __( 'Link copied!', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
        shareBtn.addEventListener('click', function(){
            var url   = shareBtn.dataset.shareUrl   || '';
            var title = shareBtn.dataset.shareTitle || '';
            if ( ! url ) return;
            if ( navigator.share ) { navigator.share({ title: title, url: url }).catch(function(){}); return; }
            if ( navigator.clipboard ) {
                var prev = shareBtn.textContent;
                navigator.clipboard.writeText(url).then(function(){
                    shareBtn.textContent = copiedMsg;
                    setTimeout(function(){ shareBtn.textContent = prev; }, 2200);
                });
            }
        });
    }
})();
</script>
<?php get_footer(); ?>
