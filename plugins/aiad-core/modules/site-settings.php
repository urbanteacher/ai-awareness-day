<?php
/**
 * Site settings module: the site-wide values that were Customizer theme mods (the footer's links, breadcrumbs, and the
 * downloadable files), stored in the aiad_site option so they survive a theme change and can be edited on
 * Settings → AI Awareness Day (includes/settings-screen.php registers the option for REST).
 *
 * The theme still reads them with get_theme_mod(), and the Customizer still writes them, so nothing that reads or
 * writes one has to change yet. This option is the single source of truth, the way aiad_campaign is (modules/campaign.php):
 *   - reading: the theme_mod_{name} filter returns the option (at priority 5, so a Customizer preview still wins);
 *   - writing: pre_set_theme_mod_{name} copies a Customizer save into the option, and saving the settings screen writes
 *     the theme mod back.
 * The first read copies each value from the stored theme mod, or the default the Customizer would have shown, so the
 * site looks the same the moment this loads. The option holds the value in effect, so an empty value means empty (a
 * footer link left blank is shown as pending), unlike aiad_campaign, where empty means "use the default".
 *
 * The footer's LinkedIn and Instagram addresses are the SEO option's social profiles (modules/seo/settings.php): one
 * address for the footer and for search engines. See aiad_site_reconcile_social().
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
define( 'AIAD_CORE_MODULE_SITE_SETTINGS', __FILE__ );

/**
 * The settings: key => [ theme mod it mirrors, type (url, bool, file), default ].
 *
 * A `file` is an attachment ID, 0 for none.
 *
 * @return array<string, array{0: string, 1: string, 2: mixed}>
 */
function aiad_site_fields(): array {
	$fields = array(
		'newsletter_url'             => array( 'aiad_newsletter_url', 'url', 'https://aiawarenessday.beehiiv.com/p/ai-awareness-day-launched' ),
		'asset_pack_url'             => array( 'aiad_asset_pack_url', 'url', '' ),
		'implementation_guide_url'   => array( 'aiad_implementation_guide_url', 'url', '' ),
		'show_breadcrumbs'           => array( 'aiad_show_breadcrumbs', 'bool', false ),
		'header_logo'                => array( 'aiad_header_logo', 'file', 0 ),
		'press_release_file'         => array( 'aiad_press_release_file', 'file', 0 ),
		'asset_logo'                 => array( 'aiad_asset_logo', 'file', 0 ),
		'asset_banner_participating' => array( 'aiad_asset_banner_participating', 'file', 0 ),
		'asset_banner_participated'  => array( 'aiad_asset_banner_participated', 'file', 0 ),
		'hero_logo'                  => array( 'aiad_hero_logo', 'file', 0 ),
		'ai_literacy_logo'           => array( 'aiad_ai_literacy_logo', 'file', 0 ),
		'display_board_image_2'      => array( 'aiad_display_board_image_2', 'file', 0 ),
		'display_board_image_3'      => array( 'aiad_display_board_image_3', 'file', 0 ),
	);
	// The homepage images: a badge for each strand (the Five Core Principles and the By theme links) and one for each
	// session length.
	foreach ( aiad_site_strand_slugs() as $slug ) {
		$fields[ 'badge_' . $slug ] = array( 'aiad_badge_' . $slug, 'file', 0 );
	}
	foreach ( aiad_site_session_slugs() as $slug ) {
		$fields[ 'session_badge_' . $slug ] = array( 'aiad_session_badge_' . $slug, 'file', 0 );
	}
	return $fields;
}

/**
 * The five strands, in the order the homepage shows them.
 *
 * @return string[]
 */
function aiad_site_strand_slugs(): array {
	return array( 'safe', 'smart', 'creative', 'responsible', 'future' );
}

/**
 * The session-length terms that have a badge image on the homepage.
 *
 * @return string[]
 */
function aiad_site_session_slugs(): array {
	return array( '5-min-lesson-starters', '15-20-min-tutor-time', '20-min-assemblies', '30-45-min-after-school' );
}

/**
 * Bring one value to its type: a URL or nothing, a boolean, or the ID of an attachment that exists.
 *
 * @param mixed  $value Raw value.
 * @param string $type  url, bool or file.
 * @return mixed
 */
function aiad_site_clean_value( $value, string $type ) {
	switch ( $type ) {
		case 'bool':
			return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
		case 'file':
			$id = absint( $value );
			return $id && 'attachment' === get_post_type( $id ) ? $id : 0;
		default:
			return esc_url_raw( trim( (string) $value ) );
	}
}

/**
 * The value each field has when nothing is stored: the theme mod if there is one, else the Customizer's default.
 *
 * Reads theme mods through get_theme_mods(), not get_theme_mod(), so the filters below are not re-entered.
 *
 * @return array<string, mixed>
 */
function aiad_site_values_from_theme_mods(): array {
	$mods = get_theme_mods();
	$mods = is_array( $mods ) ? $mods : array();
	$out  = array();
	foreach ( aiad_site_fields() as $key => $field ) {
		$out[ $key ] = array_key_exists( $field[0], $mods ) ? $mods[ $field[0] ] : $field[2];
	}
	return $out;
}

/**
 * All site settings. A key that has no stored value yet (the first call, or a field added since) is copied from the
 * theme mods and saved.
 *
 * @return array<string, mixed>
 */
function aiad_site_settings(): array {
	$saved = get_option( 'aiad_site', null );
	$saved = is_array( $saved ) ? $saved : array();
	$fresh = false;
	$out   = array();
	foreach ( aiad_site_fields() as $key => $field ) {
		if ( ! array_key_exists( $key, $saved ) ) {
			$fresh = true;
			break;
		}
	}
	if ( $fresh ) {
		$from_mods = aiad_site_values_from_theme_mods();
		foreach ( aiad_site_fields() as $key => $field ) {
			$saved[ $key ] = array_key_exists( $key, $saved ) ? $saved[ $key ] : $from_mods[ $key ];
		}
		update_option( 'aiad_site', $saved );
	}
	foreach ( aiad_site_fields() as $key => $field ) {
		$out[ $key ] = aiad_site_clean_value( $saved[ $key ] ?? $field[2], $field[1] );
	}
	return $out;
}

/**
 * One site setting, e.g. aiad_site_setting( 'newsletter_url' ).
 *
 * @param string $key A key of aiad_site_fields().
 * @return mixed
 */
function aiad_site_setting( string $key ) {
	return aiad_site_settings()[ $key ] ?? null;
}

/**
 * Sanitise the setting on every write: REST, the screen and the Customizer mirror alike. A key that was not sent keeps
 * its stored value. Reads the stored option directly, not aiad_site_settings(): that creates the option on its first
 * read, which runs this sanitiser.
 *
 * @param mixed $input Submitted values.
 * @return array<string, mixed>
 */
function aiad_site_sanitize_settings( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$old   = get_option( 'aiad_site', array() );
	$old   = is_array( $old ) ? $old : array();
	$clean = array();
	foreach ( aiad_site_fields() as $key => $field ) {
		$raw           = array_key_exists( $key, $input ) ? $input[ $key ] : ( $old[ $key ] ?? $field[2] );
		$clean[ $key ] = aiad_site_clean_value( $raw, $field[1] );
	}
	return $clean;
}

/*
 * Keep the theme mods and the option in step.
 */
foreach ( aiad_site_fields() as $aiad_site_key => $aiad_site_field ) {
	// Reading: the option's value. A Customizer preview still wins over it (its filter runs after, at 10).
	add_filter(
		"theme_mod_{$aiad_site_field[0]}",
		static function () use ( $aiad_site_key ) {
			return aiad_site_setting( $aiad_site_key );
		},
		5
	);
	// Writing through set_theme_mod() (the Customizer): copy the saved value into the option.
	add_filter(
		"pre_set_theme_mod_{$aiad_site_field[0]}",
		static function ( $value ) use ( $aiad_site_key ) {
			$settings                   = aiad_site_settings();
			$settings[ $aiad_site_key ] = $value;
			update_option( 'aiad_site', $settings );
			return $value;
		}
	);
}
unset( $aiad_site_key, $aiad_site_field );

/**
 * Writing through the settings screen: copy each changed value back to its theme mod, so code that reads the stored
 * theme mods directly agrees.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function aiad_site_sync_theme_mods( $old_value, $value ): void {
	$value = is_array( $value ) ? $value : array();
	$old   = is_array( $old_value ) ? $old_value : array();
	$mods  = get_theme_mods();
	$mods  = is_array( $mods ) ? $mods : array();
	foreach ( aiad_site_fields() as $key => $field ) {
		// Not in the previous value: the first copy from the theme mods, which already agrees with them.
		if ( ! array_key_exists( $key, $value ) || ! array_key_exists( $key, $old ) ) {
			continue;
		}
		$new = $value[ $key ];
		// A value back at its default with no theme mod set needs none: unset already reads as the default.
		if ( ! array_key_exists( $field[0], $mods ) && $new === $field[2] ) {
			continue;
		}
		if ( ( $old[ $key ] ?? null ) === $new && ( $mods[ $field[0] ] ?? null ) === $new ) {
			continue;
		}
		if ( ( $mods[ $field[0] ] ?? null ) !== $new ) {
			set_theme_mod( $field[0], $new ); // Its pre_set filter writes the same value back: a no-op.
		}
	}
}
add_action( 'update_option_aiad_site', 'aiad_site_sync_theme_mods', 10, 2 );

/**
 * A theme mod removed with remove_theme_mod() puts its setting back to the default.
 *
 * WordPress runs no hook of its own for a removal, only the one for the whole theme_mods option being saved, so this
 * compares the mods before and after. Without it a removed override (the 2027 migration clears old badge uploads this
 * way) would be ignored, because the option's value is what the theme mod filter returns.
 *
 * @param string $option    Option name, e.g. theme_mods_ai-awareness-day.
 * @param mixed  $old_value Previous value.
 * @param mixed  $value     New value.
 */
function aiad_site_theme_mod_removed( $option, $old_value, $value ): void {
	if ( 'theme_mods_' . get_option( 'stylesheet' ) !== $option || ! is_array( $old_value ) ) {
		return;
	}
	$value    = is_array( $value ) ? $value : array();
	$settings = null;
	foreach ( aiad_site_fields() as $key => $field ) {
		if ( ! array_key_exists( $field[0], $old_value ) || array_key_exists( $field[0], $value ) ) {
			continue;
		}
		$settings              = $settings ?? aiad_site_settings();
		$settings[ $key ]      = $field[2];
	}
	if ( null !== $settings ) {
		update_option( 'aiad_site', $settings );
	}
}
add_action( 'updated_option', 'aiad_site_theme_mod_removed', 10, 3 );

/*
 * The footer's social links are the SEO option's social profiles.
 */

/**
 * Footer setting => SEO key.
 *
 * @return array<string, string>
 */
function aiad_site_social_mods(): array {
	return array(
		'aiad_linkedin'  => 'social_linkedin',
		'aiad_instagram' => 'social_instagram',
	);
}

/**
 * Make the SEO option the one place for the footer's LinkedIn and Instagram addresses. Runs once.
 *
 * Until now these were two copies of each address: the Customizer's (what the footer shows) and SEO's, copied from it
 * the first time SEO was read and separate since. The footer is what visitors see, so the footer's address wins where
 * the two differ, and SEO's previous value is kept in aiad_social_reconciled so nothing is lost.
 */
function aiad_site_reconcile_social(): void {
	if ( get_option( 'aiad_social_reconciled', null ) !== null || ! function_exists( 'aiad_seo_settings' ) ) {
		return;
	}
	$defaults = function_exists( 'aiad_get_customizer_defaults' ) ? aiad_get_customizer_defaults() : array();
	$mods     = get_theme_mods();
	$mods     = is_array( $mods ) ? $mods : array();
	$seo      = aiad_seo_settings();
	$replaced = array();
	foreach ( aiad_site_social_mods() as $mod => $seo_key ) {
		$footer = array_key_exists( $mod, $mods ) ? (string) $mods[ $mod ] : (string) ( $defaults[ $mod ] ?? '' );
		$footer = '#' === trim( $footer ) ? '' : esc_url_raw( trim( $footer ) );
		if ( $footer !== $seo[ $seo_key ] ) {
			$replaced[ $seo_key ] = $seo[ $seo_key ];
			$seo[ $seo_key ]      = $footer;
		}
	}
	if ( $replaced ) {
		update_option( 'aiad_seo', $seo );
	}
	// The values SEO had that the footer's replaced, with the time. Empty when the two already agreed.
	add_option( 'aiad_social_reconciled', array( 'at' => gmdate( 'c' ), 'previous_seo' => $replaced ), '', false );
}
add_action( 'init', 'aiad_site_reconcile_social', 20 );

foreach ( aiad_site_social_mods() as $aiad_site_mod => $aiad_site_seo_key ) {
	// Reading: the SEO address, or '#' (the footer's "no link") when there is none. Until the reconcile has run, the
	// theme mod's own value passes through.
	add_filter(
		"theme_mod_{$aiad_site_mod}",
		static function ( $value ) use ( $aiad_site_seo_key ) {
			if ( get_option( 'aiad_social_reconciled', null ) === null || ! function_exists( 'aiad_seo_setting' ) ) {
				return $value;
			}
			$address = aiad_seo_setting( $aiad_site_seo_key );
			return '' !== $address ? $address : '#';
		},
		5
	);
	// Writing through the Customizer: into SEO's address.
	add_filter(
		"pre_set_theme_mod_{$aiad_site_mod}",
		static function ( $value ) use ( $aiad_site_seo_key ) {
			if ( get_option( 'aiad_social_reconciled', null ) === null || ! function_exists( 'aiad_seo_settings' ) ) {
				return $value;
			}
			$seo                       = aiad_seo_settings();
			$seo[ $aiad_site_seo_key ] = '#' === trim( (string) $value ) ? '' : esc_url_raw( trim( (string) $value ) );
			update_option( 'aiad_seo', $seo );
			return $value;
		}
	);
}
unset( $aiad_site_mod, $aiad_site_seo_key );

/**
 * Remove the Customizer controls for the settings that are now edited on Settings → AI Awareness Day, so each is
 * edited in one place. The Customizer settings go with them; their values are in the options.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function aiad_site_remove_customizer_fields( $wp_customize ): void {
	$ids = array_merge(
		array_column( aiad_site_fields(), 0 ),
		array_keys( aiad_site_social_mods() )
	);
	foreach ( $ids as $id ) {
		$wp_customize->remove_control( $id );
		$wp_customize->remove_setting( $id );
	}
	foreach ( array( 'aiad_header', 'aiad_footer_resource_links', 'aiad_assets_pack', 'aiad_press_release', 'aiad_social' ) as $section ) {
		$wp_customize->remove_section( $section );
	}
	// Its two sections were the downloads, now on the settings screen.
	$wp_customize->remove_panel( 'aiad_panel_files' );
}
add_action( 'customize_register', 'aiad_site_remove_customizer_fields', 1000 );
