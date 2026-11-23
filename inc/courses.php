<?php
/**
 * Courses and challenges: clean addresses for the self-paced pages in assets/courses/ and assets/challenges/.
 *
 *   /courses/                         → /courses/ai-under-the-hood/ (the hub)
 *   /courses/{slug}/                  → assets/courses/{slug}/index.html
 *   /challenges/{slug}/               → assets/challenges/{slug}/index.html
 *
 * Each page is a finished HTML file that also runs on its own (a local preview server or the desktop launcher), so it
 * links its files relatively ("../shared/flow.js", "../../css/base/tokens.css"). When WordPress serves it, those links
 * are rewritten to the theme's asset addresses, and the shared scripts and styles get a ?ver= from their modified time,
 * so a deploy never pairs a new page with an old, cached flow.js. The page also gains a canonical link and share tags.
 *
 * assets/previews/ is the experimental area: a page moves to assets/courses/ or assets/challenges/ once approved, and
 * previews/ is blocked from the web by .htaccess. Add a page to aiad_course_pages() to give it an address.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The pages with an address, by area (the folder in assets/ and the first part of the address) and slug.
 *
 * @return array<string, array<string, string>> area => [ slug => slug ]
 */
function aiad_course_pages(): array {
	return array(
		'courses'    => array( 'ai-under-the-hood', 'sand-to-sentence', 'answer-to-action', 'agent-to-team', 'ai-exam' ),
		'challenges' => array( 'christmas-countdown' ),
	);
}

/**
 * Where /courses/ itself goes: the hub. A redirect rather than serving the hub at /courses/, because the hub's
 * relative links ("../sand-to-sentence/") only resolve from inside a page's own folder.
 */
const AIAD_COURSES_HUB = 'ai-under-the-hood';

add_action(
	'init',
	static function (): void {
		add_rewrite_tag( '%aiad_area%', '(courses|challenges)' );
		add_rewrite_tag( '%aiad_page%', '([a-z0-9-]+)' );
		add_rewrite_rule( '^(courses|challenges)/?$', 'index.php?aiad_area=$matches[1]', 'top' );
		add_rewrite_rule( '^(courses|challenges)/([a-z0-9-]+)/?$', 'index.php?aiad_area=$matches[1]&aiad_page=$matches[2]', 'top' );
	}
);

add_action( 'template_redirect', 'aiad_course_serve', 0 );

/**
 * Serve a course or challenge page, or redirect to the right address.
 */
function aiad_course_serve(): void {
	$area = (string) get_query_var( 'aiad_area' );
	if ( '' === $area ) {
		return;
	}
	$pages = aiad_course_pages();
	$slug  = (string) get_query_var( 'aiad_page' );

	if ( '' === $slug ) {
		// /courses/ opens the hub; /challenges/ has no index page yet, so it goes there too.
		wp_safe_redirect( home_url( '/courses/' . AIAD_COURSES_HUB . '/' ), 302 );
		exit;
	}

	$file = AIAD_DIR . '/assets/' . $area . '/' . $slug . '/index.html';
	if ( ! isset( $pages[ $area ] ) || ! in_array( $slug, $pages[ $area ], true ) || ! is_readable( $file ) ) {
		// Not one of ours. The query has only our variables, so WordPress would show the homepage: make it a 404.
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
		return;
	}

	// The page's relative links need the trailing slash ("/courses/x" would resolve "../shared/" to "/shared/").
	$path = (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '', PHP_URL_PATH ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	if ( '/' !== substr( $path, -1 ) ) {
		$query = isset( $_SERVER['QUERY_STRING'] ) && '' !== $_SERVER['QUERY_STRING'] ? '?' . wp_unslash( $_SERVER['QUERY_STRING'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		wp_safe_redirect( home_url( '/' . $area . '/' . $slug . '/' ) . $query, 301 );
		exit;
	}

	$html = aiad_course_page_html( $area, $slug, (string) file_get_contents( $file ) );

	status_header( 200 );
	header( 'Content-Type: text/html; charset=UTF-8' );
	// Short, so a deploy reaches students within minutes; the files it loads are versioned, so they can be cached long.
	header( 'Cache-Control: public, max-age=300' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', (int) filemtime( $file ) ) . ' GMT' );
	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- a theme file, served as written.
	exit;
}

/**
 * A page's HTML as WordPress serves it: theme asset addresses, versioned shared files, canonical link and share tags.
 *
 * @param string $area Area (courses or challenges).
 * @param string $slug Page slug.
 * @param string $html The page's file.
 * @return string
 */
function aiad_course_page_html( string $area, string $slug, string $html ): string {
	$assets = AIAD_URI . '/assets/';
	$dir    = AIAD_DIR . '/assets/';

	$ver = static function ( string $rel ) use ( $dir ): string {
		$f = $dir . $rel;
		return is_readable( $f ) ? '?ver=' . filemtime( $f ) : '';
	};

	// "../shared/x.js" → the area's shared folder, versioned.
	$html = preg_replace_callback(
		'#(["\'(])\.\./shared/([A-Za-z0-9._-]+\.(?:js|css))#',
		static function ( array $m ) use ( $assets, $area, $ver ): string {
			return $m[1] . $assets . $area . '/shared/' . $m[2] . $ver( $area . '/shared/' . $m[2] );
		},
		$html
	);

	// "../../css/base/tokens.css", "../../brand/aiad27/…" → the theme's assets; stylesheets and scripts versioned.
	$html = preg_replace_callback(
		'#(["\'(])\.\./\.\./((?:css|js|brand|fonts|images)/[A-Za-z0-9/._-]*)#',
		static function ( array $m ) use ( $assets, $ver ): string {
			$v = preg_match( '#\.(?:css|js)$#', $m[2] ) ? $ver( $m[2] ) : '';
			return $m[1] . $assets . $m[2] . $v;
		},
		$html
	);

	// The page's own files, named bare ("url('winter-tree.svg')"), when they exist in its folder.
	$html = preg_replace_callback(
		'#(["\'(])([A-Za-z0-9_-]+\.(?:svg|png|jpe?g|webp|gif|mp3|mp4|json))(?=["\')])#',
		static function ( array $m ) use ( $assets, $dir, $area, $slug ): string {
			return is_readable( $dir . $area . '/' . $slug . '/' . $m[2] ) ? $m[1] . $assets . $area . '/' . $slug . '/' . $m[2] : $m[0];
		},
		$html
	);

	return aiad_course_head_tags( $area, $slug, $html );
}

/**
 * Add a canonical link and share tags to the page's <head>, taken from its own <title> and description.
 *
 * @param string $area Area.
 * @param string $slug Slug.
 * @param string $html Page HTML.
 * @return string
 */
function aiad_course_head_tags( string $area, string $slug, string $html ): string {
	$url   = home_url( '/' . $area . '/' . $slug . '/' );
	$title = preg_match( '#<title>(.*?)</title>#s', $html, $m ) ? html_entity_decode( trim( $m[1] ), ENT_QUOTES, 'UTF-8' ) : get_bloginfo( 'name' );
	$desc  = preg_match( '#<meta\s+name="description"\s+content="([^"]*)"#', $html, $m ) ? html_entity_decode( $m[1], ENT_QUOTES, 'UTF-8' ) : '';

	$tags = array( '<link rel="canonical" href="' . esc_url( $url ) . '">' );
	if ( false === stripos( $html, 'property="og:' ) ) {
		$tags[] = '<meta property="og:type" content="website">';
		$tags[] = '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">';
		$tags[] = '<meta property="og:title" content="' . esc_attr( $title ) . '">';
		$tags[] = '<meta property="og:url" content="' . esc_url( $url ) . '">';
		if ( '' !== $desc ) {
			$tags[] = '<meta property="og:description" content="' . esc_attr( $desc ) . '">';
		}
		$image = function_exists( 'aiad_get_social_share_image_data' ) ? aiad_get_social_share_image_data() : array();
		if ( ! empty( $image['image'] ) ) {
			$tags[] = '<meta property="og:image" content="' . esc_url( $image['image'] ) . '">';
			$tags[] = '<meta name="twitter:card" content="summary_large_image">';
		}
	}
	$block = "\n" . implode( "\n", $tags ) . "\n";

	$out = preg_replace( '#</head>#i', $block . '</head>', $html, 1 );
	return is_string( $out ) ? $out : $html;
}
