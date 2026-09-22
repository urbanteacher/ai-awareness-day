/**
 * Tools archive: client-side category filter.
 * No page reloads — shows/hides .tools-group elements by data-category.
 *
 * @package AI_Awareness_Day
 */
(function () {
	'use strict';

	var filterBtns = document.querySelectorAll( '.tools-filter__btn' );
	var groups     = document.querySelectorAll( '.tools-group' );

	if ( ! filterBtns.length || ! groups.length ) {
		return;
	}

	filterBtns.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var filter = btn.getAttribute( 'data-filter' ) || 'all';

			// Keep ?category= in step, so a filtered view can be shared or reloaded.
			if ( window.history && window.history.replaceState ) {
				var url = new URL( window.location.href );
				if ( filter === 'all' ) {
					url.searchParams.delete( 'category' );
				} else {
					url.searchParams.set( 'category', filter );
				}
				window.history.replaceState( null, '', url );
			}

			// Update active state
			filterBtns.forEach( function ( b ) {
				b.classList.remove( 'tools-filter__btn--active' );
				b.setAttribute( 'aria-pressed', 'false' );
			} );
			btn.classList.add( 'tools-filter__btn--active' );
			btn.setAttribute( 'aria-pressed', 'true' );

			// Show / hide groups
			groups.forEach( function ( group ) {
				var cat = group.getAttribute( 'data-category' );
				if ( filter === 'all' || cat === filter ) {
					group.style.display = '';
				} else {
					group.style.display = 'none';
				}
			} );
		} );
	} );

	// The homepage's category chips link here as ?category=<slug>. Open on that
	// category by pressing its button, so the view is exactly what a click gives.
	var wanted = new URLSearchParams( window.location.search ).get( 'category' );
	if ( wanted ) {
		filterBtns.forEach( function ( btn ) {
			if ( btn.getAttribute( 'data-filter' ) === wanted ) {
				btn.click();
			}
		} );
	}
})();
