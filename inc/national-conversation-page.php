<?php
/**
 * The landing page that explains the National AI Conversation: /national-conversation/.
 *
 * It starts as a route, not a WordPress page, so a fresh install has it without anyone creating content, and it is
 * public and indexable (the platform's own /conversation/ pages are not); page-national-conversation.php renders it.
 * Pages → Theme pages (inc/editable-pages.php) can create an editable page in blocks at the same address
 * (patterns/national-conversation.php, templates/page-national-conversation.html), which the address then shows.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const AIAD_NC_QUERY_VAR = 'aiad_nc_page';

/**
 * Whether this request is the landing page.
 */
function aiad_is_national_conversation_page(): bool {
	if ( '' !== (string) get_query_var( AIAD_NC_QUERY_VAR ) ) {
		return true; // The virtual page (page-national-conversation.php).
	}
	$page = aiad_national_conversation_post();
	return $page && is_page( $page->ID );
}

/**
 * The editable page, once it has been created (Pages → Theme pages, inc/editable-pages.php): a published page at
 * /national-conversation/ built from blocks. While it exists the address shows it; without it, the virtual page.
 */
function aiad_national_conversation_post(): ?WP_Post {
	return aiad_editable_page_post( 'national-conversation' );
}

/**
 * Where the landing page lives.
 */
function aiad_national_conversation_page_url(): string {
	return home_url( '/national-conversation/' );
}

/**
 * The two dates the page is built around: the day the conversation opens and AI Awareness Day itself.
 * They come from the same places as the homepage countdown, so the two can never disagree.
 *
 * @return array{opens:DateTimeImmutable,event:DateTimeImmutable}
 */
function aiad_national_conversation_dates(): array {
	$tz       = wp_timezone();
	$defaults = aiad_get_customizer_defaults();
	$opens    = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	$event    = (string) get_theme_mod( 'aiad_event_date_ymd', $defaults['aiad_event_date_ymd'] );
	return array(
		'opens' => new DateTimeImmutable( $opens . ' 12:00:00', $tz ),
		'event' => new DateTimeImmutable( $event . ' 12:00:00', $tz ),
	);
}

/**
 * Whether schools can use the registration and sign-in portal yet. Until the day the conversation opens the public
 * pages send people to the landing page instead. Define AIAD_PORTAL_LIVE in wp-config.php to open it earlier
 * (true) or hold it back (false).
 */
function aiad_portal_is_live(): bool {
	if ( defined( 'AIAD_PORTAL_LIVE' ) ) {
		return (bool) AIAD_PORTAL_LIVE;
	}
	$opens = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	return time() >= ( new DateTimeImmutable( $opens . ' 00:00:00', wp_timezone() ) )->getTimestamp();
}

add_filter(
	'query_vars',
	static function ( array $vars ): array {
		$vars[] = AIAD_NC_QUERY_VAR;
		return $vars;
	}
);

add_action(
	'init',
	static function (): void {
		add_rewrite_rule( '^national-conversation/?$', 'index.php?' . AIAD_NC_QUERY_VAR . '=1', 'top' );
	},
	4
);

/** Once the editable page exists, the address shows it rather than the virtual page. */
add_filter(
	'request',
	static function ( array $vars ): array {
		if ( empty( $vars[ AIAD_NC_QUERY_VAR ] ) ) {
			return $vars;
		}
		$page = aiad_national_conversation_post();
		return $page ? array( 'page_id' => $page->ID ) : $vars;
	}
);

/** Flush once per theme version, so a deploy never needs a manual permalink reset. */
add_action(
	'init',
	static function (): void {
		if ( AIAD_VERSION === get_option( 'aiad_nc_rewrite_version' ) ) {
			return;
		}
		flush_rewrite_rules( false );
		update_option( 'aiad_nc_rewrite_version', AIAD_VERSION, false );
	},
	99
);

/** The route has no post behind it, so say plainly that it is a real page. */
add_action(
	'template_redirect',
	static function (): void {
		if ( ! aiad_is_national_conversation_page() ) {
			return;
		}
		global $wp_query;
		$wp_query->is_404  = false;
		$wp_query->is_home = false;
		status_header( 200 );
	},
	1
);

add_filter(
	'template_include',
	static function ( string $template ): string {
		if ( ! aiad_is_national_conversation_page() || aiad_national_conversation_post() ) {
			return $template; // The editable page has its own template (templates/page-national-conversation.html).
		}
		$custom = get_template_directory() . '/page-national-conversation.php';
		return is_readable( $custom ) ? $custom : $template;
	},
	20
);

add_filter(
	'pre_get_document_title',
	static function ( string $title ): string {
		return aiad_is_national_conversation_page()
			? __( 'The National AI Conversation 2027 | AI Awareness Day', 'ai-awareness-day' )
			: $title;
	},
	20
);

add_filter(
	'body_class',
	static function ( array $classes ): array {
		if ( aiad_is_national_conversation_page() ) {
			$classes[] = 'national-conversation-page';
		}
		return $classes;
	}
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( is_admin() || ! aiad_is_national_conversation_page() ) {
			return;
		}
		$css = AIAD_DIR . '/assets/css/pages/national-conversation.css';
		if ( is_readable( $css ) ) {
			wp_enqueue_style( 'aiad-national-conversation', AIAD_URI . '/assets/css/pages/national-conversation.css', array( 'aiad-style' ), AIAD_VERSION . '.' . (string) filemtime( $css ) );
		}
	},
	15
);

/**
 * Placeholders the editable page's text can use for what changes on its own: the two dates, the year, the date
 * contact details are deleted, and "Opens" or "Opened".
 *
 * @return array<string, string> Placeholder => HTML.
 */
function aiad_national_conversation_placeholders(): array {
	$dates     = aiad_national_conversation_dates();
	$retention = class_exists( 'AIADN_Privacy' ) ? wp_date( 'j F Y', strtotime( AIADN_Privacy::retention_date() ) ) : '31 August 2027';
	return array(
		'{opens}'       => esc_html( wp_date( 'j F Y', $dates['opens']->getTimestamp() ) ),
		'{event}'       => esc_html( wp_date( 'l j F Y', $dates['event']->getTimestamp() ) ),
		'{event_year}'  => esc_html( wp_date( 'Y', $dates['event']->getTimestamp() ) ),
		'{retention}'   => esc_html( $retention ),
		'{opens_label}' => time() >= $dates['opens']->getTimestamp() ? esc_html__( 'Opened', 'ai-awareness-day' ) : esc_html__( 'Opens', 'ai-awareness-day' ),
	);
}
