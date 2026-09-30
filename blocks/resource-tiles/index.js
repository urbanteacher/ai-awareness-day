/**
 * Resource tiles (block.json, render.php): a preview in the editor. The resources are picked in Appearance → Edit
 * Homepage; free or featured is set in the sidebar.
 */
( function ( blocks, element, blockEditor, ServerSideRender ) {
	var el = element.createElement;

	function empty() {
		return el( 'p', { className: 'section-desc' }, 'No resources are picked yet. Pick them in Appearance → Edit Homepage; until then this section is hidden on the site.' );
	}

	blocks.registerBlockType( 'aiad/resource-tiles', {
		edit: function ( props ) {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, { block: 'aiad/resource-tiles', attributes: props.attributes, EmptyResponsePlaceholder: empty } )
			);
		},
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.serverSideRender );
