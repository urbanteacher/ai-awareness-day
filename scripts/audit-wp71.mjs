#!/usr/bin/env node
/* Counts the pre-7.1 habits in this codebase, and fails when one grows.
 *
 *   node scripts/audit-wp71.mjs            # compare with docs/wp71-baseline.json
 *   node scripts/audit-wp71.mjs --update   # accept the current counts as the baseline
 *   node scripts/audit-wp71.mjs --json     # the counts, for other tools
 *
 * Why this exists. The theme is a block theme on WordPress 7.1, and says so in
 * theme.json, but a lot of the editing and scripting underneath is still the
 * 6.x way: meta boxes, the Customizer, shortcodes, admin-ajax, jQuery. Nothing
 * kept that honest, so new work could quietly add more of it. This is a ratchet,
 * the same idea as SlideForge's audit:render-surface: a habit may go down, never
 * up, without someone running --update and saying why in docs/WP71-STANDARDISATION.md.
 *
 * It reads source files only (no WordPress, no Docker), so it runs anywhere.
 * Counts are by pattern, not by page: they say where the old habits are, not
 * how a page looks. The definitions are the `METRICS` list below.
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = path.resolve( path.dirname( fileURLToPath( import.meta.url ) ), '..' );
const BASELINE = path.join( ROOT, 'docs', 'wp71-baseline.json' );

// Not ours, generated, or kept for reference.
const SKIP = new Set( [ 'node_modules', 'vendor', '.git', 'archive', 'build', 'bundles', 'tests', 'DEMO' ] );

function walk( dir, out = [] ) {
	for ( const entry of fs.readdirSync( dir, { withFileTypes: true } ) ) {
		// Hidden folders too: .claude/worktrees holds whole copies of the repo, which counted every habit twice.
		if ( SKIP.has( entry.name ) || entry.name.startsWith( '.' ) ) {
			continue;
		}
		const full = path.join( dir, entry.name );
		if ( entry.isDirectory() ) {
			walk( full, out );
		} else {
			out.push( full );
		}
	}
	return out;
}

const files = walk( ROOT ).map( ( f ) => ( { abs: f, rel: path.relative( ROOT, f ) } ) );
const read = ( f ) => fs.readFileSync( f.abs, 'utf8' );
const php = files.filter( ( f ) => f.rel.endsWith( '.php' ) );
const count = ( text, re ) => ( text.match( re ) || [] ).length;
const total = ( list, re ) => list.reduce( ( n, f ) => n + count( read( f ), re ), 0 );

/**
 * The source with its comments blanked out, so an apostrophe in a comment ("the entry's
 * fields") is not taken for the start of a string. Strings are kept, and skipped over
 * correctly, so "https://" inside one is not taken for a comment.
 */
function withoutComments( text ) {
	let out = '';
	let quote = null;
	for ( let i = 0; i < text.length; i++ ) {
		const c = text[ i ];
		const next = text[ i + 1 ];
		if ( quote ) {
			out += c;
			if ( c === '\\' ) {
				out += text[ ++i ] ?? '';
			} else if ( c === quote ) {
				quote = null;
			}
		} else if ( c === "'" || c === '"' ) {
			quote = c;
			out += c;
		} else if ( ( c === '/' && next === '/' ) || c === '#' ) {
			while ( i < text.length && text[ i ] !== '\n' ) {
				i++;
			}
			out += '\n';
		} else if ( c === '/' && next === '*' ) {
			const end = text.indexOf( '*/', i + 2 );
			i = end < 0 ? text.length : end + 1;
			out += ' ';
		} else {
			out += c;
		}
	}
	return out;
}

/** The text of each add_meta_box( ... ) call, found by matching its parentheses. */
function metaBoxCalls() {
	const calls = [];
	for ( const f of php ) {
		const text = withoutComments( read( f ) );
		let from = 0;
		for ( ;; ) {
			const at = text.indexOf( 'add_meta_box(', from );
			if ( at < 0 ) {
				break;
			}
			// A function named ..._add_meta_box() is not a call to it.
			if ( /\w/.test( text[ at - 1 ] || '' ) ) {
				from = at + 1;
				continue;
			}
			let depth = 0;
			let i = at + 'add_meta_box'.length;
			let quote = null;
			for ( ; i < text.length; i++ ) {
				const c = text[ i ];
				if ( quote ) {
					if ( c === '\\' ) {
						i++;
					} else if ( c === quote ) {
						quote = null;
					}
				} else if ( c === "'" || c === '"' ) {
					quote = c;
				} else if ( c === '(' ) {
					depth++;
				} else if ( c === ')' && --depth === 0 ) {
					break;
				}
			}
			calls.push( text.slice( at, i + 1 ) );
			from = i;
		}
	}
	return calls;
}

const metaBoxes = metaBoxCalls();

// register_setting() calls whose option is in REST, so a settings screen can use it.
function restSettings() {
	let n = 0;
	for ( const f of php ) {
		const text = withoutComments( read( f ) );
		let from = 0;
		for ( ;; ) {
			const at = text.indexOf( 'register_setting(', from );
			if ( at < 0 ) {
				break;
			}
			const end = text.indexOf( ');', at );
			const call = text.slice( at, end < 0 ? undefined : end );
			if ( /['"]show_in_rest['"]\s*=>\s*(true|array)/.test( call ) ) {
				n++;
			}
			from = at + 1;
		}
	}
	return n;
}

// Post types that are not in REST. WordPress opens these on the classic edit
// screen whatever the editor setting, so a meta box on one is that screen's form
// by design, not a box in the block editor's drawer. These are the admin-only
// records (survey responses, form submissions): data people read and act on, not
// content they write.
function classicOnlyPostTypes() {
	const types = new Set();
	for ( const f of php ) {
		const text = withoutComments( read( f ) );
		const re = /register_post_type\(\s*['"]([a-z_]+)['"]/g;
		let m;
		while ( ( m = re.exec( text ) ) ) {
			const rest = text.slice( m.index, m.index + 2500 );
			if ( /['"]show_in_rest['"]\s*=>\s*false/.test( rest.split( /register_post_type\(/ )[ 1 ] || rest ) ) {
				types.add( m[ 1 ] );
			}
		}
	}
	return types;
}
const classicOnly = classicOnlyPostTypes();
const blockEditorBoxes = metaBoxes.filter(
	( c ) =>
		! c.includes( '__back_compat_meta_box' ) &&
		! [ ...classicOnly ].some( ( type ) => c.includes( `'${ type }'` ) )
);

// Templates the theme draws in PHP through get_header(), which a block template would replace.
const rootPhp = php.filter( ( f ) => ! f.rel.includes( path.sep ) );
const phpTemplates = rootPhp.filter( ( f ) => /get_header\(\)/.test( read( f ) ) );

// Plugin blocks whose render.php just runs a shortcode: a block in name only.
const pluginRenders = files.filter( ( f ) => /^plugins\/aiad-core\/src\/blocks\/[^/]+\/render\.php$/.test( f.rel ) );
const shortcodeBlocks = pluginRenders.filter( ( f ) => /do_shortcode|_shortcode\(/.test( read( f ) ) );

// Where PHP echoes a <script> tag instead of enqueueing a script.
const scriptScope = php.filter( ( f ) => ! f.rel.includes( '/' ) || /^(inc|template-parts|plugins\/aiad-core\/modules)\//.test( f.rel ) );

// Declared WordPress floors below 7.1, in plugin and theme headers.
const floors = files.filter( ( f ) => /(^style\.css|readme\.txt|aiad-core\.php|debate-network\.php|readiness-benchmark\.php)$/.test( f.rel ) );
const staleFloors = floors.reduce( ( n, f ) => {
	const m = read( f ).match( /Requires at least:\s*(\d+)\.(\d+)/ );
	return n + ( m && ( Number( m[ 1 ] ) < 7 || ( Number( m[ 1 ] ) === 7 && Number( m[ 2 ] ) < 1 ) ) ? 1 : 0 );
}, 0 );

/*
 * lowerIsBetter: a habit. higherIsBetter: the 7.1 way. `why` is what the number means,
 * written for the next person who has to decide whether to move it.
 */
const METRICS = [
	// Habits.
	{ key: 'metaBoxesInBlockEditor', kind: 'habit', value: blockEditorBoxes.length,
		why: 'Classic meta boxes that show in the block editor, in its collapsed drawer. A record editor (a block on the canvas and a Details panel) replaces one; the box is then marked __back_compat_meta_box so only the classic editor shows it. Boxes on post types that are not in REST (admin-only records) are not counted: those open on the classic screen by design.' },
	{ key: 'customizerSettings', kind: 'habit', value: total( php, /->add_setting\(/g ),
		why: 'Customizer settings: content that belongs on a page or in a settings screen, not in a preview panel.' },
	{ key: 'themeModReads', kind: 'habit', value: total( php.filter( ( f ) => f.rel !== 'inc/customizer.php' ), /get_theme_mod\(/g ),
		why: 'Places that read a Customizer value.' },
	{ key: 'shortcodes', kind: 'habit', value: total( php, /add_shortcode\(/g ),
		why: 'Shortcodes. The blocks wrap them for now; they go when the block holds the code.' },
	{ key: 'shortcodeBlocks', kind: 'habit', value: shortcodeBlocks.length,
		why: 'Plugin blocks whose render.php runs a shortcode: a block in name, a shortcode in practice.' },
	{ key: 'ajaxHandlers', kind: 'habit', value: total( php, /add_action\(\s*['"]wp_ajax/g ),
		why: 'admin-ajax handlers. New server calls should be REST routes.' },
	{ key: 'localizeScript', kind: 'habit', value: total( php, /wp_localize_script\(/g ),
		why: 'Data handed to scripts through a global. Script modules read it from the page instead.' },
	{ key: 'jquerySignedScripts', kind: 'habit', value: total( php, /array\(\s*['"]jquery['"]/g ),
		why: 'Scripts enqueued with jQuery as a dependency.' },
	{ key: 'echoedScriptTags', kind: 'habit', value: scriptScope.reduce( ( n, f ) => n + count( read( f ), /echo\s+'<script|\?>\s*<script/g ), 0 ),
		why: 'A <script> tag printed from PHP rather than an enqueued script.' },
	{ key: 'phpTemplates', kind: 'habit', value: phpTemplates.length,
		why: 'Templates drawn in PHP through get_header(). The data-heavy ones may stay, but each stays on purpose (see the plan).' },
	{ key: 'unbuiltThemeBlocks', kind: 'habit', value: files.filter( ( f ) => /^blocks\/[^/]+\/block\.json$/.test( f.rel ) ).length,
		why: 'Blocks hand-written in the theme with no build step. New blocks go in aiad-core/src, built with wp-scripts.' },
	{ key: 'settingsApiForms', kind: 'habit', value: total( php, /add_settings_section\(/g ),
		why: 'Settings screens drawn by PHP forms (Settings API). A settings screen is React on core-data\'s site entity, over options registered for REST (includes/settings-screen.php).' },
	{ key: 'staleVersionFloors', kind: 'habit', value: staleFloors,
		why: 'Headers that still declare a WordPress minimum below 7.1, the only version this is tested on.' },

	// The 7.1 way.
	{ key: 'blockTemplates', kind: 'progress', value: files.filter( ( f ) => /^templates\/[^/]+\.html$/.test( f.rel ) ).length,
		why: 'Block templates, editable in the Site Editor.' },
	{ key: 'recordEditors', kind: 'progress', value: total( files.filter( ( f ) => /^plugins\/aiad-core\/src\/(blocks|editors)\/.*\.js$/.test( f.rel ) ), /registerRecordDetails\(\s*\{/g ),
		why: 'Content types edited with the shared record editor kit (src/shared/record-editor): one Details panel each, in the block editor, in place of a meta box.' },
	{ key: 'settingsScreenOptions', kind: 'progress', value: restSettings(),
		why: 'Options registered for REST with a schema, which the settings screen reads and saves through core-data.' },
	{ key: 'restRoutes', kind: 'progress', value: total( php, /register_rest_route\(/g ),
		why: 'REST routes.' },
	{ key: 'blockBindingSources', kind: 'progress', value: total( php, /register_block_bindings_source\(/g ),
		why: 'Block bindings sources.' },
	{ key: 'scriptModules', kind: 'progress', value: total( php, /wp_(register|enqueue)_script_module\(/g ),
		why: 'Script modules.' },
];

const current = Object.fromEntries( METRICS.map( ( m ) => [ m.key, m.value ] ) );

if ( process.argv.includes( '--json' ) ) {
	console.log( JSON.stringify( current, null, 2 ) );
	process.exit( 0 );
}

if ( process.argv.includes( '--update' ) ) {
	fs.mkdirSync( path.dirname( BASELINE ), { recursive: true } );
	fs.writeFileSync( BASELINE, JSON.stringify( current, null, 2 ) + '\n' );
	console.log( `Baseline written: ${ path.relative( ROOT, BASELINE ) }` );
	process.exit( 0 );
}

if ( ! fs.existsSync( BASELINE ) ) {
	console.error( 'No baseline yet. Run: node scripts/audit-wp71.mjs --update' );
	process.exit( 1 );
}

const baseline = JSON.parse( fs.readFileSync( BASELINE, 'utf8' ) );
let worse = 0;
const pad = ( s, n ) => String( s ).padEnd( n );

console.log( `${ pad( 'metric', 26 ) }${ pad( 'now', 6 ) }${ pad( 'baseline', 10 ) }` );
for ( const kind of [ 'habit', 'progress' ] ) {
	console.log( kind === 'habit' ? '\nHabits (should only go down)' : '\nThe 7.1 way (should only go up)' );
	for ( const m of METRICS.filter( ( x ) => x.kind === kind ) ) {
		const was = baseline[ m.key ];
		const delta = was === undefined ? 0 : m.value - was;
		const bad = was !== undefined && ( kind === 'habit' ? delta > 0 : delta < 0 );
		const note = was === undefined ? '  (new)' : delta === 0 ? '' : `  ${ delta > 0 ? '+' : '' }${ delta }${ bad ? '   <-- WORSE' : '   better' }`;
		console.log( `  ${ pad( m.key, 24 ) }${ pad( m.value, 6 ) }${ pad( was ?? '-', 10 ) }${ note }` );
		if ( bad ) {
			worse++;
			console.log( `      ${ m.why }` );
		}
	}
}

if ( worse ) {
	console.error( `\n${ worse } got worse. Fix it, or accept it with --update and say why in docs/WP71-STANDARDISATION.md.` );
	process.exit( 1 );
}
console.log( '\nNo habit grew.' );
