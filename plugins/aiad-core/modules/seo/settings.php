<?php
/**
 * SEO & sharing settings: the site name, event date and homepage description used in titles, share text and schema;
 * social profile URLs (Organization sameAs); and search-engine verification codes.
 *
 * Stored in the aiad_seo option, so they survive a theme change. On first use they are copied once from the
 * Customizer values SEO used to read. From then on the homepage's Customizer wording and these settings are separate.
 * Edited under Settings → SEO & sharing.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The settings: key => [ label, section, type, description ].
 *
 * @return array<string, array{0: string, 1: string, 2: string, 3?: string}>
 */
function aiad_seo_setting_fields(): array {
	return array(
		'site_name'        => array( __( 'Site name', 'aiad-core' ), 'site', 'text', __( 'Used in page titles, share previews and the Organization schema. Leave empty to use the WordPress site title.', 'aiad-core' ) ),
		'event_date'       => array( __( 'Event date', 'aiad-core' ), 'site', 'text', __( 'Shown in the homepage share title and share message, e.g. "Thursday 29th April 2027".', 'aiad-core' ) ),
		'home_description' => array( __( 'Homepage description', 'aiad-core' ), 'site', 'textarea', __( 'The homepage share preview description. Trimmed to about 160 characters. Leave empty to use the WordPress tagline.', 'aiad-core' ) ),
		'social_linkedin'  => array( __( 'LinkedIn URL', 'aiad-core' ), 'social', 'url' ),
		'social_instagram' => array( __( 'Instagram URL', 'aiad-core' ), 'social', 'url' ),
		'social_twitter'   => array( __( 'X / Twitter URL', 'aiad-core' ), 'social', 'url' ),
		'social_facebook'  => array( __( 'Facebook URL', 'aiad-core' ), 'social', 'url' ),
		'social_youtube'   => array( __( 'YouTube URL', 'aiad-core' ), 'social', 'url' ),
		'social_tiktok'    => array( __( 'TikTok URL', 'aiad-core' ), 'social', 'url' ),
		'social_github'    => array( __( 'GitHub URL', 'aiad-core' ), 'social', 'url' ),
		'verify_google'    => array( __( 'Google Search Console', 'aiad-core' ), 'verify', 'text', __( 'In Search Console, choose the "HTML tag" method and paste only the content value (the part between the quotes).', 'aiad-core' ) ),
		'verify_bing'      => array( __( 'Bing Webmaster Tools', 'aiad-core' ), 'verify', 'text', __( 'Paste only the content value.', 'aiad-core' ) ),
		'verify_pinterest' => array( __( 'Pinterest', 'aiad-core' ), 'verify', 'text', __( 'Paste only the content value.', 'aiad-core' ) ),
	);
}

/**
 * The values SEO used to read from the Customizer, resolved the way it resolved them.
 *
 * @return array<string, string>
 */
function aiad_seo_settings_from_customizer(): array {
	$defaults = function_exists( 'aiad_get_customizer_defaults' ) ? aiad_get_customizer_defaults() : array();
	$mod      = static function ( string $key ) use ( $defaults ): string {
		return (string) get_theme_mod( $key, $defaults[ $key ] ?? '' );
	};

	$campaign_text = $mod( 'aiad_campaign_text' );
	$subtitle      = $mod( 'aiad_hero_subtitle' );

	// The Customizer uses '#' for "no link"; SEO skips it, so copy it as empty.
	$url = static function ( string $key ) use ( $mod ): string {
		$value = $mod( $key );
		return '#' === trim( $value ) ? '' : $value;
	};

	return array(
		'site_name'        => $mod( 'aiad_hero_title' ),
		'event_date'       => $mod( 'aiad_hero_date' ),
		// Exactly the description the homepage share preview was built from.
		'home_description' => sprintf( '%s %s', $campaign_text ?: '', $subtitle ?: get_bloginfo( 'description' ) ),
		'social_linkedin'  => $url( 'aiad_linkedin' ),
		'social_instagram' => $url( 'aiad_instagram' ),
		'social_twitter'   => $url( 'aiad_twitter' ),
		'social_facebook'  => $url( 'aiad_facebook' ),
		'social_youtube'   => $url( 'aiad_youtube' ),
		'social_tiktok'    => $url( 'aiad_tiktok' ),
		'social_github'    => $url( 'aiad_github' ),
		'verify_google'    => $mod( 'aiad_verify_google' ),
		'verify_bing'      => $mod( 'aiad_verify_bing' ),
		'verify_pinterest' => $mod( 'aiad_verify_pinterest' ),
	);
}

/**
 * All SEO settings. The first call copies them from the Customizer and saves them.
 *
 * @return array<string, string>
 */
function aiad_seo_settings(): array {
	$saved = get_option( 'aiad_seo', null );
	if ( ! is_array( $saved ) ) {
		$saved = aiad_seo_settings_from_customizer();
		add_option( 'aiad_seo', $saved );
	}
	return array_merge( array_fill_keys( array_keys( aiad_seo_setting_fields() ), '' ), array_map( 'strval', $saved ) );
}

/**
 * One SEO setting, e.g. aiad_seo_setting( 'site_name' ).
 */
function aiad_seo_setting( string $key ): string {
	return aiad_seo_settings()[ $key ] ?? '';
}

/**
 * Sanitise the settings form: plain text, URLs, and the description's line breaks.
 *
 * @param mixed $input Submitted values.
 * @return array<string, string>
 */
function aiad_seo_sanitize_settings( $input ): array {
	$input = is_array( $input ) ? $input : array();
	$clean = array();
	foreach ( aiad_seo_setting_fields() as $key => $field ) {
		$value = isset( $input[ $key ] ) ? wp_unslash( (string) $input[ $key ] ) : '';
		if ( 'url' === $field[2] ) {
			// '#' is the Customizer's "no link" placeholder; SEO skips it, so store it as empty.
			$clean[ $key ] = '#' === trim( $value ) ? '' : esc_url_raw( trim( $value ) );
		} elseif ( 'textarea' === $field[2] ) {
			$clean[ $key ] = sanitize_textarea_field( $value );
		} else {
			$clean[ $key ] = sanitize_text_field( $value );
		}
	}
	return $clean;
}

/**
 * SEO settings sections: slug => [ title, description ].
 *
 * @return array<string, array{0: string, 1: string}>
 */
function aiad_seo_setting_sections(): array {
	return array(
		'site'   => array( __( 'Site and homepage sharing', 'aiad-core' ), __( 'These were copied from the Customizer when first used. The homepage keeps its own wording in the Customizer, so change both if you want them to match.', 'aiad-core' ) ),
		'social' => array( __( 'Social profiles', 'aiad-core' ), __( 'Used in the Organization schema (sameAs), so search engines can link the site to its profiles. The footer links still come from the Customizer.', 'aiad-core' ) ),
		'verify' => array( __( 'Search engine verification', 'aiad-core' ), __( 'Each code adds its verification <meta> tag to every page.', 'aiad-core' ) ),
	);
}

/**
 * Register the setting, its sections and fields (Settings API).
 *
 * @see https://developer.wordpress.org/plugins/settings/custom-settings-page/
 */
function aiad_seo_register_settings(): void {
	register_setting(
		'aiad_seo',
		'aiad_seo',
		array(
			'type'              => 'array',
			'label'             => __( 'SEO & sharing', 'aiad-core' ),
			'description'       => __( 'Site name, homepage sharing, social profiles and search-engine verification.', 'aiad-core' ),
			'sanitize_callback' => 'aiad_seo_sanitize_settings',
			'show_in_rest'      => false,
		)
	);

	foreach ( aiad_seo_setting_sections() as $section => $info ) {
		add_settings_section(
			'aiad_seo_' . $section,
			$info[0],
			static function () use ( $info ): void {
				echo '<p>' . esc_html( $info[1] ) . '</p>';
			},
			'aiad-seo'
		);
	}

	foreach ( aiad_seo_setting_fields() as $key => $field ) {
		add_settings_field(
			'aiad-seo-' . $key,
			$field[0],
			'aiad_seo_render_field',
			'aiad-seo',
			'aiad_seo_' . $field[1],
			array(
				'label_for' => 'aiad-seo-' . $key,
				'key'       => $key,
			)
		);
	}
}
add_action( 'admin_init', 'aiad_seo_register_settings' );

/**
 * Render one SEO field.
 *
 * @param array{label_for: string, key: string} $args Field arguments from add_settings_field().
 */
function aiad_seo_render_field( array $args ): void {
	$key   = $args['key'];
	$field = aiad_seo_setting_fields()[ $key ];
	$value = aiad_seo_setting( $key );
	if ( 'url' === $field[2] && '#' === trim( $value ) ) {
		$value = ''; // Not a URL; the browser would refuse to submit the form. SEO skips it anyway.
	}
	$name  = 'aiad_seo[' . $key . ']';
	if ( 'textarea' === $field[2] ) {
		printf( '<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>', esc_attr( $args['label_for'] ), esc_attr( $name ), esc_textarea( $value ) );
	} else {
		printf( '<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" />', 'url' === $field[2] ? 'url' : 'text', esc_attr( $args['label_for'] ), esc_attr( $name ), esc_attr( $value ) );
	}
	if ( ! empty( $field[3] ) ) {
		echo '<p class="description">' . esc_html( $field[3] ) . '</p>';
	}
}

/**
 * Add the settings page.
 */
function aiad_seo_add_settings_page(): void {
	add_options_page(
		__( 'SEO & sharing', 'aiad-core' ),
		__( 'SEO & sharing', 'aiad-core' ),
		'manage_options',
		'aiad-seo',
		'aiad_seo_render_settings_page'
	);
}
add_action( 'admin_menu', 'aiad_seo_add_settings_page' );

/**
 * Render the settings page. Under the Settings menu WordPress shows the saved/validation notices itself.
 */
function aiad_seo_render_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'aiad_seo' );
			do_settings_sections( 'aiad-seo' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Remove the Customizer fields that only SEO used, now edited on the settings page. LinkedIn and Instagram stay:
 * the footer still links to them.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function aiad_seo_remove_customizer_fields( $wp_customize ): void {
	foreach ( array( 'aiad_twitter', 'aiad_facebook', 'aiad_youtube', 'aiad_tiktok', 'aiad_github', 'aiad_verify_google', 'aiad_verify_bing', 'aiad_verify_pinterest' ) as $id ) {
		$wp_customize->remove_control( $id );
		$wp_customize->remove_setting( $id );
	}
	$wp_customize->remove_section( 'aiad_seo_verify' );
}
add_action( 'customize_register', 'aiad_seo_remove_customizer_fields', 1000 );
