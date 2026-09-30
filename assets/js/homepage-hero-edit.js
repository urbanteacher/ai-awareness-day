/**
 * The homepage hero block, edited on the canvas (used by assets/js/homepage-section-blocks.js).
 *
 * The editor shows the hero with the site's markup and classes, so the theme's styles apply, and its words are edited
 * in place: the headline, intro, button labels, panel title and, for the strand chosen with the panel's tabs, its
 * question. They are saved in the block's wording attribute, as plain text, like the fields in the sidebar; the page
 * itself is rendered in PHP (template-parts/front-page/section-hero.php). A word left as the site's wording is not
 * saved, so the Customizer's value or the standard wording keeps showing.
 *
 * What the page works out when it renders (dates, countdown, the join link, the portal links) is shown as it is now
 * (aiad_homepage_hero_editor_data()).
 */
( function ( element, blockEditor, richText, escapeHtml ) {
	var el = element.createElement;
	var data = window.aiadHeroEditor || {};

	function plain( html ) {
		return richText.create( { html: html } ).text;
	}

	window.aiadHomepageHeroEdit = function ( props ) {
		var wording = props.attributes.wording || {};
		var fields = {};
		( ( window.aiadHomepageFields || {} )[ 'aiad/section-hero' ] || [] ).forEach( function ( field ) {
			fields[ field.key ] = field;
		} );
		var active = element.useState( 'safe' );
		var strand = active[ 0 ];

		function current( key ) {
			return wording[ key ] || ( fields[ key ] ? fields[ key ].placeholder : '' );
		}

		// One editable word, in the element the site prints it in.
		function words( key, tagName, className ) {
			return el( blockEditor.RichText, {
				key: key,
				tagName: tagName,
				className: className,
				value: escapeHtml.escapeHTML( current( key ) ),
				allowedFormats: [],
				disableLineBreaks: true,
				placeholder: fields[ key ] ? fields[ key ].label : '',
				'aria-label': fields[ key ] ? fields[ key ].label : key,
				onChange: function ( html ) {
					var next = Object.assign( {}, wording );
					var value = plain( html ).trim();
					if ( ! value || ( fields[ key ] && value === fields[ key ].placeholder ) ) {
						delete next[ key ]; // The site's wording: nothing to save.
					} else {
						next[ key ] = value;
					}
					props.setAttributes( { wording: next } );
				},
			} );
		}

		var copy = [
			el( 'p', { key: 'eyebrow', className: 'hero-eyebrow' }, data.eyebrow + ' ', el( 'span', { className: 'hero-eyebrow__year' }, '2027' ) ),
			el( 'h1', { key: 'title', className: 'hero-title' }, words( 'aiad_hero27_title_1', 'span', 'hero-title__line' ), words( 'aiad_hero27_title_2', 'span', 'hero-title__line' ) ),
			data.eventDate ? el( 'p', { key: 'date', className: 'hero-eyebrow-date' }, data.eventDate ) : null,
			el( 'p', { key: 'intro', className: 'hero-subtitle' }, words( 'aiad_hero27_lead', 'strong' ), ' ', words( 'aiad_hero27_body', 'span' ) ),
			el(
				'div',
				{ key: 'cta', className: 'hero-cta' },
				words( 'aiad_hero27_involved_label', 'a', 'hero-cta__btn hero-cta__btn--secondary' ),
				words( 'aiad_hero27_join_label', 'a', 'hero-cta__btn hero-cta__btn--primary' )
			),
			data.portal && data.portal.length
				? el( 'p', { key: 'more', className: 'hero-cta-more' }, data.portal.map( function ( label ) {
						return el( 'a', { key: label }, label );
				  } ) )
				: null,
			data.countdown
				? el(
						'div',
						{ key: 'facts', className: 'hero-facts' },
						el(
							'div',
							{ className: 'hero-countdown-wrap' },
							el( 'p', { className: 'hero-countdown__title' }, data.countdown.label ),
							el(
								'div',
								{ className: 'hero-countdown' },
								data.units.map( function ( unit, i ) {
									return el(
										'div',
										{ key: unit, className: 'hero-countdown__item' },
										el( 'span', { className: 'hero-countdown__value' }, 0 === i ? String( data.countdown.days ) : '00' ),
										el( 'span', { className: 'hero-countdown__label' }, unit )
									);
								} )
							)
						)
				  )
				: null,
		];

		var slugs = Object.keys( data.strands || {} );
		var panel = el(
			'div',
			{ className: 'hero-strand-feature' },
			el(
				'div',
				{ className: 'hero-strand-feature__head' },
				words( 'aiad_hero27_panel_title', 'p', 'hero-strand-feature__title' ),
				data.starts ? el( 'p', { className: 'hero-strand-feature__starts' }, data.starts ) : null,
				el(
					'div',
					{ className: 'hero-strand-feature__themes' },
					slugs.map( function ( slug ) {
						return el(
							'a',
							{
								key: slug,
								href: '#',
								className: 'hero-strand-feature__theme' + ( slug === strand ? ' is-active' : '' ),
								'aria-current': slug === strand ? 'true' : 'false',
								onClick: function ( event ) {
									event.preventDefault();
									active[ 1 ]( slug ); // Show, and edit, this strand's question.
								},
							},
							data.strands[ slug ]
						);
					} )
				)
			),
			el(
				'div',
				{ className: 'hero-strand-feature__stage' },
				el( 'span', { className: 'hero-strand-feature__mark', 'aria-hidden': 'true' } ),
				el( 'span', { className: 'hero-strand-feature__word' }, data.strands ? data.strands[ strand ] : '' )
			),
			el(
				'p',
				{ className: 'hero-strand-feature__summary' },
				words( 'aiad_hero27_question_label', 'span', 'hero-strand-feature__motion-label' ),
				' ',
				words( 'aiad_hero27_q_' + strand, 'span', 'hero-strand-feature__motion-text' )
			)
		);

		// The block's wrapper takes the editor's own id, so the section inside it carries #hero, which the styles use.
		return el(
			'div',
			blockEditor.useBlockProps(),
			el(
				'section',
				{ className: 'hero-section', id: 'hero', 'data-strand': strand },
				el( 'div', { className: 'container' }, el( 'div', { className: 'hero-title-block' }, el( 'div', { className: 'hero-copy' }, copy ), panel ) )
			)
		);
	};

	window.aiadHomepageHeroEdit.enabled = ! data.previous;
} )( window.wp.element, window.wp.blockEditor, window.wp.richText, window.wp.escapeHtml );
