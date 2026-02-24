/**
 * Single Timeline Entry: share button handler.
 *
 * @package AI_Awareness_Day
 */
( function () {
	'use strict';

	var shareBtn = document.querySelector( '.single-timeline-entry__share' );
	if ( ! shareBtn ) { return; }

	shareBtn.addEventListener( 'click', function () {
		var url          = shareBtn.getAttribute( 'data-url' ) || window.location.href;
		var title        = shareBtn.getAttribute( 'data-title' ) || document.title;
		var originalLabel = shareBtn.getAttribute( 'aria-label' ) || 'Share';

		if ( navigator.share && typeof navigator.share === 'function' ) {
			navigator.share( { title: title, text: title, url: url } )
				.then( function () {
					shareBtn.setAttribute( 'aria-label', 'Shared!' );
					setTimeout( function () { shareBtn.setAttribute( 'aria-label', originalLabel ); }, 2000 );
				} )
				.catch( function () {} );
		} else {
			copyToClipboard( url ).then( function ( ok ) {
				var label = ok ? 'Link copied!' : 'Copy failed';
				shareBtn.setAttribute( 'aria-label', label );
				// Also update visible text node briefly
				var textNode = Array.from( shareBtn.childNodes ).find( function ( n ) {
					return n.nodeType === Node.TEXT_NODE && n.textContent.trim();
				} );
				var original = textNode ? textNode.textContent : null;
				if ( textNode ) { textNode.textContent = ok ? ' Copied!' : ' Failed'; }
				setTimeout( function () {
					shareBtn.setAttribute( 'aria-label', originalLabel );
					if ( textNode && original ) { textNode.textContent = original; }
				}, 2000 );
			} );
		}
	} );

	function copyToClipboard( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			return navigator.clipboard.writeText( text ).then(
				function () { return true; },
				function () { return false; }
			);
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
