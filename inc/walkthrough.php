<?php
/**
 * /walkthrough/ : a public tour of the National AI Conversation platform, for showing clients.
 *
 * It works anywhere, on any device, because nothing on it needs signing in: the signed-in screens are screenshots of
 * the local demo (assets/images/walkthrough/), and only the public pages link to the live site. The interactive demo
 * itself (demo-walkthrough.html and demo-start.php, which signs the browser in as the demo administrator) stays on the
 * presenter's laptop and out of git; on the local copy the tour links to it.
 *
 * The address is matched directly rather than through a rewrite rule, so it works straight after a deploy without
 * waiting for the stored rules to be flushed.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the walkthrough.
 */
function aiad_is_walkthrough(): bool {
	static $is = null;
	if ( null === $is ) {
		$path = strtolower( trim( (string) wp_parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH ), '/' ) );
		$home = strtolower( trim( (string) wp_parse_url( home_url(), PHP_URL_PATH ), '/' ) );
		if ( '' !== $home && 0 === strpos( $path, $home . '/' ) ) {
			$path = substr( $path, strlen( $home ) + 1 );
		}
		$is = ( 'walkthrough' === $path );
	}
	return $is;
}

/**
 * The interactive demo on the presenter's laptop, or '' anywhere else.
 */
function aiad_walkthrough_demo_url(): string {
	$host = strtolower( (string) wp_parse_url( home_url(), PHP_URL_HOST ) );
	if ( in_array( $host, array( 'localhost', '127.0.0.1' ), true ) && is_readable( get_template_directory() . '/demo-walkthrough.html' ) ) {
		return AIAD_URI . '/demo-walkthrough.html';
	}
	return '';
}

/** No post sits behind the address, so say plainly that it is a real page. */
add_action(
	'template_redirect',
	static function (): void {
		if ( ! aiad_is_walkthrough() ) {
			return;
		}
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	},
	1 // Before WordPress guesses at a similar page for an address it does not know.
);

add_filter(
	'template_include',
	static function ( string $template ): string {
		if ( ! aiad_is_walkthrough() ) {
			return $template;
		}
		$custom = get_template_directory() . '/page-walkthrough.php';
		return is_readable( $custom ) ? $custom : $template;
	},
	20
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return aiad_is_walkthrough() ? __( 'Platform walkthrough | AI Awareness Day', 'ai-awareness-day' ) : $title;
	},
	20
);

// Public, but shared by link: every screen shows demo data, so it is kept out of search results.
add_filter(
	'wp_robots',
	static function ( array $robots ): array {
		if ( aiad_is_walkthrough() ) {
			$robots['noindex'] = true;
		}
		return $robots;
	}
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( aiad_is_walkthrough() ) {
			$classes[] = 'walkthrough-page';
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! aiad_is_walkthrough() ) {
			return;
		}
		$css = AIAD_DIR . '/assets/css/pages/walkthrough.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'aiad-walkthrough', AIAD_URI . '/assets/css/pages/walkthrough.css', array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( $css ) );
		}
	},
	15
);
