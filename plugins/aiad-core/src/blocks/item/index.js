/**
 * Styled list item (block.json): an li (or a div, inside a dl or a box) holding any blocks.
 */
// Side-effect imports so the build lists these packages as the script's dependencies; the code uses window.wp.
import '@wordpress/block-editor';
import '@wordpress/blocks';
import '@wordpress/components';
import '@wordpress/element';
import '@wordpress/i18n';

( function ( blocks, element, blockEditor, components, i18n ) {
	const el = element.createElement;
	const __ = i18n.__;

	blocks.registerBlockType( 'aiad/item', {
		edit( props ) {
			const a = props.attributes;
			const inner = blockEditor.useInnerBlocksProps(
				blockEditor.useBlockProps(),
				{
					template: [ [ 'core/paragraph' ] ],
				}
			);
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Item', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'HTML element', 'ai-awareness-day' ),
							value: a.tagName,
							options: [
								{
									label: __(
										'List item (li)',
										'ai-awareness-day'
									),
									value: 'li',
								},
								{
									label: __(
										'Box (div)',
										'ai-awareness-day'
									),
									value: 'div',
								},
							],
							onChange( value ) {
								props.setAttributes( { tagName: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el( a.tagName, inner )
			);
		},
		save( props ) {
			return el(
				props.attributes.tagName,
				blockEditor.useInnerBlocksProps.save(
					blockEditor.useBlockProps.save()
				)
			);
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.i18n
);
