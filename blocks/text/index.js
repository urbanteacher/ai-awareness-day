/**
 * Styled text (block.json): a term (dt), its description (dd), a list item (li) or a tag (span), edited in place.
 */
( function ( blocks, element, blockEditor, components, i18n ) {
	var el = element.createElement;
	var __ = i18n.__;

	blocks.registerBlockType( 'aiad/text', {
		edit: function ( props ) {
			var a = props.attributes;
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
								{ label: __( 'Term (dt)', 'ai-awareness-day' ), value: 'dt' },
								{ label: __( 'Description (dd)', 'ai-awareness-day' ), value: 'dd' },
								{ label: __( 'List item (li)', 'ai-awareness-day' ), value: 'li' },
								{ label: __( 'Tag (span)', 'ai-awareness-day' ), value: 'span' },
							],
							onChange: function ( value ) {
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
						onChange: function ( value ) {
							props.setAttributes( { content: value } );
						},
					} )
				)
			);
		},
		save: function ( props ) {
			var a = props.attributes;
			return el( blockEditor.RichText.Content, Object.assign( blockEditor.useBlockProps.save(), { tagName: a.tagName, value: a.content } ) );
		},
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
