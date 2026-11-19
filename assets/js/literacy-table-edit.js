/**
 * Editor side of the AI Literacy Periodic Table block (aiad/literacy-table, inc/literacy-table.php).
 *
 * The block has nothing to set: its elements are written in the theme. The editor shows the table as the page
 * renders it, so the page reads the same in the editor as on the site.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	blocks.registerBlockType( 'aiad/literacy-table', {
		edit: function () {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, { block: 'aiad/literacy-table' } )
			);
		},
		save: function () {
			return null;
		},
	} );
}( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender ) );
