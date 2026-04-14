/**
 * Timeline: AJAX "Load more" and filter handlers.
 * Fetches additional timeline entries and handles filtering.
 *
 * @package AI_Awareness_Day
 */
(function () {
    'use strict';

    if ( typeof aiad_ajax === 'undefined' || ! aiad_ajax.url ) {
        return;
    }

    var feed   = document.getElementById( 'timeline-feed' );
    var track  = feed ? feed.querySelector( '.timeline-feed__track' ) : null;
    var btn    = document.getElementById( 'timeline-load-more' );
    var wrap   = btn ? btn.closest( '.timeline-feed__load-more' ) : null;
    var filters = document.querySelectorAll( '.timeline-filter-btn' );
    var currentFilter = 'all';

    if ( ! feed || ! track ) {
        return;
    }

    function renderTrackMessage( className, message ) {
        track.replaceChildren();
        var box = document.createElement( 'div' );
        box.className = className;
        box.style.textAlign = 'center';
        box.style.padding = '2rem';
        box.style.color = className.indexOf( 'error' ) !== -1 ? 'var(--red-600)' : 'var(--gray-500)';
        box.textContent = message;
        track.appendChild( box );
    }

    function extractTimelineEntries( html ) {
        if ( ! html ) {
            return [];
        }
        var parser = new DOMParser();
        var doc = parser.parseFromString( html, 'text/html' );
        return Array.prototype.slice.call( doc.querySelectorAll( '.timeline-entry' ) ).map( sanitizeEntryNode );
    }

    function sanitizeEntryNode( node ) {
        var clone = node.cloneNode( true );
        clone.querySelectorAll( 'script, style' ).forEach( function (el) { el.remove(); } );
        clone.querySelectorAll( '*' ).forEach( function (el) {
            Array.prototype.slice.call( el.attributes ).forEach( function (attr) {
                var name = attr.name.toLowerCase();
                var value = attr.value || '';
                if ( name.indexOf( 'on' ) === 0 ) {
                    el.removeAttribute( attr.name );
                    return;
                }
                if ( ( name === 'href' || name === 'src' ) && value.trim().toLowerCase().indexOf( 'javascript:' ) === 0 ) {
                    el.removeAttribute( attr.name );
                }
            } );
        } );
        return clone;
    }

    // ─── Filter buttons ─────────
    if ( filters.length > 0 ) {
        filters.forEach( function ( filterBtn ) {
            filterBtn.addEventListener( 'click', function () {
                var filter = filterBtn.getAttribute( 'data-filter' ) || 'all';
                if ( filter === currentFilter ) {
                    return;
                }

                // Update active state
                filters.forEach( function ( f ) { f.classList.remove( 'timeline-filter-btn--active' ); } );
                filterBtn.classList.add( 'timeline-filter-btn--active' );

                currentFilter = filter;

                // Show loading state on track
                renderTrackMessage( 'timeline-loading', 'Loading...' );

                // Hide load more button during filter
                if ( wrap ) {
                    wrap.style.display = 'none';
                }

                var body = 'action=aiad_timeline_filter'
                    + '&nonce=' + encodeURIComponent( aiad_ajax.timeline_nonce || '' )
                    + '&filter=' + encodeURIComponent( filter );

                fetch( aiad_ajax.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body,
                } )
                    .then( function ( res ) { return res.json(); } )
                    .then( function ( json ) {
                        if ( json.success && json.data ) {
                            if ( json.data.html ) {
                                var entries = extractTimelineEntries( json.data.html );
                                track.replaceChildren();
                                entries.forEach( function ( entry ) {
                                    track.appendChild( entry );
                                } );

                                // Trigger fade-up animation
                                track.offsetHeight;
                                var newEntries = track.querySelectorAll( '.timeline-entry' );
                                newEntries.forEach( function ( entry ) {
                                    entry.classList.add( 'visible' );
                                } );
                            } else {
                                renderTrackMessage( 'timeline-empty', 'No entries found.' );
                            }

                            // Reset offset
                            feed.setAttribute( 'data-offset', String( json.data.count || 0 ) );

                            // Show/hide load more button
                            if ( wrap ) {
                                wrap.style.display = json.data.has_more ? '' : 'none';
                            }
                        }
                    } )
                    .catch( function () {
                        renderTrackMessage( 'timeline-error', 'Error loading entries.' );
                    } );
            } );
        } );
    }

    if ( btn ) {
        btn.addEventListener( 'click', function () {
        if ( btn.classList.contains( 'is-loading' ) ) {
            return;
        }

        btn.classList.add( 'is-loading' );
        var originalText = btn.innerHTML;
        btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite;" aria-hidden="true"><path d="M21 12a9 9 0 11-6.219-8.56"></path></svg> Loading\u2026';

        var offset = parseInt( feed.getAttribute( 'data-offset' ) || '0', 10 );

        var body = 'action=aiad_timeline_load_more'
            + '&nonce=' + encodeURIComponent( aiad_ajax.timeline_nonce || '' )
            + '&offset=' + encodeURIComponent( offset )
            + '&filter=' + encodeURIComponent( currentFilter );

        fetch( aiad_ajax.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        } )
            .then( function ( res ) { return res.json(); } )
            .then( function ( json ) {
                btn.classList.remove( 'is-loading' );
                btn.innerHTML = originalText;

                if ( json.success && json.data ) {
                    if ( json.data.html ) {
                        // Append new entries
                        var newEntries = extractTimelineEntries( json.data.html );

                        newEntries.forEach( function ( entry ) {
                            track.appendChild( entry );
                        } );

                        // Trigger fade-up animation after reflow
                        track.offsetHeight;
                        newEntries.forEach( function ( entry ) {
                            entry.classList.add( 'visible' );
                        } );

                        // Update offset
                        var newOffset = offset + ( json.data.count || 0 );
                        feed.setAttribute( 'data-offset', String( newOffset ) );
                    }

                    // Hide button if no more entries
                    if ( ! json.data.has_more && wrap ) {
                        wrap.style.display = 'none';
                    }
                }
            } )
            .catch( function () {
                btn.classList.remove( 'is-loading' );
                btn.innerHTML = originalText;
            } );
        } );
    }

    // ─── Like, Share, Lite YouTube facade (delegated on feed) ─────────
    feed.addEventListener( 'click', function ( e ) {
        var likeBtn = e.target.closest( '.timeline-entry__like' );
        var shareBtn = e.target.closest( '.timeline-entry__share' );
        var facade = e.target.closest( '.timeline-lite-yt' );
        if ( likeBtn ) {
            e.preventDefault();
            handleLike( likeBtn );
        } else if ( shareBtn ) {
            e.preventDefault();
            handleShare( shareBtn );
        } else if ( facade && ! facade.classList.contains( 'is-activated' ) ) {
            e.preventDefault();
            activateLiteYt( facade );
        }
    } );

    feed.addEventListener( 'keydown', function ( e ) {
        var facade = e.target.closest( '.timeline-lite-yt' );
        if ( facade && ! facade.classList.contains( 'is-activated' ) && ( e.key === 'Enter' || e.key === ' ' ) ) {
            e.preventDefault();
            activateLiteYt( facade );
        }
    } );

    function activateLiteYt( facade ) {
        var videoId = facade.getAttribute( 'data-video-id' );
        var title = facade.getAttribute( 'data-title' ) || 'YouTube video';
        if ( ! videoId ) { return; }
        var iframe = document.createElement( 'iframe' );
        iframe.setAttribute( 'src', 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0' );
        iframe.setAttribute( 'title', title );
        iframe.setAttribute( 'frameborder', '0' );
        iframe.setAttribute( 'allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' );
        iframe.setAttribute( 'allowfullscreen', 'true' );
        facade.appendChild( iframe );
        facade.classList.add( 'is-activated' );
    }

    function handleLike( btn ) {
        if ( btn.getAttribute( 'aria-pressed' ) === 'true' ) {
            return;
        }
        var entryId = btn.getAttribute( 'data-entry-id' );
        var countEl = btn.querySelector( '.timeline-entry__like-count' );
        if ( ! entryId || ! countEl ) { return; }

        var body = 'action=aiad_timeline_like'
            + '&nonce=' + encodeURIComponent( aiad_ajax.timeline_nonce || '' )
            + '&entry_id=' + encodeURIComponent( entryId );

        fetch( aiad_ajax.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        } )
            .then( function ( res ) { return res.json(); } )
            .then( function ( json ) {
                if ( json.success && json.data && typeof json.data.count === 'number' ) {
                    countEl.textContent = String( json.data.count );
                    btn.setAttribute( 'aria-pressed', 'true' );
                    btn.classList.add( 'is-liked' );
                }
            } );
    }

    function handleShare( btn ) {
        var url = btn.getAttribute( 'data-url' ) || '';
        var title = btn.getAttribute( 'data-title' ) || '';
        var originalAria = btn.getAttribute( 'aria-label' ) || 'Share';

        if ( navigator.share && typeof navigator.share === 'function' ) {
            navigator.share( { title: title, text: title, url: url } ).then( function () {
                btn.setAttribute( 'aria-label', 'Shared!' );
                setTimeout( function () { btn.setAttribute( 'aria-label', originalAria ); }, 2000 );
            } ).catch( function () { } );
        } else {
            copyToClipboard( url ).then( function ( ok ) {
                btn.setAttribute( 'aria-label', ok ? 'Link copied!' : 'Copy failed' );
                setTimeout( function () { btn.setAttribute( 'aria-label', originalAria ); }, 2000 );
            } );
        }
    }

    function copyToClipboard( text ) {
        if ( navigator.clipboard && navigator.clipboard.writeText ) {
            return navigator.clipboard.writeText( text ).then( function () { return true; }, function () { return false; } );
        }
        var ta = document.createElement( 'textarea' );
        ta.value = text;
        ta.setAttribute( 'readonly', '' );
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild( ta );
        ta.select();
        try {
            document.execCommand( 'copy' );
            return Promise.resolve( true );
        } catch ( err ) {
            return Promise.resolve( false );
        } finally {
            document.body.removeChild( ta );
        }
    }
})();
