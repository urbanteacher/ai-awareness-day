<?php
/**
 * Export a role's questions from the PHP bank into the shared format.
 *
 * One-off, run through WP so the question bank and its translations load:
 *   docker exec <container> php wp-content/plugins/.../tools/export-bank.php <role>
 *
 * Converts the two hand-rolled conditional mechanisms into the schema's
 * condition object: show_unless_answer becomes a `notIn` leaf, and
 * show_for_phases a `profile` leaf.
 *
 * @package AI_Risk_Readiness_Benchmark
 */

require_once '/var/www/html/wp-load.php';

$role = $argv[1] ?? 'public';
$all  = AIRB_Questions::all();
$rows = array_values(
	array_filter(
		$all,
		static fn( $q ) => ( $q['role'] ?? '' ) === $role
	)
);
if ( ! $rows ) {
	fwrite( STDERR, "No questions for role: {$role}\n" );
	exit( 1 );
}

$questions = array();
foreach ( $rows as $row ) {
	$conditions = array();
	foreach ( (array) ( $row['show_unless_answer'] ?? array() ) as $dep => $values ) {
		$conditions[] = array(
			'question' => (string) $dep,
			'notIn'    => array_values( array_map( 'strval', (array) $values ) ),
		);
	}
	if ( ! empty( $row['show_for_phases'] ) ) {
		$conditions[] = array(
			'profile' => 'schoolPhase',
			'in'      => array_values( array_map( 'strval', (array) $row['show_for_phases'] ) ),
		);
	}

	$question = array(
		'id'      => (string) $row['id'],
		'domain'  => (string) ( $row['domain'] ?? '' ),
		'section' => (string) ( $row['section'] ?? '' ),
		'type'    => 'radio' === ( $row['type'] ?? 'radio' ) ? 'single' : (string) $row['type'],
		'text'    => (string) ( $row['text'] ?? '' ),
		'options' => array_map(
			static fn( $o ) => array(
				'value' => (string) ( $o['value'] ?? '' ),
				'label' => (string) ( $o['label'] ?? '' ),
				'score' => (int) ( $o['score'] ?? 0 ),
			),
			(array) ( $row['options'] ?? array() )
		),
	);
	if ( $conditions ) {
		$question['visible'] = 1 === count( $conditions ) ? $conditions[0] : array( 'allOf' => $conditions );
	}
	$questions[] = $question;
}

$bank = array(
	'schema'    => 'aiad.questions/1',
	'bank'      => 'benchmark-' . $role,
	'title'     => ucfirst( $role ) . ' benchmark',
	'locale'    => 'en-GB',
	'roles'     => array( $role ),
	'passMark'  => 70,
	'questions' => $questions,
);

$path = AIRB_PLUGIN_DIR . 'includes/data/banks/benchmark-' . $role . '.json';
file_put_contents( $path, wp_json_encode( $bank, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n" );
printf( "wrote %s — %d questions, %d with conditions\n", basename( $path ), count( $questions ), count( array_filter( $questions, static fn( $q ) => isset( $q['visible'] ) ) ) );
