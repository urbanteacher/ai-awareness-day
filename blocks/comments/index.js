/**
 * Comments (block.json, render.php): they depend on the post, so the editor shows a note.
 */
( function ( blocks, element, blockEditor ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/comments', {
		edit: function () {
			return el( 'p', blockEditor.useBlockProps(), 'Comments: the post’s comments and comment form show here on the site, when comments are open.' );
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor );
