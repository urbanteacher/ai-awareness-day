<?php
/**
 * Live Sessions: CPT, taxonomy, admin UI, and helpers.
 *
 * A live_session post represents one scheduled live event on AI Awareness Day
 * (e.g. "KS2 Assembly: AI in Our World"). Each session references an existing
 * partner post so the provider logo/name is reused, not duplicated.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the live_session CPT and its session_audience taxonomy.
 */
function aiad_register_live_session_cpt(): void {
    register_post_type( 'live_session', array(
        'labels'        => array(
            'name'          => __( 'Live Sessions', 'ai-awareness-day' ),
            'singular_name' => __( 'Live Session', 'ai-awareness-day' ),
            'add_new'       => __( 'Add New', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Live Session', 'ai-awareness-day' ),
            'edit_item'     => __( 'Edit Live Session', 'ai-awareness-day' ),
            'view_item'     => __( 'View Live Session', 'ai-awareness-day' ),
            'menu_name'     => __( 'Live Sessions', 'ai-awareness-day' ),
        ),
        'public'        => true,
        'has_archive'   => 'schedule',
        'rewrite'       => array( 'slug' => 'schedule' ),
        'menu_icon'     => 'dashicons-calendar-alt',
        'menu_position' => 22,
        'supports'      => array( 'title', 'editor', 'thumbnail' ),
        'show_in_rest'  => true,
    ) );

    register_taxonomy( 'session_audience', array( 'live_session' ), array(
        'labels'            => array(
            'name'          => __( 'Audiences', 'ai-awareness-day' ),
            'singular_name' => __( 'Audience', 'ai-awareness-day' ),
            'add_new_item'  => __( 'Add New Audience', 'ai-awareness-day' ),
        ),
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'audience' ),
    ) );
}
add_action( 'init', 'aiad_register_live_session_cpt', 20 );

/**
 * Seed default audience terms once.
 */
function aiad_seed_session_audience_terms(): void {
    if ( get_option( 'aiad_session_audience_seeded' ) ) {
        return;
    }
    $terms = array(
        'KS1 (ages 5–7)'   => 'ks1',
        'KS2 (ages 7–11)'  => 'ks2',
        'KS3 (ages 11–13)' => 'ks3',
        'KS4 (ages 14–16)' => 'ks4',
        'KS5 (ages 16–18)' => 'ks5',
        __( 'Teachers', 'ai-awareness-day' ) => 'teachers',
        __( 'All audiences', 'ai-awareness-day' ) => 'all',
    );
    foreach ( $terms as $name => $slug ) {
        if ( ! term_exists( $slug, 'session_audience' ) ) {
            wp_insert_term( $name, 'session_audience', array( 'slug' => $slug ) );
        }
    }
    update_option( 'aiad_session_audience_seeded', true );
}
add_action( 'init', 'aiad_seed_session_audience_terms', 23 );

/**
 * Register meta fields and admin meta box.
 */
function aiad_live_session_meta_box(): void {
    add_meta_box(
        'aiad_live_session_details',
        __( 'Session details', 'ai-awareness-day' ),
        'aiad_live_session_meta_box_callback',
        'live_session',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aiad_live_session_meta_box' );

/**
 * Meta box content.
 *
 * @param WP_Post $post Current post.
 */
function aiad_live_session_meta_box_callback( WP_Post $post ): void {
    wp_nonce_field( 'aiad_live_session_save', 'aiad_live_session_nonce' );

    $start_time       = (string) get_post_meta( $post->ID, '_session_start_time', true );
    $end_time         = (string) get_post_meta( $post->ID, '_session_end_time', true );
    $format_label     = (string) get_post_meta( $post->ID, '_session_format', true );
    $registration_url = (string) get_post_meta( $post->ID, '_session_registration_url', true );
    $partner_id       = (int) get_post_meta( $post->ID, '_session_partner_id', true );

    // Pre-fill format with sensible default.
    if ( $format_label === '' ) {
        $format_label = __( 'LIVE — MS Teams', 'ai-awareness-day' );
    }

    $partners = get_posts( array(
        'post_type'      => 'partner',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );
    ?>
    <style>
        .aiad-ls-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem 1.5rem; margin-top:.5rem; }
        .aiad-ls-grid label { display:block; font-weight:600; margin-bottom:.25rem; }
        .aiad-ls-grid input[type="datetime-local"],
        .aiad-ls-grid input[type="text"],
        .aiad-ls-grid input[type="url"],
        .aiad-ls-grid select { width:100%; }
        .aiad-ls-full { grid-column:1 / -1; }
    </style>
    <div class="aiad-ls-grid">
        <div>
            <label for="aiad_session_start_time"><?php esc_html_e( 'Start time', 'ai-awareness-day' ); ?></label>
            <input type="datetime-local" id="aiad_session_start_time" name="aiad_session_start_time" value="<?php echo esc_attr( $start_time ); ?>" />
            <p class="description"><?php esc_html_e( 'Local time (Europe/London).', 'ai-awareness-day' ); ?></p>
        </div>
        <div>
            <label for="aiad_session_end_time"><?php esc_html_e( 'End time', 'ai-awareness-day' ); ?></label>
            <input type="datetime-local" id="aiad_session_end_time" name="aiad_session_end_time" value="<?php echo esc_attr( $end_time ); ?>" />
        </div>
        <div>
            <label for="aiad_session_format"><?php esc_html_e( 'Format', 'ai-awareness-day' ); ?></label>
            <input type="text" id="aiad_session_format" name="aiad_session_format" value="<?php echo esc_attr( $format_label ); ?>" placeholder="LIVE — MS Teams" />
        </div>
        <div>
            <label for="aiad_session_partner_id"><?php esc_html_e( 'Provider (Partner)', 'ai-awareness-day' ); ?></label>
            <select id="aiad_session_partner_id" name="aiad_session_partner_id">
                <option value="0"><?php esc_html_e( '— Select partner —', 'ai-awareness-day' ); ?></option>
                <?php foreach ( $partners as $partner ) : ?>
                    <option value="<?php echo esc_attr( $partner->ID ); ?>" <?php selected( $partner_id, (int) $partner->ID ); ?>>
                        <?php echo esc_html( $partner->post_title ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php esc_html_e( 'Reuses logo and name from the Partner post. Add the partner first under Partners.', 'ai-awareness-day' ); ?></p>
        </div>
        <div class="aiad-ls-full">
            <label for="aiad_session_registration_url"><?php esc_html_e( 'Registration / join URL', 'ai-awareness-day' ); ?></label>
            <input type="url" id="aiad_session_registration_url" name="aiad_session_registration_url" value="<?php echo esc_attr( $registration_url ); ?>" placeholder="https://" />
        </div>
    </div>
    <?php
}

/**
 * Save meta box.
 *
 * @param int $post_id Post ID.
 */
function aiad_save_live_session_meta( int $post_id ): void {
    if ( ! isset( $_POST['aiad_live_session_nonce'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_live_session_nonce'] ) ), 'aiad_live_session_save' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'live_session' ) {
        return;
    }

    $fields = array(
        'aiad_session_start_time'       => '_session_start_time',
        'aiad_session_end_time'         => '_session_end_time',
        'aiad_session_format'           => '_session_format',
        'aiad_session_registration_url' => '_session_registration_url',
        'aiad_session_partner_id'       => '_session_partner_id',
    );
    foreach ( $fields as $post_key => $meta_key ) {
        if ( ! isset( $_POST[ $post_key ] ) ) {
            continue;
        }
        $raw = wp_unslash( $_POST[ $post_key ] );
        switch ( $meta_key ) {
            case '_session_registration_url':
                update_post_meta( $post_id, $meta_key, esc_url_raw( (string) $raw ) );
                break;
            case '_session_partner_id':
                update_post_meta( $post_id, $meta_key, absint( $raw ) );
                break;
            default:
                update_post_meta( $post_id, $meta_key, sanitize_text_field( (string) $raw ) );
        }
    }
}
add_action( 'save_post_live_session', 'aiad_save_live_session_meta' );

/**
 * Get all live sessions ordered by start time ascending.
 *
 * @param int $limit -1 for all.
 * @return WP_Post[]
 */
function aiad_get_live_sessions( int $limit = -1 ): array {
    return get_posts( array(
        'post_type'      => 'live_session',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_key'       => '_session_start_time',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ) );
}

/**
 * Format a "10:00 – 11:00" range from start/end ISO datetimes.
 */
function aiad_format_session_time_range( string $start, string $end ): string {
    if ( $start === '' ) {
        return '';
    }
    try {
        $tz       = new DateTimeZone( 'Europe/London' );
        $start_dt = new DateTime( $start, $tz );
        $end_dt   = $end !== '' ? new DateTime( $end, $tz ) : null;
        $fmt      = 'H:i';
        $out      = $start_dt->format( $fmt );
        if ( $end_dt ) {
            $out .= ' – ' . $end_dt->format( $fmt );
        }
        return $out;
    } catch ( \Exception $e ) {
        return $start;
    }
}

/**
 * Admin list columns for live_session.
 *
 * @param array<string,string> $cols
 * @return array<string,string>
 */
function aiad_live_session_admin_columns( array $cols ): array {
    $new = array();
    foreach ( $cols as $k => $v ) {
        $new[ $k ] = $v;
        if ( $k === 'title' ) {
            $new['session_time']     = __( 'Time', 'ai-awareness-day' );
            $new['session_audience'] = __( 'Audience', 'ai-awareness-day' );
            $new['session_provider'] = __( 'Provider', 'ai-awareness-day' );
        }
    }
    return $new;
}
add_filter( 'manage_live_session_posts_columns', 'aiad_live_session_admin_columns' );

/**
 * Render values for the new admin columns.
 */
function aiad_live_session_admin_column_content( string $column, int $post_id ): void {
    switch ( $column ) {
        case 'session_time':
            $s = (string) get_post_meta( $post_id, '_session_start_time', true );
            $e = (string) get_post_meta( $post_id, '_session_end_time', true );
            echo esc_html( aiad_format_session_time_range( $s, $e ) ?: '—' );
            break;
        case 'session_audience':
            $terms = get_the_terms( $post_id, 'session_audience' );
            if ( $terms && ! is_wp_error( $terms ) ) {
                echo esc_html( implode( ', ', wp_list_pluck( $terms, 'name' ) ) );
            } else {
                echo '—';
            }
            break;
        case 'session_provider':
            $pid = (int) get_post_meta( $post_id, '_session_partner_id', true );
            echo $pid ? esc_html( get_the_title( $pid ) ) : '—';
            break;
    }
}
add_action( 'manage_live_session_posts_custom_column', 'aiad_live_session_admin_column_content', 10, 2 );
