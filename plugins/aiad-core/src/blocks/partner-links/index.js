/**
 * Partner resources (block.json, render.php): a preview in the editor.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';
import '@wordpress/server-side-render';

( function ( blocks, element, blockEditor, ServerSideRender ) {
	const el = element.createElement;
	// What the editor shows when the partner it previews has nothing for this block (the site shows nothing).
	const Empty = () =>
		el(
			'p',
			{ style: { margin: 0, opacity: 0.6 } },
			'Partner resources: shown when the partner has links.'
		);

	blocks.registerBlockType( 'aiad/partner-links', {
		edit() {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'aiad/partner-links',
					EmptyResponsePlaceholder: Empty,
				} )
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
	window.wp.serverSideRender
);
