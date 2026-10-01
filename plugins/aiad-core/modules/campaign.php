<?php
/**
 * Campaign module: the event date and the contact form's recipient, stored in the aiad_campaign option so they
 * survive a theme change. Edited on Settings → AI Awareness Day (includes/settings-screen.php, which registers it for REST).
 *
 * Both used to be Customizer theme mods (aiad_event_date_ymd, aiad_contact_email), and the theme, the homepage editor
 * and the benchmark plugin still read and write them that way. This option stays the single source of truth for all
 * of them:
 *   - reading: the theme_mod_{name} filter returns the option (at priority 5, so a Customizer preview still wins);
 *   - writing: pre_set_theme_mod_{name} copies a Customizer or homepage editor save into the option, and saving the
 *     settings page writes the theme mod back.
 * On first use the option is copied from the stored theme mods. An empty value means "not set", so each reader keeps
 * its own fallback (the site admin email for the contact form, the default date for the countdown).
 *
 * @see https://developer.wordpress.org/reference/hooks/theme_mod_name/
 * @see https://developer.wordpress.org/reference/hooks/pre_set_theme_mod_name/
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_CAMPAIGN', __FILE__ );

/**
 * Settings key => theme mod it mirrors.
 *
 * @return array<string, string>
 */
function aiad_campaign_theme_mods(): array {
	return array(
		'event_date'    => 'aiad_event_date_ymd',
		'contact_email' => 'aiad_contact_email',
	);
}

/**
 * Event date used when none is set (the Customizer's default).
 */
function aiad_campaign_default_event_date(): string {
	if ( function_exists( 'aiad_get_customizer_defaults' ) ) {
		$defaults = aiad_get_customizer_defaults();
		if ( ! empty( $defaults['aiad_event_date_ymd'] ) ) {
			return (string) $defaults['aiad_event_date_ymd'];
		}
	}
	return '2027-04-29';
}

/**
 * Whether a value is a real Y-m-d date.
 */
function aiad_campaign_is_ymd( string $value ): bool {
	if ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
		return false;
	}
	return checkdate( (int) $m[2], (int) $m[3], (int) $m[1] );
}

/**
 * All campaign settings. The first call copies them from the stored theme mods and saves them.
 *
 * Reads theme mods through get_theme_mods(), not get_theme_mod(), so the filters below are not re-entered.
 *
 * @return array{event_date: string, contact_email: string}
 */
function aiad_campaign_settings(): array {
	$saved = get_option( 'aiad_campaign', null );
	if ( ! is_array( $saved ) ) {
		$mods  = get_theme_mods();
		$mods  = is_array( $mods ) ? $mods : array();
		$saved = array();
		foreach ( aiad_campaign_theme_mods() as $key => $mod ) {
			$saved[ $key ] = isset( $mods[ $mod ] ) ? (string) $mods[ $mod ] : '';
		}
		add_option( 'aiad_campaign', $saved );
	}
	return array(
		'event_date'    => (string) ( $saved['event_date'] ?? '' ),
		'contact_email' => (string) ( $saved['contact_email'] ?? '' ),
	);
}

/**
 * One campaign setting: 'event_date' (Y-m-d) or 'contact_email'. Empty when not set.
 */
function aiad_campaign_setting( string $key ): string {
	return aiad_campaign_settings()[ $key ] ?? '';
}

/**
 * The event date (Y-m-d), or the default when none is set.
 */
function aiad_campaign_event_date(): string {
	return aiad_campaign_setting( 'event_date' ) ?: aiad_campaign_default_event_date();
}

/*
 * Keep the theme mods and the option in step.
 */
foreach ( aiad_campaign_theme_mods() as $aiad_campaign_key => $aiad_campaign_mod ) {
	// Reading: the option wins when set; otherwise the caller's own stored value or default passes through.
	add_filter(
		"theme_mod_{$aiad_campaign_mod}",
		static function ( $value ) use ( $aiad_campaign_key ) {
			$setting = aiad_campaign_setting( $aiad_campaign_key );
			return '' !== $setting ? $setting : $value;
		},
		5
	);
	// Writing through set_theme_mod() (Customizer, homepage editor): copy the saved value into the option.
	add_filter(
		"pre_set_theme_mod_{$aiad_campaign_mod}",
		static function ( $value ) use ( $aiad_campaign_key ) {
			$settings                       = aiad_campaign_settings();
			$settings[ $aiad_campaign_key ] = (string) $value;
			update_option( 'aiad_campaign', $settings );
			return $value;
		}
	);
}
unset( $aiad_campaign_key, $aiad_campaign_mod );

/**
 * Writing through the settings page: copy each changed value back to its theme mod, so code that reads the stored
 * theme mods directly agrees. An empty value removes the theme mod, so readers fall back to their defaults.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function aiad_campaign_sync_theme_mods( $old_value, $value ): void {
	$old_value = is_array( $old_value ) ? $old_value : array();
	$value     = is_array( $value ) ? $value : array();
	$mods      = get_theme_mods();
	$mods      = is_array( $mods ) ? $mods : array();
	foreach ( aiad_campaign_theme_mods() as $key => $mod ) {
		$new = (string) ( $value[ $key ] ?? '' );
		if ( $new === (string) ( $old_value[ $key ] ?? '' ) && $new === (string) ( $mods[ $mod ] ?? '' ) ) {
			continue;
		}
		if ( '' === $new ) {
			remove_theme_mod( $mod );
		} elseif ( ( $mods[ $mod ] ?? null ) !== $new ) {
			set_theme_mod( $mod, $new ); // Its pre_set filter writes the same value back: a no-op.
		}
	}
}
add_action( 'update_option_aiad_campaign', 'aiad_campaign_sync_theme_mods', 10, 2 );

/**
 * Validate the setting on every write. Invalid input keeps the previous value. The value arrives unslashed, from
 * REST and from options.php alike.
 *
 * @param mixed $input Submitted values.
 * @return array{event_date: string, contact_email: string}
 */
function aiad_campaign_sanitize_settings( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$old   = aiad_campaign_settings();
	$clean = $old;

	$date = sanitize_text_field( (string) ( $input['event_date'] ?? '' ) );
	if ( '' === $date || aiad_campaign_is_ymd( $date ) ) {
		$clean['event_date'] = $date;
	}
	// A date that is not a real one keeps the previous date. The settings screen checks first and compares what
	// came back, so it tells the editor; add_settings_error() is not loaded for a REST write.

	$raw_email = trim( (string) ( $input['contact_email'] ?? '' ) );
	$email     = sanitize_email( $raw_email );
	if ( '' === $raw_email ) {
		$clean['contact_email'] = '';
	} elseif ( '' !== $email && is_email( $email ) ) {
		$clean['contact_email'] = $email;
	}
	// Anything else keeps the previous address, as for the date.

	return $clean;
}
