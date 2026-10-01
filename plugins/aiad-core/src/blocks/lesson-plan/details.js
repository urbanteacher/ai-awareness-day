/**
 * Lesson details: the old Resource Details box, as a panel in the editor's
 * sidebar, on the record editor kit. Session length and format are core's own
 * taxonomy panels; the theme is a single choice, so it is a radio group here and
 * core's checkbox panel for it is removed.
 */
import { __ } from '@wordpress/i18n';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { store as editorStore } from '@wordpress/editor';
import {
	Button,
	CheckboxControl,
	SelectControl,
	TextControl,
	TextareaControl,
	BaseControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import {
	CardImageKeywords,
	FocalPoint,
	SingleTerm,
	registerRecordDetails,
	useEditorSetting,
} from '../../shared/record-editor';

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

// The lesson page crops its featured image once, for the page itself.
const FOCAL_CONTEXTS = [
	{
		key: '_aiad_thumbnail_focal_point_single',
		label: __( 'Lesson page', 'aiad-core' ),
		help: __(
			'Drag to keep faces or headline text in view when the image is cropped.',
			'aiad-core'
		),
		fallback: { x: 0.5, y: 0.5 },
	},
];

function LessonFields( record ) {
	const { set, text, list } = record;
	const published = useSelect(
		( select ) =>
			select( editorStore ).getEditedPostAttribute( 'status' ) ===
			'publish',
		[]
	);
	const lesson = useEditorSetting( 'aiadLesson' );

	const stages = list( '_aiad_key_stage' );
	const download = text( '_aiad_download_url' );

	return (
		<>
			<TextareaControl
				__nextHasNoMarginBottom
				label={ __( 'Subtitle', 'aiad-core' ) }
				help={ __(
					'One sentence under the title: what pupils will do. Up to 120 characters.',
					'aiad-core'
				) }
				rows={ 3 }
				maxLength={ 120 }
				value={ text( '_aiad_subtitle' ) }
				onChange={ set( '_aiad_subtitle' ) }
			/>

			<SingleTerm
				postType="resource"
				taxonomy="resource_principle"
				label={ __( 'Theme', 'aiad-core' ) }
				help={ __(
					'Sets the lesson’s colour and its place in Classroom resources.',
					'aiad-core'
				) }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Level', 'aiad-core' ) }
				options={ LEVELS }
				value={ text( '_aiad_level' ) }
				onChange={ set( '_aiad_level' ) }
			/>

			<SelectControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				label={ __( 'Status', 'aiad-core' ) }
				help={ __(
					'For the editorial checks; publishing is the Publish button.',
					'aiad-core'
				) }
				options={ STATUSES }
				value={
					text( '_aiad_status' ) ||
					( published ? 'published' : 'draft' )
				}
				onChange={ set( '_aiad_status' ) }
			/>

			<BaseControl
				id="aiad-lesson-key-stages"
				label={ __( 'Key stage', 'aiad-core' ) }
				__nextHasNoMarginBottom
			>
				{ Object.entries( lesson.keyStages || {} ).map(
					( [ slug, label ] ) => (
						<CheckboxControl
							key={ slug }
							__nextHasNoMarginBottom
							label={ label }
							checked={ stages.includes( slug ) }
							onChange={ ( on ) =>
								set( '_aiad_key_stage' )(
									on
										? [ ...stages, slug ]
										: stages.filter( ( s ) => s !== slug )
								)
							}
						/>
					)
				) }
			</BaseControl>

			<BaseControl
				id="aiad-lesson-download"
				label={ __( 'Download file', 'aiad-core' ) }
				help={
					lesson.slideforge
						? __(
								'Set by the SlideForge export: the lesson’s PDF.',
								'aiad-core'
							)
						: __(
								'The slides or worksheet teachers download. A PDF or PowerPoint is also shown beside the steps.',
								'aiad-core'
							)
				}
				__nextHasNoMarginBottom
			>
				{ download && (
					<p className="aiad-re-details__file">
						{ decodeURIComponent( download.split( '/' ).pop() ) }
					</p>
				) }
				<MediaUploadCheck>
					<MediaUpload
						onSelect={ ( media ) =>
							set( '_aiad_download_url' )( media?.url || '' )
						}
						render={ ( { open } ) => (
							<Button variant="secondary" onClick={ open }>
								{ download
									? __( 'Replace file', 'aiad-core' )
									: __( 'Choose a file', 'aiad-core' ) }
							</Button>
						) }
					/>
				</MediaUploadCheck>
				{ download && (
					<Button
						variant="link"
						isDestructive
						onClick={ () => set( '_aiad_download_url' )( '' ) }
					>
						{ __( 'Remove', 'aiad-core' ) }
					</Button>
				) }
			</BaseControl>

			<TextControl
				__next40pxDefaultSize
				__nextHasNoMarginBottom
				type="url"
				label={ __( 'Video preview', 'aiad-core' ) }
				help={ __(
					'A YouTube or video file link. Steps that name a time (“Video 2:01”) jump the player there.',
					'aiad-core'
				) }
				value={ text( '_aiad_preview_video_url' ) }
				onChange={ set( '_aiad_preview_video_url' ) }
			/>

			<CardImageKeywords
				record={ record }
				keyName="_aiad_image_keywords"
				help={ __(
					'Used to find a picture for the lesson’s card when it has no featured image.',
					'aiad-core'
				) }
			/>

			<FocalPoint contexts={ FOCAL_CONTEXTS } record={ record } />
		</>
	);
}

registerRecordDetails( {
	postType: 'resource',
	name: 'aiad-lesson-details',
	title: __( 'Lesson details', 'aiad-core' ),
	// A lesson picks its theme above (a single choice), not in core's checkboxes.
	hidePanels: [ 'taxonomy-panel-resource_principle' ],
	Fields: LessonFields,
} );
