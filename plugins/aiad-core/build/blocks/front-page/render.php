<?php
/**
 * Front-end output for aiad/front-page: the theme's template-parts/pages/front-page.php, which holds the page's markup
 * (it was front-page.php). Nothing is printed in the editor's server-side requests, which have no post or archive to show.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
	return;
}

get_template_part( 'template-parts/pages/front-page' );
