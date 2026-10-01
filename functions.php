<?php
/**
 * AI Awareness Day theme functions.
 *
 * Kept small and old-fashioned on purpose: it must parse on any PHP a site might still run, so that a deploy onto a site
 * that is behind (see inc/requirements.php) switches the theme off with a notice rather than failing. The theme itself
 * is inc/bootstrap.php.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/inc/requirements.php';

if ( ! aiad_requirements_met() ) {
	aiad_requirements_hold();
	return;
}

require_once __DIR__ . '/inc/bootstrap.php';
