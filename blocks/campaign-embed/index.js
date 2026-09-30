/**
 * Campaign video (block.json, render.php): the address is edited on the block, above a preview of the video.
 */
( function ( blocks, element, blockEditor, components, i18n, ServerSideRender ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'aiad/campaign-embed', {
		edit: function ( props ) {
			var url = props.attributes.url || '';
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( components.TextControl, {
					label: __( 'Video or LinkedIn post address', 'ai-awareness-day' ),
					help: __( 'A YouTube or Vimeo embed address, or the src of a LinkedIn post embed. Leave empty to show the campaign text full width.', 'ai-awareness-day' ),
					type: 'url',
					value: url,
					onChange: function ( value ) {
						props.setAttributes( { url: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				url ? el( ServerSideRender, { block: 'aiad/campaign-embed', attributes: { url: url } } ) : null
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n, window.wp.serverSideRender );
