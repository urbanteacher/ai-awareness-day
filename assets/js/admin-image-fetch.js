/* Admin (classic screen): fetch card image from LoremFlickr using keywords, through the aiad/v1/card-image route. */
( function () {
	document.addEventListener( 'click', function ( event ) {
		var btn = event.target.closest( '.aiad-fetch-image-btn' );
		if ( ! btn ) {
			return;
		}
		var wrap = btn.closest( 'p, .aiad-rd-section' );
		var input = wrap ? wrap.querySelector( '.aiad-image-keywords-input' ) : null;
		var keywords = input ? input.value : '';
		var status = btn.parentNode.querySelector( '.aiad-fetch-image-status' );

		function say( text, colour ) {
			if ( status ) {
				status.textContent = text;
				status.style.color = colour;
			}
		}

		if ( ! keywords ) {
			say( 'Enter keywords first.', '#A32D2D' );
			return;
		}

		btn.disabled = true;
		btn.textContent = 'Fetching…';
		say( '', '' );

		window.wp.apiFetch( {
			path: '/aiad/v1/card-image/' + encodeURIComponent( btn.getAttribute( 'data-post-id' ) ),
			method: 'POST',
			data: { keywords: keywords },
		} )
			.then( function ( result ) {
				say( '✓ Featured image set.', '#176E3B' );
				if ( ! result.thumb_url || ! wrap ) {
					return;
				}
				var old = wrap.querySelector( '.aiad-image-preview' );
				if ( old ) {
					old.remove();
				}
				var preview = document.createElement( 'div' );
				preview.className = 'aiad-image-preview';
				preview.style.marginTop = '0.6rem';
				var img = document.createElement( 'img' );
				img.src = result.thumb_url;
				img.alt = 'Fetched card image';
				img.style.cssText = 'max-width:160px;height:auto;border-radius:4px;border:1px solid #EAE7DF;';
				var note = document.createElement( 'p' );
				note.style.cssText = 'margin:0.3rem 0 0;font-size:11px;color:#54504E;';
				note.textContent = 'Saved as featured image. To replace manually, use the Featured Image panel on the right.';
				preview.appendChild( img );
				preview.appendChild( note );
				wrap.appendChild( preview );
				// Also update the sidebar featured image box if already visible.
				var side = document.querySelector( '#postimagediv .inside img' );
				if ( side ) {
					side.src = result.thumb_url;
				}
			} )
			.catch( function ( error ) {
				say( 'Error: ' + ( ( error && error.message ) || 'Unknown error' ), '#A32D2D' );
			} )
			.then( function () {
				btn.disabled = false;
				btn.textContent = 'Fetch image';
			} );
	} );
} )();
