/**
 * Admin resource download file uploader (classic editor only).
 * Handles PDF/PPTX file selection using the WordPress media library. Plain DOM; the words come from wp.i18n.
 *
 * @package AI_Awareness_Day
 */
( function () {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) {
		return;
	}

	var __ = wp.i18n.__;
	var frame;
	var pick = document.getElementById( 'aiad_upload_download_btn' );
	var remove = document.getElementById( 'aiad_remove_download_btn' );
	var urlField = document.getElementById( 'aiad_download_url' );
	var fileBox = document.getElementById( 'aiad_download_filename' );

	if ( pick ) {
		pick.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( frame ) {
				frame.open();
				return;
			}
			frame = wp.media( {
				title: __( 'Select or upload PDF or PPTX', 'ai-awareness-day' ),
				library: {
					type: [
						'application/pdf',
						'application/vnd.openxmlformats-officedocument.presentationml.presentation',
						'application/vnd.ms-powerpoint',
					],
				},
				button: { text: __( 'Use this file', 'ai-awareness-day' ) },
				multiple: false,
			} );
			frame.on( 'select', function () {
				var att = frame.state().get( 'selection' ).first().toJSON();
				if ( att && att.url && urlField ) {
					urlField.value = att.url;
					var name = att.filename || att.url.split( '/' ).pop().split( '?' )[ 0 ];
					var strong = fileBox && fileBox.querySelector( 'strong' );
					if ( strong ) {
						strong.textContent = name;
					}
					if ( fileBox ) {
						fileBox.style.display = '';
					}
					if ( remove ) {
						remove.style.display = '';
					}
				}
			} );
			frame.open();
		} );
	}

	if ( remove ) {
		remove.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			if ( urlField ) {
				urlField.value = '';
			}
			if ( fileBox ) {
				fileBox.style.display = 'none';
			}
			remove.style.display = 'none';
		} );
	}
} )();
