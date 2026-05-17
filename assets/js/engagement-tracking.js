/**
 * Track schedule joins/clicks, session actions, and AI resource partner clicks.
 */
( function () {
    function track( postId, event, targetUrl ) {
        if ( ! postId || typeof aiad_ajax === 'undefined' || ! aiad_ajax.engagement_nonce ) {
            return;
        }
        var body = 'action=aiad_track_engagement'
            + '&nonce=' + encodeURIComponent( aiad_ajax.engagement_nonce )
            + '&post_id=' + encodeURIComponent( postId )
            + '&event=' + encodeURIComponent( event );
        if ( targetUrl ) {
            body += '&target_url=' + encodeURIComponent( targetUrl );
        }
        if ( typeof navigator.sendBeacon === 'function' ) {
            var blob = new Blob( [ body ], { type: 'application/x-www-form-urlencoded' } );
            navigator.sendBeacon( aiad_ajax.url, blob );
            return;
        }
        fetch( aiad_ajax.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
            keepalive: true,
        } ).catch( function () {} );
    }

    function sessionIdFromEl( el ) {
        if ( ! el ) {
            return '';
        }
        if ( el.dataset && el.dataset.sessionId ) {
            return el.dataset.sessionId;
        }
        var host = el.closest( '[data-session-id]' );
        return host ? host.getAttribute( 'data-session-id' ) || '' : '';
    }

    document.addEventListener(
        'click',
        function ( e ) {
            var partnerCard = e.target.closest( 'a.partner-card--ai-resources[data-partner-id]' );
            if ( partnerCard ) {
                track( partnerCard.getAttribute( 'data-partner-id' ), 'click' );
                return;
            }

            var joinBtn = e.target.closest(
                '.session-single__btn--primary[href], .aiad-schedule-table__cta[href], a.aiad-schedule-card__join[href]'
            );
            if ( joinBtn ) {
                var joinId = sessionIdFromEl( joinBtn );
                if ( joinId ) {
                    track( joinId, 'join' );
                }
                return;
            }

            var titleLink = e.target.closest( '.aiad-schedule-card__title, .aiad-schedule-filter-item td a[href]' );
            if ( titleLink && titleLink.closest( '.aiad-schedule-filter-item, .aiad-schedule-card' ) ) {
                var clickId = sessionIdFromEl( titleLink );
                if ( clickId ) {
                    track( clickId, 'click', titleLink.href || '' );
                }
                return;
            }

            var icsBtn = e.target.closest(
                '.aiad-schedule-card__ics, .session-single__ics, .aiad-schedule-item__ics, .aiad-schedule-table__ics'
            );
            if ( icsBtn ) {
                var calId = sessionIdFromEl( icsBtn );
                if ( calId ) {
                    track( calId, 'calendar' );
                }
                return;
            }

            var shareBtn = e.target.closest(
                '.aiad-schedule-card__share, .session-single__share, .aiad-schedule-table__share'
            );
            if ( shareBtn ) {
                var shareId = sessionIdFromEl( shareBtn );
                if ( shareId ) {
                    track( shareId, 'share' );
                }
            }
        },
        true
    );
} )();
