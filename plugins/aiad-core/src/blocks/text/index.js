/**
 * Styled text (block.json): a term (dt), its description (dd), a list item (li) or a tag (span), edited in place.
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

	blocks.registerBlockType( 'aiad/text', {
		edit( props ) {
			const a = props.attributes;
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: __( 'Text', 'ai-awareness-day' ) },
						el( components.SelectControl, {
							label: __( 'HTML element', 'ai-awareness-day' ),
							value: a.tagName,
							options: [
								{
									label: __(
										'Term (dt)',
										'ai-awareness-day'
									),
									value: 'dt',
								},
								{
									label: __(
										'Description (dd)',
										'ai-awareness-day'
									),
									value: 'dd',
								},
								{
									label: __(
										'List item (li)',
										'ai-awareness-day'
									),
									value: 'li',
								},
								{
									label: __(
										'Tag (span)',
										'ai-awareness-day'
									),
									value: 'span',
								},
							],
							onChange( value ) {
								props.setAttributes( { tagName: value } );
							},
							__nextHasNoMarginBottom: true,
						} )
					)
				),
				el(
					blockEditor.RichText,
					Object.assign( blockEditor.useBlockProps(), {
						tagName: a.tagName,
						value: a.content,
						placeholder: __( 'Text', 'ai-awareness-day' ),
						onChange( value ) {
							props.setAttributes( { content: value } );
						},
					} )
				)
			);
		},
		save( props ) {
			const a = props.attributes;
			return el(
				blockEditor.RichText.Content,
				Object.assign( blockEditor.useBlockProps.save(), {
					tagName: a.tagName,
					value: a.content,
				} )
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
