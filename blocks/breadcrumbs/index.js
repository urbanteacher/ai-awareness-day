/**
 * Breadcrumbs (block.json, render.php): a preview in the editor.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	function empty() {
		return el( 'p', null, 'Breadcrumbs are switched off in the Customizer, so nothing shows here on the site.' );
	}

	blocks.registerBlockType( 'aiad/breadcrumbs', {
		edit: function () {
			return el( 'div', blockEditor.useBlockProps(), el( ServerSideRender, { block: 'aiad/breadcrumbs', EmptyResponsePlaceholder: empty } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
