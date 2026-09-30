/**
 * Featured badge (block.json, render.php): it depends on the post it is shown with, so the editor shows a note.
 */
( function ( blocks, element, blockEditor ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/post-badge', {
		edit: function () {
			return el( 'p', blockEditor.useBlockProps(), 'Featured badge: shows “Featured” here on sticky posts.' );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
