/**
 * The AI Literacy Periodic Table (inc/literacy-table.php).
 *
 * Without this script every tile is a link to its definition in the list below the table. With it, choosing a tile
 * shows the element in the detail panel; the family buttons and the search box fade out the other elements; Previous,
 * Next and Random element step through the table; and "Print the poster" prints it.
 */
( function () {
	'use strict';

	function init( root ) {
		var dataEl = root.querySelector( '[data-alpt-data]' );
		if ( ! dataEl ) {
			return;
		}
		var data;
		try {
			data = JSON.parse( dataEl.textContent );
		} catch ( e ) {
			return;
		}

		var elements = data.elements; // [ sym, name, family, def, id ]
		var families = data.families;
		var tiles = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-el]' ) );
		var panel = root.querySelector( '[data-alpt-panel]' );
		var outs = {};
		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-out]' ), function ( el ) {
			outs[ el.getAttribute( 'data-alpt-out' ) ] = el;
		} );
		var search = root.querySelector( '[data-alpt-search]' );
		var status = root.querySelector( '[data-alpt-status]' );
		var familyButtons = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-family]' ) );
		var narrow = window.matchMedia( '(max-width: 1199px)' );
		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		var current = 0;
		var family = '';

		document.documentElement.classList.add( 'alpt-js' );
		root.querySelector( '[data-alpt-controls]' ).hidden = false;
		root.querySelector( '[data-alpt-nav]' ).hidden = false;

		function setText( key, text ) {
			if ( outs[ key ] ) {
				outs[ key ].textContent = text;
			}
		}

		function show( index, opts ) {
			opts = opts || {};
			var el = elements[ index ];
			if ( ! el ) {
				return;
			}
			current = index;
			setText( 'n', String( index + 1 ) );
			setText( 'n2', String( index + 1 ) );
			setText( 'sym', el[ 0 ] );
			setText( 'name', el[ 1 ] );
			setText( 'title', el[ 1 ] );
			setText( 'family', families[ el[ 2 ] ] || '' );
			setText( 'def', el[ 3 ] );
			panel.className = panel.className.replace( /\balpt-fam--\S+/g, '' ).trim() + ' alpt-fam--' + el[ 2 ];

			tiles.forEach( function ( tile, i ) {
				if ( i === index ) {
					tile.setAttribute( 'aria-current', 'true' );
				} else {
					tile.removeAttribute( 'aria-current' );
				}
			} );

			// The address names the element for sharing (#element-pv). Nothing on the page has that id, so opening the
			// link doesn't jump down to the list of definitions.
			if ( opts.updateHash && window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', '#element-' + el[ 0 ].toLowerCase() );
			}

			// On narrower screens the panel sits above the tiles, so bring it into view.
			if ( opts.reveal && narrow.matches ) {
				var rect = panel.getBoundingClientRect();
				if ( rect.top < 80 || rect.top > window.innerHeight * 0.5 ) {
					panel.scrollIntoView( { behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'start' } );
				}
			}
		}

		function matches( el, query ) {
			if ( family && el[ 2 ] !== family ) {
				return false;
			}
			if ( ! query ) {
				return true;
			}
			return ( el[ 0 ] + ' ' + el[ 1 ] + ' ' + el[ 3 ] ).toLowerCase().indexOf( query ) !== -1;
		}

		function applyFilter() {
			var query = search ? search.value.trim().toLowerCase() : '';
			var shown = 0;
			var firstShown = -1;
			elements.forEach( function ( el, i ) {
				var on = matches( el, query );
				tiles[ i ].classList.toggle( 'is-dim', ! on );
				if ( on ) {
					shown++;
					if ( firstShown < 0 ) {
						firstShown = i;
					}
				}
			} );

			familyButtons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-alpt-family' ) === family ? 'true' : 'false' );
			} );

			if ( ! family && ! query ) {
				status.textContent = '';
			} else if ( ! shown ) {
				status.textContent = 'No elements match. Try another word, or choose All families.';
			} else {
				status.textContent = 'Showing ' + shown + ' of ' + elements.length + ( family ? ': ' + families[ family ] : '' ) + ( query ? ', matching "' + search.value.trim() + '"' : '' ) + '.';
			}

			// Keep the panel on an element that is still showing.
			if ( shown && ! matches( elements[ current ], query ) ) {
				show( firstShown );
			}
		}

		root.addEventListener( 'click', function ( event ) {
			var tile = event.target.closest( '[data-alpt-el]' );
			if ( tile ) {
				event.preventDefault();
				show( parseInt( tile.getAttribute( 'data-alpt-el' ), 10 ), { updateHash: true, reveal: true } );
				return;
			}
			var step = event.target.closest( '[data-alpt-step]' );
			if ( step ) {
				var dir = parseInt( step.getAttribute( 'data-alpt-step' ), 10 );
				var query = search ? search.value.trim().toLowerCase() : '';
				// Step through the elements that are showing.
				for ( var n = 1; n <= elements.length; n++ ) {
					var next = ( ( ( current + dir * n ) % elements.length ) + elements.length ) % elements.length;
					if ( matches( elements[ next ], query ) ) {
						show( next, { updateHash: true } );
						break;
					}
				}
				return;
			}
			if ( event.target.closest( '[data-alpt-random]' ) ) {
				var q = search ? search.value.trim().toLowerCase() : '';
				var pool = [];
				elements.forEach( function ( el, i ) {
					if ( i !== current && matches( el, q ) ) {
						pool.push( i );
					}
				} );
				if ( pool.length ) {
					show( pool[ Math.floor( Math.random() * pool.length ) ], { updateHash: true } );
				}
				return;
			}
			var button = event.target.closest( '[data-alpt-family]' );
			if ( button ) {
				var picked = button.getAttribute( 'data-alpt-family' );
				family = ( picked && picked === family ) ? '' : picked;
				applyFilter();
			}
		} );

		if ( search ) {
			search.addEventListener( 'input', applyFilter );
		}

		// Arrow keys move between tiles in reading order.
		root.addEventListener( 'keydown', function ( event ) {
			var tile = event.target.closest( '[data-alpt-el]' );
			if ( ! tile || ( event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' ) ) {
				return;
			}
			var i = parseInt( tile.getAttribute( 'data-alpt-el' ), 10 ) + ( event.key === 'ArrowRight' ? 1 : -1 );
			if ( tiles[ i ] ) {
				event.preventDefault();
				tiles[ i ].focus();
			}
		} );

		// A shared link to an element (#element-pv) opens it in the panel, with the table in view. A link to its
		// definition (#alpt-pv) opens it in the panel too, and the browser shows the definition it points at.
		function fromHash() {
			var hash = window.location.hash.slice( 1 ).toLowerCase();
			var shared = hash.indexOf( 'element-' ) === 0;
			for ( var i = 0; i < elements.length; i++ ) {
				if ( elements[ i ][ 4 ] === hash || ( shared && 'element-' + elements[ i ][ 0 ].toLowerCase() === hash ) ) {
					show( i );
					if ( shared ) {
						// Again once the page has loaded, in case images or fonts moved it.
						panel.scrollIntoView( { block: 'start' } );
						window.addEventListener( 'load', function () {
							panel.scrollIntoView( { block: 'start' } );
						}, { once: true } );
					}
					return true;
				}
			}
			return false;
		}
		if ( ! fromHash() ) {
			show( 0 );
		}
	}

	function ready() {
		Array.prototype.forEach.call( document.querySelectorAll( '[data-alpt]' ), init );

		// "Print the poster" in the introduction.
		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( 'a[href="#alpt-print"]' );
			if ( link ) {
				event.preventDefault();
				window.print();
			}
		} );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', ready );
	} else {
		ready();
	}
}() );
