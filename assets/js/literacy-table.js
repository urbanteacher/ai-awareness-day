/**
 * The AI Literacy Periodic Table (inc/literacy-table.php).
 *
 * Without this script every tile is a link to its definition in the list below the table. With it, choosing a tile
 * shows the element in the detail panel; the family buttons and the search box filter the elements; Previous,
 * Next step through the table; Connections lights up the elements linked to the one in the panel; the compound challenge lets students combine elements to discover
 * the compounds (saved in this browser); and "Print the poster" prints it.
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
		// Shows a part of the table if it is on the page. Editable pages can drop parts, and one missing part must
		// not stop the rest of the table working.
		function unhide( selector ) {
			var node = root.querySelector( selector );
			if ( node ) {
				node.hidden = false;
			}
		}

		// Tile names: a whole word moves to the next line rather than splitting ("Data / Governance", not
		// "Data Gover- / nance"). Each name carries soft hyphens from the server; here they are kept only in words too
		// wide for the tile on their own, and the rest are taken out. Done again whenever the tiles change size.
		var SHY = '\u00AD';
		var measure = document.createElement( 'canvas' ).getContext( '2d' );
		function fitNames() {
			tiles.forEach( function ( tile ) {
				var nameEl = tile.querySelector( '.alpt-tile__name' );
				if ( ! nameEl ) {
					return;
				}
				if ( ! nameEl.hasAttribute( 'data-alpt-name' ) ) {
					nameEl.setAttribute( 'data-alpt-name', nameEl.textContent );
				}
				var width = nameEl.clientWidth;
				if ( ! width ) {
					return;
				}
				var style = window.getComputedStyle( nameEl );
				measure.font = style.fontWeight + ' ' + style.fontSize + ' ' + style.fontFamily;
				var fitted = nameEl.getAttribute( 'data-alpt-name' ).split( ' ' ).map( function ( word ) {
					// Hard hyphens (Human-in-the-Loop) are break points already, so each part is judged on its own.
					return word.split( '-' ).map( function ( part ) {
						var plain = part.split( SHY ).join( '' );
						// On a line of its own the word needs no hyphen, so it is measured as it is.
						return measure.measureText( plain ).width <= width + 0.5 ? plain : part;
					} ).join( '-' );
				} ).join( ' ' );
				if ( nameEl.textContent !== fitted ) {
					nameEl.textContent = fitted;
				}
			} );
		}
		var gridEl = root.querySelector( '.alpt-grid' );
		if ( gridEl ) {
			var fitTimer = null;
			var refit = function () {
				window.clearTimeout( fitTimer );
				fitTimer = window.setTimeout( fitNames, 60 );
			};
			if ( 'ResizeObserver' in window ) {
				new window.ResizeObserver( refit ).observe( gridEl );
			} else {
				window.addEventListener( 'resize', refit );
			}
			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( fitNames );
			}
			fitNames();
		}
		var panel = root.querySelector( '[data-alpt-panel]' );
		var outs = {};
		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-out]' ), function ( el ) {
			outs[ el.getAttribute( 'data-alpt-out' ) ] = el;
		} );
		var search = root.querySelector( '[data-alpt-search]' );
		var status = root.querySelector( '[data-alpt-status]' );
		var familyButtons = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-family]' ) );
		var narrow = window.matchMedia( '(max-width: 1199px)' );
		// Tablets show the panel as a drawer along the bottom of the screen, so the tiles stay where they are.
		var tablet = window.matchMedia( '(min-width: 768px) and (max-width: 1199px) and (min-height: 600px)' );
		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		var current = 0;
		var family = '';
		var themeFilter = '';
		var themes = data.themes || {};
		var themeButtons = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-theme-filter]' ) );
		var viewButtons = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-view]' ) );
		var eraFilter = '';
		var eras = data.eras || {};
		var eraButtons = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-era-filter]' ) );
		var yearRange = root.querySelector( '[data-alpt-year-range]' );
		var yearOut = root.querySelector( '[data-alpt-year-out]' );
		var playButton = root.querySelector( '[data-alpt-play]' );
		var playText = root.querySelector( '[data-alpt-play-text]' );
		var yearSteps = yearRange ? yearRange.getAttribute( 'data-years' ).split( ',' ).map( Number ) : [];
		var playTimer = null;
		var linksOn = false;
		var links = data.links || {};
		var symIndex = {};
		elements.forEach( function ( el, i ) {
			symIndex[ el[ 0 ] ] = i;
		} );
		initBuzzwordQuiz( root, elements, symIndex, data.quiz );
		var linksOut = root.querySelector( '[data-alpt-links-out]' );
		var linksList = root.querySelector( '[data-alpt-links-list]' );
		var linksCompounds = root.querySelector( '[data-alpt-links-compounds]' );
		// Theme pages drop empty paragraphs and divs (aiad_editable_page_render_block()), so make any that went.
		function ensure( el, tag, className, parent ) {
			if ( el ) {
				return el;
			}
			el = document.createElement( tag );
			el.className = className;
			parent.appendChild( el );
			return el;
		}
		linksList = ensure( linksList, 'div', 'alpt-panel__links-list', linksOut );
		linksCompounds = ensure( linksCompounds, 'p', 'alpt-panel__links-compounds', linksOut );

		document.documentElement.classList.add( 'alpt-js' );

		var clearSearch = root.querySelector( '[data-alpt-clear-search]' );
		var actions = document.querySelector( '.alpt-hero .alpt-actions' );
		// Existing pages may have a saved copy of the original introduction.
		if ( actions && ! actions.querySelector( '[href="#alpt-explore"]' ) ) {
			var explore = document.createElement( 'a' );
			explore.href = '#alpt-explore';
			explore.className = 'alpt-btn alpt-btn--ink';
			explore.textContent = 'Explore the table';
			actions.prepend( explore );
		}
		if ( actions ) {
			actions.querySelector( '[href="#alpt-explore"]' )?.addEventListener( 'click', function ( event ) {
				event.preventDefault();
				root.scrollIntoView( { block: 'start' } );
				search.focus( { preventScroll: true } );
			} );
		}
		panel.tabIndex = -1;
		root.querySelector( '[data-alpt-dismiss]' )?.addEventListener( 'click', function () {
			root.classList.remove( 'is-detail-open' );
			tiles[ current ].focus();
		} );
		// Esc closes the panel on narrower screens too, like Back to elements (the board handles its own Esc).
		root.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' !== event.key || event.target.closest( '.alpt-board' ) ) {
				return;
			}
			if ( narrow.matches && root.classList.contains( 'is-detail-open' ) ) {
				root.classList.remove( 'is-detail-open' );
				tiles[ current ].focus();
			} else if ( ! narrow.matches && panel.contains( event.target ) && tiles[ current ] ) {
				// On wide screens Esc in the panel goes back to the tile it shows.
				tiles[ current ].focus();
			}
		} );
		// On tablets the panel is a drawer over the bottom of the screen: a tile reached with Tab is scrolled
		// up so it isn't hidden behind it.
		root.addEventListener( 'focusin', function ( event ) {
			var tile = event.target.closest ? event.target.closest( '[data-alpt-el]' ) : null;
			if ( ! tile || ! tablet.matches || ! root.classList.contains( 'is-detail-open' ) ) {
				return;
			}
			// After the browser's own scroll to the focused tile.
			window.requestAnimationFrame( function () {
				var drawerTop = window.innerHeight - panel.offsetHeight;
				var box = tile.getBoundingClientRect();
				if ( box.bottom > drawerTop - 8 ) {
					window.scrollBy( { top: box.bottom - drawerTop + 24, behavior: 'instant' } );
				}
			} );
		} );
		unhide( '[data-alpt-controls]' );
		unhide( '[data-alpt-nav]' );

		function setText( key, text ) {
			if ( outs[ key ] ) {
				outs[ key ].textContent = text;
			}
		}

		// A short, visually hidden line for screen readers: the element's name and definition, not the whole panel.
		// (Named panelSay: the compound challenge has its own say() for its messages.)
		var panelSay = document.createElement( 'div' );
		panelSay.className = 'screen-reader-text';
		panelSay.setAttribute( 'aria-live', 'polite' );
		panel.appendChild( panelSay );
		var shownOnce = false;
		// Elements that can lead to a safeguarding conversation carry a note, in the panel and on the board.
		var safeSyms = data.safe || [];
		var SAFE_NOTE = 'This topic can lead to conversations about safeguarding. Follow your school\'s procedures, and make sure students know who they can talk to, including Childline on 0800 1111. If a child is in immediate danger, call 999.';
		var safeNote = document.createElement( 'p' );
		safeNote.className = 'alpt-panel__safe';
		safeNote.textContent = SAFE_NOTE;
		safeNote.hidden = true;
		// An element added from a reader's suggestion thanks them by the credit line they chose.
		var credits = data.credits || {};
		var creditNote = document.createElement( 'p' );
		creditNote.className = 'alpt-panel__credit';
		creditNote.hidden = true;
		var defOut = root.querySelector( '[data-alpt-out="def"]' );
		if ( defOut ) {
			var notesAfter = defOut.nextElementSibling && defOut.nextElementSibling.classList.contains( 'alpt-panel__talk' ) ? defOut.nextElementSibling : defOut;
			notesAfter.after( safeNote );
			notesAfter.after( creditNote );
		}

		// An element with a picture shows it in place of the big tile, with its symbol as a badge on it.
		var pics = data.pics || {};
		var panelTile = panel.querySelector( '.alpt-panel__tile' );
		var picOut = panel.querySelector( '[data-alpt-pic-out]' );
		var picShown = null;
		var picCache = {};
		var picBase = data.picBase || '';
		// The pictures are fetched only when an element is opened (then cached), so the page doesn't carry all of them.
		function loadPicture( sym ) {
			if ( ! picCache[ sym ] ) {
				picCache[ sym ] = window.fetch( picBase.replace( '{sym}', encodeURIComponent( sym.toLowerCase() ) ) ).then( function ( response ) {
					return response.ok ? response.text() : '';
				} ).then( function ( text ) {
					var svg = text ? new window.DOMParser().parseFromString( text, 'image/svg+xml' ).documentElement : null;
					return svg && 'svg' === svg.nodeName ? svg : null;
				} ).catch( function () {
					delete picCache[ sym ];
					return null;
				} );
			}
			return picCache[ sym ];
		}
		function showPicture( sym ) {
			if ( ! panelTile || ! picOut || picShown === sym ) {
				return;
			}
			picShown = sym;
			var has = !! picBase && Object.prototype.hasOwnProperty.call( pics, sym );
			panelTile.classList.toggle( 'has-pic', has );
			picOut.hidden = ! has;
			picOut.replaceChildren();
			if ( ! has ) {
				picOut.removeAttribute( 'role' );
				picOut.removeAttribute( 'aria-label' );
				return;
			}
			picOut.setAttribute( 'role', 'img' );
			picOut.setAttribute( 'aria-label', pics[ sym ] || '' );
			loadPicture( sym ).then( function ( svg ) {
				if ( picShown !== sym ) {
					return;
				}
				if ( svg ) {
					picOut.replaceChildren( document.importNode( svg, true ) );
				} else {
					// Couldn't load it: fall back to the plain tile.
					panelTile.classList.remove( 'has-pic' );
					picOut.hidden = true;
					picOut.removeAttribute( 'role' );
					picOut.removeAttribute( 'aria-label' );
				}
			} );
			// Fetch the next and previous elements' pictures while the reader looks at this one.
			var at = symIndex[ sym ];
			[ at + 1, at - 1 ].forEach( function ( i ) {
				var e = elements[ i ];
				if ( e && Object.prototype.hasOwnProperty.call( pics, e[ 0 ] ) ) {
					( window.requestIdleCallback || window.setTimeout )( function () {
						loadPicture( e[ 0 ] );
					} );
				}
			} );
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
			showPicture( el[ 0 ] );
			setText( 'name', el[ 1 ] );
			setText( 'title', el[ 1 ] );
			setText( 'family', families[ el[ 2 ] ] || '' );
			setText( 'def', el[ 3 ] );
			panel.setAttribute( 'data-alpt-theme', el[ 5 ] || '' );
			panel.setAttribute( 'data-alpt-era', el[ 8 ] || '' );
			setText( 'year', ( el[ 9 ] ? 'Used this way since ' : 'In use since ' ) + ( el[ 7 ] ? 'about ' : '' ) + el[ 6 ] + ( el[ 9 ] ? ' (the word is older)' : '' ) );
			var themeLabel = root.querySelector( '[data-alpt-theme-label]' );
			if ( themeLabel ) {
				themeLabel.className = 'alpt-panel__theme alpt-th--' + el[ 5 ];
				themeLabel.textContent = ( themes[ el[ 5 ] ] || '' ) + ' theme';
			}
			panel.className = panel.className.replace( /\balpt-fam--\S+/g, '' ).trim() + ' alpt-fam--' + el[ 2 ];

			tiles.forEach( function ( tile, i ) {
				if ( i === index ) {
					tile.setAttribute( 'aria-current', 'true' );
				} else {
					tile.removeAttribute( 'aria-current' );
				}
			} );

			renderLinks();
			renderIssues( el );
			safeNote.hidden = safeSyms.indexOf( el[ 0 ] ) === -1;
			creditNote.hidden = ! credits[ el[ 0 ] ];
			creditNote.textContent = credits[ el[ 0 ] ] ? 'Suggested by ' + credits[ el[ 0 ] ] + '. Thank you!' : '';
			var bench = root.querySelector( '[data-alpt-bench]' );
			if ( bench && data.bench ) {
				bench.textContent = data.bench[ el[ 0 ] ] || 'AI Literacy';
			}

			// The address names the element for sharing (#element-pv). Nothing on the page has that id, so opening the
			// link doesn't jump down to the list of definitions.
			if ( opts.updateHash && window.history && window.history.replaceState ) {
				window.history.replaceState( null, '', '#element-' + el[ 0 ].toLowerCase() );
			}

			// On narrower screens the panel sits above the tiles, so bring it into view.
			if ( opts.reveal && narrow.matches ) {
				root.classList.add( 'is-detail-open' );
				panel.focus( { preventScroll: true } );
				var rect = panel.getBoundingClientRect();
				if ( ! tablet.matches && ( rect.top < 80 || rect.top > window.innerHeight * 0.5 ) ) {
					panel.scrollIntoView( { behavior: reduceMotion.matches ? 'auto' : 'smooth', block: 'start' } );
				} else if ( tablet.matches && tiles[ index ] ) {
					// The drawer covers the lower part of the screen: keep the chosen tile above it.
					var tileRect = tiles[ index ].getBoundingClientRect();
					var drawerTop = window.innerHeight - panel.offsetHeight;
					if ( tileRect.bottom > drawerTop - 8 ) {
						window.scrollBy( { top: tileRect.bottom - drawerTop + 24, behavior: reduceMotion.matches ? 'auto' : 'smooth' } );
					}
				}
			}
			// Announced last, once the panel is showing (on narrow screens it is hidden until a tile is chosen).
			if ( shownOnce ) {
				panelSay.textContent = el[ 1 ] + '. ' + el[ 3 ];
			}
			shownOnce = true;
		}

		// Issues: what the technical term raises in real life (like symptoms of it), each with what it means and a question
		// to talk about. One opens at a time, under the list.
		var issues = data.issues || {};
		var elIssues = data.elIssues || {};
		var talk = data.talk || {};
		var resources = data.resources || [];
		var kindNames = { lesson: 'AIAD lesson', article: 'AIAD article', tool: 'AIAD tool', partner: 'from a partner' };

		// "Learn more" links: AIAD's own resources on the element or issue (partners' after), as a run of links.
		function resourceLinks( indexes, into ) {
			into.textContent = '';
			( indexes || [] ).forEach( function ( i, n ) {
				var r = resources[ i ];
				if ( ! r ) {
					return;
				}
				if ( n ) {
					into.appendChild( document.createTextNode( ', ' ) );
				}
				var a = document.createElement( 'a' );
				a.href = r[ 1 ];
				a.textContent = r[ 0 ];
				into.appendChild( a );
				var kind = document.createElement( 'span' );
				kind.className = 'alpt-res__kind';
				kind.textContent = ' (' + ( kindNames[ r[ 2 ] ] || '' ) + ')';
				into.appendChild( kind );
			} );
			return into.childNodes.length > 0;
		}
		var issuesOut = root.querySelector( '[data-alpt-issues-out]' );
		var issuesList = null;
		var issueDetail = null;
		var talkLine = null;
		if ( issuesOut ) {
			issuesList = document.createElement( 'div' );
			issuesList.className = 'alpt-panel__issues-list';
			issueDetail = document.createElement( 'div' );
			issueDetail.className = 'alpt-panel__issue';
			issueDetail.hidden = true;
			talkLine = document.createElement( 'p' );
			talkLine.className = 'alpt-panel__talk';
			issuesOut.appendChild( issuesList );
			issuesOut.appendChild( issueDetail );
			// The question shows under the definition, outside Classroom notes: questioning the word is the point.
			var defLine = root.querySelector( '[data-alpt-out="def"]' );
			if ( defLine ) {
				defLine.after( talkLine );
			} else {
				issuesOut.parentNode.insertBefore( talkLine, issuesOut );
			}
		}

		function openIssue( key ) {
			var issue = issues[ key ];
			Array.prototype.forEach.call( issuesList.querySelectorAll( '[data-alpt-issue]' ), function ( b ) {
				b.setAttribute( 'aria-expanded', b.getAttribute( 'data-alpt-issue' ) === key && issueDetail.hidden !== false ? 'true' : 'false' );
			} );
			var already = issueDetail.getAttribute( 'data-open' ) === key && ! issueDetail.hidden;
			issueDetail.textContent = '';
			if ( ! issue || already ) {
				issueDetail.hidden = true;
				issueDetail.removeAttribute( 'data-open' );
				Array.prototype.forEach.call( issuesList.querySelectorAll( '[data-alpt-issue]' ), function ( b ) {
					b.setAttribute( 'aria-expanded', 'false' );
				} );
				return;
			}
			Array.prototype.forEach.call( issuesList.querySelectorAll( '[data-alpt-issue]' ), function ( b ) {
				b.setAttribute( 'aria-expanded', b.getAttribute( 'data-alpt-issue' ) === key ? 'true' : 'false' );
			} );
			issueDetail.className = 'alpt-panel__issue alpt-th--' + issue[ 1 ];
			var def = document.createElement( 'p' );
			var name = document.createElement( 'strong' );
			name.textContent = issue[ 0 ] + ': ';
			def.appendChild( name );
			def.appendChild( document.createTextNode( issue[ 2 ] ) );
			var q = document.createElement( 'p' );
			q.className = 'alpt-panel__issue-talk';
			var label = document.createElement( 'strong' );
			label.textContent = 'Talk about it: ';
			q.appendChild( label );
			q.appendChild( document.createTextNode( issue[ 3 ] ) );
			issueDetail.appendChild( def );
			issueDetail.appendChild( q );
			var more = document.createElement( 'p' );
			more.className = 'alpt-panel__issue-res';
			var moreLabel = document.createElement( 'strong' );
			moreLabel.textContent = 'Learn more: ';
			var moreList = document.createElement( 'span' );
			if ( resourceLinks( ( data.issueRes || {} )[ key ], moreList ) ) {
				more.appendChild( moreLabel );
				more.appendChild( moreList );
				issueDetail.appendChild( more );
			}
			issueDetail.setAttribute( 'data-open', key );
			issueDetail.hidden = false;
		}

		function renderIssues( el ) {
			if ( ! issuesOut ) {
				return;
			}
			var keys = elIssues[ el[ 0 ] ] || [];
			issuesList.textContent = '';
			issueDetail.hidden = true;
			issueDetail.removeAttribute( 'data-open' );
			var SHOW = narrow.matches ? 6 : 4;
			keys.forEach( function ( key, k ) {
				var issue = issues[ key ];
				if ( ! issue ) {
					return;
				}
				if ( k === SHOW && keys.length > SHOW + 1 ) {
					var more = document.createElement( 'button' );
					more.type = 'button';
					more.className = 'alpt-issue alpt-issue--more';
					more.setAttribute( 'data-alpt-issues-more', '' );
					more.textContent = '+' + ( keys.length - SHOW ) + ' more';
					issuesList.appendChild( more );
				}
				var b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'alpt-issue alpt-th--' + issue[ 1 ];
				b.setAttribute( 'data-alpt-issue', key );
				b.setAttribute( 'aria-expanded', 'false' );
				b.textContent = issue[ 0 ];
				if ( k >= SHOW && keys.length > SHOW + 1 ) {
					b.hidden = true;
				}
				issuesList.appendChild( b );
			} );
			issuesOut.hidden = ! keys.length;
			// The first issue opens straight away, so Classroom notes always shows something to read; the others
			// open with a press.
			var first = keys.filter( function ( key ) { return issues[ key ]; } )[ 0 ];
			if ( first ) {
				openIssue( first );
			}
			var question = talk[ el[ 0 ] ];
			talkLine.textContent = '';
			if ( question ) {
				var label = document.createElement( 'strong' );
				label.textContent = 'Talk about it: ';
				talkLine.appendChild( label );
				talkLine.appendChild( document.createTextNode( question ) );
			}
			talkLine.hidden = ! question;
			var resLine = root.querySelector( '[data-alpt-res]' );
			var resList = root.querySelector( '[data-alpt-res-list]' );
			if ( resLine && resList ) {
				// Two in the panel, to keep it short; the list of definitions has up to three.
				resLine.hidden = ! resourceLinks( ( ( data.elRes || {} )[ el[ 0 ] ] || [] ).slice( 0, 2 ), resList );
			}
		}

		if ( issuesOut ) {
			issuesList.addEventListener( 'click', function ( event ) {
				var more = event.target.closest( '[data-alpt-issues-more]' );
				if ( more ) {
					Array.prototype.forEach.call( issuesList.querySelectorAll( '[data-alpt-issue][hidden]' ), function ( hiddenIssue ) {
						hiddenIssue.hidden = false;
					} );
					more.remove();
					return;
				}
				var b = event.target.closest( '[data-alpt-issue]' );
				if ( b ) {
					openIssue( b.getAttribute( 'data-alpt-issue' ) );
				}
			} );
		}

		// Connections: the elements linked to the one in the panel light up on the table, and are listed in the panel
		// to jump to. The compounds it belongs to are named once discovered, and counted until then.
		function renderLinks() {
			var el = elements[ current ];
			var linked = ( links[ el[ 0 ] ] || [] ).map( function ( sym ) { return symIndex[ sym ]; } ).filter( function ( i ) { return i !== undefined; } );
			tiles.forEach( function ( tile, i ) {
				var on = linksOn && linked.indexOf( i ) !== -1;
				tile.classList.toggle( 'is-linked', on );
				tile.classList.toggle( 'is-faded', linksOn && ! on && i !== current );
			} );
			linksOut.hidden = ! linksOn;
			if ( ! linksOn ) {
				return;
			}
			linksList.textContent = '';
			linked.forEach( function ( i ) {
				var other = elements[ i ];
				var button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'alpt-link alpt-fam--' + other[ 2 ];
				button.setAttribute( 'data-alpt-goto', String( i ) );
				var sym = document.createElement( 'span' );
				sym.className = 'alpt-link__sym';
				sym.textContent = other[ 0 ];
				button.appendChild( sym );
				button.appendChild( document.createTextNode( ' ' + other[ 1 ] ) );
				linksList.appendChild( button );
			} );
			var all = ( typeof compounds !== 'undefined' && compounds ) ? compounds : ( data.compounds || [] );
			var done = ( typeof found !== 'undefined' && found ) ? found : [];
			var named = [];
			var hidden = 0;
			all.forEach( function ( c ) {
				if ( c[ 2 ].indexOf( el[ 0 ] ) === -1 ) {
					return;
				}
				if ( done.indexOf( c[ 0 ] ) !== -1 ) {
					named.push( c[ 1 ] );
				} else {
					hidden++;
				}
			} );
			var text = '';
			if ( named.length ) {
				text = 'In compounds: ' + named.join( ', ' ) + '.';
			}
			if ( hidden ) {
				text += ( text ? ' ' : '' ) + ( named.length ? hidden + ' more' : 'Part of ' + hidden + ( hidden === 1 ? ' compound' : ' compounds' ) ) + ' to discover in the compound challenge.';
			}
			linksCompounds.textContent = text;
			linksCompounds.hidden = ! text;
		}

		function matches( el, query ) {
			if ( family && el[ 2 ] !== family ) {
				return false;
			}
			if ( themeFilter && el[ 5 ] !== themeFilter ) {
				return false;
			}
			if ( eraFilter && el[ 8 ] !== eraFilter ) {
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
				tiles[ i ].classList.toggle( 'is-excluded', ! on );
				if ( on ) {
					shown++;
					if ( firstShown < 0 ) {
						firstShown = i;
					}
				}
			} );

			root.classList.toggle( 'is-filtered', Boolean( family || themeFilter || eraFilter || query ) );
			root.classList.toggle( 'has-no-matches', ! shown );
			clearSearch.hidden = ! query;
			familyButtons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-alpt-family' ) === family ? 'true' : 'false' );
			} );
			themeButtons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-alpt-theme-filter' ) === themeFilter ? 'true' : 'false' );
			} );
			eraButtons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-alpt-era-filter' ) === eraFilter ? 'true' : 'false' );
			} );

			if ( ! family && ! themeFilter && ! eraFilter && ! query ) {
				status.textContent = '';
			} else if ( ! shown ) {
				status.textContent = 'No elements match. Clear the search or try a different word.';
			} else {
				status.textContent = 'Showing ' + shown + ' of ' + elements.length + ( family ? ': ' + families[ family ] : '' ) + ( themeFilter ? ': ' + themes[ themeFilter ] + ' theme' : '' ) + ( eraFilter ? ': ' + eras[ eraFilter ] : '' ) + ( query ? ', matching "' + search.value.trim() + '"' : '' ) + '.';
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
				var index = parseInt( tile.getAttribute( 'data-alpt-el' ), 10 );
				if ( labOn ) {
					// In the lab a tile goes into (or out of) the mix, and the panel still shows what it is.
					if ( target && candidates.indexOf( index ) === -1 ) {
						say( 'Try the bright tiles: the answer is one of them. Or press Show the whole table.' );
						return;
					}
					pick( index );
					show( index );
					return;
				}
				show( index, { updateHash: true, reveal: true } );
				// From the keyboard (Enter gives a click with no detail count), go on into the panel beside the
				// tiles, so Share and Previous/Next are a few Tabs away rather than past every tile. Esc comes back.
				if ( 0 === event.detail && ! narrow.matches ) {
					panel.focus( { preventScroll: true } );
				}
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
			if ( event.target.closest( '[data-alpt-links]' ) ) {
				linksOn = ! linksOn;
				event.target.closest( '[data-alpt-links]' ).setAttribute( 'aria-pressed', linksOn ? 'true' : 'false' );
				renderLinks();
				return;
			}
			var go = event.target.closest( '[data-alpt-goto]' );
			if ( go ) {
				var destination = parseInt( go.getAttribute( 'data-alpt-goto' ), 10 );
				if ( tiles[ destination ].classList.contains( 'is-excluded' ) ) {
					family = ''; themeFilter = ''; eraFilter = ''; search.value = '';
					applyFilter();
				}
				show( destination, { updateHash: true } );
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
			clearSearch.addEventListener( 'click', function () {
				search.value = '';
				applyFilter();
				search.focus();
			} );
		}

		// The theme chips work like the family chips; pressing the chosen one again shows every theme.
		themeButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var picked = button.getAttribute( 'data-alpt-theme-filter' );
				themeFilter = ( picked && picked === themeFilter ) ? '' : picked;
				applyFilter();
			} );
		} );

		// The era chips work like the theme chips.
		eraButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				var picked = button.getAttribute( 'data-alpt-era-filter' );
				eraFilter = ( picked && picked === eraFilter ) ? '' : picked;
				applyFilter();
			} );
		} );

		// The Year view's slider steps through the years in which a word on the table came into use. Words from later
		// years fade, so dragging it (or pressing Play) shows the vocabulary arriving, from Regression (1886) to now.
		function setYear( step ) {
			if ( ! yearRange ) {
				return;
			}
			step = Math.max( 0, Math.min( yearSteps.length - 1, step ) );
			yearRange.value = String( step );
			var year = yearSteps[ step ];
			var count = 0;
			elements.forEach( function ( el, i ) {
				var later = el[ 6 ] > year;
				tiles[ i ].classList.toggle( 'is-later', later );
				// Faded tiles say so to screen readers too, not only by how they look.
				var note = tiles[ i ].querySelector( '.alpt-tile__later' );
				if ( later && ! note ) {
					note = document.createElement( 'span' );
					note.className = 'screen-reader-text alpt-tile__later';
					note.textContent = ' (not yet in use in ' + year + ')';
					tiles[ i ].appendChild( note );
				} else if ( note ) {
					if ( later ) {
						note.textContent = ' (not yet in use in ' + year + ')';
					} else {
						note.remove();
					}
				}
				if ( ! later ) {
					count++;
				}
			} );
			yearOut.textContent = String( year );
			yearRange.setAttribute( 'aria-valuetext', year + ': ' + count + ' of ' + elements.length + ' words' );
			root.classList.toggle( 'is-time-travelling', step < yearSteps.length - 1 );
			// While Play runs, the status line waits: announcing every year would talk over itself. stopPlay() says it once.
			if ( root.classList.contains( 'is-years' ) && ! playTimer ) {
				status.textContent = step < yearSteps.length - 1 ? 'By ' + year + ': ' + count + ' of ' + elements.length + ' words on the table were in use.' : '';
			}
		}
		function stopPlay() {
			if ( playTimer ) {
				window.clearInterval( playTimer );
				playTimer = null;
				setYear( parseInt( yearRange.value, 10 ) );
			}
			if ( playButton ) {
				playText.textContent = 'Play';
				playButton.classList.remove( 'is-playing' );
			}
		}
		if ( yearRange ) {
			yearRange.addEventListener( 'input', function () {
				stopPlay();
				setYear( parseInt( yearRange.value, 10 ) );
			} );
			playButton.addEventListener( 'click', function () {
				if ( playTimer ) {
					stopPlay();
					return;
				}
				var step = parseInt( yearRange.value, 10 );
				if ( step >= yearSteps.length - 1 ) {
					step = 0;
				}
				setYear( step );
				playText.textContent = 'Pause';
				playButton.classList.add( 'is-playing' );
				playTimer = window.setInterval( function () {
					var next = parseInt( yearRange.value, 10 ) + 1;
					setYear( next );
					if ( next >= yearSteps.length - 1 ) {
						stopPlay();
					}
				}, reduceMotion.matches ? 1200 : 650 );
			} );
		}

		// View by: Families (the table's own colours) or AIAD themes. The tiles stay where they are; only their colours,
		// the filter chips and the grouping on phones change. ?view=themes opens the page in the themes view.
		function setView( view, updateUrl ) {
			var themed = 'themes' === view;
			root.classList.toggle( 'is-themes', themed );
			root.classList.toggle( 'is-years', 'years' === view );
			stopPlay();
			eraFilter = '';
			setYear( yearSteps.length - 1 );
			viewButtons.forEach( function ( button ) {
				button.setAttribute( 'aria-pressed', button.getAttribute( 'data-alpt-view' ) === view ? 'true' : 'false' );
			} );
			family = '';
			themeFilter = '';
			applyFilter();
			if ( updateUrl && window.history && window.history.replaceState && window.URL ) {
				var url = new window.URL( window.location.href );
				if ( 'families' !== view ) {
					url.searchParams.set( 'view', view );
				} else {
					url.searchParams.delete( 'view' );
				}
				window.history.replaceState( null, '', url.toString() );
			}
		}
		viewButtons.forEach( function ( button ) {
			button.addEventListener( 'click', function () {
				setView( button.getAttribute( 'data-alpt-view' ), true );
			} );
		} );
		var viewMatch = /[?&]view=(themes|years)(&|$)/.exec( window.location.search );
		if ( viewMatch ) {
			setView( viewMatch[ 1 ], false );
		}

		// Arrow keys move between tiles: Left and Right in reading order, Up and Down to the nearest tile in the
		// row above or below, Home and End to the first and last tile showing.
		root.addEventListener( 'keydown', function ( event ) {
			var tile = event.target.closest( '[data-alpt-el]' );
			if ( ! tile ) {
				return;
			}
			var showing = function ( t ) {
				return ! t.classList.contains( 'is-excluded' ) && null !== t.offsetParent;
			};
			if ( 'Home' === event.key || 'End' === event.key ) {
				var list = tiles.filter( showing );
				if ( list.length ) {
					event.preventDefault();
					list[ 'Home' === event.key ? 0 : list.length - 1 ].focus();
				}
				return;
			}
			if ( 'ArrowUp' === event.key || 'ArrowDown' === event.key ) {
				var from = tile.getBoundingClientRect();
				var cx = from.left + from.width / 2;
				var down = 'ArrowDown' === event.key;
				var best = null, bestScore = Infinity;
				tiles.forEach( function ( t ) {
					if ( t === tile || ! showing( t ) ) {
						return;
					}
					var b = t.getBoundingClientRect();
					var dy = down ? b.top - from.bottom : from.top - b.bottom;
					if ( dy < -2 ) {
						return;
					}
					var score = dy * 4 + Math.abs( b.left + b.width / 2 - cx );
					if ( score < bestScore ) {
						bestScore = score;
						best = t;
					}
				} );
				if ( best ) {
					event.preventDefault();
					best.focus();
				}
				return;
			}
			if ( event.key !== 'ArrowRight' && event.key !== 'ArrowLeft' ) {
				return;
			}
			var i = parseInt( tile.getAttribute( 'data-alpt-el' ), 10 ) + ( event.key === 'ArrowRight' ? 1 : -1 );
			while ( tiles[ i ] && tiles[ i ].classList.contains( 'is-excluded' ) ) {
				i += event.key === 'ArrowRight' ? 1 : -1;
			}
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
					if ( shared ) { root.classList.add( 'is-detail-open' ); }
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
		window.addEventListener( 'hashchange', fromHash );
		// The site's smooth-scroll script changes the address with pushState, which fires no hashchange, so links on
		// the page to an element (#element-aw) or a compound (#alpt-cp-…) are handled once the address has changed.
		root.addEventListener( 'click', function ( event ) {
			var link = event.target.closest ? event.target.closest( 'a[href^="#element-"], a[href^="#alpt-cp-"]' ) : null;
			if ( ! link ) {
				return;
			}
			window.setTimeout( function () {
				if ( 0 === link.getAttribute( 'href' ).indexOf( '#element-' ) ) {
					fromHash();
				} else if ( typeof compoundFromHash === 'function' ) {
					compoundFromHash();
				}
			}, 0 );
		} );

		// ---- The compound challenge: choose two to four elements and combine them to discover a compound. ----
		var compounds = data.compounds || []; // [ id, name, syms, volatile ]
		var lab = root.querySelector( '[data-alpt-lab]' );
		var slots = root.querySelector( '[data-alpt-slots]' );
		var labMsg = root.querySelector( '[data-alpt-lab-msg]' );
		var combineButton = root.querySelector( '[data-alpt-combine]' );
		var clearButton = root.querySelector( '[data-alpt-clear]' );
		var labToggles = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-lab-toggle]' ) );
		var cards = {};
		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-compound]' ), function ( card ) {
			cards[ card.getAttribute( 'data-alpt-compound' ) ] = card;
		} );
		var STORE = 'alpt-compounds-v1';
		var MAX = 4;
		var picked = [];
		var labOn = false;
		var labStarter = null; // The level button that started the challenge, for focus when it ends.
		var level = '';
		var found = loadFound();

		function loadFound() {
			try {
				var saved = JSON.parse( window.localStorage.getItem( STORE ) || '[]' );
				return Array.isArray( saved ) ? saved.filter( function ( id ) { return !! cards[ id ]; } ) : [];
			} catch ( e ) {
				return [];
			}
		}

		function saveFound() {
			try {
				window.localStorage.setItem( STORE, JSON.stringify( found ) );
			} catch ( e ) {
				// Private browsing: progress lasts until the page is closed.
			}
		}

		function idOf( syms ) {
			return syms.map( function ( s ) { return s.toLowerCase(); } ).sort().join( '-' );
		}

		function isSubset( small, big ) {
			return small.every( function ( s ) { return big.indexOf( s ) !== -1; } );
		}

		function renderProgress() {
			var n = found.length;
			var total = compounds.length;
			// Progress in each level, and in the level being played.
			function inLevel( key ) {
				var all = compounds.filter( function ( c ) { return ! key || c[ 4 ] === key; } );
				return [ all.filter( function ( c ) { return found.indexOf( c[ 0 ] ) !== -1; } ).length, all.length ];
			}
			Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-level-count]' ), function ( el ) {
				var p = inLevel( el.getAttribute( 'data-alpt-level-count' ) );
				el.textContent = p[ 0 ] + ' of ' + p[ 1 ] + ' found';
			} );
			var here = inLevel( level );
			Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-found-count]' ), function ( el ) {
				el.textContent = ( level ? levelNames[ level ] + ': ' : '' ) + here[ 0 ] + '/' + here[ 1 ];
			} );
			Object.keys( cards ).forEach( function ( id ) {
				var mark = cards[ id ].querySelector( '[data-alpt-found-mark]' );
				if ( mark ) {
					mark.hidden = found.indexOf( id ) === -1;
				}
			} );
			var text = root.querySelector( '[data-alpt-found-text]' );
			if ( text ) {
				text.textContent = n === total ? 'You have discovered all ' + total + ' compounds. Brilliant work.' : 'You have discovered ' + n + ' of ' + total + ' compounds.';
			}
			var meter = root.querySelector( '[data-alpt-meter]' );
			if ( meter ) {
				meter.style.width = ( total ? ( n / total ) * 100 : 0 ) + '%';
			}
			Object.keys( cards ).forEach( function ( id ) {
				cards[ id ].classList.toggle( 'is-found', found.indexOf( id ) !== -1 );
			} );
			var reset = root.querySelector( '[data-alpt-reset]' );
			if ( reset ) {
				reset.hidden = ! n;
			}
			renderLinks();
		}

		function say( parts ) {
			labMsg.textContent = '';
			( Array.isArray( parts ) ? parts : [ parts ] ).forEach( function ( part ) {
				labMsg.appendChild( typeof part === 'string' ? document.createTextNode( part ) : part );
			} );
		}

		function renderSlots() {
			slots.textContent = '';
			for ( var s = 0; s < MAX; s++ ) {
				var li = document.createElement( 'li' );
				var i = picked[ s ];
				if ( i === undefined ) {
					li.className = 'alpt-lab__slot alpt-lab__slot--empty';
					li.setAttribute( 'aria-hidden', 'true' );
				} else {
					var el = elements[ i ];
					var button = document.createElement( 'button' );
					button.type = 'button';
					button.className = 'alpt-lab__slot alpt-fam--' + el[ 2 ];
					button.setAttribute( 'data-alpt-unpick', String( i ) );
					button.setAttribute( 'aria-label', 'Remove ' + el[ 1 ] );
					var sym = document.createElement( 'span' );
					sym.className = 'alpt-lab__slot-sym';
					sym.textContent = el[ 0 ];
					var name = document.createElement( 'span' );
					name.className = 'alpt-lab__slot-name';
					name.textContent = el[ 1 ];
					button.appendChild( sym );
					button.appendChild( name );
					li.appendChild( button );
				}
				slots.appendChild( li );
			}
			tiles.forEach( function ( tile, t ) {
				tile.classList.toggle( 'is-picked', picked.indexOf( t ) !== -1 );
			} );
			if ( combineButton ) {
				combineButton.disabled = picked.length < 2;
			}
			clearButton.disabled = ! picked.length;
			if ( typeof renderFocus === 'function' && targetBox ) {
				renderFocus();
			}
		}

		function setLabHeight() {
			document.documentElement.style.setProperty( '--alpt-lab-h', ( labOn ? lab.offsetHeight : 0 ) + 'px' );
		}
		// The bar changes height as its messages come and go: keep the page's bottom padding matched to it.
		if ( lab && 'ResizeObserver' in window ) {
			new window.ResizeObserver( setLabHeight ).observe( lab );
		}

		function setLab( on ) {
			if ( on ) {
				family = ''; themeFilter = ''; eraFilter = ''; search.value = '';
				root.classList.remove( 'is-detail-open' );
				applyFilter();
			}
			labOn = on;
			lab.hidden = ! on;
			if ( ! on && typeof unfocus === 'function' && targetBox ) {
				unfocus();
			}
			// The challenge hides the compounds not yet discovered; ending it shows them all again.
			root.classList.toggle( 'is-challenge', on );
			document.documentElement.classList.toggle( 'alpt-lab-open', on );
			labToggles.forEach( function ( toggle ) {
				if ( toggle.hasAttribute( 'aria-pressed' ) ) {
					toggle.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
				}
			} );
			if ( on ) {
				renderSlots();
				say( 'Use the clues on the hidden compounds below the table.' );
			} else {
				if ( picked.length ) {
					picked = [];
					renderSlots();
				}
				// The bar is gone: don't leave focus on nothing.
				if ( lab.contains( document.activeElement ) || document.activeElement === document.body ) {
					var back = labStarter && labStarter.offsetParent ? labStarter : root.querySelector( '#alpt-challenge-title' );
					if ( back ) {
						if ( back.id === 'alpt-challenge-title' ) {
							back.setAttribute( 'tabindex', '-1' );
						}
						back.focus();
					}
				}
			}
			if ( typeof setLevel === 'function' && levelChips ) {
				setLevel( on ? level : '' );
			}
			renderProgress();
			setLabHeight();
		}

		function pick( index ) {
			if ( typeof clearHint === 'function' && hinted ) {
				clearHint();
			}
			var at = picked.indexOf( index );
			if ( at !== -1 ) {
				picked.splice( at, 1 );
				say( '' );
			} else if ( picked.length >= MAX ) {
				say( 'Four is the most any compound needs. Remove one to add another.' );
				return;
			} else {
				picked.push( index );
				say( '' );
			}
			renderSlots();
		}

		function combine() {
			clearHint();
			var syms = picked.map( function ( i ) { return elements[ i ][ 0 ]; } );
			var lower = syms.map( function ( s ) { return s.toLowerCase(); } );
			var id = idOf( syms );
			var match = null;
			compounds.forEach( function ( c ) {
				if ( c[ 0 ] === id ) {
					match = c;
				}
			} );

			if ( match ) {
				picked = [];
				renderSlots();
				var link = document.createElement( 'a' );
				link.href = '#alpt-cp-' + match[ 0 ];
				link.textContent = 'See its card';
				if ( found.indexOf( match[ 0 ] ) !== -1 ) {
					say( [ 'You have already discovered ' + match[ 1 ] + '. ', link ] );
					return;
				}
				if ( target && target[ 0 ] === match[ 0 ] ) {
					unfocus();
				}
				found.push( match[ 0 ] );
				saveFound();
				renderProgress();
				var card = cards[ match[ 0 ] ];
				if ( card ) {
					var more = card.querySelector( 'details' );
					if ( more ) {
						more.open = true;
					}
					card.classList.remove( 'is-new' );
					void card.offsetWidth; // Restart the animation.
					card.classList.add( 'is-new' );
				}
				var strong = document.createElement( 'strong' );
				strong.textContent = 'New compound: ' + match[ 1 ] + '! ';
				var note = match[ 3 ] ? 'It\'s volatile: risky without a human in the loop. Can you find the element that stabilises it? ' : '';
				var parts = [ strong, note, found.length === compounds.length ? 'That\'s every compound. ' : found.length + ' of ' + compounds.length + ' discovered. ', link ];
				var after = 'primary' === level ? nextGuided() : '';
				if ( after ) {
					var nextButton = document.createElement( 'button' );
					nextButton.type = 'button';
					nextButton.className = 'alpt-btn alpt-say-next';
					nextButton.textContent = 'Next one';
					nextButton.addEventListener( 'click', function () {
						focusOn( after );
					} );
					parts.push( nextButton );
				}
				say( parts );
				return;
			}

			if ( target ) {
				var right = lower.filter( function ( s ) { return target[ 2 ].map( function ( t ) { return t.toLowerCase(); } ).indexOf( s ) !== -1; } ).length;
				say( right + ' of your ' + lower.length + ' elements ' + ( right === 1 ? 'is' : 'are' ) + ' in this compound, which has ' + target[ 2 ].length + '. ' + ( right < target[ 2 ].length ? 'Swap the others, or press Give me a hint.' : 'Take out the extra ones.' ) );
				return;
			}
			var fizz = 0;
			var tooMany = false;
			compounds.forEach( function ( c ) {
				var parts = c[ 2 ].map( function ( s ) { return s.toLowerCase(); } );
				if ( found.indexOf( c[ 0 ] ) === -1 && parts.length > lower.length && isSubset( lower, parts ) ) {
					fizz++;
				}
				if ( parts.length < lower.length && isSubset( parts, lower ) ) {
					tooMany = true;
				}
			} );
			if ( fizz ) {
				say( 'Something\'s fizzing: these elements appear together in ' + fizz + ' undiscovered compound' + ( fizz === 1 ? '' : 's' ) + '. Try adding another element.' );
			} else if ( tooMany ) {
				say( 'Too many ingredients: there\'s a compound hiding in this mix. Try taking one out.' );
			} else {
				// Say which of the chosen elements are worth keeping: they belong to compounds not yet found.
				var keep = syms.filter( function ( sym ) {
					return openCompounds().some( function ( c ) { return c[ 2 ].indexOf( sym ) !== -1; } );
				} ).map( function ( sym ) { return elements[ symIndex[ sym ] ][ 1 ]; } );
				say( keep.length
					? 'No reaction. ' + listOf( keep ) + ( keep.length === 1 ? ' is' : ' are' ) + ' in compounds you haven\'t found yet: keep ' + ( keep.length === 1 ? 'it' : 'them' ) + ' and swap the rest, or press Hint.'
					: 'No reaction, and none of these are in compounds you haven\'t found yet. Clear the mix and try again, or press Hint.' );
			}
		}

		combineButton?.addEventListener( 'click', combine );
		clearButton.addEventListener( 'click', function () {
			clearHint();
			picked = [];
			renderSlots();
			say( '' );
		} );
		slots.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( '[data-alpt-unpick]' );
			if ( button ) {
				var removed = parseInt( button.getAttribute( 'data-alpt-unpick' ), 10 );
				var position = picked.indexOf( removed );
				pick( removed );
				var remaining = slots.querySelectorAll( '[data-alpt-unpick]' );
				if ( remaining.length ) {
					remaining[ Math.min( position, remaining.length - 1 ) ].focus();
				} else if ( tiles[ removed ].classList.contains( 'is-excluded' ) ) {
					search.focus();
				} else {
					tiles[ removed ].focus();
				}
			}
		} );
		labToggles.forEach( function ( toggle ) {
			toggle.addEventListener( 'click', function () {
				setLab( ! labOn );
			} );
		} );
		// Levels: the chips above the compounds show one level; a level button in the challenge plays just that level.
		// Outside the challenge only the first few compounds show, until "Show all".
		var levelNames = { primary: 'Primary', secondary: 'Secondary', further: 'Further education' };
		var levelChips = Array.prototype.slice.call( root.querySelectorAll( '[data-alpt-level]' ) );
		var moreWrap = root.querySelector( '[data-alpt-more-wrap]' );
		var moreButton = root.querySelector( '[data-alpt-more]' );
		var PREVIEW = 4;
		var showAll = false;
		function setLevel( key ) {
			level = key;
			levelChips.forEach( function ( chip ) {
				chip.setAttribute( 'aria-pressed', chip.getAttribute( 'data-alpt-level' ) === key ? 'true' : 'false' );
			} );
			var shown = 0;
			var total = 0;
			Object.keys( cards ).forEach( function ( id ) {
				var inLevel = ! key || cards[ id ].getAttribute( 'data-alpt-compound-level' ) === key;
				if ( inLevel ) {
					total++;
				}
				var show = inLevel && ( showAll || labOn || shown < PREVIEW );
				if ( show ) {
					shown++;
				}
				cards[ id ].hidden = ! show;
			} );
			if ( moreWrap ) {
				moreWrap.hidden = labOn || total <= PREVIEW;
				moreButton.setAttribute( 'aria-expanded', showAll ? 'true' : 'false' );
				moreButton.textContent = showAll ? 'Show fewer' : 'Show all ' + total + ' compounds';
			}
			renderProgress();
		}
		levelChips.forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				setLevel( chip.getAttribute( 'data-alpt-level' ) );
			} );
		} );
		if ( moreButton ) {
			moreButton.addEventListener( 'click', function () {
				var wasOpen = showAll;
				showAll = ! showAll;
				setLevel( level );
				if ( wasOpen ) {
					root.querySelector( '.alpt-compounds' ).scrollIntoView( { block: 'start' } );
				}
			} );
		}
		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-start]' ), function ( button ) {
			button.addEventListener( 'click', function () {
				setLevel( button.getAttribute( 'data-alpt-start' ) );
				labStarter = button;
				setLab( true );
				// Primary starts guided: the table narrows straight to the first compound's few bright tiles.
				if ( 'primary' === button.getAttribute( 'data-alpt-start' ) && nextGuided() ) {
					focusOn( nextGuided() );
					return;
				}
				// Focus goes up to the table with the view, so keyboard users start choosing tiles straight away.
				for ( var f = 0; f < tiles.length; f++ ) {
					if ( ! tiles[ f ].classList.contains( 'is-excluded' ) ) {
						tiles[ f ].focus( { preventScroll: true } );
						break;
					}
				}
				// Back up to the table, below the fixed site header (the grid's scroll-margin-top).
				var grid = root.querySelector( '.alpt-grid' );
				var margin = parseFloat( window.getComputedStyle( grid ).scrollMarginTop ) || 0;
				window.scrollTo( { top: grid.getBoundingClientRect().top + window.pageYOffset - margin, behavior: reduceMotion.matches ? 'auto' : 'smooth' } );
			} );
		} );
		// ---- Guided mode: "Find this one" on a hidden compound narrows the table to its elements and a few related
		// decoys, starts the mix with the clue element, and offers hints one element at a time. ----
		var target = null; // [ id, name, syms, volatile, level ]
		var candidates = [];
		var targetBox = root.querySelector( '[data-alpt-target-box]' );
		var targetText = root.querySelector( '[data-alpt-target-text]' );
		var hintButton = root.querySelector( '[data-alpt-hint]' );
		var DECOYS = { beginner: 4, intermediate: 5, advanced: 6 };

		function answerIndexes( c ) {
			return c[ 2 ].map( function ( sym ) { return symIndex[ sym ]; } );
		}

		// Decoys come from the answer's own connections, so they are plausible; the same ones every time.
		function decoysFor( c ) {
			var answer = c[ 2 ];
			var pool = [];
			answer.forEach( function ( sym ) {
				( links[ sym ] || [] ).forEach( function ( other ) {
					if ( answer.indexOf( other ) === -1 && pool.indexOf( other ) === -1 && symIndex[ other ] !== undefined ) {
						pool.push( other );
					}
				} );
			} );
			var want = DECOYS[ c[ 4 ] ] || 5;
			// Spread the picks across the pool rather than taking the first few.
			var chosen = [];
			var step = Math.max( 1, Math.floor( pool.length / want ) );
			for ( var k = 0; k < pool.length && chosen.length < want; k += step ) {
				chosen.push( pool[ k ] );
			}
			return chosen.map( function ( sym ) { return symIndex[ sym ]; } );
		}

		function renderFocus() {
			tiles.forEach( function ( tile, i ) {
				tile.classList.toggle( 'is-unfocused', !! target && candidates.indexOf( i ) === -1 );
			} );
			root.classList.toggle( 'is-focused', !! target );
			targetBox.hidden = ! target;
			if ( ! target ) {
				return;
			}
			var first = elements[ symIndex[ target[ 2 ][ 0 ] ] ];
			var levelWord = levelNames[ target[ 4 ] ].toLowerCase();
			targetText.textContent = 'Finding ' + ( /^[aeiou]/.test( levelWord ) ? 'an ' : 'a ' ) + levelWord + ' compound with ' + target[ 2 ].length + ' elements, including ' + first[ 1 ] + ' (' + first[ 0 ] + '). The answer is among the bright tiles.' + ( target[ 8 ] ? ' Looks like: ' + target[ 8 ] : '' );
			var missing = target[ 2 ].filter( function ( sym ) { return picked.indexOf( symIndex[ sym ] ) === -1; } );
			hintButton.disabled = ! missing.length;
		}

		function focusOn( id ) {
			var c = null;
			compounds.forEach( function ( x ) {
				if ( x[ 0 ] === id ) {
					c = x;
				}
			} );
			if ( ! c ) {
				return;
			}
			target = c;
			var answer = answerIndexes( c );
			candidates = answer.concat( decoysFor( c ) );
			picked = [ answer[ 0 ] ];
			show( answer[ 0 ] );
			if ( ! labOn ) {
				setLab( true );
			}
			renderSlots();
			renderFocus();
			say( 'The clue element is in the mix. Add the others from the bright tiles, then press Combine.' );
			var grid = root.querySelector( '.alpt-grid' );
			var margin = parseFloat( window.getComputedStyle( grid ).scrollMarginTop ) || 0;
			window.scrollTo( { top: grid.getBoundingClientRect().top + window.pageYOffset - margin, behavior: reduceMotion.matches ? 'auto' : 'smooth' } );
		}

		// The next compound in the level still to discover, for the guided Primary level.
		function nextGuided() {
			for ( var g = 0; g < compounds.length; g++ ) {
				if ( compounds[ g ][ 4 ] === level && found.indexOf( compounds[ g ][ 0 ] ) === -1 ) {
					return compounds[ g ][ 0 ];
				}
			}
			return '';
		}

		function unfocus() {
			clearHint();
			target = null;
			candidates = [];
			renderFocus();
		}

		// Hints point the way rather than doing it: the first press lights up the area of the table (the family) where
		// the next missing element is and dims the rest; a second press makes that one tile glow. The student still
		// picks it, and the highlight clears when they pick a tile.
		var hintAim = null; // The compound a hint is pointing towards.
		var hinted = null; // { index: element, step: 1 (area) or 2 (tile) }

		function clearHint() {
			hinted = null;
			tiles.forEach( function ( tile ) {
				tile.classList.remove( 'is-hint-area', 'is-hint-tile', 'is-hint-dim' );
			} );
		}

		function renderHint() {
			tiles.forEach( function ( tile, i ) {
				var el = elements[ i ];
				var inArea = !! hinted && el[ 2 ] === elements[ hinted.index ][ 2 ];
				var isTile = !! hinted && i === hinted.index;
				tile.classList.toggle( 'is-hint-area', !! hinted && hinted.step === 1 && inArea );
				tile.classList.toggle( 'is-hint-tile', !! hinted && hinted.step === 2 && isTile );
				tile.classList.toggle( 'is-hint-dim', !! hinted && ( hinted.step === 1 ? ! inArea : ! isTile ) && picked.indexOf( i ) === -1 );
			} );
		}

		// Compounds still to find: in the level being played, not yet discovered.
		function openCompounds() {
			return compounds.filter( function ( c ) {
				return found.indexOf( c[ 0 ] ) === -1 && ( ! level || c[ 4 ] === level );
			} );
		}

		function listOf( names ) {
			return names.length < 2 ? names.join( '' ) : names.slice( 0, -1 ).join( ', ' ) + ' and ' + names[ names.length - 1 ];
		}

		// The compound to point towards: the one being found, or the open compound closest to the mix (with nothing
		// close, the one with the fewest elements).
		function aimFor() {
			if ( target ) {
				return target;
			}
			var open = openCompounds();
			if ( hintAim && open.indexOf( hintAim ) !== -1 ) {
				var mixNow = picked.map( function ( i ) { return elements[ i ][ 0 ]; } );
				var stillFits = mixNow.every( function ( sym ) { return hintAim[ 2 ].indexOf( sym ) !== -1; } );
				if ( stillFits ) {
					return hintAim;
				}
			}
			var mix = picked.map( function ( i ) { return elements[ i ][ 0 ]; } );
			var best = null;
			var bestScore = -Infinity;
			open.forEach( function ( c ) {
				var overlap = c[ 2 ].filter( function ( sym ) { return mix.indexOf( sym ) !== -1; } ).length;
				var score = overlap * 10 - c[ 2 ].length;
				if ( score > bestScore ) {
					bestScore = score;
					best = c;
				}
			} );
			return best;
		}

		function hint() {
			var aim = aimFor();
			if ( ! aim ) {
				clearHint();
				say( 'You have found every compound in this level. Choose another level, or end the challenge.' );
				return;
			}
			hintAim = aim;
			var mix = picked.map( function ( i ) { return elements[ i ][ 0 ]; } );
			var keep = aim[ 2 ].filter( function ( sym ) { return mix.indexOf( sym ) !== -1; } );
			var wrong = mix.filter( function ( sym ) { return aim[ 2 ].indexOf( sym ) === -1; } );
			var next = aim[ 2 ].filter( function ( sym ) { return mix.indexOf( sym ) === -1; } )[ 0 ];
			if ( next === undefined ) {
				clearHint();
				say( wrong.length ? 'Everything you need is in the mix. Take out ' + listOf( wrong.map( function ( sym ) { return elements[ symIndex[ sym ] ][ 1 ]; } ) ) + ', then press Combine.' : 'Everything you need is in the mix. Press Combine!' );
				return;
			}
			var index = symIndex[ next ];
			var step = hinted && hinted.index === index ? 2 : 1;
			hinted = { index: index, step: step };
			renderHint();
			var family = families[ elements[ index ][ 2 ] ];
			var left = aim[ 2 ].length - keep.length;
			var start = keep.length
				? 'Keep ' + listOf( keep.map( function ( sym ) { return elements[ symIndex[ sym ] ][ 1 ]; } ) ) + '. '
				: 'Try a compound with ' + aim[ 2 ].length + ' elements. ';
			var drop = wrong.length ? 'Take out ' + listOf( wrong.map( function ( sym ) { return elements[ symIndex[ sym ] ][ 1 ]; } ) ) + '. ' : '';
			say( step === 1
				? start + drop + 'The next element is in the highlighted ' + family + ' area' + ( left > 1 ? ' (' + left + ' still to find).' : '.' ) + ' Press Hint again to narrow it down.'
				: start + drop + 'It\'s the glowing tile in ' + family + '.' );
		}
		root.querySelector( '[data-alpt-free-hint]' )?.addEventListener( 'click', hint );

		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-target]' ), function ( button ) {
			button.addEventListener( 'click', function () {
				focusOn( button.getAttribute( 'data-alpt-target' ) );
			} );
		} );
		hintButton.addEventListener( 'click', hint );
		root.querySelector( '[data-alpt-unfocus]' )?.addEventListener( 'click', function () {
			unfocus();
			say( 'The whole table is back. Choose any elements to combine.' );
		} );

		// Start again asks twice: the first press arms it for four seconds.
		var resetButton = root.querySelector( '[data-alpt-reset]' );
		var resetArmed = null;
		if ( resetButton ) {
			resetButton.addEventListener( 'click', function () {
				if ( ! resetArmed ) {
					resetButton.textContent = 'Press again to start again';
					resetArmed = window.setTimeout( function () {
						resetArmed = null;
						resetButton.textContent = 'Start again';
					}, 4000 );
					return;
				}
				window.clearTimeout( resetArmed );
				resetArmed = null;
				resetButton.textContent = 'Start again';
				found = [];
				saveFound();
				renderProgress();
			} );
		}

		// Esc ends the challenge, but not while typing in a field (where it clears the field) or using the board.
		document.addEventListener( 'keydown', function ( event ) {
			if ( labOn && event.key === 'Escape' && ! event.target.closest( 'input, textarea, select, .alpt-board' ) && board.hidden ) {
				setLab( false );
			}
		} );
		window.addEventListener( 'resize', setLabHeight );

		unhide( '[data-alpt-challenge]' );
		unhide( '[data-alpt-levels]' );
		root.classList.add( 'has-lab' );
		setLevel( '' );

		// A link to a compound (#alpt-cp-…, from Copy link on its card) shows every compound, scrolls to that one and
		// marks it for a moment. A compound still hidden by the challenge stays hidden, so links never give answers away.
		function compoundFromHash() {
			var hash = window.location.hash.slice( 1 );
			if ( hash.indexOf( 'alpt-cp-' ) !== 0 ) {
				return;
			}
			var card = cards[ hash.slice( 8 ) ];
			if ( ! card ) {
				return;
			}
			showAll = true;
			setLevel( '' );
			card.scrollIntoView( { block: 'center' } );
			card.classList.add( 'is-linked' );
			window.setTimeout( function () {
				card.classList.remove( 'is-linked' );
			}, 2500 );
		}
		compoundFromHash();
		window.addEventListener( 'hashchange', compoundFromHash );

		// ---- Show to class: an element or a compound fills the screen, like a resource's "Show on the board".
		// Full screen where the browser allows it, otherwise the whole window. Arrow keys step through the elements;
		// "Hide definition" lets a class guess first. Esc or Close ends it. ----
		var board = document.createElement( 'div' );
		board.className = 'alpt-board';
		board.setAttribute( 'role', 'dialog' );
		board.setAttribute( 'aria-modal', 'true' );
		board.setAttribute( 'aria-label', 'Shown to the class' );
		board.tabIndex = -1;
		board.hidden = true;
		root.appendChild( board );
		// Announces each new element as the board steps, since the board's content is rebuilt each time.
		var boardSay = document.createElement( 'div' );
		boardSay.className = 'screen-reader-text';
		boardSay.setAttribute( 'aria-live', 'polite' );
		board.appendChild( boardSay );
		// The board is rebuilt for each element; this keeps its live region, inside the dialog, in place.
		function clearBoard() {
			Array.prototype.slice.call( board.children ).forEach( function ( child ) {
				if ( child !== boardSay ) {
					child.remove();
				}
			} );
		}
		var boardMode = null; // 'element' or 'compound'
		var boardIndex = 0;
		var boardHideDef = false;
		var boardPrefHide = true; // Guess first: an element opens with its definition hidden until you choose otherwise.
		var boardOpener = null;

		function el( tag, className, text ) {
			var node = document.createElement( tag );
			if ( className ) {
				node.className = className;
			}
			if ( text ) {
				node.textContent = text;
			}
			return node;
		}

		function boardButton( label, action, extra ) {
			var b = el( 'button', 'alpt-board__btn' + ( extra ? ' ' + extra : '' ), label );
			b.type = 'button';
			b.setAttribute( 'data-alpt-board-action', action );
			return b;
		}

		function themed( key ) {
			return root.classList.contains( 'is-themes' ) ? 'alpt-th--' + key : '';
		}

		// ---- A shareable graphic card of an element: 1080 × 1350, the portrait size LinkedIn and Instagram use. ----
		var shareFilesOk = null;
		function canShareFiles() {
			if ( null === shareFilesOk ) {
				try {
					shareFilesOk = !! ( navigator.canShare && window.File && navigator.canShare( { files: [ new File( [ '' ], 'x.png', { type: 'image/png' } ) ] } ) );
				} catch ( err ) {
					shareFilesOk = false;
				}
			}
			return shareFilesOk;
		}

		function cardColours( e ) {
			var probe = el( 'span', 'alpt-fam--' + e[ 2 ] );
			probe.hidden = true;
			root.appendChild( probe );
			var cs = getComputedStyle( probe );
			var rs = getComputedStyle( document.documentElement );
			var out = {
				fam: cs.getPropertyValue( '--fam' ).trim() || '#E5E7EB',
				deep: cs.getPropertyValue( '--fam-deep' ).trim() || '#231F20',
				ink: rs.getPropertyValue( '--a27-ink' ).trim() || '#231F20',
				cream: rs.getPropertyValue( '--a27-cream' ).trim() || '#F6F4ED',
				accent: rs.getPropertyValue( '--a27-safe' ).trim() || '#00BEDD'
			};
			probe.remove();
			return out;
		}

		// Breaks at spaces and after hyphens, so "Human-in-the-Loop" can wrap.
		function wrapLines( ctx, text, width ) {
			var lines = [], line = '';
			String( text ).split( /\s+/ ).forEach( function ( word ) {
				word.split( /(?<=-)/ ).forEach( function ( bit, j ) {
					var test = line ? line + ( 0 === j ? ' ' : '' ) + bit : bit;
					if ( line && ctx.measureText( test ).width > width ) {
						lines.push( line );
						line = bit;
					} else {
						line = test;
					}
				} );
			} );
			if ( line ) {
				lines.push( line );
			}
			return lines;
		}

		// Keeps at most max lines, ending the last with "…" when text was cut.
		function clampLines( ctx, lines, max, width ) {
			if ( lines.length <= max ) {
				return lines;
			}
			var out = lines.slice( 0, max );
			var last = out[ max - 1 ];
			while ( last.length > 1 && ctx.measureText( last + '…' ).width > width ) {
				last = last.slice( 0, -1 );
			}
			out[ max - 1 ] = last.replace( /[\s-]+$/, '' ) + '…';
			return out;
		}

		function spaced( ctx, text, x, y, px ) {
			if ( 'letterSpacing' in ctx ) {
				ctx.letterSpacing = px + 'px';
				ctx.fillText( text, x, y );
				ctx.letterSpacing = '0px';
			} else {
				ctx.fillText( text, x, y );
			}
		}

		function drawCard( index ) {
			var e = elements[ index ];
			var c = cardColours( e );
			var W = 1080, H = 1350, P = 84;
			var canvas = document.createElement( 'canvas' );
			canvas.width = W;
			canvas.height = H;
			var ctx = canvas.getContext( '2d' );
			var face = getComputedStyle( document.querySelector( '.alpt-tile__sym' ) || document.body ).fontFamily;
			var font = function ( weight, size ) {
				return weight + ' ' + Math.round( size ) + 'px ' + face;
			};
			ctx.fillStyle = c.ink;
			ctx.fillRect( 0, 0, W, H );

			ctx.fillStyle = c.accent;
			ctx.font = font( 700, 26 );
			spaced( ctx, 'THE AI LITERACY PERIODIC TABLE', P, P + 20, 4 );

			// The tile, with its cut corner, as on the table.
			var T = 400, tx = P, ty = P + 72, cut = 64;
			ctx.fillStyle = c.fam;
			ctx.beginPath();
			ctx.moveTo( tx, ty );
			ctx.lineTo( tx + T - cut, ty );
			ctx.lineTo( tx + T, ty + cut );
			ctx.lineTo( tx + T, ty + T );
			ctx.lineTo( tx, ty + T );
			ctx.closePath();
			ctx.fill();
			ctx.fillStyle = c.ink;
			ctx.font = font( 600, 40 );
			ctx.fillText( String( index + 1 ), tx + 36, ty + 72 );
			// The name first, at the foot of the tile, then the symbol in the space left above it.
			var nameSize = 34;
			ctx.font = font( 700, nameSize );
			var nameLines = wrapLines( ctx, e[ 1 ], T - 72 );
			while ( nameLines.length > 2 && nameSize > 24 ) {
				nameSize -= 2;
				ctx.font = font( 700, nameSize );
				nameLines = wrapLines( ctx, e[ 1 ], T - 72 );
			}
			nameLines = nameLines.slice( 0, 3 );
			var nameTop = ty + T - 36 - ( nameLines.length - 1 ) * ( nameSize + 6 ) - nameSize;
			ctx.fillStyle = c.ink;
			nameLines.forEach( function ( l, i ) {
				ctx.fillText( l, tx + 36, nameTop + nameSize + i * ( nameSize + 6 ) );
			} );
			// Cap height is about 0.72 of the size and a descender (g, p, y) about 0.22.
			var room = nameTop - 16 - ( ty + 96 );
			var symSize = Math.min( 210, room / 0.96 );
			ctx.font = font( 700, symSize );
			while ( ctx.measureText( e[ 0 ] ).width > T - 72 && symSize > 80 ) {
				symSize -= 6;
				ctx.font = font( 700, symSize );
			}
			ctx.fillStyle = c.deep;
			ctx.fillText( e[ 0 ], tx + 30, nameTop - 16 - symSize * 0.22 );

			// Beside the tile: the family and the theme.
			var sx = tx + T + 48, sw = W - P - sx, y = ty + 52;
			ctx.fillStyle = c.fam;
			ctx.font = font( 700, 30 );
			wrapLines( ctx, ( families[ e[ 2 ] ] || '' ).toUpperCase(), sw ).forEach( function ( l ) {
				spaced( ctx, l, sx, y, 2 );
				y += 42;
			} );
			y += 8;
			ctx.fillStyle = c.cream;
			ctx.globalAlpha = 0.75;
			ctx.font = font( 500, 30 );
			wrapLines( ctx, ( themes[ e[ 5 ] ] || '' ) + ' theme', sw ).forEach( function ( l ) {
				ctx.fillText( l, sx, y );
				y += 40;
			} );
			ctx.globalAlpha = 1;

			// The name, definition and talk question, made smaller together until they fit above the footer.
			var top = ty + T + 110, bottom = H - P - 100, width = W - P * 2, question = talk[ e[ 0 ] ] || '';
			var plan = function ( k ) {
				var parts = [];
				ctx.font = font( 700, 84 * k );
				parts.push( { lines: wrapLines( ctx, e[ 1 ], width ), size: 84 * k, weight: 700, lh: 1.04, after: 30 * k } );
				ctx.font = font( 600, 44 * k );
				parts.push( { lines: wrapLines( ctx, e[ 3 ], width ), size: 44 * k, weight: 600, lh: 1.28, after: 48 * k } );
				if ( question ) {
					ctx.font = font( 500, 34 * k );
					parts.push( { lines: wrapLines( ctx, question, width - 40 ), size: 34 * k, weight: 500, lh: 1.34, after: 0, talk: true } );
				}
				var h = 0;
				parts.forEach( function ( p ) {
					h += ( p.talk ? 48 * k : 0 ) + p.lines.length * p.size * p.lh + p.after;
				} );
				return { parts: parts, height: h, k: k };
			};
			var layout = plan( 1 );
			for ( var k = 0.95; top + layout.height > bottom && k >= 0.5; k -= 0.05 ) {
				layout = plan( k );
			}
			y = top;
			layout.parts.forEach( function ( p ) {
				var x = P;
				if ( p.talk ) {
					var blockTop = y - p.size;
					ctx.fillStyle = c.accent;
					ctx.font = font( 700, 24 * layout.k );
					spaced( ctx, 'TALK ABOUT IT', P + 36, blockTop + 22 * layout.k, 3 );
					y += 48 * layout.k;
					ctx.fillRect( P, blockTop - 4, 8, 48 * layout.k + p.lines.length * p.size * p.lh );
					x = P + 36;
				}
				ctx.fillStyle = c.cream;
				ctx.font = font( p.weight, p.size );
				p.lines.forEach( function ( l ) {
					ctx.fillText( l, x, y );
					y += p.size * p.lh;
				} );
				y += p.after;
			} );

			// Footer.
			ctx.fillStyle = c.cream;
			ctx.globalAlpha = 0.25;
			ctx.fillRect( P, H - P - 62, W - P * 2, 2 );
			ctx.globalAlpha = 1;
			ctx.font = font( 700, 28 );
			ctx.fillText( 'AI Awareness Day · Thursday 29 April 2027', P, H - P );
			ctx.textAlign = 'right';
			ctx.fillStyle = c.accent;
			ctx.font = font( 600, 26 );
			ctx.fillText( 'aiawarenessday.co.uk', W - P, H - P );
			ctx.textAlign = 'left';
			return canvas;
		}

		function drawCompoundCard( index ) {
			var cp = compounds[ index ];
			var rs = getComputedStyle( document.documentElement );
			var ink = rs.getPropertyValue( '--a27-ink' ).trim() || '#231F20';
			var cream = rs.getPropertyValue( '--a27-cream' ).trim() || '#F6F4ED';
			var accent = rs.getPropertyValue( '--a27-safe' ).trim() || '#00BEDD';
			var W = 1080, H = 1350, P = 84;
			var canvas = document.createElement( 'canvas' );
			canvas.width = W;
			canvas.height = H;
			var ctx = canvas.getContext( '2d' );
			var face = getComputedStyle( document.querySelector( '.alpt-tile__sym' ) || document.body ).fontFamily;
			var font = function ( weight, size ) {
				return weight + ' ' + Math.round( size ) + 'px ' + face;
			};
			ctx.fillStyle = ink;
			ctx.fillRect( 0, 0, W, H );
			ctx.fillStyle = accent;
			ctx.font = font( 700, 26 );
			spaced( ctx, 'THE AI LITERACY PERIODIC TABLE · COMPOUND', P, P + 20, 4 );

			// The elements it is made of, as tiles joined by "+".
			var parts = cp[ 2 ].map( function ( sym ) {
				return elements[ symIndex[ sym ] ];
			} ).filter( Boolean );
			var gapW = 64, n = parts.length;
			var T = Math.min( 230, ( W - P * 2 - ( n - 1 ) * gapW ) / n );
			var ty = P + 76, x = P, cut = T * 0.16;
			parts.forEach( function ( e, i ) {
				var colours = cardColours( e );
				ctx.fillStyle = colours.fam;
				ctx.beginPath();
				ctx.moveTo( x, ty );
				ctx.lineTo( x + T - cut, ty );
				ctx.lineTo( x + T, ty + cut );
				ctx.lineTo( x + T, ty + T );
				ctx.lineTo( x, ty + T );
				ctx.closePath();
				ctx.fill();
				// Shrink the name until every line fits inside the tile.
				var nameSize = Math.max( 18, T * 0.1 ), nameW = T - T * 0.2, lines;
				var fits = function () {
					return lines.length <= 3 && lines.every( function ( l ) {
						return ctx.measureText( l ).width <= nameW;
					} );
				};
				do {
					ctx.font = font( 700, nameSize );
					lines = wrapLines( ctx, e[ 1 ], nameW );
				} while ( ! fits() && ( nameSize -= 1 ) >= 14 );
				nameSize = Math.max( 14, nameSize );
				ctx.font = font( 700, nameSize );
				lines = clampLines( ctx, wrapLines( ctx, e[ 1 ], nameW ), 3, nameW );
				var nameTop = ty + T - T * 0.1 - ( lines.length - 1 ) * ( nameSize + 4 ) - nameSize;
				ctx.fillStyle = ink;
				lines.forEach( function ( l, j ) {
					ctx.fillText( l, x + T * 0.1, nameTop + nameSize + j * ( nameSize + 4 ) );
				} );
				var symSize = Math.min( T * 0.5, ( nameTop - ty - T * 0.14 ) / 0.96 );
				ctx.font = font( 700, symSize );
				while ( ctx.measureText( e[ 0 ] ).width > T * 0.82 && symSize > 30 ) {
					symSize -= 4;
					ctx.font = font( 700, symSize );
				}
				ctx.fillStyle = colours.deep;
				ctx.fillText( e[ 0 ], x + T * 0.08, nameTop - T * 0.06 - symSize * 0.22 );
				if ( i < n - 1 ) {
					ctx.fillStyle = cream;
					ctx.font = font( 700, 56 );
					ctx.textAlign = 'center';
					ctx.fillText( '+', x + T + gapW / 2, ty + T / 2 + 20 );
					ctx.textAlign = 'left';
				}
				x += T + gapW;
			} );

			// Stable or volatile, theme, then the name, what it is and the debate, made smaller together to fit.
			var top = ty + T + 92, bottom = H - P - 100, width = W - P * 2;
			var label = ( cp[ 3 ] ? 'VOLATILE COMPOUND' : 'STABLE COMPOUND' ) + ' · ' + String( themes[ cp[ 7 ] ] || '' ).toUpperCase() + ' THEME';
			var plan = function ( k ) {
				var rows = [];
				ctx.font = font( 700, 80 * k );
				rows.push( { lines: wrapLines( ctx, cp[ 1 ], width ), size: 80 * k, weight: 700, lh: 1.04, after: 30 * k } );
				ctx.font = font( 600, 42 * k );
				rows.push( { lines: wrapLines( ctx, cp[ 5 ], width ), size: 42 * k, weight: 600, lh: 1.28, after: 46 * k } );
				if ( cp[ 6 ] ) {
					ctx.font = font( 500, 34 * k );
					rows.push( { lines: wrapLines( ctx, cp[ 6 ], width - 40 ), size: 34 * k, weight: 500, lh: 1.34, after: 0, debate: true } );
				}
				var h = 60 * k;
				rows.forEach( function ( r ) {
					h += ( r.debate ? 48 * k : 0 ) + r.lines.length * r.size * r.lh + r.after;
				} );
				return { rows: rows, height: h, k: k };
			};
			var layout = plan( 1 );
			for ( var k = 0.95; top + layout.height > bottom && k >= 0.5; k -= 0.05 ) {
				layout = plan( k );
			}
			var y = top;
			ctx.fillStyle = cp[ 3 ] ? '#F59E0B' : accent;
			ctx.font = font( 700, 26 * layout.k );
			spaced( ctx, label, P, y, 3 );
			y += 60 * layout.k + 40 * layout.k;
			layout.rows.forEach( function ( r ) {
				var rx = P;
				if ( r.debate ) {
					var blockTop = y - r.size;
					ctx.fillStyle = accent;
					ctx.font = font( 700, 24 * layout.k );
					spaced( ctx, 'DEBATE IT', P + 36, blockTop + 22 * layout.k, 3 );
					y += 48 * layout.k;
					ctx.fillRect( P, blockTop - 4, 8, 48 * layout.k + r.lines.length * r.size * r.lh );
					rx = P + 36;
				}
				ctx.fillStyle = cream;
				ctx.font = font( r.weight, r.size );
				r.lines.forEach( function ( l ) {
					ctx.fillText( l, rx, y );
					y += r.size * r.lh;
				} );
				y += r.after;
			} );

			ctx.fillStyle = cream;
			ctx.globalAlpha = 0.25;
			ctx.fillRect( P, H - P - 62, W - P * 2, 2 );
			ctx.globalAlpha = 1;
			ctx.font = font( 700, 28 );
			ctx.fillText( 'AI Awareness Day · Thursday 29 April 2027', P, H - P );
			ctx.textAlign = 'right';
			ctx.fillStyle = accent;
			ctx.font = font( 600, 26 );
			ctx.fillText( 'aiawarenessday.co.uk', W - P, H - P );
			ctx.textAlign = 'left';
			return canvas;
		}

		// Cards are drawn ahead of time (when the board shows one), so the
		// share sheet can open straight from the tap. Safari refuses to share
		// if the tap was followed by slow work such as drawing the card.
		var cardBlobs = {}, cardJobs = {};

		function cardFonts() {
			if ( ! document.fonts || ! document.fonts.load ) {
				return Promise.resolve();
			}
			var face = getComputedStyle( document.querySelector( '.alpt-tile__sym' ) || document.body ).fontFamily;
			return Promise.all( [ 400, 500, 600, 700 ].map( function ( w ) {
				return document.fonts.load( w + ' 40px ' + face ).catch( function () {} );
			} ) );
		}

		function prepareCard( index, mode ) {
			var key = mode + ':' + index;
			if ( ! cardJobs[ key ] ) {
				cardJobs[ key ] = cardFonts().then( function () {
					return new Promise( function ( resolve ) {
						( 'compound' === mode ? drawCompoundCard( index ) : drawCard( index ) ).toBlob( resolve, 'image/png' );
					} );
				} ).then( function ( blob ) {
					if ( blob ) {
						cardBlobs[ key ] = blob;
					} else {
						delete cardJobs[ key ];
					}
					return blob;
				} );
			}
			return cardJobs[ key ];
		}

		function shareCard( index, button, mode ) {
			var compound = 'compound' === mode;
			var item = compound ? compounds[ index ] : elements[ index ];
			var title = item[ 1 ];
			var about = compound ? item[ 5 ] : item[ 3 ];
			var slug = compound ? item[ 0 ] : item[ 0 ].toLowerCase();
			var name = 'ai-literacy-periodic-table-' + ( compound ? 'compound-' : '' ) + slug + '.png';
			var url = window.location.origin + window.location.pathname + ( compound ? '#alpt-cp-' : '#element-' ) + slug;
			var label = button ? button.textContent : '';
			var flash = function ( text ) {
				if ( ! button ) {
					return;
				}
				button.textContent = text;
				window.setTimeout( function () {
					if ( button.isConnected ) {
						button.textContent = label;
					}
				}, 2500 );
			};
			var download = function ( blob ) {
				var link = document.createElement( 'a' );
				link.href = URL.createObjectURL( blob );
				link.download = name;
				document.body.appendChild( link );
				link.click();
				link.remove();
				window.setTimeout( function () {
					URL.revokeObjectURL( link.href );
				}, 4000 );
				flash( 'Card downloaded' );
				boardSay.textContent = 'Card for ' + title + ' downloaded.';
			};
			var send = function ( blob ) {
				if ( ! canShareFiles() ) {
					download( blob );
					return;
				}
				var file = new File( [ blob ], name, { type: 'image/png' } );
				var first = String( about ).split( /(?<=[.!?])\s/ )[ 0 ];
				navigator.share( { files: [ file ], title: title + ' | The AI Literacy Periodic Table', text: title + ': ' + first + ' ' + url } ).catch( function ( err ) {
					// Closing the share sheet is fine; anything else falls back to a download.
					if ( ! err || 'AbortError' !== err.name ) {
						download( blob );
					}
				} );
			};
			var ready = cardBlobs[ mode + ':' + index ];
			if ( ready ) {
				send( ready );
				return;
			}
			flash( 'Making the card…' );
			prepareCard( index, mode ).then( function ( blob ) {
				if ( ! blob ) {
					flash( 'Card could not be made' );
					return;
				}
				if ( button ) {
					button.textContent = label;
				}
				send( blob );
			} );
		}

		function renderBoardElement() {
			var e = elements[ boardIndex ];
			clearBoard();
			var wrap = el( 'div', 'alpt-board__inner' );
			var tile = el( 'div', 'alpt-board__tile alpt-fam--' + e[ 2 ] + ' ' + themed( e[ 5 ] ) );
			tile.appendChild( el( 'span', 'alpt-board__n', String( boardIndex + 1 ) ) );
			tile.appendChild( el( 'span', 'alpt-board__sym', e[ 0 ] ) );
			tile.appendChild( el( 'span', 'alpt-board__tile-name', e[ 1 ] ) );
			var text = el( 'div', 'alpt-board__text' );
			text.appendChild( el( 'p', 'alpt-board__label', ( families[ e[ 2 ] ] || '' ) + ' · ' + ( themes[ e[ 5 ] ] || '' ) + ' theme' ) );
			text.appendChild( el( 'h2', 'alpt-board__title', e[ 1 ] ) );
			if ( boardHideDef ) {
				text.appendChild( el( 'p', 'alpt-board__prompt', 'What do you think it means?' ) );
			} else {
				text.appendChild( el( 'p', 'alpt-board__def', e[ 3 ] ) );
				if ( talk[ e[ 0 ] ] ) {
					var q = el( 'p', 'alpt-board__talk' );
					q.appendChild( el( 'span', 'alpt-board__talk-label', 'Talk about it' ) );
					q.appendChild( document.createTextNode( talk[ e[ 0 ] ] ) );
					text.appendChild( q );
				}
			}
			if ( safeSyms.indexOf( e[ 0 ] ) !== -1 ) {
				text.appendChild( el( 'p', 'alpt-board__safe', SAFE_NOTE ) );
			}
			wrap.appendChild( tile );
			wrap.appendChild( text );
			board.appendChild( wrap );
			var bar = el( 'div', 'alpt-board__bar' );
			var prev = boardButton( '', 'prev', 'alpt-board__btn--prev' );
			prev.innerHTML = '<span aria-hidden="true">←</span> <span class="alpt-board__btn-word">Previous</span>';
			prev.setAttribute( 'aria-label', 'Previous' );
			var next = boardButton( '', 'next', 'alpt-board__btn--next' );
			next.innerHTML = '<span class="alpt-board__btn-word">Next</span> <span aria-hidden="true">→</span>';
			next.setAttribute( 'aria-label', 'Next' );
			bar.appendChild( prev );
			bar.appendChild( next );
			bar.appendChild( boardButton( boardHideDef ? 'Show definition' : 'Hide definition', 'toggle', 'alpt-board__btn--toggle' ) );
			bar.appendChild( boardButton( canShareFiles() ? 'Share as a card' : 'Download as a card', 'card', 'alpt-board__btn--card' ) );
			bar.appendChild( boardButton( 'Close', 'close', 'alpt-board__btn--close' ) );
			board.appendChild( bar );
		}

		function renderBoardCompound() {
			var c = compounds[ boardIndex ];
			clearBoard();
			var wrap = el( 'div', 'alpt-board__inner alpt-board__inner--compound' );
			var text = el( 'div', 'alpt-board__text' );
			text.appendChild( el( 'p', 'alpt-board__label', ( c[ 3 ] ? 'Volatile compound' : 'Stable compound' ) + ' · ' + ( themes[ c[ 7 ] ] || '' ) + ' theme' ) );
			text.appendChild( el( 'h2', 'alpt-board__title', c[ 1 ] ) );
			var formula = el( 'ul', 'alpt-board__formula' );
			c[ 2 ].forEach( function ( sym ) {
				var e = elements[ symIndex[ sym ] ];
				if ( ! e ) {
					return;
				}
				var li = el( 'li', 'alpt-board__el alpt-fam--' + e[ 2 ] + ' ' + themed( e[ 5 ] ) );
				li.appendChild( el( 'span', 'alpt-board__el-sym', e[ 0 ] ) );
				// The tile's own name, which has soft hyphens in its long words, so a narrow tile breaks them cleanly.
				var tileName = root.querySelector( '[data-alpt-el="' + symIndex[ sym ] + '"] .alpt-tile__name' );
				li.appendChild( el( 'span', 'alpt-board__el-name', tileName ? tileName.textContent : e[ 1 ] ) );
				formula.appendChild( li );
			} );
			text.appendChild( formula );
			if ( boardHideDef ) {
				text.appendChild( el( 'p', 'alpt-board__prompt', 'What could this AI system be?' ) );
			} else {
				text.appendChild( el( 'p', 'alpt-board__def', c[ 5 ] ) );
				var q = el( 'p', 'alpt-board__talk' );
				q.appendChild( el( 'span', 'alpt-board__talk-label', 'Debate it' ) );
				q.appendChild( document.createTextNode( c[ 6 ] ) );
				text.appendChild( q );
			}
			wrap.appendChild( text );
			board.appendChild( wrap );
			var bar = el( 'div', 'alpt-board__bar' );
			bar.appendChild( boardButton( boardHideDef ? 'Show the answer' : 'Hide the answer', 'toggle', 'alpt-board__btn--toggle' ) );
			bar.appendChild( boardButton( canShareFiles() ? 'Share as a card' : 'Download as a card', 'card', 'alpt-board__btn--card' ) );
			bar.appendChild( boardButton( 'Close', 'close', 'alpt-board__btn--close' ) );
			board.appendChild( bar );
		}

		function renderBoard() {
			if ( 'compound' === boardMode ) {
				renderBoardCompound();
			} else {
				renderBoardElement();
			}
			// Draw this card in the background so Share can open at once.
			var mode = boardMode, index = boardIndex;
			( window.requestIdleCallback || window.setTimeout )( function () {
				prepareCard( index, mode );
			} );
		}

		function openBoard( mode, index, opener ) {
			boardMode = mode;
			boardIndex = index;
			boardHideDef = 'element' === mode ? boardPrefHide : false;
			boardOpener = opener || null;
			renderBoard();
			board.hidden = false;
			document.documentElement.classList.add( 'alpt-board-open' );
			if ( board.requestFullscreen ) {
				board.requestFullscreen().catch( function () {} );
			}
			focusBoardTitle();
		}

		// Focus the title, so a screen reader starts with what is on the board.
		function focusBoardTitle() {
			var title = board.querySelector( '.alpt-board__title' );
			if ( title ) {
				title.tabIndex = -1;
				title.focus();
			} else {
				board.focus();
			}
		}

		function closeBoard() {
			if ( board.hidden ) {
				return;
			}
			if ( document.fullscreenElement === board && document.exitFullscreen ) {
				document.exitFullscreen().catch( function () {} );
			}
			board.hidden = true;
			document.documentElement.classList.remove( 'alpt-board-open' );
			if ( 'element' === boardMode ) {
				show( boardIndex );
			}
			if ( boardOpener ) {
				var opener = boardOpener;
				opener.focus();
				// Leaving full screen can swallow that focus, so give it back once the browser has left it.
				window.setTimeout( function () { opener.focus(); }, 150 );
			}
		}

		function stepBoard( dir ) {
			if ( 'element' !== boardMode ) {
				return;
			}
			// The board is rebuilt, so put focus back on the same button (Previous or Next), or on the title.
			var was = document.activeElement && document.activeElement.getAttribute ? document.activeElement.getAttribute( 'data-alpt-board-action' ) : null;
			boardIndex = ( boardIndex + dir + elements.length ) % elements.length;
			renderBoard();
			var again = was ? board.querySelector( '[data-alpt-board-action="' + was + '"]' ) : null;
			if ( again ) {
				again.focus();
			} else {
				focusBoardTitle();
			}
			boardSay.textContent = ( boardIndex + 1 ) + ' of ' + elements.length + ': ' + elements[ boardIndex ][ 1 ] + '.';
		}

		board.addEventListener( 'click', function ( event ) {
			var b = event.target.closest( '[data-alpt-board-action]' );
			if ( ! b ) {
				return;
			}
			var action = b.getAttribute( 'data-alpt-board-action' );
			if ( 'close' === action ) {
				closeBoard();
			} else if ( 'prev' === action ) {
				stepBoard( -1 );
			} else if ( 'next' === action ) {
				stepBoard( 1 );
			} else if ( 'card' === action ) {
				shareCard( boardIndex, b, boardMode );
			} else if ( 'toggle' === action ) {
				boardHideDef = ! boardHideDef;
				if ( 'element' === boardMode ) {
					boardPrefHide = boardHideDef;
				}
				renderBoard();
				var again = board.querySelector( '[data-alpt-board-action="toggle"]' );
				if ( again ) {
					again.focus();
				}
			}
		} );
		board.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				event.stopPropagation();
				closeBoard();
			} else if ( 'ArrowRight' === event.key ) {
				stepBoard( 1 );
			} else if ( 'ArrowLeft' === event.key ) {
				stepBoard( -1 );
			} else if ( 'Tab' === event.key ) {
				// Keep focus on the board's buttons while it is open.
				var buttons = Array.prototype.slice.call( board.querySelectorAll( 'button' ) );
				if ( ! buttons.length ) {
					return;
				}
				var first = buttons[ 0 ];
				var last = buttons[ buttons.length - 1 ];
				// From the board itself or its title (not buttons), Tab goes to the first button, Shift+Tab to the last.
				if ( buttons.indexOf( document.activeElement ) === -1 ) {
					event.preventDefault();
					( event.shiftKey ? last : first ).focus();
				} else if ( event.shiftKey && document.activeElement === first ) {
					event.preventDefault();
					last.focus();
				} else if ( ! event.shiftKey && document.activeElement === last ) {
					event.preventDefault();
					first.focus();
				}
			}
		} );
		// Leaving full screen with the browser's own Esc closes the board too.
		document.addEventListener( 'fullscreenchange', function () {
			if ( ! document.fullscreenElement && ! board.hidden ) {
				closeBoard();
			}
		} );

		var boardElementButton = root.querySelector( '[data-alpt-board-element]' );
		if ( boardElementButton ) {
			boardElementButton.addEventListener( 'click', function () {
				openBoard( 'element', current, boardElementButton );
			} );
		}
		// The element of the week can go straight on the board.
		// It sits in the page's introduction, above the table, so it is found in the whole document.
		var weekBoard = document.querySelector( '[data-alpt-eotw-board]' );
		var weekLink = document.querySelector( '.alpt-eotw a[href^="#element-"]' );
		if ( weekLink ) {
			weekLink.addEventListener( 'click', function () {
				window.setTimeout( fromHash, 0 );
			} );
		}
		if ( weekBoard ) {
			weekBoard.hidden = false;
			weekBoard.addEventListener( 'click', function () {
				var i = parseInt( weekBoard.getAttribute( 'data-alpt-eotw-board' ), 10 );
				show( i );
				openBoard( 'element', i, weekBoard );
			} );
		}

		// Copy link: the address of the element in the panel, to paste into a slide, an email or the staff bulletin.
		// Print element cards: twelve to an A4 page, for the elements showing now, so a filter or search prints just
		// that family, theme, era or word. Each card has the symbol, name, family, definition and Talk about it question.
		var FAMILY_CODES = { foundations: 'F', generative: 'G', predictive: 'P', agents: 'A', automation: 'W', governance: 'E', infra: 'I' };
		function printCards() {
			var sheet = document.createElement( 'div' );
			sheet.className = 'alpt-cards-print';
			elements.forEach( function ( e, i ) {
				if ( tiles[ i ].classList.contains( 'is-excluded' ) ) {
					return;
				}
				var card = el( 'div', 'alpt-card alpt-fam--' + e[ 2 ] );
				var head = el( 'div', 'alpt-card__head' );
				head.appendChild( el( 'span', 'alpt-card__sym', e[ 0 ] ) );
				var fam = el( 'span', 'alpt-card__family', families[ e[ 2 ] ] || '' );
				fam.appendChild( el( 'span', 'alpt-card__code', FAMILY_CODES[ e[ 2 ] ] || '' ) );
				head.appendChild( fam );
				card.appendChild( head );
				card.appendChild( el( 'p', 'alpt-card__name', e[ 1 ] ) );
				card.appendChild( el( 'p', 'alpt-card__def', e[ 3 ] ) );
				if ( talk[ e[ 0 ] ] ) {
					card.appendChild( el( 'p', 'alpt-card__talk', 'Talk about it: ' + talk[ e[ 0 ] ] ) );
				}
				if ( safeSyms.indexOf( e[ 0 ] ) !== -1 ) {
					card.appendChild( el( 'p', 'alpt-card__foot alpt-card__safe', 'Safeguarding: follow your school\'s procedures. Childline: 0800 1111.' ) );
				}
				card.appendChild( el( 'p', 'alpt-card__foot', 'The AI Literacy Periodic Table · AI Awareness Day' ) );
				sheet.appendChild( card );
			} );
			document.body.appendChild( sheet );
			// A4 portrait for the cards, also for browsers that don't support the stylesheet's named page.
			var pageSize = document.createElement( 'style' );
			pageSize.textContent = '@page { size: A4 portrait; margin: 10mm; }';
			document.head.appendChild( pageSize );
			document.documentElement.classList.add( 'alpt-printing-cards' );
			var done = function () {
				document.documentElement.classList.remove( 'alpt-printing-cards' );
				sheet.remove();
				pageSize.remove();
				window.removeEventListener( 'afterprint', done );
			};
			window.addEventListener( 'afterprint', done );
			window.print();
		}
		var printCardsButton = root.querySelector( '[data-alpt-print-cards]' );
		if ( printCardsButton ) {
			printCardsButton.hidden = false;
			printCardsButton.addEventListener( 'click', printCards );
		}
		document.addEventListener( 'click', function ( event ) {
			if ( event.target.closest( 'a[href="#alpt-print-cards"]' ) ) {
				event.preventDefault();
				printCards();
			}
		} );

		// Share: the device's own share sheet where there is one (phones, Safari), otherwise a row of share links
		// (LinkedIn, X, Facebook, WhatsApp, email, copy link) under the element.
		var shareButton = root.querySelector( '[data-alpt-share]' );
		var shareMenu = root.querySelector( '[data-alpt-share-menu]' );
		var copyButton = root.querySelector( '[data-alpt-copy]' );
		var shareInfo = function () {
			var el = elements[ current ];
			return {
				url: window.location.origin + window.location.pathname + '#element-' + el[ 0 ].toLowerCase(),
				title: el[ 0 ] + ' · ' + el[ 1 ] + ' | The AI Literacy Periodic Table',
				text: el[ 1 ] + ': ' + el[ 3 ] + ' From the AI Literacy Periodic Table by AI Awareness Day.'
			};
		};
		var closeShare = function ( refocus ) {
			if ( ! shareMenu || shareMenu.hidden ) {
				return;
			}
			shareMenu.hidden = true;
			shareButton.setAttribute( 'aria-expanded', 'false' );
			if ( refocus ) {
				shareButton.focus();
			}
		};
		var fillShare = function () {
			var info = shareInfo();
			var u = encodeURIComponent( info.url );
			var t = encodeURIComponent( info.title );
			var links = {
				linkedin: 'https://www.linkedin.com/sharing/share-offsite/?url=' + u,
				x: 'https://x.com/intent/post?text=' + encodeURIComponent( info.text ) + '&url=' + u,
				facebook: 'https://www.facebook.com/sharer/sharer.php?u=' + u,
				whatsapp: 'https://wa.me/?text=' + encodeURIComponent( info.text + ' ' + info.url ),
				email: 'mailto:?subject=' + t + '&body=' + encodeURIComponent( info.text + '\n\n' + info.url )
			};
			Array.prototype.forEach.call( shareMenu.querySelectorAll( '[data-alpt-share-to]' ), function ( a ) {
				a.href = links[ a.getAttribute( 'data-alpt-share-to' ) ];
			} );
		};
		if ( shareButton && shareMenu ) {
			shareButton.addEventListener( 'click', function () {
				var info = shareInfo();
				if ( navigator.share && ( ! navigator.canShare || navigator.canShare( { url: info.url } ) ) ) {
					navigator.share( { title: info.title, text: info.text, url: info.url } ).catch( function () {} );
					return;
				}
				if ( shareMenu.hidden ) {
					fillShare();
					shareMenu.hidden = false;
					shareButton.setAttribute( 'aria-expanded', 'true' );
					var first = shareMenu.querySelector( 'a, button' );
					if ( first ) {
						first.focus();
					}
				} else {
					closeShare( true );
				}
			} );
			[ shareMenu, shareButton ].forEach( function ( node ) {
				node.addEventListener( 'keydown', function ( event ) {
					if ( 'Escape' === event.key && ! shareMenu.hidden ) {
						event.stopPropagation();
						closeShare( true );
					}
				} );
				// Tabbing away from the button and the menu closes it.
				node.addEventListener( 'focusout', function ( event ) {
					var to = event.relatedTarget;
					if ( to && ! shareMenu.contains( to ) && to !== shareButton ) {
						closeShare();
					}
				} );
			} );
			// So does a click anywhere else.
			document.addEventListener( 'click', function ( event ) {
				if ( ! shareMenu.hidden && ! shareMenu.contains( event.target ) && ! shareButton.contains( event.target ) ) {
					closeShare();
				}
			} );
			shareMenu.addEventListener( 'click', function ( event ) {
				if ( event.target.closest( '[data-alpt-share-to]' ) ) {
					window.setTimeout( closeShare, 0 );
				}
			} );
			// A different element, or closing the panel, closes the menu.
			var shareName = root.querySelector( '.alpt-panel [data-alpt-out="name"]' );
			if ( shareName && window.MutationObserver ) {
				new MutationObserver( function () {
					closeShare();
				} ).observe( shareName, { childList: true, characterData: true, subtree: true } );
			}
		}
		if ( copyButton ) {
			var copyLabel = copyButton.textContent;
			var copyTimer = null;
			copyButton.addEventListener( 'click', function () {
				var url = shareInfo().url;
				var done = function () {
					copyButton.textContent = 'Link copied';
					status.textContent = 'Link to ' + elements[ current ][ 1 ] + ' copied.';
					window.clearTimeout( copyTimer );
					copyTimer = window.setTimeout( function () {
						copyButton.textContent = copyLabel;
					}, 2500 );
				};
				if ( navigator.clipboard && window.isSecureContext !== false ) {
					navigator.clipboard.writeText( url ).then( done ).catch( function () {
						window.prompt( 'Copy this link:', url );
					} );
				} else {
					window.prompt( 'Copy this link:', url );
				}
			} );
		}

		if ( navigator.clipboard && window.isSecureContext !== false ) {
			Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-copy-compound]' ), function ( button ) {
				button.hidden = false;
				button.addEventListener( 'click', function () {
					var url = window.location.href.split( '#' )[ 0 ] + '#alpt-cp-' + button.getAttribute( 'data-alpt-copy-compound' );
					navigator.clipboard.writeText( url ).then( function () {
						button.textContent = 'Link copied';
						status.textContent = 'Link to the compound copied.';
						window.setTimeout( function () {
							button.textContent = 'Copy link';
						}, 2500 );
					} ).catch( function () {} );
				} );
			} );
		}
		Array.prototype.forEach.call( root.querySelectorAll( '[data-alpt-board-compound]' ), function ( button ) {
			button.hidden = false;
			button.addEventListener( 'click', function () {
				var id = button.getAttribute( 'data-alpt-board-compound' );
				for ( var k = 0; k < compounds.length; k++ ) {
					if ( compounds[ k ][ 0 ] === id ) {
						openBoard( 'compound', k, button );
						return;
					}
				}
			} );
		} );
	}

	// "Suggest a new element": sent to aiad/v1/literacy-term (inc/literacy-table.php), which saves it for the team.
	function initBuzzwordQuiz( root, elements, indexes, quiz ) {
		var section = root.querySelector( '.alpt-quiz' );
		if ( ! section ) return;
		// Each scenario's first symbol is the answer. The order on screen varies by question.
		// The questions live in data/literacy-table/lists.json ("quiz"): the right answer first, then two others.
		// Any question whose symbols aren't on the table is left out, so renaming an element can't break the quiz.
		var questions = ( quiz || [] ).filter( function ( q ) {
			return q.length === 4 && q.slice( 1 ).every( function ( sym ) {
				return undefined !== indexes[ sym ];
			} );
		} );
		if ( questions.length < 3 ) {
			return;
		}
		// Each round deals ten of the questions, in a new order; the best score is remembered on this device.
		var bank = questions.slice();
		var ROUND = Math.min( 10, bank.length );
		function deal() {
			for ( var i = bank.length - 1; i > 0; i-- ) {
				var j = Math.floor( Math.random() * ( i + 1 ) );
				var swap = bank[ i ]; bank[ i ] = bank[ j ]; bank[ j ] = swap;
			}
			questions = bank.slice( 0, ROUND );
		}
		deal();
		var bestOut = section.querySelector( '[data-quiz-best]' );
		var BEST_KEY = 'alpt-quiz-best-v1';
		function readBest() {
			try {
				return parseInt( window.localStorage.getItem( BEST_KEY ), 10 ) || 0;
			} catch ( e ) {
				return 0;
			}
		}
		function showBest() {
			var best = readBest();
			if ( bestOut && best ) {
				bestOut.textContent = 'Your best so far: ' + best + ' out of ' + ROUND + '.';
				bestOut.hidden = false;
			}
		}
		showBest();
		var question = section.querySelector( '[data-quiz-question]' );
		var options = section.querySelector( '[data-quiz-options]' );
		var progress = section.querySelector( '[data-quiz-progress]' );
		var feedback = section.querySelector( '[data-quiz-feedback]' );
		var next = section.querySelector( '[data-quiz-next]' );
		var scoreOut = section.querySelector( '[data-quiz-score]' );
		var bar = section.querySelector( '[data-quiz-bar]' );
		var position = 0;
		var score = 0;
		var answered = false;

		function display() {
			answered = false;
			options.replaceChildren();
			feedback.textContent = '';
			feedback.className = 'alpt-quiz__feedback';
			if ( scoreOut ) {
				scoreOut.textContent = String( score );
			}
			if ( bar ) {
				bar.style.width = ( Math.min( position, questions.length ) / questions.length * 100 ) + '%';
			}
			section.classList.toggle( 'is-done', ! questions[ position ] );
			var item = questions[ position ];
			if ( ! item ) {
				progress.textContent = 'Quiz complete';
				var before = readBest();
				question.textContent = 'You scored ' + score + ' out of ' + questions.length + '.' + ( score > before ? ( before ? ' A new best!' : '' ) : ' Your best is ' + before + '.' );
				if ( score > before ) {
					try {
						window.localStorage.setItem( BEST_KEY, String( score ) );
					} catch ( e ) {}
				}
				showBest();
				next.textContent = 'Try again';
				next.hidden = false;
				return;
			}
			progress.textContent = 'Question ' + ( position + 1 ) + ' of ' + questions.length;
			question.textContent = item[ 0 ];
			next.hidden = true;
			var choices = item.slice( 1 );
			for ( var i = choices.length - 1; i > 0; i-- ) {
				var j = Math.floor( Math.random() * ( i + 1 ) );
				var swap = choices[ i ]; choices[ i ] = choices[ j ]; choices[ j ] = swap;
			}
			choices.forEach( function ( symbol ) {
				var button = document.createElement( 'button' );
				button.type = 'button';
				button.className = 'alpt-quiz__option';
				button.textContent = elements[ indexes[ symbol ] ][ 1 ];
				button.addEventListener( 'click', function () {
					if ( answered ) return;
					answered = true;
					var correct = symbol === item[ 1 ];
					if ( correct ) score++;
					if ( scoreOut ) {
						scoreOut.textContent = String( score );
					}
					if ( bar ) {
						bar.style.width = ( ( position + 1 ) / questions.length * 100 ) + '%';
					}
					var term = elements[ indexes[ item[ 1 ] ] ];
					feedback.textContent = ( correct ? 'Correct. ' : 'The answer is ' + term[ 1 ] + '. ' ) + term[ 3 ];
					feedback.className = 'alpt-quiz__feedback ' + ( correct ? 'is-right' : 'is-wrong' );
					if ( ! correct ) {
						button.classList.add( 'is-wrong' );
					}
					Array.from( options.children ).forEach( function ( choice ) {
						choice.setAttribute( 'aria-disabled', 'true' );
						choice.tabIndex = choice === button ? 0 : -1;
						if ( choice.textContent === term[ 1 ] ) choice.classList.add( 'is-answer' );
					} );
					next.textContent = position === questions.length - 1 ? 'See my score' : 'Next question';
					next.hidden = false;
				} );
				options.appendChild( button );
			} );
		}
		next.addEventListener( 'click', function () {
			if ( position === questions.length ) { position = 0; score = 0; deal(); }
			else position++;
			display();
			question.focus();
		} );
		display();
		section.hidden = false;
		// The quiz is listed under Try it, below the table, rather than with the introduction's buttons.
	}

	function initSuggest( form ) {
		form.hidden = false;
		var status = form.querySelector( '[data-alpt-suggest-status]' );
		var button = form.querySelector( 'button[type="submit"]' );
		// How long the visitor spent on the form: a person needs more than a few seconds to write a definition.
		var started = 0;
		form.addEventListener( 'focusin', function () {
			started = started || Date.now();
		} );
		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
			var term = form.elements.term.value.trim();
			var definition = form.elements.definition.value.trim();
			// A field with a problem is marked invalid and points at the message, so a screen reader says what to fix.
			status.id = status.id || 'alpt-suggest-status';
			[ form.elements.term, form.elements.definition ].forEach( function ( field ) {
				field.removeAttribute( 'aria-invalid' );
				field.removeAttribute( 'aria-describedby' );
			} );
			function invalid( field, message ) {
				status.textContent = message;
				field.setAttribute( 'aria-invalid', 'true' );
				field.setAttribute( 'aria-describedby', status.id );
				field.focus();
			}
			if ( term.length < 2 ) {
				invalid( form.elements.term, 'Please enter the term you would like to add.' );
				return;
			}
			if ( definition.length < 10 ) {
				invalid( form.elements.definition, 'Please explain what the term means, in a sentence or two.' );
				return;
			}
			var body = {};
			[ 'term', 'symbol', 'family', 'definition', 'why', 'name', 'role', 'credit', 'aiad_website' ].forEach( function ( key ) {
				body[ key ] = form.elements[ key ] ? form.elements[ key ].value : '';
			} );
			body.elapsed = started ? Date.now() - started : 0;
			button.disabled = true;
			status.textContent = 'Sending…';
			window.fetch( form.getAttribute( 'data-endpoint' ), {
				method: 'POST',
				headers: { 'Content-Type': 'application/json' },
				credentials: 'same-origin',
				body: JSON.stringify( body )
			} ).then( function ( response ) {
				return response.json().then( function ( json ) {
					return { ok: response.ok, json: json };
				} );
			} ).then( function ( result ) {
				button.disabled = false;
				status.textContent = ( result.json && result.json.message ) || ( result.ok ? 'Thank you!' : 'Sorry, that didn\'t send. Please try again.' );
				if ( result.ok ) {
					form.reset();
					started = 0;
				}
			} ).catch( function () {
				button.disabled = false;
				status.textContent = 'Sorry, that didn\'t send. Check your connection and try again.';
			} );
		} );
	}

	function ready() {
		Array.prototype.forEach.call( document.querySelectorAll( '[data-alpt]' ), init );
		Array.prototype.forEach.call( document.querySelectorAll( '[data-alpt-suggest]' ), initSuggest );

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

/*
 * Phone navigation (under 768px): a dark bar, like the benchmark's app bar, that appears once the hero has scrolled
 * away. It names the section you are in, shows how far down the page you are, and opens a "Jump to" menu with every
 * family (or theme, or era, in the other views) and every section below the table. A search button goes straight
 * back to the search box.
 */
( function () {
	'use strict';
	var root = document.querySelector( '.alpt' );
	if ( ! root || ! window.matchMedia ) {
		return;
	}
	var phone = window.matchMedia( '(max-width: 767px)' );
	var sections = [
		[ '.alpt-tryit__title', 'Try it' ],
		[ '#alpt-compounds-title', 'Compounds' ],
		[ '#alpt-challenge-title', 'Compound challenge' ],
		[ '#alpt-buzzword-check', 'Buzzword check' ],
		[ '#alpt-glossary-title', 'Glossary' ],
		[ '#alpt-issues', 'The issues they raise' ],
		[ '#alpt-suggest-title', 'Suggest an element' ],
		[ '#alpt-sessions', 'Ready-made sessions' ],
		[ '#alpt-classroom', 'Classroom display' ],
		[ '#alpt-close', 'AI Awareness Day' ]
	];
	var controls = root.querySelector( '.alpt-controls' );
	var search = root.querySelector( '.alpt-controls input[type="search"], .alpt-controls input' );
	var bar = null, menu = null, here = null, fill = null, toggle = null, built = false, ticking = false;

	function el( tag, cls, text ) {
		var n = document.createElement( tag );
		if ( cls ) {
			n.className = cls;
		}
		if ( text ) {
			n.textContent = text;
		}
		return n;
	}

	function headerOffset() {
		var head = document.querySelector( '.site-header' );
		var admin = document.getElementById( 'wpadminbar' );
		var h = 0;
		if ( head && 'fixed' === getComputedStyle( head ).position ) {
			h += head.getBoundingClientRect().bottom > 0 ? head.offsetHeight : 0;
		}
		if ( admin && 'fixed' === getComputedStyle( admin ).position ) {
			h += admin.offsetHeight;
		}
		return h;
	}

	function targets() {
		var list = [];
		if ( controls ) {
			list.push( { node: controls, label: 'Search and filters', group: 'table' } );
		}
		root.querySelectorAll( '.alpt-family-label' ).forEach( function ( label ) {
			if ( ! label.offsetParent ) {
				return;
			}
			var name = label.querySelector( '.alpt-family-label__name' ) || label;
			var cls = ( label.className.match( /alpt-(fam|th)--[a-z0-9-]+/ ) || [ '' ] )[ 0 ];
			list.push( { node: label, label: name.textContent.trim(), group: 'table', cls: cls } );
		} );
		sections.forEach( function ( s ) {
			var node = document.querySelector( s[ 0 ] );
			if ( node && node.offsetParent ) {
				list.push( { node: node, label: s[ 1 ], group: 'page' } );
			}
		} );
		return list;
	}

	function go( node ) {
		closeMenu();
		var top = node.getBoundingClientRect().top + window.pageYOffset - headerOffset() - bar.offsetHeight - 12;
		window.scrollTo( { top: Math.max( 0, top ), behavior: window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth' } );
	}

	function fillMenu() {
		menu.textContent = '';
		var groups = { table: el( 'ul', 'alpt-mnav__list' ), page: el( 'ul', 'alpt-mnav__list' ) };
		targets().forEach( function ( t ) {
			var li = el( 'li' );
			var b = el( 'button', 'alpt-mnav__item' );
			b.type = 'button';
			if ( t.cls ) {
				var sw = el( 'span', 'alpt-chip__swatch ' + t.cls );
				sw.setAttribute( 'aria-hidden', 'true' );
				b.appendChild( sw );
			}
			b.appendChild( el( 'span', '', t.label ) );
			b.addEventListener( 'click', function () {
				go( t.node );
			} );
			li.appendChild( b );
			groups[ t.group ].appendChild( li );
		} );
		menu.appendChild( el( 'p', 'alpt-mnav__group', 'The table' ) );
		menu.appendChild( groups.table );
		menu.appendChild( el( 'p', 'alpt-mnav__group', 'On this page' ) );
		menu.appendChild( groups.page );
	}

	function openMenu() {
		fillMenu();
		menu.hidden = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		bar.classList.add( 'is-open' );
		var first = menu.querySelector( 'button' );
		if ( first ) {
			first.focus();
		}
	}

	function closeMenu( refocus ) {
		if ( ! menu || menu.hidden ) {
			return;
		}
		menu.hidden = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
		bar.classList.remove( 'is-open' );
		if ( refocus ) {
			toggle.focus();
		}
	}

	function build() {
		bar = el( 'nav', 'alpt-mnav' );
		bar.setAttribute( 'aria-label', 'Periodic table sections' );
		var row = el( 'div', 'alpt-mnav__row' );
		var mark = el( 'span', 'alpt-mnav__mark', 'Pt' );
		mark.setAttribute( 'aria-hidden', 'true' );
		var text = el( 'span', 'alpt-mnav__text' );
		text.appendChild( el( 'span', 'alpt-mnav__title', 'Periodic Table' ) );
		here = el( 'span', 'alpt-mnav__here' );
		here.setAttribute( 'aria-live', 'off' );
		text.appendChild( here );
		var find = el( 'button', 'alpt-mnav__btn alpt-mnav__btn--search' );
		find.type = 'button';
		find.setAttribute( 'aria-label', 'Search the table' );
		find.innerHTML = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><circle cx="10.5" cy="10.5" r="6.5" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M15.5 15.5 21 21" stroke="currentColor" stroke-width="2.4" stroke-linecap="square"/></svg>';
		find.addEventListener( 'click', function () {
			if ( ! search ) {
				return;
			}
			go( controls || search );
			window.setTimeout( function () {
				search.focus( { preventScroll: true } );
			}, 450 );
		} );
		toggle = el( 'button', 'alpt-mnav__btn alpt-mnav__btn--jump' );
		toggle.type = 'button';
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-controls', 'alpt-mnav-menu' );
		toggle.innerHTML = '<span>Jump to</span><svg viewBox="0 0 12 12" width="12" height="12" aria-hidden="true" focusable="false"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="2"/></svg>';
		toggle.addEventListener( 'click', function () {
			if ( menu.hidden ) {
				openMenu();
			} else {
				closeMenu( true );
			}
		} );
		row.appendChild( mark );
		row.appendChild( text );
		row.appendChild( find );
		row.appendChild( toggle );
		var track = el( 'span', 'alpt-mnav__track' );
		track.setAttribute( 'aria-hidden', 'true' );
		fill = el( 'span', 'alpt-mnav__fill' );
		track.appendChild( fill );
		menu = el( 'div', 'alpt-mnav__menu' );
		menu.id = 'alpt-mnav-menu';
		menu.hidden = true;
		bar.appendChild( row );
		bar.appendChild( track );
		bar.appendChild( menu );
		document.body.appendChild( bar );
		document.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && ! menu.hidden ) {
				closeMenu( true );
			}
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( ! menu.hidden && ! bar.contains( e.target ) ) {
				closeMenu();
			}
		} );
		built = true;
	}

	function update() {
		ticking = false;
		if ( ! phone.matches ) {
			if ( bar ) {
				bar.classList.remove( 'is-shown' );
				closeMenu();
			}
			return;
		}
		if ( ! built ) {
			build();
		}
		var start = controls || root;
		var shown = start.getBoundingClientRect().top < headerOffset() + 40;
		// Out of the way while the challenge bar or the classroom board is open.
		if ( document.documentElement.classList.contains( 'alpt-lab-open' ) || document.querySelector( '.alpt-board:not([hidden])' ) ) {
			shown = false;
		}
		bar.style.top = headerOffset() + 'px';
		bar.classList.toggle( 'is-shown', shown );
		bar.inert = ! shown;
		if ( ! shown ) {
			closeMenu();
		}
		var max = document.documentElement.scrollHeight - window.innerHeight;
		fill.style.transform = 'scaleX(' + ( max > 0 ? Math.min( 1, window.pageYOffset / max ) : 0 ) + ')';
		var line = headerOffset() + bar.offsetHeight + 24, current = '';
		targets().forEach( function ( t ) {
			if ( t.node.getBoundingClientRect().top <= line ) {
				current = t.label;
			}
		} );
		// While an element is open and its card crosses the line under the bar, name the element instead.
		var panel = root.classList.contains( 'is-detail-open' ) ? root.querySelector( '.alpt-panel' ) : null;
		if ( panel && panel.offsetParent ) {
			var box = panel.getBoundingClientRect();
			var name = panel.querySelector( '[data-alpt-out="name"]' );
			var sym = panel.querySelector( '[data-alpt-out="sym"]' );
			if ( name && box.top < window.innerHeight * 0.5 && box.bottom > line + 80 ) {
				current = ( sym && sym.textContent.trim() ? sym.textContent.trim() + ' · ' : '' ) + name.textContent.trim();
			}
		}
		if ( here.textContent !== current ) {
			here.textContent = current;
		}
	}

	function onScroll() {
		if ( ! ticking ) {
			ticking = true;
			window.requestAnimationFrame( update );
		}
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll );
	// Previous, Next and the arrow keys change the open element without scrolling.
	var shownName = root.querySelector( '.alpt-panel [data-alpt-out="name"]' );
	if ( shownName && window.MutationObserver ) {
		new MutationObserver( onScroll ).observe( shownName, { childList: true, characterData: true, subtree: true } );
	}
	if ( phone.addEventListener ) {
		phone.addEventListener( 'change', onScroll );
	}
	onScroll();
}() );
