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
} from '@wordpress/components';
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
				'Used in the Organization schema (sameAs), so search engines can link the site to its profiles. The footer links still come from the Customizer.',
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

function Field( { field, value, onChange, error } ) {
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

	const valueOf = ( option, key ) => editedRecord?.[ option ]?.[ key ] || '';
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
					if ( was !== '' && was !== now ) {
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
