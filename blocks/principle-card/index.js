/**
 * Principle card (block.json, render.php). The title and description are edited on the card; the strand, which
 * sets the colour and icon, is chosen in the sidebar. The page is rendered in PHP, so only the attributes are saved.
 *
 * In the editor the card is a div rather than the site's link, so clicking the text edits it.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;
	var data = window.aiadPrincipleCards || { icons: {}, strands: {} };

	blocks.registerBlockType( 'aiad/principle-card', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var strand = attributes.strand || 'safe';
			var standard = data.strands[ strand ] || { title: '', text: '', label: strand };
			var literacy = 'literacy' === strand;
			var icon = data.icons[ strand ];

			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Strand', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'Strand', 'ai-awareness-day' ),
							help: __( 'Sets the card’s colour and icon.', 'ai-awareness-day' ),
							value: strand,
							options: Object.keys( data.strands ).map( function ( key ) {
								return { value: key, label: data.strands[ key ].label };
							} ),
							onChange: function ( value ) {
								props.setAttributes( { strand: value } );
							},
							__nextHasNoMarginBottom: true,
							__next40pxDefaultSize: true,
						} )
					)
				),
				el(
					'div',
					blockEditor.useBlockProps( {
						className: literacy ? 'ai-literacy-box principle-card' : 'principle-card principle-card--' + strand,
					} ),
					el(
						'div',
						{ className: 'principle-badge' },
						icon
							? el( 'img', { src: icon, alt: '', 'aria-hidden': 'true', className: 'principle-badge__img' } )
							: el(
									'div',
									{ className: 'principle-badge__placeholder', 'aria-hidden': 'true' },
									el( 'span', { className: 'principle-badge__placeholder-text' }, 'AI' )
							  )
					),
					el( blockEditor.RichText, {
						tagName: 'h3',
						value: attributes.title || '',
						placeholder: standard.title,
						allowedFormats: [ 'core/bold', 'core/italic' ],
						onChange: function ( value ) {
							props.setAttributes( { title: value } );
						},
					} ),
					el( blockEditor.RichText, {
						tagName: 'p',
						className: 'section-desc',
						value: attributes.text || '',
						placeholder: standard.text,
						allowedFormats: [ 'core/bold', 'core/italic' ],
						onChange: function ( value ) {
							props.setAttributes( { text: value } );
						},
					} )
				)
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
