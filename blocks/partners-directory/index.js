/**
 * Partners directory (block.json, render.php): a preview in the editor.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/partners-directory', {
		edit: function () {
			return el( 'div', blockEditor.useBlockProps(), el( ServerSideRender, { block: 'aiad/partners-directory' } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
