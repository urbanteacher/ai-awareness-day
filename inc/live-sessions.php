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
            <p class="description"><?php esc_html_e( 'Full URL required for online sessions (e.g. https://teams.microsoft.com/…). Used for the join button and event schema.', 'ai-awareness-day' ); ?></p>
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
 * One-time seeder: create the AI Awareness Day 2026 live sessions (and any
 * missing Partner posts they depend on). Runs once, gated by an option.
 * Edit or delete the posts in admin afterwards — re-running is harmless,
 * the option prevents duplicates.
 */
function aiad_seed_live_sessions(): void {
    if ( get_option( 'aiad_live_sessions_seeded_v1' ) ) {
        return;
    }
    if ( ! post_type_exists( 'live_session' ) || ! post_type_exists( 'partner' ) ) {
        return;
    }

    // Helper to find or create a partner by title.
    $ensure_partner = function ( string $title ): int {
        $existing = get_posts( array(
            'post_type'   => 'partner',
            'title'       => $title,
            'numberposts' => 1,
            'post_status' => 'any',
        ) );
        if ( ! empty( $existing ) ) {
            return (int) $existing[0]->ID;
        }
        $id = wp_insert_post( array(
            'post_type'   => 'partner',
            'post_title'  => $title,
            'post_status' => 'publish',
        ) );
        return is_wp_error( $id ) ? 0 : (int) $id;
    };

    $tech_she_can  = $ensure_partner( 'Tech She Can' );
    $barefoot      = $ensure_partner( 'Barefoot' );
    $stem_learning = $ensure_partner( 'STEM Learning' );
    $accenture     = $ensure_partner( 'Accenture' );

    $sessions = array(
        array(
            'title'    => 'AI in Our World — KS2 Live Assembly',
            'desc'     => 'Students explore AI in everyday life — chatbots, voice assistants, gaming, and creative tools. Covers generative AI, prompt use, and responsible practice. Highlights tech careers aligned with student interests. Delivered by Tech She Can in partnership with Accenture.',
            'start'    => '2026-06-04 09:15:00',
            'end'      => '2026-06-04 10:00:00',
            'audience' => array( 'ks2' ),
            'partner'  => $accenture,
        ),
        array(
            'title'    => 'AI Explorers Live Lesson – Level 1 (Ages 5–7)',
            'desc'     => 'Explores how data trains computers and evaluates AI-generated content. Delivered by Barefoot Computing. 45 minutes.',
            'start'    => '2026-06-04 13:00:00',
            'end'      => '2026-06-04 13:45:00',
            'audience' => array( 'ks1' ),
            'partner'  => $barefoot,
        ),
        array(
            'title'    => 'AI in Everyday Life — KS3 Live Assembly',
            'desc'     => 'Explores how AI integrates into daily life — navigation, recommendations, and voice tools. Discusses AI training, human roles, critical evaluation, fairness, and responsible use. Delivered by Tech She Can in partnership with Accenture.',
            'start'    => '2026-06-04 14:00:00',
            'end'      => '2026-06-04 15:00:00',
            'audience' => array( 'ks3' ),
            'partner'  => $accenture,
        ),
        array(
            'title'    => 'AI Explorers Live Lesson – Level 2 (Ages 7–11)',
            'desc'     => 'Examines AI\'s role in daily life, current applications, and misinformation. Delivered by Barefoot Computing. 55 minutes.',
            'start'    => '2026-06-04 14:00:00',
            'end'      => '2026-06-04 14:55:00',
            'audience' => array( 'ks2' ),
            'partner'  => $barefoot,
        ),
        array(
            'title'    => 'AI for ALL CPD (in partnership with Microsoft)',
            'desc'     => 'CPD session for primary and secondary teachers across all specialisms.',
            'start'    => '2026-06-04 15:30:00',
            'end'      => '2026-06-04 17:00:00',
            'audience' => array( 'teachers' ),
            'partner'  => $tech_she_can,
        ),
        array(
            'title'    => 'Secondary Teacher CPD',
            'desc'     => 'STEM Learning CPD focused on KS4 teaching practice.',
            'start'    => '2026-06-04 16:00:00',
            'end'      => '2026-06-04 17:00:00',
            'audience' => array( 'ks4' ),
            'partner'  => $stem_learning,
        ),
        array(
            'title'    => 'Careers in AI',
            'desc'     => 'KS5 panel exploring careers and pathways into AI.',
            'start'    => '2026-06-04 16:00:00',
            'end'      => '2026-06-04 17:00:00',
            'audience' => array( 'ks5' ),
            'partner'  => 0, // TBC
        ),
    );

    foreach ( $sessions as $s ) {
        $start_iso = str_replace( ' ', 'T', substr( $s['start'], 0, 16 ) );
        $end_iso   = str_replace( ' ', 'T', substr( $s['end'], 0, 16 ) );
        $post_id   = wp_insert_post( array(
            'post_type'    => 'live_session',
            'post_title'   => $s['title'],
            'post_content' => $s['desc'],
            'post_status'  => 'publish',
        ) );
        if ( is_wp_error( $post_id ) || ! $post_id ) {
            continue;
        }
        update_post_meta( $post_id, '_session_start_time', $start_iso );
        update_post_meta( $post_id, '_session_end_time', $end_iso );
        update_post_meta( $post_id, '_session_format', 'LIVE — MS Teams' );
        update_post_meta( $post_id, '_session_partner_id', (int) $s['partner'] );
        if ( ! empty( $s['audience'] ) ) {
            wp_set_object_terms( $post_id, $s['audience'], 'session_audience' );
        }
    }

    update_option( 'aiad_live_sessions_seeded_v1', true );
}
add_action( 'init', 'aiad_seed_live_sessions', 30 );

/**
 * One-time migration: correct Barefoot session titles/times seeded under old names.
 */
function aiad_migrate_barefoot_sessions(): void {
    if ( get_option( 'aiad_barefoot_migration_v1' ) ) {
        return;
    }
    $fixes = array(
        'Barefoot Workshop Level 1 with Ben Davies' => array(
            'title' => 'AI Explorers Live Lesson – Level 1 (Ages 5–7)',
            'desc'  => 'Explores how data trains computers and evaluates AI-generated content. Delivered by Barefoot Computing. 45 minutes.',
            'end'   => '2026-06-04T13:45',
        ),
        'Barefoot Workshop Level 2 with Ben Davies' => array(
            'title' => 'AI Explorers Live Lesson – Level 2 (Ages 7–11)',
            'desc'  => 'Examines AI\'s role in daily life, current applications, and misinformation. Delivered by Barefoot Computing. 55 minutes.',
            'end'   => '2026-06-04T14:55',
        ),
    );
    foreach ( $fixes as $old_title => $data ) {
        $posts = get_posts( array(
            'post_type'   => 'live_session',
            'post_status' => 'publish',
            'title'       => $old_title,
            'numberposts' => 1,
        ) );
        if ( empty( $posts ) ) {
            continue;
        }
        $id = $posts[0]->ID;
        wp_update_post( array(
            'ID'           => $id,
            'post_title'   => $data['title'],
            'post_content' => $data['desc'],
        ) );
        update_post_meta( $id, '_session_end_time', $data['end'] );
    }
    update_option( 'aiad_barefoot_migration_v1', true );
}
add_action( 'init', 'aiad_migrate_barefoot_sessions', 31 );

/**
 * One-time migration: fix Tech She Can assembly titles, times, and assign Accenture as partner.
 */
function aiad_migrate_techshecan_sessions(): void {
    if ( get_option( 'aiad_techshecan_migration_v1' ) ) {
        return;
    }

    // Ensure Accenture partner post exists.
    $accenture_posts = get_posts( array(
        'post_type'   => 'partner',
        'post_status' => 'publish',
        'title'       => 'Accenture',
        'numberposts' => 1,
    ) );
    if ( $accenture_posts ) {
        $accenture_id = $accenture_posts[0]->ID;
    } else {
        $accenture_id = wp_insert_post( array(
            'post_type'   => 'partner',
            'post_title'  => 'Accenture',
            'post_status' => 'publish',
        ) );
        if ( is_wp_error( $accenture_id ) ) {
            $accenture_id = 0;
        }
    }

    $fixes = array(
        'KS2 Assembly: AI in Our World' => array(
            'title'   => 'AI in Our World — KS2 Live Assembly',
            'desc'    => 'Students explore AI in everyday life — chatbots, voice assistants, gaming, and creative tools. Covers generative AI, prompt use, and responsible practice. Highlights tech careers aligned with student interests. Delivered by Tech She Can in partnership with Accenture.',
            'start'   => '2026-06-04T09:15',
            'partner' => $accenture_id,
        ),
        'KS3 Assembly: AI in Everyday Life' => array(
            'title'   => 'AI in Everyday Life — KS3 Live Assembly',
            'desc'    => 'Explores how AI integrates into daily life — navigation, recommendations, and voice tools. Discusses AI training, human roles, critical evaluation, fairness, and responsible use. Delivered by Tech She Can in partnership with Accenture.',
            'partner' => $accenture_id,
        ),
    );

    foreach ( $fixes as $old_title => $data ) {
        $posts = get_posts( array(
            'post_type'   => 'live_session',
            'post_status' => 'publish',
            'title'       => $old_title,
            'numberposts' => 1,
        ) );
        if ( empty( $posts ) ) {
            continue;
        }
        $id = $posts[0]->ID;
        wp_update_post( array(
            'ID'           => $id,
            'post_title'   => $data['title'],
            'post_content' => $data['desc'],
        ) );
        if ( ! empty( $data['start'] ) ) {
            update_post_meta( $id, '_session_start_time', $data['start'] );
        }
        if ( $data['partner'] ) {
            update_post_meta( $id, '_session_partner_id', $data['partner'] );
        }
    }

    update_option( 'aiad_techshecan_migration_v1', true );
}
add_action( 'init', 'aiad_migrate_techshecan_sessions', 32 );

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
 * Preferred order for session audience filter tabs (taxonomy slugs).
 *
 * @return string[]
 */
function aiad_get_session_audience_tab_order(): array {
    return array( 'ks1', 'ks2', 'ks3', 'ks4', 'ks5', 'teachers', 'all' );
}

/**
 * Build per-session audience slugs and tab labels/counts for schedule UIs.
 *
 * @param WP_Post[] $sessions Sessions from aiad_get_live_sessions().
 * @return array{session_audience_map: array<int, string[]>, audience_counts: array<string, int>, audience_labels: array<string, string>}
 */
function aiad_get_schedule_audience_filter_data( array $sessions ): array {
    $session_audience_map = array();
    $audience_counts      = array();
    $audience_labels      = array();

    foreach ( $sessions as $s ) {
        $terms = get_the_terms( $s->ID, 'session_audience' );
        $slugs = array();
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $t ) {
                $slugs[]                       = $t->slug;
                $audience_labels[ $t->slug ]   = $t->name;
                $audience_counts[ $t->slug ] = ( $audience_counts[ $t->slug ] ?? 0 ) + 1;
            }
        }
        $session_audience_map[ $s->ID ] = $slugs;
    }

    $preferred_order = aiad_get_session_audience_tab_order();
    uksort(
        $audience_labels,
        function ( $a, $b ) use ( $preferred_order ) {
            $ai = array_search( $a, $preferred_order, true );
            $bi = array_search( $b, $preferred_order, true );
            $ai = $ai === false ? 99 : $ai;
            $bi = $bi === false ? 99 : $bi;
            return $ai - $bi;
        }
    );

    return array(
        'session_audience_map' => $session_audience_map,
        'audience_counts'      => $audience_counts,
        'audience_labels'      => $audience_labels,
    );
}

/**
 * Audience filters shared by the front-page schedule block and /schedule/ archive.
 * Markup matches the Live Timeline filter row (timeline-filters / timeline-filter-btn).
 *
 * @param array<string, string> $audience_labels Slug => display name.
 * @param array<string, int>    $audience_counts Retained for API compatibility; not displayed (timeline pills have no counts).
 * @param int                   $session_count   Retained for API compatibility; not displayed.
 */
function aiad_render_schedule_audience_tabs( array $audience_labels, array $audience_counts, int $session_count ): void {
    ?>
    <div class="timeline-filters fade-up" role="group" aria-label="<?php esc_attr_e( 'Filter sessions by audience', 'ai-awareness-day' ); ?>">
        <button type="button" class="timeline-filter-btn timeline-filter-btn--active" data-filter="all">
            <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
        </button>
        <?php foreach ( $audience_labels as $slug => $name ) : ?>
            <button type="button" class="timeline-filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>">
                <?php echo esc_html( $name ); ?>
            </button>
        <?php endforeach; ?>
        <button type="button" class="timeline-filter-btn" disabled aria-disabled="true">
            <?php esc_html_e( 'Parents', 'ai-awareness-day' ); ?>
            <span class="timeline-filter-btn__suffix" aria-hidden="true"><?php esc_html_e( ' · Soon', 'ai-awareness-day' ); ?></span>
        </button>
    </div>
    <?php
}

/**
 * One inline script per page: schedule archive audience filters + ICS, and homepage spotlight ICS/share.
 */
function aiad_print_schedule_audience_filter_script(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;
    ?>
<script>
(function(){
    function pad( n ){ return String(n).padStart(2, '0'); }
    function nowStamp(){
        var d = new Date();
        return d.getUTCFullYear() + pad(d.getUTCMonth()+1) + pad(d.getUTCDate())
             + 'T' + pad(d.getUTCHours()) + pad(d.getUTCMinutes()) + pad(d.getUTCSeconds()) + 'Z';
    }
    function escapeICS( s ){
        return String(s || '').replace(/\\/g,'\\\\').replace(/\n/g,'\\n').replace(/,/g,'\\,').replace(/;/g,'\\;');
    }
    function downloadIcsFromHost( host ){
        if ( ! host ) return;
        var title  = host.getAttribute('data-ics-title') || 'AI Awareness Day session';
        var desc   = host.getAttribute('data-ics-desc')  || '';
        var start  = host.getAttribute('data-ics-start') || '';
        var end    = host.getAttribute('data-ics-end')   || start;
        var url    = host.getAttribute('data-ics-url')   || '';
        if ( ! start ) return;
        var ics = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//AI Awareness Day//Schedule//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:' + start + '-' + Math.random().toString(36).slice(2) + '@aiawarenessday',
            'DTSTAMP:' + nowStamp(),
            'DTSTART;TZID=Europe/London:' + start,
            'DTEND;TZID=Europe/London:' + end,
            'SUMMARY:' + escapeICS(title),
            'DESCRIPTION:' + escapeICS(desc + (url ? '\n\nJoin: ' + url : '')),
            url ? 'URL:' + url : '',
            'END:VEVENT',
            'END:VCALENDAR'
        ].filter(Boolean).join('\r\n');
        var blob = new Blob([ics], { type: 'text/calendar;charset=utf-8' });
        var a    = document.createElement('a');
        a.href     = URL.createObjectURL(blob);
        a.download = title.replace(/[^a-z0-9]+/gi, '-').toLowerCase() + '.ics';
        document.body.appendChild(a);
        a.click();
        setTimeout(function(){ URL.revokeObjectURL(a.href); a.remove(); }, 1000);
    }
    function setScheduleRowVisible( row, show ) {
        row.classList.toggle('aiad-schedule-filter-item--hidden', !show);
        row.hidden = !show;
        row.setAttribute('aria-hidden', show ? 'false' : 'true');
        row.querySelectorAll('td').forEach(function( cell ){
            if ( show ) {
                cell.removeAttribute('hidden');
            } else {
                cell.setAttribute('hidden', '');
            }
        });
    }
    function wireAudienceFilters( root ) {
        var tabs  = root.querySelectorAll('.timeline-filter-btn');
        var items = root.querySelectorAll('.aiad-schedule-filter-item');
        var empty = root.querySelector('.aiad-schedule-row__empty');
        tabs.forEach(function( tab ){
            if ( tab.disabled ) return;
            tab.addEventListener('click', function(){
                var target = tab.getAttribute('data-filter') || 'all';
                tabs.forEach(function( t ){
                    if ( t.disabled ) return;
                    var active = t === tab;
                    t.classList.toggle('timeline-filter-btn--active', active);
                });
                var visibleCount = 0;
                items.forEach(function( row ){
                    var slugs = (row.getAttribute('data-audience') || '').split(/\s+/).filter(Boolean);
                    var show  = target === 'all' || slugs.indexOf(target) !== -1;
                    setScheduleRowVisible(row, show);
                    if ( show ) visibleCount++;
                });
                if ( empty ) empty.hidden = visibleCount > 0;
            });
        });
    }
    function wireIcsButtons( root, btnSel, hostSel ) {
        root.querySelectorAll( btnSel ).forEach(function( btn ){
            btn.addEventListener('click', function(){
                var host = btn.closest( hostSel );
                downloadIcsFromHost( host );
            });
        });
    }
    function wireScheduleSpotlight( root ) {
        var copiedMsg = <?php echo wp_json_encode( __( 'Link copied', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); ?>;
        root.querySelectorAll('.aiad-schedule-card__share').forEach(function( btn ){
            btn.addEventListener('click', function(){
                var url = btn.getAttribute('data-share-url') || '';
                var title = btn.getAttribute('data-share-title') || '';
                if ( ! url ) return;
                if ( navigator.share ) {
                    navigator.share({ title: title, text: title, url: url }).catch(function(){});
                    return;
                }
                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    var prev = btn.getAttribute('aria-label') || '';
                    navigator.clipboard.writeText( url ).then(function(){
                        btn.setAttribute('aria-label', copiedMsg);
                        setTimeout(function(){ btn.setAttribute('aria-label', prev); }, 2200);
                    }).catch(function(){});
                }
            });
        });
        wireIcsButtons( root, '.aiad-schedule-card__ics', '.aiad-schedule-card' );
    }
    function wireTableShare( root ) {
        var copiedMsg = <?php echo wp_json_encode( __( 'Link copied', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE ); ?>;
        root.querySelectorAll('.aiad-schedule-table__share').forEach(function( btn ){
            btn.addEventListener('click', function(){
                var url   = btn.getAttribute('data-share-url')   || '';
                var title = btn.getAttribute('data-share-title') || '';
                if ( ! url ) return;
                if ( navigator.share ) {
                    navigator.share({ title: title, url: url }).catch(function(){});
                    return;
                }
                if ( navigator.clipboard && navigator.clipboard.writeText ) {
                    var prev = btn.getAttribute('aria-label') || '';
                    navigator.clipboard.writeText( url ).then(function(){
                        btn.setAttribute('aria-label', copiedMsg);
                        setTimeout(function(){ btn.setAttribute('aria-label', prev); }, 2200);
                    }).catch(function(){});
                }
            });
        });
    }
    document.querySelectorAll('.aiad-schedule-filter-root').forEach(function( root ){
        wireAudienceFilters( root );
        wireIcsButtons( root, '.aiad-schedule-item__ics', '.aiad-schedule-item' );
        wireTableShare( root );
    });
    var spotlight = document.getElementById('schedule');
    if ( spotlight && spotlight.classList.contains('aiad-schedule-home') ) {
        wireScheduleSpotlight( spotlight );
    }
    // Archive page: wire copy-link + native share buttons
    var copyBtn   = document.querySelector('.aiad-schedule-share-bar__btn--copy');
    var nativeBtn = document.querySelector('.aiad-schedule-share-bar__btn--native');
    var shareStatus = document.querySelector('.aiad-schedule-share-bar__status');
    if ( nativeBtn && navigator.share ) {
        nativeBtn.hidden = false;
        nativeBtn.addEventListener('click', function(){
            navigator.share({ title: document.title, url: window.location.href }).catch(function(){});
        });
    }
    if ( copyBtn ) {
        copyBtn.addEventListener('click', function(){
            if ( navigator.clipboard ) {
                var copiedMsg = <?php echo wp_json_encode( __( 'Link copied!', 'ai-awareness-day' ), JSON_HEX_TAG | JSON_HEX_AMP ); ?>;
                navigator.clipboard.writeText( window.location.href ).then(function(){
                    if ( shareStatus ) {
                        shareStatus.textContent = copiedMsg;
                        setTimeout(function(){ shareStatus.textContent = ''; }, 2500);
                    }
                }).catch(function(){});
            }
        });
    }
})();
</script>
    <?php
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

/**
 * Server-side ICS download endpoint.
 *
 * Registers /session-ics/{id}/ as a proper downloadable calendar file so that
 * calendar apps can subscribe directly — no JS Blob required.
 */
function aiad_register_session_ics_rewrite(): void {
    add_rewrite_rule(
        '^session-ics/([0-9]+)/?$',
        'index.php?session_ics_id=$matches[1]',
        'top'
    );
}
add_action( 'init', 'aiad_register_session_ics_rewrite' );

function aiad_session_ics_query_var( array $vars ): array {
    $vars[] = 'session_ics_id';
    return $vars;
}
add_filter( 'query_vars', 'aiad_session_ics_query_var' );

function aiad_session_ics_serve(): void {
    $id = (int) get_query_var( 'session_ics_id' );
    if ( ! $id ) {
        return;
    }

    $post = get_post( $id );
    if ( ! $post || $post->post_type !== 'live_session' ) {
        status_header( 404 );
        exit;
    }

    $title = get_the_title( $id );
    $start = (string) get_post_meta( $id, '_session_start_time', true );
    $end   = (string) get_post_meta( $id, '_session_end_time', true );
    $url   = (string) get_post_meta( $id, '_session_registration_url', true );
    $desc  = wp_strip_all_tags( $post->post_content ?: '' );

    if ( ! $start ) {
        status_header( 404 );
        exit;
    }

    $ics_start = str_replace( array( '-', ':' ), '', $start ) . '00';
    $ics_end   = $end ? str_replace( array( '-', ':' ), '', $end ) . '00' : $ics_start;
    $dtstamp   = gmdate( 'Ymd\THis\Z' );
    $uid       = $ics_start . '-' . $id . '@aiawarenessday.co.uk';

    $esc = function ( string $s ): string {
        return str_replace(
            array( '\\', ';', ',', "\n" ),
            array( '\\\\', '\\;', '\\,', '\\n' ),
            $s
        );
    };

    $lines = array(
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//AI Awareness Day//Schedule//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        'UID:' . $uid,
        'DTSTAMP:' . $dtstamp,
        'DTSTART;TZID=Europe/London:' . $ics_start,
        'DTEND;TZID=Europe/London:' . $ics_end,
        'SUMMARY:' . $esc( $title ),
        'DESCRIPTION:' . $esc( $desc . ( $url ? "\n\nJoin: $url" : '' ) ),
    );
    if ( $url ) {
        $lines[] = 'URL:' . $url;
    }
    $lines[] = 'END:VEVENT';
    $lines[] = 'END:VCALENDAR';

    $filename = sanitize_title( $title ) . '.ics';

    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Cache-Control: no-cache, no-store, must-revalidate' );
    header( 'Pragma: no-cache' );

    echo implode( "\r\n", $lines );
    exit;
}
add_action( 'template_redirect', 'aiad_session_ics_serve' );
