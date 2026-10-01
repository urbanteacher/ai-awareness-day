<?php
/**
 * Export detail-page expectations for scripts/check-detail-pages.py.
 * Run with `wp eval-file scripts/list-detail-pages.php` on the target site.
 */
$posts = get_posts( array(
	'post_type'   => array( 'resource', 'timeline', 'live_session' ),
	'post_status' => 'publish',
	'numberposts' => -1,
) );
$pages = array();
foreach ( $posts as $post ) {
	$pages[] = array(
		'url'     => get_permalink( $post ),
		'title'   => $post->post_title,
		// Some reports deliberately use an editorial headline via the_title.
		'heading' => get_the_title( $post ),
		'type'    => $post->post_type,
	);
}
echo wp_json_encode( $pages );
