<?php
/**
 * Email report template.
 *
 * @package AIRB
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo AIRB_Report::render_print_fragment( $role, $results );
