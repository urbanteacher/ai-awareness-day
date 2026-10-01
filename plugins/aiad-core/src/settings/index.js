/**
 * Settings → AI Awareness Day.
 *
 * One screen for the site's settings. It reads and saves through core's settings
 * endpoint, as the `site` entity in core-data, so it needs no routes of its own;
 * each option is registered for REST in includes/settings-screen.php, which also
 * holds the rules the server keeps if a write does not come from here.
 */
import { __, sprintf } from '@wordpress/i18n';
import { createRoot, useMemo, useState } from '@wordpress/element';
import {
	Button,
	Card,
	CardBody,
	CardHeader,
	Notice,
	Spinner,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import { MediaUpload } from '@wordpress/media-utils';
import { store as coreStore, useEntityRecord } from '@wordpress/core-data';
import { useSelect } from '@wordpress/data';

import './settings.scss';

const URL_PATTERN = /^https?:\/\/\S+$/i;
const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

/**
 * Whether a Y-m-d string is a real date.
 *
 * @param {string} value The text typed.
 * @return {boolean} True for a real date.
 */
function isRealDate( value ) {
	const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec( value );
	if ( ! m ) {
		return false;
	}
	const date = new Date( Date.UTC( +m[ 1 ], +m[ 2 ] - 1, +m[ 3 ] ) );
	return (
		date.getUTCFullYear() === +m[ 1 ] &&
		date.getUTCMonth() === +m[ 2 ] - 1 &&
		date.getUTCDate() === +m[ 3 ]
	);
}

/**
 * The sections, in the order they show. A field is { key, label, type, help }; a
 * type of `date`, `email`, `url` or `textarea` also sets how it is checked.
 *
 * @param {Object} config What PHP put on the mount element.
 */
function sections( config ) {
	return [
		{
			option: 'aiad_campaign',
			title: __( 'Campaign and contact', 'aiad-core' ),
			intro: __(
				'For reliable delivery of form submissions, install and configure an SMTP plugin such as WP Mail SMTP (Plugins, Add New), using your hosting provider\u2019s SMTP details or a service such as Gmail or SendGrid.',
				'aiad-core'
			),
			fields: [
				{
					key: 'event_date',
					label: __( 'Event date', 'aiad-core' ),
					type: 'date',
					help: sprintf(
						/* translators: %s: default date, YYYY-MM-DD */
						__(
							'AI Awareness Day itself. Drives the homepage countdown, the timeline and the National Conversation page. Leave empty for the default (%s).',
							'aiad-core'
						),
						config.defaultEventDate
					),
				},
				{
					key: 'contact_email',
					label: __( 'Contact form recipient', 'aiad-core' ),
					type: 'email',
					help: sprintf(
						/* translators: %s: the site admin email address */
						__(
							'Where Get Involved form submissions are sent. Leave empty to use the site admin email (%s).',
							'aiad-core'
						),
						config.adminEmail
					),
				},
			],
		},
		{
			option: 'aiad_site',
			title: __( 'Footer links', 'aiad-core' ),
			intro: __(
				'Text links in the site footer. A link left empty shows as pending. The Press Release and Asset Pack links go to their own pages once those are published.',
				'aiad-core'
			),
			fields: [
				{
					key: 'newsletter_url',
					label: __( 'Newsletter', 'aiad-core' ),
					type: 'url',
					help: __(
						'Usually your Beehiiv or newsletter signup page. Appears as "Newsletter" in the footer.',
						'aiad-core'
					),
				},
				{
					key: 'asset_pack_url',
					label: __( 'Assets Pack page address', 'aiad-core' ),
					type: 'url',
					help: __(
						'Optional override. Leave empty to use your published Assets Pack page.',
						'aiad-core'
					),
				},
				{
					key: 'implementation_guide_url',
					label: __( 'Implementation Guide', 'aiad-core' ),
					type: 'url',
					help: __(
						'A PDF or page address shown as "Implementation Guide" in the footer.',
						'aiad-core'
					),
				},
			],
		},
		{
			option: 'aiad_site',
			title: __( 'Header', 'aiad-core' ),
			fields: [
				{
					key: 'show_breadcrumbs',
					label: __( 'Show breadcrumbs', 'aiad-core' ),
					type: 'toggle',
					help: __(
						'Show the breadcrumb trail below the header on inner pages.',
						'aiad-core'
					),
				},
				{
					key: 'header_logo',
					label: __( 'Header logo (fallback)', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: __(
						'Used only when the Site Logo is empty. Prefer setting the logo in the Site Editor, under the header.',
						'aiad-core'
					),
				},
			],
		},
		{
			option: 'aiad_site',
			title: __( 'Downloads', 'aiad-core' ),
			intro: __(
				'Files schools download. The Press Release and Assets Pack pages show them with download buttons.',
				'aiad-core'
			),
			fields: [
				{
					key: 'press_release_file',
					label: __( 'Press release file', 'aiad-core' ),
					type: 'file',
					media: '',
					help: __(
						'Typically a PDF. Shown on the Press Release page with a download button.',
						'aiad-core'
					),
				},
				{
					key: 'asset_logo',
					label: __( 'Logo (download)', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: __(
						'The AI Awareness Day logo for schools to use in documents and presentations.',
						'aiad-core'
					),
				},
				{
					key: 'asset_banner_participating',
					label: __(
						'"I\u2019m Participating" email banner',
						'aiad-core'
					),
					type: 'file',
					media: 'image',
					help: __(
						'Banner teachers add to their email signature before the event.',
						'aiad-core'
					),
				},
				{
					key: 'asset_banner_participated',
					label: __(
						'"I\u2019ve Participated" email banner',
						'aiad-core'
					),
					type: 'file',
					media: 'image',
					help: __(
						'Banner teachers add to their email signature after the event.',
						'aiad-core'
					),
				},
			],
		},
		{
			option: 'aiad_site',
			title: __( 'Homepage images', 'aiad-core' ),
			intro: __(
				'Optional images on the homepage. A badge left empty shows the standard artwork or a placeholder.',
				'aiad-core'
			),
			fields: [
				...[
					[ 'badge_safe', __( 'Safe', 'aiad-core' ) ],
					[ 'badge_smart', __( 'Smart', 'aiad-core' ) ],
					[ 'badge_creative', __( 'Creative', 'aiad-core' ) ],
					[ 'badge_responsible', __( 'Responsible', 'aiad-core' ) ],
					[ 'badge_future', __( 'Future', 'aiad-core' ) ],
				].map( ( [ key, label ] ) => ( {
					key,
					label: sprintf(
						/* translators: %s: strand name, e.g. Safe */
						__( 'Badge: %s', 'aiad-core' ),
						label
					),
					type: 'file',
					media: 'image',
					help:
						key === 'badge_safe'
							? __(
									'The five badges are used for the Five Core Principles and the By theme links.',
									'aiad-core'
								)
							: '',
				} ) ),
				{
					key: 'ai_literacy_logo',
					label: __( 'Our AI Literacy logo', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: __(
						'Badge for the "Our AI literacy" card. Leave empty to use the brand logo.',
						'aiad-core'
					),
				},
				...[
					[
						'session_badge_5-min-lesson-starters',
						__( '5 min', 'aiad-core' ),
					],
					[
						'session_badge_15-20-min-tutor-time',
						__( '15 min', 'aiad-core' ),
					],
					[
						'session_badge_20-min-assemblies',
						__( '20 min', 'aiad-core' ),
					],
					[
						'session_badge_30-45-min-after-school',
						__( '30 min', 'aiad-core' ),
					],
				].map( ( [ key, label ] ) => ( {
					key,
					label: sprintf(
						/* translators: %s: session length, e.g. 5 min */
						__( 'By session length: %s', 'aiad-core' ),
						label
					),
					type: 'file',
					media: 'image',
					help:
						key === 'session_badge_5-min-lesson-starters'
							? __(
									'Images for the "By session length" cards, shown in the badge holder on mobile.',
									'aiad-core'
								)
							: '',
				} ) ),
				{
					key: 'display_board_image_2',
					label: __( 'Display board: photo 1', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: __(
						'Photos for the display board section\'s "More examples" tab. Upload real school boards to inspire teachers.',
						'aiad-core'
					),
				},
				{
					key: 'display_board_image_3',
					label: __( 'Display board: photo 2', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: '',
				},
				{
					key: 'hero_logo',
					label: __( 'Previous hero: logo', 'aiad-core' ),
					type: 'file',
					media: 'image',
					help: __(
						'Large image above the date in the previous hero design. Leave empty to use the Site Logo.',
						'aiad-core'
					),
				},
			],
		},
		{
			option: 'aiad_seo',
			title: __( 'Site and homepage sharing', 'aiad-core' ),
			intro: __(
				'Used in page titles, share previews and the Organization schema. The homepage keeps its own wording on the page.',
				'aiad-core'
			),
			fields: [
				{
					key: 'site_name',
					label: __( 'Site name', 'aiad-core' ),
					type: 'text',
					help: __(
						'Used in page titles, share previews and the Organization schema. Leave empty to use the WordPress site title.',
						'aiad-core'
					),
				},
				{
					key: 'event_date',
					label: __( 'Event date, as shared', 'aiad-core' ),
					type: 'text',
					help: __(
						'Shown in the homepage share title and share message, e.g. "Thursday 29th April 2027".',
						'aiad-core'
					),
				},
				{
					key: 'home_description',
					label: __( 'Homepage description', 'aiad-core' ),
					type: 'textarea',
					help: __(
						'The homepage share preview description. Trimmed to about 160 characters. Leave empty to use the WordPress tagline.',
						'aiad-core'
					),
				},
			],
		},
		{
			option: 'aiad_seo',
			title: __( 'Social profiles', 'aiad-core' ),
			intro: __(
				'The footer shows the LinkedIn and Instagram addresses, and all of them go into the Organization schema (sameAs), so search engines can link the site to its profiles. Leave one empty to hide it.',
				'aiad-core'
			),
			fields: [
				[ 'social_linkedin', __( 'LinkedIn URL', 'aiad-core' ) ],
				[ 'social_instagram', __( 'Instagram URL', 'aiad-core' ) ],
				[ 'social_twitter', __( 'X / Twitter URL', 'aiad-core' ) ],
				[ 'social_facebook', __( 'Facebook URL', 'aiad-core' ) ],
				[ 'social_youtube', __( 'YouTube URL', 'aiad-core' ) ],
				[ 'social_tiktok', __( 'TikTok URL', 'aiad-core' ) ],
				[ 'social_github', __( 'GitHub URL', 'aiad-core' ) ],
			].map( ( [ key, label ] ) => ( { key, label, type: 'url' } ) ),
		},
		{
			option: 'aiad_seo',
			title: __( 'Search engine verification', 'aiad-core' ),
			intro: __(
				'Each code adds its verification <meta> tag to every page. Paste only the content value, the part between the quotes.',
				'aiad-core'
			),
			fields: [
				[ 'verify_google', __( 'Google Search Console', 'aiad-core' ) ],
				[ 'verify_bing', __( 'Bing Webmaster Tools', 'aiad-core' ) ],
				[ 'verify_pinterest', __( 'Pinterest', 'aiad-core' ) ],
			].map( ( [ key, label ] ) => ( { key, label, type: 'text' } ) ),
		},
	];
}

/**
 * The problem with a value, or '' when it is fine. Empty is always fine.
 *
 * @param {Object} field The field being checked.
 * @param {string} value The text typed.
 * @return {string} The message, or ''.
 */
function problem( field, value ) {
	const text = String( value || '' ).trim();
	if ( ! text ) {
		return '';
	}
	if ( field.type === 'date' && ! isRealDate( text ) ) {
		return __( 'Enter a real date.', 'aiad-core' );
	}
	if ( field.type === 'email' && ! EMAIL_PATTERN.test( text ) ) {
		return __( 'Enter a valid email address.', 'aiad-core' );
	}
	if ( field.type === 'url' && ! URL_PATTERN.test( text ) ) {
		return __(
			'Enter the full address, starting with https://',
			'aiad-core'
		);
	}
	return '';
}

/**
 * A file from the media library: its preview or name, with choose, replace and remove.
 *
 * @param {Object}               props          Props.
 * @param {Object}               props.field    The field.
 * @param {number}               props.value    The attachment ID, or 0 for none.
 * @param {(id: number) => void} props.onChange Called with the new ID.
 */
function FileField( { field, value, onChange } ) {
	const id = Number( value ) || 0;
	const item = useSelect(
		( select ) =>
			id
				? select( coreStore ).getEntityRecord(
						'postType',
						'attachment',
						id
					)
				: null,
		[ id ]
	);
	const thumb =
		item?.media_details?.sizes?.thumbnail?.source_url ||
		( item?.mime_type?.startsWith( 'image/' ) ? item.source_url : '' );
	const name = item?.source_url ? item.source_url.split( '/' ).pop() : '';

	return (
		<div className="aiad-settings__file">
			<span className="aiad-settings__file-label">{ field.label }</span>
			<div className="aiad-settings__file-body">
				{ id ? (
					<div className="aiad-settings__file-current">
						{ thumb && <img src={ thumb } alt="" /> }
						<span>
							{ item
								? name
								: sprintf(
										/* translators: %d: attachment ID */
										__( 'File %d', 'aiad-core' ),
										id
									) }
						</span>
					</div>
				) : (
					<span className="aiad-settings__file-none">
						{ __( 'No file chosen', 'aiad-core' ) }
					</span>
				) }
				<MediaUpload
					allowedTypes={ field.media ? [ field.media ] : undefined }
					value={ id || undefined }
					onSelect={ ( media ) => onChange( media?.id || 0 ) }
					render={ ( { open } ) => (
						<Button variant="secondary" onClick={ open }>
							{ id
								? __( 'Replace', 'aiad-core' )
								: __( 'Choose file', 'aiad-core' ) }
						</Button>
					) }
				/>
				{ id > 0 && (
					<Button
						variant="tertiary"
						isDestructive
						onClick={ () => onChange( 0 ) }
					>
						{ __( 'Remove', 'aiad-core' ) }
					</Button>
				) }
			</div>
			{ field.help && (
				<p className="aiad-settings__file-help">{ field.help }</p>
			) }
		</div>
	);
}

function Field( { field, value, onChange, error } ) {
	if ( field.type === 'file' ) {
		return (
			<FileField field={ field } value={ value } onChange={ onChange } />
		);
	}
	if ( field.type === 'toggle' ) {
		return (
			<ToggleControl
				__nextHasNoMarginBottom
				label={ field.label }
				help={ field.help }
				checked={ !! value }
				onChange={ onChange }
			/>
		);
	}
	const common = {
		__nextHasNoMarginBottom: true,
		label: field.label,
		value: value || '',
		onChange,
		help: error ? (
			<span className="aiad-settings__error">{ error }</span>
		) : (
			field.help
		),
	};
	if ( field.type === 'textarea' ) {
		return <TextareaControl { ...common } rows={ 3 } />;
	}
	return (
		<TextControl
			{ ...common }
			__next40pxDefaultSize
			type={ field.type === 'text' ? 'text' : field.type }
		/>
	);
}

function SettingsScreen( { config } ) {
	const { editedRecord, edit, save, hasEdits, isResolving, hasResolved } =
		useEntityRecord( 'root', 'site' );
	const registry = useSelect( ( select ) => select( coreStore ), [] );
	const [ saving, setSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const groups = useMemo( () => sections( config ), [ config ] );

	if ( ! hasResolved || isResolving ) {
		return <Spinner />;
	}

	const valueOf = ( option, key ) => editedRecord?.[ option ]?.[ key ] ?? '';
	const change = ( option, key ) => ( value ) =>
		edit( {
			[ option ]: {
				...( editedRecord?.[ option ] || {} ),
				[ key ]: value,
			},
		} );

	const invalid = groups.some( ( group ) =>
		group.fields.some( ( field ) =>
			problem( field, valueOf( group.option, field.key ) )
		)
	);

	const onSave = async () => {
		setSaving( true );
		setNotice( null );
		const sent = {
			aiad_campaign: { ...( editedRecord?.aiad_campaign || {} ) },
			aiad_site: { ...( editedRecord?.aiad_site || {} ) },
			aiad_seo: { ...( editedRecord?.aiad_seo || {} ) },
		};
		try {
			await save();
			// The server runs its own checks. Report anything it did not keep, so
			// the screen never says "saved" about a value it refused.
			const kept = registry.getEntityRecord( 'root', 'site' ) || {};
			const refused = [];
			groups.forEach( ( group ) =>
				group.fields.forEach( ( field ) => {
					const was = String(
						sent[ group.option ]?.[ field.key ] || ''
					).trim();
					const now = String(
						kept[ group.option ]?.[ field.key ] || ''
					);
					if (
						field.type !== 'toggle' &&
						was !== '' &&
						was !== '0' &&
						was !== now
					) {
						refused.push( field.label );
					}
				} )
			);
			if ( refused.length ) {
				setNotice( {
					status: 'warning',
					text: sprintf(
						/* translators: %s: field names */
						__(
							'Saved, but the server did not accept: %s. The previous value was kept.',
							'aiad-core'
						),
						refused.join( ', ' )
					),
				} );
			} else {
				setNotice( {
					status: 'success',
					text: __( 'Settings saved.', 'aiad-core' ),
				} );
			}
		} catch ( error ) {
			setNotice( {
				status: 'error',
				text:
					error?.message ||
					__( 'The settings could not be saved.', 'aiad-core' ),
			} );
		}
		setSaving( false );
	};

	return (
		<div className="aiad-settings">
			<h1>{ __( 'AI Awareness Day', 'aiad-core' ) }</h1>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
				>
					{ notice.text }
				</Notice>
			) }

			{ groups.map( ( group ) => (
				<Card key={ group.title } className="aiad-settings__card">
					<CardHeader>
						<h2>{ group.title }</h2>
					</CardHeader>
					<CardBody>
						{ group.intro && (
							<p className="aiad-settings__intro">
								{ group.intro }
							</p>
						) }
						<div className="aiad-settings__fields">
							{ group.fields.map( ( field ) => (
								<Field
									key={ field.key }
									field={ field }
									value={ valueOf( group.option, field.key ) }
									onChange={ change(
										group.option,
										field.key
									) }
									error={ problem(
										field,
										valueOf( group.option, field.key )
									) }
								/>
							) ) }
						</div>
					</CardBody>
				</Card>
			) ) }

			<div className="aiad-settings__bar">
				<Button
					variant="primary"
					onClick={ onSave }
					isBusy={ saving }
					disabled={ saving || ! hasEdits || invalid }
					accessibleWhenDisabled
				>
					{ __( 'Save settings', 'aiad-core' ) }
				</Button>
			</div>
		</div>
	);
}

const root = document.getElementById( 'aiad-settings-root' );
if ( root ) {
	let config = {};
	try {
		config = JSON.parse( root.dataset.config || '{}' );
	} catch {
		config = {};
	}
	createRoot( root ).render( <SettingsScreen config={ config } /> );
}
