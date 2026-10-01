/**
 * Schedule: audience filters on the live sessions archive and the homepage spotlight's calendar download and share.
 * Enqueued by aiad_print_schedule_audience_filter_script() (inc/live-sessions.php).
 */
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
        var copiedMsg = window.wp.i18n.__( 'Link copied', 'ai-awareness-day' );
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
        var copiedMsg = window.wp.i18n.__( 'Link copied', 'ai-awareness-day' );
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
                var copiedMsg = window.wp.i18n.__( 'Link copied!', 'ai-awareness-day' );
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
