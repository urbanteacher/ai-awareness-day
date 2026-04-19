/**
 * AJAX resource filtering — no page reload.
 * Listens to filter selects, fetches results, updates grid and URL.
 */
(function () {
    'use strict';

    var form = document.querySelector( '.resource-filter-form' );
    var grid = document.querySelector( '.resources-grid' );
    var loadingEl = document.querySelector( '.resources-loading' );
    var emptyMessage = document.querySelector( '.resources-empty-message' );
    var ajaxConfig = typeof aiad_ajax !== 'undefined' ? aiad_ajax : {};
    var cardConfig = typeof aiadResourceCard !== 'undefined' ? aiadResourceCard : {};
    var durationPillParts = cardConfig.durationPillParts || {};
    var canUseAjax = !! ajaxConfig.url;

    if ( ! form || ! grid ) {
        return;
    }

    var baseUrl = form.getAttribute( 'action' ) || window.location.href;
    var isFeatured = window.location.pathname.indexOf( 'from-partners' ) !== -1;
    var postType = isFeatured ? 'featured_resource' : 'resource';

    function getFilterValues() {
        var selects = form.querySelectorAll( 'select[data-filter="true"]' );
        var data = {
            action: 'aiad_filter_resources',
            filter_nonce: ajaxConfig.filter_nonce || '',
            post_type: postType
        };
        selects.forEach( function (sel) {
            var name = sel.getAttribute( 'name' );
            if ( name && sel.value ) {
                data[ name ] = sel.value;
            }
        });
        return data;
    }

    function buildParams( data ) {
        return Object.keys( data ).map( function (k) {
            return encodeURIComponent( k ) + '=' + encodeURIComponent( data[ k ] );
        }).join( '&' );
    }

    function escapeHtml( text ) {
        if ( ! text ) return '';
        var div = document.createElement( 'div' );
        div.textContent = text;
        return div.innerHTML;
    }

    function buildPlaceholderText( resource ) {
        if ( resource.activity_types && resource.activity_types.length > 0 ) return resource.activity_types[ 0 ];
        if ( resource.duration_names && resource.duration_names.length > 0 ) return resource.duration_names[ 0 ];
        if ( resource.type_names && resource.type_names.length > 0 ) return resource.type_names[ 0 ];
        if ( resource.type_name ) return resource.type_name;
        if ( resource.duration_name ) return resource.duration_name;
        if ( resource.org_name ) {
            var words = ( resource.org_name || '' ).trim().split( /\s+/ );
            var first = words[ 0 ] || '';
            var second = words[ 1 ] || '';
            return ( first.charAt( 0 ) + ( second ? second.charAt( 0 ) : first.charAt( 1 ) || '' ) ).toUpperCase();
        }
        return '—';
    }

    function durationTypePillHtml( slug, fullName ) {
        var parts = slug && durationPillParts[ slug ];
        if ( parts && parts.slot && parts.time ) {
            return (
                '<span class="resource-card__pill resource-card__pill--type resource-card__pill--duration">' +
                '<span class="resource-card__pill-slot">' + escapeHtml( parts.slot ) + '</span>' +
                '<span class="resource-card__pill-time">' + escapeHtml( parts.time ) + '</span>' +
                '</span>'
            );
        }
        return '<span class="resource-card__pill resource-card__pill--type">' + escapeHtml( fullName ) + '</span>';
    }

    function themePillClass( themeSlug ) {
        if ( ! themeSlug ) return 'resource-card__pill--theme';
        var slug = themeSlug.toLowerCase();
        if ( [ 'safe', 'smart', 'creative', 'responsible', 'future' ].indexOf( slug ) !== -1 ) {
            return 'resource-card__pill resource-card__pill--theme resource-card__pill--' + slug;
        }
        return 'resource-card__pill resource-card__pill--theme';
    }

    function renderCard( resource ) {
        var isExternal = !! ( resource.external_url && postType === 'featured_resource' );
        var cardClass = isExternal ? 'resource-card resource-card--external fade-up' : 'resource-card resource-card--download fade-up';
        var linkHref = isExternal ? ( resource.external_url || resource.permalink ) : resource.permalink;
        var linkTarget = isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
        var placeholderText = buildPlaceholderText( resource );
        var hasOverlay = !!( ( resource.duration_names && resource.duration_names.length ) || ( resource.type_names && resource.type_names.length ) || resource.type_name || resource.duration_name || resource.theme_name || resource.org_name );

        var imgHtml = resource.thumbnail
            ? '<img src="' + escapeHtml( resource.thumbnail ) + '" class="resource-card__image" alt="" aria-hidden="true" />'
            : '<div class="resource-card__image-placeholder" aria-hidden="true"><span class="resource-card__image-placeholder-text">' + escapeHtml( placeholderText ) + '</span></div>';

        var overlayHtml = '';
        if ( hasOverlay ) {
            overlayHtml = '<div class="resource-card__image-overlay" aria-hidden="true"><div class="resource-card__image-top">';
            var slotLabels = ( resource.duration_names && resource.duration_names.length ) ? resource.duration_names : ( resource.type_names && resource.type_names.length ? resource.type_names : [] );
            var slotSlugs = ( resource.duration_slugs && resource.duration_slugs.length ) ? resource.duration_slugs : [];
            if ( slotLabels.length > 0 ) {
                slotLabels.forEach( function ( name, i ) {
                    var slug = slotSlugs[ i ] || '';
                    overlayHtml += durationTypePillHtml( slug, name );
                } );
            } else if ( resource.type_name || resource.duration_name ) {
                var fallbackSlug = ( resource.duration_slugs && resource.duration_slugs[ 0 ] ) ? resource.duration_slugs[ 0 ] : '';
                overlayHtml += durationTypePillHtml( fallbackSlug, resource.duration_name || resource.type_name );
            }
            if ( resource.theme_name ) {
                overlayHtml += '<span class="' + themePillClass( resource.theme_slug ) + '">' + escapeHtml( resource.theme_name ) + '</span>';
            }
            if ( resource.org_name && isExternal ) {
                overlayHtml += '<span class="resource-card__pill resource-card__pill--org">' + escapeHtml( resource.org_name ) + '</span>';
            }
            overlayHtml += '</div></div>';
        }

        var actionHtml = '';
        if ( isExternal ) {
            actionHtml = '<a href="' + escapeHtml( resource.external_url || resource.permalink ) + '" class="resource-card__link" target="_blank" rel="noopener noreferrer">' + ( resource.org_name ? escapeHtml( resource.org_name ) + ' →' : 'Visit →' ) + '</a>';
        } else if ( resource.download_url ) {
            actionHtml = '<a href="' + escapeHtml( resource.download_url ) + '" class="resource-card__link resource-download-link" data-resource-id="' + escapeHtml( String( resource.id ) ) + '" download target="_blank" rel="noopener">' + escapeHtml( resource.download_label || 'Download' ) + ' →</a>';
        } else {
            actionHtml = '<a href="' + escapeHtml( resource.permalink ) + '" class="resource-card__link">View resource →</a>';
        }
        actionHtml += '<button type="button" class="resource-bookmark-btn" data-resource-id="' + escapeHtml( String( resource.id ) ) + '" data-resource-title="' + escapeHtml( resource.title ) + '" data-resource-url="' + escapeHtml( resource.permalink ) + '" aria-pressed="false" aria-label="Save resource">Save</button>';

        // Excerpt is escaped for XSS safety; any HTML in excerpts will appear as literal tags.
        var excerptHtml = resource.excerpt
            ? '<p class="resource-card__excerpt">' + escapeHtml( resource.excerpt ) + '</p>'
            : '';

        return '<article class="' + cardClass + '">' +
            '<a href="' + escapeHtml( linkHref ) + '" class="resource-card__image-link"' + linkTarget + '>' +
            imgHtml + overlayHtml +
            '</a>' +
            '<div class="resource-card__body">' +
            '<h2 class="resource-card__title"><a href="' + escapeHtml( linkHref ) + '"' + linkTarget + '>' + escapeHtml( resource.title ) + '</a></h2>' +
            excerptHtml +
            '<p class="resource-card__action">' + actionHtml + '</p>' +
            '</div></article>';
    }

    function updateUrl( params ) {
        var query = buildParams( params );
        var url = baseUrl.split( '?' )[ 0 ];
        if ( query ) {
            url = url + ( url.indexOf( '?' ) !== -1 ? '&' : '?' ) + query;
        }
        window.history.replaceState( { filters: params }, '', url );
    }

    function updateGrid( resources ) {
        var html = resources.map( renderCard ).join( '' );
        function applyRender() {
            grid.innerHTML = html;
            document.dispatchEvent(new CustomEvent('aiad:resourcesRendered'));
            // Fade-up: trigger reflow for animation if CSS uses opacity/transform
            grid.offsetHeight;
            var cards = grid.querySelectorAll( '.resource-card' );
            cards.forEach( function ( card, i ) {
                card.style.animationDelay = ( i % 6 ) * 0.05 + 's';
            });
        }
        if (document.startViewTransition && window.matchMedia && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.startViewTransition(applyRender);
        } else {
            applyRender();
        }
    }

    function applyFilterCounts( filterCounts ) {
        if ( ! filterCounts ) return;
        var selectMap = {
            resource_principle: 'principle',
            resource_duration: 'duration',
            activity_type: 'activity_type',
            key_stage: 'key_stage'
        };
        Object.keys( selectMap ).forEach( function ( tax ) {
            var name = selectMap[ tax ];
            var sel = form.querySelector( 'select[name="' + name + '"]' );
            if ( ! sel || ! filterCounts[ tax ] ) return;
            var counts = filterCounts[ tax ];
            Array.prototype.forEach.call( sel.options, function ( opt ) {
                var slug = opt.value;
                var count = slug ? ( counts[ slug ] !== undefined ? counts[ slug ] : -1 ) : -1;
                var label = opt.text.replace( /\s*\(\d+\)\s*$/, '' );
                if ( count >= 0 ) {
                    opt.textContent = label + ' (' + count + ')';
                    opt.disabled = count === 0;
                    opt.style.opacity = count === 0 ? '0.5' : '1';
                }
            });
        });
    }

    function showLoading( show ) {
        if ( loadingEl ) loadingEl.style.display = show ? 'block' : 'none';
        if ( grid ) grid.style.opacity = show ? '0.5' : '1';
    }

    function runFilter() {
        // Fallback for stale/missing localized script data on live caches:
        // submit as normal GET request so server-side archive filtering still works.
        if ( ! canUseAjax ) {
            form.submit();
            return;
        }

        var data = getFilterValues();
        showLoading( true );
        if ( emptyMessage ) emptyMessage.style.display = 'none';

        fetch( ajaxConfig.url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: buildParams( data )
        })
            .then( function ( res ) { return res.json(); } )
            .then( function ( json ) {
                if ( ! json || ! json.success || ! json.data ) {
                    // Stale cached nonce on live can cause AJAX to fail even when config exists.
                    // Fall back to server-rendered filtering so users are never stuck.
                    form.submit();
                    return;
                }
                showLoading( false );
                var resources = json.data.resources || [];
                updateGrid( resources );
                applyFilterCounts( json.data.filter_counts );
                var params = {};
                form.querySelectorAll( 'select[data-filter="true"]' ).forEach( function ( sel ) {
                    if ( sel.name && sel.value ) params[ sel.name ] = sel.value;
                });
                updateUrl( params );
                if ( emptyMessage ) {
                    emptyMessage.style.display = resources.length === 0 ? 'block' : 'none';
                }
            })
            .catch( function () {
                // Network/proxy/CDN edge failures should not block filtering.
                form.submit();
            });
    }

    form.querySelectorAll( 'select[data-filter="true"]' ).forEach( function ( sel ) {
        sel.addEventListener( 'change', runFilter );
    });

    // Clear filters link
    var clearLink = document.querySelector( '.resource-filters-clear' );
    if ( clearLink ) {
        clearLink.addEventListener( 'click', function ( e ) {
            e.preventDefault();
            var clearHref = clearLink.getAttribute( 'href' );
            window.location.href = clearHref || baseUrl.split( '?' )[ 0 ];
        });
    }

    // Popstate (back/forward)
    window.addEventListener( 'popstate', function () {
        var params = new URLSearchParams( window.location.search );
        form.querySelectorAll( 'select[data-filter="true"]' ).forEach( function ( sel ) {
            if ( sel.name && params.has( sel.name ) ) {
                sel.value = params.get( sel.name );
            }
        });
        runFilter();
    });
})();
