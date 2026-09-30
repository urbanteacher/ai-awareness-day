/**
 * Editor side of the homepage section blocks (inc/homepage-blocks.php).
 *
 * The blocks are registered in PHP; this gives each one a server-rendered preview and, for sections with wording,
 * a Wording panel in the settings sidebar. An empty field shows its placeholder (the Customizer's value or the
 * standard wording) on the page. The front end is rendered in PHP too, so nothing is saved but the block comment
 * and its wording attribute.
 *
 * A section with a pattern of core blocks (aiad_homepage_section_patterns()) can swap itself for that pattern, so its
 * wording is then edited on the page.
 */
( function ( blocks, element, blockEditor, components, ServerSideRender, data ) {
	var el = element.createElement;
	var fieldsByBlock = window.aiadHomepageFields || {};
	var patternByBlock = window.aiadHomepagePatterns || {};

	// Sidebar wording is plain text (its fields strip tags when the page renders); the cards hold HTML.
	function escapeText( value ) {
		return String( value ).replace( /<[^>]*>/g, '' ).replace( /&/g, '&amp;' ).replace( /</g, '&lt;' ).replace( />/g, '&gt;' );
	}

	var targetsByBlock = window.aiadHomepagePatternTargets || {};

	// Wording set in the section block's sidebar goes into the pattern's blocks, so the page reads the same after the swap.
	// A core block takes the value named for its class (aiad_homepage_pattern_wording_targets()); the first match wins.
	function applyWording( list, wording, targets, filled ) {
		targets = targets || {};
		filled = filled || {};
		list.forEach( function ( block ) {
			var classes = ( block.attributes.className || '' ).split( ' ' );
			Object.keys( targets ).forEach( function ( key ) {
				var target = targets[ key ];
				if ( wording[ key ] && ! filled[ key ] && -1 !== classes.indexOf( target.className ) && 'content' in block.attributes ) {
					block.attributes.content = target.html ? wording[ key ] : escapeText( wording[ key ] );
					filled[ key ] = true;
				}
			} );
			if ( 'aiad/principle-card' === block.name ) {
				var strand = block.attributes.strand || 'safe';
				if ( wording[ 'aiad_principle_title_' + strand ] ) {
					block.attributes.title = escapeText( wording[ 'aiad_principle_title_' + strand ] );
				}
				if ( wording[ 'aiad_principle_desc_' + strand ] ) {
					block.attributes.text = escapeText( wording[ 'aiad_principle_desc_' + strand ] );
				}
			}
			applyWording( block.innerBlocks, wording, targets, filled );
		} );
		return list;
	}

	function editOnPagePanel( name, clientId, wording ) {
		var content = patternByBlock[ name ];
		if ( ! content ) {
			return null;
		}
		return el(
			blockEditor.InspectorControls,
			null,
			el(
				components.PanelBody,
				{ title: 'Edit on the page', initialOpen: true },
				el( 'p', null, 'Swap this section for ordinary blocks with the same wording and design, so you can edit its text directly on the page. To undo, press Undo or put this section block back.' ),
				el(
					components.Button,
					{
						variant: 'secondary',
						onClick: function () {
							data.dispatch( 'core/block-editor' ).replaceBlocks( clientId, applyWording( blocks.parse( content ), wording || {}, targetsByBlock[ name ] ) );
						},
					},
					'Edit on the page'
				)
			)
		);
	}

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
					editOnPagePanel( name, props.clientId, props.attributes.wording ),
					wordingPanel( name, props.attributes, props.setAttributes ),
					el( ServerSideRender, { block: name, attributes: props.attributes } )
				);
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.serverSideRender, window.wp.data );
