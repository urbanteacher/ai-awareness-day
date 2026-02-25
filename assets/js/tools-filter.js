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
})();
