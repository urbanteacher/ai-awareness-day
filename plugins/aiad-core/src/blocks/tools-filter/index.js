/**
 * Tools category filter (block.json, render.php): a preview in the editor.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';
import '@wordpress/server-side-render';

( function ( blocks, element, blockEditor, ServerSideRender ) {
	const el = element.createElement;
	// What the editor shows when there is nothing to print (the site shows nothing).
	const Empty = () =>
		el(
			'p',
			{ style: { margin: 0, opacity: 0.6 } },
			'Category filter: shown when tools have categories.'
		);

	blocks.registerBlockType( 'aiad/tools-filter', {
		edit() {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'aiad/tools-filter',
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
