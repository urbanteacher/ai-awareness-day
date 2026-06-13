<?php
/**
 * One-off importer for participants who signed up via a different form than
 * the WP-native one — typically an earlier external CSV (Google Forms,
 * Typeform, etc.) whose rows aren't yet in the form_submission post type.
 *
 * Maps the external CSV's columns onto the WP submission meta layout so the
 * existing certificate / letter / CSV-export tooling treats them identically
 * to in-WP signups.
 *
 * Run inside the docker container (so wp-load.php resolves):
 *
 *     docker exec -i ai-awareness-day-wordpress-1 \
 *         php /var/www/html/wp-content/themes/ai-awareness-day/scripts/import-external-signups.php \
 *         /var/www/html/wp-content/themes/ai-awareness-day/scripts/_external.csv \
 *         teacher
 *
 * Required CSV columns (header row, case-insensitive):
 *
 *     Name         → _submission_first_name
 *     Last name    → _submission_last_name
 *     Your email   → _submission_email
 *     Organisation → _submission_school_name
 *     Message      → _submission_message  (preserved as-is, not used in PDFs)
 *     dateAdded    → post_date            (parsed, falls back to now)
 *
 * Optional second CLI argument:  involved_as role to apply to all rows.
 * Defaults to "teacher". Pass "school_leader", "parent", "organisation" etc.
 *
 * Idempotency: rows whose email already has a form_submission are skipped
 * unless --force is passed as a third argument.
 *
 * @package AI_Awareness_Day
 */

if ( php_sapi_name() !== 'cli' ) {
	fwrite( STDERR, "This script must be run from the command line.\n" );
	exit( 1 );
}

$csv_path = $argv[1] ?? '';
$role     = $argv[2] ?? 'teacher';
$force    = in_array( '--force', $argv, true );

if ( $csv_path === '' || ! is_readable( $csv_path ) ) {
	fwrite( STDERR, "Usage: php import-external-signups.php /path/to/file.csv [role] [--force]\n" );
	fwrite( STDERR, "CSV not readable: {$csv_path}\n" );
	exit( 1 );
}

// Bootstrap WordPress
define( 'WP_USE_THEMES', false );
$_SERVER['HTTP_HOST']   = 'localhost:8888';
$_SERVER['REQUEST_URI'] = '/';

$wp_load = '/var/www/html/wp-load.php';
if ( ! file_exists( $wp_load ) ) {
	// Fallback for local theme path
	$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
}
if ( ! file_exists( $wp_load ) ) {
	fwrite( STDERR, "Could not locate wp-load.php. Run this inside the WP container.\n" );
	exit( 1 );
}
require_once $wp_load;

if ( ! function_exists( 'wp_insert_post' ) ) {
	fwrite( STDERR, "WordPress did not bootstrap.\n" );
	exit( 1 );
}

// Strip UTF-8 BOM if present
$raw = file_get_contents( $csv_path );
$raw = preg_replace( '/^\xEF\xBB\xBF/', '', $raw );

$fh = fopen( 'php://memory', 'r+' );
fwrite( $fh, $raw );
rewind( $fh );

$header = fgetcsv( $fh );
if ( ! $header ) {
	fwrite( STDERR, "CSV is empty or unreadable.\n" );
	exit( 1 );
}
$header = array_map( 'strtolower', array_map( 'trim', $header ) );

/** Find a column index by any of the candidate names (lowercased). */
$col = static function ( array $candidates ) use ( $header ) {
	foreach ( $candidates as $c ) {
		$idx = array_search( strtolower( $c ), $header, true );
		if ( $idx !== false ) {
			return $idx;
		}
	}
	return null;
};

$idx_first = $col( array( 'name', 'first name', 'first_name' ) );
$idx_last  = $col( array( 'last name', 'last_name', 'surname' ) );
$idx_email = $col( array( 'your email', 'email', 'e-mail' ) );
$idx_org   = $col( array( 'organisation', 'organization', 'school', 'school name' ) );
$idx_msg   = $col( array( 'message', 'note', 'comments' ) );
$idx_date  = $col( array( 'dateadded', 'date_added', 'date', 'created', 'created_at' ) );

if ( $idx_first === null || $idx_email === null ) {
	fwrite( STDERR, "CSV must have at least Name and Your Email columns.\n" );
	fwrite( STDERR, "Found columns: " . implode( ', ', $header ) . "\n" );
	exit( 1 );
}

// Pre-load existing emails for dedupe
$existing = array();
$q = new WP_Query(
	array(
		'post_type'              => 'form_submission',
		'post_status'            => array( 'private', 'publish' ),
		'posts_per_page'         => -1,
		'fields'                 => 'ids',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
	)
);
foreach ( $q->posts as $pid ) {
	$e = strtolower( trim( (string) get_post_meta( $pid, '_submission_email', true ) ) );
	if ( $e !== '' ) {
		$existing[ $e ] = $pid;
	}
}

$created  = 0;
$skipped  = 0;
$failures = 0;
$line     = 1;

while ( ( $row = fgetcsv( $fh ) ) !== false ) {
	++$line;
	$first = trim( (string) ( $row[ $idx_first ] ?? '' ) );
	$last  = trim( (string) ( $row[ $idx_last  ] ?? '' ) );
	$email = trim( (string) ( $row[ $idx_email ] ?? '' ) );
	$org   = $idx_org !== null ? trim( (string) ( $row[ $idx_org ] ?? '' ) ) : '';
	$msg   = $idx_msg !== null ? trim( (string) ( $row[ $idx_msg ] ?? '' ) ) : '';
	$date  = $idx_date !== null ? trim( (string) ( $row[ $idx_date ] ?? '' ) ) : '';

	if ( $email === '' || ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		printf( "[line %d] ✗ skipped — missing or invalid email\n", $line );
		++$failures;
		continue;
	}
	if ( $first === '' && $last === '' ) {
		printf( "[line %d] ✗ skipped — no name\n", $line );
		++$failures;
		continue;
	}

	$email_lc = strtolower( $email );
	if ( isset( $existing[ $email_lc ] ) && ! $force ) {
		printf( "[line %d] ↷ skipped — %s already exists (post #%d)\n",
			$line, $email, $existing[ $email_lc ] );
		++$skipped;
		continue;
	}

	$post_date = '';
	if ( $date !== '' ) {
		$ts = strtotime( $date );
		if ( $ts ) {
			$post_date = gmdate( 'Y-m-d H:i:s', $ts );
		}
	}

	$post_data = array(
		'post_type'   => 'form_submission',
		'post_status' => 'private',
		'post_title'  => trim( $first . ' ' . $last ) ?: $email,
	);
	if ( $post_date !== '' ) {
		$post_data['post_date']     = get_date_from_gmt( $post_date );
		$post_data['post_date_gmt'] = $post_date;
	}

	$post_id = wp_insert_post( wp_slash( $post_data ), true );
	if ( is_wp_error( $post_id ) ) {
		printf( "[line %d] ✗ insert failed for %s: %s\n", $line, $email, $post_id->get_error_message() );
		++$failures;
		continue;
	}

	update_post_meta( $post_id, '_submission_first_name', $first );
	update_post_meta( $post_id, '_submission_last_name',  $last );
	update_post_meta( $post_id, '_submission_email',      $email );
	update_post_meta( $post_id, '_submission_school_name', $org );
	update_post_meta( $post_id, '_submission_involved_as', $role );
	update_post_meta( $post_id, '_submission_org_type',    '' );
	if ( $msg !== '' ) {
		update_post_meta( $post_id, '_submission_message', $msg );
	}
	update_post_meta( $post_id, '_submission_source', 'external_csv' );
	update_post_meta( $post_id, '_submission_imported_at', current_time( 'mysql' ) );

	printf( "[line %d] ✓ #%d  %s %s  <%s>\n", $line, $post_id, $first, $last, $email );
	$existing[ $email_lc ] = $post_id;
	++$created;
}

fclose( $fh );

printf( "\n────────────────────────────────────────\n" );
printf( "Created : %d\n", $created );
printf( "Skipped : %d (already existed)\n", $skipped );
printf( "Failed  : %d\n", $failures );
printf( "Role    : %s\n", $role );
printf( "────────────────────────────────────────\n" );
