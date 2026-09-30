/**
 * Previous and next posts (block.json, render.php): it depends on the post it is shown with, so the editor shows a note.
 */
( function ( blocks, element, blockEditor ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/post-navigation', {
		edit: function () {
			return el( 'p', blockEditor.useBlockProps(), 'Previous and next posts: links to the neighbouring posts show here on the site.' );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
