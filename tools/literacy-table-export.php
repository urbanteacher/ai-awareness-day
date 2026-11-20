<?php
/**
 * One-off: write the AI Literacy Periodic Table's content, which used to be PHP arrays in inc/literacy-table.php,
 * to the JSON files in data/literacy-table/ (format: data/literacy-table/README.md).
 *
 *   docker exec ai-awareness-day-wordpress-1 php /var/www/html/wp-content/themes/ai-awareness-day/tools/literacy-table-export.php
 *
 * Run it against the old PHP version only: once the table reads the JSON files, they are the source.
 *
 * @package AI_Awareness_Day
 */

require_once '/var/www/html/wp-load.php';

$src = (string) file_get_contents( AIAD_DIR . '/inc/literacy-table.php' );
$out = AIAD_DIR . '/data/literacy-table';
if ( ! is_dir( $out ) ) {
	mkdir( $out, 0775, true );
}

/** The hand-picked connections, in the order they were written (the function only returns them merged). */
preg_match( '/\$picks = array\((.*?)\n\t\);/s', $src, $m );
preg_match_all( "/'([A-Za-z0-9]+)' => '([^']*)'/", $m[1] ?? '', $pm, PREG_SET_ORDER );
$picks = array();
foreach ( $pm as $p ) {
	$picks[ $p[1] ] = explode( ' ', $p[2] );
}

$themes  = aiad_literacy_element_themes();
$years   = aiad_literacy_element_years();
$talk    = aiad_literacy_talk();
$safe    = aiad_literacy_safeguarding_syms();
$domains = aiad_literacy_benchmark_domains();
$bench   = array();
foreach ( $domains as $key => $d ) {
	foreach ( explode( ' ', $d['syms'] ) as $s ) {
		$bench[ $s ] = $key;
	}
}
$notes = array(
	'2d' => 'Sketchpad (1963).',
	'3d' => '"Computer-aided design", Douglas T. Ross (1959).',
);

$elements = array();
foreach ( aiad_literacy_raw_elements() as $e ) {
	$sym  = $e[0];
	$year = $years[ $sym ] ?? '~2023';
	$row  = array(
		'sym'    => $sym,
		'name'   => $e[1],
		'family' => $e[2],
		'row'    => $e[3],
		'col'    => $e[4],
		'theme'  => $themes[ $sym ] ?? 'smart',
		'year'   => (int) ltrim( $year, '^~' ),
		'approx' => str_contains( $year, '~' ),
		'older'  => str_starts_with( $year, '^' ),
		'def'    => $e[5],
	);
	if ( isset( $notes[ $sym ] ) ) {
		$row['year_note'] = $notes[ $sym ];
	}
	if ( isset( $talk[ $sym ] ) ) {
		$row['talk'] = $talk[ $sym ];
	}
	if ( isset( $picks[ $sym ] ) ) {
		$row['links'] = $picks[ $sym ];
	}
	if ( isset( $bench[ $sym ] ) ) {
		$row['benchmark'] = $bench[ $sym ];
	}
	if ( in_array( $sym, $safe, true ) ) {
		$row['safeguarding'] = true;
	}
	$elements[] = $row;
}

$issues = array();
foreach ( aiad_literacy_issues() as $key => $i ) {
	$issues[] = array(
		'key'      => $key,
		'name'     => $i['name'],
		'theme'    => $i['theme'],
		'def'      => $i['def'],
		'talk'     => $i['talk'],
		'elements' => $i['syms'],
	);
}

$compounds = array();
foreach ( aiad_literacy_compounds() as $c ) {
	$compounds[] = array(
		'name'     => $c['name'],
		'elements' => $c['syms'],
		'theme'    => $c['strand'],
		'level'    => $c['level'],
		'volatile' => $c['volatile'],
		'what'     => $c['what'],
		'example'  => $c['example'],
		'loop'     => $c['loop'],
		'debate'   => $c['debate'],
	);
}

/** Resources: the raw rows (post type and slug), not the looked-up titles, so unpublished ones survive. */
preg_match( '/function aiad_literacy_resources\(\).*?\$raw = array\((.*?)\n\t\);/s', $src, $rm );
preg_match_all( "/array\( '([a-z_]+)', '([^']+)', '([a-z]+)', '([^']*)', '([^']*)' \)/", $rm[1] ?? '', $rr, PREG_SET_ORDER );
$resources = array();
foreach ( $rr as $r ) {
	$resources[] = array(
		'post_type' => $r[1],
		'slug'      => $r[2],
		'kind'      => $r[3],
		'elements'  => '' === $r[4] ? array() : explode( ' ', $r[4] ),
		'issues'    => '' === $r[5] ? array() : explode( ' ', $r[5] ),
	);
}

$families = array();
foreach ( aiad_literacy_families() as $key => $f ) {
	$families[] = array( 'key' => $key, 'name' => $f['name'], 'about' => $f['about'] );
}
$lists = array(
	'families'          => $families,
	'themes'            => array_map( static fn( $k, $v ) => array( 'key' => $k, 'name' => $v ), array_keys( aiad_literacy_strands() ), aiad_literacy_strands() ),
	'levels'            => array_map( static fn( $k, $v ) => array( 'key' => $k, 'name' => $v ), array_keys( aiad_literacy_levels() ), aiad_literacy_levels() ),
	'eras'              => array_map( static fn( $k, $v ) => array( 'key' => $k ) + $v, array_keys( aiad_literacy_eras() ), aiad_literacy_eras() ),
	'benchmark_domains' => array_map( static fn( $k, $v ) => array( 'key' => $k, 'name' => $v['name'] ), array_keys( $domains ), $domains ),
	// The order the word of the week works through, one element a week (safeguarding elements are skipped).
	'week_rotation'     => array_keys( $talk ),
);

$write = static function ( string $file, string $kind, string $about, array $body ) use ( $out ): void {
	$json = array_merge( array( 'schema' => 'aiad.literacy-table/1', 'kind' => $kind, 'about' => $about ), $body );
	file_put_contents( $out . '/' . $file, wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n" );
	echo $file, "\n";
};
$write( 'elements.json', 'elements', 'The 142 elements: one record each, with its place on the grid, theme, year, definition, talk question, hand-picked connections and benchmark area.', array( 'elements' => $elements ) );
$write( 'issues.json', 'issues', 'The issues the elements raise, theme by theme, each with the elements it arises from.', array( 'issues' => $issues ) );
$write( 'compounds.json', 'compounds', 'The compounds for the compound challenge: real AI systems made by combining elements.', array( 'compounds' => $compounds ) );
$write( 'resources.json', 'resources', 'The "Learn more" links: site posts by post type and slug. Unpublished ones are left out of the page.', array( 'resources' => $resources ) );
$write( 'lists.json', 'lists', 'The families, themes, challenge levels, eras, benchmark areas and the word-of-the-week order the other files refer to by key.', $lists );
printf( "%d elements, %d issues, %d compounds, %d resources, %d connections\n", count( $elements ), count( $issues ), count( $compounds ), count( $resources ), count( $picks ) );
