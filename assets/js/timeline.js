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

        viewport.querySelectorAll( '.timeline-swipe__slide--clone' ).forEach( function ( el ) {
            el.remove();
        } );

        var slides = viewport.querySelectorAll( '.timeline-swipe__slide' );
        var dotsWrap = swipe.querySelector( '.timeline-swipe__dots' );
        var counterCurrent = swipe.querySelector( '.timeline-swipe__counter-current' );
        var counterTotal = swipe.querySelector( '.timeline-swipe__counter-total' );
        var mediaHints = swipe.querySelectorAll( '.timeline-swipe__media-hint' );
        var count = slides.length;
        var infinite = count >= 2;

        if ( infinite ) {
            var firstSlide = slides[0];
            var lastSlide = slides[ count - 1 ];
            var firstClone = firstSlide.cloneNode( true );
            var lastClone = lastSlide.cloneNode( true );
            firstClone.classList.add( 'timeline-swipe__slide--clone' );
            lastClone.classList.add( 'timeline-swipe__slide--clone' );
            firstClone.setAttribute( 'aria-hidden', 'true' );
            lastClone.setAttribute( 'aria-hidden', 'true' );
            viewport.insertBefore( lastClone, firstSlide );
            viewport.appendChild( firstClone );
        }

        if ( dotsWrap ) {
            dotsWrap.replaceChildren();
            for ( var i = 0; i < count; i++ ) {
                var dot = document.createElement( 'button' );
                dot.type = 'button';
                dot.className = 'timeline-swipe__dot' + ( i === 0 ? ' is-active' : '' );
                dot.setAttribute( 'aria-label', 'Go to update ' + ( i + 1 ) );
                dot.setAttribute( 'role', 'tab' );
                dot.setAttribute( 'aria-selected', i === 0 ? 'true' : 'false' );
                dot.dataset.index = String( i );
                dotsWrap.appendChild( dot );
            }
        }

        if ( counterTotal ) {
            counterTotal.textContent = String( count );
        }

        function getSlideWidth() {
            return viewport.clientWidth;
        }

        function getRawIndex() {
            var slideWidth = getSlideWidth();
            if ( slideWidth <= 0 ) {
                return infinite ? 1 : 0;
            }
            return Math.round( viewport.scrollLeft / slideWidth );
        }

        function rawToLogical( raw ) {
            if ( ! infinite ) {
                return Math.min( count - 1, Math.max( 0, raw ) );
            }
            if ( raw <= 0 ) {
                return count - 1;
            }
            if ( raw >= count + 1 ) {
                return 0;
            }
            return raw - 1;
        }

        function logicalToRaw( logical ) {
            return infinite ? logical + 1 : logical;
        }

        function setActive( index ) {
            if ( dotsWrap ) {
                dotsWrap.querySelectorAll( '.timeline-swipe__dot' ).forEach( function ( dot, i ) {
                    var isCurrent = ( i === index );
                    dot.classList.toggle( 'is-active', isCurrent );
                    dot.setAttribute( 'aria-selected', isCurrent ? 'true' : 'false' );
                } );
            }
            if ( counterCurrent ) {
                counterCurrent.textContent = String( index + 1 );
            }
            if ( mediaHints.length && index > 0 ) {
                mediaHints.forEach( function ( el ) {
                    el.classList.add( 'is-hidden' );
                } );
            }
        }

        function normalizeInfiniteScroll() {
            if ( ! infinite ) {
                return;
            }
            var slideWidth = getSlideWidth();
            if ( slideWidth <= 0 ) {
                return;
            }
            var raw = getRawIndex();
            var target = null;
            if ( raw === 0 ) {
                target = count * slideWidth;
            } else if ( raw >= count + 1 ) {
                target = slideWidth;
            }
            if ( target !== null ) {
                viewport.scrollLeft = target;
            }
        }

        function syncFromScroll() {
            if ( count === 0 ) {
                return;
            }
            setActive( rawToLogical( getRawIndex() ) );
        }

        var jumpTimer;
        function onSwipeScroll() {
            syncFromScroll();
            if ( ! infinite ) {
                return;
            }
            clearTimeout( jumpTimer );
            jumpTimer = setTimeout( function () {
                normalizeInfiniteScroll();
                syncFromScroll();
            }, 90 );
        }

        if ( viewport._aiadSwipeScroll ) {
            viewport.removeEventListener( 'scroll', viewport._aiadSwipeScroll );
        }
        viewport._aiadSwipeScroll = onSwipeScroll;
        viewport.addEventListener( 'scroll', onSwipeScroll, { passive: true } );

        if ( viewport._aiadSwipeScrollEnd ) {
            viewport.removeEventListener( 'scrollend', viewport._aiadSwipeScrollEnd );
        }
        if ( 'onscrollend' in window ) {
            viewport._aiadSwipeScrollEnd = function () {
                normalizeInfiniteScroll();
                syncFromScroll();
            };
            viewport.addEventListener( 'scrollend', viewport._aiadSwipeScrollEnd );
        }

        function scrollToLogical( logical, smooth ) {
            var slideWidth = getSlideWidth();
            if ( slideWidth <= 0 ) {
                return;
            }
            viewport.scrollTo( {
                left: logicalToRaw( logical ) * slideWidth,
                behavior: smooth ? 'smooth' : 'auto',
            } );
        }

        function setInitialPosition() {
            if ( infinite ) {
                var slideWidth = getSlideWidth();
                if ( slideWidth > 0 ) {
                    viewport.scrollLeft = slideWidth;
                }
            }
            syncFromScroll();
        }

        setInitialPosition();
        requestAnimationFrame( setInitialPosition );

        if ( feed._aiadSwipeResize ) {
            window.removeEventListener( 'resize', feed._aiadSwipeResize );
        }
        feed._aiadSwipeResize = function () {
            if ( ! viewport.isConnected ) {
                return;
            }
            var logical = rawToLogical( getRawIndex() );
            scrollToLogical( logical, false );
        };
        window.addEventListener( 'resize', feed._aiadSwipeResize );

        if ( dotsWrap && ! dotsWrap._aiadDotsBound ) {
            dotsWrap._aiadDotsBound = true;
            dotsWrap.addEventListener( 'click', function ( e ) {
                var dot = e.target.closest( '.timeline-swipe__dot' );
                if ( ! dot ) {
                    return;
                }
                var idx = parseInt( dot.dataset.index || '0', 10 );
                scrollToLogical( idx, true );
            } );

            dotsWrap.addEventListener( 'keydown', function ( e ) {
                var activeDot = dotsWrap.querySelector( '.timeline-swipe__dot.is-active' );
                if ( ! activeDot ) {
                    return;
                }
                var idx = parseInt( activeDot.dataset.index || '0', 10 );
                if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) {
                    e.preventDefault();
                    var nextIdx = ( idx + 1 ) % count;
                    scrollToLogical( nextIdx, true );
                    var nextDot = dotsWrap.querySelector( '.timeline-swipe__dot[data-index="' + nextIdx + '"]' );
                    if ( nextDot ) {
                        nextDot.focus();
                    }
                } else if ( e.key === 'ArrowLeft' || e.key === 'ArrowUp' ) {
                    e.preventDefault();
                    var prevIdx = ( idx - 1 + count ) % count;
                    scrollToLogical( prevIdx, true );
                    var prevDot = dotsWrap.querySelector( '.timeline-swipe__dot[data-index="' + prevIdx + '"]' );
                    if ( prevDot ) {
                        prevDot.focus();
                    }
                }
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

    function trackEngagement( postId, event, targetUrl ) {
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
        fetch( aiad_ajax.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        } ).catch( function () {} );
    }

    feed.addEventListener( 'click', function ( e ) {
        var likeBtn = e.target.closest( '.timeline-entry__like' );
        var shareBtn = e.target.closest( '.timeline-entry__share' );
        var linkEl = e.target.closest( '.timeline-entry__link' );
        if ( likeBtn ) {
            e.preventDefault();
            handleLike( likeBtn );
        } else if ( shareBtn ) {
            e.preventDefault();
            handleShare( shareBtn );
        } else if ( linkEl ) {
            var entryFromLink = linkEl.getAttribute( 'data-entry-id' )
                || ( linkEl.closest( '[data-entry-id]' ) && linkEl.closest( '[data-entry-id]' ).getAttribute( 'data-entry-id' ) );
            if ( entryFromLink ) {
                trackEngagement( entryFromLink, 'click', linkEl.href || '' );
            }
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
                trackEngagement( btn.getAttribute( 'data-entry-id' ), 'share' );
                btn.setAttribute( 'aria-label', 'Shared!' );
                setTimeout( function () { btn.setAttribute( 'aria-label', originalAria ); }, 2000 );
            } ).catch( function () { } );
        } else {
            copyToClipboard( url ).then( function ( ok ) {
                if ( ok ) {
                    trackEngagement( btn.getAttribute( 'data-entry-id' ), 'share' );
                }
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
