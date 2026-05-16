/**
 * Timeline: swipe deck (mobile), magazine (desktop), AJAX filter, like/share.
 *
 * @package AI_Awareness_Day
 */
(function () {
    'use strict';

    if ( typeof aiad_ajax === 'undefined' || ! aiad_ajax.url ) {
        return;
    }

    var feed    = document.getElementById( 'timeline-feed' );
    var feedRoot = feed ? feed.closest( '.timeline-archive__root, #timeline' ) : null;
    var filters = feedRoot
        ? feedRoot.querySelectorAll( '.timeline-filter-btn' )
        : document.querySelectorAll( '.timeline-filter-btn' );
    var isArchive = feed && feed.hasAttribute( 'data-timeline-archive' );
    var currentFilter = 'all';

    if ( ! feed ) {
        return;
    }

    function getFeedBody() {
        return feed.querySelector( '.timeline-feed__body' );
    }

    function getSwipeViewport() {
        return feed.querySelector( '.timeline-swipe__viewport' );
    }

    function renderFeedEmpty( message ) {
        var body = getFeedBody();
        if ( body ) {
            body.innerHTML = '<p class="timeline-feed__empty">' + message + '</p>';
        }
    }

    function initSwipeDeck() {
        var swipe = feed.querySelector( '.timeline-swipe' );
        var viewport = getSwipeViewport();
        if ( ! swipe || ! viewport ) {
            return;
        }

        var slides = viewport.querySelectorAll( '.timeline-swipe__slide' );
        var dotsWrap = swipe.querySelector( '.timeline-swipe__dots' );
        var counterCurrent = swipe.querySelector( '.timeline-swipe__counter-current' );
        var counterTotal = swipe.querySelector( '.timeline-swipe__counter-total' );
        var hint = swipe.querySelector( '.timeline-swipe__hint' );
        var count = slides.length;

        if ( dotsWrap ) {
            dotsWrap.replaceChildren();
            for ( var i = 0; i < count; i++ ) {
                var dot = document.createElement( 'button' );
                dot.type = 'button';
                dot.className = 'timeline-swipe__dot' + ( i === 0 ? ' is-active' : '' );
                dot.setAttribute( 'aria-label', 'Go to update ' + ( i + 1 ) );
                dot.dataset.index = String( i );
                dotsWrap.appendChild( dot );
            }
        }

        if ( counterTotal ) {
            counterTotal.textContent = String( count );
        }

        function setActive( index ) {
            if ( dotsWrap ) {
                dotsWrap.querySelectorAll( '.timeline-swipe__dot' ).forEach( function ( dot, i ) {
                    dot.classList.toggle( 'is-active', i === index );
                } );
            }
            if ( counterCurrent ) {
                counterCurrent.textContent = String( index + 1 );
            }
            if ( hint && index > 0 ) {
                hint.classList.add( 'is-hidden' );
            }
        }

        function syncFromScroll() {
            var slideWidth = viewport.clientWidth;
            if ( slideWidth <= 0 || count === 0 ) {
                return;
            }
            var index = Math.min( count - 1, Math.max( 0, Math.round( viewport.scrollLeft / slideWidth ) ) );
            setActive( index );
        }

        if ( viewport._aiadSwipeScroll ) {
            viewport.removeEventListener( 'scroll', viewport._aiadSwipeScroll );
        }
        viewport._aiadSwipeScroll = syncFromScroll;
        viewport.addEventListener( 'scroll', syncFromScroll, { passive: true } );
        syncFromScroll();

        if ( dotsWrap && ! dotsWrap._aiadDotsBound ) {
            dotsWrap._aiadDotsBound = true;
            dotsWrap.addEventListener( 'click', function ( e ) {
                var dot = e.target.closest( '.timeline-swipe__dot' );
                if ( ! dot ) {
                    return;
                }
                var idx = parseInt( dot.dataset.index || '0', 10 );
                var w = viewport.clientWidth;
                viewport.scrollTo( { left: idx * w, behavior: 'smooth' } );
            } );
        }
    }

    function replaceFeedLayouts( html ) {
        var existing = getFeedBody();
        var temp = document.createElement( 'div' );
        temp.innerHTML = html.trim();
        var newBody = temp.querySelector( '.timeline-feed__body' ) || temp.firstElementChild;
        if ( existing && newBody ) {
            existing.replaceWith( newBody );
        } else if ( newBody ) {
            feed.appendChild( newBody );
        }
        if ( ! isArchive ) {
            initSwipeDeck();
        }
    }

    if ( ! isArchive ) {
        initSwipeDeck();
    }

    if ( filters.length > 0 ) {
        filters.forEach( function ( filterBtn ) {
            filterBtn.addEventListener( 'click', function () {
                var filter = filterBtn.getAttribute( 'data-filter' ) || 'all';
                if ( filter === currentFilter ) {
                    return;
                }

                filters.forEach( function ( f ) {
                    f.classList.remove( 'timeline-filter-btn--active' );
                } );
                filterBtn.classList.add( 'timeline-filter-btn--active' );
                currentFilter = filter;

                renderFeedEmpty( 'Loading…' );

                var body = 'action=aiad_timeline_filter'
                    + '&nonce=' + encodeURIComponent( aiad_ajax.timeline_nonce || '' )
                    + '&filter=' + encodeURIComponent( filter );
                if ( isArchive ) {
                    body += '&archive=1';
                }

                fetch( aiad_ajax.url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body,
                } )
                    .then( function ( res ) { return res.json(); } )
                    .then( function ( json ) {
                        if ( json.success && json.data ) {
                            if ( json.data.html ) {
                                replaceFeedLayouts( json.data.html );
                            } else {
                                renderFeedEmpty( 'No entries found.' );
                            }
                        }
                    } )
                    .catch( function () {
                        renderFeedEmpty( 'Error loading entries.' );
                    } );
            } );
        } );
    }

    feed.addEventListener( 'click', function ( e ) {
        var likeBtn = e.target.closest( '.timeline-entry__like' );
        var shareBtn = e.target.closest( '.timeline-entry__share' );
        if ( likeBtn ) {
            e.preventDefault();
            handleLike( likeBtn );
        } else if ( shareBtn ) {
            e.preventDefault();
            handleShare( shareBtn );
        }
    } );

    function handleLike( btn ) {
        if ( btn.getAttribute( 'aria-pressed' ) === 'true' ) {
            return;
        }
        var entryId = btn.getAttribute( 'data-entry-id' );
        var countEl = btn.querySelector( '.timeline-entry__like-count' );
        if ( ! entryId || ! countEl ) {
            return;
        }

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
} )();
