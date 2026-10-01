/**
 * Social icons (block.json, render.php): a preview in the editor.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';
import '@wordpress/server-side-render';

( function ( blocks, element, blockEditor, ServerSideRender ) {
	const el = element.createElement;

	function empty() {
		return el(
			'p',
			null,
			'No LinkedIn or Instagram profile is set in the Customizer, so the icons are hidden on the site.'
		);
	}

	blocks.registerBlockType( 'aiad/footer-social', {
		edit() {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'aiad/footer-social',
					EmptyResponsePlaceholder: empty,
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
