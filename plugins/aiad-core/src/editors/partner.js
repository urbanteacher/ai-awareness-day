/**
 * Partners: the Partner details panel.
 *
 * A partner's content is its description, which is the body on the canvas, so it
 * needs the panel and no canvas block. This is the old "Partner URL" and "Partner
 * Statistics" meta boxes (modules/admin/meta-boxes.php) in one panel, with the
 * same fields. The old boxes showed the profile intro and the partner links but
 * never saved them; here they save.
 */
import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';

import { Rows, registerRecordDetails } from '../shared/record-editor';

const THEMES = [
	{ value: 'safe', label: __( 'Safe', 'aiad-core' ) },
	{ value: 'smart', label: __( 'Smart', 'aiad-core' ) },
	{ value: 'creative', label: __( 'Creative', 'aiad-core' ) },
	{ value: 'responsible', label: __( 'Responsible', 'aiad-core' ) },
	{ value: 'future', label: __( 'Future', 'aiad-core' ) },
];

function PartnerFields( { set, text, flag, list, meta } ) {
	// Shown as empty when it is zero, as the classic box did.
	const schools = meta?._partner_school_count || '';

	return (
		<>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Website URL (optional)', 'aiad-core' ) }
				value={ text( '_partner_url' ) }
				onChange={ set( '_partner_url' ) }
			/>

			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Profile intro (optional)', 'aiad-core' ) }
				help={ __(
					'Short intro near the top of the partner profile page. If empty, the page content is used as the description.',
					'aiad-core'
				) }
				placeholder={ __(
					'One sentence summary of who they are and what they offer for AI Awareness Day.',
					'aiad-core'
				) }
				rows={ 3 }
				value={ text( '_partner_profile_intro' ) }
				onChange={ set( '_partner_profile_intro' ) }
			/>

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __(
					'Provides linked AI learning resources',
					'aiad-core'
				) }
				checked={ flag( '_partner_provides_ai_resources' ) }
				onChange={ set( '_partner_provides_ai_resources' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'AI resources URL (optional)', 'aiad-core' ) }
				help={ __(
					'When the switch above is on, this partner appears first on the homepage Traction grid with a subtle highlight. The card links here, or to the website URL if this is empty.',
					'aiad-core'
				) }
				placeholder={ __(
					'Leave empty to use the website URL',
					'aiad-core'
				) }
				value={ text( '_partner_ai_resources_url' ) }
				onChange={ set( '_partner_ai_resources_url' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Statistics / description', 'aiad-core' ) }
				help={ __(
					'Shown on the Traction section card, for example "32,000 students" or "20 schools across Bedfordshire".',
					'aiad-core'
				) }
				placeholder={ __( 'e.g. 32,000 students', 'aiad-core' ) }
				value={ text( '_partner_stats' ) }
				onChange={ set( '_partner_stats' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="number"
				min={ 0 }
				step={ 1 }
				label={ __( 'Schools in portfolio', 'aiad-core' ) }
				help={ __(
					'For MATs and organisations: how many schools are in their catchment. Added to the "Schools registered" total on the front page.',
					'aiad-core'
				) }
				value={ schools }
				onChange={ ( value ) =>
					set( '_partner_school_count' )(
						Math.max( 0, parseInt( value, 10 ) || 0 )
					)
				}
			/>

			<h3 className="aiad-re-label">
				{ __( 'Partner resources and links (optional)', 'aiad-core' ) }
			</h3>
			<p className="aiad-re-help">
				{ __(
					'Themed links to the partner’s own AI Awareness Day page, units, downloads or activities. They show on the partner profile page, grouped by theme. A row needs a title or a link.',
					'aiad-core'
				) }
			</p>
			<Rows
				items={ list( '_partner_links' ) }
				onChange={ set( '_partner_links' ) }
				blank={ { theme: 'safe', title: '', duration: '', url: '' } }
				addLabel={ __( 'Add link', 'aiad-core' ) }
				row={ ( item, update ) => (
					<>
						<SelectControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Theme', 'aiad-core' ) }
							options={ THEMES }
							value={ item?.theme || 'safe' }
							onChange={ ( theme ) => update( { theme } ) }
						/>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Title', 'aiad-core' ) }
							placeholder={ __(
								'e.g. Introduction to Artificial Intelligence',
								'aiad-core'
							) }
							value={ item?.title || '' }
							onChange={ ( title ) => update( { title } ) }
						/>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Duration (optional)', 'aiad-core' ) }
							placeholder={ __(
								'e.g. 30 mins, 1 hour, 2 lessons',
								'aiad-core'
							) }
							value={ item?.duration || '' }
							onChange={ ( duration ) => update( { duration } ) }
						/>
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							type="url"
							label={ __( 'Link URL', 'aiad-core' ) }
							placeholder="https://…"
							value={ item?.url || '' }
							onChange={ ( url ) => update( { url } ) }
						/>
					</>
				) }
			/>
		</>
	);
}

registerRecordDetails( {
	postType: 'partner',
	name: 'aiad-partner-details',
	title: __( 'Partner details', 'aiad-core' ),
	Fields: PartnerFields,
} );
