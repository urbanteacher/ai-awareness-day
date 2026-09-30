/**
 * Campaign updates feed (block.json, render.php): a preview in the editor.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/timeline-feed', {
		edit: function () {
			return el( 'div', blockEditor.useBlockProps(), el( ServerSideRender, { block: 'aiad/timeline-feed' } ) );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
