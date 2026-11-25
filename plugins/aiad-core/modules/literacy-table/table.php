<?php
/**
 * The AI Literacy Periodic Table: /ai-literacy-periodic-table/, for teachers and students.
 *
 * 142 AI terms in seven families: the 120 terms of the original Periodic Table of AI, from the foundations everything
 * is built on, through generative, predictive, agentic and automation AI, to the governance and infrastructure that
 * hold it together, with 21 newer buzzwords and reverse engineering terms added to the families they belong to. Each element has a plain-English definition. (The first version, with six families built on the campaign's themes, is kept in
 * archive/literacy-periodic-table-v1-aiad-strands/.)
 *
 * It is an ordinary page of blocks (patterns/ai-literacy-periodic-table.php), created the first time the theme loads
 * (aiad_maybe_convert_theme_pages() in inc/editable-pages.php), so its introduction and classroom ideas are edited on
 * the page. The table itself is one block, aiad/literacy-table, drawn here from aiad_literacy_elements(), so its 142
 * elements stay in one place: the tiles, the detail panel and the list of every definition below them all come from
 * that list. Without JavaScript the tiles are links to their definitions; assets/js/literacy-table.js turns them into
 * the interactive table (detail panel, family filter, search, random element, print).
 *
 * Moved from the theme's inc/literacy-table.php with its data (data/literacy-table/), pictures, script and
 * stylesheet, so the table and its content no longer depend on the theme. The page's own presentation (title, body
 * class, the homepage promo card) stays in the theme's inc/literacy-table-page.php.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The table's address.
 */
function aiad_literacy_table_url(): string {
	return home_url( '/ai-literacy-periodic-table/' );
}

/**
 * Whether this request is the table's page.
 */
function aiad_is_literacy_table_page(): bool {
	// The theme decides which editable page is showing; under another theme this is never the table's page.
	return function_exists( 'aiad_current_editable_page' ) && 'ai-literacy-periodic-table' === aiad_current_editable_page();
}

/**
 * The table's content, from the JSON files in data/literacy-table/ (format: data/literacy-table/README.md). Edit those
 * files to change a definition, add an element or issue, or move the word of the week on; the admin page (Tools →
 * Periodic Table) checks them and exports them as JSON or CSV.
 *
 * @param string $kind elements, issues, compounds, resources or lists.
 * @return array<string, mixed> The decoded file, or an empty array if it is missing or broken (logged).
 */
function aiad_literacy_data( string $kind ): array {
	static $cache = array();
	if ( isset( $cache[ $kind ] ) ) {
		return $cache[ $kind ];
	}
	$path = aiad_core_path( 'data/literacy-table/' . sanitize_file_name( $kind ) . '.json' );
	$raw  = is_readable( $path ) ? (string) file_get_contents( $path ) : ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$data = '' === $raw ? null : json_decode( $raw, true );
	$keep = 'aiad_literacy_data_' . $kind;
	if ( is_array( $data ) ) {
		// Keep the last copy that read cleanly, so a typo in a deployed file can't take the page down.
		$hash = md5( $raw );
		$kept = get_option( $keep );
		if ( ! is_array( $kept ) || ( $kept['hash'] ?? '' ) !== $hash ) {
			update_option( $keep, array( 'hash' => $hash, 'data' => $data ), false );
		}
	} else {
		error_log( 'AI Literacy Periodic Table: could not read ' . $path . ( '' === $raw ? ' (missing)' : ' (' . json_last_error_msg() . ')' ) . '; using the last good copy.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		$kept           = get_option( $keep );
		$data           = is_array( $kept ) && is_array( $kept['data'] ?? null ) ? $kept['data'] : array();
		$GLOBALS['aiad_literacy_data_failed'][ $kind ] = true;
	}
	$cache[ $kind ] = $data;
	return $data;
}

/**
 * One list from data/literacy-table/lists.json, as key => the rest of each entry.
 *
 * @param string $list families, themes, levels, eras or benchmark_domains.
 * @return array<string, array<string, mixed>>
 */
function aiad_literacy_list( string $list ): array {
	$out = array();
	foreach ( (array) ( aiad_literacy_data( 'lists' )[ $list ] ?? array() ) as $row ) {
		$key         = (string) ( $row['key'] ?? '' );
		$out[ $key ] = array_diff_key( $row, array( 'key' => true ) );
	}
	return $out;
}

/**
 * The element records from data/literacy-table/elements.json, in the order the file lists them.
 *
 * @return array<int, array<string, mixed>>
 */
function aiad_literacy_element_records(): array {
	return (array) ( aiad_literacy_data( 'elements' )['elements'] ?? array() );
}

/**
 * The seven families, in table order: key => name and what it covers.
 *
 * @return array<string, array{name:string, about:string}>
 */
function aiad_literacy_families(): array {
	return array_map( static fn( array $f ): array => array( 'name' => (string) ( $f['name'] ?? '' ), 'about' => (string) ( $f['about'] ?? '' ) ), aiad_literacy_list( 'families' ) );
}

/**
 * Every element: symbol, name, family, row and column on the 18-column table, and definition. Rows 1 to 8 are the
 * main table (rows 1 to 3 have a gap in the middle for the detail panel, as a periodic table does); rows 10 to 12 are
 * the three rows below it. Elements are numbered in reading order, so the numbers follow from where they sit.
 *
 * @return array<int, array{0:string, 1:string, 2:string, 3:int, 4:int, 5:string}>
 */
function aiad_literacy_raw_elements(): array {
	return array_map( static fn( array $e ): array => array( (string) $e['sym'], (string) $e['name'], (string) $e['family'], (int) $e['row'], (int) $e['col'], (string) $e['def'] ), aiad_literacy_element_records() );
}

/**
 * Each element's AI Awareness Day theme, for the table's "View by: AIAD themes" switch. The tiles keep their places;
 * only their colours (and the filter, and the grouping on phones) change, so the switch shows how the themes cut
 * across the families.
 *
 * @return array<string, string> Symbol => theme key (aiad_literacy_strands()).
 */
function aiad_literacy_element_themes(): array {
	static $by_sym = null;
	if ( null === $by_sym ) {
		$by_sym = array_column( aiad_literacy_element_records(), 'theme', 'sym' );
	}
	return $by_sym;
}

/**
 * The issues the technical terms raise: what people actually meet when they use the technology, like symptoms of it.
 * Critical thinking, consent, over-reliance and plagiarism aren't AI terms, so they aren't tiles; each element's
 * panel lists the issues it raises instead. Each issue has a name, its AI Awareness Day theme, what it means for
 * students, a question to talk about in class, and the elements it arises from. (Written for the first version of the
 * table, archive/literacy-periodic-table-v1-aiad-strands/.)
 *
 * @return array<string, array{name:string, theme:string, def:string, talk:string, syms:array<int, string>}>
 */
function aiad_literacy_issues(): array {
	static $issues = null;
	if ( null !== $issues ) {
		return $issues;
	}
	$issues = array();
	foreach ( (array) ( aiad_literacy_data( 'issues' )['issues'] ?? array() ) as $i ) {
		$issues[ (string) $i['key'] ] = array(
			'name'  => (string) $i['name'],
			'theme' => (string) $i['theme'],
			'def'   => (string) $i['def'],
			'talk'  => (string) ( $i['talk'] ?? '' ),
			'syms'  => array_values( (array) ( $i['elements'] ?? array() ) ),
		);
	}
	return $issues;
}

/**
 * The issues each element raises, as issue keys (aiad_literacy_issues()).
 *
 * @return array<string, array<int, string>> Symbol => issue keys.
 */
function aiad_literacy_element_issues(): array {
	static $by_sym = null;
	if ( null === $by_sym ) {
		$by_sym = array();
		foreach ( aiad_literacy_issues() as $key => $issue ) {
			foreach ( $issue['syms'] as $sym ) {
				$by_sym[ $sym ][] = $key;
			}
		}
	}
	return $by_sym;
}

/**
 * Elements added from readers' suggestions ("Suggest a new element"), with the credit line they asked for. The panel
 * shows it ("Suggested by Year 9 at Oakfield School"), so the people who suggest a word see it arrive. Check that a
 * credit names no one in full before adding it.
 *
 * @return array<string, string> Symbol => credit line.
 */
function aiad_literacy_reader_credits(): array {
	$credits = array_filter( array_column( aiad_literacy_element_records(), 'credit', 'sym' ) );
	return (array) apply_filters( 'aiad_literacy_reader_credits', $credits );
}

/**
 * The elements that can lead to a safeguarding conversation. The panel and the Show to class board add a note to
 * them: follow the school's procedures, and make sure students know who to talk to.
 *
 * @return string[] Symbols.
 */
function aiad_literacy_safeguarding_syms(): array {
	return array_values( array_column( array_filter( aiad_literacy_element_records(), static fn( array $e ): bool => ! empty( $e['safeguarding'] ) ), 'sym' ) );
}

/**
 * The element of the week: one of the elements with a Talk about it question (not a safeguarding one), changing every Monday, so the page
 * (and the homepage promo) has something new each week. The same element all week, for everyone.
 * The aiad_literacy_element_of_week filter can choose a different one (return a symbol).
 *
 * @return array{el:?array, talk:string, monday:int} The element (null if there are none), its question and the Monday its week began.
 */
function aiad_literacy_element_of_week( ?int $monday = null ): array {
	$talk   = aiad_literacy_talk();
	$by_sym = array_column( aiad_literacy_elements(), null, 'sym' );
	$pool   = aiad_literacy_week_pool();
	$monday = $monday ?? strtotime( 'monday this week', current_time( 'timestamp' ) );
	if ( array() === $pool ) {
		$pool = array_keys( $by_sym ) ?: array( '' );
	}
	$sym    = (string) apply_filters( 'aiad_literacy_element_of_week', $pool[ (int) floor( $monday / WEEK_IN_SECONDS ) % count( $pool ) ] );
	if ( ! isset( $by_sym[ $sym ] ) ) {
		$sym = $pool[0];
	}
	return array( 'el' => $by_sym[ $sym ] ?? null, 'talk' => $talk[ $sym ] ?? '', 'monday' => $monday );
}

/**
 * The elements the word of the week works through, in order: every element with a talk question, in the
 * week_rotation order (lists.json), except the safeguarding ones. A teacher should choose to raise those, not find
 * them on the homepage.
 *
 * @return string[] Symbols.
 */
function aiad_literacy_week_pool(): array {
	$by_sym = array_column( aiad_literacy_elements(), null, 'sym' );
	return array_values( array_filter( array_keys( aiad_literacy_talk() ), static fn( string $sym ): bool => isset( $by_sym[ $sym ] ) && ! in_array( $sym, aiad_literacy_safeguarding_syms(), true ) ) );
}

/**
 * A question to talk about in class, for the elements the first version of the table had one for.
 *
 * @return array<string, string> Symbol => question.
 */
function aiad_literacy_talk(): array {
	static $talk = null;
	if ( null !== $talk ) {
		return $talk;
	}
	$questions = array_filter( array_column( aiad_literacy_element_records(), 'talk', 'sym' ) );
	// In the word-of-the-week order (lists.json), then any question not in it yet.
	$talk = array();
	foreach ( (array) ( aiad_literacy_data( 'lists' )['week_rotation'] ?? array() ) as $sym ) {
		if ( isset( $questions[ $sym ] ) ) {
			$talk[ $sym ] = (string) $questions[ $sym ];
		}
	}
	return $talk + array_map( 'strval', $questions );
}

/**
 * The elements' pictures: assets/images/literacy-table/{symbol}.svg (lower case), drawn with the classes styled in
 * the page's stylesheet so they take the element's family colour. The alt text is the element's "picture_alt" in
 * elements.json. An element without a picture keeps its plain tile.
 *
 * @return array<string, array{alt:string}> Keyed by symbol.
 */
function aiad_literacy_pictures(): array {
	static $pictures = null;
	if ( null !== $pictures ) {
		return $pictures;
	}
	$pictures = array();
	$dir      = aiad_core_path( 'assets/images/literacy-table/' );
	foreach ( aiad_literacy_element_records() as $e ) {
		$sym = (string) ( $e['sym'] ?? '' );
		if ( '' !== $sym && is_readable( $dir . strtolower( $sym ) . '.svg' ) ) {
			$pictures[ $sym ] = array( 'alt' => (string) ( $e['picture_alt'] ?? '' ) );
		}
	}
	return $pictures;
}

/**
 * Where the script fetches the pictures from (only when an element is opened), with a version so a new or redrawn
 * picture isn't served from an old cache.
 */
function aiad_literacy_picture_base(): string {
	$files = (array) glob( aiad_core_path( 'assets/images/literacy-table/*.svg' ) );
	$ver   = $files ? max( array_map( 'filemtime', $files ) ) : 0;
	return add_query_arg( 'ver', (string) $ver, aiad_core_url( 'assets/images/literacy-table/{sym}.svg' ) );
}

function aiad_literacy_quiz(): array {
	$syms = array_flip( array_column( aiad_literacy_element_records(), 'sym' ) );
	$out  = array();
	foreach ( (array) ( aiad_literacy_data( 'lists' )['quiz'] ?? array() ) as $q ) {
		$row = array_merge( array( (string) ( $q['question'] ?? '' ), (string) ( $q['answer'] ?? '' ) ), array_map( 'strval', (array) ( $q['others'] ?? array() ) ) );
		if ( 4 === count( $row ) && '' !== $row[0] && ! array_diff_key( array_flip( array_slice( $row, 1 ) ), $syms ) ) {
			$out[] = $row;
		}
	}
	return $out;
}

/**
 * AI Awareness Day's own resources (and some from partners) that go deeper on an element or an issue: the panel,
 * the issues and both lists link to them as "Learn more". Each is found by its post type and slug, so one that isn't
 * on the site (or not yet published) is simply left out. AIAD's own come first; partners' are marked.
 *
 * Each: post type, slug, kind (lesson, article, tool, partner), the elements it suits, the issues it suits.
 *
 * @return array<int, array{title:string, url:string, kind:string, syms:array<int, string>, issues:array<int, string>}>
 */
function aiad_literacy_resources(): array {
	static $resources = null;
	if ( null !== $resources ) {
		return $resources;
	}
	$raw = array_map(
		static fn( array $r ): array => array( (string) $r['post_type'], (string) $r['slug'], (string) $r['kind'], implode( ' ', (array) ( $r['elements'] ?? array() ) ), implode( ' ', (array) ( $r['issues'] ?? array() ) ) ),
		(array) ( aiad_literacy_data( 'resources' )['resources'] ?? array() )
	);
	// One query for every linked post (was one get_page_by_path() call each, about 77 queries).
	global $wpdb;
	$found = array();
	if ( $raw ) {
		$slugs = array_values( array_unique( array_map( static fn( array $r ): string => rawurlencode( urldecode( $r[1] ) ), $raw ) ) );
		$types = array_values( array_unique( array_map( static fn( array $r ): string => $r[0], $raw ) ) );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_name, post_type FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_name IN (" . implode( ',', array_fill( 0, count( $slugs ), '%s' ) ) . ') AND post_type IN (' . implode( ',', array_fill( 0, count( $types ), '%s' ) ) . ')', // phpcs:ignore WordPress.DB.PreparedSQLPlaceholders
				array_merge( $slugs, $types )
			)
		);
		foreach ( (array) $rows as $row ) {
			$found[ $row->post_type . '|' . strtolower( $row->post_name ) ] = (int) $row->ID;
		}
		_prime_post_caches( array_values( $found ), false, false );
	}
	$resources = array();
	foreach ( $raw as $r ) {
		$id   = $found[ $r[0] . '|' . strtolower( rawurlencode( urldecode( $r[1] ) ) ) ] ?? 0;
		$post = $id ? get_post( $id ) : null;
		if ( ! $post instanceof WP_Post ) {
			continue;
		}
		$resources[] = array(
			// Titles without their emoji, which read oddly in a list of links.
			'title'  => trim( (string) preg_replace( '/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', html_entity_decode( wp_strip_all_tags( get_the_title( $post ) ), ENT_QUOTES, 'UTF-8' ) ) ),
			'url'    => (string) get_permalink( $post ),
			'kind'   => $r[2],
			'syms'   => '' === $r[3] ? array() : explode( ' ', $r[3] ),
			'issues' => '' === $r[4] ? array() : explode( ' ', $r[4] ),
		);
	}
	return $resources;
}

/**
 * The "Learn more" links for an element or an issue: AIAD's own first, at most three.
 *
 * @param string $field 'syms' or 'issues'.
 * @param string $key   The element's symbol or the issue's key.
 * @return array<int, int> Indexes into aiad_literacy_resources().
 */
function aiad_literacy_resources_for( string $field, string $key ): array {
	$mine     = array();
	$partners = array();
	foreach ( aiad_literacy_resources() as $i => $r ) {
		if ( in_array( $key, $r[ $field ], true ) ) {
			if ( 'partner' === $r['kind'] ) {
				$partners[] = $i;
			} else {
				$mine[] = $i;
			}
		}
	}
	return array_slice( array_merge( $mine, $partners ), 0, 3 );
}

/**
 * A short list of "Learn more" links, for the lists of definitions and issues.
 *
 * @param array<int, int> $indexes Indexes into aiad_literacy_resources().
 */
function aiad_literacy_resource_links( array $indexes ): string {
	$resources = aiad_literacy_resources();
	$kinds     = array(
		'lesson'  => __( 'AIAD lesson', 'aiad-core' ),
		'article' => __( 'AIAD article', 'aiad-core' ),
		'tool'    => __( 'AIAD tool', 'aiad-core' ),
		'partner' => __( 'from a partner', 'aiad-core' ),
	);
	$links = array();
	foreach ( $indexes as $i ) {
		$r       = $resources[ $i ];
		$links[] = '<a href="' . esc_url( $r['url'] ) . '">' . esc_html( $r['title'] ) . '</a> <span class="alpt-res__kind">(' . esc_html( $kinds[ $r['kind'] ] ?? '' ) . ')</span>';
	}
	return implode( ', ', $links );
}

/**
 * When each element's term came into use, for the table's "View by: Year" switch and the "In use since" line in the
 * panel. A year without "~" is when the term was coined or first published in this sense (for example AI in the 1955
 * Dartmouth proposal, Transformer in 2017, Vibe Coding in February 2025). A year with "~" is approximate: when the term
 * came into common use, for words that grew up gradually or that are older than AI (Privacy, Forecasting), where the
 * year is when the word took on the meaning on the table. Check before quoting a "~" year as a fact. A leading "^" marks
 * a word much older than AI (Algorithm, Bias, Privacy, Temperature): its year is when it took on the meaning on the table,
 * and the panel says the word itself is older.
 *
 * @return array<string, string> Symbol => year, with a leading "^" for an older word, then "~" when approximate.
 */
function aiad_literacy_element_years(): array {
	$years = array();
	foreach ( aiad_literacy_element_records() as $e ) {
		if ( isset( $e['year'] ) ) {
			$years[ (string) $e['sym'] ] = ( empty( $e['older'] ) ? '' : '^' ) . ( empty( $e['approx'] ) ? '' : '~' ) . (int) $e['year'];
		}
	}
	return $years;
}

/**
 * The eras the Year view colours the table by, oldest first.
 *
 * @return array<string, array{name:string, from:int, to:int}> Era key => its label and the years it covers.
 */
function aiad_literacy_eras(): array {
	return array_map( static fn( array $e ): array => array( 'name' => (string) $e['name'], 'from' => (int) $e['from'], 'to' => (int) $e['to'] ), aiad_literacy_list( 'eras' ) );
}

/**
 * The era a year falls in.
 *
 * @param int $year A year.
 */
function aiad_literacy_era( int $year ): string {
	foreach ( aiad_literacy_eras() as $key => $era ) {
		if ( $year >= $era['from'] && $year <= $era['to'] ) {
			return $key;
		}
	}
	return 'e7';
}

/**
 * How the panel describes when a term came into use: "In use since 2017", "In use since about 1960", or, for a word
 * much older than AI, "Used this way since 1996 (the word is older)". "Since" rather than "first used": an earlier use
 * someone finds does not make it wrong.
 *
 * @param array{year:int, approx:bool, older:bool} $el An element.
 */
function aiad_literacy_year_label( array $el ): string {
	if ( $el['older'] ) {
		/* translators: %d: a year */
		return sprintf( $el['approx'] ? __( 'Used this way since about %d (the word is older)', 'aiad-core' ) : __( 'Used this way since %d (the word is older)', 'aiad-core' ), $el['year'] );
	}
	/* translators: %d: a year */
	return sprintf( $el['approx'] ? __( 'In use since about %d', 'aiad-core' ) : __( 'In use since %d', 'aiad-core' ), $el['year'] );
}

/**
 * The elements in reading order (row by row, left to right), numbered from 1.
 *
 * @return array<int, array{n:int, sym:string, id:string, name:string, family:string, row:int, col:int, def:string, theme:string, year:int, approx:bool, older:bool, era:string}>
 */
function aiad_literacy_elements(): array {
	static $elements = null;
	if ( null !== $elements ) {
		return $elements;
	}
	$raw = aiad_literacy_raw_elements();
	usort( $raw, static fn( array $a, array $b ): int => ( $a[3] <=> $b[3] ) ?: ( $a[4] <=> $b[4] ) );
	$elements = array();
	foreach ( $raw as $i => $e ) {
		$elements[] = array(
			'n'      => $i + 1,
			'sym'    => $e[0],
			'id'     => 'alpt-' . strtolower( $e[0] ),
			'name'   => $e[1],
			'family' => $e[2],
			'row'    => $e[3],
			'col'    => $e[4],
			'def'    => $e[5],
			'theme'  => aiad_literacy_element_themes()[ $e[0] ] ?? 'smart',
			'year'   => (int) ltrim( aiad_literacy_element_years()[ $e[0] ] ?? '~2023', '^~' ),
			'approx' => str_contains( aiad_literacy_element_years()[ $e[0] ] ?? '~', '~' ),
			'older'  => str_starts_with( aiad_literacy_element_years()[ $e[0] ] ?? '', '^' ),
		);
		$elements[ $i ]['era'] = aiad_literacy_era( $elements[ $i ]['year'] );
	}
	return $elements;
}

/**
 * A tile's name with soft hyphens in its long words, so a narrow tile breaks them where a reader expects, in every
 * browser (not all have hyphenation dictionaries, and print-to-PDF often has none). Only the tiles use it.
 *
 * @param string $name An element's name.
 */
function aiad_literacy_tile_name( string $name ): string {
	static $breaks = null;
	if ( null === $breaks ) {
		$words  = array(
			'Accept|able', 'Account|ability', 'Access|ibility', 'Adapt|ability', 'Attri|bution', 'Author|ship', 'Auto|mation', 'Brain|storming',
			'Collab|oration', 'Com|panion', 'Confi|dent', 'Conse|quences', 'Copy|right', 'Cyber|security', 'Dis|closure', 'Dis|inform|ation',
			'Environ|mental', 'Explain|ability', 'Gener|ation', 'Gener|ative', 'Halluci|nation', 'Imagin|ation', 'Inform|ation', 'Intelli|gence',
			'Know|ledge', 'Meta|cognition', 'Mis|inform|ation', 'Oppor|tunity', 'Origin|ality', 'Per|mission', 'Plagia|rism', 'Pre|diction',
			'Pro|ductive', 'Reason|ing', 'Recom|mend|ation', 'Regu|lation', 'Same|ness', 'Stereo|type', 'Summar|ising', 'Syco|phancy',
			'Trans|parency', 'Water|mark',
			'Adver|sarial', 'Analy|tics', 'Augment|ed', 'Charac|ter', 'Classi|fication', 'Cluster|ing', 'Distil|lation',
			'Docu|ment', 'Embed|ding', 'Engin|eering', 'Fore|casting', 'Gover|nance', 'Hyper|auto|mation', 'Integ|ration', 'Intelli|gent',
			'Multi|modal', 'Orches|tration', 'Orches|trator', 'Para|meters', 'Predic|tive', 'Prescrip|tive', 'Process|ing', 'Proto|col',
			'Recog|nition', 'Recur|sive', 'Improve|ment', 'Reflec|tion', 'Regres|sion', 'Retriev|al', 'Senti|ment', 'Un|super|vised', 'Super|vised',
			'Trans|former', 'Quanti|sation', 'Guard|rails', 'Guard|rail', 'Auto|nomy', 'Com|puter', 'Align|ment', 'Sover|eignty', 'Over|fitting', 'Foun|dation', 'Rein|force|ment', 'Accel|er|ators', 'Temper|ature', 'Respon|sible', 'Optim|isation', 'Super|intelli|gence', 'Mechan|istic', 'Interpret|ability', 'Activ|ation', 'Extrac|tion', 'Abli|ter|ation', 'Uncen|sored', 'Jail|breaking', 'Direc|tion',
		);
		$breaks = array();
		foreach ( $words as $word ) {
			$breaks[ str_replace( '|', '', $word ) ] = str_replace( '|', "\u{00AD}", $word );
		}
	}
	return strtr( $name, $breaks );
}

/**
 * How the elements connect: each element's closest relations, chosen by hand. Every link works both ways, so an
 * element is also connected to every element that lists it. The detail panel's Connections button shows them.
 *
 * @return array<string, array<int, string>> Symbol => connected symbols.
 */
function aiad_literacy_connections(): array {
	static $links = null;
	if ( null !== $links ) {
		return $links;
	}
	$links = array();
	foreach ( aiad_literacy_element_records() as $e ) {
		foreach ( (array) ( $e['links'] ?? array() ) as $other ) {
			$links[ $e['sym'] ][ $other ] = true;
			$links[ $other ][ $e['sym'] ] = true;
		}
	}
	$links = array_map( 'array_keys', $links );
	return $links;
}

/**
 * The AI Awareness Day strands a compound belongs to.
 *
 * @return array<string, string> Key => name.
 */
function aiad_literacy_strands(): array {
	return array_map( static fn( array $t ): string => (string) $t['name'], aiad_literacy_list( 'themes' ) );
}

/**
 * The compound challenge's levels.
 *
 * @return array<string, string> Key => name.
 */
function aiad_literacy_levels(): array {
	return array_map( static fn( array $l ): string => (string) $l['name'], aiad_literacy_list( 'levels' ) );
}

/**
 * The compounds: real AI systems that students "synthesise" in the compound challenge by combining elements. Each
 * has a name, the symbols it is made of (in any order), its AI Awareness Day strand, whether it is volatile (risky
 * without a human in the loop), what it is, an example from real life, where a human stays in the loop, a question
 * to debate, and its challenge level (primary: everyday words, two elements each; secondary: everyday systems,
 * agents and predictions; further: how AI is built and governed). Its marks pair each element's symbol with the words in
 * "what" that stand for it; the classroom board and the card colour those words like the element's tile. A compound's id is its symbols, lowercase and sorted, so saved progress survives reordering
 * this list.
 *
 * @return array<int, array{id:string, name:string, syms:array<int, string>, strand:string, volatile:bool, what:string, example:string, loop:string, debate:string, level:string, marks:array<string, string>}>
 */
function aiad_literacy_compounds(): array {
	static $compounds = null;
	if ( null !== $compounds ) {
		return $compounds;
	}
	$raw = array_map(
		static fn( array $c ): array => array( (string) $c['name'], array_values( (array) $c['elements'] ), (string) $c['theme'], ! empty( $c['volatile'] ), (string) $c['what'], (string) $c['example'], (string) $c['loop'], (string) $c['debate'], (string) $c['level'], array_map( 'strval', (array) ( $c['marks'] ?? array() ) ) ),
		(array) ( aiad_literacy_data( 'compounds' )['compounds'] ?? array() )
	);
	$compounds = array();
	foreach ( $raw as $c ) {
		$ids = array_map( 'strtolower', $c[1] );
		sort( $ids );
		$compounds[] = array(
			'id'       => implode( '-', $ids ),
			'name'     => $c[0],
			'syms'     => $c[1],
			'strand'   => $c[2],
			'volatile' => $c[3],
			'what'     => $c[4],
			'example'  => $c[5],
			'loop'     => $c[6],
			'debate'   => $c[7],
			'level'    => $c[8],
			'marks'    => $c[9],
		);
	}
	return $compounds;
}

/**
 * The table: the family filter and search, the 18-column grid with the detail panel in its gap, the compound
 * combinations, the compound challenge, and every definition, family by family, below it (what the tiles link to without JavaScript, and what search engines read).
 */
function aiad_render_literacy_table(): string {
	$families = aiad_literacy_families();
	$elements = aiad_literacy_elements();
	if ( array() === $elements ) {
		// Nothing to show (a broken elements.json and no saved copy yet). Tools > Periodic Table says what is wrong.
		return '';
	}
	$total    = count( $elements );
	$order    = array_flip( array_keys( $families ) );
	$first    = $elements[0];
	$counts   = array_count_values( array_column( $elements, 'family' ) );
	$compounds = aiad_literacy_compounds();
	$by_sym   = array_column( $elements, null, 'sym' );
	$strands  = aiad_literacy_strands();
	$bench_url = aiad_literacy_benchmark_url();
	$issues    = aiad_literacy_issues();
	$el_issues = aiad_literacy_element_issues();
	$talk      = aiad_literacy_talk();
	$levels   = aiad_literacy_levels();
	$theme_order  = array_flip( array_keys( $strands ) );
	$theme_counts = array_count_values( array_column( $elements, 'theme' ) );
	$level_counts = array_count_values( array_column( $compounds, 'level' ) );
	$eras         = aiad_literacy_eras();
	$era_order    = array_flip( array_keys( $eras ) );
	$era_counts   = array_count_values( array_column( $elements, 'era' ) );
	$years        = array_values( array_unique( array_column( $elements, 'year' ) ) );
	sort( $years );

	ob_start();
	?>
<div class="alpt" id="alpt-explore" data-alpt>
	<div class="alpt-controls" data-alpt-controls>
		<div class="alpt-view" role="group" aria-label="<?php esc_attr_e( 'Colour the table by', 'aiad-core' ); ?>">
			<span class="alpt-view__label"><?php esc_html_e( 'View by', 'aiad-core' ); ?></span>
			<button type="button" class="alpt-view__option" data-alpt-view="families" aria-pressed="true"><?php esc_html_e( 'Technical terms', 'aiad-core' ); ?></button>
			<button type="button" class="alpt-view__option" data-alpt-view="themes" aria-pressed="false"><?php esc_html_e( 'AIAD terms', 'aiad-core' ); ?></button>
			<button type="button" class="alpt-view__option" data-alpt-view="years" aria-pressed="false"><?php esc_html_e( 'Year', 'aiad-core' ); ?></button>
		</div>
		<div class="alpt-search">
			<label for="alpt-search-input"><?php esc_html_e( 'Search the table', 'aiad-core' ); ?></label>
			<input type="search" id="alpt-search-input" data-alpt-search autocomplete="off" placeholder="<?php esc_attr_e( 'Try "privacy" or "bias"', 'aiad-core' ); ?>">
		</div>
		<div class="alpt-legend" role="group" aria-label="<?php esc_attr_e( 'Show one family', 'aiad-core' ); ?>">
			<?php foreach ( $families as $key => $family ) : ?>
				<button type="button" class="alpt-chip alpt-fam--<?php echo esc_attr( $key ); ?>" data-alpt-family="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $family['name'] ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-family="" aria-pressed="true"><?php esc_html_e( 'All families', 'aiad-core' ); ?></button>
		</div>
		<div class="alpt-legend alpt-legend--themes" role="group" aria-label="<?php esc_attr_e( 'Show one theme', 'aiad-core' ); ?>">
			<?php foreach ( $strands as $key => $label ) : ?>
				<button type="button" class="alpt-chip alpt-th--<?php echo esc_attr( $key ); ?>" data-alpt-theme-filter="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $label ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $theme_counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-theme-filter="" aria-pressed="true"><?php esc_html_e( 'All themes', 'aiad-core' ); ?></button>
		</div>
		<div class="alpt-timeline" data-alpt-timeline>
			<label class="alpt-timeline__label" for="alpt-year-range"><?php esc_html_e( 'Words in use by', 'aiad-core' ); ?> <output class="alpt-timeline__year" data-alpt-year-out for="alpt-year-range"><?php echo esc_html( (string) end( $years ) ); ?></output></label>
			<div class="alpt-timeline__row">
				<button type="button" class="alpt-btn alpt-btn--ink alpt-timeline__play" data-alpt-play><span class="alpt-timeline__play-icon" aria-hidden="true"></span><span data-alpt-play-text><?php esc_html_e( 'Play', 'aiad-core' ); ?></span></button>
				<input type="range" id="alpt-year-range" class="alpt-timeline__range" data-alpt-year-range min="0" max="<?php echo esc_attr( (string) ( count( $years ) - 1 ) ); ?>" step="1" value="<?php echo esc_attr( (string) ( count( $years ) - 1 ) ); ?>" data-years="<?php echo esc_attr( implode( ',', $years ) ); ?>">
			</div>
			<p class="alpt-timeline__note"><?php esc_html_e( 'Drag the slider, or press Play, to watch the words arrive. Dates show when each term was in use in AI, not necessarily the very first time anyone said it; "about" means it came into use gradually. Some words, like Bias and Privacy, are much older: their year is when they took on their AI meaning.', 'aiad-core' ); ?> <a href="#alpt-suggest-title"><?php esc_html_e( 'Know an earlier date? Tell us.', 'aiad-core' ); ?></a></p>
		</div>
		<div class="alpt-legend alpt-legend--eras" role="group" aria-label="<?php esc_attr_e( 'Show one era', 'aiad-core' ); ?>">
			<?php foreach ( $eras as $key => $era ) : ?>
				<button type="button" class="alpt-chip alpt-era--<?php echo esc_attr( $key ); ?>" data-alpt-era-filter="<?php echo esc_attr( $key ); ?>" aria-pressed="false">
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span><?php echo esc_html( $era['name'] ); ?></span>
					<span class="alpt-chip__count"><?php echo esc_html( (string) ( $era_counts[ $key ] ?? 0 ) ); ?></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-chip alpt-chip--all" data-alpt-era-filter="" aria-pressed="true"><?php esc_html_e( 'All years', 'aiad-core' ); ?></button>
		</div>
		<button type="button" class="alpt-btn" data-alpt-clear-search hidden><?php esc_html_e( 'Clear search', 'aiad-core' ); ?></button>
		<a class="alpt-print-cards alpt-print-poster" href="#alpt-print"><?php esc_html_e( 'Print the poster', 'aiad-core' ); ?></a>
		<button type="button" class="alpt-print-cards" data-alpt-print-cards hidden><?php esc_html_e( 'Print these as cards', 'aiad-core' ); ?></button>
		<div class="alpt-status" data-alpt-status aria-live="polite"></div><?php // A div, not a p: theme pages drop empty paragraphs (aiad_editable_page_render_block()). ?>
	</div>

	<div class="alpt-grid">
		<div class="alpt-panel alpt-fam--<?php echo esc_attr( $first['family'] ); ?>" data-alpt-panel>
			<div class="alpt-panel__print" aria-hidden="true">
				<p class="alpt-panel__print-eyebrow"><?php esc_html_e( 'AI Awareness Day', 'aiad-core' ); ?></p>
				<p class="alpt-panel__print-title"><?php esc_html_e( 'The AI Literacy Periodic Table', 'aiad-core' ); ?></p>
				<p class="alpt-panel__print-lead">
					<?php
					/* translators: %d: number of elements */
					echo esc_html( sprintf( __( '%d AI terms in seven families. Read every definition at aiawarenessday.co.uk/ai-literacy-periodic-table', 'aiad-core' ), $total ) );
					?>
				</p>
				<ul class="alpt-panel__print-key">
					<?php foreach ( $families as $key => $family ) : ?>
						<li class="alpt-fam--<?php echo esc_attr( $key ); ?> alpt-key-family"><span class="alpt-chip__swatch"></span><?php echo esc_html( $family['name'] ); ?></li>
					<?php endforeach; ?>
					<?php foreach ( $strands as $key => $label ) : ?>
						<li class="alpt-th--<?php echo esc_attr( $key ); ?> alpt-key-theme"><span class="alpt-chip__swatch"></span><?php echo esc_html( $label ); ?></li>
					<?php endforeach; ?>
					<?php foreach ( $eras as $key => $era ) : ?>
						<li class="alpt-era--<?php echo esc_attr( $key ); ?> alpt-key-year"><span class="alpt-chip__swatch"></span><?php echo esc_html( $era['name'] ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
			<div class="alpt-panel__tile">
				<span class="alpt-panel__pic" data-alpt-pic-out hidden></span>
				<span class="alpt-panel__n" data-alpt-out="n" aria-hidden="true"><?php echo esc_html( (string) $first['n'] ); ?></span>
				<span class="alpt-panel__sym" data-alpt-out="sym" aria-hidden="true"><?php echo esc_html( $first['sym'] ); ?></span>
				<span class="alpt-panel__tile-name" data-alpt-out="name" aria-hidden="true"><?php echo esc_html( $first['name'] ); ?></span>
			</div>
			<div class="alpt-panel__body" data-alpt-live><?php // Not a live region: it would re-read the whole panel. The script announces the element through a short line instead. ?>
				<p class="alpt-panel__labels"><span class="alpt-panel__family" data-alpt-out="family"><?php echo esc_html( $families[ $first['family'] ]['name'] ); ?></span> <span class="alpt-panel__theme alpt-th--<?php echo esc_attr( $first['theme'] ); ?>" data-alpt-theme-label><?php echo esc_html( sprintf( /* translators: %s: theme name, such as Safe */ __( '%s theme', 'aiad-core' ), $strands[ $first['theme'] ] ?? '' ) ); ?></span> <span class="alpt-panel__year" data-alpt-out="year"><?php echo esc_html( aiad_literacy_year_label( $first ) ); ?></span></p>
				<h2 class="alpt-panel__title" data-alpt-out="title"><?php echo esc_html( $first['name'] ); ?></h2>
				<p class="alpt-panel__def" data-alpt-out="def"><?php echo esc_html( $first['def'] ); ?></p>
				<?php // Find Santa (December): "Is Santa here?" for the word showing, filled by literacy-santa.js. ?>
				<div class="alpt-santa-check" data-alpt-santa-slot hidden></div>
				<?php if ( aiad_literacy_flows_ready() ) : ?>
				<button type="button" class="alpt-flow-cta" data-alpt-flow hidden><span class="alpt-flow-cta__play" aria-hidden="true"></span><span class="alpt-flow-cta__text"><b><?php esc_html_e( 'See how it works', 'aiad-core' ); ?></b><small data-alpt-flow-from>&nbsp;</small></span></button>
				<?php endif; ?>
				<details class="alpt-reader-notes">
					<summary><?php esc_html_e( 'Classroom notes & resources', 'aiad-core' ); ?></summary>
				<div class="alpt-panel__issues" data-alpt-issues-out>
					<p class="alpt-panel__issues-label"><?php esc_html_e( 'Issues it raises', 'aiad-core' ); ?> <span class="alpt-panel__issues-hint"><?php esc_html_e( '· choose one to read about it', 'aiad-core' ); ?></span></p>
					<?php // The script fills in the issues and the question (theme pages drop empty elements). ?>
				</div>
				<p class="alpt-panel__res" data-alpt-res hidden><span class="alpt-panel__bench-label"><?php esc_html_e( 'Learn more:', 'aiad-core' ); ?></span> <span data-alpt-res-list>&nbsp;</span></p>
				<p class="alpt-panel__bench"><span class="alpt-panel__bench-label"><?php esc_html_e( 'Benchmark:', 'aiad-core' ); ?></span> <a href="<?php echo esc_url( aiad_literacy_benchmark_url() ); ?>" data-alpt-bench><?php echo esc_html( aiad_literacy_benchmark_domain( $first['sym'] ) ); ?></a></p>
				</details>
				<div class="alpt-panel__links" data-alpt-links-out hidden>
					<p class="alpt-panel__links-label"><?php esc_html_e( 'Connected to', 'aiad-core' ); ?></p>
					<?php // The script fills in the list and the compounds line (theme pages drop empty elements). ?>
				</div>
			</div>

			<div class="alpt-panel__nav" data-alpt-nav>
				<button type="button" class="alpt-btn alpt-step alpt-step--prev" data-alpt-step="-1" title="<?php esc_attr_e( 'Previous', 'aiad-core' ); ?>"><span aria-hidden="true">&larr;</span> <span class="alpt-btn__word"><?php esc_html_e( 'Previous', 'aiad-core' ); ?></span></button>
				<button type="button" class="alpt-btn alpt-btn--ink alpt-links-toggle" data-alpt-links aria-pressed="false"><span class="alpt-links-toggle__icon" aria-hidden="true"></span><?php esc_html_e( 'Connections', 'aiad-core' ); ?></button>
				<button type="button" class="alpt-btn alpt-board-btn" data-alpt-board-element><span class="alpt-board-btn__icon" aria-hidden="true"></span><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></button>
				<button type="button" class="alpt-btn alpt-nav-play" data-alpt-flow-nav hidden title="<?php esc_attr_e( 'See how it works', 'aiad-core' ); ?>"><span class="alpt-nav-play__icon" aria-hidden="true"></span><span class="alpt-btn__word"><?php esc_html_e( 'See how it works', 'aiad-core' ); ?></span></button>
				<button type="button" class="alpt-btn alpt-share-btn" data-alpt-share aria-expanded="false" aria-controls="alpt-share-menu" title="<?php esc_attr_e( 'Share', 'aiad-core' ); ?>"><svg class="alpt-share-btn__icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M12 3v12M7 8l5-5 5 5" fill="none" stroke="currentColor" stroke-width="2.4"/><path d="M5 12v8h14v-8" fill="none" stroke="currentColor" stroke-width="2.4"/></svg><span class="alpt-btn__word"><?php esc_html_e( 'Share', 'aiad-core' ); ?></span></button>
				<div class="alpt-share" id="alpt-share-menu" data-alpt-share-menu hidden>
					<p class="alpt-share__label"><?php esc_html_e( 'Share this element', 'aiad-core' ); ?></p>
					<ul class="alpt-share__list">
						<li><a class="alpt-share__link alpt-share__link--linkedin" data-alpt-share-to="linkedin" href="#" target="_blank" rel="noopener noreferrer">LinkedIn</a></li>
						<li><a class="alpt-share__link alpt-share__link--x" data-alpt-share-to="x" href="#" target="_blank" rel="noopener noreferrer">X</a></li>
						<li><a class="alpt-share__link alpt-share__link--facebook" data-alpt-share-to="facebook" href="#" target="_blank" rel="noopener noreferrer">Facebook</a></li>
						<li><a class="alpt-share__link alpt-share__link--whatsapp" data-alpt-share-to="whatsapp" href="#" target="_blank" rel="noopener noreferrer">WhatsApp</a></li>
						<li><a class="alpt-share__link alpt-share__link--email" data-alpt-share-to="email" href="#"><?php esc_html_e( 'Email', 'aiad-core' ); ?></a></li>
						<li><button type="button" class="alpt-share__link alpt-share__link--copy" data-alpt-copy><?php esc_html_e( 'Copy link', 'aiad-core' ); ?></button></li>
					</ul>
				</div>

			<button type="button" class="alpt-btn alpt-back-to-tiles" data-alpt-dismiss title="<?php esc_attr_e( 'Back to elements', 'aiad-core' ); ?>"><svg class="alpt-back-to-tiles__icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.6"/></svg><span class="alpt-btn__word"><?php esc_html_e( 'Back to elements', 'aiad-core' ); ?></span></button>
				<?php // Phones only (the picture is hidden there to keep the buttons on screen): opens the picture with its definition. ?>
				<button type="button" class="alpt-btn alpt-pic-btn" data-alpt-pic-btn hidden><svg class="alpt-pic-btn__icon" viewBox="0 0 24 24" width="16" height="16" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="16" fill="none" stroke="currentColor" stroke-width="2.2"/><path d="M3 17l6-6 5 5 3-3 4 4" fill="none" stroke="currentColor" stroke-width="2.2"/></svg><?php esc_html_e( 'Picture', 'aiad-core' ); ?></button>
				<span class="alpt-panel__count" data-alpt-count>
					<?php
					/* translators: 1: element number, 2: number of elements */
					printf( esc_html__( 'Element %1$s of %2$s', 'aiad-core' ), '<span data-alpt-out="n2">' . esc_html( (string) $first['n'] ) . '</span>', esc_html( (string) $total ) );
					?>
				</span>
				<button type="button" class="alpt-btn alpt-step alpt-step--next" data-alpt-step="1" title="<?php esc_attr_e( 'Next', 'aiad-core' ); ?>"><span class="alpt-btn__word"><?php esc_html_e( 'Next', 'aiad-core' ); ?></span> <span aria-hidden="true">&rarr;</span></button>
			</div>
		</div>

		<div class="alpt-key" aria-hidden="true">
			<div class="alpt-tile alpt-tile--key">
				<span class="alpt-tile__n">102</span>
				<span class="alpt-tile__sym">Bi</span>
				<span class="alpt-tile__name"><?php esc_html_e( 'Bias', 'aiad-core' ); ?></span>
			</div>
			<div class="alpt-key__text">
				<p class="alpt-key__title"><?php esc_html_e( 'How to read a tile', 'aiad-core' ); ?></p>
				<p><?php esc_html_e( 'Top: its number, then its symbol', 'aiad-core' ); ?></p>
				<p><?php esc_html_e( 'Bottom: the term', 'aiad-core' ); ?></p>
				<p class="alpt-key-family"><?php esc_html_e( 'Colour: the family it belongs to', 'aiad-core' ); ?></p>
				<p class="alpt-key-theme"><?php esc_html_e( 'Colour: its AI Awareness Day theme', 'aiad-core' ); ?></p>
				<p class="alpt-key-year"><?php esc_html_e( 'Colour: when the word came into use', 'aiad-core' ); ?></p>
			</div>
		</div>

		<?php foreach ( $families as $key => $family ) : ?>
			<p class="alpt-family-label alpt-fam--<?php echo esc_attr( $key ); ?>" style="--o:<?php echo esc_attr( (string) ( $order[ $key ] * 1000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( $family['name'] ); ?></span>
				<span class="alpt-family-label__about"><?php echo esc_html( $family['about'] ); ?></span>
			</p>
		<?php endforeach; ?>
		<?php foreach ( $strands as $key => $label ) : ?>
			<p class="alpt-family-label alpt-theme-label alpt-th--<?php echo esc_attr( $key ); ?>" style="--o2:<?php echo esc_attr( (string) ( $theme_order[ $key ] * 1000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( sprintf( /* translators: %s: theme name, such as Safe */ __( '%s theme', 'aiad-core' ), $label ) ); ?></span>
			</p>
		<?php endforeach; ?>

		<?php foreach ( $eras as $key => $era ) : ?>
			<p class="alpt-family-label alpt-era-label alpt-era--<?php echo esc_attr( $key ); ?>" style="--o3:<?php echo esc_attr( (string) ( $era_order[ $key ] * 100000 ) ); ?>">
				<span class="alpt-family-label__name"><?php echo esc_html( $era['name'] ); ?></span>
			</p>
		<?php endforeach; ?>

		<?php foreach ( $elements as $el ) : ?>
			<a class="alpt-tile alpt-fam--<?php echo esc_attr( $el['family'] ); ?>" href="#<?php echo esc_attr( $el['id'] ); ?>" data-alpt-el="<?php echo esc_attr( (string) ( $el['n'] - 1 ) ); ?>" data-alpt-theme="<?php echo esc_attr( $el['theme'] ); ?>" data-alpt-era="<?php echo esc_attr( $el['era'] ); ?>" data-alpt-year="<?php echo esc_attr( (string) $el['year'] ); ?>" style="--o3:<?php echo esc_attr( (string) ( $era_order[ $el['era'] ] * 100000 + $el['year'] * 10 + 1 ) ); ?>;--r:<?php echo esc_attr( (string) $el['row'] ); ?>;--c:<?php echo esc_attr( (string) $el['col'] ); ?>;--o:<?php echo esc_attr( (string) ( $order[ $el['family'] ] * 1000 + $el['n'] ) ); ?>;--o2:<?php echo esc_attr( (string) ( ( $theme_order[ $el['theme'] ] ?? 0 ) * 1000 + $el['n'] ) ); ?>">
				<span class="alpt-tile__n"><?php echo esc_html( (string) $el['n'] ); ?></span>
				<span class="alpt-tile__sym"><?php echo esc_html( $el['sym'] ); ?></span>
				<?php $aiad_len = mb_strlen( $el['name'] ); ?>
				<span class="alpt-tile__name<?php echo $aiad_len >= 23 ? ' alpt-tile__name--xlong' : ( $aiad_len >= 21 ? ' alpt-tile__name--long' : '' ); ?>"><?php echo esc_html( aiad_literacy_tile_name( $el['name'] ) ); ?></span>
				<span class="screen-reader-text">, <?php echo esc_html( $families[ $el['family'] ]['name'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<p class="alpt-footnote"><?php esc_html_e( 'Left: foundations. Centre: generative and predictive AI. Right: autonomous agents and automation. Below: the governance and infrastructure that hold it all together. Numbers run left to right, top to bottom.', 'aiad-core' ); ?></p>

	<div class="alpt-tryit" id="alpt-tryit">
		<p class="alpt-sticker"><?php esc_html_e( 'Pick one · 5 to 50 minutes', 'aiad-core' ); ?></p>
		<h2 class="alpt-tryit__title"><?php esc_html_e( 'Try it', 'aiad-core' ); ?></h2>
		<p class="alpt-tryit__lead"><?php esc_html_e( 'Ways to put the words to work, from a five-minute quiz to a full lesson.', 'aiad-core' ); ?></p>
		<ul class="alpt-tryit__cards">
			<li><a class="alpt-tryit__card" href="#alpt-compounds-title"><span class="alpt-tryit__time"><?php esc_html_e( '10 min', 'aiad-core' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Compound combinations', 'aiad-core' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'See how elements combine into the AI people use every day.', 'aiad-core' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-challenge-title"><span class="alpt-tryit__time"><?php esc_html_e( '15–30 min', 'aiad-core' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'The compound challenge', 'aiad-core' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'Work the compounds out yourself, at three levels.', 'aiad-core' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-buzzword-check"><span class="alpt-tryit__time"><?php esc_html_e( '5 min', 'aiad-core' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Buzzword check', 'aiad-core' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'Ten quick questions, different each time.', 'aiad-core' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
			<li><a class="alpt-tryit__card" href="#alpt-sessions"><span class="alpt-tryit__time"><?php esc_html_e( '10–50 min', 'aiad-core' ); ?></span><span class="alpt-tryit__name"><?php esc_html_e( 'Ready-made sessions', 'aiad-core' ); ?></span> <span class="alpt-tryit__about"><?php esc_html_e( 'A starter, a 50-minute lesson, a staff INSET and a page for parents.', 'aiad-core' ); ?></span><span class="alpt-tryit__go" aria-hidden="true">&darr;</span></a></li>
		</ul>
	</div>

	<section class="alpt-compounds" aria-labelledby="alpt-compounds-title">
		<p class="alpt-sticker">
			<?php
			/* translators: %d: number of compounds */
			echo esc_html( sprintf( __( '%d compounds · Stable or volatile?', 'aiad-core' ), count( $compounds ) ) );
			?>
		</p>
		<h2 id="alpt-compounds-title" class="alpt-compounds__title"><?php esc_html_e( 'Compound combinations', 'aiad-core' ); ?></h2>
		<p class="alpt-compounds__lead"><?php esc_html_e( 'Elements combine into the AI systems people use every day. Each compound shows which elements a real system needs, where a human stays in the loop, and a question for your class to debate. Want to work them out yourself? Take the compound challenge below.', 'aiad-core' ); ?></p>
		<p class="alpt-compounds__key">
			<span class="alpt-badge alpt-badge--stable"><?php esc_html_e( 'Stable', 'aiad-core' ); ?></span> <?php esc_html_e( 'A human stays in the loop.', 'aiad-core' ); ?>
			<span class="alpt-badge alpt-badge--volatile"><?php esc_html_e( 'Volatile', 'aiad-core' ); ?></span> <?php esc_html_e( 'Risky without a human in the loop. Can you find the element that stabilises it?', 'aiad-core' ); ?>
		</p>
		<div class="alpt-levels" role="group" aria-label="<?php esc_attr_e( 'Show one level', 'aiad-core' ); ?>" data-alpt-levels hidden>
			<button type="button" class="alpt-chip" data-alpt-level="" aria-pressed="true"><?php esc_html_e( 'All levels', 'aiad-core' ); ?> <span class="alpt-chip__count"><?php echo esc_html( (string) count( $compounds ) ); ?></span></button>
			<?php foreach ( $levels as $key => $label ) : ?>
				<button type="button" class="alpt-chip alpt-level--<?php echo esc_attr( $key ); ?>" data-alpt-level="<?php echo esc_attr( $key ); ?>" aria-pressed="false"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $label ); ?> <span class="alpt-chip__count"><?php echo esc_html( (string) ( $level_counts[ $key ] ?? 0 ) ); ?></span></button>
			<?php endforeach; ?>
		</div>
		<p class="alpt-compounds__locked-note" data-alpt-locked-note><?php esc_html_e( 'Challenge on: compounds you haven\'t discovered yet are hidden. Each one gives you a clue: how many elements it needs, and one of them.', 'aiad-core' ); ?></p>
		<ul class="alpt-compounds__grid" data-alpt-compounds>
			<?php foreach ( $compounds as $compound ) : ?>
				<?php
				$aiad_first   = $by_sym[ $compound['syms'][0] ] ?? null;
				$aiad_strand  = $strands[ $compound['strand'] ] ?? '';
				$aiad_vol     = $compound['volatile'];
				?>
				<li class="alpt-compound alpt-compound--<?php echo esc_attr( $compound['strand'] ); ?><?php echo $aiad_vol ? ' alpt-compound--volatile' : ''; ?>" id="alpt-cp-<?php echo esc_attr( $compound['id'] ); ?>" data-alpt-compound="<?php echo esc_attr( $compound['id'] ); ?>" data-alpt-compound-level="<?php echo esc_attr( $compound['level'] ); ?>">
					<div class="alpt-compound__locked">
						<span class="alpt-compound__q" aria-hidden="true">?</span>
						<p class="alpt-compound__locked-title"><?php esc_html_e( 'Undiscovered compound', 'aiad-core' ); ?></p>
						<p class="alpt-compound__hint">
							<?php
							/* translators: 1: number of elements in the compound, 2: an element's name, 3: its symbol */
							echo esc_html( sprintf( _n( 'Clue: %1$d element, including %2$s (%3$s).', 'Clue: %1$d elements, including %2$s (%3$s).', count( $compound['syms'] ), 'aiad-core' ), count( $compound['syms'] ), $aiad_first['name'] ?? '', $compound['syms'][0] ) );
							?>
						</p>
						<p class="alpt-compound__looks"><strong><?php esc_html_e( 'Looks like:', 'aiad-core' ); ?></strong> <?php echo esc_html( $compound['example'] ); ?></p>
						<span class="alpt-compound__swatches" aria-hidden="true">
							<?php foreach ( $compound['syms'] as $sym ) : ?>
								<span class="alpt-chip__swatch alpt-fam--<?php echo esc_attr( $by_sym[ $sym ]['family'] ?? '' ); ?>"></span>
							<?php endforeach; ?>
						</span>
						<p class="alpt-compound__strand alpt-strand--<?php echo esc_attr( $compound['strand'] ); ?>">
							<?php
							/* translators: %s: strand name, such as Safe */
							echo esc_html( sprintf( __( '%s strand', 'aiad-core' ), $aiad_strand ) );
							?>
						</p>
						<button type="button" class="alpt-btn alpt-compound__try" data-alpt-target="<?php echo esc_attr( $compound['id'] ); ?>"><?php esc_html_e( 'Find this one', 'aiad-core' ); ?></button>
					</div>
					<div class="alpt-compound__body">
						<div class="alpt-compound__tags">
							<span class="alpt-badge alpt-badge--<?php echo $aiad_vol ? 'volatile' : 'stable'; ?>"><?php echo $aiad_vol ? esc_html__( 'Volatile', 'aiad-core' ) : esc_html__( 'Stable', 'aiad-core' ); ?></span>
							<span class="alpt-compound__found" data-alpt-found-mark hidden><?php esc_html_e( 'Discovered', 'aiad-core' ); ?></span>
							<span class="alpt-compound__level alpt-level--<?php echo esc_attr( $compound['level'] ); ?>"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $levels[ $compound['level'] ] ?? '' ); ?></span>
						</div>
						<div class="alpt-compound__head">
							<h3 class="alpt-compound__name"><?php echo esc_html( $compound['name'] ); ?></h3>
							<p class="alpt-compound__strand alpt-strand--<?php echo esc_attr( $compound['strand'] ); ?>">
								<?php
								/* translators: %s: strand name, such as Safe */
								echo esc_html( sprintf( __( '%s strand', 'aiad-core' ), $aiad_strand ) );
								?>
							</p>
						</div>
						<p class="alpt-compound__made"><?php esc_html_e( 'Made from', 'aiad-core' ); ?></p>
						<ul class="alpt-compound__formula">
							<?php foreach ( $compound['syms'] as $i => $sym ) : ?>
								<?php if ( $i > 0 ) : ?><li class="alpt-compound__plus" aria-hidden="true">+</li><?php endif; ?>
								<li class="alpt-compound__el alpt-fam--<?php echo esc_attr( $by_sym[ $sym ]['family'] ?? '' ); ?>">
									<span class="alpt-compound__el-sym"><?php echo esc_html( $sym ); ?></span>
									<span class="alpt-compound__el-name"><?php echo esc_html( $by_sym[ $sym ]['name'] ?? '' ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
						<button type="button" class="alpt-btn alpt-board-btn alpt-compound__board" data-alpt-board-compound="<?php echo esc_attr( $compound['id'] ); ?>" hidden><span class="alpt-board-btn__icon" aria-hidden="true"></span><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></button>
						<button type="button" class="alpt-btn alpt-compound__copy" data-alpt-copy-compound="<?php echo esc_attr( $compound['id'] ); ?>" hidden><?php esc_html_e( 'Copy link', 'aiad-core' ); ?></button>
						<details class="alpt-compound__more">
							<summary><?php esc_html_e( 'More about it', 'aiad-core' ); ?></summary>
							<p class="alpt-compound__what"><?php echo esc_html( $compound['what'] ); ?></p>
							<dl class="alpt-compound__facts">
								<div>
									<dt><?php esc_html_e( 'In real life', 'aiad-core' ); ?></dt>
									<dd><?php echo esc_html( $compound['example'] ); ?></dd>
								</div>
								<div>
									<dt><?php esc_html_e( 'Human in the loop', 'aiad-core' ); ?></dt>
									<dd><?php echo esc_html( $compound['loop'] ); ?></dd>
								</div>
							</dl>
							<div class="alpt-compound__debate">
								<p class="alpt-compound__debate-label"><?php esc_html_e( 'Debate it', 'aiad-core' ); ?></p>
								<p class="alpt-compound__debate-q"><?php echo esc_html( $compound['debate'] ); ?></p>
							</div>
						</details>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<p class="alpt-compounds__more-wrap" data-alpt-more-wrap hidden><button type="button" class="alpt-btn" data-alpt-more aria-expanded="false"></button></p>
	</section>

	<section class="alpt-challenge" aria-labelledby="alpt-challenge-title" data-alpt-challenge hidden>
		<p class="alpt-sticker">
			<?php
			/* translators: %d: number of compounds */
			echo esc_html( sprintf( __( 'Challenge · %d compounds', 'aiad-core' ), count( $compounds ) ) );
			?>
		</p>
		<h2 id="alpt-challenge-title" class="alpt-challenge__title"><?php esc_html_e( 'The compound challenge', 'aiad-core' ); ?></h2>
		<p class="alpt-challenge__lead"><?php esc_html_e( 'Ready to work them out yourself? Choose a level: the compounds you haven\'t discovered are hidden, with a clue for each. Choose two to four elements on the table and combine them to find the answers. Stuck? Press Find this one on any hidden compound: the table narrows to a few elements, and hints help you the rest of the way. End the challenge to see every compound again.', 'aiad-core' ); ?></p>
		<div class="alpt-challenge__bar">
			<p class="alpt-challenge__progress"><span data-alpt-found-text></span></p>
			<div class="alpt-meter" aria-hidden="true"><span class="alpt-meter__fill" data-alpt-meter></span></div>
		</div>
		<div class="alpt-challenge__levels">
			<?php foreach ( $levels as $key => $label ) : ?>
				<button type="button" class="alpt-challenge__level alpt-level--<?php echo esc_attr( $key ); ?>" data-alpt-start="<?php echo esc_attr( $key ); ?>">
					<span class="alpt-challenge__level-name"><span class="alpt-level-mark" aria-hidden="true"></span><?php echo esc_html( $label ); ?></span>
					<span class="alpt-challenge__level-about">
						<?php
						$aiad_about = array(
							'primary'   => __( 'Words children know, two at a time. The table narrows to a few tiles for you.', 'aiad-core' ),
							'secondary' => __( 'Everyday AI, agents, predictions and fair decisions.', 'aiad-core' ),
							'further'   => __( 'How AI is built, tested, run and governed.', 'aiad-core' ),
						);
						echo esc_html( $aiad_about[ $key ] ?? '' );
						?>
					</span>
					<span class="alpt-challenge__level-count" data-alpt-level-count="<?php echo esc_attr( $key ); ?>"></span>
				</button>
			<?php endforeach; ?>
			<button type="button" class="alpt-challenge__level alpt-challenge__level--all" data-alpt-start="">
				<span class="alpt-challenge__level-name"><?php esc_html_e( 'All levels', 'aiad-core' ); ?></span>
				<span class="alpt-challenge__level-about"><?php esc_html_e( 'Every compound at once.', 'aiad-core' ); ?></span>
				<span class="alpt-challenge__level-count" data-alpt-level-count=""></span>
			</button>
		</div>
		<div class="alpt-challenge__actions">
			<button type="button" class="alpt-btn alpt-btn--ghost" data-alpt-reset hidden><?php esc_html_e( 'Start again', 'aiad-core' ); ?></button>
		</div>
	</section>

	<div class="alpt-lab" id="alpt-lab" data-alpt-lab role="region" aria-label="<?php esc_attr_e( 'The compound challenge', 'aiad-core' ); ?>" hidden>
		<div class="alpt-lab__inner">
			<div class="alpt-lab__head">
				<p class="alpt-lab__title"><?php esc_html_e( 'The compound challenge', 'aiad-core' ); ?> <span class="alpt-lab__count" data-alpt-found-count></span></p>
				<p class="alpt-lab__help"><?php esc_html_e( 'Choose two to four elements on the table, then combine them.', 'aiad-core' ); ?></p>
			</div>
			<div class="alpt-lab__target" data-alpt-target-box hidden>
				<p class="alpt-lab__target-text" data-alpt-target-text>&nbsp;</p>
				<div class="alpt-lab__target-actions">
					<button type="button" class="alpt-btn" data-alpt-hint><?php esc_html_e( 'Give me a hint', 'aiad-core' ); ?></button>
					<button type="button" class="alpt-btn alpt-btn--ghost" data-alpt-unfocus><?php esc_html_e( 'Show the whole table', 'aiad-core' ); ?></button>
				</div>
			</div>
			<ol class="alpt-lab__slots" data-alpt-slots aria-label="<?php esc_attr_e( 'Chosen elements', 'aiad-core' ); ?>"></ol>
			<div class="alpt-lab__actions">
				<button type="button" class="alpt-btn alpt-btn--ink" data-alpt-combine disabled><?php esc_html_e( 'Combine', 'aiad-core' ); ?></button>
				<button type="button" class="alpt-btn" data-alpt-clear disabled><?php esc_html_e( 'Clear', 'aiad-core' ); ?></button>
				<button type="button" class="alpt-btn alpt-lab__hint" data-alpt-free-hint><span class="alpt-lab__hint-icon" aria-hidden="true">?</span><?php esc_html_e( 'Hint', 'aiad-core' ); ?></button>
				<button type="button" class="alpt-btn alpt-btn--ghost alpt-lab__close" data-alpt-lab-toggle aria-controls="alpt-lab"><?php esc_html_e( 'End challenge', 'aiad-core' ); ?></button>
			</div>
			<div class="alpt-lab__msg" data-alpt-lab-msg aria-live="polite"></div>
		</div>
	</div>

	<section class="alpt-quiz" id="alpt-buzzword-check" aria-labelledby="alpt-quiz-title" hidden>
		<div class="alpt-quiz__intro">
			<p class="alpt-sticker alpt-quiz__eyebrow"><?php esc_html_e( 'Quiz · 10 questions', 'aiad-core' ); ?></p>
			<h2 id="alpt-quiz-title"><?php esc_html_e( 'Buzzword check', 'aiad-core' ); ?></h2>
			<p class="alpt-quiz__lead"><?php esc_html_e( 'Which AI term fits the situation? Ten questions, picked at random each time you play.', 'aiad-core' ); ?></p>
			<div class="alpt-quiz__scores">
				<p class="alpt-quiz__score"><span class="alpt-quiz__score-label"><?php esc_html_e( 'Score', 'aiad-core' ); ?></span> <span data-quiz-score>0</span></p>
				<p class="alpt-quiz__best" data-quiz-best hidden>&nbsp;</p>
			</div>
		</div>
		<div class="alpt-quiz__card">
			<div class="alpt-quiz__top">
				<p class="alpt-quiz__progress" data-quiz-progress>&nbsp;</p>
				<div class="alpt-quiz__bar" aria-hidden="true"><span data-quiz-bar></span></div>
			</div>
			<h3 id="alpt-quiz-question" class="alpt-quiz__question" data-quiz-question tabindex="-1">&nbsp;</h3>
			<div class="alpt-quiz__options" data-quiz-options role="group" aria-labelledby="alpt-quiz-question"></div>
			<p class="alpt-quiz__feedback" data-quiz-feedback role="status">&nbsp;</p>
			<button class="alpt-btn alpt-btn--ink alpt-quiz__next" type="button" data-quiz-next hidden>Next question</button>
		</div>
	</section>

	<section class="alpt-glossary" aria-labelledby="alpt-glossary-title">
		<h2 id="alpt-glossary-title" class="alpt-glossary__title"><?php esc_html_e( 'Glossary: the periodic table broken down', 'aiad-core' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'All 142 definitions, family by family, to read, copy into a lesson or print.', 'aiad-core' ); ?></p>
		<?php foreach ( $families as $key => $family ) : ?>
			<details class="alpt-glossary__family alpt-fam--<?php echo esc_attr( $key ); ?>">
				<summary>
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span class="alpt-glossary__family-name"><?php echo esc_html( $family['name'] ); ?></span>
					<span class="alpt-glossary__family-about"><?php echo esc_html( $family['about'] ); ?></span>
				</summary>
				<dl>
					<?php foreach ( $elements as $el ) : ?>
						<?php
						if ( $el['family'] !== $key ) {
							continue;
						}
						?>
						<div class="alpt-glossary__item">
							<dt id="<?php echo esc_attr( $el['id'] ); ?>"><span class="alpt-glossary__sym" aria-hidden="true"><?php echo esc_html( $el['sym'] ); ?></span> <?php echo esc_html( $el['name'] ); ?> <span class="alpt-glossary__n"><?php echo esc_html( (string) $el['n'] ); ?></span></dt>
							<dd>
								<p><?php echo esc_html( $el['def'] ); ?></p>
								<?php if ( ! empty( $talk[ $el['sym'] ] ) ) : ?>
									<p class="alpt-glossary__talk"><strong><?php esc_html_e( 'Talk about it:', 'aiad-core' ); ?></strong> <?php echo esc_html( $talk[ $el['sym'] ] ); ?></p>
								<?php endif; ?>
								<?php $aiad_res = aiad_literacy_resources_for( 'syms', $el['sym'] ); ?>
								<?php if ( $aiad_res ) : ?>
									<p class="alpt-glossary__res"><strong><?php esc_html_e( 'Learn more:', 'aiad-core' ); ?></strong> <?php echo aiad_literacy_resource_links( $aiad_res ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_literacy_resource_links(). ?></p>
								<?php endif; ?>
								<?php if ( ! empty( $el_issues[ $el['sym'] ] ) ) : ?>
									<p class="alpt-glossary__issues"><strong><?php esc_html_e( 'Issues it raises:', 'aiad-core' ); ?></strong> <?php echo esc_html( implode( ', ', array_map( static fn( string $k ): string => $issues[ $k ]['name'], $el_issues[ $el['sym'] ] ) ) ); ?></p>
								<?php endif; ?>
								<p class="alpt-glossary__bench">
									<?php
									/* translators: %s: a benchmark domain, such as Privacy & Data Protection */
									printf( esc_html__( 'In the benchmark: %s.', 'aiad-core' ), '<a href="' . esc_url( $bench_url ) . '">' . esc_html( aiad_literacy_benchmark_domain( $el['sym'] ) ) . '</a>' );
									?>
								</p>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<section class="alpt-glossary alpt-issues" aria-labelledby="alpt-issues-title" id="alpt-issues">
		<h2 id="alpt-issues-title" class="alpt-glossary__title"><?php esc_html_e( 'The issues they raise, theme by theme', 'aiad-core' ); ?></h2>
		<p class="alpt-glossary__lead"><?php esc_html_e( 'The technical terms are the causes; these are the symptoms: what students actually meet when people use the technology. Each comes with a question to talk about in class, and the elements it arises from.', 'aiad-core' ); ?></p>
		<?php foreach ( $strands as $theme_key => $theme_name ) : ?>
			<details class="alpt-glossary__family alpt-th--<?php echo esc_attr( $theme_key ); ?>">
				<summary>
					<span class="alpt-chip__swatch" aria-hidden="true"></span>
					<span class="alpt-glossary__family-name">
						<?php
						/* translators: %s: theme name, such as Safe */
						echo esc_html( sprintf( __( '%s theme', 'aiad-core' ), $theme_name ) );
						?>
					</span>
				</summary>
				<dl>
					<?php foreach ( $issues as $issue_key => $issue ) : ?>
						<?php
						if ( $issue['theme'] !== $theme_key ) {
							continue;
						}
						?>
						<div class="alpt-glossary__item">
							<dt id="alpt-issue-<?php echo esc_attr( $issue_key ); ?>"><?php echo esc_html( $issue['name'] ); ?></dt>
							<dd>
								<p><?php echo esc_html( $issue['def'] ); ?></p>
								<p class="alpt-glossary__talk"><strong><?php esc_html_e( 'Talk about it:', 'aiad-core' ); ?></strong> <?php echo esc_html( $issue['talk'] ); ?></p>
								<?php $aiad_res = aiad_literacy_resources_for( 'issues', $issue_key ); ?>
								<?php if ( $aiad_res ) : ?>
									<p class="alpt-glossary__res"><strong><?php esc_html_e( 'Learn more:', 'aiad-core' ); ?></strong> <?php echo aiad_literacy_resource_links( $aiad_res ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in aiad_literacy_resource_links(). ?></p>
								<?php endif; ?>
								<p class="alpt-glossary__issues"><strong><?php esc_html_e( 'Arises from:', 'aiad-core' ); ?></strong>
									<?php
									echo implode( ', ', array_map( static fn( string $sym ): string => '<a href="#' . esc_attr( $by_sym[ $sym ]['id'] ?? '' ) . '">' . esc_html( $by_sym[ $sym ]['name'] ?? $sym ) . '</a>', $issue['syms'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
									?>
								</p>
							</dd>
						</div>
					<?php endforeach; ?>
				</dl>
			</details>
		<?php endforeach; ?>
	</section>

	<section class="alpt-suggest" aria-labelledby="alpt-suggest-title">
		<div class="alpt-suggest__intro">
			<p class="alpt-sticker"><?php esc_html_e( 'Your word here', 'aiad-core' ); ?></p>
			<h2 id="alpt-suggest-title" class="alpt-suggest__title"><?php esc_html_e( 'Suggest a new element', 'aiad-core' ); ?></h2>
			<p><?php esc_html_e( 'AI words change every month. Heard a term that should be on the table? Tell us what it means and which family it belongs to. The team reviews every suggestion and adds the best ones.', 'aiad-core' ); ?> <?php esc_html_e( 'If we add your word, we can credit you or your class on the table.', 'aiad-core' ); ?></p>
			<p class="alpt-suggest__bench">
				<?php esc_html_e( 'Want to see how ready you or your school are for these ideas in practice?', 'aiad-core' ); ?>
				<a href="<?php echo esc_url( aiad_literacy_benchmark_url() ); ?>"><?php esc_html_e( 'Take the AI Risk Readiness Benchmark', 'aiad-core' ); ?></a>
			</p>
		</div>
		<form class="alpt-suggest__form" data-alpt-suggest data-endpoint="<?php echo esc_url( rest_url( 'aiad/v1/literacy-term' ) ); ?>" novalidate hidden>
			<div class="alpt-suggest__row">
				<label class="alpt-suggest__field alpt-suggest__field--wide">
					<span><?php esc_html_e( 'The term', 'aiad-core' ); ?></span>
					<input type="text" name="term" maxlength="60" required autocomplete="off" placeholder="<?php esc_attr_e( 'For example, Prompt Injection', 'aiad-core' ); ?>">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'Symbol (optional)', 'aiad-core' ); ?></span>
					<input type="text" name="symbol" maxlength="3" autocomplete="off" placeholder="<?php esc_attr_e( 'Pj', 'aiad-core' ); ?>">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'Family', 'aiad-core' ); ?></span>
					<select name="family">
						<option value=""><?php esc_html_e( 'Not sure', 'aiad-core' ); ?></option>
						<?php foreach ( $families as $key => $family ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $family['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
			</div>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'What it means, in plain English', 'aiad-core' ); ?></span>
				<textarea name="definition" rows="3" maxlength="400" required></textarea>
			</label>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'Why should it be on the table? (optional)', 'aiad-core' ); ?></span>
				<textarea name="why" rows="2" maxlength="300"></textarea>
			</label>
			<div class="alpt-suggest__row">
				<label class="alpt-suggest__field alpt-suggest__field--wide">
					<span><?php esc_html_e( 'Your first name (optional)', 'aiad-core' ); ?></span>
					<input type="text" name="name" maxlength="60" autocomplete="given-name">
				</label>
				<label class="alpt-suggest__field">
					<span><?php esc_html_e( 'I am a…', 'aiad-core' ); ?></span>
					<select name="role">
						<option value=""><?php esc_html_e( 'Prefer not to say', 'aiad-core' ); ?></option>
						<option value="student"><?php esc_html_e( 'Student', 'aiad-core' ); ?></option>
						<option value="teacher"><?php esc_html_e( 'Teacher', 'aiad-core' ); ?></option>
						<option value="parent"><?php esc_html_e( 'Parent or carer', 'aiad-core' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'aiad-core' ); ?></option>
					</select>
				</label>
			</div>
			<label class="alpt-suggest__field">
				<span><?php esc_html_e( 'If we add it, credit it to (optional)', 'aiad-core' ); ?></span>
				<input type="text" name="credit" maxlength="80" autocomplete="off" placeholder="<?php esc_attr_e( 'For example, Year 9 at Oakfield School', 'aiad-core' ); ?>" aria-describedby="alpt-credit-help">
				<small id="alpt-credit-help" class="alpt-suggest__help"><?php esc_html_e( 'A class, group or first name only. Never a surname.', 'aiad-core' ); ?></small>
			</label>
			<label class="alpt-suggest__trap" aria-hidden="true"><?php esc_html_e( 'Leave this empty', 'aiad-core' ); ?> <input type="text" name="aiad_website" tabindex="-1" autocomplete="off"></label>
			<p class="alpt-suggest__note"><?php esc_html_e( 'Please don\'t include anyone\'s personal details. Suggestions are not published as they are: the team checks and edits them first.', 'aiad-core' ); ?></p>
			<div class="alpt-suggest__actions">
				<button type="submit" class="alpt-btn alpt-btn--ink"><?php esc_html_e( 'Send my suggestion', 'aiad-core' ); ?></button>
				<div class="alpt-suggest__status" data-alpt-suggest-status role="status" aria-live="polite">&nbsp;</div>
			</div>
		</form>
	</section>

	<?php if ( aiad_literacy_flows_ready() ) : ?>
	<script type="application/json" data-alpt-flows data-flow-js="<?php echo esc_url( AIAD_URI . '/assets/courses/shared/flow.js?ver=' . (int) @filemtime( AIAD_DIR . '/assets/courses/shared/flow.js' ) ); ?>" data-flow-css="<?php echo esc_url( AIAD_URI . '/assets/courses/shared/flow.css?ver=' . (int) @filemtime( AIAD_DIR . '/assets/courses/shared/flow.css' ) ); ?>" data-courses="<?php echo esc_url( home_url( '/courses/' ) ); ?>" data-stills="<?php echo esc_url( aiad_core_url( 'assets/data/literacy-table-stills.json' ) . '?ver=' . aiad_core_asset_version( 'assets/data/literacy-table-stills.json' ) ); ?>"><?php echo wp_json_encode( aiad_literacy_data( 'flows' ) + array( 'stills' => aiad_literacy_data( 'stills' ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
	<?php endif; ?>
	<script type="application/json" data-alpt-data><?php echo wp_json_encode( array( 'families' => array_map( static fn( array $f ): string => $f['name'], $families ), 'themes' => $strands, 'eras' => array_map( static fn( array $e ): string => $e['name'], $eras ), 'issues' => array_map( static fn( array $i ): array => array( $i['name'], $i['theme'], $i['def'], $i['talk'] ), $issues ), 'elIssues' => $el_issues, 'resources' => array_map( static fn( array $r ): array => array( $r['title'], $r['url'], $r['kind'] ), aiad_literacy_resources() ), 'elRes' => array_combine( array_column( $elements, 'sym' ), array_map( static fn( string $sym ): array => aiad_literacy_resources_for( 'syms', $sym ), array_column( $elements, 'sym' ) ) ), 'issueRes' => array_combine( array_keys( $issues ), array_map( static fn( string $k ): array => aiad_literacy_resources_for( 'issues', $k ), array_keys( $issues ) ) ), 'talk' => $talk, 'quiz' => aiad_literacy_quiz(), 'pics' => (object) array_map( static fn( array $p ): string => $p['alt'], aiad_literacy_pictures() ), 'picBase' => aiad_literacy_picture_base(), 'safe' => aiad_literacy_safeguarding_syms(), 'credits' => (object) aiad_literacy_reader_credits(), 'notes' => (object) array_filter( array_column( aiad_literacy_element_records(), 'teacher_note', 'sym' ) ), 'elements' => array_map( static fn( array $el ): array => array( $el['sym'], $el['name'], $el['family'], $el['def'], $el['id'], $el['theme'], $el['year'], $el['approx'], $el['era'], $el['older'] ), $elements ), 'compounds' => array_map( static fn( array $c ): array => array( $c['id'], $c['name'], $c['syms'], $c['volatile'], $c['level'], $c['what'], $c['debate'], $c['strand'], $c['example'], (object) $c['marks'] ), $compounds ), 'links' => aiad_literacy_connections(), 'bench' => array_combine( array_column( $elements, 'sym' ), array_map( 'aiad_literacy_benchmark_domain', array_column( $elements, 'sym' ) ) ) ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?></script>
</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Whether "See how it works" can run: the course animations and illustrations (data/literacy-table/flows.json and
 * stills.json, built from the AI Under the Hood courses by scripts/build-literacy-flows.py and build-literacy-stills.mjs;
 * the drawings themselves are in assets/data/literacy-table-stills.json) and the courses' flow engine in the theme.
 */
function aiad_literacy_flows_ready(): bool {
	return defined( 'AIAD_URI' ) && defined( 'AIAD_DIR' ) && is_readable( AIAD_DIR . '/assets/courses/shared/flow.js' ) && ! empty( aiad_literacy_data( 'flows' )['elements'] );
}

/**
 * The AI Risk Readiness Benchmark's address (plugins/ai-risk-readiness-benchmark), for the links from the table.
 */
function aiad_literacy_benchmark_url(): string {
	if ( class_exists( 'AIRB_Defaults' ) && method_exists( 'AIRB_Defaults', 'benchmark_page_url' ) ) {
		return (string) AIRB_Defaults::benchmark_page_url();
	}
	return home_url( '/ai-risk-readiness-benchmark/' );
}

/**
 * Which benchmark domain each element relates to (AIRB_Defaults::domains()). The benchmark can't open at one
 * domain, so the panel and the list of definitions name the domain and link to the benchmark. Elements not listed
 * here fall under AI Literacy.
 *
 * @return array<string, array{name:string, syms:string}> Domain key => its name and the symbols in it.
 */
function aiad_literacy_benchmark_domains(): array {
	$syms = array();
	foreach ( aiad_literacy_element_records() as $e ) {
		if ( ! empty( $e['benchmark'] ) ) {
			$syms[ (string) $e['benchmark'] ][] = (string) $e['sym'];
		}
	}
	$domains = array();
	foreach ( aiad_literacy_list( 'benchmark_domains' ) as $key => $d ) {
		$domains[ $key ] = array( 'name' => (string) $d['name'], 'syms' => implode( ' ', $syms[ $key ] ?? array() ) );
	}
	return $domains;
}

/**
 * An element's benchmark domain name (AI Literacy when it has no closer one).
 *
 * @param string $sym An element's symbol.
 */
function aiad_literacy_benchmark_domain( string $sym ): string {
	static $by_sym = null;
	if ( null === $by_sym ) {
		$by_sym = array();
		foreach ( aiad_literacy_benchmark_domains() as $domain ) {
			foreach ( explode( ' ', $domain['syms'] ) as $s ) {
				$by_sym[ $s ] = $domain['name'];
			}
		}
	}
	return $by_sym[ $sym ] ?? __( 'AI Literacy', 'aiad-core' );
}

/*
 * The "Suggest a new element" form posts to aiad/v1/literacy-term, which saves and emails the suggestion. That route
 * is in the aiad-core plugin (plugins/aiad-core/modules/literacy-term.php), loaded by inc/bootstrap.php.
 */

/**
 * The element of the week card, for the page's introduction (block aiad/literacy-week). It is its own block so it can
 * sit beside the title while the table follows below; it still changes every Monday, since it is drawn on each view.
 */
function aiad_render_literacy_week(): string {
	// No elements (a broken elements.json and no saved copy yet): show nothing rather than a broken box.
	if ( null === aiad_literacy_element_of_week()['el'] ) {
		return '';
	}
	// On another page (the National Conversation, say) the card links to the table: "Open it" to the word's panel and
	// "Show to class" to the table with the word already on the board (#board-me, read by literacy-table.js).
	$aiad_away = ! aiad_is_literacy_table_page();
	$aiad_base = $aiad_away ? aiad_literacy_table_url() : '';
	ob_start();
	?>
<?php $aiad_week = aiad_literacy_element_of_week(); $aiad_week_theme = $aiad_week['el']['theme'] ?? 'smart'; $aiad_week_sym = strtolower( $aiad_week['el']['sym'] ); ?>
<?php if ( $aiad_away ) : ?><div class="alpt-away"><?php endif; ?>
<div class="alpt-eotw alpt-eotw--<?php echo esc_attr( $aiad_week_theme ); ?>">
	<span class="alpt-eotw__sticker" aria-hidden="true"><?php esc_html_e( 'New every Monday', 'aiad-core' ); ?></span>
	<div class="alpt-eotw__tile alpt-fam--<?php echo esc_attr( $aiad_week['el']['family'] ); ?>" aria-hidden="true"><span class="alpt-eotw__sym"><?php echo esc_html( $aiad_week['el']['sym'] ); ?></span></div>
	<div class="alpt-eotw__text">
		<p class="alpt-eotw__label">
			<?php
			/* translators: %s: a date, such as 16 November 2026 */
			echo esc_html( sprintf( /* translators: 1: date, such as 16 Nov, 2: theme name, such as Safe */ __( 'Word of the week · %1$s · %2$s theme', 'aiad-core' ), date_i18n( 'j M', $aiad_week['monday'] ), aiad_literacy_strands()[ $aiad_week_theme ] ?? '' ) );
			?>
		</p>
		<h2 class="alpt-eotw__name"><?php echo esc_html( $aiad_week['el']['name'] ); ?></h2>
		<p class="alpt-eotw__talk"><?php echo esc_html( $aiad_week['talk'] ); ?></p>
	</div>
	<div class="alpt-eotw__actions">
		<?php if ( $aiad_away ) : ?>
		<a class="alpt-btn alpt-btn--ink" href="<?php echo esc_url( $aiad_base . '#element-' . $aiad_week_sym ); ?>"><?php esc_html_e( 'Open it', 'aiad-core' ); ?></a>
		<a class="alpt-btn" href="<?php echo esc_url( $aiad_base . '#board-' . $aiad_week_sym ); ?>"><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></a>
		<?php else : ?>
		<a class="alpt-btn alpt-btn--ink" href="#element-<?php echo esc_attr( $aiad_week_sym ); ?>"><?php esc_html_e( 'Open it', 'aiad-core' ); ?></a>
		<button type="button" class="alpt-btn" data-alpt-eotw-board="<?php echo esc_attr( (string) ( $aiad_week['el']['n'] - 1 ) ); ?>" hidden><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></button>
		<?php endif; ?>
	</div>
</div>
<?php if ( $aiad_away ) : ?></div><?php endif; ?>
	<?php
	// From 1 to 25 December the table's own page has the Find Santa riddle in this card's place (santa.php).
	return (string) ob_get_clean() . ( $aiad_away ? '' : aiad_render_literacy_santa() );
}

/**
 * A key word for one AIAD theme, on another page (the National Conversation's theme pop-ups): the word of the week
 * card, drawn the same way, for an element in that theme with a question to talk about. Like the word of the week it
 * changes every Monday, working through the theme's words in the same order, and leaves out the safeguarding ones.
 * "Open it" goes to the word on the table and "Show to class" puts it on the table's board.
 *
 * @param string $theme safe, smart, creative, responsible or future.
 */
function aiad_render_literacy_theme_word( string $theme ): string {
	$by_sym = array_column( aiad_literacy_elements(), null, 'sym' );
	$pool   = array_values( array_filter( aiad_literacy_week_pool(), static fn( string $sym ): bool => ( $by_sym[ $sym ]['theme'] ?? '' ) === $theme ) );
	if ( array() === $pool ) {
		return '';
	}
	$monday = strtotime( 'monday this week', current_time( 'timestamp' ) );
	$el     = $by_sym[ $pool[ (int) floor( $monday / WEEK_IN_SECONDS ) % count( $pool ) ] ];
	$sym    = strtolower( $el['sym'] );
	$base   = aiad_literacy_table_url();
	wp_enqueue_style( 'aiad-literacy-table' );
	ob_start();
	?>
<div class="alpt-away alpt-away--key">
<div class="alpt-eotw alpt-eotw--<?php echo esc_attr( $theme ); ?>">
	<div class="alpt-eotw__tile alpt-fam--<?php echo esc_attr( $el['family'] ); ?>" aria-hidden="true"><span class="alpt-eotw__sym"><?php echo esc_html( $el['sym'] ); ?></span></div>
	<div class="alpt-eotw__text">
		<p class="alpt-eotw__label"><?php echo esc_html( sprintf( /* translators: %s: theme name, such as Safe */ __( 'Key word from the Periodic Table · %s theme', 'aiad-core' ), aiad_literacy_strands()[ $theme ] ?? '' ) ); ?></p>
		<p class="alpt-eotw__name"><?php echo esc_html( $el['name'] ); ?></p>
		<p class="alpt-eotw__talk"><?php echo esc_html( aiad_literacy_talk()[ $el['sym'] ] ?? '' ); ?></p>
	</div>
	<div class="alpt-eotw__actions">
		<a class="alpt-btn alpt-btn--ink" href="<?php echo esc_url( $base . '#element-' . $sym ); ?>"><?php esc_html_e( 'Open it', 'aiad-core' ); ?></a>
		<a class="alpt-btn" href="<?php echo esc_url( $base . '#board-' . $sym ); ?>"><?php esc_html_e( 'Show to class', 'aiad-core' ); ?></a>
	</div>
</div>
</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * The page's stylesheet and script, and the table's block (aiad/literacy-table), which loads them wherever it is used.
 * In the editor it shows the table as the page does (assets/js/literacy-table-edit.js).
 */
function aiad_register_literacy_table(): void {
	$css    = 'assets/css/pages/ai-literacy-periodic-table.css';
	$js     = 'assets/js/literacy-table.js';
	$editor = 'assets/js/literacy-table-edit.js';

	// No dependency here: the theme's main stylesheet is added as one when it is registered (below), so the table's
	// styles still print after it, and still print at all under another theme.
	wp_register_style( 'aiad-literacy-table', aiad_core_url( $css ), array(), aiad_core_asset_version( $css ) );
	wp_register_script( 'aiad-literacy-table', aiad_core_url( $js ), array(), aiad_core_asset_version( $js ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	$santa  = 'assets/js/literacy-santa.js';
	wp_register_script( 'aiad-literacy-santa', aiad_core_url( $santa ), array(), aiad_core_asset_version( $santa ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_register_script( 'aiad-literacy-table-edit', aiad_core_url( $editor ), array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ), aiad_core_asset_version( $editor ), true );

	register_block_type(
		'aiad/literacy-table',
		array(
			'api_version'     => 3,
			'title'           => __( 'AI Literacy Periodic Table', 'aiad-core' ),
			'description'     => __( 'The interactive table of 142 AI terms, with every definition listed below it. Its elements come from the JSON files in the AI Awareness Day Core plugin.', 'aiad-core' ),
			'category'        => 'widgets',
			'icon'            => 'grid-view',
			'keywords'        => array( 'periodic', 'literacy', 'glossary' ),
			'supports'        => array( 'html' => false, 'multiple' => false, 'reusable' => false ),
			'editor_script'   => 'aiad-literacy-table-edit',
			'style'           => 'aiad-literacy-table',
			'view_script'     => 'aiad-literacy-table',
			'render_callback' => static fn(): string => aiad_render_literacy_table(),
		)
	);
	register_block_type(
		'aiad/literacy-week',
		array(
			'api_version'     => 3,
			'title'           => __( 'AI Literacy element of the week', 'aiad-core' ),
			'description'     => __( 'This week\'s element from the AI Literacy Periodic Table, with its question for class. It changes every Monday.', 'aiad-core' ),
			'category'        => 'widgets',
			'icon'            => 'calendar-alt',
			'supports'        => array( 'html' => false, 'multiple' => false, 'reusable' => false ),
			'style'           => 'aiad-literacy-table',
			'render_callback' => static fn(): string => aiad_render_literacy_week(),
		)
	);
}
add_action( 'init', 'aiad_register_literacy_table' );

/**
 * Print the table's stylesheet after the theme's main one, as it always has, when the theme provides it.
 */
function aiad_literacy_table_style_after_theme(): void {
	$styles = wp_styles();
	if ( isset( $styles->registered['aiad-literacy-table'], $styles->registered['aiad-style'] ) && ! in_array( 'aiad-style', $styles->registered['aiad-literacy-table']->deps, true ) ) {
		$styles->registered['aiad-literacy-table']->deps[] = 'aiad-style';
	}
}
add_action( 'wp_enqueue_scripts', 'aiad_literacy_table_style_after_theme', 11 );
