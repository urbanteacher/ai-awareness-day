<?php
/**
 * Parity check: the PHP reader and the app's TypeScript reader must agree.
 *
 * Two readers that score the same bank differently are worse than one reader,
 * so this prints each reader's answer for a fixed set of scenarios and the
 * caller diffs them.
 *
 * Usage: php tools/bank-parity.php <bank-id>
 *
 * @package AI_Risk_Readiness_Benchmark
 */

require_once '/var/www/html/wp-load.php';

$bank_id = $argv[1] ?? 'benchmark-public';
$bank    = AIRB_Bank::load( $bank_id );
$all     = $bank->questions();

/** Fixed scenarios, chosen to exercise the conditional and the extremes. */
$best  = array();
$worst = array();
foreach ( $all as $q ) {
	$options = (array) $q['options'];
	usort( $options, static fn( $a, $b ) => (int) $a['score'] <=> (int) $b['score'] );
	$best[ (string) $q['id'] ]  = (string) $options[0]['value'];
	$worst[ (string) $q['id'] ] = (string) $options[ count( $options ) - 1 ]['value'];
}

$cases = array(
	'empty'              => array(),
	'all_best'           => $best,
	'all_worst'          => $worst,
	'frequency_rarely'   => array( 'pub_use_frequency' => 'rarely' ),
	'frequency_daily'    => array( 'pub_use_frequency' => 'daily' ),
	'best_but_rarely'    => array_merge( $best, array( 'pub_use_frequency' => 'rarely' ) ),
);

$out = array();
foreach ( $cases as $name => $answers ) {
	$scored          = $bank->score( $answers );
	$out[ $name ] = array(
		'visible' => count( $bank->visible_questions( $answers ) ),
		'score'   => $scored['score'],
		'answered'=> $scored['answered'],
	);
}
echo wp_json_encode( $out, JSON_PRETTY_PRINT ), "\n";
