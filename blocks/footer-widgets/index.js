/**
 * Footer widgets (block.json, render.php): a preview in the editor.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	function empty() {
		return el( 'p', null, 'The Footer Content widget area (Appearance → Widgets) is empty, so nothing shows on the site.' );
	}

	blocks.registerBlockType( 'aiad/footer-widgets', {
		edit: function () {
			return el( 'div', blockEditor.useBlockProps(), el( ServerSideRender, { block: 'aiad/footer-widgets', EmptyResponsePlaceholder: empty } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
