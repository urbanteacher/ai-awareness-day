/* Admin: fetch card image from LoremFlickr using keywords */
jQuery( function ( $ ) {
	$( document ).on( 'click', '.aiad-fetch-image-btn', function () {
		var btn      = $( this );
		var postId   = btn.data( 'post-id' );
		var input    = btn.closest( 'p, .aiad-rd-section' ).find( '.aiad-image-keywords-input' );
		var keywords = input.val();
		var status   = btn.siblings( '.aiad-fetch-image-status' );

		if ( ! keywords ) {
			status.text( 'Enter keywords first.' ).css( 'color', '#cc0000' );
			return;
		}

		btn.prop( 'disabled', true ).text( 'Fetching…' );
		status.text( '' );

		$.post(
			ajaxurl,
			{
				action:   'aiad_fetch_card_image',
				post_id:  postId,
				keywords: keywords,
				nonce:    aiadImageFetch.nonce,
			},
			function ( response ) {
				btn.prop( 'disabled', false ).text( 'Fetch image' );
				if ( response.success ) {
					status.text( '✓ Featured image set.' ).css( 'color', '#00a32a' );
					if ( response.data.thumb_url ) {
						// Show inline preview
						btn.closest( 'p, .aiad-rd-section' ).find( '.aiad-image-preview' ).remove();
						btn.closest( 'p, .aiad-rd-section' ).append(
							'<div class="aiad-image-preview" style="margin-top:0.6rem;">' +
							'<img src="' + response.data.thumb_url + '" style="max-width:160px;height:auto;border-radius:4px;border:1px solid #ddd;" alt="Fetched card image" />' +
							'<p style="margin:0.3rem 0 0;font-size:11px;color:#666;">Saved as featured image. To replace manually, use the <strong>Featured Image</strong> panel on the right.</p>' +
							'</div>'
						);
						// Also update the sidebar featured image box if already visible
						var $sideImg = $( '#postimagediv .inside img' );
						if ( $sideImg.length ) {
							$sideImg.attr( 'src', response.data.thumb_url );
						}
					}
				} else {
					status.text( 'Error: ' + ( response.data || 'Unknown error' ) ).css( 'color', '#cc0000' );
				}
			}
		).fail( function () {
			btn.prop( 'disabled', false ).text( 'Fetch image' );
			status.text( 'Request failed.' ).css( 'color', '#cc0000' );
		} );
	} );
} );
