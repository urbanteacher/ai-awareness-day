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
 * Register the dashboard widget.
 */
function aiad_register_dashboard_widget(): void {
    wp_add_dashboard_widget(
        'aiad_campaign_analytics',
        __( '📊 AI Awareness Day — Campaign Analytics', 'ai-awareness-day' ),
        'aiad_dashboard_widget_callback'
    );
}
add_action( 'wp_dashboard_setup', 'aiad_register_dashboard_widget' );

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
    <p style="margin:0;">
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=form_submission' ) ); ?>">View all submissions →</a>
        &nbsp;·&nbsp;
        <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=timeline' ) ); ?>">Manage timeline →</a>
    </p>
    <?php
}
