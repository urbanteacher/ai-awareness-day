/**
 * Timeline entries: the Entry details panel.
 *
 * A timeline entry's content is its body, which already sits on the canvas, so it
 * needs the panel and no canvas block. This is the old "Entry Details" meta box
 * (modules/timeline/admin-meta-box.php) with the same fields and the same
 * rules about which fields suit which card type. The benchmark audience is core's
 * own taxonomy panel; Topics is too.
 */
import { __ } from '@wordpress/i18n';
import {
	SelectControl,
	TextControl,
	ToggleControl,
} from '@wordpress/components';

import {
	FocalPoint,
	registerRecordDetails,
	useEditorSetting,
} from '../shared/record-editor';

// Which card types each field belongs to; the rest show for all. The same rules as the classic box.
const SHOW_FOR = {
	_aiad_timeline_video_url: [ 'video', 'default' ],
	_aiad_timeline_linkedin_url: [ 'linkedin' ],
	_aiad_timeline_link_url: [ 'link', 'default', 'video', 'linkedin' ],
	_aiad_timeline_link_label: [ 'link', 'default', 'video', 'linkedin' ],
};

const FOCAL_CONTEXTS = [
	{
		key: '_aiad_thumbnail_focal_point_feed',
		label: __( 'Homepage timeline cards', 'aiad-core' ),
		help: __(
			'A wide crop on the homepage cards (swipe and magazine).',
			'aiad-core'
		),
		fallback: { x: 0.5, y: 0.3 },
	},
	{
		key: '_aiad_thumbnail_focal_point_single',
		label: __( 'Entry page', 'aiad-core' ),
		help: __(
			'A taller crop on the entry’s own page: keep faces or headline text in view.',
			'aiad-core'
		),
		fallback: { x: 0.5, y: 0.5 },
	},
];

const options = ( map, blank ) => [
	...( blank ? [ blank ] : [] ),
	...Object.entries( map || {} ).map( ( [ value, label ] ) => ( {
		value,
		label,
	} ) ),
];

function EntryFields( record ) {
	const { set, text, flag } = record;
	const config = useEditorSetting( 'aiadTimeline' );
	const cardType = text( '_aiad_timeline_card_type' ) || 'default';
	const field = ( key ) => ( config.fields || {} )[ key ] || {};
	const shows = ( key ) =>
		! SHOW_FOR[ key ] || SHOW_FOR[ key ].includes( cardType );

	const urlField = ( key, type ) =>
		shows( key ) && (
			<TextControl
				key={ key }
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type={ type }
				label={ field( key ).label }
				placeholder={ field( key ).placeholder }
				help={ field( key ).help }
				value={ text( key ) }
				onChange={ set( key ) }
			/>
		);

	return (
		<>
			<p className="aiad-re-help">
				{ __(
					'Set a featured image and/or choose a card type. The timeline card shows the content prominently.',
					'aiad-core'
				) }
			</p>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={
					field( '_aiad_timeline_card_type' ).label ||
					__( 'Card type', 'aiad-core' )
				}
				help={ field( '_aiad_timeline_card_type' ).help }
				options={ options( config.cardTypes ) }
				value={ cardType }
				onChange={ set( '_aiad_timeline_card_type' ) }
			/>

			{ urlField( '_aiad_timeline_video_url', 'url' ) }
			{ urlField( '_aiad_timeline_linkedin_url', 'url' ) }
			{ urlField( '_aiad_timeline_link_url', 'url' ) }
			{ urlField( '_aiad_timeline_link_label', 'text' ) }

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Pin to top of timeline', 'aiad-core' ) }
				checked={ flag( '_aiad_timeline_pinned' ) }
				onChange={ set( '_aiad_timeline_pinned' ) }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Icon', 'aiad-core' ) }
				options={ options( config.icons ) }
				value={ text( '_aiad_timeline_icon' ) || 'announcement' }
				onChange={ set( '_aiad_timeline_icon' ) }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Cover when no featured image', 'aiad-core' ) }
				help={ __(
					'Used if no featured image is set. Upload one to use your own photo instead.',
					'aiad-core'
				) }
				options={ options( config.covers ) }
				value={ text( '_aiad_timeline_cover_fallback' ) }
				onChange={ set( '_aiad_timeline_cover_fallback' ) }
			/>

			<FocalPoint contexts={ FOCAL_CONTEXTS } record={ record } />

			<ToggleControl
				__nextHasNoMarginBottom
				label={ __( 'Pin in benchmark results', 'aiad-core' ) }
				help={ __(
					'Always show this in the AI Risk Benchmark’s “More to read” links when the audience matches. Choose who should see it in the Audience panel.',
					'aiad-core'
				) }
				checked={ flag( '_airb_benchmark_outcome_pin' ) }
				onChange={ set( '_airb_benchmark_outcome_pin' ) }
			/>

			<p className="aiad-re-help">
				{ config.auto
					? __( 'Source: made automatically.', 'aiad-core' )
					: __( 'Source: written by hand.', 'aiad-core' ) }
			</p>
		</>
	);
}

registerRecordDetails( {
	postType: 'timeline',
	name: 'aiad-timeline-details',
	title: __( 'Entry details', 'aiad-core' ),
	Fields: EntryFields,
} );
