<?php
/**
 * Events (live sessions): markup helpers for the schedule — session action link and its icon, audience filter tabs,
 * and the inline filter / calendar script.
 *
 * The live_session post type, its data helpers, admin UI and calendar (ICS) feed live in the aiad-core plugin
 * (plugins/aiad-core/modules/live-sessions.php).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * External-link icon for conference / CPD cards.
 */
function aiad_session_link_icon_svg(): string {
	if ( function_exists( 'aiad_timeline_link_icon_svg' ) ) {
		return aiad_timeline_link_icon_svg();
	}
	return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
}

/**
 * Primary schedule CTA: “Join” for live sessions, chain-link icon for conferences.
 *
 * @param int    $post_id live_session post ID.
 * @param string $url     Registration / join URL.
 * @param string $block   BEM block prefix (e.g. aiad-schedule-card, aiad-schedule-table).
 */
function aiad_render_session_action_link( int $post_id, string $url, string $block = 'aiad-schedule-card' ): string {
	if ( ! aiad_session_show_join_link( $post_id ) || $url === '' ) {
		return '';
	}

	$is_conference = aiad_session_is_conference( $post_id );
	$label         = aiad_session_cta_label( $post_id );
	$classes       = $block . '__join ' . $block . '__link--action';
	if ( $is_conference ) {
		$classes .= ' ' . $block . '__link--icon';
	}
	if ( $block === 'aiad-schedule-table' ) {
		$classes = 'aiad-schedule-table__cta' . ( $is_conference ? ' aiad-schedule-table__cta--icon' : '' );
	}
	if ( $block === 'session-single' ) {
		$classes = 'session-single__btn session-single__btn--primary' . ( $is_conference ? ' session-single__btn--icon' : '' );
	}

	$inner = $is_conference
		? '<span class="' . esc_attr( $block ) . '__link-icon" aria-hidden="true">' . aiad_session_link_icon_svg() . '</span>'
		: esc_html( $label );

	if ( $is_conference && $block === 'session-single' ) {
		$inner = '<span class="session-single__link-icon" aria-hidden="true">' . aiad_session_link_icon_svg() . '</span>';
	} elseif ( ! $is_conference && $block === 'session-single' ) {
		$inner = esc_html( $label ) . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" data-session-id="%3$s" target="_blank" rel="noopener"%4$s>%5$s</a>',
		esc_attr( $classes ),
		esc_url( $url ),
		esc_attr( (string) $post_id ),
		$is_conference ? ' aria-label="' . esc_attr( $label ) . '"' : '',
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from trusted helper.
	);
}

/**
 * Audience filters shared by the front-page schedule block and /schedule/ archive.
 * Markup matches the Live Timeline filter row (timeline-filters / timeline-filter-btn).
 *
 * @param array<string, string> $audience_labels Slug => display name.
 * @param array<string, int>    $audience_counts Retained for API compatibility; not displayed (timeline pills have no counts).
 * @param int                   $session_count   Retained for API compatibility; not displayed.
 */
function aiad_render_schedule_audience_tabs( array $audience_labels, array $audience_counts, int $session_count ): void {
    ?>
    <div class="timeline-filters fade-up" role="group" aria-label="<?php esc_attr_e( 'Filter sessions by audience', 'ai-awareness-day' ); ?>">
        <button type="button" class="timeline-filter-btn timeline-filter-btn--active" data-filter="all">
            <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
        </button>
        <?php foreach ( $audience_labels as $slug => $name ) : ?>
            <button type="button" class="timeline-filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>">
                <?php echo esc_html( $name ); ?>
            </button>
        <?php endforeach; ?>
        <button type="button" class="timeline-filter-btn" disabled aria-disabled="true">
            <?php esc_html_e( 'Parents', 'ai-awareness-day' ); ?>
            <span class="timeline-filter-btn__suffix" aria-hidden="true"><?php esc_html_e( ' · Soon', 'ai-awareness-day' ); ?></span>
        </button>
    </div>
    <?php
}

/**
 * One inline script per page: schedule archive audience filters + ICS, and homepage spotlight ICS/share.
 */
function aiad_print_schedule_audience_filter_script(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;
    ?>
<script>
(function(){
    function pad( n ){ return String(n).padStart(2, '0'); }
    function nowStamp(){
        var d = new Date();
        return d.getUTCFullYear() + pad(d.getUTCMonth()+1) + pad(d.getUTCDate())
             + 'T' + pad(d.getUTCHours()) + pad(d.getUTCMinutes()) + pad(d.getUTCSeconds()) + 'Z';
    }
    function escapeICS( s ){
        return String(s || '').replace(/\\/g,'\\\\').replace(/\n/g,'\\n').replace(/,/g,'\\,').replace(/;/g,'\\;');
    }
    function downloadIcsFromHost( host ){
        if ( ! host ) return;
        var title  = host.getAttribute('data-ics-title') || 'AI Awareness Day session';
        var desc   = host.getAttribute('data-ics-desc')  || '';
        var start  = host.getAttribute('data-ics-start') || '';
        var end    = host.getAttribute('data-ics-end')   || start;
        var url    = host.getAttribute('data-ics-url')   || '';
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
    }
    function setScheduleRowVisible( row, show ) {
        row.classList.toggle('aiad-schedule-filter-item--hidden', !show);
        row.hidden = !show;
        row.setAttribute('aria-hidden', show ? 'false' : 'true');
        row.querySelectorAll('td').forEach(function( cell ){
            if ( show ) {
                cell.removeAttribute('hidden');
            } else {
                cell.setAttribute('hidden', '');
            }
        });
    }
    function wireAudienceFilters( root ) {
        var tabs  = root.querySelectorAll('.timeline-filter-btn');
        var items = root.querySelectorAll('.aiad-schedule-filter-item');
        var empty = root.querySelector('.aiad-schedule-row__empty');
        tabs.forEach(function( tab ){
            if ( tab.disabled ) return;
            tab.addEventListener('click', function(){
                var target = tab.getAttribute('data-filter') || 'all';
                tabs.forEach(function( t ){
                    if ( t.disabled ) return;
                    var active = t === tab;
                    t.classList.toggle('timeline-filter-btn--active', active);
                });
                var visibleCount = 0;
                items.forEach(function( row ){
                    var slugs = (row.getAttribute('data-audience') || '').split(/\s+/).filter(Boolean);
                    var show  = target === 'all' || slugs.indexOf(target) !== -1;
                    setScheduleRowVisible(row, show);
                    if ( show ) visibleCount++;
                });
                if ( empty ) empty.hidden = visibleCount > 0;
            });
        });
    }
    function wireIcsButtons( root, btnSel, hostSel ) {
        root.querySelectorAll( btnSel ).forEach(function( btn ){
            btn.addEventListener('click', function(){
                var host = btn.closest( hostSel );
                downloadIcsFromHost( host );
            });
        });
    }
    function wireScheduleSpotlight( root ) {
        var copiedMsg = <?php echo wp_json_encode( __( 'Link copied', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); ?>;
        root.querySelectorAll('.aiad-schedule-card__share').forEach(function( btn ){
            btn.addEventListener('click', function(){
                var url = btn.getAttribute('data-share-url') || '';
                var title = btn.getAttribute('data-share-title') || '';
                if ( ! url ) return;
                if ( navigator.share ) {
                    navigator.share({ title: title, text: title, url: url }).catch(function(){});
                    return;
                }
                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    var prev = btn.getAttribute('aria-label') || '';
                    navigator.clipboard.writeText( url ).then(function(){
                        btn.setAttribute('aria-label', copiedMsg);
                        setTimeout(function(){ btn.setAttribute('aria-label', prev); }, 2200);
                    }).catch(function(){});
                }
            });
        });
        wireIcsButtons( root, '.aiad-schedule-card__ics', '.aiad-schedule-card' );
    }
    function wireTableShare( root ) {
        var copiedMsg = <?php echo wp_json_encode( __( 'Link copied', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); ?>;
        root.querySelectorAll('.aiad-schedule-table__share').forEach(function( btn ){
            btn.addEventListener('click', function(){
                var url   = btn.getAttribute('data-share-url')   || '';
                var title = btn.getAttribute('data-share-title') || '';
                if ( ! url ) return;
                if ( navigator.share ) {
                    navigator.share({ title: title, url: url }).catch(function(){});
                    return;
                }
                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    var prev = btn.getAttribute('aria-label') || '';
                    navigator.clipboard.writeText( url ).then(function(){
                        btn.setAttribute('aria-label', copiedMsg);
                        setTimeout(function(){ btn.setAttribute('aria-label', prev); }, 2200);
                    }).catch(function(){});
                }
            });
        });
    }
    document.querySelectorAll('.aiad-schedule-filter-root').forEach(function( root ){
        wireAudienceFilters( root );
        wireIcsButtons( root, '.aiad-schedule-item__ics', '.aiad-schedule-item' );
        wireTableShare( root );
    });
    var spotlight = document.getElementById('schedule');
    if ( spotlight && spotlight.classList.contains('aiad-schedule-home') ) {
        wireScheduleSpotlight( spotlight );
    }
    // Archive page: wire copy-link + native share buttons
    var copyBtn   = document.querySelector('.aiad-schedule-share-bar__btn--copy');
    var nativeBtn = document.querySelector('.aiad-schedule-share-bar__btn--native');
    var shareStatus = document.querySelector('.aiad-schedule-share-bar__status');
    if ( nativeBtn && navigator.share ) {
        nativeBtn.hidden = false;
        nativeBtn.addEventListener('click', function(){
            navigator.share({ title: document.title, url: window.location.href }).catch(function(){});
        });
    }
    if ( copyBtn ) {
        copyBtn.addEventListener('click', function(){
            if ( navigator.clipboard ) {
                var copiedMsg = <?php echo wp_json_encode( __( 'Link copied!', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
                navigator.clipboard.writeText( window.location.href ).then(function(){
                    if ( shareStatus ) {
                        shareStatus.textContent = copiedMsg;
                        setTimeout(function(){ shareStatus.textContent = ''; }, 2500);
                    }
                }).catch(function(){});
            }
        });
    }
})();
</script>
    <?php
}
