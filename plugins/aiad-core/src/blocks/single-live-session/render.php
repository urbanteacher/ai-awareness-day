<?php
/**
 * Front-end output for aiad/single-live-session: the theme's template-parts/pages/single-live-session.php, which holds the page's markup
 * (it was single-live_session.php). Nothing is printed in the editor's server-side requests, which have no post or archive to show.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
	return;
}

get_template_part( 'template-parts/pages/single-live-session' );
