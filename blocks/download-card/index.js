/**
 * Download card (block.json): a preview (for a document with no preview, its type and a label), a title, a
 * description and a button that downloads a file (or, for a page card, opens a page), saved as plain HTML with the Assets Pack's classes (assets/css/pages/assets-pack.css).
 * Choose the file from the toolbar: its address, name and preview come with it.
 */
( function ( blocks, element, blockEditor, components, i18n, escapeHtml ) {
	var el = element.createElement;
	var __ = i18n.__;

	function icon() {
		return el(
			'svg',
			{ xmlns: 'http://www.w3.org/2000/svg', width: '16', height: '16', viewBox: '0 0 24 24', fill: 'none', stroke: 'currentColor', strokeWidth: '2', strokeLinecap: 'round', strokeLinejoin: 'round', 'aria-hidden': 'true' },
			el( 'path', { d: 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4' } ),
			el( 'polyline', { points: '7 10 12 15 17 10' } ),
			el( 'line', { x1: '12', y1: '15', x2: '12', y2: '3' } )
		);
	}

	// The card; title and description are passed in, as editable text in the editor and saved text on the site.
	function card( a, props, title, description, button ) {
		var page = 'page' === a.kind;
		var doc = ! page && ! a.preview; // A document: its type and a label in place of a preview.
		return el(
			'div',
			props,
			el(
				'div',
				{ className: 'assets-pack__preview' + ( page ? ' assets-pack__preview--page' : '' ) + ( doc ? ' assets-pack__preview--doc' : '' ) },
				a.preview ? el( 'img', { src: a.preview, alt: a.alt, loading: 'lazy' } ) : null,
				page && a.badge ? el( 'span', { className: 'assets-pack__doc-badge' }, a.badge ) : null,
				doc ? el( 'span', { className: 'assets-pack__doc-badge', 'aria-hidden': 'true' }, a.badge || 'PDF' ) : null,
				doc ? el( 'span', { className: 'assets-pack__doc-label' }, a.docLabel ) : null
			),
			el( 'div', { className: 'assets-pack__info' }, title, description, button )
		);
	}

	function cardClass( a ) {
		return 'assets-pack__card' + ( 'page' === a.kind ? ' assets-pack__card--page' : '' ) + ' fade-up';
	}

	function text( key, tagName, className, placeholder, props ) {
		return el( blockEditor.RichText, {
			tagName: tagName,
			className: className,
			value: props.attributes[ key ],
			placeholder: placeholder,
			onChange: function ( value ) {
				var next = {};
				next[ key ] = value;
				props.setAttributes( next );
			},
		} );
	}

	blocks.registerBlockType( 'aiad/download-card', {
		edit: function ( props ) {
			var a = props.attributes;
			var set = props.setAttributes;
			var page = 'page' === a.kind;
			var field = function ( key, label, help ) {
				return el( components.TextControl, {
					label: label,
					help: help,
					value: a[ key ],
					onChange: function ( value ) {
						var next = {};
						next[ key ] = value;
						set( next );
					},
					__nextHasNoMarginBottom: true,
				} );
			};
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.BlockControls,
					{ group: 'other' },
					el( blockEditor.MediaReplaceFlow, {
						mediaURL: a.href,
						name: page ? __( 'Preview image', 'ai-awareness-day' ) : __( 'Choose file', 'ai-awareness-day' ),
						onSelect: function ( media ) {
							if ( page ) {
								set( { preview: media.url } );
								return;
							}
							var image = 'image' === media.type;
							var filename = media.filename || media.url.split( '/' ).pop();
							set( {
								href: media.url,
								filename: filename,
								// A document shows its type instead of a preview.
								preview: image ? ( ( media.sizes && media.sizes.large && media.sizes.large.url ) || media.url ) : '',
								badge: image ? a.badge : ( filename.split( '.' ).pop() || 'pdf' ).toUpperCase(),
							} );
						},
						onSelectURL: function ( url ) {
							set( page ? { preview: url } : { href: url, filename: url.split( '/' ).pop(), preview: url } );
						},
					} )
				),
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Card', 'ai-awareness-day' ) },
						el( components.ToggleControl, {
							label: __( 'Opens a page instead of downloading a file', 'ai-awareness-day' ),
							checked: page,
							onChange: function ( value ) {
								set( { kind: value ? 'page' : 'download' } );
							},
							__nextHasNoMarginBottom: true,
						} ),
						field( 'href', page ? __( 'Page address', 'ai-awareness-day' ) : __( 'File address', 'ai-awareness-day' ) ),
						page ? field( 'badge', __( 'Badge on the preview', 'ai-awareness-day' ) ) : field( 'filename', __( 'File name when downloaded', 'ai-awareness-day' ) ),
						! page && ! a.preview ? field( 'badge', __( 'Document type shown', 'ai-awareness-day' ) ) : null,
						! page && ! a.preview ? field( 'docLabel', __( 'Label under the document type', 'ai-awareness-day' ) ) : null,
						field( 'preview', __( 'Preview image address', 'ai-awareness-day' ) ),
						field( 'alt', __( 'Preview\'s alternative text', 'ai-awareness-day' ), __( 'Leave empty when the title says what the preview shows.', 'ai-awareness-day' ) )
					)
				),
				card(
					a,
					blockEditor.useBlockProps( { className: cardClass( a ) } ),
					text( 'title', 'h2', 'assets-pack__card-title', __( 'Title', 'ai-awareness-day' ), props ),
					text( 'description', 'p', 'assets-pack__card-desc section-desc', __( 'What it is and where to use it', 'ai-awareness-day' ), props ),
					el(
						'span',
						{ className: 'btn assets-pack__download-btn' },
						el( blockEditor.RichText, {
							tagName: 'span',
							value: a.button,
							allowedFormats: [],
							withoutInteractiveFormatting: true,
							placeholder: __( 'Button label', 'ai-awareness-day' ),
							onChange: function ( value ) {
								set( { button: value } );
							},
						} ),
						page ? null : icon()
					)
				)
			);
		},
		save: function ( props ) {
			var a = props.attributes;
			var page = 'page' === a.kind;
			return card(
				a,
				blockEditor.useBlockProps.save( { className: cardClass( a ) } ),
				el( blockEditor.RichText.Content, { tagName: 'h2', className: 'assets-pack__card-title', value: a.title } ),
				el( blockEditor.RichText.Content, { tagName: 'p', className: 'assets-pack__card-desc section-desc', value: a.description } ),
				// Written out, because WordPress's serialiser treats download as a yes/no attribute and drops the file name.
				el(
					element.RawHTML,
					null,
					'<a href="' + escapeHtml.escapeAttribute( a.href ) + '"' + ( page ? '' : ' download="' + escapeHtml.escapeAttribute( a.filename ) + '"' ) + ' class="btn assets-pack__download-btn">' + escapeHtml.escapeHTML( a.button ) + ( page ? '' : element.renderToString( icon() ) ) + '</a>'
				)
			);
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.escapeHtml );
