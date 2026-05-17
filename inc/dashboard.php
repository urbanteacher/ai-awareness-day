<?php
/**
 * Admin dashboard widget: Campaign sign-up analytics.
 * Shows total sign-ups, progress toward goal, role breakdown,
 * weekly trend, and next milestone.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the dashboard widgets.
 */
function aiad_register_dashboard_widget(): void {
    wp_add_dashboard_widget(
        'aiad_campaign_analytics',
        __( '📊 AI Awareness Day — Campaign Analytics', 'ai-awareness-day' ),
        'aiad_dashboard_widget_callback'
    );
    wp_add_dashboard_widget(
        'aiad_resource_analytics',
        __( '📚 Resource Analytics — Downloads & Views', 'ai-awareness-day' ),
        'aiad_resource_analytics_widget_callback'
    );
    wp_add_dashboard_widget(
        'aiad_ai_tool_analytics',
        __( '🛠 AI Tools — Homepage visits', 'ai-awareness-day' ),
        'aiad_ai_tool_analytics_widget_callback'
    );
}
add_action( 'wp_dashboard_setup', 'aiad_register_dashboard_widget' );

/**
 * Aggregate resource analytics: total downloads, total views, resource counts.
 *
 * @return array{total_downloads:int,total_views:int,resources_with_downloads:int,resources_with_views:int,resource_count:int}
 */
function aiad_get_resource_analytics(): array {
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $total_downloads = (int) $wpdb->get_var(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)),0)
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_aiad_download_count'
           AND p.post_type = 'resource'
           AND p.post_status = 'publish'"
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $total_views = (int) $wpdb->get_var(
        "SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)),0)
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_aiad_view_count'
           AND p.post_type = 'resource'
           AND p.post_status = 'publish'"
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $resources_with_downloads = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT p.ID)
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_aiad_download_count'
           AND CAST(pm.meta_value AS UNSIGNED) > 0
           AND p.post_type = 'resource'
           AND p.post_status = 'publish'"
    );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $resources_with_views = (int) $wpdb->get_var(
        "SELECT COUNT(DISTINCT p.ID)
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_aiad_view_count'
           AND CAST(pm.meta_value AS UNSIGNED) > 0
           AND p.post_type = 'resource'
           AND p.post_status = 'publish'"
    );
    $resource_count = (int) wp_count_posts( 'resource' )->publish;

    return array(
        'total_downloads'          => $total_downloads,
        'total_views'              => $total_views,
        'resources_with_downloads' => $resources_with_downloads,
        'resources_with_views'     => $resources_with_views,
        'resource_count'           => $resource_count,
    );
}

/**
 * Top N resources by a numeric meta key.
 *
 * @param string $meta_key e.g. _aiad_download_count
 * @param int    $limit    Max results.
 * @return array<int,array{id:int,title:string,count:int,url:string,edit:string}>
 */
function aiad_get_top_resources_by_meta( string $meta_key, int $limit = 5 ): array {
    $q = new WP_Query( array(
        'post_type'      => 'resource',
        'post_status'    => 'publish',
        'posts_per_page' => $limit,
        'meta_key'       => $meta_key,
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => $meta_key,
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ),
        ),
    ) );
    $out = array();
    foreach ( $q->posts as $p ) {
        $out[] = array(
            'id'    => (int) $p->ID,
            'title' => get_the_title( $p ),
            'count' => (int) get_post_meta( $p->ID, $meta_key, true ),
            'url'   => (string) get_permalink( $p ),
            'edit'  => (string) get_edit_post_link( $p->ID ),
        );
    }
    return $out;
}

/**
 * Render the Resource Analytics dashboard widget.
 */
function aiad_resource_analytics_widget_callback(): void {
    $stats = aiad_get_resource_analytics();
    $top_downloads = aiad_get_top_resources_by_meta( '_aiad_download_count', 5 );
    $top_views     = aiad_get_top_resources_by_meta( '_aiad_view_count', 5 );
    $handpicked_total = function_exists( 'aiad_sum_meta_for_post_type' )
        ? aiad_sum_meta_for_post_type( 'featured_resource', '_aiad_featured_resource_clicks' )
        : 0;
    $top_handpicked = function_exists( 'aiad_get_top_posts_for_type' )
        ? aiad_get_top_posts_for_type( 'featured_resource', '_aiad_featured_resource_clicks', 5 )
        : array();

    $coverage_dl = $stats['resource_count'] > 0
        ? round( ( $stats['resources_with_downloads'] / $stats['resource_count'] ) * 100 )
        : 0;
    $coverage_vw = $stats['resource_count'] > 0
        ? round( ( $stats['resources_with_views'] / $stats['resource_count'] ) * 100 )
        : 0;
    ?>
    <style>
        #aiad_resource_analytics .aiad-ra-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1.5rem;
            margin-bottom: 0.75rem;
        }
        #aiad_resource_analytics .aiad-ra-stat__val {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
            color: #1e1e1e;
        }
        #aiad_resource_analytics .aiad-ra-stat__lbl {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #646970;
            margin-top: 0.2rem;
            display: block;
        }
        #aiad_resource_analytics .aiad-ra-sub {
            font-size: 0.72rem;
            color: #646970;
            margin-top: 0.1rem;
        }
        #aiad_resource_analytics .aiad-ra-section-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1e1e1e;
            margin: 0 0 0.4rem;
        }
        #aiad_resource_analytics .aiad-ra-list {
            list-style: none;
            margin: 0 0 0.75rem;
            padding: 0;
        }
        #aiad_resource_analytics .aiad-ra-list li {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.3rem 0;
            border-bottom: 1px solid #f0f0f1;
            font-size: 0.85rem;
        }
        #aiad_resource_analytics .aiad-ra-list li:last-child { border-bottom: none; }
        #aiad_resource_analytics .aiad-ra-list a {
            color: #2271b1;
            text-decoration: none;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #aiad_resource_analytics .aiad-ra-list .count {
            font-weight: 700;
            color: #1e1e1e;
        }
        #aiad_resource_analytics hr.aiad-ra-divider {
            border: none;
            border-top: 1px solid #dcdcde;
            margin: 0.75rem 0;
        }
        #aiad_resource_analytics .aiad-ra-empty {
            color: #646970;
            font-size: 0.85rem;
            font-style: italic;
        }
    </style>

    <div class="aiad-ra-grid">
        <div>
            <span class="aiad-ra-stat__val"><?php echo esc_html( number_format( $stats['total_downloads'] ) ); ?></span>
            <span class="aiad-ra-stat__lbl"><?php esc_html_e( 'Total downloads', 'ai-awareness-day' ); ?></span>
            <p class="aiad-ra-sub">
                <?php
                /* translators: 1: count, 2: total resources, 3: coverage % */
                echo esc_html( sprintf( __( '%1$d of %2$d resources (%3$d%%)', 'ai-awareness-day' ), $stats['resources_with_downloads'], $stats['resource_count'], $coverage_dl ) );
                ?>
            </p>
        </div>
        <div>
            <span class="aiad-ra-stat__val"><?php echo esc_html( number_format( $stats['total_views'] ) ); ?></span>
            <span class="aiad-ra-stat__lbl"><?php esc_html_e( 'Total views', 'ai-awareness-day' ); ?></span>
            <p class="aiad-ra-sub">
                <?php
                echo esc_html( sprintf( __( '%1$d of %2$d resources (%3$d%%)', 'ai-awareness-day' ), $stats['resources_with_views'], $stats['resource_count'], $coverage_vw ) );
                ?>
            </p>
        </div>
    </div>

    <hr class="aiad-ra-divider">
    <p class="aiad-ra-section-title"><?php esc_html_e( 'Top downloads', 'ai-awareness-day' ); ?></p>
    <?php if ( empty( $top_downloads ) ) : ?>
        <p class="aiad-ra-empty"><?php esc_html_e( 'No downloads recorded yet.', 'ai-awareness-day' ); ?></p>
    <?php else : ?>
        <ul class="aiad-ra-list">
            <?php foreach ( $top_downloads as $row ) : ?>
                <li>
                    <a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
                    <span class="count"><?php echo esc_html( number_format( $row['count'] ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p class="aiad-ra-section-title"><?php esc_html_e( 'Top views', 'ai-awareness-day' ); ?></p>
    <?php if ( empty( $top_views ) ) : ?>
        <p class="aiad-ra-empty"><?php esc_html_e( 'No views recorded yet.', 'ai-awareness-day' ); ?></p>
    <?php else : ?>
        <ul class="aiad-ra-list">
            <?php foreach ( $top_views as $row ) : ?>
                <li>
                    <a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
                    <span class="count"><?php echo esc_html( number_format( $row['count'] ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr class="aiad-ra-divider">
    <p class="aiad-ra-section-title"><?php esc_html_e( 'Handpicked partner resources (homepage clicks)', 'ai-awareness-day' ); ?></p>
    <p class="aiad-ra-sub" style="margin:0 0 0.5rem;">
        <?php esc_html_e( 'Outbound clicks on handpicked cards in the Extra Resources section.', 'ai-awareness-day' ); ?>
        <strong><?php echo esc_html( number_format( $handpicked_total ) ); ?></strong>
        <?php esc_html_e( 'total', 'ai-awareness-day' ); ?>
    </p>
    <?php if ( empty( $top_handpicked ) ) : ?>
        <p class="aiad-ra-empty"><?php esc_html_e( 'No handpicked clicks recorded yet.', 'ai-awareness-day' ); ?></p>
    <?php else : ?>
        <ul class="aiad-ra-list">
            <?php foreach ( $top_handpicked as $row ) : ?>
                <li>
                    <a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
                    <span class="count"><?php echo esc_html( number_format( $row['count'] ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr class="aiad-ra-divider">
    <p style="margin:0;">
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=resource&orderby=downloads&order=desc' ) ); ?>"><?php esc_html_e( 'All resources by downloads →', 'ai-awareness-day' ); ?></a>
        &nbsp;·&nbsp;
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=resource&orderby=views&order=desc' ) ); ?>"><?php esc_html_e( 'All resources by views →', 'ai-awareness-day' ); ?></a>
        &nbsp;·&nbsp;
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=featured_resource' ) ); ?>"><?php esc_html_e( 'Handpicked resources →', 'ai-awareness-day' ); ?></a>
    </p>
    <?php
}

/**
 * Dashboard widget: AI tool outbound clicks from the homepage.
 */
function aiad_ai_tool_analytics_widget_callback(): void {
    $total_clicks = function_exists( 'aiad_sum_meta_for_post_type' )
        ? aiad_sum_meta_for_post_type( 'ai_tool', '_aiad_tool_clicks' )
        : 0;
    $top_tools = function_exists( 'aiad_get_top_posts_for_type' )
        ? aiad_get_top_posts_for_type( 'ai_tool', '_aiad_tool_clicks', 5 )
        : array();
    $tool_count = (int) ( wp_count_posts( 'ai_tool' )->publish ?? 0 );
    ?>
    <style>
        #aiad_ai_tool_analytics .aiad-at-stat__val {
            font-size: 1.7rem;
            font-weight: 800;
            line-height: 1;
            color: #1e1e1e;
        }
        #aiad_ai_tool_analytics .aiad-at-stat__lbl {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #646970;
            margin-top: 0.2rem;
            display: block;
        }
        #aiad_ai_tool_analytics .aiad-at-note {
            font-size: 0.78rem;
            color: #646970;
            margin: 0 0 0.75rem;
        }
        #aiad_ai_tool_analytics .aiad-at-section-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1e1e1e;
            margin: 0.75rem 0 0.35rem;
        }
        #aiad_ai_tool_analytics .aiad-at-list {
            list-style: none;
            margin: 0 0 0.5rem;
            padding: 0;
        }
        #aiad_ai_tool_analytics .aiad-at-list li {
            display: flex;
            justify-content: space-between;
            gap: 0.5rem;
            padding: 0.3rem 0;
            border-bottom: 1px solid #f0f0f1;
            font-size: 0.85rem;
        }
        #aiad_ai_tool_analytics .aiad-at-list li:last-child { border-bottom: none; }
        #aiad_ai_tool_analytics .aiad-at-list a {
            color: #2271b1;
            text-decoration: none;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #aiad_ai_tool_analytics .aiad-at-list .count { font-weight: 700; color: #1e1e1e; }
        #aiad_ai_tool_analytics .aiad-at-empty {
            color: #646970;
            font-size: 0.85rem;
            font-style: italic;
        }
    </style>

    <p class="aiad-at-note"><?php esc_html_e( '“Visit website” clicks on AI tool cards on the homepage. Stats count from deploy onward.', 'ai-awareness-day' ); ?></p>

    <span class="aiad-at-stat__val"><?php echo esc_html( number_format( $total_clicks ) ); ?></span>
    <span class="aiad-at-stat__lbl"><?php esc_html_e( 'Total homepage visits', 'ai-awareness-day' ); ?></span>
    <p class="aiad-at-note" style="margin-top:0.35rem;">
        <?php
        echo esc_html(
            sprintf(
                /* translators: %d: number of published AI tools */
                __( '%d published tools', 'ai-awareness-day' ),
                $tool_count
            )
        );
        ?>
    </p>

    <p class="aiad-at-section-title"><?php esc_html_e( 'Most visited tools', 'ai-awareness-day' ); ?></p>
    <?php if ( empty( $top_tools ) ) : ?>
        <p class="aiad-at-empty"><?php esc_html_e( 'No visits recorded yet.', 'ai-awareness-day' ); ?></p>
    <?php else : ?>
        <ul class="aiad-at-list">
            <?php foreach ( $top_tools as $row ) : ?>
                <li>
                    <a href="<?php echo esc_url( $row['edit'] ); ?>"><?php echo esc_html( $row['title'] ); ?></a>
                    <span class="count"><?php echo esc_html( number_format( $row['count'] ) ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p style="margin:0.5rem 0 0;">
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ai_tool' ) ); ?>"><?php esc_html_e( 'All AI tools →', 'ai-awareness-day' ); ?></a>
    </p>
    <?php
}

/**
 * Get submission counts broken down by role.
 *
 * @return array<string, int>
 */
function aiad_get_submission_breakdown(): array {
    global $wpdb;

    $roles = array(
        'teacher'       => 0,
        'school_leader' => 0,
        'parent'        => 0,
        'organisation'  => 0,
        'other'         => 0,
    );

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $results = $wpdb->get_results(
        "SELECT meta_value AS role, COUNT(*) AS cnt
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_submission_involved_as'
           AND p.post_type = 'form_submission'
           AND p.post_status = 'publish'
         GROUP BY meta_value",
        ARRAY_A
    );

    foreach ( $results as $row ) {
        $role = $row['role'];
        if ( isset( $roles[ $role ] ) ) {
            $roles[ $role ] = (int) $row['cnt'];
        } else {
            $roles['other'] += (int) $row['cnt'];
        }
    }

    return $roles;
}

/**
 * Get submission counts broken down by chase-up status.
 *
 * @return array<string, int>
 */
function aiad_get_submission_chase_breakdown(): array {
    global $wpdb;

    $defaults = array(
        'not_contacted' => 0,
        'contacted'     => 0,
        'following_up'  => 0,
        'done'          => 0,
    );

    $total_q = new WP_Query( array(
        'post_type'      => 'form_submission',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
    ) );
    $total = (int) $total_q->found_posts;
    if ( $total === 0 ) {
        return $defaults;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $results = $wpdb->get_results(
        "SELECT meta_value AS status, COUNT(*) AS cnt
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_submission_chase_status'
           AND p.post_type = 'form_submission'
           AND p.post_status = 'publish'
         GROUP BY meta_value",
        ARRAY_A
    );

    $set = 0;
    foreach ( $results as $row ) {
        $key = $row['status'];
        if ( isset( $defaults[ $key ] ) ) {
            $defaults[ $key ] = (int) $row['cnt'];
            $set += (int) $row['cnt'];
        }
    }
    // Submissions with no chase_status meta count as not_contacted (default).
    $defaults['not_contacted'] += max( 0, $total - $set );

    return $defaults;
}

/**
 * Get tallies of how many submissions ticked each checklist interest.
 *
 * @return array<string, int>  Keyed by checklist slug, value = count.
 */
function aiad_get_submission_interest_breakdown(): array {
    global $wpdb;

    if ( ! function_exists( 'aiad_get_contact_checklist_labels' ) ) {
        return array();
    }
    $labels = aiad_get_contact_checklist_labels();

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $rows = $wpdb->get_col(
        "SELECT pm.meta_value
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = '_submission_checklist'
           AND p.post_type = 'form_submission'
           AND p.post_status = 'publish'"
    );

    $counts = array_fill_keys( array_keys( $labels ), 0 );
    foreach ( $rows as $serialised ) {
        $arr = maybe_unserialize( $serialised );
        if ( ! is_array( $arr ) ) {
            continue;
        }
        foreach ( $arr as $key ) {
            if ( isset( $counts[ $key ] ) ) {
                $counts[ $key ]++;
            }
        }
    }
    arsort( $counts );
    return $counts;
}

/**
 * Get submission count for the last N days.
 *
 * @param int $days Number of days to look back.
 * @return int
 */
function aiad_get_recent_submission_count( int $days = 7 ): int {
    $args = array(
        'post_type'      => 'form_submission',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => false,
        'date_query'     => array(
            array(
                'after'     => $days . ' days ago',
                'inclusive' => true,
            ),
        ),
    );
    $q = new WP_Query( $args );
    return (int) $q->found_posts;
}

/**
 * Render the dashboard widget.
 */
function aiad_dashboard_widget_callback(): void {
    $goal       = (int) apply_filters( 'aiad_school_pledge_goal', 500 );
    $pledges    = aiad_get_school_pledge_count();
    $pct        = $goal > 0 ? min( 100, round( ( $pledges / $goal ) * 100, 1 ) ) : 0;
    $breakdown  = aiad_get_submission_breakdown();
    $total_subs = array_sum( $breakdown );
    $week_count = aiad_get_recent_submission_count( 7 );

    // Next milestone
    $milestones  = array( 10, 25, 50, 100, 250, 500, 1000 );
    $next_ms     = null;
    foreach ( $milestones as $ms ) {
        if ( $pledges < $ms ) {
            $next_ms = $ms;
            break;
        }
    }

    $role_labels = array(
        'teacher'       => '👩‍🏫 Teachers',
        'school_leader' => '🏫 School leaders',
        'parent'        => '👪 Parents',
        'organisation'  => '🏢 Organisations',
        'other'         => '👤 Other',
    );

    ?>
    <style>
        #aiad_campaign_analytics .aiad-dw-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem 1.5rem;
            margin-bottom: 1rem;
        }
        #aiad_campaign_analytics .aiad-dw-stat {
            display: flex;
            flex-direction: column;
        }
        #aiad_campaign_analytics .aiad-dw-stat__val {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            color: #1e1e1e;
        }
        #aiad_campaign_analytics .aiad-dw-stat__lbl {
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            color: #646970;
            margin-top: 0.2rem;
        }
        #aiad_campaign_analytics .aiad-dw-progress {
            height: 10px;
            background: #dcdcde;
            border-radius: 999px;
            overflow: hidden;
            margin: 0.75rem 0 0.3rem;
        }
        #aiad_campaign_analytics .aiad-dw-progress__bar {
            height: 100%;
            background: #2271b1;
            border-radius: 999px;
            transition: width 0.4s ease;
        }
        #aiad_campaign_analytics .aiad-dw-progress__label {
            font-size: 0.75rem;
            color: #646970;
            margin-bottom: 1rem;
        }
        #aiad_campaign_analytics .aiad-dw-divider {
            border: none;
            border-top: 1px solid #dcdcde;
            margin: 0.75rem 0;
        }
        #aiad_campaign_analytics .aiad-dw-section-title {
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1e1e1e;
            margin: 0 0 0.5rem;
        }
        #aiad_campaign_analytics .aiad-dw-breakdown {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }
        #aiad_campaign_analytics .aiad-dw-breakdown li {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.82rem;
        }
        #aiad_campaign_analytics .aiad-dw-breakdown__bar-wrap {
            flex: 1;
            height: 6px;
            background: #f0f0f1;
            border-radius: 999px;
            overflow: hidden;
        }
        #aiad_campaign_analytics .aiad-dw-breakdown__bar {
            height: 100%;
            background: #2271b1;
            border-radius: 999px;
        }
        #aiad_campaign_analytics .aiad-dw-breakdown__count {
            font-weight: 700;
            min-width: 2rem;
            text-align: right;
            color: #1e1e1e;
        }
        #aiad_campaign_analytics .aiad-dw-next-ms {
            background: #f0f6fc;
            border: 1px solid #c5d9ed;
            border-radius: 3px;
            padding: 0.5rem 0.75rem;
            font-size: 0.8rem;
            color: #1e1e1e;
            margin-top: 0.75rem;
        }
        #aiad_campaign_analytics .aiad-dw-next-ms strong {
            color: #2271b1;
        }
        #aiad_campaign_analytics .aiad-dw-week {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            background: <?php echo $week_count > 0 ? '#dcfce7' : '#f0f0f1'; ?>;
            color: <?php echo $week_count > 0 ? '#15803d' : '#646970'; ?>;
            border-radius: 999px;
            padding: 0.15em 0.6em;
            font-size: 0.72rem;
            font-weight: 700;
        }
    </style>

    <div class="aiad-dw-grid">
        <div class="aiad-dw-stat">
            <span class="aiad-dw-stat__val"><?php echo esc_html( number_format( $pledges ) ); ?></span>
            <span class="aiad-dw-stat__lbl">Schools pledged</span>
        </div>
        <div class="aiad-dw-stat">
            <span class="aiad-dw-stat__val"><?php echo esc_html( number_format( $total_subs ) ); ?></span>
            <span class="aiad-dw-stat__lbl">Total sign-ups
                <span class="aiad-dw-week">+<?php echo esc_html( $week_count ); ?> this week</span>
            </span>
        </div>
    </div>

    <div class="aiad-dw-progress">
        <div class="aiad-dw-progress__bar" style="width:<?php echo esc_attr( $pct ); ?>%"></div>
    </div>
    <p class="aiad-dw-progress__label">
        <strong><?php echo esc_html( $pct ); ?>%</strong> of <?php echo esc_html( number_format( $goal ) ); ?>-school goal
    </p>

    <hr class="aiad-dw-divider">
    <p class="aiad-dw-section-title">Sign-ups by role</p>
    <ul class="aiad-dw-breakdown">
        <?php foreach ( $role_labels as $key => $label ) :
            $val     = $breakdown[ $key ] ?? 0;
            $bar_pct = $total_subs > 0 ? min( 100, round( ( $val / $total_subs ) * 100 ) ) : 0;
        ?>
            <li>
                <span style="min-width:10rem;"><?php echo esc_html( $label ); ?></span>
                <div class="aiad-dw-breakdown__bar-wrap">
                    <div class="aiad-dw-breakdown__bar" style="width:<?php echo esc_attr( $bar_pct ); ?>%"></div>
                </div>
                <span class="aiad-dw-breakdown__count"><?php echo esc_html( $val ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if ( $next_ms ) :
        $needed = $next_ms - $pledges;
    ?>
        <div class="aiad-dw-next-ms">
            Next milestone: <strong><?php echo esc_html( number_format( $next_ms ) ); ?> schools</strong>
            — <?php echo esc_html( number_format( $needed ) ); ?> more pledge<?php echo $needed === 1 ? '' : 's'; ?> to go
        </div>
    <?php else : ?>
        <div class="aiad-dw-next-ms">
            🎉 All milestones reached! The campaign has exceeded 1,000 sign-ups.
        </div>
    <?php endif; ?>

    <hr class="aiad-dw-divider">
    <?php
    $chase = aiad_get_submission_chase_breakdown();
    $chase_labels = function_exists( 'aiad_submission_status_options' ) ? aiad_submission_status_options() : array(
        'not_contacted' => __( 'Not contacted', 'ai-awareness-day' ),
        'contacted'     => __( 'Contacted', 'ai-awareness-day' ),
        'following_up'  => __( 'Following up', 'ai-awareness-day' ),
        'done'          => __( 'Done', 'ai-awareness-day' ),
    );
    $chase_colors = array(
        'not_contacted' => '#b45309',
        'contacted'     => '#2563eb',
        'following_up'  => '#7c3aed',
        'done'          => '#16a34a',
    );
    $chase_total = array_sum( $chase );
    ?>
    <p class="aiad-dw-section-title"><?php esc_html_e( 'Chase-up progress', 'ai-awareness-day' ); ?></p>
    <ul class="aiad-dw-breakdown">
        <?php foreach ( $chase_labels as $key => $label ) :
            $val     = $chase[ $key ] ?? 0;
            $bar_pct = $chase_total > 0 ? min( 100, round( ( $val / $chase_total ) * 100 ) ) : 0;
            $color   = $chase_colors[ $key ] ?? '#2271b1';
            $url     = add_query_arg( array( 'post_type' => 'form_submission', 'chase_status' => $key ), admin_url( 'edit.php' ) );
        ?>
            <li>
                <span style="min-width:10rem;"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></span>
                <div class="aiad-dw-breakdown__bar-wrap">
                    <div class="aiad-dw-breakdown__bar" style="width:<?php echo esc_attr( $bar_pct ); ?>%;background:<?php echo esc_attr( $color ); ?>;"></div>
                </div>
                <span class="aiad-dw-breakdown__count"><?php echo esc_html( $val ); ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php
    $interests = aiad_get_submission_interest_breakdown();
    $checklist_labels = function_exists( 'aiad_get_contact_checklist_labels' ) ? aiad_get_contact_checklist_labels() : array();
    $interest_max = $interests ? max( $interests ) : 0;
    ?>
    <?php if ( $interests && $interest_max > 0 ) : ?>
        <hr class="aiad-dw-divider">
        <p class="aiad-dw-section-title"><?php esc_html_e( 'Interest demand', 'ai-awareness-day' ); ?></p>
        <ul class="aiad-dw-breakdown">
            <?php foreach ( $interests as $key => $val ) :
                if ( $val === 0 ) { continue; }
                $label   = $checklist_labels[ $key ] ?? $key;
                $bar_pct = $interest_max > 0 ? min( 100, round( ( $val / $interest_max ) * 100 ) ) : 0;
                $url     = add_query_arg( array( 'post_type' => 'form_submission', 'interest' => $key ), admin_url( 'edit.php' ) );
            ?>
                <li>
                    <span style="min-width:14rem;"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></span>
                    <div class="aiad-dw-breakdown__bar-wrap">
                        <div class="aiad-dw-breakdown__bar" style="width:<?php echo esc_attr( $bar_pct ); ?>%;"></div>
                    </div>
                    <span class="aiad-dw-breakdown__count"><?php echo esc_html( $val ); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <hr class="aiad-dw-divider">
    <p style="margin:0;">
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=form_submission' ) ); ?>">View all submissions →</a>
        &nbsp;·&nbsp;
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=timeline' ) ); ?>">Manage timeline →</a>
    </p>
    <?php
}
