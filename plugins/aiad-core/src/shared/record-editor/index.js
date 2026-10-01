/**
 * The record editor kit.
 *
 * Every content type of the site is edited the same way in the block editor: its
 * fields are on the editor's own screen, in a "Details" panel in the sidebar and,
 * where the content is structured meta rather than the post body, in a block on
 * the canvas (the lesson's Lesson plan). This file is what those share, so a new
 * type is configuration, not a new build:
 *
 *   registerRecordDetails()  the sidebar panel: registered, post type checked,
 *                            core's duplicate panels removed, opened the first time
 *   useRecordMeta()          the post's meta, with set/text/flag/list helpers
 *   Section, Rows            the canvas block's building blocks
 *   FocalPoint               an image's focal point, for the featured image
 *   useEditorSetting()       a value PHP put in the editor's settings
 *
 * See docs/WP71-STANDARDISATION.md for why, and for the order the types follow.
 */
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { registerPlugin } from '@wordpress/plugins';
import {
	PluginDocumentSettingPanel,
	store as editorStore,
} from '@wordpress/editor';
import {
	Button,
	FocalPointPicker,
	RadioControl,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp, store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

import './record-editor.scss';

/** The post type being edited. */
function useCurrentPostType() {
	return useSelect(
		( select ) => select( editorStore ).getCurrentPostType(),
		[]
	);
}

/**
 * A value PHP put in the editor's settings under `aiadLesson`, `aiadTimeline`, and so on.
 *
 * @param {string} key The setting's name.
 * @return {Object} The setting, or an empty object.
 */
export function useEditorSetting( key ) {
	return useSelect(
		( select ) => select( editorStore ).getEditorSettings()[ key ] || {},
		[ key ]
	);
}

/**
 * The post's meta, and helpers for reading and writing it.
 *
 * @param {string} postType The type being edited.
 * @return {Object} { meta, set, text, flag, list }; meta is undefined when the type has none.
 */
export function useRecordMeta( postType ) {
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );
	return {
		meta,
		// set( key )( value ) writes one field.
		// The editor merges this one change into the meta it holds and saves the whole
		// object, so anything the server changes while an editor has the screen open
		// would be put back. Keep server-managed fields (counters) out of REST instead:
		// see modules/tracking/engagement-tracking.php.
		set: ( key ) => ( value ) => setMeta( { [ key ]: value } ),
		text: ( key ) =>
			meta && typeof meta[ key ] === 'string' ? meta[ key ] : '',
		flag: ( key ) => !! ( meta && meta[ key ] ),
		list: ( key ) =>
			meta && Array.isArray( meta[ key ] ) ? meta[ key ] : [],
	};
}

/**
 * Hide core's panels for a post type: its own panel does the job better, or a
 * meta box already holds the field.
 *
 * @param {string}   postType The type.
 * @param {string[]} panels   Panel names, e.g. 'taxonomy-panel-resource_principle'.
 */
export function useHidePanels( postType, panels ) {
	const current = useCurrentPostType();
	const { removeEditorPanel } = useDispatch( editorStore );
	useEffect( () => {
		if ( current === postType ) {
			panels.forEach( ( name ) => removeEditorPanel( name ) );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ current, postType ] );
}

/**
 * Register a type's Details panel.
 *
 * @param {Object}   config
 * @param {string}   config.postType   The type this panel belongs to.
 * @param {string}   config.name       Panel and plugin name, e.g. 'aiad-lesson-details'.
 * @param {string}   config.title      Panel title.
 * @param {string[]} config.hidePanels Core panels this one replaces.
 * @param {Object}   config.Fields     A component drawing the fields; gets { meta, set, text, flag, list }.
 */
export function registerRecordDetails( {
	postType,
	name,
	title,
	hidePanels = [],
	Fields,
} ) {
	function Details() {
		const current = useCurrentPostType();
		const record = useRecordMeta( postType );
		const panelName = `${ name }/${ name }`;
		const open = useSelect(
			( select ) =>
				select( editorStore ).isEditorPanelOpened( panelName ),
			[ panelName ]
		);
		const { toggleEditorPanelOpened } = useDispatch( editorStore );

		useHidePanels( postType, hidePanels );

		// Open the first time an editor meets it, so the fields are found; after that
		// the editor's own choice to close it is kept.
		useEffect( () => {
			if ( current !== postType ) {
				return;
			}
			const seen = `${ name }:seen`;
			try {
				if ( ! open && ! window.localStorage.getItem( seen ) ) {
					toggleEditorPanelOpened( panelName );
				}
				window.localStorage.setItem( seen, '1' );
			} catch {
				// Storage can be blocked; the panel then simply opens as the editor left it.
			}
			// eslint-disable-next-line react-hooks/exhaustive-deps
		}, [ current ] );

		if ( current !== postType || ! record.meta ) {
			return null;
		}
		return (
			<PluginDocumentSettingPanel
				name={ name }
				title={ title }
				className={ `aiad-re-details ${ name }` }
			>
				<Fields { ...record } />
			</PluginDocumentSettingPanel>
		);
	}
	registerPlugin( name, { render: Details } );
}

/**
 * A taxonomy where a record has exactly one term (a lesson's theme, a resource's
 * format). Core's panel for a taxonomy is a list of checkboxes that allows several;
 * this is the single choice the classic box offered. Hide core's panel for the
 * taxonomy with `hidePanels` when you use this.
 *
 * @param {Object}  props
 * @param {string}  props.postType Post type being edited.
 * @param {string}  props.taxonomy Taxonomy slug, e.g. 'resource_principle'.
 * @param {string}  props.label    The control's label.
 * @param {string}  props.help     Help under it.
 * @param {string}  props.variant  'radio' (few terms, all visible) or 'select'.
 * @param {boolean} props.optional Offer "none" (select only).
 * @return {Element|null} The control, once the terms have loaded.
 */
export function SingleTerm( {
	postType,
	taxonomy,
	label,
	help,
	variant = 'radio',
	optional = false,
} ) {
	const [ termIds, setTermIds ] = useEntityProp(
		'postType',
		postType,
		taxonomy
	);
	const terms = useSelect(
		( select ) =>
			select( coreStore ).getEntityRecords( 'taxonomy', taxonomy, {
				per_page: -1,
				orderby: 'id',
				order: 'asc',
			} ),
		[ taxonomy ]
	);
	if ( ! Array.isArray( terms ) ) {
		return null;
	}
	const selected = String( ( termIds || [] )[ 0 ] || '' );
	const options = terms.map( ( t ) => ( {
		value: String( t.id ),
		label: t.name,
	} ) );
	const onChange = ( id ) => setTermIds( id ? [ Number( id ) ] : [] );

	if ( variant === 'select' ) {
		return (
			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ label }
				help={ help }
				value={ selected }
				options={ [
					...( optional
						? [
								{
									value: '',
									label: __( '— None —', 'aiad-core' ),
								},
							]
						: [] ),
					...options,
				] }
				onChange={ onChange }
			/>
		);
	}
	return (
		<RadioControl
			label={ label }
			help={ help }
			selected={ selected }
			options={ options }
			onChange={ onChange }
		/>
	);
}

/**
 * Card image keywords, with the Fetch image button: asks the server for a picture
 * for the keywords, which it saves to the media library, and takes it as the
 * record's featured image (modules/admin/card-image.php).
 *
 * @param {Object} props
 * @param {Object} props.record  The result of useRecordMeta().
 * @param {string} props.keyName The meta key that holds the keywords.
 * @param {string} props.help    Help under the field.
 * @return {Element} The control.
 */
export function CardImageKeywords( { record, keyName, help } ) {
	const postId = useSelect(
		( select ) => select( editorStore ).getCurrentPostId(),
		[]
	);
	const { editPost } = useDispatch( editorStore );
	const [ state, setState ] = useState( { busy: false, ok: true, text: '' } );
	const keywords = record.text( keyName );

	async function fetchImage() {
		if ( ! keywords.trim() ) {
			setState( {
				busy: false,
				ok: false,
				text: __( 'Enter keywords first.', 'aiad-core' ),
			} );
			return;
		}
		setState( { busy: true, ok: true, text: '' } );
		try {
			const result = await apiFetch( {
				path: `/aiad/v1/card-image/${ postId }`,
				method: 'POST',
				data: { keywords },
			} );
			editPost( { featured_media: result.attachment_id } );
			setState( {
				busy: false,
				ok: true,
				text: __( 'Featured image set.', 'aiad-core' ),
			} );
		} catch ( error ) {
			setState( {
				busy: false,
				ok: false,
				text: sprintf(
					/* translators: %s: the reason the image could not be fetched. */
					__( 'Error: %s', 'aiad-core' ),
					String(
						error?.message || __( 'unknown error', 'aiad-core' )
					)
				),
			} );
		}
	}

	return (
		<div className="aiad-re-fetch">
			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Card image keywords', 'aiad-core' ) }
				help={ help }
				value={ keywords }
				onChange={ record.set( keyName ) }
			/>
			<Button
				variant="secondary"
				isBusy={ state.busy }
				disabled={ state.busy }
				onClick={ fetchImage }
			>
				{ state.busy
					? __( 'Fetching…', 'aiad-core' )
					: __( 'Fetch image', 'aiad-core' ) }
			</Button>
			{ state.text && (
				<p
					className={ `aiad-re-fetch__status${ state.ok ? '' : ' is-error' }` }
					role="status"
				>
					{ state.text }
				</p>
			) }
		</div>
	);
}

/**
 * A heading with a rule above it, as the lesson page and the National Conversation page draw their sections.
 *
 * @param {Object}  props
 * @param {string}  props.title    The heading.
 * @param {Element} props.children The section's content.
 * @param {Element} props.note     Something to show between the heading and the content.
 * @return {Element} The section.
 */
export function Section( { title, children, note } ) {
	return (
		<section className="aiad-re-section">
			<h2 className="aiad-re-section__title">{ title }</h2>
			{ note }
			{ children }
		</section>
	);
}

/**
 * A list of rows with move up, move down and remove on each, and an add button.
 * `row( item, update, index )` draws one row's fields; `update( patch )` merges into it.
 *
 * @param {Object}  props
 * @param {Array}   props.items    The rows.
 * @param {Object}  props.onChange Called with the new list.
 * @param {Object}  props.blank    What a new row starts as (a string, or an object of blank fields).
 * @param {string}  props.addLabel The add button's text.
 * @param {Object}  props.row      Draws one row: row( item, update, index ).
 * @param {boolean} props.numbered Number the rows.
 * @return {Element} The list.
 */
export function Rows( { items, onChange, blank, addLabel, row, numbered } ) {
	const move = ( from, to ) => {
		const next = [ ...items ];
		next.splice( to, 0, next.splice( from, 1 )[ 0 ] );
		onChange( next );
	};
	return (
		<div className="aiad-re-rows">
			{ items.map( ( item, i ) => (
				<div className="aiad-re-row" key={ i }>
					{ numbered && (
						<span className="aiad-re-row__num">{ i + 1 }</span>
					) }
					<div className="aiad-re-row__fields">
						{ row(
							item,
							( patch ) => {
								const next = [ ...items ];
								next[ i ] =
									typeof patch === 'object' &&
									patch !== null &&
									typeof item === 'object'
										? { ...item, ...patch }
										: patch;
								onChange( next );
							},
							i
						) }
					</div>
					<div className="aiad-re-row__tools">
						<Button
							icon="arrow-up-alt2"
							label={ __( 'Move up', 'aiad-core' ) }
							size="small"
							disabled={ i === 0 }
							onClick={ () => move( i, i - 1 ) }
						/>
						<Button
							icon="arrow-down-alt2"
							label={ __( 'Move down', 'aiad-core' ) }
							size="small"
							disabled={ i === items.length - 1 }
							onClick={ () => move( i, i + 1 ) }
						/>
						<Button
							icon="trash"
							label={ __( 'Remove', 'aiad-core' ) }
							size="small"
							isDestructive
							onClick={ () =>
								onChange( items.filter( ( _, j ) => j !== i ) )
							}
						/>
					</div>
				</div>
			) ) }
			<Button
				variant="secondary"
				onClick={ () => onChange( [ ...items, blank ] ) }
			>
				{ addLabel }
			</Button>
		</div>
	);
}

/**
 * Where an image is cropped from: one picker for each place the featured image is
 * shown. Stored as { x, y } from 0 to 1 in the meta the theme reads
 * (aiad_get_post_thumbnail_focal_point()).
 *
 * @param {Object}   props
 * @param {Object[]} props.contexts [ { key: '_aiad_thumbnail_focal_point_feed', label, help, fallback: { x, y } } ]
 * @param {Object}   props.record   The result of useRecordMeta().
 */
export function FocalPoint( { contexts, record } ) {
	const imageUrl = useSelect( ( select ) => {
		const id =
			select( editorStore ).getEditedPostAttribute( 'featured_media' );
		const media = id ? select( coreStore ).getMedia( id ) : null;
		return (
			media?.media_details?.sizes?.large?.source_url ||
			media?.source_url ||
			''
		);
	}, [] );

	return (
		<div className="aiad-re-focal">
			<h3 className="aiad-re-label">
				{ __( 'Image focal point', 'aiad-core' ) }
			</h3>
			{ ! imageUrl && (
				<p className="aiad-re-help">
					{ __(
						'Set a featured image, then drag to choose what stays in view when it is cropped.',
						'aiad-core'
					) }
				</p>
			) }
			{ imageUrl &&
				contexts.map( ( context ) => {
					const stored = record.meta?.[ context.key ];
					const value =
						stored &&
						typeof stored.x === 'number' &&
						typeof stored.y === 'number'
							? stored
							: context.fallback;
					return (
						<div
							className="aiad-re-focal__context"
							key={ context.key }
						>
							<FocalPointPicker
								__nextHasNoMarginBottom
								label={ context.label }
								help={ context.help }
								url={ imageUrl }
								value={ value }
								onChange={ ( point ) =>
									record.set( context.key )( {
										x: Math.round( point.x * 100 ) / 100,
										y: Math.round( point.y * 100 ) / 100,
									} )
								}
							/>
						</div>
					);
				} ) }
		</div>
	);
}
