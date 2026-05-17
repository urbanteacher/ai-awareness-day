<?php
/**
 * Engagement tracking: clicks, shares, views (posts & timeline), dashboard widget.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Post types included in content engagement analytics. */
function aiad_engagement_post_types(): array {
	return array( 'timeline', 'post' );
}

/**
 * Register engagement meta for timeline and blog posts.
 */
function aiad_register_engagement_meta(): void {
	$fields = array(
		'_aiad_engagement_clicks'  => array( 'type' => 'integer', 'default' => 0 ),
		'_aiad_engagement_shares'  => array( 'type' => 'integer', 'default' => 0 ),
		'_aiad_engagement_views'   => array( 'type' => 'integer', 'default' => 0 ),
	);

	foreach ( aiad_engagement_post_types() as $post_type ) {
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
	foreach ( array_merge( aiad_engagement_post_types(), array( 'resource', 'page' ) ) as $post_type ) {
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
 * @param int $post_id Post ID.
 */
function aiad_engagement_is_trackable_post( int $post_id ): bool {
	$post = get_post( $post_id );
	if ( ! $post || $post->post_status !== 'publish' ) {
		return false;
	}
	return in_array( $post->post_type, aiad_engagement_post_types(), true );
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

	if ( ! $post_id || ! aiad_engagement_is_trackable_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid content.', 'ai-awareness-day' ) ) );
	}

	$meta_map = array(
		'click' => '_aiad_engagement_clicks',
		'share' => '_aiad_engagement_shares',
		'view'  => '_aiad_engagement_views',
	);

	if ( ! isset( $meta_map[ $event ] ) ) {
		wp_send_json_error( array( 'message' => __( 'Invalid event.', 'ai-awareness-day' ) ) );
	}

	if ( $event === 'view' ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( $ip !== '' ) {
			$key = 'aiad_engagement_viewed_' . md5( $ip . $post_id );
			if ( get_transient( $key ) ) {
				wp_send_json_success( array( 'count' => (int) get_post_meta( $post_id, $meta_map['view'], true ), 'skipped' => true ) );
			}
			set_transient( $key, true, 6 * HOUR_IN_SECONDS );
		}
	}

	$count = aiad_increment_engagement_meta( $post_id, $meta_map[ $event ] );

	// Clicks on outbound links: also credit the destination article when it is on this site.
	if ( $event === 'click' && ! empty( $_POST['target_url'] ) ) {
		$target_id = aiad_engagement_post_id_from_url( (string) wp_unslash( $_POST['target_url'] ) );
		if ( $target_id > 0 && $target_id !== $post_id && aiad_engagement_is_trackable_post( $target_id ) ) {
			aiad_increment_engagement_meta( $target_id, '_aiad_engagement_clicks' );
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
 * Register dashboard widget.
 */
function aiad_register_engagement_dashboard_widget(): void {
	wp_add_dashboard_widget(
		'aiad_content_engagement',
		__( '📰 Content engagement — Clicks, hearts & shares', 'ai-awareness-day' ),
		'aiad_content_engagement_widget_callback'
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
