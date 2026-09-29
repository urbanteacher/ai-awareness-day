<?php
/**
 * /walkthrough/ : a short address for the client demo walkthrough.
 *
 * The walkthrough and its one-click sign-in (demo-walkthrough.html, demo-start.php) live only on the presenter's
 * laptop: demo-start.php signs the browser in as the demo WordPress administrator, so neither file is in git or
 * deployed. On the local copy this address opens the walkthrough; anywhere else it stays an ordinary 404, the same
 * rule as the quiet Demo link on the Asset Pack page.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'template_redirect',
	static function (): void {
		$path = strtolower( trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );
		if ( 'walkthrough' !== $path ) {
			return;
		}
		$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
		if ( ! in_array( $host, array( 'localhost', '127.0.0.1' ), true ) || ! is_readable( get_template_directory() . '/demo-walkthrough.html' ) ) {
			return;
		}
		wp_safe_redirect( AIAD_URI . '/demo-walkthrough.html', 302 );
		exit;
	},
	1 // Before WordPress guesses at a similar page for an address it does not know.
);
