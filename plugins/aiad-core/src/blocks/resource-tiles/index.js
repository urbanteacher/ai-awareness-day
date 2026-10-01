/**
 * Resource tiles (block.json, render.php): a preview in the editor. The resources are picked in Appearance → Edit
 * Homepage; free or featured is set in the sidebar.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/element';
import '@wordpress/server-side-render';

( function ( blocks, element, blockEditor, ServerSideRender ) {
	const el = element.createElement;

	function empty() {
		return el(
			'p',
			{ className: 'section-desc' },
			'No resources are picked yet. Pick them in Appearance → Edit Homepage; until then this section is hidden on the site.'
		);
	}

	blocks.registerBlockType( 'aiad/resource-tiles', {
		edit( props ) {
			return el(
				'div',
				blockEditor.useBlockProps(),
				el( ServerSideRender, {
					block: 'aiad/resource-tiles',
					attributes: props.attributes,
					EmptyResponsePlaceholder: empty,
				} )
			);
		},
		save() {
			return null;
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.serverSideRender
);
