/**
 * Social icons (block.json, render.php): a preview in the editor.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	function empty() {
		return el( 'p', null, 'No LinkedIn or Instagram profile is set in the Customizer, so the icons are hidden on the site.' );
	}

	blocks.registerBlockType( 'aiad/footer-social', {
		edit: function () {
			return el( 'div', blockEditor.useBlockProps(), el( ServerSideRender, { block: 'aiad/footer-social', EmptyResponsePlaceholder: empty } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
