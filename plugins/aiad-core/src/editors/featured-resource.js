/**
 * Featured resources (resources from other organisations): the Resource details
 * panel.
 *
 * This is the old "External resource link, theme & attribution" meta box
 * (modules/admin/meta-boxes.php) with the same fields. Theme and format are one
 * choice each, as the classic selects were, so core's checkbox panels for them are
 * hidden; session length allows several, so core's panel stays.
 */
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

import {
	CardImageKeywords,
	SingleTerm,
	registerRecordDetails,
} from '../shared/record-editor';

function FeaturedResourceFields( record ) {
	const { set, text } = record;
	const url = text( '_featured_resource_url' );

	return (
		<>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Resource URL (required)', 'aiad-core' ) }
				help={
					url
						? __( 'Where the card sends visitors.', 'aiad-core' )
						: __(
								'Required: the card links here, so it has nowhere to go without it.',
								'aiad-core'
							)
				}
				placeholder="https://…"
				value={ url }
				onChange={ set( '_featured_resource_url' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Organisation name', 'aiad-core' ) }
				placeholder={ __( 'e.g. STEM Learning', 'aiad-core' ) }
				value={ text( '_featured_resource_org_name' ) }
				onChange={ set( '_featured_resource_org_name' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Organisation website (optional)', 'aiad-core' ) }
				placeholder="https://…"
				value={ text( '_featured_resource_org_url' ) }
				onChange={ set( '_featured_resource_org_url' ) }
			/>

			<CardImageKeywords
				record={ record }
				keyName="_featured_resource_image_keywords"
				help={ __(
					'For example: rhino, wildlife, conservation. Used to find a picture when the card has no featured image.',
					'aiad-core'
				) }
			/>

			<SingleTerm
				postType="featured_resource"
				taxonomy="resource_principle"
				label={ __( 'Theme', 'aiad-core' ) }
				help={ __(
					'Used for the Safe / Smart / Creative / Responsible / Future pill and the filters.',
					'aiad-core'
				) }
			/>

			<SingleTerm
				postType="featured_resource"
				taxonomy="activity_type"
				variant="select"
				optional
				label={ __( 'Format', 'aiad-core' ) }
				help={ __(
					'Used for the Format filter (Game, Quiz, Creative Task and so on).',
					'aiad-core'
				) }
			/>
		</>
	);
}

registerRecordDetails( {
	postType: 'featured_resource',
	name: 'aiad-featured-resource-details',
	title: __( 'Resource details', 'aiad-core' ),
	// One theme and one format each, chosen above; session length keeps core's panel.
	hidePanels: [
		'taxonomy-panel-resource_principle',
		'taxonomy-panel-activity_type',
	],
	Fields: FeaturedResourceFields,
} );
