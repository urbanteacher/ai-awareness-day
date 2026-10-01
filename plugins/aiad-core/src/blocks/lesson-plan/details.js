/**
 * Lesson details: the old Resource Details box, as a panel in the editor's
 * sidebar. Session length and format are core's own taxonomy panels; the theme
 * is a single choice, so it is a radio group here and core's checkbox panel for
 * it is removed.
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel, store as editorStore } from '@wordpress/editor';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import {
	Button,
	CheckboxControl,
	RadioControl,
	SelectControl,
	TextControl,
	TextareaControl,
	BaseControl,
} from '@wordpress/components';
import { useEntityProp, store as coreStore } from '@wordpress/core-data';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';

const LEVELS = [
	{ value: '', label: __( 'Not set', 'aiad-core' ) },
	{ value: 'beginner', label: __( 'Beginner', 'aiad-core' ) },
	{ value: 'intermediate', label: __( 'Intermediate', 'aiad-core' ) },
	{ value: 'advanced', label: __( 'Advanced', 'aiad-core' ) },
];

const STATUSES = [
	{ value: 'draft', label: __( 'Draft', 'aiad-core' ) },
	{ value: 'in_review', label: __( 'In review', 'aiad-core' ) },
	{ value: 'published', label: __( 'Published', 'aiad-core' ) },
];

function LessonDetails() {
	const postType = useSelect( ( select ) => select( editorStore ).getCurrentPostType(), [] );
	const { removeEditorPanel } = useDispatch( editorStore );

	useEffect( () => {
		// A lesson picks its theme in the panel below (a single choice), not in core's checkboxes.
		if ( postType === 'resource' ) {
			removeEditorPanel( 'taxonomy-panel-resource_principle' );
		}
		// Featured resources share these taxonomies, and their meta box already holds the theme and
		// session length. Turning on REST for the taxonomies gave them core's panels as well, two
		// controls for one value, so those two are removed here. (Formats had its panel before.)
		if ( postType === 'featured_resource' ) {
			removeEditorPanel( 'taxonomy-panel-resource_principle' );
			removeEditorPanel( 'taxonomy-panel-resource_duration' );
		}
	}, [ postType, removeEditorPanel ] );

	const { toggleEditorPanelOpened } = useDispatch( editorStore );
	const panelOpen = useSelect( ( select ) => select( editorStore ).isEditorPanelOpened( 'aiad-lesson-details/aiad-lesson-details' ), [] );
	const published = useSelect( ( select ) => select( editorStore ).getEditedPostAttribute( 'status' ) === 'publish', [] );

	/* Open the first time an editor meets it, so the fields are found; after
	   that the editor's own choice to close it is kept. */
	useEffect( () => {
		const seen = 'aiadLessonDetailsSeen';
		try {
			if ( postType === 'resource' && ! panelOpen && ! window.localStorage.getItem( seen ) ) {
				toggleEditorPanelOpened( 'aiad-lesson-details/aiad-lesson-details' );
			}
			if ( postType === 'resource' ) {
				window.localStorage.setItem( seen, '1' );
			}
		} catch ( e ) {}
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ postType ] );

	const [ meta, setMeta ] = useEntityProp( 'postType', 'resource', 'meta' );
	const [ themeIds, setThemeIds ] = useEntityProp( 'postType', 'resource', 'resource_principle' );
	const themes = useSelect(
		( select ) => select( coreStore ).getEntityRecords( 'taxonomy', 'resource_principle', { per_page: -1, orderby: 'id', order: 'asc' } ),
		[]
	);
	const lesson = useSelect( ( select ) => select( editorStore ).getEditorSettings().aiadLesson || {}, [] );

	if ( postType !== 'resource' || ! meta ) {
		return null;
	}

	const set = ( key ) => ( value ) => setMeta( { ...meta, [ key ]: value } );
	const text = ( key ) => ( typeof meta[ key ] === 'string' ? meta[ key ] : '' );
	const stages = Array.isArray( meta._aiad_key_stage ) ? meta._aiad_key_stage : [];
	const download = text( '_aiad_download_url' );

	return (
		<PluginDocumentSettingPanel name="aiad-lesson-details" title={ __( 'Lesson details', 'aiad-core' ) } className="aiad-lesson-details">
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Subtitle', 'aiad-core' ) }
				help={ __( 'One sentence under the title: what pupils will do. Up to 120 characters.', 'aiad-core' ) }
				rows={ 3 }
				maxLength={ 120 }
				value={ text( '_aiad_subtitle' ) }
				onChange={ set( '_aiad_subtitle' ) }
			/>

			{ Array.isArray( themes ) && (
				<RadioControl
					label={ __( 'Theme', 'aiad-core' ) }
					help={ __( 'Sets the lesson’s colour and its place in Classroom resources.', 'aiad-core' ) }
					selected={ String( ( themeIds || [] )[ 0 ] || '' ) }
					options={ themes.map( ( t ) => ( { value: String( t.id ), label: t.name } ) ) }
					onChange={ ( id ) => setThemeIds( [ Number( id ) ] ) }
				/>
			) }

			<SelectControl __next40pxDefaultSize __nextHasNoMarginBottom label={ __( 'Level', 'aiad-core' ) } options={ LEVELS } value={ text( '_aiad_level' ) } onChange={ set( '_aiad_level' ) } />

			<SelectControl __next40pxDefaultSize __nextHasNoMarginBottom label={ __( 'Status', 'aiad-core' ) } help={ __( 'For the editorial checks; publishing is the Publish button.', 'aiad-core' ) } options={ STATUSES } value={ text( '_aiad_status' ) || ( published ? 'published' : 'draft' ) } onChange={ set( '_aiad_status' ) } />

			<BaseControl id="aiad-lesson-key-stages" label={ __( 'Key stage', 'aiad-core' ) } __nextHasNoMarginBottom>
				{ Object.entries( lesson.keyStages || {} ).map( ( [ slug, label ] ) => (
					<CheckboxControl
						key={ slug }
						__nextHasNoMarginBottom
						label={ label }
						checked={ stages.includes( slug ) }
						onChange={ ( on ) => set( '_aiad_key_stage' )( on ? [ ...stages, slug ] : stages.filter( ( s ) => s !== slug ) ) }
					/>
				) ) }
			</BaseControl>

			<BaseControl id="aiad-lesson-download" label={ __( 'Download file', 'aiad-core' ) } help={ lesson.slideforge ? __( 'Set by the SlideForge export: the lesson’s PDF.', 'aiad-core' ) : __( 'The slides or worksheet teachers download. A PDF or PowerPoint is also shown beside the steps.', 'aiad-core' ) } __nextHasNoMarginBottom>
				{ download && <p className="aiad-lesson-details__file">{ decodeURIComponent( download.split( '/' ).pop() ) }</p> }
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( media ) => set( '_aiad_download_url' )( media?.url || '' ) }
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ download ? __( 'Replace file', 'aiad-core' ) : __( 'Choose a file', 'aiad-core' ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
				{ download && (
					<Button variant="link" isDestructive onClick={ () => set( '_aiad_download_url' )( '' ) }>
						{ __( 'Remove', 'aiad-core' ) }
					</Button>
				) }
			</BaseControl>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Video preview', 'aiad-core' ) }
				help={ __( 'A YouTube or video file link. Steps that name a time (“Video 2:01”) jump the player there.', 'aiad-core' ) }
				value={ text( '_aiad_preview_video_url' ) }
				onChange={ set( '_aiad_preview_video_url' ) }
			/>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Card image keywords', 'aiad-core' ) }
				help={ __( 'Used to find a picture for the lesson’s card when it has no featured image.', 'aiad-core' ) }
				value={ text( '_aiad_image_keywords' ) }
				onChange={ set( '_aiad_image_keywords' ) }
			/>
		</PluginDocumentSettingPanel>
	);
}

registerPlugin( 'aiad-lesson-details', { render: LessonDetails } );
