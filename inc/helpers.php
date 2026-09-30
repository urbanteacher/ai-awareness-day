<?php
/**
 * Presentation helpers: press release links, resource card duration pill, explore cards, resource video preview
 * embed HTML, logos and avatars, Customizer defaults, event date sanitising, hero partner marquee, National
 * Conversation links and figures, and the 2027 hero fields.
 *
 * Data helpers (durations, key stages, organisation types, post lookup by title, YouTube IDs, download labels,
 * normalisers, pledge count) live in plugins/aiad-core/modules/helpers-data.php.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Find the first page using the Press Release template.
 *
 * @return WP_Post|null
 */
function aiad_get_press_release_page(): ?WP_Post {
	$pages = get_pages(
		array(
			'meta_key'    => '_wp_page_template',
			'meta_value'  => 'template-press-release.php',
			'number'      => 1,
			'post_status' => array( 'publish', 'draft', 'pending', 'private' ),
		)
	);
	if ( empty( $pages ) ) {
		return null;
	}
	return $pages[0];
}

/**
 * Footer / nav URL for Press Release: prefer published page, then legacy Customizer URL, then direct file attachment.
 *
 * @return string
 */
function aiad_get_press_release_public_url(): string {
	$page = aiad_get_press_release_page();
	if ( $page && 'publish' === $page->post_status ) {
		return (string) get_permalink( $page );
	}
	$legacy = (string) get_theme_mod( 'aiad_press_release_url', '' );
	if ( $legacy !== '' ) {
		return $legacy;
	}
	$fid = absint( get_theme_mod( 'aiad_press_release_file', 0 ) );
	return $fid ? (string) wp_get_attachment_url( $fid ) : '';
}

/**
 * Echo session-length pill markup for resource cards (stacked slot + time when mappable).
 *
 * @param WP_Term|object|string $term_or_slug Duration term or slug.
 */
function aiad_render_resource_card_duration_pill( object|string $term_or_slug ): void {
    $parts = aiad_duration_badge_parts( $term_or_slug );
    if ( $parts ) {
        echo '<span class="resource-card__pill resource-card__pill--type resource-card__pill--duration">';
        echo '<span class="resource-card__pill-slot">' . esc_html( $parts['slot'] ) . '</span>';
        echo '<span class="resource-card__pill-time">' . esc_html( $parts['time'] ) . '</span>';
        echo '</span>';
        return;
    }
    if ( is_object( $term_or_slug ) && isset( $term_or_slug->slug ) ) {
        $full = function_exists( 'aiad_duration_badge_label' ) ? aiad_duration_badge_label( $term_or_slug ) : (string) ( $term_or_slug->name ?? '' );
    } else {
        $full = (string) $term_or_slug;
    }
    echo '<span class="resource-card__pill resource-card__pill--type">' . esc_html( $full ) . '</span>';
}

/**
 * Explore section: session length cards (icon, title, description, badge)
 * Keys match resource_duration slugs.
 *
 * @return array<string, array{title: string, description: string, badge_short: string, icon_bg: string, icon: string, status?: string, status_live?: bool}>
 */
function aiad_explore_session_cards(): array {
    return array(
        '5-min-lesson-starters' => array(
            'title'       => __( 'Lesson Starter', 'ai-awareness-day' ),
            'short_title' => __( 'Starter', 'ai-awareness-day' ),
            'description' => __( 'Quick 5-minute AI discussions to kick off any lesson', 'ai-awareness-day' ),
            'badge_short' => '5 min',
            'icon_bg'     => '#93c5fd',
            'icon'        => 'clock',
            'status'      => __( 'Live', 'ai-awareness-day' ),
            'status_live' => true,
        ),
        '15-20-min-tutor-time' => array(
            'title'       => __( 'Tutor Time', 'ai-awareness-day' ),
            'description' => __( '15 minute group activities for form time', 'ai-awareness-day' ),
            'badge_short' => '15 min',
            'icon_bg'     => '#86efac',
            'icon'        => 'people',
            'status'      => __( 'March 2027', 'ai-awareness-day' ),
            'status_live' => false,
        ),
        '20-min-assemblies' => array(
            'title'       => __( 'Assembly', 'ai-awareness-day' ),
            'description' => __( '20 minute whole-school presentations', 'ai-awareness-day' ),
            'badge_short' => '20 min',
            'icon_bg'     => '#c4b5fd',
            'icon'        => 'presentation',
            'status'      => __( 'April 2027', 'ai-awareness-day' ),
            'status_live' => false,
        ),
        '30-45-min-after-school' => array(
            'title'       => __( 'After School', 'ai-awareness-day' ),
            'description' => __( '30 minute hands-on projects and activities', 'ai-awareness-day' ),
            'badge_short' => '30 min',
            'icon_bg'     => '#fdba74',
            'icon'        => 'book',
            'status'      => __( 'April 2027', 'ai-awareness-day' ),
            'status_live' => false,
        ),
    );
}

/**
 * Build embed HTML for optional resource video preview (oEmbed: YouTube, Vimeo, etc., or direct MP4/WebM/Ogg).
 *
 * @param string $url URL from post meta.
 * @return string HTML or empty if not embeddable.
 */
function aiad_resource_preview_video_html( string $url ): string {
    $url = esc_url_raw( trim( $url ) );
    if ( $url === '' ) {
        return '';
    }

    // Normalise YouTube Shorts URLs → standard watch URL so oEmbed works
    // e.g. https://www.youtube.com/shorts/UNe2gLAFG8g → https://www.youtube.com/watch?v=UNe2gLAFG8g
    $oembed_url = preg_replace(
        '#youtube\.com/shorts/([a-zA-Z0-9_-]+)#',
        'youtube.com/watch?v=$1',
        $url
    );

    $embed = wp_oembed_get( $oembed_url );
    if ( is_string( $embed ) && $embed !== '' ) {
        return $embed;
    }

    $path = (string) wp_parse_url( $url, PHP_URL_PATH );
    $ext  = $path !== '' ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';
    if ( in_array( $ext, array( 'mp4', 'webm', 'ogg' ), true ) ) {
        return sprintf(
            '<video class="resource-preview-video-native" controls playsinline preload="metadata" src="%s"></video>',
            esc_url( $url )
        );
    }

    return '';
}

/**
 * Brand logo for header and on-page display (WordPress Site Identity first).
 *
 * Priority: Site Identity → Logo, Site Icon, legacy Header Logo, legacy Hero Logo.
 *
 * @return int Attachment ID or 0.
 */
function aiad_get_brand_logo_attachment_id(): int {
	if ( has_custom_logo() ) {
		$id = (int) get_theme_mod( 'custom_logo' );
		if ( $id ) {
			return $id;
		}
	}
	$site_icon = (int) get_option( 'site_icon' );
	if ( $site_icon ) {
		return $site_icon;
	}
	$header_logo = absint( get_theme_mod( 'aiad_header_logo', 0 ) );
	if ( $header_logo ) {
		return $header_logo;
	}
	return absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
}

/**
 * Hero section logo: optional override, else brand logo.
 *
 * @return int Attachment ID or 0.
 */
function aiad_get_hero_logo_attachment_id(): int {
	$hero_logo = absint( get_theme_mod( 'aiad_hero_logo', 0 ) );
	if ( $hero_logo ) {
		return $hero_logo;
	}
	return aiad_get_brand_logo_attachment_id();
}

/**
 * Principles “AI literacy” card logo: section override, else brand logo.
 *
 * @return int Attachment ID or 0.
 */
function aiad_get_literacy_logo_attachment_id(): int {
	$literacy_logo = absint( get_theme_mod( 'aiad_ai_literacy_logo', 0 ) );
	if ( $literacy_logo ) {
		return $literacy_logo;
	}
	return aiad_get_brand_logo_attachment_id();
}

/**
 * Schema / OG logo: Site Icon (square PNG) preferred, else brand logo.
 *
 * @return int Attachment ID or 0.
 */
function aiad_get_schema_logo_attachment_id(): int {
	$site_icon = (int) get_option( 'site_icon' );
	if ( $site_icon ) {
		return $site_icon;
	}
	return aiad_get_brand_logo_attachment_id();
}

/**
 * Attachment image URL for a logo ID.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $size          Image size.
 * @return string URL or empty.
 */
function aiad_get_logo_image_url( int $attachment_id, string $size = 'full' ): string {
	if ( ! $attachment_id ) {
		return '';
	}
	$url = wp_get_attachment_image_url( $attachment_id, $size );
	return $url ? (string) $url : '';
}

/**
 * Default avatar when a user has no Gravatar (Site Identity / brand logo).
 *
 * @return string Public image URL or empty.
 */
function aiad_get_default_avatar_url(): string {
	$logo_id = aiad_get_brand_logo_attachment_id();
	if ( ! $logo_id ) {
		return '';
	}
	foreach ( array( 'thumbnail', 'medium', 'full' ) as $size ) {
		$url = aiad_get_logo_image_url( $logo_id, $size );
		if ( '' !== $url ) {
			return $url;
		}
	}
	return '';
}

/**
 * Get default values for customizer settings.
 *
 * @return array<string, mixed> Array of setting names => default values.
 */
function aiad_get_customizer_defaults(): array {
    static $defaults = null;
    if ( null !== $defaults ) {
        return $defaults;
    }
    $defaults = array(
        'aiad_hero_logo'         => '',
        'aiad_hero_slogan'        => __( 'Keep Humans in the Loop', 'ai-awareness-day' ),
        'aiad_hero_title'         => __( 'AI Awareness Day 2027', 'ai-awareness-day' ),
        'aiad_hero_date'          => __( 'AI Awareness Day 2027', 'ai-awareness-day' ),
        'aiad_event_date_ymd'     => '2027-04-29',
        'aiad_show_breadcrumbs'   => false,
        'aiad_hero_subtitle'      => __( 'We are back for 2027. A nationwide day for schools, students, and parents to explore AI together.', 'ai-awareness-day' ),
        'aiad_campaign_title'     => __( 'AI Awareness Day', 'ai-awareness-day' ),
        'aiad_campaign_text'      => __( 'National AI Awareness Day is a nationwide campaign designed to build AI literacy across schools. The model is simple: schools commit to running just one activity.', 'ai-awareness-day' ),
        'aiad_campaign_text_2'    => __( 'Our goal is to create a unified moment where the entire education community comes together to engage positively and critically with AI — preparing the next generation for a world increasingly shaped by intelligent technology.', 'ai-awareness-day' ),
        'aiad_campaign_linkedin_embed_src' => 'https://www.youtube-nocookie.com/embed/ayg1efXE8d0?autoplay=1&mute=1&playsinline=1&rel=0',
        'aiad_youtube_url'        => '',
        'aiad_youtube_title'      => __( 'Watch', 'ai-awareness-day' ),
        'aiad_contact_title'      => __( 'Get Involved', 'ai-awareness-day' ),
        'aiad_contact_desc'       => __( 'Whether you\'re a teacher, school leader, parent, or organisation — we\'d love to hear from you. Join the movement and help shape how the next generation engages with AI.', 'ai-awareness-day' ),
        'aiad_contact_email'      => '',
        'aiad_linkedin'           => 'https://www.linkedin.com/company/110126438/',
        'aiad_instagram'          => '#',
        'aiad_linkedin_post_url'   => '',
    );
    return $defaults;
}

/**
 * Sanitize event date for countdown / schema (Y-m-d).
 *
 * @param mixed $value Raw customizer value.
 * @return string Valid date or default.
 */
function aiad_sanitize_event_date_ymd( $value ): string {
    $value = sanitize_text_field( (string) $value );
    if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
        $parts = array_map( 'intval', explode( '-', $value ) );
        if ( checkdate( $parts[1], $parts[2], $parts[0] ) ) {
            return $value;
        }
    }
    $defaults = aiad_get_customizer_defaults();
    return $defaults['aiad_event_date_ymd'];
}

/**
 * Published partners that have a featured image, for the front-page hero logo strip (below stats).
 *
 * @return array<int, array{id: int, title: string, href: string, img: string}>
 */
function aiad_get_hero_partner_marquee_entries(): array {
    $query = new WP_Query(
        apply_filters(
            'aiad_hero_partner_marquee_query_args',
            array(
                'post_type'      => 'partner',
                'posts_per_page' => 36,
                'orderby'        => array(
                    'menu_order' => 'ASC',
                    'title'      => 'ASC',
                ),
                'post_status'    => 'publish',
                'no_found_rows'  => true,
            )
        )
    );
    $out = array();
    if ( ! $query->have_posts() ) {
        wp_reset_postdata();
        return $out;
    }
    while ( $query->have_posts() ) {
        $query->the_post();
        $id  = (int) get_the_ID();
        $img = get_the_post_thumbnail_url( $id, 'medium' );
        if ( ! $img ) {
            continue;
        }
        $out[] = array(
            'id'    => $id,
            'title' => get_the_title(),
            'href'  => get_permalink( $id ),
            'img'   => $img,
        );
    }
    wp_reset_postdata();
    return apply_filters( 'aiad_hero_partner_marquee_entries', $out );
}

/* ------------------------------------------------------------------ */
/* National AI Conversation hero                                       */
/* ------------------------------------------------------------------ */

/**
 * A link into the National AI Conversation platform, or '' when that plugin is not running.
 *
 * @param string $view register, join, nominate...
 */
function aiad_conversation_url( string $view ): string {
	return class_exists( 'AIADN_Front' ) ? AIADN_Front::url( $view ) : '';
}

/**
 * Totals for the hero, from the platform's own counts: schools, debates judged, students reached.
 * Null until there are enough for the numbers to mean something, so an early site never shows "3 schools".
 * Cached for ten minutes, because the counts are worked out from several tables.
 *
 * @return array{schools:int,debates:int,students:int}|null
 */
function aiad_national_conversation_totals(): ?array {
	if ( ! class_exists( 'AIADN_Stats' ) ) {
		return null;
	}
	$cached = get_transient( 'aiad_nc_totals' );
	if ( is_array( $cached ) ) {
		return $cached['ok'] ? $cached['data'] : null;
	}
	AIADN_Stats::reset();
	$f    = AIADN_Stats::figures( null );
	$min  = defined( 'AIAD_TOTALS_MIN_SCHOOLS' ) ? (int) AIAD_TOTALS_MIN_SCHOOLS : 10;
	$ok   = (int) $f['approved'] >= $min;
	$data = array( 'schools' => (int) $f['approved'], 'debates' => (int) $f['counting'], 'students' => (int) $f['students'] );
	set_transient( 'aiad_nc_totals', array( 'ok' => $ok, 'data' => $data ), 10 * MINUTE_IN_SECONDS );
	return $ok ? $data : null;
}

/**
 * What the hero counts down to: AI Awareness Day itself. The day the conversation opens is stated on the page as
 * text ("Starting January 2027"), not counted, so one clock never stands for two dates.
 *
 * @return array{label:string,ts_ms:int,date:string}|null null once AI Awareness Day has passed
 */
function aiad_national_conversation_countdown(): ?array {
	$defaults = aiad_get_customizer_defaults();
	$event    = (string) get_theme_mod( 'aiad_event_date_ymd', $defaults['aiad_event_date_ymd'] );
	$target   = new DateTimeImmutable( $event . ' 00:00:00', wp_timezone() );
	if ( time() >= $target->getTimestamp() ) {
		return null;
	}
	return array(
		'label' => __( 'AI Awareness Day 2027 is in', 'ai-awareness-day' ),
		'ts_ms' => $target->getTimestamp() * 1000,
		'date'  => $target->format( 'Y-m-d' ),
	);
}

/**
 * Whether the homepage shows the previous hero instead of the 2027 National Conversation one.
 * Set in Appearance > Customise > Front Page Sections > Hero Section > Homepage hero, so the homepage can go back without a theme upload.
 */
function aiad_homepage_hero_is_previous(): bool {
	return 'previous' === get_theme_mod( 'aiad_homepage_hero', 'new' );
}
