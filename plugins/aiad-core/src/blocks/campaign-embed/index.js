/**
 * Campaign video (block.json, render.php): the address is edited on the block, above a preview of the video.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/components';
import '@wordpress/element';
import '@wordpress/i18n';
import '@wordpress/server-side-render';

( function (
	blocks,
	element,
	blockEditor,
	components,
	i18n,
	ServerSideRender
) {
	const el = element.createElement;
	const __ = i18n.__;

	blocks.registerBlockType( 'aiad/campaign-embed', {
		edit( props ) {
			const url = props.attributes.url || '';
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( components.TextControl, {
					label: __(
						'Video or LinkedIn post address',
						'ai-awareness-day'
					),
					help: __(
						'A YouTube or Vimeo embed address, or the src of a LinkedIn post embed. Leave empty to show the campaign text full width.',
						'ai-awareness-day'
					),
					type: 'url',
					value: url,
					onChange( value ) {
						props.setAttributes( { url: value } );
					},
					__nextHasNoMarginBottom: true,
					__next40pxDefaultSize: true,
				} ),
				url
					? el( ServerSideRender, {
							block: 'aiad/campaign-embed',
							attributes: { url },
						} )
					: null
			);
		},
		save() {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n,
	window.wp.serverSideRender
);
