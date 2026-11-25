/**
 * Find Santa: a riddle a day from 1 to 25 December on the AI Literacy Periodic Table's page
 * (modules/literacy-table/santa.php; styles in assets/css/pages/ai-literacy-periodic-table.css).
 *
 * - On 1 to 25 December (UK time, on the reader's clock, so a cached page still changes at midnight) the Santa card
 *   takes the word of the week's place. Outside those days nothing changes.
 * - The table works as normal: opening a word's card is free. In each card, "Is Santa here?" uses one of three guesses.
 *   A wrong guess says warm (Santa is in that word's family) or cold. The right word's card shows Santa.
 * - Progress and the streak are kept in this browser only (localStorage), per day.
 * - Show to class puts the riddle alone on the screen, with a Hint button that names Santa's family.
 *
 * The table's script (literacy-table.js) announces each element it shows with an "alpt:show" event.
 */
( function () {
	'use strict';

	function start() {
		var card = document.querySelector( '[data-alpt-santa]' );
		var cfgEl = card && card.querySelector( '[data-alpt-santa-data]' );
		var tableEl = document.querySelector( '[data-alpt-data]' );
		if ( ! cfgEl || ! tableEl ) {
			return;
		}
		var cfg, table;
		try {
			cfg = JSON.parse( cfgEl.textContent );
			table = JSON.parse( tableEl.textContent );
		} catch ( e ) {
			return;
		}
		var elements = table.elements || []; // [ sym, name, family, def, ... ]
		var families = table.families || {};

		// Today in the UK, or ?santa-day=7 for an editor checking a day.
		function londonToday() {
			try {
				var parts = new Intl.DateTimeFormat( 'en-GB', { timeZone: 'Europe/London', year: 'numeric', month: 'numeric', day: 'numeric' } ).formatToParts( new Date() );
				var o = {};
				parts.forEach( function ( p ) { o[ p.type ] = parseInt( p.value, 10 ); } );
				return o;
			} catch ( e ) {
				var d = new Date();
				return { year: d.getFullYear(), month: d.getMonth() + 1, day: d.getDate() };
			}
		}
		var dayNum = 0;
		var preview = cfg.preview && /[?&]santa-day=(\d+)/.exec( window.location.search );
		if ( preview ) {
			dayNum = parseInt( preview[ 1 ], 10 );
		} else {
			var t = londonToday();
			if ( t.year === cfg.year && 12 === t.month ) {
				dayNum = t.day;
			}
		}
		var today = null;
		( cfg.days || [] ).forEach( function ( d ) {
			if ( d[ 0 ] === dayNum ) {
				today = { day: d[ 0 ], riddle: d[ 1 ], sym: decode( d[ 2 ] ) };
			}
		} );
		if ( ! today ) {
			return;
		}
		var answer = -1;
		elements.forEach( function ( e, i ) {
			if ( e[ 0 ] === today.sym ) {
				answer = i;
			}
		} );
		if ( answer < 0 ) {
			return;
		}
		var ans = elements[ answer ];

		function decode( s ) {
			try {
				return window.atob( s ).split( '' ).reverse().join( '' );
			} catch ( e ) {
				return '';
			}
		}

		// ---- Saved progress: { days: { "7": { tried: [ "Pr" ], won: true, over: true } } } ----
		var KEY = 'aiad-santa-' + cfg.year;
		var store = { days: {} };
		try {
			store = JSON.parse( window.localStorage.getItem( KEY ) || '' ) || store;
		} catch ( e ) {}
		store.days = store.days || {};
		var state = store.days[ today.day ] || { tried: [], won: false, over: false };
		store.days[ today.day ] = state;
		function save() {
			try {
				window.localStorage.setItem( KEY, JSON.stringify( store ) );
			} catch ( e ) {}
		}
		function left() {
			return Math.max( 0, 3 - state.tried.length );
		}
		// Days found in a row, up to today (or yesterday, until today is found).
		function streak() {
			var n = 0;
			for ( var d = state.won ? today.day : today.day - 1; d >= 1; d-- ) {
				if ( store.days[ d ] && store.days[ d ].won ) {
					n++;
				} else {
					break;
				}
			}
			return n;
		}

		// ---- The card at the top ----
		var week = document.querySelector( '.alpt-eotw' );
		if ( week && ! week.closest( '.alpt-away' ) ) {
			week.hidden = true;
		}
		card.hidden = false;
		var q = function ( sel ) { return card.querySelector( sel ); };
		q( '[data-alpt-santa-day]' ).textContent = 'Day ' + today.day + ' of 25';
		q( '[data-alpt-santa-riddle]' ).textContent = today.riddle;
		var hintOut = q( '[data-alpt-santa-hint]' );

		function marks() {
			var html = '';
			for ( var i = 0; i < 3; i++ ) {
				html += '<i class="alpt-santa-mark' + ( i >= left() ? ' is-used' : '' ) + '"></i>';
			}
			return html;
		}
		function warmth( sym ) {
			var e = elements[ indexOf( sym ) ];
			if ( ! e ) {
				return '';
			}
			return e[ 2 ] === ans[ 2 ] ? 'Warm: he’s in ' + families[ e[ 2 ] ] + ', just not this card.' : 'Cold: he isn’t in ' + families[ e[ 2 ] ] + '.';
		}
		function indexOf( sym ) {
			for ( var i = 0; i < elements.length; i++ ) {
				if ( elements[ i ][ 0 ] === sym ) {
					return i;
				}
			}
			return -1;
		}
		function renderTop() {
			var s = streak();
			q( '[data-alpt-santa-streak]' ).textContent = s > 0 ? s + '-day streak' : '';
			q( '[data-alpt-santa-marks]' ).innerHTML = marks();
			q( '[data-alpt-santa-marks]' ).setAttribute( 'aria-label', left() + ' of 3 guesses left' );
			q( '[data-alpt-santa-marks]' ).setAttribute( 'role', 'img' );
			hintOut.className = 'alpt-santa__hint';
			if ( state.won ) {
				hintOut.classList.add( 'is-warm' );
				hintOut.textContent = 'Found in ' + ans[ 1 ] + '. A new riddle tomorrow.';
			} else if ( state.over ) {
				hintOut.textContent = 'Santa was hiding in ' + ans[ 1 ] + '. A new riddle tomorrow.';
			} else if ( state.tried.length ) {
				var last = state.tried[ state.tried.length - 1 ];
				var e = elements[ indexOf( last ) ];
				hintOut.classList.add( e && e[ 2 ] === ans[ 2 ] ? 'is-warm' : 'is-cold' );
				hintOut.textContent = warmth( last );
			} else {
				hintOut.textContent = 'Open a word’s card and press “Is Santa here?”';
			}
		}

		// Hint: names Santa's family, in its colours.
		function famHint( out, button ) {
			var open = out.hidden;
			out.className = out.className.replace( /\balpt-fam--\S+/g, '' ).trim() + ' alpt-fam--' + ans[ 2 ];
			out.innerHTML = '';
			var small = document.createElement( 'small' );
			small.textContent = 'Hint';
			out.append( small, ' Santa is in ' + ( families[ ans[ 2 ] ] || '' ) );
			out.hidden = ! open;
			button.textContent = open ? 'Hide hint' : 'Hint';
			button.setAttribute( 'aria-expanded', String( open ) );
		}
		var hintBtn = q( '[data-alpt-santa-hint-btn]' );
		hintBtn.addEventListener( 'click', function () {
			famHint( q( '[data-alpt-santa-fam]' ), hintBtn );
		} );
		var howBtn = q( '[data-alpt-santa-how]' );
		howBtn.addEventListener( 'click', function () {
			var how = card.querySelector( '#alpt-santa-how' );
			how.hidden = ! how.hidden;
			howBtn.setAttribute( 'aria-expanded', String( ! how.hidden ) );
		} );

		// ---- Decorations: lights to the card's width, and snow that falls its height ----
		var lights = q( '[data-alpt-santa-lights]' );
		var snow = q( '[data-alpt-santa-snow]' );
		var colours = [ '#D7263D', '#F3C44F', '#63DF93', '#00BEDD', '#FA83EB' ];
		function drawLights() {
			var w = card.clientWidth;
			var n = Math.max( 8, Math.round( w / 46 ) );
			var d = 'M0 4';
			var bulbs = '';
			for ( var i = 0; i < n; i++ ) {
				var x0 = i * w / n, x1 = ( i + 1 ) * w / n, mx = ( x0 + x1 ) / 2;
				d += ' Q' + mx + ' 18 ' + x1 + ' 4';
				bulbs += '<g class="bulb" style="animation-delay:' + ( ( i % 5 ) * 0.45 ) + 's"><rect x="' + ( mx - 2.5 ) + '" y="10" width="5" height="4" fill="#8A8480"/><ellipse cx="' + mx + '" cy="19" rx="4.2" ry="6" fill="' + colours[ i % colours.length ] + '"/></g>';
			}
			lights.setAttribute( 'viewBox', '0 0 ' + w + ' 30' );
			lights.innerHTML = '<path d="' + d + '" fill="none" stroke="#5B5552" stroke-width="1.5"/>' + bulbs;
		}
		function makeSnow() {
			var h = card.clientHeight + 16;
			var html = '';
			for ( var i = 0; i < 34; i++ ) {
				html += '<i style="left:' + ( Math.random() * 100 ).toFixed( 1 ) + '%;--s:' + ( 2 + Math.random() * 4 ).toFixed( 1 ) + 'px;--o:' + ( 0.35 + Math.random() * 0.5 ).toFixed( 2 ) + ';--d:' + ( 7 + Math.random() * 8 ).toFixed( 1 ) + 's;--delay:-' + ( Math.random() * 14 ).toFixed( 1 ) + 's;--x:' + Math.round( Math.random() * 40 - 20 ) + 'px;--fall:' + h + 'px;--y:' + ( Math.random() * 100 ).toFixed( 1 ) + '%"></i>';
			}
			snow.innerHTML = html;
		}
		drawLights();
		makeSnow();
		if ( window.ResizeObserver ) {
			new window.ResizeObserver( function () {
				drawLights();
				var h = ( card.clientHeight + 16 ) + 'px';
				snow.querySelectorAll( 'i' ).forEach( function ( f ) { f.style.setProperty( '--fall', h ); } );
			} ).observe( card );
		}

		// ---- In each word's card ----
		var slot = document.querySelector( '[data-alpt-santa-slot]' );
		var panelTile = document.querySelector( '.alpt-panel__tile' );
		var santaPic = null;
		if ( panelTile ) {
			santaPic = document.createElement( 'span' );
			santaPic.className = 'alpt-panel__santa';
			santaPic.hidden = true;
			santaPic.setAttribute( 'aria-hidden', 'true' );
			santaPic.innerHTML = '<svg viewBox="0 0 120 120"><circle cx="60" cy="72" r="34" fill="#F6D5BE"/><path d="M26 74c4 30 20 40 34 40s30-10 34-40c-10 8-22 10-34 10s-24-2-34-10z" fill="#fff"/><ellipse cx="60" cy="82" rx="12" ry="5" fill="#fff"/><circle cx="48" cy="66" r="3.4" fill="#231F20"/><circle cx="72" cy="66" r="3.4" fill="#231F20"/><circle cx="60" cy="75" r="5" fill="#E58A7B"/><path d="M24 52c6-28 30-42 58-34 10 3 18 12 20 22-14-6-24-4-32 2z" fill="#C8102E"/><rect x="22" y="46" width="76" height="14" rx="7" fill="#fff"/><circle cx="104" cy="40" r="8" fill="#fff"/></svg>';
			panelTile.appendChild( santaPic );
		}
		var shownIndex = -1;

		function el( tag, cls, text ) {
			var n = document.createElement( tag );
			if ( cls ) {
				n.className = cls;
			}
			if ( null != text ) {
				n.textContent = text;
			}
			return n;
		}
		function guessesLeft() {
			var p = el( 'p', 'alpt-santa-check__left', 'Guesses left ' );
			var m = el( 'span', 'alpt-santa-check__marks' );
			m.innerHTML = marks();
			m.setAttribute( 'role', 'img' );
			m.setAttribute( 'aria-label', left() + ' of 3' );
			p.appendChild( m );
			return p;
		}
		function shareText() {
			var squares = state.tried.map( function ( s ) { return s === ans[ 0 ] ? '🎅' : '❌'; } ).join( '' );
			var s = streak();
			return 'AI Advent · Find Santa · Day ' + today.day + '\n' + squares + ( s > 1 ? '  🔥 ' + s : '' ) + '\n' + cfg.share.replace( /^https?:\/\//, '' ).replace( /\/$/, '' );
		}
		function countdown( out ) {
			var parts = londonToday();
			void parts;
			var now = new Date();
			var mid = new Date( now );
			mid.setHours( 24, 0, 0, 0 );
			var s = Math.max( 0, Math.floor( ( mid - now ) / 1000 ) );
			out.textContent = Math.floor( s / 3600 ) + 'h ' + String( Math.floor( s % 3600 / 60 ) ).padStart( 2, '0' ) + 'm';
		}
		function foundPanel() {
			var box = el( 'div', 'alpt-santa-found' );
			box.appendChild( el( 'h3', '', 'You found Santa!' ) );
			var share = el( 'div', 'alpt-santa-share' );
			share.appendChild( el( 'p', 'alpt-santa-share__label', 'Find Santa · Day ' + today.day + ' of 25' ) );
			var row = el( 'div', 'alpt-santa-share__row' );
			var sq = el( 'span', 'alpt-santa-share__sq' );
			sq.setAttribute( 'role', 'img' );
			sq.setAttribute( 'aria-label', 'Found in ' + state.tried.length + ' of 3 guesses' );
			for ( var i = 0; i < 3; i++ ) {
				var tried = state.tried[ i ];
				var k = el( 'i', 'alpt-santa-sq ' + ( ! tried ? 'alpt-santa-sq--spare' : tried === ans[ 0 ] ? 'alpt-santa-sq--found' : 'alpt-santa-sq--miss' ) );
				if ( tried === ans[ 0 ] ) {
					k.innerHTML = '<svg viewBox="0 0 60 44" aria-hidden="true"><path d="M6 34C10 14 26 2 44 6c6 2 9 8 9 14-8-5-16-3-22 3z" fill="#fff"/><rect x="2" y="30" width="44" height="11" fill="#fff" opacity=".85"/><circle cx="53" cy="22" r="6" fill="#fff"/></svg>';
				}
				sq.appendChild( k );
			}
			row.appendChild( sq );
			var s = streak();
			row.appendChild( el( 'span', 'alpt-santa-share__streak', s > 1 ? s + '-day streak' : '' ) );
			share.appendChild( row );
			share.appendChild( el( 'p', 'alpt-santa-share__result', 'Found in ' + state.tried.length + ' of 3 guesses' ) );
			share.appendChild( el( 'p', 'alpt-santa-share__url', cfg.share.replace( /^https?:\/\//, '' ).replace( /\/$/, '' ) ) );
			box.appendChild( share );
			var copy = el( 'button', 'alpt-btn alpt-btn--santa', 'Copy my result' );
			copy.type = 'button';
			copy.addEventListener( 'click', function () {
				var text = shareText();
				var done = function () { copy.textContent = 'Copied'; };
				if ( navigator.clipboard && navigator.clipboard.writeText ) {
					navigator.clipboard.writeText( text ).then( done, function () {} );
				}
			} );
			box.appendChild( copy );
			var next = el( 'p', 'alpt-santa-found__next', 'A new riddle in ' );
			var c = el( 'strong' );
			countdown( c );
			next.append( c, '.' );
			box.appendChild( next );
			return box;
		}

		function renderSlot( index ) {
			if ( ! slot ) {
				return;
			}
			shownIndex = index;
			var e = elements[ index ];
			// Straight under the definition, above Talk about it (which the table's script puts there too).
			var def = document.querySelector( '[data-alpt-panel] [data-alpt-out="def"]' );
			if ( def && def.nextElementSibling !== slot ) {
				def.after( slot );
			}
			slot.replaceChildren();
			slot.hidden = false;
			var isAns = index === answer;
			if ( santaPic ) {
				santaPic.hidden = ! ( state.over && isAns );
			}
			if ( state.over ) {
				if ( ! isAns ) {
					slot.hidden = true;
					return;
				}
				if ( state.won ) {
					slot.appendChild( foundPanel() );
				} else {
					var m = el( 'p', 'alpt-santa-check__msg', 'Santa was hiding here today.' );
					m.appendChild( el( 'small', '', 'A new riddle tomorrow.' ) );
					slot.appendChild( m );
				}
				return;
			}
			var r = el( 'p', 'alpt-santa-check__riddle' );
			r.append( el( 'b', '', 'Find Santa · Day ' + today.day ), ' ' + today.riddle );
			slot.appendChild( r );
			if ( state.tried.indexOf( e[ 0 ] ) !== -1 ) {
				var msg = el( 'p', 'alpt-santa-check__msg', 'You checked here.' );
				msg.appendChild( el( 'small', '', 'Santa isn’t in this card.' ) );
				slot.appendChild( msg );
				return;
			}
			var row = el( 'div', 'alpt-santa-check__ask' );
			var ask = el( 'button', 'alpt-btn alpt-btn--santa', 'Is Santa here?' );
			ask.type = 'button';
			ask.addEventListener( 'click', function () { check( index ); } );
			row.append( ask, guessesLeft() );
			slot.appendChild( row );
		}

		function check( index ) {
			var e = elements[ index ];
			if ( state.over || state.tried.indexOf( e[ 0 ] ) !== -1 ) {
				return;
			}
			state.tried.push( e[ 0 ] );
			if ( index === answer ) {
				state.won = true;
				state.over = true;
				save();
				renderTop();
				renderSlot( index );
				focusSlot();
				return;
			}
			if ( 0 === left() ) {
				state.over = true;
			}
			save();
			renderTop();
			slot.replaceChildren();
			var msg = el( 'p', 'alpt-santa-check__msg', 'Not here.' );
			if ( state.over ) {
				msg.appendChild( el( 'small', '', 'That was your last guess. He was hiding in ' + ans[ 1 ] + ' (' + ans[ 0 ] + ').' ) );
				slot.appendChild( msg );
				var open = el( 'a', 'alpt-btn alpt-btn--santa', 'Open ' + ans[ 1 ] );
				open.href = '#element-' + ans[ 0 ].toLowerCase();
				slot.appendChild( open );
			} else {
				msg.appendChild( el( 'small', '', warmth( e[ 0 ] ) ) );
				slot.appendChild( msg );
				slot.appendChild( guessesLeft() );
			}
			focusSlot();
		}
		function focusSlot() {
			var m = slot.querySelector( '.alpt-santa-check__msg, h3' );
			if ( m ) {
				m.setAttribute( 'tabindex', '-1' );
				m.focus( { preventScroll: true } );
			}
		}

		document.addEventListener( 'alpt:show', function ( ev ) {
			renderSlot( ev.detail.index );
		} );
		// The table may have shown its first element before this script ran.
		var symOut = document.querySelector( '[data-alpt-panel] [data-alpt-out="sym"]' );
		if ( symOut ) {
			var first = indexOf( symOut.textContent.trim() );
			if ( first >= 0 ) {
				renderSlot( first );
			}
		}

		// ---- Show to class ----
		var boardBtn = q( '[data-alpt-santa-board]' );
		var board = null;
		function openBoard() {
			if ( ! board ) {
				board = el( 'div', 'alpt-santa-board' );
				board.setAttribute( 'role', 'dialog' );
				board.setAttribute( 'aria-modal', 'true' );
				board.setAttribute( 'aria-label', 'Find Santa: today’s riddle' );
				board.appendChild( el( 'p', 'alpt-santa-board__label', 'Find Santa · Day ' + today.day + ' of 25' ) );
				board.appendChild( el( 'p', 'alpt-santa-board__q', today.riddle ) );
				var fam = el( 'p', 'alpt-santa-board__fam' );
				fam.id = 'alpt-santa-board-fam';
				fam.hidden = true;
				board.appendChild( fam );
				var bar = el( 'div', 'alpt-santa-board__bar' );
				var hb = el( 'button', 'alpt-btn alpt-btn--santa', 'Hint' );
				hb.type = 'button';
				hb.setAttribute( 'aria-expanded', 'false' );
				hb.setAttribute( 'aria-controls', 'alpt-santa-board-fam' );
				hb.addEventListener( 'click', function () { famHint( fam, hb ); } );
				var close = el( 'button', 'alpt-btn', 'Close' );
				close.type = 'button';
				close.addEventListener( 'click', closeBoard );
				bar.append( hb, close );
				board.appendChild( bar );
				board.addEventListener( 'keydown', function ( ev ) {
					if ( 'Escape' === ev.key ) {
						closeBoard();
					}
					// Keep focus on the board's two buttons.
					if ( 'Tab' === ev.key ) {
						var f = board.querySelectorAll( 'button' );
						var firstB = f[ 0 ], lastB = f[ f.length - 1 ];
						if ( ev.shiftKey && document.activeElement === firstB ) {
							ev.preventDefault();
							lastB.focus();
						} else if ( ! ev.shiftKey && document.activeElement === lastB ) {
							ev.preventDefault();
							firstB.focus();
						}
					}
				} );
				document.body.appendChild( board );
			}
			var famOut = board.querySelector( '.alpt-santa-board__fam' );
			var famBtn = board.querySelector( '[aria-controls="alpt-santa-board-fam"]' );
			famOut.hidden = true;
			famBtn.textContent = 'Hint';
			famBtn.setAttribute( 'aria-expanded', 'false' );
			board.hidden = false;
			document.documentElement.style.overflow = 'hidden';
			board.querySelector( 'button:last-child' ).focus();
		}
		function closeBoard() {
			board.hidden = true;
			document.documentElement.style.overflow = '';
			boardBtn.focus();
		}
		boardBtn.addEventListener( 'click', openBoard );

		renderTop();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', start );
	} else {
		start();
	}
}() );
