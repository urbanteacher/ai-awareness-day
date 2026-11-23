<?php
/**
 * The AI Literacy Periodic Table's back end: Tools → Periodic Table in wp-admin.
 *
 * The table's content lives in the JSON files in plugins/aiad-core/data/literacy-table/ (format: plugins/aiad-core/data/literacy-table/README.md), the way
 * the AI Risk Readiness Benchmark keeps its question bank in JSON. Edit those files and deploy; this page shows what is
 * in them, checks them (an issue naming an element that doesn't exist, two tiles in one place, a compound with an
 * unknown level…), previews the coming words of the week, and exports everything as JSON or CSV for review.
 *
 * Read-only on purpose: the files stay the one source, with their history in git.
 *
 * Moved from the theme's inc/literacy-table-admin.php; the data and pictures are this plugin's.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check the table's JSON files. Errors break something on the page; warnings are worth a look.
 *
 * @return array<int, array{0:string, 1:string}> Level (error or warning) and message.
 */
function aiad_literacy_check(): array {
	$out  = array();
	$err  = static function ( string $m ) use ( &$out ): void {
		$out[] = array( 'error', $m );
	};
	$warn = static function ( string $m ) use ( &$out ): void {
		$out[] = array( 'warning', $m );
	};

	foreach ( array( 'elements', 'issues', 'compounds', 'resources', 'lists' ) as $kind ) {
		$data = aiad_literacy_data( $kind );
		if ( ! empty( $GLOBALS['aiad_literacy_data_failed'][ $kind ] ) ) {
			$err( sprintf( '%s.json is missing or is not valid JSON. The page is using the last copy that read cleanly until it is fixed.', $kind ) );
		} elseif ( array() === $data ) {
			$err( sprintf( '%s.json is empty.', $kind ) );
		} elseif ( 'aiad.literacy-table/1' !== ( $data['schema'] ?? '' ) ) {
			$warn( sprintf( '%s.json does not say "schema": "aiad.literacy-table/1".', $kind ) );
		}
	}

	$families = aiad_literacy_list( 'families' );
	$themes   = aiad_literacy_list( 'themes' );
	$levels   = aiad_literacy_list( 'levels' );
	$domains  = aiad_literacy_list( 'benchmark_domains' );
	$records  = aiad_literacy_element_records();
	$syms     = array();
	$places   = array();
	foreach ( $records as $i => $e ) {
		$sym   = (string) ( $e['sym'] ?? '' );
		$label = '' === $sym ? sprintf( 'Element %d', $i + 1 ) : $sym . ' (' . ( $e['name'] ?? '?' ) . ')';
		if ( '' === $sym ) {
			$err( $label . ' has no symbol.' );
			continue;
		}
		if ( isset( $syms[ $sym ] ) ) {
			$err( sprintf( 'Two elements use the symbol %s.', $sym ) );
		}
		$syms[ $sym ] = true;
		foreach ( array( 'name', 'def' ) as $field ) {
			if ( '' === trim( (string) ( $e[ $field ] ?? '' ) ) ) {
				$err( sprintf( '%s has no %s.', $label, 'def' === $field ? 'definition' : 'name' ) );
			}
		}
		if ( ! isset( $families[ $e['family'] ?? '' ] ) ) {
			$err( sprintf( '%s is in an unknown family, "%s".', $label, $e['family'] ?? '' ) );
		}
		if ( ! isset( $themes[ $e['theme'] ?? '' ] ) ) {
			$err( sprintf( '%s has an unknown theme, "%s".', $label, $e['theme'] ?? '' ) );
		}
		$row = (int) ( $e['row'] ?? 0 );
		$col = (int) ( $e['col'] ?? 0 );
		if ( $row < 1 || $row > 12 || $col < 1 || $col > 18 ) {
			$err( sprintf( '%s sits off the grid (row %d, column %d). Rows run 1 to 12, columns 1 to 18.', $label, $row, $col ) );
		}
		$place = $row . '/' . $col;
		if ( isset( $places[ $place ] ) ) {
			$err( sprintf( '%s and %s are both at row %d, column %d.', $places[ $place ], $label, $row, $col ) );
		}
		$places[ $place ] = $label;
		$year = (int) ( $e['year'] ?? 0 );
		if ( $year < 1800 || $year > (int) gmdate( 'Y' ) + 1 ) {
			$warn( sprintf( '%s has the year %d. Check it.', $label, $year ) );
		}
		if ( ! empty( $e['benchmark'] ) && ! isset( $domains[ $e['benchmark'] ] ) ) {
			$err( sprintf( '%s links to an unknown benchmark area, "%s".', $label, $e['benchmark'] ) );
		}
		if ( isset( $e['links'] ) && ! is_array( $e['links'] ) ) {
			$err( sprintf( '%s: "links" should be a list of symbols.', $label ) );
		}
	}
	foreach ( $records as $e ) {
		foreach ( (array) ( $e['links'] ?? array() ) as $other ) {
			if ( ! isset( $syms[ $other ] ) ) {
				$err( sprintf( '%s is connected to %s, which is not an element.', $e['sym'] ?? '?', $other ) );
			}
		}
	}
	if ( 142 !== count( $records ) ) {
		$warn( sprintf( 'There are %d elements. The page, poster and launch post say 142.', count( $records ) ) );
	}

	$keys = array();
	foreach ( (array) ( aiad_literacy_data( 'issues' )['issues'] ?? array() ) as $i => $issue ) {
		$key   = (string) ( $issue['key'] ?? '' );
		$label = 'Issue "' . ( $issue['name'] ?? $key ) . '"';
		if ( '' === $key || ! preg_match( '/^[a-z0-9-]+$/', $key ) ) {
			$err( sprintf( '%s needs a key in lowercase letters, numbers and hyphens.', $label ) );
		}
		if ( isset( $keys[ $key ] ) ) {
			$err( sprintf( 'Two issues use the key "%s".', $key ) );
		}
		$keys[ $key ] = true;
		if ( ! isset( $themes[ $issue['theme'] ?? '' ] ) ) {
			$err( sprintf( '%s has an unknown theme, "%s".', $label, $issue['theme'] ?? '' ) );
		}
		if ( '' === trim( (string) ( $issue['def'] ?? '' ) ) ) {
			$err( $label . ' has no definition.' );
		}
		$els = (array) ( $issue['elements'] ?? array() );
		if ( array() === $els ) {
			$warn( $label . ' is not linked to any element, so no panel shows it.' );
		}
		foreach ( $els as $sym ) {
			if ( ! isset( $syms[ $sym ] ) ) {
				$err( sprintf( '%s names %s, which is not an element.', $label, $sym ) );
			}
		}
	}

	$ids = array();
	foreach ( aiad_literacy_compounds() as $c ) {
		$label = 'Compound "' . $c['name'] . '"';
		if ( isset( $ids[ $c['id'] ] ) ) {
			$err( sprintf( '%s uses the same elements as "%s", so the challenge can\'t tell them apart.', $label, $ids[ $c['id'] ] ) );
		}
		$ids[ $c['id'] ] = $c['name'];
		if ( count( $c['syms'] ) < 2 ) {
			$err( $label . ' needs at least two elements.' );
		}
		foreach ( $c['syms'] as $sym ) {
			if ( ! isset( $syms[ $sym ] ) ) {
				$err( sprintf( '%s uses %s, which is not an element.', $label, $sym ) );
			}
		}
		if ( ! isset( $themes[ $c['strand'] ] ) ) {
			$err( sprintf( '%s has an unknown theme, "%s".', $label, $c['strand'] ) );
		}
		if ( ! isset( $levels[ $c['level'] ] ) ) {
			$err( sprintf( '%s has an unknown level, "%s".', $label, $c['level'] ) );
		}
		foreach ( array( 'what', 'example', 'loop', 'debate' ) as $field ) {
			if ( '' === trim( (string) $c[ $field ] ) ) {
				$warn( sprintf( '%s has no "%s".', $label, $field ) );
			}
		}
		foreach ( $c['marks'] as $sym => $words ) {
			if ( ! in_array( $sym, $c['syms'], true ) ) {
				$warn( sprintf( '%s marks words for %s, which is not one of its elements.', $label, $sym ) );
			} elseif ( false === strpos( $c['what'], $words ) ) {
				$warn( sprintf( '%s marks "%s" for %s, but those words are not in its "what", so nothing is coloured.', $label, $words, $sym ) );
			}
		}
	}

	foreach ( (array) ( aiad_literacy_data( 'resources' )['resources'] ?? array() ) as $r ) {
		$label = 'Resource "' . ( $r['slug'] ?? '?' ) . '"';
		$post  = get_page_by_path( (string) ( $r['slug'] ?? '' ), OBJECT, (string) ( $r['post_type'] ?? '' ) );
		if ( ! $post instanceof WP_Post ) {
			$warn( $label . ' is not on this site, so it is left out of the page.' );
		} elseif ( 'publish' !== $post->post_status ) {
			$warn( $label . ' is not published, so it is left out of the page.' );
		}
		foreach ( (array) ( $r['elements'] ?? array() ) as $sym ) {
			if ( ! isset( $syms[ $sym ] ) ) {
				$err( sprintf( '%s names %s, which is not an element.', $label, $sym ) );
			}
		}
		foreach ( (array) ( $r['issues'] ?? array() ) as $key ) {
			if ( ! isset( $keys[ $key ] ) ) {
				$err( sprintf( '%s names the issue "%s", which does not exist.', $label, $key ) );
			}
		}
	}

	$seen = array();
	// Pictures: every SVG needs an element and alt text, and alt text needs a picture.
	$pictures = aiad_literacy_pictures();
	foreach ( $records as $e ) {
		$sym = (string) ( $e['sym'] ?? '' );
		if ( isset( $pictures[ $sym ] ) && '' === trim( (string) ( $e['picture_alt'] ?? '' ) ) ) {
			$warn( sprintf( '%s has a picture but no "picture_alt" in elements.json, so screen readers get no description.', $sym ) );
		}
		if ( ! isset( $pictures[ $sym ] ) && '' !== trim( (string) ( $e['picture_alt'] ?? '' ) ) ) {
			$warn( sprintf( '%1$s has "picture_alt" but no picture: add plugins/aiad-core/assets/images/literacy-table/%2$s.svg.', $sym, strtolower( $sym ) ) );
		}
	}
	$lower = array_change_key_case( array_flip( array_keys( $syms ) ), CASE_LOWER );
	foreach ( (array) glob( aiad_core_path( 'assets/images/literacy-table/*.svg' ) ) as $file ) {
		$name = basename( (string) $file, '.svg' );
		if ( ! isset( $lower[ $name ] ) ) {
			$warn( sprintf( 'The picture %s.svg does not match any element symbol, so it is not shown.', $name ) );
		}
	}
	foreach ( (array) ( aiad_literacy_data( 'lists' )['quiz'] ?? array() ) as $n => $q ) {
		$q_syms = array_merge( array( (string) ( $q['answer'] ?? '' ) ), array_map( 'strval', (array) ( $q['others'] ?? array() ) ) );
		if ( 3 !== count( $q_syms ) || '' === (string) ( $q['question'] ?? '' ) ) {
			$warn( sprintf( 'Quiz question %d needs a question, an answer and two others. It is left out of the quiz.', $n + 1 ) );
			continue;
		}
		foreach ( $q_syms as $sym ) {
			if ( ! isset( $syms[ $sym ] ) ) {
				$warn( sprintf( 'Quiz question %1$d uses %2$s, which is not on the table. It is left out of the quiz.', $n + 1, $sym ) );
			}
		}
	}
	foreach ( (array) ( aiad_literacy_data( 'lists' )['week_rotation'] ?? array() ) as $sym ) {
		if ( ! isset( $syms[ $sym ] ) ) {
			$err( sprintf( 'The word-of-the-week order names %s, which is not an element.', $sym ) );
		}
		if ( isset( $seen[ $sym ] ) ) {
			$warn( sprintf( 'The word-of-the-week order lists %s twice.', $sym ) );
		}
		$seen[ $sym ] = true;
	}
	if ( array() === aiad_literacy_week_pool() ) {
		$err( 'No element can be word of the week: none has a talk question outside the safeguarding ones.' );
	}
	return $out;
}

/**
 * Everything in the table's files, as one array (the "All, as JSON" export).
 *
 * @return array<string, mixed>
 */
function aiad_literacy_export_all(): array {
	$all = array(
		'schema'   => 'aiad.literacy-table/1',
		'kind'     => 'export',
		'exported' => gmdate( 'c' ),
		'site'     => home_url( '/' ),
	);
	foreach ( array( 'elements', 'issues', 'compounds', 'resources', 'lists' ) as $kind ) {
		$all[ $kind ] = array_diff_key( aiad_literacy_data( $kind ), array_flip( array( 'schema', 'kind', 'about' ) ) );
	}
	return $all;
}

/**
 * Rows for a CSV export.
 *
 * @param string $what elements, issues, compounds or resources.
 * @return array<int, array<int, string>> Header row first.
 */
function aiad_literacy_csv_rows( string $what ): array {
	$list = static fn( $v ): string => implode( ' ', (array) $v );
	$yes  = static fn( $v ): string => empty( $v ) ? '' : 'yes';
	switch ( $what ) {
		case 'issues':
			$rows = array( array( 'key', 'name', 'theme', 'definition', 'talk question', 'elements' ) );
			foreach ( (array) ( aiad_literacy_data( 'issues' )['issues'] ?? array() ) as $i ) {
				$rows[] = array( $i['key'] ?? '', $i['name'] ?? '', $i['theme'] ?? '', $i['def'] ?? '', $i['talk'] ?? '', $list( $i['elements'] ?? array() ) );
			}
			return $rows;
		case 'compounds':
			$rows = array( array( 'id', 'name', 'elements', 'theme', 'level', 'volatile', 'what it is', 'example', 'human in the loop', 'debate' ) );
			foreach ( aiad_literacy_compounds() as $c ) {
				$rows[] = array( $c['id'], $c['name'], $list( $c['syms'] ), $c['strand'], $c['level'], $yes( $c['volatile'] ), $c['what'], $c['example'], $c['loop'], $c['debate'] );
			}
			return $rows;
		case 'resources':
			$rows = array( array( 'post type', 'slug', 'kind', 'elements', 'issues', 'on the page', 'title', 'url' ) );
			foreach ( (array) ( aiad_literacy_data( 'resources' )['resources'] ?? array() ) as $r ) {
				$post   = get_page_by_path( (string) ( $r['slug'] ?? '' ), OBJECT, (string) ( $r['post_type'] ?? '' ) );
				$live   = $post instanceof WP_Post && 'publish' === $post->post_status;
				$rows[] = array( $r['post_type'] ?? '', $r['slug'] ?? '', $r['kind'] ?? '', $list( $r['elements'] ?? array() ), $list( $r['issues'] ?? array() ), $live ? 'yes' : 'no', $live ? get_the_title( $post ) : '', $live ? get_permalink( $post ) : '' );
			}
			return $rows;
		default:
			$numbers = array_column( aiad_literacy_elements(), 'n', 'sym' );
			$issues  = aiad_literacy_element_issues();
			$rows    = array( array( 'number', 'symbol', 'name', 'family', 'theme', 'row', 'column', 'year', 'approximate', 'older word', 'definition', 'talk question', 'connections', 'benchmark area', 'safeguarding', 'issues', 'credit', 'year note' ) );
			foreach ( aiad_literacy_element_records() as $e ) {
				$sym    = (string) $e['sym'];
				$rows[] = array( (string) ( $numbers[ $sym ] ?? '' ), $sym, $e['name'] ?? '', $e['family'] ?? '', $e['theme'] ?? '', (string) ( $e['row'] ?? '' ), (string) ( $e['col'] ?? '' ), (string) ( $e['year'] ?? '' ), $yes( $e['approx'] ?? false ), $yes( $e['older'] ?? false ), $e['def'] ?? '', $e['talk'] ?? '', $list( $e['links'] ?? array() ), $e['benchmark'] ?? '', $yes( $e['safeguarding'] ?? false ), $list( $issues[ $sym ] ?? array() ), $e['credit'] ?? '', $e['year_note'] ?? '' );
			}
			usort( $rows, static fn( $a, $b ) => 'number' === $a[0] ? -1 : ( 'number' === $b[0] ? 1 : (int) $a[0] <=> (int) $b[0] ) );
			return $rows;
	}
}

/** The capability the page and its exports need. */
function aiad_literacy_admin_cap(): string {
	return 'edit_pages';
}

add_action(
	'admin_menu',
	static function (): void {
		add_management_page(
			__( 'AI Literacy Periodic Table', 'aiad-core' ),
			__( 'Periodic Table', 'aiad-core' ),
			aiad_literacy_admin_cap(),
			'aiad-literacy-table',
			'aiad_render_literacy_admin'
		);
	}
);

add_action(
	'admin_post_aiad_literacy_export',
	static function (): void {
		if ( ! current_user_can( aiad_literacy_admin_cap() ) ) {
			wp_die( esc_html__( 'You are not allowed to export the table.', 'aiad-core' ), 403 );
		}
		check_admin_referer( 'aiad_literacy_export' );
		$format = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : 'json';
		$what   = isset( $_GET['what'] ) ? sanitize_key( wp_unslash( $_GET['what'] ) ) : 'all';
		$date   = gmdate( 'Y-m-d' );
		nocache_headers();
		if ( 'csv' === $format && in_array( $what, array( 'elements', 'issues', 'compounds', 'resources' ), true ) ) {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename=ai-literacy-periodic-table-' . $what . '-' . $date . '.csv' );
			$fh = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
			fwrite( $fh, "\xEF\xBB\xBF" ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite -- So Excel reads it as UTF-8.
			foreach ( aiad_literacy_csv_rows( $what ) as $row ) {
				fputcsv( $fh, aiad_csv_safe_row( $row ) );
			}
			fclose( $fh ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			exit;
		}
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=ai-literacy-periodic-table-' . $date . '.json' );
		echo wp_json_encode( aiad_literacy_export_all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}
);

/**
 * An export link.
 *
 * @param string $format json or csv.
 * @param string $what   all, elements, issues, compounds or resources.
 */
function aiad_literacy_export_url( string $format, string $what ): string {
	return wp_nonce_url( add_query_arg( array( 'action' => 'aiad_literacy_export', 'format' => $format, 'what' => $what ), admin_url( 'admin-post.php' ) ), 'aiad_literacy_export' );
}

/** Tools → Periodic Table. */
function aiad_render_literacy_admin(): void {
	if ( ! current_user_can( aiad_literacy_admin_cap() ) ) {
		return;
	}
	$checks    = aiad_literacy_check();
	$errors    = array_filter( $checks, static fn( $c ) => 'error' === $c[0] );
	$warnings  = array_filter( $checks, static fn( $c ) => 'warning' === $c[0] );
	$elements  = aiad_literacy_elements();
	$issues    = aiad_literacy_issues();
	$compounds = aiad_literacy_compounds();
	$families  = aiad_literacy_families();
	$themes    = aiad_literacy_strands();
	$levels    = aiad_literacy_levels();
	$talk      = aiad_literacy_talk();
	$el_issues = aiad_literacy_element_issues();
	$res_all   = (array) ( aiad_literacy_data( 'resources' )['resources'] ?? array() );
	$res_live  = aiad_literacy_resources();
	$dir       = aiad_core_path( 'data/literacy-table' );
	$suggested = post_type_exists( 'form_submission' ) ? get_posts(
		array(
			'post_type'   => 'form_submission',
			'post_status' => 'any',
			'numberposts' => 200,
			'meta_key'    => '_submission_involved_as', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'  => 'periodic_table_suggestion', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	) : array();
	$tab       = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'elements'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$tabs      = array(
		'elements'  => sprintf( __( 'Elements (%d)', 'aiad-core' ), count( $elements ) ),
		'issues'    => sprintf( __( 'Issues (%d)', 'aiad-core' ), count( $issues ) ),
		'compounds' => sprintf( __( 'Compounds (%d)', 'aiad-core' ), count( $compounds ) ),
		'resources' => sprintf( __( 'Learn more links (%d)', 'aiad-core' ), count( $res_all ) ),
		'week'      => __( 'Word of the week', 'aiad-core' ),
		'suggest'   => sprintf( __( 'Suggestions from visitors (%d)', 'aiad-core' ), count( $suggested ) ),
	);
	if ( ! isset( $tabs[ $tab ] ) ) {
		$tab = 'elements';
	}
	$page_url = admin_url( 'tools.php?page=aiad-literacy-table' );
	?>
	<div class="wrap aiad-lt">
		<style>
			.aiad-lt .aiad-lt__cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(10rem, 1fr)); gap: 12px; margin: 16px 0; }
			.aiad-lt .aiad-lt__card { background: #fff; border: 1px solid #c3c4c7; padding: 12px 14px; }
			.aiad-lt .aiad-lt__card strong { display: block; font-size: 26px; line-height: 1.1; }
			.aiad-lt .aiad-lt__card--ok { border-left: 4px solid #00a32a; }
			.aiad-lt .aiad-lt__card--bad { border-left: 4px solid #d63638; }
			.aiad-lt .aiad-lt__card--warn { border-left: 4px solid #dba617; }
			.aiad-lt .aiad-lt__exports { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; margin: 8px 0 16px; }
			.aiad-lt .aiad-lt__checks li { margin: 4px 0; }
			.aiad-lt .aiad-lt__checks .is-error::before { content: "Error: "; color: #d63638; font-weight: 600; }
			.aiad-lt .aiad-lt__checks .is-warning::before { content: "Check: "; color: #996800; font-weight: 600; }
			.aiad-lt .aiad-lt__filter { width: min(100%, 26rem); margin: 12px 0; }
			.aiad-lt table.widefat td { vertical-align: top; }
			.aiad-lt .aiad-lt__sym { font-weight: 700; font-size: 15px; }
			.aiad-lt .aiad-lt__muted { color: #646970; }
			.aiad-lt code { white-space: nowrap; }
			.aiad-lt .aiad-lt__files td:first-child { white-space: nowrap; }
		</style>
		<h1><?php esc_html_e( 'AI Literacy Periodic Table', 'aiad-core' ); ?></h1>
		<p>
			<?php esc_html_e( 'Everything on the table comes from the JSON files below, kept with the theme in git, the way the AI Risk Readiness Benchmark keeps its questions. To change a definition, add an element or issue, or move the word of the week on, edit the file, commit it to main and deploy. This page checks the files and exports them.', 'aiad-core' ); ?>
			<a href="<?php echo esc_url( aiad_literacy_table_url() ); ?>"><?php esc_html_e( 'View the table', 'aiad-core' ); ?></a>
		</p>

		<div class="aiad-lt__cards">
			<div class="aiad-lt__card <?php echo $errors ? 'aiad-lt__card--bad' : ( $warnings ? 'aiad-lt__card--warn' : 'aiad-lt__card--ok' ); ?>">
				<strong><?php echo $errors ? esc_html( (string) count( $errors ) ) : '✓'; ?></strong>
				<?php echo $errors ? esc_html( _n( 'error to fix', 'errors to fix', count( $errors ), 'aiad-core' ) ) : esc_html__( 'No errors', 'aiad-core' ); ?>
				<?php if ( $warnings ) : ?>
					<br><span class="aiad-lt__muted"><?php echo esc_html( sprintf( _n( '%d thing to check', '%d things to check', count( $warnings ), 'aiad-core' ), count( $warnings ) ) ); ?></span>
				<?php endif; ?>
			</div>
			<div class="aiad-lt__card"><strong><?php echo esc_html( (string) count( $elements ) ); ?></strong><?php esc_html_e( 'elements', 'aiad-core' ); ?></div>
			<div class="aiad-lt__card"><strong><?php echo esc_html( (string) count( $issues ) ); ?></strong><?php esc_html_e( 'issues', 'aiad-core' ); ?></div>
			<div class="aiad-lt__card"><strong><?php echo esc_html( (string) count( $compounds ) ); ?></strong><?php esc_html_e( 'compounds', 'aiad-core' ); ?></div>
			<div class="aiad-lt__card"><strong><?php echo esc_html( count( $res_live ) . ' / ' . count( $res_all ) ); ?></strong><?php esc_html_e( 'learn-more links live', 'aiad-core' ); ?></div>
			<div class="aiad-lt__card"><strong><?php echo esc_html( (string) count( $talk ) ); ?></strong><?php esc_html_e( 'talk questions', 'aiad-core' ); ?></div>
		</div>

		<div class="aiad-lt__exports">
			<strong><?php esc_html_e( 'Export:', 'aiad-core' ); ?></strong>
			<a class="button button-primary" href="<?php echo esc_url( aiad_literacy_export_url( 'json', 'all' ) ); ?>"><?php esc_html_e( 'Everything, as JSON', 'aiad-core' ); ?></a>
			<?php foreach ( array( 'elements' => __( 'Elements, CSV', 'aiad-core' ), 'issues' => __( 'Issues, CSV', 'aiad-core' ), 'compounds' => __( 'Compounds, CSV', 'aiad-core' ), 'resources' => __( 'Links, CSV', 'aiad-core' ) ) as $what => $label ) : ?>
				<a class="button" href="<?php echo esc_url( aiad_literacy_export_url( 'csv', $what ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
			<span class="aiad-lt__muted"><?php esc_html_e( 'CSVs open in Excel or Google Sheets, for reviewing and proofreading.', 'aiad-core' ); ?></span>
		</div>

		<?php if ( $checks ) : ?>
			<h2><?php esc_html_e( 'Checks', 'aiad-core' ); ?></h2>
			<ul class="aiad-lt__checks">
				<?php foreach ( array_merge( $errors, $warnings ) as $c ) : ?>
					<li class="is-<?php echo esc_attr( $c[0] ); ?>"><?php echo esc_html( $c[1] ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<details>
			<summary><strong><?php esc_html_e( 'The files', 'aiad-core' ); ?></strong></summary>
			<table class="widefat striped aiad-lt__files" style="max-width:60rem;margin-top:8px">
				<tbody>
				<?php foreach ( array( 'elements', 'issues', 'compounds', 'resources', 'lists' ) as $kind ) : ?>
					<?php $path = $dir . '/' . $kind . '.json'; ?>
					<tr>
						<td><code>plugins/aiad-core/data/literacy-table/<?php echo esc_html( $kind ); ?>.json</code></td>
						<td><?php echo esc_html( (string) ( aiad_literacy_data( $kind )['about'] ?? '' ) ); ?></td>
						<td class="aiad-lt__muted"><?php echo is_readable( $path ) ? esc_html( wp_date( 'j M Y, H:i', (int) filemtime( $path ) ) ) : esc_html__( 'missing', 'aiad-core' ); ?></td>
					</tr>
				<?php endforeach; ?>
					<tr><td><code>plugins/aiad-core/data/literacy-table/README.md</code></td><td colspan="2"><?php esc_html_e( 'How each file is laid out, and how to add an element, issue or compound.', 'aiad-core' ); ?></td></tr>
				</tbody>
			</table>
		</details>

		<nav class="nav-tab-wrapper" style="margin-top:16px">
			<?php foreach ( $tabs as $key => $label ) : ?>
				<a class="nav-tab<?php echo $tab === $key ? ' nav-tab-active' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'tab', $key, $page_url ) ); ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</nav>

		<?php if ( ! in_array( $tab, array( 'week', 'suggest' ), true ) ) : ?>
			<p><label><?php esc_html_e( 'Filter:', 'aiad-core' ); ?> <input type="search" class="aiad-lt__filter" data-aiad-lt-filter placeholder="<?php esc_attr_e( 'Type a word, symbol, family or theme', 'aiad-core' ); ?>"></label> <span class="aiad-lt__muted" data-aiad-lt-count></span></p>
		<?php endif; ?>

		<?php if ( 'elements' === $tab ) : ?>
			<p>
				<?php
				/* translators: 1: elements with a picture, 2: all elements */
				echo esc_html( sprintf( __( '%1$d of %2$d elements have a picture. To add one, save it as plugins/aiad-core/assets/images/literacy-table/{symbol in lower case}.svg and describe it in the element\'s "picture_alt" in elements.json. The README explains how to draw one.', 'aiad-core' ), count( aiad_literacy_pictures() ), count( $elements ) ) );
				?>
			</p>
			<table class="widefat striped" data-aiad-lt-table>
				<thead><tr><th>#</th><th><?php esc_html_e( 'Element', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Family / theme', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Year', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Definition', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Talk about it', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Issues', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Picture', 'aiad-core' ); ?></th></tr></thead>
				<tbody>
				<?php $pictures = aiad_literacy_pictures(); ?>
				<?php foreach ( $elements as $el ) : ?>
					<tr>
						<td><?php echo esc_html( (string) $el['n'] ); ?></td>
						<td><span class="aiad-lt__sym"><?php echo esc_html( $el['sym'] ); ?></span><br><?php echo esc_html( $el['name'] ); ?><?php echo in_array( $el['sym'], aiad_literacy_safeguarding_syms(), true ) ? '<br><span class="aiad-lt__muted">' . esc_html__( 'Safeguarding note', 'aiad-core' ) . '</span>' : ''; ?></td>
						<td><?php echo esc_html( $families[ $el['family'] ]['name'] ?? $el['family'] ); ?><br><span class="aiad-lt__muted"><?php echo esc_html( ( $themes[ $el['theme'] ] ?? $el['theme'] ) . ' theme' ); ?></span></td>
						<td><?php echo esc_html( ( $el['approx'] ? '~' : '' ) . $el['year'] ); ?></td>
						<td><?php echo esc_html( $el['def'] ); ?></td>
						<td><?php echo isset( $talk[ $el['sym'] ] ) ? esc_html( $talk[ $el['sym'] ] ) : '<span class="aiad-lt__muted">—</span>'; ?></td>
						<td class="aiad-lt__muted"><?php echo esc_html( implode( ', ', array_map( static fn( $k ) => $issues[ $k ]['name'] ?? $k, $el_issues[ $el['sym'] ] ?? array() ) ) ); ?></td>
						<td><?php echo isset( $pictures[ $el['sym'] ] ) ? '<strong>' . esc_html__( 'Yes', 'aiad-core' ) . '</strong><br><span class="aiad-lt__muted">' . esc_html( $pictures[ $el['sym'] ]['alt'] ) . '</span>' : '<span class="aiad-lt__muted">' . esc_html__( 'Not yet', 'aiad-core' ) . '</span>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( 'issues' === $tab ) : ?>
			<table class="widefat striped" data-aiad-lt-table>
				<thead><tr><th><?php esc_html_e( 'Issue', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Theme', 'aiad-core' ); ?></th><th><?php esc_html_e( 'What it means', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Talk about it', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Elements', 'aiad-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $issues as $key => $issue ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $issue['name'] ); ?></strong><br><code class="aiad-lt__muted"><?php echo esc_html( $key ); ?></code></td>
						<td><?php echo esc_html( $themes[ $issue['theme'] ] ?? $issue['theme'] ); ?></td>
						<td><?php echo esc_html( $issue['def'] ); ?></td>
						<td><?php echo esc_html( $issue['talk'] ); ?></td>
						<td><?php echo esc_html( implode( ' ', $issue['syms'] ) ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( 'compounds' === $tab ) : ?>
			<table class="widefat striped" data-aiad-lt-table>
				<thead><tr><th><?php esc_html_e( 'Compound', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Elements', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Level / theme', 'aiad-core' ); ?></th><th><?php esc_html_e( 'What it is', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Example', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Debate', 'aiad-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $compounds as $c ) : ?>
					<tr>
						<td><strong><?php echo esc_html( $c['name'] ); ?></strong><?php echo $c['volatile'] ? '<br><span class="aiad-lt__muted">' . esc_html__( 'Volatile', 'aiad-core' ) . '</span>' : ''; ?></td>
						<td><?php echo esc_html( implode( ' + ', $c['syms'] ) ); ?></td>
						<td><?php echo esc_html( $levels[ $c['level'] ] ?? $c['level'] ); ?><br><span class="aiad-lt__muted"><?php echo esc_html( $themes[ $c['strand'] ] ?? $c['strand'] ); ?></span></td>
						<td><?php echo esc_html( $c['what'] ); ?></td>
						<td><?php echo esc_html( $c['example'] ); ?></td>
						<td><?php echo esc_html( $c['debate'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( 'resources' === $tab ) : ?>
			<table class="widefat striped" data-aiad-lt-table>
				<thead><tr><th><?php esc_html_e( 'Post', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Kind', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Elements', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Issues', 'aiad-core' ); ?></th><th><?php esc_html_e( 'On the page', 'aiad-core' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $res_all as $r ) : ?>
					<?php
					$post = get_page_by_path( (string) $r['slug'], OBJECT, (string) $r['post_type'] );
					$live = $post instanceof WP_Post && 'publish' === $post->post_status;
					?>
					<tr>
						<td><?php echo $live ? '<a href="' . esc_url( get_permalink( $post ) ) . '">' . esc_html( get_the_title( $post ) ) . '</a>' : '<code>' . esc_html( urldecode( $r['slug'] ) ) . '</code>'; ?><br><span class="aiad-lt__muted"><?php echo esc_html( $r['post_type'] ); ?></span></td>
						<td><?php echo esc_html( $r['kind'] ); ?></td>
						<td><?php echo esc_html( implode( ' ', (array) $r['elements'] ) ); ?></td>
						<td><?php echo esc_html( implode( ', ', (array) $r['issues'] ) ); ?></td>
						<td><?php echo $live ? esc_html__( 'Yes', 'aiad-core' ) : '<strong>' . esc_html__( 'No: not published', 'aiad-core' ) . '</strong>'; ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php elseif ( 'suggest' === $tab ) : ?>
			<p>
				<?php esc_html_e( 'What visitors send through "Suggest a new element" on the table. Nothing they send is published: to add a word, write it into elements.json (with a "credit" line if they asked for one, a class or first name only) and deploy. Each suggestion is also emailed to the team and kept under Form Submissions.', 'aiad-core' ); ?>
				<a href="<?php echo esc_url( aiad_literacy_table_url() . '#alpt-suggest-title' ); ?>"><?php esc_html_e( 'See the form', 'aiad-core' ); ?></a>
			</p>
			<?php if ( ! $suggested ) : ?>
				<p><em><?php esc_html_e( 'No suggestions yet.', 'aiad-core' ); ?></em></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead><tr><th><?php esc_html_e( 'Sent', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Term', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Already on the table?', 'aiad-core' ); ?></th><th><?php esc_html_e( 'What they sent', 'aiad-core' ); ?></th></tr></thead>
					<tbody>
					<?php
					$names = array_map( 'strtolower', array_column( $elements, 'name' ) );
					foreach ( $suggested as $sp ) :
						$term = (string) get_post_meta( $sp->ID, '_submission_term', true );
						$have = in_array( strtolower( trim( $term ) ), $names, true ) || isset( $issues[ sanitize_title( $term ) ] );
						?>
						<tr>
							<td><?php echo esc_html( get_the_date( 'j M Y', $sp ) ); ?><br><a href="<?php echo esc_url( (string) get_edit_post_link( $sp->ID ) ); ?>"><?php esc_html_e( 'Open', 'aiad-core' ); ?></a></td>
							<td><strong><?php echo esc_html( $term ); ?></strong></td>
							<td><?php echo $have ? esc_html__( 'Yes', 'aiad-core' ) : esc_html__( 'No', 'aiad-core' ); ?></td>
							<td><?php echo nl2br( esc_html( wp_strip_all_tags( $sp->post_content ) ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		<?php else : ?>
			<p><?php esc_html_e( 'A new word every Monday, worked through in the order of week_rotation in lists.json. Elements with a safeguarding note are skipped.', 'aiad-core' ); ?></p>
			<table class="widefat striped" style="max-width:60rem">
				<thead><tr><th><?php esc_html_e( 'Week of', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Element', 'aiad-core' ); ?></th><th><?php esc_html_e( 'Talk about it', 'aiad-core' ); ?></th></tr></thead>
				<tbody>
				<?php
				$monday = strtotime( 'monday this week', current_time( 'timestamp' ) );
				for ( $w = 0; $w < 12; $w++ ) :
					$week = aiad_literacy_element_of_week( $monday + $w * WEEK_IN_SECONDS );
					if ( null === $week['el'] ) {
						break;
					}
					?>
					<tr>
						<td><?php echo esc_html( wp_date( 'j M Y', $week['monday'] ) ); ?><?php echo 0 === $w ? ' <strong>' . esc_html__( '(this week)', 'aiad-core' ) . '</strong>' : ''; ?></td>
						<td><span class="aiad-lt__sym"><?php echo esc_html( $week['el']['sym'] ); ?></span> <?php echo esc_html( $week['el']['name'] ); ?></td>
						<td><?php echo esc_html( $week['talk'] ); ?></td>
					</tr>
				<?php endfor; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<script>
		( function () {
			var input = document.querySelector( '[data-aiad-lt-filter]' );
			var table = document.querySelector( '[data-aiad-lt-table]' );
			var count = document.querySelector( '[data-aiad-lt-count]' );
			if ( ! input || ! table ) {
				return;
			}
			var rows = Array.prototype.slice.call( table.tBodies[ 0 ].rows );
			var update = function () {
				var q = input.value.trim().toLowerCase(), shown = 0;
				rows.forEach( function ( row ) {
					var on = ! q || row.textContent.toLowerCase().indexOf( q ) !== -1;
					row.hidden = ! on;
					shown += on ? 1 : 0;
				} );
				count.textContent = q ? shown + ' of ' + rows.length : '';
			};
			input.addEventListener( 'input', update );
		}() );
		</script>
	</div>
	<?php
}

if ( ! function_exists( 'aiad_csv_safe_row' ) ) {
	/**
	 * Stops spreadsheet formula injection: a cell that starts with = + - @, a tab or a carriage return gets a
	 * leading apostrophe, so Excel and Google Sheets show it as text instead of running it.
	 *
	 * @param array<int|string, mixed> $row One CSV row.
	 * @return array<int, string>
	 */
	function aiad_csv_safe_row( array $row ): array {
		return array_map(
			static function ( $cell ): string {
				$cell = (string) $cell;
				return ( '' !== $cell && false !== strpos( "=+-@\t\r", $cell[0] ) ) ? "'" . $cell : $cell;
			},
			array_values( $row )
		);
	}
}
