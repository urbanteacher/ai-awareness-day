<?php
/**
 * Engagement tracking: clicks, shares, views (posts & timeline), dashboard widget.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Post types included in timeline/blog content engagement analytics. */
function aiad_engagement_post_types(): array {
	return array( 'timeline', 'post' );
}

/**
 * Meta keys registered per post type.
 *
 * @return array<string,array<string,array{type:string,default:int}>>
 */
function aiad_engagement_meta_registry(): array {
	return array(
		'timeline'     => array(
			'_aiad_engagement_clicks' => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_engagement_shares' => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_engagement_views'  => array( 'type' => 'integer', 'default' => 0 ),
		),
		'post'         => array(
			'_aiad_engagement_clicks' => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_engagement_shares' => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_engagement_views'  => array( 'type' => 'integer', 'default' => 0 ),
		),
		'live_session' => array(
			'_aiad_session_clicks'   => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_session_joins'    => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_session_calendar' => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_session_shares'   => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_session_views'    => array( 'type' => 'integer', 'default' => 0 ),
		),
		'partner'           => array(
			'_aiad_partner_ai_clicks'      => array( 'type' => 'integer', 'default' => 0 ),
			'_aiad_partner_marquee_clicks' => array( 'type' => 'integer', 'default' => 0 ),
		),
		'featured_resource' => array(
			'_aiad_featured_resource_clicks' => array( 'type' => 'integer', 'default' => 0 ),
		),
		'ai_tool'           => array(
			'_aiad_tool_clicks' => array( 'type' => 'integer', 'default' => 0 ),
		),
	);
}

/**
 * Register engagement meta for timeline, blog, live sessions, and partners.
 */
function aiad_register_engagement_meta(): void {
	foreach ( aiad_engagement_meta_registry() as $post_type => $fields ) {
		foreach ( $fields as $key => $args ) {
			register_post_meta(
				$post_type,
				$key,
				array(
					'type'              => $args['type'],
					'single'            => true,
					'default'           => $args['default'],
					'show_in_rest'      => true,
					'sanitize_callback' => 'absint',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
add_action( 'init', 'aiad_register_engagement_meta', 20 );

/**
 * Meta key for a post type + event.
 *
 * @param string $post_type Post type.
 * @param string $event     Event slug.
 * @return string|null
 */
function aiad_engagement_event_meta_key( string $post_type, string $event ): ?string {
	$map = array(
		'timeline'     => array(
			'click' => '_aiad_engagement_clicks',
			'share' => '_aiad_engagement_shares',
			'view'  => '_aiad_engagement_views',
		),
		'post'         => array(
			'click' => '_aiad_engagement_clicks',
			'share' => '_aiad_engagement_shares',
			'view'  => '_aiad_engagement_views',
		),
		'live_session' => array(
			'click'    => '_aiad_session_clicks',
			'join'     => '_aiad_session_joins',
			'calendar' => '_aiad_session_calendar',
			'share'    => '_aiad_session_shares',
			'view'     => '_aiad_session_views',
		),
		'partner'           => array(
			'click'   => '_aiad_partner_ai_clicks',
			'marquee' => '_aiad_partner_marquee_clicks',
		),
		'featured_resource' => array(
			'click' => '_aiad_featured_resource_clicks',
		),
		'ai_tool'           => array(
			'click' => '_aiad_tool_clicks',
		),
	);
	if ( ! isset( $map[ $post_type ][ $event ] ) ) {
		return null;
	}
	return $map[ $post_type ][ $event ];
}

/**
 * Increment a numeric post meta counter.
 *
 * @param int    $post_id   Post ID.
 * @param string $meta_key  Meta key.
 * @return int New count.
 */
function aiad_increment_engagement_meta( int $post_id, string $meta_key ): int {
	$count = (int) get_post_meta( $post_id, $meta_key, true );
	++$count;
	update_post_meta( $post_id, $meta_key, $count );
	return $count;
}

/**
 * Resolve a on-site post ID from a URL (blog article, timeline, resource, etc.).
 *
 * @param string $url Absolute URL.
 * @return int Post ID or 0.
 */
function aiad_engagement_post_id_from_url( string $url ): int {
	$url = esc_url_raw( $url );
	if ( $url === '' ) {
		return 0;
	}
	$post_id = (int) url_to_postid( $url );
	if ( $post_id > 0 ) {
		return $post_id;
	}
	$home = home_url( '/' );
	if ( strpos( $url, $home ) !== 0 ) {
		return 0;
	}
	$path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
	if ( $path === '' ) {
		return 0;
	}
	$slug = basename( $path );
	if ( $slug === '' ) {
		return 0;
	}
	foreach ( array_merge( aiad_engagement_post_types(), array( 'resource', 'page', 'live_session' ) ) as $post_type ) {
		$post = get_page_by_path( $slug, OBJECT, $post_type );
		if ( $post instanceof WP_Post ) {
			return (int) $post->ID;
		}
	}
	return 0;
}

/**
 * Whether a post can receive engagement stats.
 *
 * @param int    $post_id Post ID.
 * @param string $event   Event slug.
 */
function aiad_engagement_is_trackable_post( int $post_id, string $event = '' ): bool {
	$post = get_post( $post_id );
	if ( ! $post || $post->post_status !== 'publish' ) {
		return false;
	}
	if ( $post->post_type === 'partner' ) {
		if ( $event === 'marquee' ) {
			return aiad_engagement_event_meta_key( 'partner', 'marquee' ) !== null;
		}
		if ( $event !== 'click' || get_post_meta( $post_id, '_partner_provides_ai_resources', true ) !== '1' ) {
			return false;
		}
		return aiad_engagement_event_meta_key( 'partner', 'click' ) !== null;
	}
	if ( $post->post_type === 'featured_resource' || $post->post_type === 'ai_tool' ) {
		return $event === 'click' && aiad_engagement_event_meta_key( $post->post_type, 'click' ) !== null;
	}
	if ( $post->post_type === 'live_session' ) {
		return $event !== '' && aiad_engagement_event_meta_key( 'live_session', $event ) !== null;
	}
	if ( in_array( $post->post_type, aiad_engagement_post_types(), true ) ) {
		return $event === '' || aiad_engagement_event_meta_key( $post->post_type, $event ) !== null;
	}
	return false;
}

/**
 * AJAX: track click, share, or view.
 */
function aiad_ajax_track_engagement(): void {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'aiad_engagement_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'ai-awareness-day' ) ) );
	}

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$event   = isset( $_POST['event'] ) ? sanitize_key( wp_unslash( $_POST['event'] ) ) : '';

	if ( $event === 'hero_partners_stat' ) {
		$count = (int) get_option( 'aiad_hero_partners_stat_clicks', 0 );
		++$count;
		update_option( 'aiad_hero_partners_stat_clicks', $count, false );
		wp_send_json_success( array( 'count' => $count ) );
	}

	if ( ! $post_id || ! aiad_engagement_is_trackable_post( $post_id, $event ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid content.', 'ai-awareness-day' ) ) );
	}

	$post      = get_post( $post_id );
	$meta_key  = $post ? aiad_engagement_event_meta_key( $post->post_type, $event ) : null;
	if ( ! $meta_key ) {
		wp_send_json_error( array( 'message' => __( 'Invalid event.', 'ai-awareness-day' ) ) );
	}

	if ( $event === 'view' ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( $ip !== '' ) {
			$key = 'aiad_engagement_viewed_' . md5( $ip . $post_id . $event );
			if ( get_transient( $key ) ) {
				wp_send_json_success( array( 'count' => (int) get_post_meta( $post_id, $meta_key, true ), 'skipped' => true ) );
			}
			set_transient( $key, true, 6 * HOUR_IN_SECONDS );
		}
	}

	$count = aiad_increment_engagement_meta( $post_id, $meta_key );

	// Clicks on outbound links: also credit the destination article when it is on this site.
	if ( $event === 'click' && ! empty( $_POST['target_url'] ) && $post && in_array( $post->post_type, aiad_engagement_post_types(), true ) ) {
		$target_id = aiad_engagement_post_id_from_url( (string) wp_unslash( $_POST['target_url'] ) );
		if ( $target_id > 0 && $target_id !== $post_id && aiad_engagement_is_trackable_post( $target_id, 'click' ) ) {
			$target_key = aiad_engagement_event_meta_key( get_post_type( $target_id ), 'click' );
			if ( $target_key ) {
				aiad_increment_engagement_meta( $target_id, $target_key );
			}
		}
	}

	wp_send_json_success( array( 'count' => $count ) );
}
add_action( 'wp_ajax_aiad_track_engagement', 'aiad_ajax_track_engagement' );
add_action( 'wp_ajax_nopriv_aiad_track_engagement', 'aiad_ajax_track_engagement' );

/**
 * Top published posts by meta key across engagement post types.
 *
 * @param string $meta_key Meta key.
 * @param int    $limit    Max rows.
 * @return array<int,array{id:int,title:string,count:int,type:string,type_label:string,edit:string,url:string}>
 */
function aiad_get_top_engagement_posts( string $meta_key, int $limit = 5 ): array {
	$q = new WP_Query(
		array(
			'post_type'      => aiad_engagement_post_types(),
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
		)
	);

	$out = array();
	foreach ( $q->posts as $p ) {
		$type_obj = get_post_type_object( $p->post_type );
		$out[]    = array(
			'id'          => (int) $p->ID,
			'title'       => get_the_title( $p ),
			'count'       => (int) get_post_meta( $p->ID, $meta_key, true ),
			'type'        => $p->post_type,
			'type_label'  => $type_obj ? $type_obj->labels->singular_name : $p->post_type,
			'edit'        => (string) get_edit_post_link( $p->ID ),
			'url'         => (string) get_permalink( $p ),
		);
	}
	return $out;
}

/**
 * Top posts for one post type by meta key.
 *
 * @param string $post_type Post type slug.
 * @param string $meta_key  Meta key.
 * @param int    $limit     Max rows.
 * @return array<int,array{id:int,title:string,count:int,type_label:string,edit:string}>
 */
function aiad_get_top_posts_for_type( string $post_type, string $meta_key, int $limit = 5 ): array {
	$q = new WP_Query(
		array(
			'post_type'      => $post_type,
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
		)
	);
	$type_obj = get_post_type_object( $post_type );
	$label    = $type_obj ? $type_obj->labels->singular_name : $post_type;
	$out      = array();
	foreach ( $q->posts as $p ) {
		$out[] = array(
			'id'         => (int) $p->ID,
			'title'      => get_the_title( $p ),
			'count'      => (int) get_post_meta( $p->ID, $meta_key, true ),
			'type_label' => $label,
			'edit'       => (string) get_edit_post_link( $p->ID ),
		);
	}
	return $out;
}

/**
 * Sum meta for a single post type.
 *
 * @param string $post_type Post type.
 * @param string $meta_key  Meta key.
 */
function aiad_sum_meta_for_post_type( string $post_type, string $meta_key ): int {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)),0)
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type = %s
			   AND p.post_status = 'publish'",
			$meta_key,
			$post_type
		)
	);
}

/**
 * Sum a meta key across timeline posts (hearts use dedicated like meta).
 *
 * @param string $meta_key Meta key.
 * @return int
 */
function aiad_sum_timeline_meta( string $meta_key ): int {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)),0)
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type = 'timeline'
			   AND p.post_status = 'publish'",
			$meta_key
		)
	);
}

/**
 * Sum engagement meta across timeline + posts.
 *
 * @param string $meta_key Meta key.
 * @return int
 */
function aiad_sum_engagement_meta( string $meta_key ): int {
	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(SUM(CAST(pm.meta_value AS UNSIGNED)),0)
			 FROM {$wpdb->postmeta} pm
			 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
			 WHERE pm.meta_key = %s
			   AND p.post_type IN ('timeline', 'post')
			   AND p.post_status = 'publish'",
			$meta_key
		)
	);
}

/**
 * Top AI-resource partners by outbound click count.
 *
 * @param int $limit Max rows.
 * @return array<int,array{id:int,title:string,count:int,type_label:string,edit:string}>
 */
function aiad_get_top_ai_resource_partners( int $limit = 5 ): array {
	$q = new WP_Query(
		array(
			'post_type'      => 'partner',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'meta_key'       => '_aiad_partner_ai_clicks',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'     => '_aiad_partner_ai_clicks',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
				array(
					'key'   => '_partner_provides_ai_resources',
					'value' => '1',
				),
			),
		)
	);
	$out = array();
	foreach ( $q->posts as $p ) {
		$out[] = array(
			'id'         => (int) $p->ID,
			'title'      => get_the_title( $p ),
			'count'      => (int) get_post_meta( $p->ID, '_aiad_partner_ai_clicks', true ),
			'type_label' => __( 'Partner', 'ai-awareness-day' ),
			'edit'       => (string) get_edit_post_link( $p->ID ),
		);
	}
	return $out;
}

/**
 * Register dashboard widget.
 */
function aiad_register_engagement_dashboard_widget(): void {
	wp_add_dashboard_widget(
		'aiad_content_engagement',
		__( '📰 Content engagement — Clicks, hearts & shares', 'ai-awareness-day' ),
		'aiad_content_engagement_widget_callback'
	);
	wp_add_dashboard_widget(
		'aiad_schedule_engagement',
		__( '📅 Live schedule — Joins & session clicks', 'ai-awareness-day' ),
		'aiad_schedule_engagement_widget_callback'
	);
	wp_add_dashboard_widget(
		'aiad_partner_engagement',
		__( '🤝 Partner analytics — AI resources', 'ai-awareness-day' ),
		'aiad_partner_engagement_widget_callback'
	);
	wp_add_dashboard_widget(
		'aiad_homepage_outbound',
		__( '🏠 Homepage — Handpicked, AI tools & hero partners', 'ai-awareness-day' ),
		'aiad_homepage_outbound_widget_callback'
	);
}
add_action( 'wp_dashboard_setup', 'aiad_register_engagement_dashboard_widget', 15 );

/**
 * Render engagement lists (shared markup).
 *
 * @param array<int,array<string,mixed>> $rows    Rows from aiad_get_top_engagement_posts.
 * @param string                         $suffix  Count suffix label.
 */
function aiad_engagement_render_list( array $rows, string $suffix ): void {
	if ( empty( $rows ) ) {
		echo '<p class="aiad-ce-empty">' . esc_html__( 'No data yet.', 'ai-awareness-day' ) . '</p>';
		return;
	}
	echo '<ul class="aiad-ce-list">';
	foreach ( $rows as $row ) {
		echo '<li>';
		echo '<a href="' . esc_url( $row['edit'] ) . '">' . esc_html( $row['title'] ) . '</a>';
		echo '<span class="aiad-ce-meta">' . esc_html( $row['type_label'] ) . '</span>';
		echo '<span class="aiad-ce-count">' . esc_html( number_format( $row['count'] ) ) . ' ' . esc_html( $suffix ) . '</span>';
		echo '</li>';
	}
	echo '</ul>';
}

/**
 * Dashboard widget: timeline & blog engagement.
 */
function aiad_content_engagement_widget_callback(): void {
	$total_hearts  = aiad_sum_timeline_meta( '_aiad_timeline_like_count' );
	$total_clicks  = aiad_sum_engagement_meta( '_aiad_engagement_clicks' );
	$total_shares  = aiad_sum_engagement_meta( '_aiad_engagement_shares' );
	$total_views   = aiad_sum_engagement_meta( '_aiad_engagement_views' );

	$top_clicks = aiad_get_top_engagement_posts( '_aiad_engagement_clicks', 5 );
	$top_shares = aiad_get_top_engagement_posts( '_aiad_engagement_shares', 5 );
	$top_views  = aiad_get_top_engagement_posts( '_aiad_engagement_views', 5 );
	?>
	<style>
		#aiad_content_engagement .aiad-ce-grid {
			display: grid;
			grid-template-columns: repeat(4, 1fr);
			gap: 0.5rem 0.75rem;
			margin-bottom: 0.75rem;
		}
		#aiad_content_engagement .aiad-ce-stat__val {
			font-size: 1.35rem;
			font-weight: 800;
			line-height: 1;
			color: #1e1e1e;
		}
		#aiad_content_engagement .aiad-ce-stat__lbl {
			font-size: 0.65rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #646970;
			display: block;
			margin-top: 0.15rem;
		}
		#aiad_content_engagement .aiad-ce-section-title {
			font-size: 0.68rem;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			color: #1e1e1e;
			margin: 0.75rem 0 0.35rem;
		}
		#aiad_content_engagement .aiad-ce-list {
			list-style: none;
			margin: 0 0 0.5rem;
			padding: 0;
		}
		#aiad_content_engagement .aiad-ce-list li {
			display: grid;
			grid-template-columns: 1fr auto auto;
			gap: 0.35rem 0.5rem;
			align-items: baseline;
			padding: 0.3rem 0;
			border-bottom: 1px solid #f0f0f1;
			font-size: 0.85rem;
		}
		#aiad_content_engagement .aiad-ce-list li:last-child { border-bottom: none; }
		#aiad_content_engagement .aiad-ce-list a {
			color: #2271b1;
			text-decoration: none;
			overflow: hidden;
			text-overflow: ellipsis;
			white-space: nowrap;
		}
		#aiad_content_engagement .aiad-ce-meta {
			font-size: 0.72rem;
			color: #646970;
		}
		#aiad_content_engagement .aiad-ce-count {
			font-weight: 700;
			color: #1e1e1e;
			font-size: 0.8rem;
			white-space: nowrap;
		}
		#aiad_content_engagement .aiad-ce-empty {
			color: #646970;
			font-size: 0.85rem;
			font-style: italic;
			margin: 0;
		}
		#aiad_content_engagement .aiad-ce-note {
			font-size: 0.78rem;
			color: #646970;
			margin: 0 0 0.5rem;
		}
		@media (max-width: 782px) {
			#aiad_content_engagement .aiad-ce-grid { grid-template-columns: 1fr 1fr; }
		}
	</style>

	<p class="aiad-ce-note"><?php esc_html_e( 'Timeline updates and blog posts. Hearts are timeline only; clicks include “Learn more” and article links.', 'ai-awareness-day' ); ?></p>

	<div class="aiad-ce-grid">
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_clicks ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Clicks', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_hearts ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Hearts', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_shares ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Shares', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_views ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Views', 'ai-awareness-day' ); ?></span>
		</div>
	</div>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most clicked', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_clicks, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most hearts', 'ai-awareness-day' ); ?></p>
	<?php
	// Hearts only exist on timeline; query timeline like meta explicitly.
	$heart_q = new WP_Query(
		array(
			'post_type'      => 'timeline',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'meta_key'       => '_aiad_timeline_like_count',
			'orderby'        => 'meta_value_num',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_aiad_timeline_like_count',
					'value'   => 0,
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		)
	);
	$heart_rows = array();
	foreach ( $heart_q->posts as $p ) {
		$heart_rows[] = array(
			'id'         => (int) $p->ID,
			'title'      => get_the_title( $p ),
			'count'      => (int) get_post_meta( $p->ID, '_aiad_timeline_like_count', true ),
			'type_label' => __( 'Timeline', 'ai-awareness-day' ),
			'edit'       => (string) get_edit_post_link( $p->ID ),
		);
	}
	if ( empty( $heart_rows ) ) {
		echo '<p class="aiad-ce-empty">' . esc_html__( 'No hearts yet.', 'ai-awareness-day' ) . '</p>';
	} else {
		echo '<ul class="aiad-ce-list">';
		foreach ( $heart_rows as $row ) {
			echo '<li>';
			echo '<a href="' . esc_url( $row['edit'] ) . '">' . esc_html( $row['title'] ) . '</a>';
			echo '<span class="aiad-ce-meta">' . esc_html( $row['type_label'] ) . '</span>';
			echo '<span class="aiad-ce-count">' . esc_html( number_format( $row['count'] ) ) . ' ' . esc_html__( 'hearts', 'ai-awareness-day' ) . '</span>';
			echo '</li>';
		}
		echo '</ul>';
	}
	?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most shared', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_shares, __( 'shares', 'ai-awareness-day' ) ); ?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most viewed articles', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_views, __( 'views', 'ai-awareness-day' ) ); ?>

	<p style="margin:0.5rem 0 0;">
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=timeline' ) ); ?>"><?php esc_html_e( 'All timeline posts →', 'ai-awareness-day' ); ?></a>
		&nbsp;·&nbsp;
		<a href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>"><?php esc_html_e( 'All blog posts →', 'ai-awareness-day' ); ?></a>
	</p>
	<?php
}

/**
 * Count partners flagged as providing AI resources.
 */
function aiad_count_ai_resource_partners(): int {
	$q = new WP_Query(
		array(
			'post_type'              => 'partner',
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => false,
			'update_post_meta_cache' => false,
			'meta_query'             => array(
				array(
					'key'   => '_partner_provides_ai_resources',
					'value' => '1',
				),
			),
		)
	);
	return (int) $q->found_posts;
}

/**
 * Dashboard widget: live schedule sessions.
 */
function aiad_schedule_engagement_widget_callback(): void {
	$total_joins    = aiad_sum_meta_for_post_type( 'live_session', '_aiad_session_joins' );
	$total_clicks   = aiad_sum_meta_for_post_type( 'live_session', '_aiad_session_clicks' );
	$total_calendar = aiad_sum_meta_for_post_type( 'live_session', '_aiad_session_calendar' );
	$total_shares   = aiad_sum_meta_for_post_type( 'live_session', '_aiad_session_shares' );

	$top_joins  = aiad_get_top_posts_for_type( 'live_session', '_aiad_session_joins', 5 );
	$top_clicks = aiad_get_top_posts_for_type( 'live_session', '_aiad_session_clicks', 5 );
	?>
	<style>
		#aiad_schedule_engagement .aiad-ce-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 0.5rem 0.75rem;
			margin-bottom: 0.75rem;
		}
		#aiad_schedule_engagement .aiad-ce-stat__val {
			font-size: 1.35rem;
			font-weight: 800;
			line-height: 1;
		}
		#aiad_schedule_engagement .aiad-ce-stat__lbl {
			font-size: 0.65rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #646970;
			display: block;
			margin-top: 0.15rem;
		}
		#aiad_schedule_engagement .aiad-ce-section-title {
			font-size: 0.68rem;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			margin: 0.75rem 0 0.35rem;
		}
		#aiad_schedule_engagement .aiad-ce-list {
			list-style: none;
			margin: 0 0 0.5rem;
			padding: 0;
		}
		#aiad_schedule_engagement .aiad-ce-list li {
			display: grid;
			grid-template-columns: 1fr auto auto;
			gap: 0.35rem 0.5rem;
			padding: 0.3rem 0;
			border-bottom: 1px solid #f0f0f1;
			font-size: 0.85rem;
		}
		#aiad_schedule_engagement .aiad-ce-list li:last-child { border-bottom: none; }
		#aiad_schedule_engagement .aiad-ce-list a { color: #2271b1; text-decoration: none; }
		#aiad_schedule_engagement .aiad-ce-meta { font-size: 0.72rem; color: #646970; }
		#aiad_schedule_engagement .aiad-ce-count { font-weight: 700; white-space: nowrap; }
		#aiad_schedule_engagement .aiad-ce-note { font-size: 0.78rem; color: #646970; margin: 0 0 0.5rem; }
	</style>

	<p class="aiad-ce-note"><?php esc_html_e( 'Homepage schedule spotlight and full schedule archive. Stats count from deploy onward.', 'ai-awareness-day' ); ?></p>

	<div class="aiad-ce-grid">
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_joins ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Join clicks', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_clicks ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Session clicks', 'ai-awareness-day' ); ?></span>
		</div>
	</div>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most joined sessions', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_joins, __( 'joins', 'ai-awareness-day' ) ); ?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most clicked sessions', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_clicks, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p style="margin:0.5rem 0 0;font-size:0.85rem;">
		<?php esc_html_e( 'Calendar adds:', 'ai-awareness-day' ); ?>
		<strong><?php echo esc_html( number_format( $total_calendar ) ); ?></strong>
		&nbsp;·&nbsp;
		<?php esc_html_e( 'Session shares:', 'ai-awareness-day' ); ?>
		<strong><?php echo esc_html( number_format( $total_shares ) ); ?></strong>
	</p>
	<p style="margin:0.35rem 0 0;">
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=live_session' ) ); ?>"><?php esc_html_e( 'All live sessions →', 'ai-awareness-day' ); ?></a>
	</p>
	<?php
}

/**
 * Dashboard widget: partner AI resource clicks (Reach section).
 */
function aiad_partner_engagement_widget_callback(): void {
	$total_clicks  = aiad_sum_meta_for_post_type( 'partner', '_aiad_partner_ai_clicks' );
	$partner_count = aiad_count_ai_resource_partners();
	$top_partners  = aiad_get_top_ai_resource_partners( 5 );
	$with_clicks   = 0;
	foreach ( $top_partners as $row ) {
		if ( $row['count'] > 0 ) {
			++$with_clicks;
		}
	}
	?>
	<style>
		#aiad_partner_engagement .aiad-ce-stat__val {
			font-size: 1.7rem;
			font-weight: 800;
			line-height: 1;
		}
		#aiad_partner_engagement .aiad-ce-stat__lbl {
			font-size: 0.65rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #646970;
			display: block;
			margin-top: 0.15rem;
		}
		#aiad_partner_engagement .aiad-ce-note {
			font-size: 0.78rem;
			color: #646970;
			margin: 0 0 0.75rem;
		}
		#aiad_partner_engagement .aiad-ce-section-title {
			font-size: 0.68rem;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			margin: 0.75rem 0 0.35rem;
		}
		#aiad_partner_engagement .aiad-ce-list {
			list-style: none;
			margin: 0 0 0.5rem;
			padding: 0;
		}
		#aiad_partner_engagement .aiad-ce-list li {
			display: grid;
			grid-template-columns: 1fr auto auto;
			gap: 0.35rem 0.5rem;
			padding: 0.3rem 0;
			border-bottom: 1px solid #f0f0f1;
			font-size: 0.85rem;
		}
		#aiad_partner_engagement .aiad-ce-list li:last-child { border-bottom: none; }
		#aiad_partner_engagement .aiad-ce-list a { color: #2271b1; text-decoration: none; }
		#aiad_partner_engagement .aiad-ce-meta { font-size: 0.72rem; color: #646970; }
		#aiad_partner_engagement .aiad-ce-count { font-weight: 700; white-space: nowrap; }
	</style>

	<p class="aiad-ce-note"><?php esc_html_e( 'Clicks on partner cards in the Reach section that link to AI learning resources. More partner metrics can be added here later.', 'ai-awareness-day' ); ?></p>

	<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $total_clicks ) ); ?></span>
	<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Total AI resource clicks', 'ai-awareness-day' ); ?></span>
	<p class="aiad-ce-note" style="margin-top:0.35rem;">
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: partners with AI resources flag, 2: partners with at least one tracked click */
				__( '%1$d partners offer AI resources · %2$d with clicks recorded', 'ai-awareness-day' ),
				$partner_count,
				$with_clicks
			)
		);
		?>
	</p>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most clicked AI resource partners', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_partners, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p style="margin:0.5rem 0 0;">
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=partner' ) ); ?>"><?php esc_html_e( 'All partners →', 'ai-awareness-day' ); ?></a>
	</p>
	<?php
}

/**
 * Dashboard widget: homepage handpicked resources, AI tools, hero partner marquee.
 */
function aiad_homepage_outbound_widget_callback(): void {
	$featured_total  = aiad_sum_meta_for_post_type( 'featured_resource', '_aiad_featured_resource_clicks' );
	$tools_total     = aiad_sum_meta_for_post_type( 'ai_tool', '_aiad_tool_clicks' );
	$marquee_total   = aiad_sum_meta_for_post_type( 'partner', '_aiad_partner_marquee_clicks' );
	$partners_stat   = (int) get_option( 'aiad_hero_partners_stat_clicks', 0 );
	$top_featured    = aiad_get_top_posts_for_type( 'featured_resource', '_aiad_featured_resource_clicks', 5 );
	$top_tools       = aiad_get_top_posts_for_type( 'ai_tool', '_aiad_tool_clicks', 5 );
	$top_marquee     = aiad_get_top_posts_for_type( 'partner', '_aiad_partner_marquee_clicks', 5 );
	?>
	<style>
		#aiad_homepage_outbound .aiad-ce-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 0.5rem 0.75rem;
			margin-bottom: 0.75rem;
		}
		#aiad_homepage_outbound .aiad-ce-stat__val {
			font-size: 1.35rem;
			font-weight: 800;
			line-height: 1;
		}
		#aiad_homepage_outbound .aiad-ce-stat__lbl {
			font-size: 0.65rem;
			font-weight: 700;
			letter-spacing: 0.06em;
			text-transform: uppercase;
			color: #646970;
			display: block;
			margin-top: 0.15rem;
		}
		#aiad_homepage_outbound .aiad-ce-note {
			font-size: 0.78rem;
			color: #646970;
			margin: 0 0 0.5rem;
		}
		#aiad_homepage_outbound .aiad-ce-section-title {
			font-size: 0.68rem;
			font-weight: 700;
			letter-spacing: 0.08em;
			text-transform: uppercase;
			margin: 0.75rem 0 0.35rem;
		}
		#aiad_homepage_outbound .aiad-ce-list {
			list-style: none;
			margin: 0 0 0.5rem;
			padding: 0;
		}
		#aiad_homepage_outbound .aiad-ce-list li {
			display: grid;
			grid-template-columns: 1fr auto auto;
			gap: 0.35rem 0.5rem;
			padding: 0.3rem 0;
			border-bottom: 1px solid #f0f0f1;
			font-size: 0.85rem;
		}
		#aiad_homepage_outbound .aiad-ce-list li:last-child { border-bottom: none; }
		#aiad_homepage_outbound .aiad-ce-list a { color: #2271b1; text-decoration: none; }
		#aiad_homepage_outbound .aiad-ce-meta { font-size: 0.72rem; color: #646970; }
		#aiad_homepage_outbound .aiad-ce-count { font-weight: 700; white-space: nowrap; }
		#aiad_homepage_outbound .aiad-ce-empty {
			color: #646970;
			font-size: 0.85rem;
			font-style: italic;
			margin: 0;
		}
	</style>

	<p class="aiad-ce-note"><?php esc_html_e( 'Front page only. Handpicked cards track outbound clicks; AI tools track “Visit website”; hero marquee tracks logo clicks; the Partners stat tracks “see all partners”.', 'ai-awareness-day' ); ?></p>

	<div class="aiad-ce-grid">
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $featured_total ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Handpicked clicks', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $tools_total ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'AI tool visits', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $marquee_total ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Hero logo clicks', 'ai-awareness-day' ); ?></span>
		</div>
		<div>
			<span class="aiad-ce-stat__val"><?php echo esc_html( number_format( $partners_stat ) ); ?></span>
			<span class="aiad-ce-stat__lbl"><?php esc_html_e( 'Partners stat clicks', 'ai-awareness-day' ); ?></span>
		</div>
	</div>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most clicked handpicked resources', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_featured, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most visited AI tools', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_tools, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p class="aiad-ce-section-title"><?php esc_html_e( 'Most clicked hero partner logos', 'ai-awareness-day' ); ?></p>
	<?php aiad_engagement_render_list( $top_marquee, __( 'clicks', 'ai-awareness-day' ) ); ?>

	<p style="margin:0.5rem 0 0;font-size:0.85rem;">
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=featured_resource' ) ); ?>"><?php esc_html_e( 'Handpicked resources →', 'ai-awareness-day' ); ?></a>
		&nbsp;·&nbsp;
		<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=ai_tool' ) ); ?>"><?php esc_html_e( 'AI tools →', 'ai-awareness-day' ); ?></a>
	</p>
	<?php
}
