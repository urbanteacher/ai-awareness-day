<?php
/**
 * Live Timeline: CPT registration, auto-generation hooks, helpers.
 *
 * Creates an "aiad_timeline" CPT that stores timeline entries —
 * both manually written (pinned announcements) and auto-generated
 * from platform activity (new resources, new partners, form submissions).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ──────────────────────────────────────────────
   1. CPT & Meta Registration
   ────────────────────────────────────────────── */

/**
 * Register the timeline CPT.
 * Hooked at priority 10 (runs inside aiad_register_post_types or separately).
 */
function aiad_register_timeline_post_type(): void {
    register_post_type( 'timeline', array(
        'labels' => array(
            'name'               => __( 'Timeline', 'ai-awareness-day' ),
            'singular_name'      => __( 'Timeline Entry', 'ai-awareness-day' ),
            'add_new'            => __( 'Add Update', 'ai-awareness-day' ),
            'add_new_item'       => __( 'Add Timeline Entry', 'ai-awareness-day' ),
            'edit_item'          => __( 'Edit Timeline Entry', 'ai-awareness-day' ),
            'view_item'          => __( 'View Entry', 'ai-awareness-day' ),
            'all_items'          => __( 'All Entries', 'ai-awareness-day' ),
            'search_items'       => __( 'Search Timeline', 'ai-awareness-day' ),
        ),
        'public'       => true,
        'publicly_queryable' => true,
        'has_archive'  => 'timeline',
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-backup',
        'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields' ),
        'show_in_rest' => true,
        'rewrite'      => array( 'slug' => 'timeline', 'with_front' => false ),
    ) );
}
add_action( 'init', 'aiad_register_timeline_post_type' );

/**
 * Register Category taxonomy for timeline entries (shown with the title on the front).
 */
function aiad_register_timeline_category_taxonomy(): void {
    register_taxonomy( 'timeline_category', 'timeline', array(
        'labels'            => array(
            'name'          => __( 'Categories', 'ai-awareness-day' ),
            'singular_name' => __( 'Category', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Category', 'ai-awareness-day' ),
            'edit_item'     => __( 'Edit Category', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
    ) );
}
add_action( 'init', 'aiad_register_timeline_category_taxonomy', 11 );

/**
 * Register post meta for timeline entries.
 */
function aiad_register_timeline_meta(): void {
    $meta_fields = array(
        // 'auto' or 'manual'
        '_aiad_timeline_source' => array( 'type' => 'string', 'default' => 'manual' ),
        // Auto-generated type: 'resource', 'partner', 'submission', 'milestone'
        '_aiad_timeline_auto_type' => array( 'type' => 'string', 'default' => '' ),
        // Related post ID (resource, partner, etc.) — 0 if none
        '_aiad_timeline_related_id' => array( 'type' => 'integer', 'default' => 0 ),
        // Whether this entry is pinned to the top
        '_aiad_timeline_pinned' => array( 'type' => 'boolean', 'default' => false ),
        // Icon key for rendering (e.g. 'resource', 'partner', 'announcement', 'milestone')
        '_aiad_timeline_icon' => array( 'type' => 'string', 'default' => 'announcement' ),
        // Card type: 'default', 'video', 'link', 'linkedin'
        '_aiad_timeline_card_type' => array( 'type' => 'string', 'default' => 'default' ),
        // Optional CTA link URL
        '_aiad_timeline_link_url' => array( 'type' => 'string', 'default' => '' ),
        // Optional CTA link label
        '_aiad_timeline_link_label' => array( 'type' => 'string', 'default' => '' ),
        // Optional video URL (YouTube, Vimeo, LinkedIn video, etc.) — shown as embed in the timeline card
        '_aiad_timeline_video_url' => array( 'type' => 'string', 'default' => '' ),
        // Optional LinkedIn post URL for embedded posts
        '_aiad_timeline_linkedin_url' => array( 'type' => 'string', 'default' => '' ),
        // Like count (incremented via front-end AJAX)
        '_aiad_timeline_like_count' => array( 'type' => 'integer', 'default' => 0 ),
        // Cover when no featured image: '' (auto), 'gradient', 'tech'
        '_aiad_timeline_cover_fallback' => array( 'type' => 'string', 'default' => '' ),
    );

    foreach ( $meta_fields as $key => $args ) {
        register_post_meta( 'timeline', $key, array(
            'type'          => $args['type'],
            'single'        => true,
            'default'       => $args['default'],
            'show_in_rest'  => true,
            'auth_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        ) );
    }
}
add_action( 'init', 'aiad_register_timeline_meta', 15 );

/**
 * Migrate existing posts from the old CPT slug (aiad_timeline) to the current slug (timeline).
 *
 * The CPT was renamed in v1.3.4. Any posts created before that rename are stored with
 * post_type = 'aiad_timeline' in the database. Since that slug is no longer registered,
 * those entries load without block editor support — no slug panel, no excerpt, no template.
 * This one-time migration updates them so they behave like normal 'timeline' posts.
 *
 * @since 1.3.4
 */
function aiad_migrate_timeline_post_type(): void {
    if ( get_option( 'aiad_timeline_cpt_migrated' ) ) {
        return;
    }
    global $wpdb;
    $ids = $wpdb->get_col( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
        'aiad_timeline'
    ) );
    if ( $ids ) {
        $wpdb->update(
            $wpdb->posts,
            array( 'post_type' => 'timeline' ),
            array( 'post_type' => 'aiad_timeline' ),
            array( '%s' ),
            array( '%s' )
        );
        foreach ( $ids as $id ) {
            clean_post_cache( (int) $id );
        }
    }
    update_option( 'aiad_timeline_cpt_migrated', true );
}
add_action( 'admin_init', 'aiad_migrate_timeline_post_type' );

/**
 * One-time flush of rewrite rules after the aiad_timeline → timeline CPT rename.
 * Runs separately from the post migration so it fires even if migration already completed.
 */
function aiad_flush_timeline_rewrite_rules(): void {
    if ( get_option( 'aiad_timeline_rewrite_flushed' ) ) {
        return;
    }
    flush_rewrite_rules( false );
    update_option( 'aiad_timeline_rewrite_flushed', true );
}
add_action( 'admin_init', 'aiad_flush_timeline_rewrite_rules' );

/**
 * Config for editable timeline meta fields. Add a new entry here to get admin UI and save automatically;
 * then use the meta key in timeline-layout renderers if you want it on the front.
 *
 * To add a new feature: 1) Add the meta key to $meta_fields in aiad_register_timeline_meta() above.
 * 2) Add an entry here (type: 'text' or 'url', label, optional placeholder/description).
 * 3) In inc/timeline-layouts.php renderers, get_post_meta( $entry->ID, $meta_key, true ) and output.
 *
 * @return array<string, array{ type: string, label: string, placeholder?: string, description?: string }>
 */
function aiad_timeline_editable_meta_config(): array {
    return array(
        '_aiad_timeline_card_type'   => array(
            'type'        => 'select',
            'label'       => __( 'Card Type', 'ai-awareness-day' ),
            'description' => __( 'Choose what type of content this card displays.', 'ai-awareness-day' ),
            'options'     => array(
                'default'  => __( 'Standard (text + optional image)', 'ai-awareness-day' ),
                'video'    => __( 'YouTube/Vimeo Video', 'ai-awareness-day' ),
                'linkedin' => __( 'LinkedIn Post', 'ai-awareness-day' ),
                'link'     => __( 'External Link Card', 'ai-awareness-day' ),
            ),
        ),
        '_aiad_timeline_video_url'   => array(
            'type'        => 'url',
            'label'       => __( 'Video URL', 'ai-awareness-day' ),
            'placeholder' => 'https://www.youtube.com/watch?v=...',
            'description' => __( 'YouTube or Vimeo link — will be embedded in the card.', 'ai-awareness-day' ),
        ),
        '_aiad_timeline_linkedin_url' => array(
            'type'        => 'url',
            'label'       => __( 'LinkedIn Post URL', 'ai-awareness-day' ),
            'placeholder' => 'https://www.linkedin.com/embed/feed/update/urn:li:ugcPost:...',
            'description' => __( 'Paste the LinkedIn embed URL (from "Embed this post" option on LinkedIn). Format: linkedin.com/embed/feed/update/...', 'ai-awareness-day' ),
        ),
        '_aiad_timeline_link_url'    => array(
            'type'        => 'url',
            'label'       => __( 'Link URL', 'ai-awareness-day' ),
            'placeholder' => 'https://...',
            'description' => __( 'External link for "Learn more" button.', 'ai-awareness-day' ),
        ),
        '_aiad_timeline_link_label' => array(
            'type'        => 'text',
            'label'       => __( 'Link Label', 'ai-awareness-day' ),
            'placeholder' => __( 'View resource →', 'ai-awareness-day' ),
        ),
    );
}

/* ──────────────────────────────────────────────
   2. Admin Meta Box (manual entries)
   ────────────────────────────────────────────── */

function aiad_timeline_meta_box(): void {
    add_meta_box(
        'aiad_timeline_details',
        __( 'Entry Details', 'ai-awareness-day' ),
        'aiad_timeline_meta_box_callback',
        'timeline',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aiad_timeline_meta_box' );

function aiad_timeline_meta_box_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_timeline_meta_save', 'aiad_timeline_meta_nonce' );

    $source       = get_post_meta( $post->ID, '_aiad_timeline_source', true ) ?: 'manual';
    $pinned       = (bool) get_post_meta( $post->ID, '_aiad_timeline_pinned', true );
    $icon         = get_post_meta( $post->ID, '_aiad_timeline_icon', true ) ?: 'announcement';
    $card_type    = get_post_meta( $post->ID, '_aiad_timeline_card_type', true ) ?: 'default';
    $icon_options = aiad_timeline_icon_options();
    $editable     = aiad_timeline_editable_meta_config();
    ?>
    <div class="aiad-resource-details">
    <p class="description" style="margin-top:0;">
        <strong><?php esc_html_e( 'Visual (recommended):', 'ai-awareness-day' ); ?></strong><br>
        <?php esc_html_e( 'Set a Featured Image (school logo, display board, faces, etc.) and/or choose a card type below. The timeline card will show the content prominently.', 'ai-awareness-day' ); ?>
    </p>
    <?php
    foreach ( $editable as $meta_key => $field ) {
        $input_name = str_replace( '_aiad_timeline_', 'aiad_timeline_', $meta_key );
        $value      = get_post_meta( $post->ID, $meta_key, true );
        $input_type = $field['type'];
        
        // Determine which card types this field should show for
        $show_for = array();
        if ( '_aiad_timeline_card_type' === $meta_key ) {
            $show_for = array( 'all' ); // Always show
        } elseif ( '_aiad_timeline_video_url' === $meta_key ) {
            $show_for = array( 'video', 'default' );
        } elseif ( '_aiad_timeline_linkedin_url' === $meta_key ) {
            $show_for = array( 'linkedin' );
        } elseif ( '_aiad_timeline_link_url' === $meta_key || '_aiad_timeline_link_label' === $meta_key ) {
            $show_for = array( 'link', 'default', 'video', 'linkedin' );
        } else {
            $show_for = array( 'all' );
        }
        
        $data_condition = '';
        if ( ! in_array( 'all', $show_for, true ) ) {
            $data_condition = implode( ',', $show_for );
        }
        
        $wrapper_class = 'aiad-timeline-field-wrapper aiad-rd-section';
        if ( '_aiad_timeline_card_type' !== $meta_key && $data_condition ) {
            $wrapper_class .= ' aiad-timeline-field-conditional';
        }
        ?>
    <div class="<?php echo esc_attr( $wrapper_class ); ?>" data-show-for="<?php echo esc_attr( $data_condition ); ?>">
        <label for="<?php echo esc_attr( $input_name ); ?>" class="aiad-rd-label"><?php echo esc_html( $field['label'] ); ?></label>
        <?php if ( 'select' === $input_type ) : ?>
            <select id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" class="widefat aiad-timeline-card-type-select">
                <?php foreach ( $field['options'] as $opt_value => $opt_label ) : ?>
                    <option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $value, $opt_value ); ?>><?php echo esc_html( $opt_label ); ?></option>
                <?php endforeach; ?>
            </select>
        <?php else : ?>
            <input type="<?php echo esc_attr( ( 'url' === $input_type ) ? 'url' : 'text' ); ?>" id="<?php echo esc_attr( $input_name ); ?>" name="<?php echo esc_attr( $input_name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="widefat" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" />
        <?php endif; ?>
        <?php if ( ! empty( $field['description'] ) ) : ?>
            <p class="description"><?php echo esc_html( $field['description'] ); ?></p>
        <?php endif; ?>
    </div>
        <?php
    }
    ?>
    <div class="aiad-rd-section">
        <strong class="aiad-rd-label"><?php esc_html_e( 'Source', 'ai-awareness-day' ); ?></strong>
        <p class="description" style="margin-top:0;"><?php echo esc_html( 'auto' === $source ? __( 'Auto-generated', 'ai-awareness-day' ) : __( 'Manual', 'ai-awareness-day' ) ); ?></p>
    </div>
    <div class="aiad-rd-section">
        <label for="aiad_timeline_pinned">
            <input type="checkbox" id="aiad_timeline_pinned" name="aiad_timeline_pinned" value="1" <?php checked( $pinned ); ?> />
            <?php esc_html_e( 'Pin to top of timeline', 'ai-awareness-day' ); ?>
        </label>
    </div>
    <div class="aiad-rd-section">
        <label for="aiad_timeline_icon" class="aiad-rd-label"><?php esc_html_e( 'Icon', 'ai-awareness-day' ); ?></label>
        <select id="aiad_timeline_icon" name="aiad_timeline_icon" class="widefat">
            <?php foreach ( $icon_options as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $icon, $value ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
    $cover_fallback = get_post_meta( $post->ID, '_aiad_timeline_cover_fallback', true );
    $cover_options  = aiad_timeline_cover_fallback_options();
    ?>
    <div class="aiad-rd-section">
        <label for="aiad_timeline_cover_fallback" class="aiad-rd-label"><?php esc_html_e( 'Cover when no featured image', 'ai-awareness-day' ); ?></label>
        <select id="aiad_timeline_cover_fallback" name="aiad_timeline_cover_fallback" class="widefat">
            <?php foreach ( $cover_options as $value => $label ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $cover_fallback, $value ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
        <p class="description"><?php esc_html_e( 'Used if no Featured Image is set. Upload a Featured Image to use your own photo instead.', 'ai-awareness-day' ); ?></p>
    </div>
    <?php
    if ( function_exists( 'aiad_render_thumbnail_focal_point_fields' ) ) {
        aiad_render_thumbnail_focal_point_fields( $post );
    }
    ?>
    </div>
    <script>
    jQuery(function($) {
        function toggleTimelineFields() {
            var cardType = $('.aiad-timeline-card-type-select').val();
            $('.aiad-timeline-field-conditional').each(function() {
                var showFor = $(this).data('show-for');
                if (showFor) {
                    var types = showFor.toString().split(',');
                    if (types.indexOf(cardType) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                }
            });
        }
        toggleTimelineFields();
        $('.aiad-timeline-card-type-select').on('change', toggleTimelineFields);
    });
    </script>
    <?php
}

function aiad_save_timeline_meta( int $post_id ): void {
    if ( ! isset( $_POST['aiad_timeline_meta_nonce'] ) ||
         ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_timeline_meta_nonce'] ) ), 'aiad_timeline_meta_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    update_post_meta( $post_id, '_aiad_timeline_pinned', ! empty( $_POST['aiad_timeline_pinned'] ) );

    if ( isset( $_POST['aiad_timeline_cover_fallback'] ) ) {
        $cover = sanitize_text_field( wp_unslash( $_POST['aiad_timeline_cover_fallback'] ) );
        $valid = array_keys( aiad_timeline_cover_fallback_options() );
        if ( in_array( $cover, $valid, true ) ) {
            update_post_meta( $post_id, '_aiad_timeline_cover_fallback', $cover );
        }
    }

    if ( isset( $_POST['aiad_timeline_icon'] ) ) {
        $icon = sanitize_text_field( wp_unslash( $_POST['aiad_timeline_icon'] ) );
        $valid_icons = array_keys( aiad_timeline_icon_options() );
        if ( in_array( $icon, $valid_icons, true ) ) {
            update_post_meta( $post_id, '_aiad_timeline_icon', $icon );
        }
    }

    foreach ( aiad_timeline_editable_meta_config() as $meta_key => $field ) {
        $post_key = str_replace( '_aiad_timeline_', 'aiad_timeline_', $meta_key );
        if ( ! isset( $_POST[ $post_key ] ) ) {
            continue;
        }
        $raw = wp_unslash( $_POST[ $post_key ] );
        if ( 'url' === $field['type'] ) {
            update_post_meta( $post_id, $meta_key, esc_url_raw( $raw ) );
        } elseif ( 'select' === $field['type'] && isset( $field['options'] ) ) {
            // Validate select value against allowed options
            $valid_options = array_keys( $field['options'] );
            if ( in_array( $raw, $valid_options, true ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $raw ) );
            }
        } else {
            update_post_meta( $post_id, $meta_key, sanitize_text_field( $raw ) );
        }
    }

    // Manual entries always have source = 'manual'
    $current_source = get_post_meta( $post_id, '_aiad_timeline_source', true );
    if ( empty( $current_source ) ) {
        update_post_meta( $post_id, '_aiad_timeline_source', 'manual' );
    }

    if ( function_exists( 'aiad_save_thumbnail_focal_point_from_post' ) ) {
        aiad_save_thumbnail_focal_point_from_post( $post_id );
    }
}
add_action( 'save_post_timeline', 'aiad_save_timeline_meta' );

/* ──────────────────────────────────────────────
   3. Icon Options & SVG Renderer
   ────────────────────────────────────────────── */

/**
 * Available icon types for timeline entries.
 *
 * @return array<string, string>
 */
function aiad_timeline_icon_options(): array {
    return array(
        'announcement' => __( 'Announcement', 'ai-awareness-day' ),
        'resource'     => __( 'New Resource', 'ai-awareness-day' ),
        'partner'      => __( 'New Partner', 'ai-awareness-day' ),
        'signup'       => __( 'Sign-up / Submission', 'ai-awareness-day' ),
        'milestone'    => __( 'News', 'ai-awareness-day' ),
        'media'        => __( 'CPD', 'ai-awareness-day' ),
        'event'        => __( 'Event', 'ai-awareness-day' ),
    );
}

/**
 * Cover fallback options when no featured image is set.
 *
 * @return array<string, string>
 */
function aiad_timeline_cover_fallback_options(): array {
    return array(
        ''       => __( 'Auto (category gradient or tech)', 'ai-awareness-day' ),
        'gradient' => __( 'Category gradient', 'ai-awareness-day' ),
        'tech'     => __( 'Tech pattern', 'ai-awareness-day' ),
    );
}

/**
 * Resolve cover fallback mode for an icon + stored preference.
 *
 * @param string $icon     Timeline icon key.
 * @param string $fallback Stored meta (empty = auto).
 * @return string 'gradient' or 'tech'
 */
function aiad_timeline_resolve_cover_fallback( string $icon, string $fallback = '' ): string {
    if ( 'tech' === $fallback ) {
        return 'tech';
    }
    if ( 'gradient' === $fallback ) {
        return 'gradient';
    }
    if ( in_array( $icon, array( 'milestone', 'signup', 'resource' ), true ) ) {
        return 'tech';
    }
    return 'gradient';
}

/**
 * Inner gradient/tech block (no wrapper figure).
 *
 * @param string $icon     Icon key for colour class.
 * @param string $fallback Meta value.
 * @param string $title    Entry title (decorative).
 * @return string HTML
 */
function aiad_timeline_cover_fallback_inner_html( string $icon, string $fallback, string $title, bool $show_icon = false ): string {
    $mode  = aiad_timeline_resolve_cover_fallback( $icon, $fallback );
    $class = 'timeline-entry__cover-fallback timeline-entry__cover-fallback--minimal timeline-entry__cover-fallback--' . sanitize_html_class( $icon );
    if ( 'tech' === $mode ) {
        $class .= ' timeline-entry__cover-fallback--tech';
    }

    $icon_html = '';
    if ( $show_icon ) {
        $icon_html = sprintf(
            '<span class="timeline-entry__cover-fallback-icon" aria-hidden="true">%s</span>',
            aiad_timeline_icon_svg( $icon ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }

    return sprintf(
        '<div class="%1$s" role="img" aria-label="%2$s">%3$s</div>',
        esc_attr( $class ),
        esc_attr( $title ),
        $icon_html // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    );
}

/**
 * Render gradient/tech cover when no featured image (legacy figure wrapper).
 *
 * @param string $icon     Icon key for colour class.
 * @param string $fallback Meta value.
 * @param string $title    Entry title (decorative).
 * @return string HTML
 */
function aiad_render_timeline_cover_fallback( string $icon, string $fallback, string $title ): string {
    return '<figure class="timeline-entry__image timeline-entry__image--fallback">'
        . aiad_timeline_cover_fallback_inner_html( $icon, $fallback, $title )
        . '</figure>';
}


/**
 * Render an inline SVG icon for a timeline entry.
 *
 * @param string $icon Icon key.
 * @return string SVG markup.
 */
function aiad_timeline_icon_svg( string $icon ): string {
    $attr = 'width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';

    $icons = array(
        'announcement' => '<svg ' . $attr . '><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        'resource'     => '<svg ' . $attr . '><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        'partner'      => '<svg ' . $attr . '><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'signup'       => '<svg ' . $attr . '><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>',
        'milestone'    => '<svg ' . $attr . '><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
        'media'        => '<svg ' . $attr . '><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>',
        'event'        => '<svg ' . $attr . '><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    );

    return $icons[ $icon ] ?? $icons['announcement'];
}

/**
 * SVG icon for the Like button (heart outline).
 *
 * @return string SVG markup.
 */
function aiad_timeline_like_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>';
}

/**
 * SVG icon for the Share button.
 *
 * @return string SVG markup.
 */
function aiad_timeline_share_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';
}

/**
 * SVG icon for the Learn more link (arrow / external link).
 *
 * @return string SVG markup.
 */
function aiad_timeline_link_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
}

/**
 * SVG icon for the "view post" button (arrow right).
 *
 * @return string SVG markup.
 */
function aiad_timeline_view_post_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>';
}

/**
 * SVG icon for print button.
 *
 * @return string SVG markup.
 */
function aiad_print_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>';
}

/**
 * SVG icon for back button (arrow left).
 *
 * @return string SVG markup.
 */
function aiad_back_icon_svg(): string {
    return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>';
}

/**
 * Play button SVG for lite YouTube facade.
 *
 * @return string SVG markup.
 */
function aiad_timeline_play_icon_svg(): string {
    return '<svg width="68" height="48" viewBox="0 0 68 48" aria-hidden="true"><path class="timeline-entry__video-facade-play-bg" d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26z"/><path d="M45 24L27 14v20" fill="#fff"/></svg>';
}

/**
 * Render YouTube lite facade HTML (thumbnail + play button overlay).
 * Uses aiad_youtube_video_id() to validate the video ID.
 *
 * @param string $video_id YouTube video ID.
 * @param string $title    Video title for accessibility.
 * @return string HTML markup for the facade.
 */
function aiad_render_youtube_facade( string $video_id, string $title = '' ): string {
    if ( empty( $video_id ) ) {
        return '';
    }

    // Validate video ID using helper function if available
    if ( function_exists( 'aiad_youtube_video_id' ) ) {
        // If a URL was passed, extract the ID
        $extracted_id = aiad_youtube_video_id( $video_id );
        if ( ! empty( $extracted_id ) ) {
            $video_id = $extracted_id;
        } elseif ( ! preg_match( '/^[a-zA-Z0-9_-]{11}$/', $video_id ) ) {
            // Invalid format
            return '';
        }
    } elseif ( ! preg_match( '/^[a-zA-Z0-9_-]{11}$/', $video_id ) ) {
        // Fallback validation if helper doesn't exist
        return '';
    }

    $yt_thumb   = 'https://img.youtube.com/vi/' . $video_id . '/hqdefault.jpg';
    $yt_title   = ! empty( $title ) ? $title : __( 'YouTube video', 'ai-awareness-day' );
    $aria_label = sprintf( __( 'Play video: %s', 'ai-awareness-day' ), $yt_title );

    ob_start();
    ?>
    <div class="timeline-entry__video-facade timeline-lite-yt" data-video-id="<?php echo esc_attr( $video_id ); ?>" data-title="<?php echo esc_attr( $yt_title ); ?>" role="button" tabindex="0" aria-label="<?php echo esc_attr( $aria_label ); ?>">
        <span class="timeline-entry__video-facade-thumb" style="background-image: url(<?php echo esc_url( $yt_thumb ); ?>);"></span>
        <span class="timeline-entry__video-facade-play" aria-hidden="true"><?php echo aiad_timeline_play_icon_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Featured badge label for a timeline entry (theme-dependent: Announcement, Update, category, etc.).
 *
 * @param WP_Post $entry Timeline post.
 * @param bool    $pinned Whether the entry is pinned.
 * @param string  $icon   Icon key (e.g. announcement, media, resource).
 * @return string Badge text for the card header.
 */
function aiad_timeline_featured_badge_label( WP_Post $entry, bool $pinned, string $icon ): string {
    if ( $pinned ) {
        return __( 'Pinned', 'ai-awareness-day' );
    }
    $terms = get_the_terms( $entry->ID, 'timeline_category' );
    if ( $terms && ! is_wp_error( $terms ) ) {
        $name = $terms[0]->name;
        return $name;
    }
    $labels = array(
        'announcement' => __( 'Announcement', 'ai-awareness-day' ),
        'media'       => __( 'CPD', 'ai-awareness-day' ),
        'resource'    => __( 'Resource', 'ai-awareness-day' ),
        'partner'     => __( 'Partner', 'ai-awareness-day' ),
        'event'       => __( 'Event', 'ai-awareness-day' ),
        'milestone'   => __( 'News', 'ai-awareness-day' ),
        'signup'      => __( 'Sign-up', 'ai-awareness-day' ),
    );
    return $labels[ $icon ] ?? $labels['announcement'];
}

require_once __DIR__ . '/timeline-layouts.php';

/* ──────────────────────────────────────────────
   4. Auto-Generation Hooks
   ────────────────────────────────────────────── */

/**
 * Create an auto-generated timeline entry.
 * Prevents duplicates by checking for existing entries with the same related_id and auto_type.
 *
 * @param array $args {
 *     @type string $title      Entry title.
 *     @type string $content    Entry body (optional).
 *     @type string $auto_type  'resource', 'partner', 'submission', 'milestone'.
 *     @type string $icon       Icon key.
 *     @type int    $related_id Related post ID (0 if none).
 *     @type string $link_url   Optional CTA URL.
 *     @type string $link_label Optional CTA label.
 * }
 * @return int|false Post ID on success, false on failure or duplicate.
 */
function aiad_create_timeline_entry( array $args ) {
    $defaults = array(
        'title'           => '',
        'content'         => '',
        'auto_type'      => '',
        'icon'           => 'announcement',
        'related_id'     => 0,
        'link_url'       => '',
        'link_label'     => '',
        'countdown_weeks' => 0,
    );
    $args = wp_parse_args( $args, $defaults );

    if ( empty( $args['title'] ) ) {
        return false;
    }

    // Prevent duplicates: check if an auto entry with this related_id + auto_type already exists
    if ( $args['related_id'] > 0 && $args['auto_type'] !== '' ) {
        $existing = get_posts( array(
            'post_type'      => 'timeline',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'meta_query'     => array(
                'relation' => 'AND',
                array( 'key' => '_aiad_timeline_related_id', 'value' => $args['related_id'], 'compare' => '=' ),
                array( 'key' => '_aiad_timeline_auto_type', 'value' => $args['auto_type'], 'compare' => '=' ),
            ),
        ) );
        if ( ! empty( $existing ) ) {
            return false;
        }
    }

    $post_id = wp_insert_post( array(
        'post_type'    => 'timeline',
        'post_title'   => sanitize_text_field( $args['title'] ),
        'post_content' => wp_kses_post( $args['content'] ),
        'post_status'  => 'publish',
    ) );

    if ( ! $post_id || is_wp_error( $post_id ) ) {
        return false;
    }

    update_post_meta( $post_id, '_aiad_timeline_source', 'auto' );
    update_post_meta( $post_id, '_aiad_timeline_auto_type', sanitize_text_field( $args['auto_type'] ) );
    update_post_meta( $post_id, '_aiad_timeline_related_id', absint( $args['related_id'] ) );
    update_post_meta( $post_id, '_aiad_timeline_icon', sanitize_text_field( $args['icon'] ) );
    update_post_meta( $post_id, '_aiad_timeline_pinned', false );

    if ( $args['link_url'] ) {
        update_post_meta( $post_id, '_aiad_timeline_link_url', esc_url_raw( $args['link_url'] ) );
    }
    if ( $args['link_label'] ) {
        update_post_meta( $post_id, '_aiad_timeline_link_label', sanitize_text_field( $args['link_label'] ) );
    }
    if ( ! empty( $args['countdown_weeks'] ) ) {
        update_post_meta( $post_id, '_aiad_timeline_countdown_weeks', absint( $args['countdown_weeks'] ) );
    }

    return $post_id;
}

/**
 * Find an auto timeline entry linked to another post.
 *
 * @param int    $related_id Related post ID.
 * @param string $auto_type  Auto type key (e.g. live_session).
 * @return int Timeline post ID or 0.
 */
function aiad_timeline_get_entry_by_related( int $related_id, string $auto_type ): int {
    if ( $related_id <= 0 || $auto_type === '' ) {
        return 0;
    }

    $existing = get_posts(
        array(
            'post_type'      => 'timeline',
            'post_status'    => array( 'publish', 'draft', 'pending', 'future' ),
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_aiad_timeline_related_id',
                    'value'   => $related_id,
                    'compare' => '=',
                ),
                array(
                    'key'     => '_aiad_timeline_auto_type',
                    'value'   => $auto_type,
                    'compare' => '=',
                ),
            ),
        )
    );

    return ! empty( $existing ) ? (int) $existing[0] : 0;
}

/**
 * Copy partner logo onto a timeline entry when it has no featured image.
 *
 * @param int $timeline_id Timeline post ID.
 * @param int $partner_id  Partner post ID.
 */
function aiad_timeline_set_featured_image_from_partner( int $timeline_id, int $partner_id ): void {
    if ( $timeline_id <= 0 || $partner_id <= 0 || has_post_thumbnail( $timeline_id ) ) {
        return;
    }

    $thumb_id = get_post_thumbnail_id( $partner_id );
    if ( $thumb_id ) {
        set_post_thumbnail( $timeline_id, $thumb_id );
        update_post_meta( $timeline_id, '_aiad_timeline_cover_fit', 'contain' );
    }
}

/**
 * Copy live session featured image (or partner logo fallback) onto a timeline entry.
 *
 * @param int $timeline_id Timeline post ID.
 * @param int $session_id  live_session post ID.
 * @param int $partner_id  Partner post ID (optional).
 */
function aiad_timeline_set_featured_image_from_session( int $timeline_id, int $session_id, int $partner_id = 0 ): void {
    if ( $timeline_id <= 0 || $session_id <= 0 || has_post_thumbnail( $timeline_id ) ) {
        return;
    }

    $session_thumb = (int) get_post_thumbnail_id( $session_id );
    if ( $session_thumb > 0 ) {
        set_post_thumbnail( $timeline_id, $session_thumb );
        delete_post_meta( $timeline_id, '_aiad_timeline_cover_fit' );
        return;
    }

    aiad_timeline_set_featured_image_from_partner( $timeline_id, $partner_id );
}

/**
 * Create or update a timeline entry for a live_session (schedule) post.
 *
 * @param int $session_id live_session post ID.
 */
function aiad_timeline_sync_live_session_entry( int $session_id ): void {
    $session = get_post( $session_id );
    if ( ! $session || $session->post_type !== 'live_session' ) {
        return;
    }

    $timeline_id = aiad_timeline_get_entry_by_related( $session_id, 'live_session' );

    if ( $session->post_status !== 'publish' ) {
        if ( $timeline_id ) {
            wp_update_post(
                array(
                    'ID'          => $timeline_id,
                    'post_status' => 'draft',
                )
            );
        }
        return;
    }

    $start       = (string) get_post_meta( $session_id, '_session_start_time', true );
    $end         = (string) get_post_meta( $session_id, '_session_end_time', true );
    $format      = (string) get_post_meta( $session_id, '_session_format', true );
    $reg_url     = (string) get_post_meta( $session_id, '_session_registration_url', true );
    $partner_id  = (int) get_post_meta( $session_id, '_session_partner_id', true );
    $time_range  = function_exists( 'aiad_format_session_time_range' )
        ? aiad_format_session_time_range( $start, $end )
        : '';
    $content     = trim( (string) $session->post_content );
    $intro_parts = array();

    if ( $time_range ) {
        $intro_parts[] = sprintf(
            /* translators: %s: time range e.g. 10:00 – 11:00 */
            __( 'Live at %s (UK time).', 'ai-awareness-day' ),
            $time_range
        );
    }
    if ( $format ) {
        $intro_parts[] = $format;
    }
    if ( ! empty( $intro_parts ) ) {
        $intro = '<p>' . esc_html( implode( ' ', $intro_parts ) ) . '</p>';
        $content = $content ? $intro . "\n\n" . $content : $intro;
    }

    $link_url   = $reg_url ? $reg_url : ( get_permalink( $session_id ) ?: '' );
    $link_label = $reg_url
        ? __( 'Register →', 'ai-awareness-day' )
        : __( 'View session →', 'ai-awareness-day' );

    if ( $timeline_id ) {
        wp_update_post(
            array(
                'ID'           => $timeline_id,
                'post_title'   => $session->post_title,
                'post_content' => $content,
                'post_status'  => 'publish',
            )
        );
        update_post_meta( $timeline_id, '_aiad_timeline_icon', 'event' );
        update_post_meta( $timeline_id, '_aiad_timeline_source', 'auto' );
        if ( $link_url ) {
            update_post_meta( $timeline_id, '_aiad_timeline_link_url', esc_url_raw( $link_url ) );
            update_post_meta( $timeline_id, '_aiad_timeline_link_label', $link_label );
        }
        aiad_timeline_set_featured_image_from_session( $timeline_id, $session_id, $partner_id );
        return;
    }

    $new_id = aiad_create_timeline_entry(
        array(
            'title'      => $session->post_title,
            'content'    => $content,
            'auto_type'  => 'live_session',
            'icon'       => 'event',
            'related_id' => $session_id,
            'link_url'   => $link_url,
            'link_label' => $link_label,
        )
    );

    if ( $new_id ) {
        aiad_timeline_set_featured_image_from_session( (int) $new_id, $session_id, $partner_id );
    }
}

/**
 * One-time: sync all published live sessions into timeline EVENT entries.
 */
function aiad_timeline_backfill_live_session_entries(): void {
    if ( get_option( 'aiad_timeline_live_sessions_synced_v1' ) ) {
        return;
    }
    if ( ! function_exists( 'aiad_get_live_sessions' ) ) {
        return;
    }

    foreach ( aiad_get_live_sessions( -1 ) as $session ) {
        aiad_timeline_sync_live_session_entry( (int) $session->ID );
    }

    update_option( 'aiad_timeline_live_sessions_synced_v1', true );
}
add_action( 'init', 'aiad_timeline_backfill_live_session_entries', 35 );

/**
 * Keep timeline EVENT cards in sync when a live session is saved.
 *
 * @param int $post_id Session post ID.
 */
function aiad_timeline_on_live_session_save( int $post_id ): void {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'live_session' ) {
        return;
    }
    aiad_timeline_sync_live_session_entry( $post_id );
}
add_action( 'save_post_live_session', 'aiad_timeline_on_live_session_save', 25 );

/**
 * Event date for countdown (Y-m-d). Filter to override.
 *
 * @return string Date string.
 */
function aiad_timeline_event_date(): string {
    return apply_filters( 'aiad_timeline_event_date', '2026-06-04' );
}

/**
 * Days until the event (for countdown display). Returns 0 if event has passed.
 *
 * @return int
 */
function aiad_timeline_days_until_event(): int {
    $event_date = aiad_timeline_event_date();
    $event_ts   = strtotime( $event_date . ' 00:00:00 UTC' );
    if ( ! $event_ts || $event_ts <= time() ) {
        return 0;
    }
    return (int) floor( ( $event_ts - time() ) / 86400 );
}

/**
 * Count of distinct schools registered (form submissions with school name). Cached for 1 hour.
 *
 * @return int
 */
function aiad_timeline_schools_registered_count(): int {
    $cache_key = 'aiad_timeline_schools_count';
    $cached    = get_transient( $cache_key );
    if ( false !== $cached && is_numeric( $cached ) ) {
        return (int) $cached;
    }

    global $wpdb;

    // Count distinct school names from individual form submissions
    $form_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COUNT(DISTINCT pm.meta_value) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = %s AND p.post_status = 'publish'
        AND pm.meta_key = %s AND TRIM(pm.meta_value) != ''",
        'form_submission',
        '_submission_school_name'
    ) );

    // Sum schools-in-portfolio from all published partner posts
    $partner_count = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)), 0) FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
        WHERE p.post_type = %s AND p.post_status = 'publish'
        AND pm.meta_key = %s AND pm.meta_value > 0",
        'partner',
        '_partner_school_count'
    ) );

    $count = $form_count + $partner_count;
    set_transient( $cache_key, $count, HOUR_IN_SECONDS );
    return $count;
}

/**
 * Maybe create countdown "weeks to go" timeline entries. Runs on daily cron.
 * Creates one entry per beat (12, 8, 6, 4, 2, 1 weeks) when we reach that threshold.
 */
function aiad_timeline_maybe_create_countdown_entries(): void {
    $event_date = aiad_timeline_event_date();
    $event_ts   = strtotime( $event_date . ' 23:59:59' );
    if ( ! $event_ts || $event_ts <= time() ) {
        return;
    }
    $days_until = (int) floor( ( $event_ts - time() ) / 86400 );
    $weeks_until = (int) floor( $days_until / 7 );

    $beats = array( 12, 8, 6, 4, 2, 1 );

    // One query to fetch all existing countdown entries instead of one per beat.
    $existing_entries = get_posts( array(
        'post_type'      => 'timeline',
        'post_status'    => 'publish',
        'posts_per_page' => count( $beats ),
        'meta_query'     => array(
            array( 'key' => '_aiad_timeline_auto_type', 'value' => 'countdown', 'compare' => '=' ),
        ),
    ) );
    $existing_weeks = array();
    foreach ( $existing_entries as $existing_post ) {
        $w = (int) get_post_meta( $existing_post->ID, '_aiad_timeline_countdown_weeks', true );
        if ( $w ) {
            $existing_weeks[] = $w;
        }
    }

    foreach ( $beats as $weeks ) {
        if ( $weeks_until > $weeks ) {
            continue;
        }
        if ( in_array( $weeks, $existing_weeks, true ) ) {
            continue;
        }
        $title = $weeks === 1
            ? __( '1 week to go — book your slot', 'ai-awareness-day' )
            : sprintf( __( '%d weeks to go — book your slot', 'ai-awareness-day' ), $weeks );
        aiad_create_timeline_entry( array(
            'title'           => $title,
            'content'         => sprintf( __( 'AI Awareness Day is in %d week(s). Sign up and get your school involved.', 'ai-awareness-day' ), $weeks ),
            'auto_type'       => 'countdown',
            'icon'            => 'signup',
            'link_url'        => home_url( '#contact' ),
            'link_label'      => __( 'Join the campaign →', 'ai-awareness-day' ),
            'countdown_weeks' => $weeks,
        ) );
    }
}

/**
 * Schedule daily countdown check on theme load (idempotent).
 */
function aiad_timeline_schedule_countdown_cron(): void {
    if ( get_transient( 'aiad_timeline_cron_scheduled' ) ) {
        return;
    }
    if ( ! wp_next_scheduled( 'aiad_timeline_countdown_daily' ) ) {
        wp_schedule_event( time(), 'daily', 'aiad_timeline_countdown_daily' );
    }
    set_transient( 'aiad_timeline_cron_scheduled', 1, DAY_IN_SECONDS );
}
add_action( 'init', 'aiad_timeline_schedule_countdown_cron', 20 );
add_action( 'aiad_timeline_countdown_daily', 'aiad_timeline_maybe_create_countdown_entries' );

/**
 * Auto-generate timeline entry when a resource is published.
 */
function aiad_timeline_on_resource_publish( string $new_status, string $old_status, WP_Post $post ): void {
    if ( $post->post_type !== 'resource' || $new_status !== 'publish' || $old_status === 'publish' ) {
        return;
    }

    $themes = get_the_terms( $post->ID, 'resource_principle' );
    $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
    $suffix = $theme_name ? sprintf( ' (%s)', $theme_name ) : '';

    aiad_create_timeline_entry( array(
        'title'      => sprintf(
            /* translators: %s: resource title */
            __( 'New resource added: %s', 'ai-awareness-day' ),
            $post->post_title . $suffix
        ),
        'content'    => $post->post_excerpt,
        'auto_type'  => 'resource',
        'icon'       => 'resource',
        'related_id' => $post->ID,
        'link_url'   => get_permalink( $post->ID ),
        'link_label' => __( 'View resource →', 'ai-awareness-day' ),
    ) );
}
add_action( 'transition_post_status', 'aiad_timeline_on_resource_publish', 10, 3 );

/**
 * Auto-generate timeline entry when a partner is published.
 */
function aiad_timeline_on_partner_publish( string $new_status, string $old_status, WP_Post $post ): void {
    if ( $post->post_type !== 'partner' || $new_status !== 'publish' || $old_status === 'publish' ) {
        return;
    }

    $types = get_the_terms( $post->ID, 'partner_type' );
    $type_name = $types && ! is_wp_error( $types ) ? $types[0]->name : __( 'Partner', 'ai-awareness-day' );

    aiad_create_timeline_entry( array(
        'title'      => sprintf(
            /* translators: 1: partner name, 2: partner type */
            __( '%1$s joined as %2$s', 'ai-awareness-day' ),
            $post->post_title,
            $type_name
        ),
        'auto_type'  => 'partner',
        'icon'       => 'partner',
        'related_id' => $post->ID,
    ) );
}
add_action( 'transition_post_status', 'aiad_timeline_on_partner_publish', 10, 3 );

/**
 * Invalidate schools count cache when a form submission or partner is saved.
 */
function aiad_timeline_invalidate_schools_count(): void {
    delete_transient( 'aiad_timeline_schools_count' );
}
add_action( 'save_post_form_submission', 'aiad_timeline_invalidate_schools_count', 5 );
add_action( 'save_post_partner', 'aiad_timeline_invalidate_schools_count', 5 );

/* ──────────────────────────────────────────────
   5. Query Helpers
   ────────────────────────────────────────────── */

/**
 * Get timeline entries for display.
 * Pinned entries first (by date), then remaining entries by date.
 *
 * @param int    $per_page Number of entries to return.
 * @param int    $offset   Offset for pagination (excludes pinned on page > 1).
 * @param string $filter   Filter by icon type (optional).
 * @return array{ entries: WP_Post[], has_more: bool }
 */
/**
 * Max supporting cards under the magazine hero (desktop).
 */
function aiad_timeline_magazine_sub_count(): int {
    return max( 0, (int) apply_filters( 'aiad_timeline_magazine_sub_count', 4 ) );
}

function aiad_timeline_feed_per_page(): int {
    $default = 1 + aiad_timeline_magazine_sub_count();

    return max( 1, (int) apply_filters( 'aiad_timeline_feed_per_page', $default ) );
}

/**
 * Entries per page on the /timeline/ archive.
 */
function aiad_timeline_archive_per_page(): int {
    return max( 1, (int) apply_filters( 'aiad_timeline_archive_per_page', 12 ) );
}

/**
 * Query timeline posts for the archive (paginated, optional icon filter).
 *
 * @param int    $paged  Current page (1-based).
 * @param string $filter Icon key or empty / all.
 * @return array{ entries: WP_Post[], max_pages: int, found: int }
 */
function aiad_get_timeline_archive_entries( int $paged = 1, string $filter = '' ): array {
    $per_page = aiad_timeline_archive_per_page();
    $args     = array(
        'post_type'      => 'timeline',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => max( 1, $paged ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    if ( ! empty( $filter ) && 'all' !== $filter ) {
        $args['meta_query'] = array(
            array(
                'key'     => '_aiad_timeline_icon',
                'value'   => $filter,
                'compare' => '=',
            ),
        );
    }

    $query = new WP_Query( $args );

    return array(
        'entries'    => $query->posts,
        'max_pages'  => (int) $query->max_num_pages,
        'found'      => (int) $query->found_posts,
    );
}

function aiad_get_timeline_entries( int $per_page = 4, int $offset = 0, string $filter = '' ): array {
    $entries = array();

    // Build meta query for filtering
    $meta_query = array();
    if ( ! empty( $filter ) && $filter !== 'all' ) {
        $meta_query = array(
            array( 'key' => '_aiad_timeline_icon', 'value' => $filter, 'compare' => '=' ),
        );
    }

    // First page: get pinned entries first
    if ( 0 === $offset ) {
        $pinned_args = array(
            'post_type'      => 'timeline',
            'post_status'    => 'publish',
            'posts_per_page' => 3, // Max 3 pinned items
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                array( 'key' => '_aiad_timeline_pinned', 'value' => '1', 'compare' => '=' ),
            ),
        );
        if ( ! empty( $meta_query ) ) {
            $pinned_args['meta_query'][] = $meta_query;
        }
        $pinned = get_posts( $pinned_args );
        $entries = $pinned;
    }

    // Fill remaining slots with non-pinned (or all if offset > 0)
    $remaining = $per_page - count( $entries );
    if ( $remaining > 0 ) {
        $exclude_ids = wp_list_pluck( $entries, 'ID' );
        $args = array(
            'post_type'      => 'timeline',
            'post_status'    => 'publish',
            'posts_per_page' => $remaining + 1, // +1 to check if more exist
            'offset'         => $offset > 0 ? $offset : 0,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'post__not_in'   => $exclude_ids,
        );

        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }

        $more_entries = get_posts( $args );
        $has_more = count( $more_entries ) > $remaining;
        $entries = array_merge( $entries, array_slice( $more_entries, 0, $remaining ) );

        return array( 'entries' => $entries, 'has_more' => $has_more );
    }

    // Pinned entries fill the page; check if any non-pinned entries exist before claiming more.
    $check = get_posts( array(
        'post_type'      => 'timeline',
        'post_status'    => 'publish',
        'posts_per_page' => 1,
        'post__not_in'   => wp_list_pluck( $entries, 'ID' ),
        'fields'         => 'ids',
    ) );
    return array( 'entries' => $entries, 'has_more' => ! empty( $check ) );
}

/**
 * Allowed HTML for oEmbed video output (iframe). wp_kses_post() strips iframes; timeline needs them for YouTube/Vimeo.
 *
 * @return array<string, array<string, bool>> Allowed HTML for wp_kses.
 */
function aiad_timeline_oembed_allowed_html(): array {
    return array(
        'iframe' => array(
            'src'             => true,
            'width'           => true,
            'height'          => true,
            'frameborder'     => true,
            'allowfullscreen' => true,
            'allow'           => true,
            'title'           => true,
            'loading'         => true,
            'referrerpolicy'  => true,
        ),
    );
}

/* ──────────────────────────────────────────────
   7. AJAX: Filter
   ────────────────────────────────────────────── */

function aiad_ajax_timeline_filter(): void {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_timeline_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-awareness-day' ) ) );
    }

    $filter  = isset( $_POST['filter'] ) ? sanitize_text_field( wp_unslash( $_POST['filter'] ) ) : 'all';
    $archive = ! empty( $_POST['archive'] );

    if ( $archive ) {
        $result = aiad_get_timeline_archive_entries( 1, $filter );
        $html   = aiad_render_timeline_archive_feed( $result['entries'] );
    } else {
        $per_page = aiad_timeline_feed_per_page();
        $result   = aiad_get_timeline_entries( $per_page, 0, $filter );
        $html     = aiad_render_timeline_feed_layouts( $result['entries'] );
    }

    wp_send_json_success( array(
        'html'  => $html,
        'count' => count( $result['entries'] ),
    ) );
}
add_action( 'wp_ajax_aiad_timeline_filter', 'aiad_ajax_timeline_filter' );
add_action( 'wp_ajax_nopriv_aiad_timeline_filter', 'aiad_ajax_timeline_filter' );

/* ──────────────────────────────────────────────
   7. AJAX: Like
   ────────────────────────────────────────────── */

/**
 * Increment like count for a timeline entry.
 * Rate limited: prevents the same visitor (by IP) from liking an entry more than once per 24 hours.
 */
function aiad_ajax_timeline_like(): void {
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_timeline_nonce' ) ) {
        wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-awareness-day' ) ) );
    }

    $entry_id = isset( $_POST['entry_id'] ) ? absint( $_POST['entry_id'] ) : 0;
    if ( ! $entry_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid entry.', 'ai-awareness-day' ) ) );
    }

    $post = get_post( $entry_id );
    if ( ! $post || $post->post_type !== 'timeline' ) {
        wp_send_json_error( array( 'message' => __( 'Invalid entry.', 'ai-awareness-day' ) ) );
    }

    // Rate limiting: check if this IP has already liked this entry
    $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
    if ( empty( $ip_address ) ) {
        wp_send_json_error( array( 'message' => __( 'Unable to verify request.', 'ai-awareness-day' ) ) );
    }

    $rate_limit_key = 'aiad_timeline_liked_' . md5( $ip_address . $entry_id );
    if ( get_transient( $rate_limit_key ) ) {
        // Already liked within the last 24 hours
        $current_count = (int) get_post_meta( $entry_id, '_aiad_timeline_like_count', true );
        wp_send_json_error( array(
            'message' => __( 'You have already liked this entry.', 'ai-awareness-day' ),
            'count'   => $current_count,
        ) );
    }

    // Increment like count
    $count = (int) get_post_meta( $entry_id, '_aiad_timeline_like_count', true );
    $count++;
    update_post_meta( $entry_id, '_aiad_timeline_like_count', $count );

    // Set rate limit transient (24 hours)
    set_transient( $rate_limit_key, true, DAY_IN_SECONDS );

    wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_timeline_like', 'aiad_ajax_timeline_like' );
add_action( 'wp_ajax_nopriv_aiad_timeline_like', 'aiad_ajax_timeline_like' );
