/**
 * Strand icon (block.json, render.php): the strand's mark, shown in the editor as the page shows it.
 */
( function ( blocks, element, blockEditor, components, i18n, serverSideRender ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'aiad/strand-icon', {
		edit: function ( props ) {
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
							value: props.attributes.strand,
							options: [ 'safe', 'smart', 'creative', 'responsible', 'future' ].map( function ( slug ) {
								return { label: slug.charAt( 0 ).toUpperCase() + slug.slice( 1 ), value: slug };
							} ),
							onChange: function ( value ) {
								props.setAttributes( { strand: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el( 'div', blockEditor.useBlockProps( { style: { display: 'contents' } } ), el( serverSideRender, { block: 'aiad/strand-icon', attributes: props.attributes } ) )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.serverSideRender );
