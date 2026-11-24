/**
 * Slideshow frame for the site's "Show on the board" views (a resource lesson's slides and big question, and the
 * National Conversation's big question), presented as the AI Literacy Periodic Table's Show to class is, after
 * SlideForge: on a landscape screen 768px and wider, one 1280 x 720 slide on a black surround with its controls in a
 * floating pill at the bottom centre (assets/css/components/slideshow.css).
 *
 *   AIADShow.start( el, { pill: element } )  when the view goes on the board
 *   AIADShow.stop( el )                       when it comes off
 *
 * While it is on: the pill and the pointer fade after three still seconds and come back on any movement or key (the
 * pill never fades while it has focus); B or . blanks the screen to black and any key or click brings it back; and
 * text marked data-show-fit shrinks (--t, 1 down to 0.5) until nothing scrolls.
 */
( function () {
	'use strict';

	var slideScreen = window.matchMedia ? window.matchMedia( '(min-width: 768px) and (orientation: landscape)' ) : null;
	var current = null;

	function isSlide() {
		return !! ( slideScreen && slideScreen.matches );
	}

	function fit( el ) {
		el.style.removeProperty( '--t' );
		if ( ! isSlide() ) {
			return;
		}
		var t = 1;
		while ( el.scrollHeight > el.clientHeight + 1 && t > 0.52 ) {
			t -= 0.04;
			el.style.setProperty( '--t', t.toFixed( 2 ) );
		}
	}

	function awake() {
		if ( ! current ) {
			return;
		}
		current.el.classList.remove( 'is-idle' );
		window.clearTimeout( current.timer );
		if ( isSlide() ) {
			current.timer = window.setTimeout( function () {
				if ( current ) {
					current.el.classList.add( 'is-idle' );
				}
			}, 3000 );
		}
	}

	function onKey( event ) {
		if ( ! current ) {
			return;
		}
		awake();
		var el = current.el;
		if ( el.classList.contains( 'is-blank' ) ) {
			el.classList.remove( 'is-blank' );
			event.preventDefault();
			event.stopImmediatePropagation();
			return;
		}
		var typing = event.target && /^(input|textarea|select)$/i.test( event.target.tagName );
		if ( ! typing && ( 'b' === event.key || 'B' === event.key || '.' === event.key ) && ! event.ctrlKey && ! event.metaKey && ! event.altKey ) {
			event.preventDefault();
			el.classList.add( 'is-blank' );
		}
	}

	function onClick( event ) {
		if ( current && current.el.classList.contains( 'is-blank' ) ) {
			current.el.classList.remove( 'is-blank' );
			event.preventDefault();
			event.stopPropagation();
		}
	}

	function refit() {
		if ( current ) {
			fit( current.el );
			awake();
		}
	}

	function start( el, opts ) {
		if ( current ) {
			stop( current.el );
		}
		opts = opts || {};
		current = { el: el, pill: opts.pill || null, timer: null };
		el.classList.add( 'aiad-show' );
		if ( current.pill ) {
			current.pill.classList.add( 'aiad-show__pill' );
		}
		el.addEventListener( 'pointermove', awake );
		el.addEventListener( 'pointerdown', awake );
		el.addEventListener( 'click', onClick, true );
		document.addEventListener( 'keydown', onKey, true );
		window.addEventListener( 'resize', refit );
		document.addEventListener( 'fullscreenchange', refit );
		fit( el );
		window.requestAnimationFrame( function () { fit( el ); } );
		window.setTimeout( function () { if ( current && current.el === el ) { fit( el ); } }, 300 );
		awake();
	}

	function stop( el ) {
		if ( ! current || current.el !== el ) {
			return;
		}
		window.clearTimeout( current.timer );
		el.classList.remove( 'aiad-show', 'is-idle', 'is-blank' );
		el.style.removeProperty( '--t' );
		if ( current.pill ) {
			current.pill.classList.remove( 'aiad-show__pill' );
		}
		el.removeEventListener( 'pointermove', awake );
		el.removeEventListener( 'pointerdown', awake );
		el.removeEventListener( 'click', onClick, true );
		document.removeEventListener( 'keydown', onKey, true );
		window.removeEventListener( 'resize', refit );
		document.removeEventListener( 'fullscreenchange', refit );
		current = null;
	}

	window.AIADShow = { start: start, stop: stop };
}() );
