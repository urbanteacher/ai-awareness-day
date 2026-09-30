<?php
/**
 * Resource filter: AJAX handler for the /resources/ and /from-partners/ archives, and the cached filter counts.
 *
 * Results carry their card HTML from the theme's template-parts/components/resource-tile.php (kept in the theme
 * until the card becomes a block).
 *
 * Moved from the theme's inc/ajax-handlers.php. The theme loads this file from its bundled copy of the plugin when
 * the plugin isn't active, so this is the only copy.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Marks this module as loaded. See aiad_core_load_modules().
define( 'AIAD_CORE_MODULE_RESOURCE_FILTER', __FILE__ );

/**
 * Get filter counts given current tax_query constraints.
 * 
 * This function calculates how many resources match each filter option when combined
 * with the currently active filters. For example, if "Theme: Safe" is selected,
 * it counts how many resources match "Safe" + each available Resource Type.
 * 
 * Algorithm:
 * 1. For each taxonomy (resource_principle, resource_duration, etc.):
 *    - Remove that taxonomy from the active filters (reduced_query)
 *    - For each term in that taxonomy:
 *      - Add the term to reduced_query
 *      - Count matching resources
 * 2. For key_stage (meta field, not taxonomy):
 *    - Count resources matching active filters + each key stage value
 * 
 * Results are cached for 1 hour to improve performance. Cache is invalidated
 * when resources are saved via aiad_bump_filter_counts_version().
 *
 * @param string $post_type       Post type slug ('resource' or 'featured_resource').
 * @param array  $active_tax_query Current tax_query array from active filters.
 * @return array Counts keyed by taxonomy => term_slug => count. Example:
 *               ['resource_duration' => ['5-min-lesson-starters' => 5, ...], ...]
 */
function aiad_get_filter_counts( string $post_type, array $active_tax_query, array $active_key_stages = [] ): array {
    // Cache key includes version number (bumped on resource save) and active filters
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    $normalized_tax_query = aiad_normalize_tax_query_for_cache( $active_tax_query );
    $cache_key = 'aiad_fc_' . $post_type . '_' . $version . '_' . md5( wp_json_encode( $normalized_tax_query ) . wp_json_encode( $active_key_stages ) );
    $cached = get_transient( $cache_key );
    if ( is_array( $cached ) ) {
        return $cached;
    }

    // Build meta_query for active key_stage so taxonomy counts reflect the
    // key_stage constraint — without this, "Responsible (2)" can show 2 even
    // though 0 of those resources match the selected key stage.
    $ks_meta_query = array();
    if ( ! empty( $active_key_stages ) && 'resource' === $post_type ) {
        $ks_clauses = array( 'relation' => 'OR' );
        foreach ( $active_key_stages as $ks ) {
            $ks_clauses[] = array(
                'key'     => '_aiad_key_stage',
                'value'   => '"' . $ks . '"',
                'compare' => 'LIKE',
            );
        }
        $ks_meta_query = $ks_clauses;
    }

    $taxonomies = array( 'resource_principle', 'resource_duration', 'activity_type' );
    $counts     = array();
    foreach ( $taxonomies as $tax ) {
        $counts[ $tax ] = array();
        $terms = get_terms( array( 'taxonomy' => $tax, 'hide_empty' => false ) );
        if ( $terms && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $counts[ $tax ][ $term->slug ] = 0;
            }
        }
    }

    // Process each taxonomy with a single ID query + a single term-object query.
    foreach ( $taxonomies as $tax ) {
        $reduced_query = array_values(
            array_filter(
                $active_tax_query,
                static function ( $clause ) use ( $tax ) {
                    return is_array( $clause ) && isset( $clause['taxonomy'] ) && $clause['taxonomy'] !== $tax;
                }
            )
        );
        if ( count( $reduced_query ) > 1 ) {
            $reduced_query['relation'] = 'AND';
        }

        $base_id_args = array(
            'post_type'              => $post_type,
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'tax_query'              => $reduced_query,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        );
        if ( ! empty( $ks_meta_query ) ) {
            $base_id_args['meta_query'] = $ks_meta_query;
        }
        $base_ids = get_posts( $base_id_args );
        if ( empty( $base_ids ) ) {
            continue;
        }

        $term_rows = wp_get_object_terms(
            $base_ids,
            $tax,
            array(
                'fields' => 'all_with_object_id',
            )
        );
        if ( is_wp_error( $term_rows ) || empty( $term_rows ) ) {
            continue;
        }

        $seen_by_term = array();
        foreach ( $term_rows as $row ) {
            $slug = isset( $row->slug ) ? (string) $row->slug : '';
            $object_id = isset( $row->object_id ) ? (int) $row->object_id : 0;
            if ( '' === $slug || ! $object_id ) {
                continue;
            }
            if ( ! isset( $seen_by_term[ $slug ] ) ) {
                $seen_by_term[ $slug ] = array();
            }
            $seen_by_term[ $slug ][ $object_id ] = true;
        }

        foreach ( $seen_by_term as $slug => $post_ids_map ) {
            $counts[ $tax ][ $slug ] = count( $post_ids_map );
        }
    }

    // Handle key_stage separately (it's a meta field, not a taxonomy)
    if ( 'resource' === $post_type && function_exists( 'aiad_key_stage_options' ) ) {
        $counts['key_stage'] = array();
        foreach ( array_keys( aiad_key_stage_options() ) as $ks ) {
            $counts['key_stage'][ $ks ] = 0;
        }

        $filtered_ids = get_posts(
            array(
                'post_type'              => $post_type,
                'post_status'            => 'publish',
                'posts_per_page'         => -1,
                'fields'                 => 'ids',
                'tax_query'              => $active_tax_query,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
            )
        );

        if ( ! empty( $filtered_ids ) ) {
            global $wpdb;
            $id_list = implode( ',', array_map( 'absint', $filtered_ids ) );
            $rows = $wpdb->get_results(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_aiad_key_stage' AND post_id IN ($id_list)", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                ARRAY_A
            );

            if ( is_array( $rows ) ) {
                $valid_stages = array_keys( aiad_key_stage_options() );
                foreach ( $rows as $row ) {
                    $values = maybe_unserialize( $row['meta_value'] ?? '' );
                    if ( ! is_array( $values ) ) {
                        continue;
                    }
                    foreach ( array_unique( $values ) as $stage ) {
                        if ( in_array( $stage, $valid_stages, true ) ) {
                            $counts['key_stage'][ $stage ]++;
                        }
                    }
                }
            }
        }
    }

    // Cache results for 1 hour
    set_transient( $cache_key, $counts, HOUR_IN_SECONDS );
    return $counts;
}

/**
 * Create stable ordering for tax_query cache keys.
 *
 * @param array $tax_query Tax query clauses.
 * @return array
 */
function aiad_normalize_tax_query_for_cache( array $tax_query ): array {
    $relation = isset( $tax_query['relation'] ) ? $tax_query['relation'] : '';
    $clauses = array_values(
        array_filter(
            $tax_query,
            static function ( $clause ) {
                return is_array( $clause ) && isset( $clause['taxonomy'] );
            }
        )
    );
    usort(
        $clauses,
        static function ( $a, $b ) {
            $a_tax = (string) ( $a['taxonomy'] ?? '' );
            $b_tax = (string) ( $b['taxonomy'] ?? '' );
            if ( $a_tax === $b_tax ) {
                return strcmp( wp_json_encode( $a ), wp_json_encode( $b ) );
            }
            return strcmp( $a_tax, $b_tax );
        }
    );
    if ( $relation ) {
        $clauses['relation'] = $relation;
    }
    return $clauses;
}

/**
 * Invalidate filter-count cache when a resource or featured_resource is saved.
 */
function aiad_bump_filter_counts_version(): void {
    $version = (int) get_option( 'aiad_filter_counts_ver', 0 );
    update_option( 'aiad_filter_counts_ver', $version + 1 );
}
add_action( 'save_post_resource', 'aiad_bump_filter_counts_version' );
add_action( 'save_post_featured_resource', 'aiad_bump_filter_counts_version' );

/**
 * AJAX handler: filter resources
 */
function aiad_ajax_filter_resources(): void {
    // No nonce required: this endpoint only returns public, published post data.
    // A nonce would expire on cached pages and break filtering for all visitors.

    $post_type = sanitize_text_field( $_POST['post_type'] ?? 'resource' );
    if ( ! in_array( $post_type, array( 'resource', 'featured_resource' ), true ) ) {
        wp_send_json_error( array( 'message' => __( 'Invalid request. Please try again.', 'ai-awareness-day' ) ) );
    }

    $args = array(
        'post_type'              => $post_type,
        'post_status'            => 'publish',
        'posts_per_page'         => 200,
        'orderby'                => 'title',
        'order'                  => 'ASC',
        'update_post_meta_cache' => true, // Prime all meta in one query — prevents N+1 inside the loop
        'update_post_term_cache' => true, // Prime all term caches in one query
    );

    $tax_query = array();

    $principle = sanitize_text_field( $_POST['principle'] ?? '' );
    if ( $principle ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_principle',
            'field'    => 'slug',
            'terms'    => $principle,
        );
    }

    $duration = sanitize_text_field( $_POST['duration'] ?? '' );
    $legacy_type = sanitize_text_field( $_POST['resource_type'] ?? '' );
    if ( ! $duration && $legacy_type && function_exists( 'aiad_legacy_resource_type_slug_to_duration_slug' ) ) {
        $duration = aiad_legacy_resource_type_slug_to_duration_slug( $legacy_type );
    }
    if ( $duration ) {
        $tax_query[] = array(
            'taxonomy' => 'resource_duration',
            'field'    => 'slug',
            'terms'    => $duration,
        );
    }

    $activity_type = sanitize_text_field( $_POST['activity_type'] ?? '' );
    if ( $activity_type ) {
        $tax_query[] = array(
            'taxonomy' => 'activity_type',
            'field'    => 'slug',
            'terms'    => $activity_type,
        );
    }

    if ( ! empty( $tax_query ) ) {
        $tax_query['relation'] = 'AND';
        $args['tax_query'] = $tax_query;
    }

    $key_stage = array();
    if ( ! empty( $_POST['key_stage'] ) ) {
        if ( is_array( $_POST['key_stage'] ) ) {
            $key_stage = array_map( 'sanitize_text_field', wp_unslash( $_POST['key_stage'] ) );
        } else {
            $key_stage = array( sanitize_text_field( wp_unslash( $_POST['key_stage'] ) ) );
        }
        $key_stage = array_values( array_intersect( $key_stage, array_keys( aiad_key_stage_options() ) ) );
    }
    if ( ! empty( $key_stage ) ) {
        $meta_clauses = array();
        foreach ( $key_stage as $ks ) {
            $meta_clauses[] = array(
                'key'     => '_aiad_key_stage',
                'value'   => '"' . $ks . '"', // Match exact serialised value to prevent substring false positives
                'compare' => 'LIKE',
            );
        }
        $meta_clauses['relation'] = 'OR';
        $args['meta_query'] = $meta_clauses;
    }

    $query   = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        while ( $query->have_posts() ) {
            $query->the_post();
            $id = get_the_ID();

            $themes     = get_the_terms( $id, 'resource_principle' );
            $durations  = get_the_terms( $id, 'resource_duration' );
            $activities = get_the_terms( $id, 'activity_type' );

            $duration_names = array();
            $duration_slugs = array();
            if ( $durations && ! is_wp_error( $durations ) && function_exists( 'aiad_resource_duration_term_labels' ) ) {
                $duration_names = aiad_resource_duration_term_labels( $durations );
                foreach ( $durations as $dterm ) {
                    $duration_slugs[] = $dterm->slug;
                }
            }
            $duration_name = ! empty( $duration_names ) ? $duration_names[0] : '';
            $theme_name = $themes && ! is_wp_error( $themes ) ? $themes[0]->name : '';
            $activity_names = array();
            if ( $activities && ! is_wp_error( $activities ) ) {
                foreach ( $activities as $a ) {
                    $activity_names[] = $a->name;
                }
            }

            $download_url  = get_post_meta( $id, '_aiad_download_url', true );
            $featured_url  = get_post_meta( $id, '_featured_resource_url', true );

            $thumbnail = get_the_post_thumbnail_url( $id, 'medium_large' );

            $key_stage_meta = function_exists( 'aiad_get_resource_key_stages' )
                ? aiad_get_resource_key_stages( $id )
                : array();
            $results[] = array(
                'id'             => $id,
                'title'          => get_the_title(),
                'permalink'      => get_permalink(),
                'excerpt'        => get_the_excerpt(),
                'thumbnail'      => $thumbnail ?: '',
                'type_name'      => $duration_name,
                'type_names'     => $duration_names,
                'duration_names' => $duration_names,
                'duration_slugs' => $duration_slugs,
                'duration_name'  => $duration_name,
                'theme_name'     => $theme_name,
                'theme_slug'     => $themes && ! is_wp_error( $themes ) ? $themes[0]->slug : '',
                'activity_types' => $activity_names,
                'key_stages'     => $key_stage_meta,
                'download_url'   => $download_url ?: '',
                'download_label' => $download_url && function_exists( 'aiad_resource_download_label' ) ? aiad_resource_download_label( $download_url ) : '',
                'external_url'   => $featured_url ?: '',
                'org_name'       => get_post_meta( $id, '_featured_resource_org_name', true ) ?: '',
            );

            // Both archives render their cards from template-parts/components/resource-tile.php.
            // Send that same markup, so a filtered grid is the first page's card rather than
            // a second copy of it rebuilt in resource-filters.js.
            ob_start();
            get_template_part(
                'template-parts/components/resource-tile',
                null,
                'featured_resource' === $post_type
                    ? array( 'link' => $featured_url ?: get_permalink(), 'external' => (bool) $featured_url )
                    : array()
            );
            $results[ count( $results ) - 1 ]['html'] = ob_get_clean();
        }
        wp_reset_postdata();
    }

    $counts = aiad_get_filter_counts( $post_type, $tax_query, $key_stage );

    wp_send_json_success( array(
        'resources'      => $results,
        'total'          => count( $results ),
        'filter_counts'  => $counts,
    ) );
}
add_action( 'wp_ajax_aiad_filter_resources', 'aiad_ajax_filter_resources' );
add_action( 'wp_ajax_nopriv_aiad_filter_resources', 'aiad_ajax_filter_resources' );
