/**
 * Editor side of the homepage section blocks (inc/homepage-blocks.php).
 *
 * The blocks are registered in PHP; this gives each one a server-rendered preview and, for sections with wording,
 * a Wording panel in the settings sidebar. An empty field shows its placeholder (the Customizer's value or the
 * standard wording) on the page. The front end is rendered in PHP too, so nothing is saved but the block comment
 * and its wording attribute.
 */
( function ( blocks, element, blockEditor, components, ServerSideRender ) {
	var el = element.createElement;
	var fieldsByBlock = window.aiadHomepageFields || {};

	function wordingPanel( name, attributes, setAttributes ) {
		var fields = fieldsByBlock[ name ] || [];
		if ( ! fields.length ) {
			return null;
		}
		var wording = attributes.wording || {};

		return el(
			blockEditor.InspectorControls,
			null,
			el(
				components.PanelBody,
				{ title: 'Wording', initialOpen: true },
				fields.map( function ( field ) {
					var Control = 'textarea' === field.type ? components.TextareaControl : components.TextControl;
					return el( Control, {
						key: field.key,
						label: field.label,
						help: field.help || undefined,
						type: 'url' === field.type ? 'url' : undefined,
						value: wording[ field.key ] || '',
						placeholder: field.placeholder,
						__nextHasNoMarginBottom: true,
						__next40pxDefaultSize: 'textarea' !== field.type ? true : undefined,
						onChange: function ( value ) {
							var next = Object.assign( {}, wording );
							if ( value && value.trim() ) {
								next[ field.key ] = value;
							} else {
								delete next[ field.key ]; // Empty: the Customizer's value or the standard wording shows.
							}
							setAttributes( { wording: next } );
						},
					} );
				} )
			)
		);
	}

	( window.aiadHomepageSections || [] ).forEach( function ( name ) {
		blocks.registerBlockType( name, {
			edit: function ( props ) {
				return el(
					'div',
					blockEditor.useBlockProps(),
					wordingPanel( name, props.attributes, props.setAttributes ),
					el( ServerSideRender, { block: name, attributes: props.attributes } )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender );
