<?php
/**
 * Shared config for certificate / letter HTML tools (REST nonce + root URL).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query args required for static generator pages to call the REST API while logged in.
 *
 * @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/
 *
 * @return array<string, string>
 */
function aiad_generator_embed_query_args(): array {
	return array(
		'rest_nonce' => wp_create_nonce( 'wp_rest' ),
		'rest_root'  => esc_url_raw( rest_url( 'aiad/v1/certificate/' ) ),
		'wp_url'     => esc_url_raw( home_url( '/' ) ),
	);
}

/**
 * Build URL to a theme generator HTML file with embed args.
 *
 * @param string $filename        e.g. certificate-generator.html
 * @param int    $submission_id   Optional submission post ID.
 * @return string
 */
function aiad_generator_html_url( string $filename, int $submission_id = 0 ): string {
	$args = aiad_generator_embed_query_args();
	if ( $submission_id > 0 ) {
		$args['submission'] = (string) $submission_id;
	}
	return add_query_arg( $args, trailingslashit( get_template_directory_uri() ) . ltrim( $filename, '/' ) );
}
