/**
 * Events: the Event details panel.
 *
 * An event's content is its description, which is the body on the canvas, so it
 * needs the panel and no canvas block. This is the old "Event details" meta box
 * (modules/live-sessions.php) with the same five fields. The audience is core's
 * own taxonomy panel. Times are the browser's own date and time field, which
 * gives the "YYYY-MM-DDTHH:MM" local time the front end reads.
 */
import { __ } from '@wordpress/i18n';
import { SelectControl, TextControl } from '@wordpress/components';
import { store as coreStore } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

import { registerRecordDetails } from '../shared/record-editor';

// What a new event is: the sessions are run on Teams.
const DEFAULT_FORMAT = 'LIVE — MS Teams';

function EventFields( record ) {
	const { set, text } = record;

	const partners = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'postType', 'partner', {
				per_page: -1,
				status: 'publish',
				orderby: 'title',
				order: 'asc',
				_fields: 'id,title',
			} ),
		[]
	);
	const isNew = useSelect(
		( select ) =>
			select( editorStore ).getCurrentPostAttribute( 'status' ) ===
			'auto-draft',
		[]
	);

	// A new event starts with the usual format filled in, as the classic box did.
	// An existing event with no format is left as it is.
	const format = text( '_session_format' );
	const setFormat = set( '_session_format' );
	useEffect( () => {
		if ( isNew && format === '' ) {
			setFormat( DEFAULT_FORMAT );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ isNew ] );

	const partnerId = record.meta?._session_partner_id || 0;

	return (
		<>
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="datetime-local"
				label={ __( 'Start time', 'aiad-core' ) }
				help={ __( 'Local time (Europe/London).', 'aiad-core' ) }
				value={ text( '_session_start_time' ) }
				onChange={ set( '_session_start_time' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="datetime-local"
				label={ __( 'End time', 'aiad-core' ) }
				value={ text( '_session_end_time' ) }
				onChange={ set( '_session_end_time' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Format', 'aiad-core' ) }
				placeholder={ DEFAULT_FORMAT }
				value={ format }
				onChange={ setFormat }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Provider (partner)', 'aiad-core' ) }
				help={ __(
					'Reuses the logo and name from the Partner post. Add the partner first under Partners.',
					'aiad-core'
				) }
				value={ String( partnerId ) }
				options={ [
					{
						value: '0',
						label: __( '— Select partner —', 'aiad-core' ),
					},
					...( partners || [] ).map( ( partner ) => ( {
						value: String( partner.id ),
						label: partner.title?.rendered || '',
					} ) ),
				] }
				onChange={ ( id ) =>
					set( '_session_partner_id' )( Number( id ) )
				}
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Registration / join URL', 'aiad-core' ) }
				help={ __(
					'Full URL required for online sessions (for example https://teams.microsoft.com/…). Used for the join button and the event schema.',
					'aiad-core'
				) }
				placeholder="https://"
				value={ text( '_session_registration_url' ) }
				onChange={ set( '_session_registration_url' ) }
			/>
		</>
	);
}

registerRecordDetails( {
	postType: 'live_session',
	name: 'aiad-event-details',
	title: __( 'Event details', 'aiad-core' ),
	Fields: EventFields,
} );
