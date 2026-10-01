<?php
/**
 * The landing page that explains the National AI Conversation: /national-conversation/.
 *
 * An ordinary page of blocks (patterns/national-conversation.php, templates/page-national-conversation.html), public
 * and indexable (the platform's own /conversation/ pages are not). A fresh install has it without anyone creating
 * content: the first time the theme loads, aiad_maybe_convert_theme_pages() (inc/editable-pages.php) creates it.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether this request is the landing page.
 */
function aiad_is_national_conversation_page(): bool {
	$page = aiad_national_conversation_post();
	return $page && is_page( $page->ID );
}

/**
 * The landing page: a published page at /national-conversation/ built from blocks, created the first time the theme
 * loads (aiad_maybe_convert_theme_pages() in inc/editable-pages.php).
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
	$tz    = wp_timezone();
	$opens = defined( 'AIAD_CONVERSATION_OPENS' ) ? AIAD_CONVERSATION_OPENS : '2027-01-01';
	$event = aiad_campaign_event_date(); // aiad-core: the campaign settings, else the standard date.
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
