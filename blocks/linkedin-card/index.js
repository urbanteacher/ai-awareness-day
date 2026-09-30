/**
 * LinkedIn card (block.json, render.php): the post's address is edited on the block, above a preview of the card.
 */
( function ( blocks, element, blockEditor, components, i18n, ServerSideRender ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'aiad/linkedin-card', {
		edit: function ( props ) {
			var url = props.attributes.url || '';
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( components.TextControl, {
					label: __( 'LinkedIn post address', 'ai-awareness-day' ),
					help: __( 'Leave empty to hide the card.', 'ai-awareness-day' ),
					type: 'url',
					value: url,
					onChange: function ( value ) {
						props.setAttributes( { url: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				url ? el( ServerSideRender, { block: 'aiad/linkedin-card', attributes: { url: url } } ) : null
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.serverSideRender );
