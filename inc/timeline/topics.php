<?php
/**
 * Campaign update topics.
 *
 * The filter pills used to be post types: Announcement, New Resource, New
 * Partner, Sign-up. Those describe the format of an update, and ten of every
 * twelve were "Announcement", so the row sorted almost nothing. Teachers browse
 * by what an update is about, so the pills are now topics.
 *
 * Topics are terms in the existing timeline_category taxonomy (shown as
 * "Topics" in the editor), so they get the core admin UI, the editor panel and
 * the admin column for free. The card badge already prefers this taxonomy's
 * name (aiad_timeline_featured_badge_label()), so a tagged update shows its
 * topic instead of "ANNOUNCEMENT" with no other change.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The topics, in pill order: slug => name.
 *
 * @return array<string, string>
 */
function aiad_timeline_topics(): array {
	return array(
		'teaching-learning'   => __( 'Teaching & learning', 'ai-awareness-day' ),
		'training-cpd'        => __( 'Training & CPD', 'ai-awareness-day' ),
		'policy-regulation'   => __( 'Policy & regulation', 'ai-awareness-day' ),
		'safety-wellbeing'    => __( 'Safety & wellbeing', 'ai-awareness-day' ),
		'skills-careers'      => __( 'Skills & careers', 'ai-awareness-day' ),
		'research-insight'    => __( 'Research & insight', 'ai-awareness-day' ),
		'student-voice'       => __( 'Student voice', 'ai-awareness-day' ),
		'classroom-resources' => __( 'Classroom resources', 'ai-awareness-day' ),
		'campaign-news'       => __( 'Campaign news', 'ai-awareness-day' ),
	);
}

/**
 * Topics that have at least one published update, in pill order: slug => name.
 *
 * Empty until the terms exist, so callers fall back to the old type pills
 * rather than showing a row of filters that return nothing.
 *
 * @return array<string, string>
 */
function aiad_timeline_topic_options(): array {
	if ( ! taxonomy_exists( 'timeline_category' ) ) {
		return array();
	}
	$terms = get_terms( array(
		'taxonomy'   => 'timeline_category',
		'slug'       => array_keys( aiad_timeline_topics() ),
		'hide_empty' => true,
	) );
	if ( is_wp_error( $terms ) || ! $terms ) {
		return array();
	}
	// Term names are stored with & as &amp;; decode so they are escaped once, on output.
	$have = array_map( static function ( $n ) {
		return html_entity_decode( $n, ENT_QUOTES, 'UTF-8' );
	}, wp_list_pluck( $terms, 'name', 'slug' ) );
	$out  = array();
	foreach ( aiad_timeline_topics() as $slug => $name ) {
		if ( isset( $have[ $slug ] ) ) {
			$out[ $slug ] = $have[ $slug ];
		}
	}
	return $out;
}

/**
 * Whether a filter value is a topic slug (as opposed to an old type key).
 */
function aiad_timeline_is_topic( string $filter ): bool {
	return isset( aiad_timeline_topics()[ $filter ] );
}

/**
 * The query clause for a filter value: a topic filters by taxonomy; an old type
 * key (announcement, resource, ...) still filters by the icon meta, so links
 * shared before the change keep working.
 *
 * @param string $filter Topic slug, type key, or '' / 'all'.
 * @return array{tax_query?: array, meta_query?: array}
 */
function aiad_timeline_filter_query_args( string $filter ): array {
	if ( '' === $filter || 'all' === $filter ) {
		return array();
	}
	if ( aiad_timeline_is_topic( $filter ) ) {
		return array(
			'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
				array( 'taxonomy' => 'timeline_category', 'field' => 'slug', 'terms' => $filter ),
			),
		);
	}
	return array(
		'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array( 'key' => '_aiad_timeline_icon', 'value' => $filter, 'compare' => '=' ),
		),
	);
}

/**
 * Create the topic terms, and apply inc/timeline/topic-assignments.php once.
 *
 * Runs on init, but only does work when the assignments file has changed since
 * it last ran (its hash is stored), so after the first request following a
 * deploy it is one autoloaded option lookup. Only topic terms are touched: a
 * post's other timeline_category terms are kept, and a post missing from the
 * file is left alone, so editors' tagging is never overwritten by a re-run.
 */
function aiad_apply_timeline_topics(): void {
	if ( ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$file = __DIR__ . '/topic-assignments.php';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$version = md5_file( $file ) . '-2'; // -2: re-apply to record topic order.
	if ( get_option( 'aiad_timeline_topics_applied' ) === $version ) {
		return;
	}
	// One request at a time: the first visitors after a deploy arrive together.
	if ( get_transient( 'aiad_timeline_topics_lock' ) ) {
		return;
	}
	set_transient( 'aiad_timeline_topics_lock', 1, 5 * MINUTE_IN_SECONDS );

	$ids = array();
	foreach ( aiad_timeline_topics() as $slug => $name ) {
		$term = term_exists( $slug, 'timeline_category' );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'timeline_category', array( 'slug' => $slug ) );
		}
		if ( ! is_wp_error( $term ) ) {
			$ids[ $slug ] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		}
	}

	$assignments = include $file;
	$applied     = 0;
	foreach ( (array) $assignments as $post_slug => $row ) {
		$topics = (array) ( $row['topics'] ?? array() );
		// The live ID, if it is a timeline post with this slug; otherwise look the slug
		// up (a local copy has different IDs). Slugs are compared decoded, since an
		// emoji slug is stored URL-encoded.
		$post = get_post( (int) ( $row['id'] ?? 0 ) );
		$same = static function ( string $a, string $b ): bool {
			return strtolower( rawurldecode( $a ) ) === strtolower( rawurldecode( $b ) );
		};
		if ( ! $post || 'timeline' !== $post->post_type || ! $same( $post->post_name, (string) $post_slug ) ) {
			$post = get_page_by_path( (string) $post_slug, OBJECT, 'timeline' );
		}
		if ( ! $post ) {
			continue; // Not on this copy of the site.
		}
		$keep = array();
		foreach ( (array) wp_get_object_terms( $post->ID, 'timeline_category', array( 'fields' => 'all' ) ) as $t ) {
			if ( ! isset( $ids[ $t->slug ] ) ) {
				$keep[] = (int) $t->term_id; // Not a topic: leave it as it was.
			}
		}
		$want = array_values( array_filter( array_map( static function ( $s ) use ( $ids ) {
			return $ids[ $s ] ?? 0;
		}, (array) $topics ) ) );
		// Topics first, primary first; the taxonomy's 'sort' records this order.
		wp_set_object_terms( $post->ID, array_merge( $want, $keep ), 'timeline_category', false );
		$applied++;
	}

	update_option( 'aiad_timeline_topics_applied', $version, true );
	delete_transient( 'aiad_timeline_topics_lock' );
	if ( function_exists( 'error_log' ) ) {
		error_log( sprintf( 'AIAD: applied campaign update topics to %d posts.', $applied ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
	}
}
add_action( 'init', 'aiad_apply_timeline_topics', 20 );
