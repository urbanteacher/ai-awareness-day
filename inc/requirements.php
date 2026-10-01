<?php
/**
 * The versions this theme runs on, and what a site that is behind them gets.
 *
 * A git deploy puts the theme's files on the server whatever WordPress and PHP the site runs: WordPress's own check
 * ("Requires at least" in style.css) only applies when someone activates a theme in the admin. On a site behind these
 * versions the theme would fail halfway (a missing function, a block that is not registered) and could lock the admin
 * out. So functions.php checks first and, if the site is behind, does nothing else: administrators see why, visitors
 * see a plain "being updated" page (503, so search engines come back later).
 *
 * Written for any PHP that can parse it (no scalar types, no arrow functions), because it runs before anything else.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'AIAD_MIN_WP' ) ) {
	define( 'AIAD_MIN_WP', '7.1' );
}
if ( ! defined( 'AIAD_MIN_PHP' ) ) {
	define( 'AIAD_MIN_PHP', '8.0' );
}

/**
 * Whether WordPress and PHP are new enough for the theme.
 *
 * @return bool
 */
function aiad_requirements_met() {
	global $wp_version;
	// "-alpha" so that a release candidate of the minimum counts as meeting it.
	return version_compare( PHP_VERSION, AIAD_MIN_PHP, '>=' ) && version_compare( $wp_version, AIAD_MIN_WP . '-alpha', '>=' );
}

/**
 * What is wrong, in a sentence.
 *
 * @return string
 */
function aiad_requirements_message() {
	global $wp_version;
	return sprintf(
		'The AI Awareness Day theme needs WordPress %1$s and PHP %2$s or newer. This site runs WordPress %3$s on PHP %4$s, so the theme is switched off until the site is updated.',
		AIAD_MIN_WP,
		AIAD_MIN_PHP,
		$wp_version,
		PHP_VERSION
	);
}

/**
 * Switch the theme off: tell administrators why and show visitors an updating page.
 *
 * @return void
 */
function aiad_requirements_hold() {
	add_action( 'admin_notices', 'aiad_requirements_notice' );
	add_action( 'template_redirect', 'aiad_requirements_maintenance', 0 );
}

/**
 * The admin notice.
 *
 * @return void
 */
function aiad_requirements_notice() {
	echo '<div class="notice notice-error"><p><strong>' . esc_html( aiad_requirements_message() ) . '</strong></p></div>';
}

/**
 * Visitors get a 503 "being updated" page; administrators still see the site, so they can fix it.
 *
 * @return void
 */
function aiad_requirements_maintenance() {
	if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
		return;
	}
	nocache_headers();
	header( 'Retry-After: 3600' );
	wp_die( 'This site is being updated. Please try again shortly.', 'Updating', array( 'response' => 503 ) );
}
