/* eslint-disable camelcase -- the lesson's meta fields (resource_ref, student_action, teacher_tip, key_stage_adapted) are snake_case in the database and in REST. */
/**
 * The Lesson plan block's editor: the lesson's meta, edited in the order the
 * lesson page reads (single-resource.php). Every change goes to the post's
 * meta through the editor's own store and is saved with the post, to the same
 * meta keys the old meta boxes wrote. Lessons keep no revisions, as before.
 */
import { __, _n, sprintf } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import {
	CheckboxControl,
	Notice,
	SelectControl,
	TabPanel,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';

import {
	Rows,
	Section,
	useEditorSetting,
	useRecordMeta,
} from '../../shared/record-editor';
import { lessonTime, lengthLabel } from './time';

const EXTENSION_TYPES = [
	{ value: '', label: __( 'Type (optional)', 'aiad-core' ) },
	{ value: 'homework', label: __( 'Homework', 'aiad-core' ) },
	{ value: 'next_lesson', label: __( 'Next lesson', 'aiad-core' ) },
	{ value: 'cross_curricular', label: __( 'Cross-curricular', 'aiad-core' ) },
	{ value: 'independent', label: __( 'Independent', 'aiad-core' ) },
];

const MATERIAL_TYPES = [
	{ value: '', label: __( 'Type (optional)', 'aiad-core' ) },
	{ value: 'slides', label: __( 'Slides', 'aiad-core' ) },
	{ value: 'worksheet', label: __( 'Worksheet', 'aiad-core' ) },
	{ value: 'handout', label: __( 'Handout', 'aiad-core' ) },
	{ value: 'video', label: __( 'Video', 'aiad-core' ) },
	{ value: 'link', label: __( 'Link', 'aiad-core' ) },
	{ value: 'other', label: __( 'Other', 'aiad-core' ) },
];

const AGES = [
	{ name: 'primary', title: __( 'Primary', 'aiad-core' ) },
	{ name: 'secondary', title: __( 'Secondary', 'aiad-core' ) },
	{ name: 'post16', title: __( 'Post-16', 'aiad-core' ) },
];

/**
 * A meta value that should be a list, as one: legacy rows can be strings or missing.
 *
 * @param {Object} value The stored value.
 * @return {Array} The value if it is a list, else an empty one.
 */
const list = ( value ) => ( Array.isArray( value ) ? value : [] );

export default function Edit() {
	const blockProps = useBlockProps( { className: 'aiad-lp' } );
	const { meta, set, text } = useRecordMeta( 'resource' );
	const lesson = useEditorSetting( 'aiadLesson' );

	if ( ! meta ) {
		return (
			<div { ...blockProps }>
				{ __(
					'This block edits a lesson, and only works on one.',
					'aiad-core'
				) }
			</div>
		);
	}

	const steps = list( meta._aiad_instructions );
	const time = lessonTime( steps );
	const diff =
		meta._aiad_differentiation &&
		typeof meta._aiad_differentiation === 'object'
			? meta._aiad_differentiation
			: {};
	const pack =
		meta._aiad_debate_pack &&
		typeof meta._aiad_debate_pack === 'object' &&
		! Array.isArray( meta._aiad_debate_pack )
			? meta._aiad_debate_pack
			: {};

	/* Six lessons take these fields from their SlideForge deck, through
	   assets/lessons/2027/lessons.json; an edit here lasts until the next export. */
	const fromDeck = lesson.slideforge ? (
		<Notice
			status="info"
			isDismissible={ false }
			className="aiad-re-notice"
		>
			{ __(
				'This lesson’s slides are built in SlideForge, and so are its steps, preparation and debate. Change them there and re-export: an edit made here is replaced by the next export.',
				'aiad-core'
			) }
		</Notice>
	) : null;

	return (
		<div { ...blockProps }>
			<p className="aiad-lp__intro">
				{ __(
					'The lesson plan, in the order the lesson page shows it. Write any introduction above this block; the title, subtitle, theme and files are in Lesson details in the sidebar.',
					'aiad-core'
				) }
			</p>

			<Section
				title={ __( 'Before you teach', 'aiad-core' ) }
				note={ fromDeck }
			>
				<h3 className="aiad-re-label">
					{ __( 'Get ready', 'aiad-core' ) }
				</h3>
				<Rows
					items={ list( meta._aiad_preparation ) }
					onChange={ set( '_aiad_preparation' ) }
					blank=""
					addLabel={ __( 'Add something to get ready', 'aiad-core' ) }
					row={ ( item, update ) => (
						<TextareaControl
							__nextHasNoMarginBottom
							label={ __( 'To get ready', 'aiad-core' ) }
							hideLabelFromVision
							rows={ 2 }
							value={ typeof item === 'string' ? item : '' }
							onChange={ update }
						/>
					) }
				/>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __( 'Teacher notes', 'aiad-core' ) }
					help={ __(
						'Background, sensitivity and safeguarding. Start a paragraph with a short label and a colon (“Sensitivity:”) to set it in bold; start lines with “- ” for a list.',
						'aiad-core'
					) }
					rows={ 5 }
					value={ text( '_aiad_teacher_notes' ) }
					onChange={ set( '_aiad_teacher_notes' ) }
				/>
			</Section>

			<Section title={ __( 'Learning objectives', 'aiad-core' ) }>
				<Rows
					items={ list( meta._aiad_learning_objectives ) }
					onChange={ set( '_aiad_learning_objectives' ) }
					blank={ { objective: '' } }
					addLabel={ __( 'Add an objective', 'aiad-core' ) }
					numbered
					row={ ( item, update ) => (
						<TextControl
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							label={ __( 'Objective', 'aiad-core' ) }
							hideLabelFromVision
							placeholder={ __(
								'Start with a verb: understand, recognise, explain…',
								'aiad-core'
							) }
							value={ item?.objective || '' }
							onChange={ ( objective ) =>
								update( { objective } )
							}
						/>
					) }
				/>
			</Section>

			<Section
				title={ __( 'The lesson', 'aiad-core' ) }
				note={
					<>
						{ fromDeck }
						<p className="aiad-re-meta">
							{ sprintf(
								/* translators: %d: number of steps. */
								_n(
									'%d step',
									'%d steps',
									steps.length,
									'aiad-core'
								),
								steps.length
							) }
							{ time.total > 0
								? ' · ' + lengthLabel( time.total )
								: ' · ' +
									__(
										'give every step a time to show the lesson clock',
										'aiad-core'
									) }
							{ time.optional > 0
								? ' · ' +
									sprintf(
										/* translators: %s: a length of time, e.g. "6 min". */
										__( '%s optional', 'aiad-core' ),
										lengthLabel( time.optional )
									)
								: '' }
						</p>
					</>
				}
			>
				<Rows
					items={ steps }
					onChange={ ( next ) =>
						set( '_aiad_instructions' )(
							next.map( ( s, i ) => ( { ...s, step: i + 1 } ) )
						)
					}
					blank={ {
						action: '',
						duration: '',
						resource_ref: '',
						student_action: '',
						teacher_tip: '',
						optional: false,
					} }
					addLabel={ __( 'Add a step', 'aiad-core' ) }
					numbered
					row={ ( item, update ) => (
						<>
							<TextareaControl
								__nextHasNoMarginBottom
								label={ __(
									'What the teacher does',
									'aiad-core'
								) }
								rows={ 2 }
								value={ item?.action || '' }
								onChange={ ( action ) => update( { action } ) }
							/>
							<div className="aiad-re-grid">
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Time', 'aiad-core' ) }
									placeholder={ __( '1 min', 'aiad-core' ) }
									value={ item?.duration || '' }
									onChange={ ( duration ) =>
										update( { duration } )
									}
								/>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __(
										'Slide or video',
										'aiad-core'
									) }
									placeholder={ __(
										'Slide 3, or Video 2:01–3:16',
										'aiad-core'
									) }
									value={ item?.resource_ref || '' }
									onChange={ ( resource_ref ) =>
										update( { resource_ref } )
									}
								/>
								<TextControl
									__next40pxDefaultSize
									__nextHasNoMarginBottom
									label={ __( 'Pupils', 'aiad-core' ) }
									placeholder={ __(
										'Pair discussion',
										'aiad-core'
									) }
									value={ item?.student_action || '' }
									onChange={ ( student_action ) =>
										update( { student_action } )
									}
								/>
							</div>
							<TextareaControl
								__nextHasNoMarginBottom
								label={ __( 'Teacher tip', 'aiad-core' ) }
								rows={ 2 }
								value={ item?.teacher_tip || '' }
								onChange={ ( teacher_tip ) =>
									update( { teacher_tip } )
								}
							/>
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __(
									'Optional — shown, but left off the lesson time',
									'aiad-core'
								) }
								checked={ !! item?.optional }
								onChange={ ( optional ) =>
									update( { optional } )
								}
							/>
						</>
					) }
				/>
			</Section>

			<Section title={ __( 'Big question', 'aiad-core' ) }>
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __(
						'The question to put on the board',
						'aiad-core'
					) }
					rows={ 2 }
					value={ text( '_aiad_discussion_question' ) }
					onChange={ set( '_aiad_discussion_question' ) }
				/>
			</Section>

			<Section title={ __( 'Adapting the lesson', 'aiad-core' ) }>
				{ [
					[
						'support',
						__(
							'Support — for pupils who need more help',
							'aiad-core'
						),
					],
					[
						'stretch',
						__(
							'Stretch — for pupils ready to go further',
							'aiad-core'
						),
					],
					[
						'send',
						__( 'SEND — for additional needs', 'aiad-core' ),
					],
				].map( ( [ key, label ] ) => (
					<TextareaControl
						key={ key }
						__nextHasNoMarginBottom
						label={ label }
						rows={ 2 }
						value={ diff[ key ] || '' }
						onChange={ ( value ) =>
							set( '_aiad_differentiation' )( {
								...diff,
								[ key ]: value,
							} )
						}
					/>
				) ) }
				<TextareaControl
					__nextHasNoMarginBottom
					label={ __(
						'Debate it — the card for the National AI Conversation',
						'aiad-core'
					) }
					help={ __(
						'About 30 words. Left empty, the card gives general advice built on the big question.',
						'aiad-core'
					) }
					rows={ 2 }
					value={ text( '_aiad_debate' ) }
					onChange={ set( '_aiad_debate' ) }
				/>
			</Section>

			<Section
				title={ __( 'Set up the debate', 'aiad-core' ) }
				note={ fromDeck }
			>
				<p className="aiad-re-help">
					{ __(
						'One set-up per age group. An age with no motion uses the National Conversation’s motion bank for the lesson’s theme. Points: one per line.',
						'aiad-core'
					) }
				</p>
				<TabPanel className="aiad-lp-tabs" tabs={ AGES }>
					{ ( tab ) => {
						const field = ( part ) =>
							pack[ tab.name + '_' + part ] || '';
						const setField = ( part ) => ( value ) =>
							set( '_aiad_debate_pack' )( {
								...pack,
								[ tab.name + '_' + part ]: value,
							} );
						return (
							<div className="aiad-lp-tab">
								<TextareaControl
									__nextHasNoMarginBottom
									label={ __( 'Motion', 'aiad-core' ) }
									rows={ 2 }
									value={ field( 'motion' ) }
									onChange={ setField( 'motion' ) }
								/>
								<TextareaControl
									__nextHasNoMarginBottom
									label={ __(
										'Help to get going',
										'aiad-core'
									) }
									help={ __(
										'A sentence starter, a “What if…?” challenge, or the tension and a research question.',
										'aiad-core'
									) }
									rows={ 2 }
									value={ field( 'prompt' ) }
									onChange={ setField( 'prompt' ) }
								/>
								<div className="aiad-re-grid aiad-re-grid--two">
									<TextareaControl
										__nextHasNoMarginBottom
										label={ __( 'For', 'aiad-core' ) }
										rows={ 5 }
										value={ field( 'for' ) }
										onChange={ setField( 'for' ) }
									/>
									<TextareaControl
										__nextHasNoMarginBottom
										label={ __( 'Against', 'aiad-core' ) }
										rows={ 5 }
										value={ field( 'against' ) }
										onChange={ setField( 'against' ) }
									/>
								</div>
							</div>
						);
					} }
				</TabPanel>
			</Section>

			<Section title={ __( 'Key words', 'aiad-core' ) }>
				<Rows
					items={ list( meta._aiad_key_definitions ) }
					onChange={ set( '_aiad_key_definitions' ) }
					blank={ {
						term: '',
						definition: '',
						key_stage_adapted: false,
					} }
					addLabel={ __( 'Add a key word', 'aiad-core' ) }
					row={ ( item, update ) => (
						<>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Word', 'aiad-core' ) }
								value={ item?.term || '' }
								onChange={ ( term ) => update( { term } ) }
							/>
							<TextareaControl
								__nextHasNoMarginBottom
								label={ __( 'Definition', 'aiad-core' ) }
								rows={ 2 }
								value={ item?.definition || '' }
								onChange={ ( definition ) =>
									update( { definition } )
								}
							/>
							<CheckboxControl
								__nextHasNoMarginBottom
								label={ __(
									'Simplified for key stage',
									'aiad-core'
								) }
								checked={ !! item?.key_stage_adapted }
								onChange={ ( key_stage_adapted ) =>
									update( { key_stage_adapted } )
								}
							/>
						</>
					) }
				/>
			</Section>

			<Section title={ __( 'Take it further', 'aiad-core' ) }>
				<Rows
					items={ list( meta._aiad_extensions ) }
					onChange={ set( '_aiad_extensions' ) }
					blank={ { activity: '', type: '' } }
					addLabel={ __( 'Add an extension activity', 'aiad-core' ) }
					row={ ( item, update ) => (
						<div className="aiad-re-grid aiad-re-grid--wide">
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Activity', 'aiad-core' ) }
								value={ item?.activity || '' }
								onChange={ ( activity ) =>
									update( { activity } )
								}
							/>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Type', 'aiad-core' ) }
								options={ EXTENSION_TYPES }
								value={ item?.type || '' }
								onChange={ ( type ) => update( { type } ) }
							/>
						</div>
					) }
				/>
			</Section>

			<Section title={ __( 'Materials', 'aiad-core' ) }>
				<Rows
					items={ list( meta._aiad_resources ) }
					onChange={ set( '_aiad_resources' ) }
					blank={ { name: '', type: '', url: '' } }
					addLabel={ __( 'Add a material', 'aiad-core' ) }
					row={ ( item, update ) => (
						<div className="aiad-re-grid">
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Name', 'aiad-core' ) }
								value={ item?.name || '' }
								onChange={ ( name ) => update( { name } ) }
							/>
							<SelectControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								label={ __( 'Type', 'aiad-core' ) }
								options={ MATERIAL_TYPES }
								value={ item?.type || '' }
								onChange={ ( type ) => update( { type } ) }
							/>
							<TextControl
								__next40pxDefaultSize
								__nextHasNoMarginBottom
								type="url"
								label={ __( 'Link', 'aiad-core' ) }
								value={ item?.url || '' }
								onChange={ ( url ) => update( { url } ) }
							/>
						</div>
					) }
				/>
			</Section>
		</div>
	);
}
