/**
 * Editor side of the homepage section blocks (inc/homepage-blocks.php).
 *
 * The blocks are registered in PHP; this gives each one a server-rendered preview in the editor. Their front end is
 * rendered in PHP too, so nothing is saved but the block comment.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	( window.aiadHomepageSections || [] ).forEach( function ( name ) {
		blocks.registerBlockType( name, {
			edit: function ( props ) {
				return el(
					'div',
					blockEditor.useBlockProps(),
					el( ServerSideRender, { block: name, attributes: props.attributes } )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
