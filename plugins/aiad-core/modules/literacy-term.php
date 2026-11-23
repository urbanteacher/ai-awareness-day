<?php
/**
 * AI Literacy Periodic Table suggestions: the public form route POST aiad/v1/literacy-term, which saves a visitor's
 * suggested element as a private form submission and emails the team.
 *
 * Moved from the theme's inc/literacy-table.php: saving form entries and sending email is plugin work, next to the
 * Get Involved form (contact.php), and the entries use this plugin's form_submission post type. The table itself, its
 * data (data/literacy-table/) and its blocks stay in the theme for now, so the route only registers while the theme's
 * table is there (aiad_literacy_elements() and aiad_literacy_families()); without it there is nothing to suggest for.
 * The theme loads this file from its bundled copy of the plugin when the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_LITERACY_TERM', __FILE__ );

/**
 * POST aiad/v1/literacy-term: a visitor suggests a new element for the table. Like the Get Involved form
 * (plugins/aiad-core/modules/contact.php) it is a public route with no nonce, as cached pages outlive one; a hidden
 * honeypot field, a typing-time check, ten suggestions per network address per hour and validation protect it. Suggestions are saved as private
 * form submissions for the team to review, and emailed to them. Nothing a visitor sends is published.
 *
 * @param WP_REST_Request $request The form's fields.
 * @return WP_REST_Response|WP_Error
 */
function aiad_rest_literacy_term( WP_REST_Request $request ) {
	$error = static function ( string $message, int $status = 400 ): WP_Error {
		return new WP_Error( 'aiad_literacy_term', $message, array( 'status' => $status ) );
	};
	// Single-line fields lose line breaks; only the definition and the reason keep them.
	$field = static function ( string $key, int $max, bool $multiline = false ) use ( $request ): string {
		$value = $request->get_param( $key );
		$value = is_string( $value ) ? trim( $multiline ? sanitize_textarea_field( $value ) : sanitize_text_field( $value ) ) : '';
		return mb_substr( $value, 0, $max );
	};
	$thanks = new WP_REST_Response( array( 'message' => __( 'Thank you for your suggestion.', 'ai-awareness-day' ) ) );

	if ( '' !== $field( 'aiad_website', 200 ) ) {
		// A bot filled the hidden field: say thanks and keep nothing.
		return $thanks;
	}
	// Sent in under three seconds of typing: a script, not a person. (Older cached pages send no time, so 0 passes.)
	$elapsed = (int) $request->get_param( 'elapsed' );
	if ( $elapsed > 0 && $elapsed < 3000 ) {
		return $thanks;
	}

	// Each network address gets ten suggestions an hour, enough for a class sharing one school connection. The
	// address alone is used: the browser name is easy to fake, so it could not stop one person sending hundreds.
	$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$limit_key = 'aiad_alpt_term_' . md5( $ip );
	$count     = (int) get_transient( $limit_key );
	if ( $count >= 10 ) {
		return $error( __( 'Thank you for your ideas! You can suggest more in an hour.', 'ai-awareness-day' ), 429 );
	}
	// Site-wide, emails stop after 40 an hour so the team's inbox can't be flooded, but suggestions are still saved
	// (up to 300 an hour), so one person sending junk can't close the form for everyone else.
	$all_key   = 'aiad_alpt_term_all';
	$all_count = (int) get_transient( $all_key );
	if ( $all_count >= 300 ) {
		return $error( __( 'Thank you for your ideas! You can suggest more in an hour.', 'ai-awareness-day' ), 429 );
	}

	$term       = $field( 'term', 60 );
	$symbol     = preg_replace( '/[^A-Za-z0-9]/', '', $field( 'symbol', 3 ) );
	$family     = $field( 'family', 30 );
	$definition = $field( 'definition', 400, true );
	$why        = $field( 'why', 300, true );
	$name       = $field( 'name', 60 );
	$role       = $field( 'role', 20 );
	$credit     = $field( 'credit', 80 );

	if ( mb_strlen( $term ) < 2 ) {
		return $error( __( 'Please enter the term you would like to add.', 'ai-awareness-day' ) );
	}
	if ( mb_strlen( $definition ) < 10 ) {
		return $error( __( 'Please explain what the term means, in a sentence or two.', 'ai-awareness-day' ) );
	}
	foreach ( aiad_literacy_elements() as $el ) {
		if ( 0 === strcasecmp( $el['name'], $term ) ) {
			/* translators: 1: element name, 2: its symbol */
			return $error( sprintf( __( '%1$s is already on the table (%2$s). Is there another term we are missing?', 'ai-awareness-day' ), $el['name'], $el['sym'] ) );
		}
	}

	$families = aiad_literacy_families();
	$family   = isset( $families[ $family ] ) ? $family : '';
	$roles    = array(
		'student' => __( 'Student', 'ai-awareness-day' ),
		'teacher' => __( 'Teacher', 'ai-awareness-day' ),
		'parent'  => __( 'Parent or carer', 'ai-awareness-day' ),
		'other'   => __( 'Other', 'ai-awareness-day' ),
	);
	$role = isset( $roles[ $role ] ) ? $role : '';

	set_transient( $limit_key, $count + 1, HOUR_IN_SECONDS );
	set_transient( $all_key, $all_count + 1, HOUR_IN_SECONDS );

	$body  = "Suggested element for the AI Literacy Periodic Table\n\n";
	$body .= "Term: {$term}\n";
	$body .= 'Symbol: ' . ( $symbol ? $symbol : '(none given)' ) . "\n";
	$body .= 'Family: ' . ( $family ? $families[ $family ]['name'] : '(not sure)' ) . "\n";
	$body .= "\nWhat it means:\n{$definition}\n";
	if ( $why ) {
		$body .= "\nWhy it should be added:\n{$why}\n";
	}
	$body .= "\nSuggested by: " . ( $name ? $name : '(no name)' ) . ( $role ? ' (' . $roles[ $role ] . ')' : '' ) . "\n";
	$body .= 'Credit line if added: ' . ( $credit ? $credit : '(none)' ) . "\n";
	if ( $credit ) {
		$body .= "To credit them, add this line as the element's \"credit\" in data/literacy-table/elements.json.\n";
	}

	$saved = 0;
	if ( post_type_exists( 'form_submission' ) ) {
		$saved = wp_insert_post(
			array(
				/* translators: %s: the suggested term */
				'post_title'   => sprintf( __( 'Periodic table suggestion: %s', 'ai-awareness-day' ), $term ),
				'post_content' => $body,
				'post_status'  => 'private',
				'post_type'    => 'form_submission',
			)
		);
		if ( $saved && ! is_wp_error( $saved ) ) {
			update_post_meta( $saved, '_submission_involved_as', 'periodic_table_suggestion' );
			update_post_meta( $saved, '_submission_term', $term );
			update_post_meta( $saved, '_submission_message', $definition );
		}
	}

	if ( $all_count >= 40 ) {
		// Saved for the team to review in Tools > Periodic Table > Suggestions, but not emailed.
		return new WP_REST_Response(
			array(
				/* translators: %s: the suggested term */
				'message' => sprintf( __( 'Thank you! We have received your suggestion, %s. The team reviews suggestions and adds the best ones to the table.', 'ai-awareness-day' ), $term ),
			)
		);
	}

	$to = function_exists( 'aiad_campaign_setting' ) && aiad_campaign_setting( 'contact_email' ) ? aiad_campaign_setting( 'contact_email' ) : get_option( 'admin_email' );
	/* translators: %s: the suggested term */
	wp_mail( $to, sprintf( __( '[AI Awareness Day] Periodic table suggestion: %s', 'ai-awareness-day' ), $term ), $body );

	return new WP_REST_Response(
		array(
			/* translators: %s: the suggested term */
			'message' => sprintf( __( 'Thank you! We have received your suggestion, %s. The team reviews suggestions and adds the best ones to the table.', 'ai-awareness-day' ), $term ),
		)
	);
}

add_action(
	'rest_api_init',
	static function (): void {
		if ( ! function_exists( 'aiad_literacy_elements' ) || ! function_exists( 'aiad_literacy_families' ) ) {
			return;
		}
		register_rest_route(
			'aiad/v1',
			'/literacy-term',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => 'aiad_rest_literacy_term',
				'permission_callback' => '__return_true',
			)
		);
	}
);
