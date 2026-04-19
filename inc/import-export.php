<?php
/**
 * WXR import/export for demo resources (admin).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Public URL for the bundled single-resource WXR shipped in the theme (import/bbc-how-ai-actually-works.wxr.xml).
 * Empty string if the file is missing.
 *
 * @return string
 */
function aiad_bundled_bbc_how_ai_wxr_url(): string {
    $rel  = '/import/bbc-how-ai-actually-works.wxr.xml';
    $path = get_template_directory() . $rel;
    if ( ! file_exists( $path ) ) {
        return '';
    }
    return get_template_directory_uri() . $rel;
}

/**
 * Resources → Import demo resources admin page.
 */
function aiad_register_resource_import_page(): void {
    add_submenu_page(
        'edit.php?post_type=resource',
        __( 'Import demo resources', 'ai-awareness-day' ),
        __( 'Import demo resources', 'ai-awareness-day' ),
        'manage_options',
        'aiad-import-resources',
        'aiad_render_resource_import_page'
    );
}
add_action( 'admin_menu', 'aiad_register_resource_import_page' );

/**
 * Run export before any output so download headers work.
 * If we run inside the page callback, WordPress may have already sent output and the browser shows raw XML instead of downloading.
 */
function aiad_maybe_do_export_download(): void {
    if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'aiad-export-resources' ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['aiad_export_resources'] ) && check_admin_referer( 'aiad_export_resources', 'aiad_export_nonce' ) ) {
        aiad_export_resources_to_wxr();
        exit;
    }
    if ( isset( $_GET['aiad_export_partner_resources'] ) && check_admin_referer( 'aiad_export_partner_resources', 'aiad_export_partner_nonce' ) ) {
        aiad_export_featured_resources_to_wxr();
        exit;
    }
}
add_action( 'admin_init', 'aiad_maybe_do_export_download', 1 );

/**
 * Resources → Export demo resources admin page.
 */
function aiad_register_resource_export_page(): void {
    add_submenu_page(
        'edit.php?post_type=resource',
        __( 'Export demo resources', 'ai-awareness-day' ),
        __( 'Export demo resources', 'ai-awareness-day' ),
        'manage_options',
        'aiad-export-resources',
        'aiad_render_resource_export_page'
    );
}
add_action( 'admin_menu', 'aiad_register_resource_export_page' );

/**
 * Partners → Resources from partners: add Export partner resources submenu (same page as Export demo resources).
 */
function aiad_register_partner_export_submenu(): void {
    add_submenu_page(
        'edit.php?post_type=featured_resource',
        __( 'Export partner resources', 'ai-awareness-day' ),
        __( 'Export partner resources', 'ai-awareness-day' ),
        'manage_options',
        'aiad-export-resources',
        'aiad_render_resource_export_page'
    );
}
add_action( 'admin_menu', 'aiad_register_partner_export_submenu', 20 );

/**
 * Clear any output buffers so download headers can be sent.
 */
function aiad_export_clean_buffer(): void {
    while ( ob_get_level() ) {
        ob_end_clean();
    }
}

/**
 * Render the Export demo resources admin page.
 */
function aiad_render_resource_export_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'ai-awareness-day' ) );
    }

    // Export downloads are handled in admin_init (aiad_maybe_do_export_download) so headers are sent before any output.

    // Count existing resources
    $resource_count = wp_count_posts( 'resource' );
    $total_resources = (int) $resource_count->publish + (int) $resource_count->draft + (int) $resource_count->pending;

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Export demo resources', 'ai-awareness-day' ); ?></h1>
        <?php
        $bundled_wxr = aiad_bundled_bbc_how_ai_wxr_url();
        if ( $bundled_wxr !== '' ) :
            ?>
            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e( 'Live / staging bundle', 'ai-awareness-day' ); ?></strong>
                    —
                    <a href="<?php echo esc_url( $bundled_wxr ); ?>"><?php esc_html_e( 'bbc-how-ai-actually-works.wxr.xml', 'ai-awareness-day' ); ?></a>
                    <?php esc_html_e( 'is included in the theme for importing the BBC Ideas tutor-time resource without using auto-seed. Same format as a full export.', 'ai-awareness-day' ); ?>
                </p>
            </div>
        <?php endif; ?>
        <p><?php esc_html_e( 'Export all Resource posts to a WordPress WXR (.xml) file. This file can be imported on another WordPress site using the Import demo resources tool.', 'ai-awareness-day' ); ?></p>
        
        <?php if ( $total_resources > 0 ) : ?>
            <div class="notice notice-info">
                <p>
                    <?php
                    /* translators: %d: number of resources */
                    printf( esc_html__( 'Found %d resource(s) to export.', 'ai-awareness-day' ), $total_resources );
                    ?>
                </p>
            </div>
            <p>
                <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aiad-export-resources&aiad_export_resources=1' ), 'aiad_export_resources', 'aiad_export_nonce' ) ); ?>" class="button button-primary">
                    <?php esc_html_e( 'Download WXR Export File', 'ai-awareness-day' ); ?>
                </a>
            </p>
            <p class="description">
                <?php esc_html_e( 'The exported WXR file will include all Resource posts with their metadata, taxonomy terms, and custom fields. You can import this file on another WordPress site using the Import demo resources tool.', 'ai-awareness-day' ); ?>
            </p>
            <hr />
            <h2><?php esc_html_e( 'Export Resources from partners', 'ai-awareness-day' ); ?></h2>
            <?php
            $partner_count = wp_count_posts( 'featured_resource' );
            $total_partner  = (int) $partner_count->publish + (int) $partner_count->draft + (int) $partner_count->pending;
            ?>
            <?php if ( $total_partner > 0 ) : ?>
                <p>
                    <?php
                    /* translators: %d: number of partner resources */
                    printf( esc_html__( 'Found %d resource(s) from partners to export.', 'ai-awareness-day' ), $total_partner );
                    ?>
                </p>
                <p>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=aiad-export-resources&aiad_export_partner_resources=1' ), 'aiad_export_partner_resources', 'aiad_export_partner_nonce' ) ); ?>" class="button button-primary">
                        <?php esc_html_e( 'Download WXR: Resources from partners', 'ai-awareness-day' ); ?>
                    </a>
                </p>
                <p class="description">
                    <?php esc_html_e( 'Use this WXR file to import or move Resources from partners (featured resources) to another site. Import via Resources → Import demo resources.', 'ai-awareness-day' ); ?>
                </p>
            <?php else : ?>
                <p><?php esc_html_e( 'No resources from partners to export. Add entries under Partners → Resources from partners.', 'ai-awareness-day' ); ?></p>
            <?php endif; ?>
        <?php else : ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e( 'No resources found to export.', 'ai-awareness-day' ); ?>
                </p>
                <p>
                    <?php esc_html_e( 'To generate demo content for export, you can temporarily enable the seed functions in functions.php (uncomment the add_action hooks), then refresh this page after the resources are created.', 'ai-awareness-day' ); ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Export all Resources from partners (featured_resource) posts to a WXR file.
 * Same WXR format as Resources; import via Import demo resources (accepts both post types).
 */
function aiad_export_featured_resources_to_wxr(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to export resources.', 'ai-awareness-day' ) );
    }
    aiad_export_clean_buffer();

    $resources = get_posts( array(
        'post_type'      => 'featured_resource',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );

    if ( empty( $resources ) ) {
        wp_die( esc_html__( 'No resources from partners found to export.', 'ai-awareness-day' ) );
    }

    $filename = 'ai-awareness-day-partner-resources-' . wp_date( 'Y-m-d-His' ) . '.xml';

    header( 'Content-Type: application/xml; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    ?>
    <rss version="2.0"
        xmlns:excerpt="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/excerpt/"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:wp="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/"
    >
        <channel>
            <title><?php bloginfo_rss( 'name' ); ?></title>
            <link><?php bloginfo_rss( 'url' ); ?></link>
            <description><?php bloginfo_rss( 'description' ); ?></description>
            <pubDate><?php echo esc_html( gmdate( 'D, d M Y H:i:s +0000' ) ); ?></pubDate>
            <language><?php bloginfo_rss( 'language' ); ?></language>
            <wp:wxr_version><?php echo esc_html( aiad_get_wxr_version() ); ?></wp:wxr_version>
            <wp:base_site_url><?php echo esc_url( site_url() ); ?></wp:base_site_url>
            <wp:base_blog_url><?php echo esc_url( home_url() ); ?></wp:base_blog_url>

            <?php
            foreach ( $resources as $resource ) {
                aiad_export_resource_post( $resource );
            }
            $taxonomies = array( 'resource_principle', 'resource_duration', 'activity_type' );
            foreach ( $taxonomies as $taxonomy ) {
                aiad_export_taxonomy_terms( $taxonomy );
            }
            ?>
        </channel>
    </rss>
    <?php
    exit;
}

/**
 * Export all Resource posts to a WXR file.
 * Generates WordPress eXtended RSS (WXR) format XML and triggers download.
 */
function aiad_export_resources_to_wxr(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to export resources.', 'ai-awareness-day' ) );
    }
    aiad_export_clean_buffer();

    // Get all resource posts
    $resources = get_posts( array(
        'post_type'      => 'resource',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC',
    ) );

    if ( empty( $resources ) ) {
        wp_die( esc_html__( 'No resources found to export.', 'ai-awareness-day' ) );
    }

    // Generate filename with timestamp
    $filename = 'ai-awareness-day-resources-' . wp_date( 'Y-m-d-His' ) . '.xml';

    // Set headers for download
    header( 'Content-Type: application/xml; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
    header( 'Pragma: no-cache' );
    header( 'Expires: 0' );

    // Start XML output
    echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";
    ?>
    <rss version="2.0"
        xmlns:excerpt="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/excerpt/"
        xmlns:content="http://purl.org/rss/1.0/modules/content/"
        xmlns:wfw="http://wellformedweb.org/CommentAPI/"
        xmlns:dc="http://purl.org/dc/elements/1.1/"
        xmlns:wp="http://wordpress.org/export/<?php echo esc_attr( aiad_get_wxr_version() ); ?>/"
    >
        <channel>
            <title><?php bloginfo_rss( 'name' ); ?></title>
            <link><?php bloginfo_rss( 'url' ); ?></link>
            <description><?php bloginfo_rss( 'description' ); ?></description>
            <pubDate><?php echo esc_html( gmdate( 'D, d M Y H:i:s +0000' ) ); ?></pubDate>
            <language><?php bloginfo_rss( 'language' ); ?></language>
            <wp:wxr_version><?php echo esc_html( aiad_get_wxr_version() ); ?></wp:wxr_version>
            <wp:base_site_url><?php echo esc_url( site_url() ); ?></wp:base_site_url>
            <wp:base_blog_url><?php echo esc_url( home_url() ); ?></wp:base_blog_url>

            <?php
            // Export each resource post
            foreach ( $resources as $resource ) {
                aiad_export_resource_post( $resource );
            }

            // Export taxonomy terms
            $taxonomies = array( 'resource_principle', 'resource_duration', 'activity_type' );
            foreach ( $taxonomies as $taxonomy ) {
                aiad_export_taxonomy_terms( $taxonomy );
            }
            ?>
        </channel>
    </rss>
    <?php
}

/**
 * Get WXR version string.
 *
 * @return string WXR version.
 */
function aiad_get_wxr_version(): string {
    return '1.2';
}

/**
 * Export a single resource post to WXR format.
 *
 * @param WP_Post $post Resource post object.
 */
function aiad_export_resource_post( WP_Post $post ): void {
    setup_postdata( $post );

    // Get all post meta
    $post_meta = get_post_meta( $post->ID );

    // Get taxonomy terms
    $taxonomies = array( 'resource_principle', 'resource_duration', 'activity_type' );
    $terms      = array();
    foreach ( $taxonomies as $taxonomy ) {
        $post_terms = wp_get_object_terms( $post->ID, $taxonomy );
        if ( ! is_wp_error( $post_terms ) && ! empty( $post_terms ) ) {
            foreach ( $post_terms as $term ) {
                $terms[] = array(
                    'domain'   => $taxonomy,
                    'slug'     => $term->slug,
                    'name'     => $term->name,
                );
            }
        }
    }

    ?>
    <item>
        <title><?php echo esc_html( apply_filters( 'the_title_rss', $post->post_title ) ); ?></title>
        <link><?php echo esc_url( get_permalink( $post->ID ) ); ?></link>
        <pubDate><?php echo esc_html( mysql2date( 'D, d M Y H:i:s +0000', $post->post_date_gmt, false ) ); ?></pubDate>
        <dc:creator><?php echo esc_html( get_the_author_meta( 'login', $post->post_author ) ); ?></dc:creator>
        <guid isPermaLink="false"><?php echo esc_url( get_permalink( $post->ID ) ); ?></guid>
        <description></description>
        <content:encoded><?php echo aiad_wxr_cdata( apply_filters( 'the_content_export', $post->post_content ) ); ?></content:encoded>
        <excerpt:encoded><?php echo aiad_wxr_cdata( apply_filters( 'the_excerpt_export', $post->post_excerpt ) ); ?></excerpt:encoded>
        <wp:post_id><?php echo (int) $post->ID; ?></wp:post_id>
        <wp:post_date><?php echo esc_html( $post->post_date ); ?></wp:post_date>
        <wp:post_date_gmt><?php echo esc_html( $post->post_date_gmt ); ?></wp:post_date_gmt>
        <wp:comment_status><?php echo esc_html( $post->comment_status ); ?></wp:comment_status>
        <wp:ping_status><?php echo esc_html( $post->ping_status ); ?></wp:ping_status>
        <wp:post_name><?php echo esc_html( $post->post_name ); ?></wp:post_name>
        <wp:status><?php echo esc_html( $post->post_status ); ?></wp:status>
        <wp:post_parent><?php echo (int) $post->post_parent; ?></wp:post_parent>
        <wp:menu_order><?php echo (int) $post->menu_order; ?></wp:menu_order>
        <wp:post_type><?php echo esc_html( $post->post_type ); ?></wp:post_type>
        <wp:post_password><?php echo esc_html( $post->post_password ); ?></wp:post_password>
        <wp:is_sticky><?php echo is_sticky( $post->ID ) ? '1' : '0'; ?></wp:is_sticky>

        <?php
        // Export taxonomy terms
        foreach ( $terms as $term ) {
            ?>
            <category domain="<?php echo esc_attr( $term['domain'] ); ?>" nicename="<?php echo esc_attr( $term['slug'] ); ?>">
                <?php echo esc_html( $term['name'] ); ?>
            </category>
            <?php
        }

        // Export post meta
        foreach ( $post_meta as $meta_key => $meta_values ) {
            // Skip internal WordPress meta
            if ( strpos( $meta_key, '_edit_' ) === 0 || strpos( $meta_key, '_wp_' ) === 0 ) {
                continue;
            }

            foreach ( $meta_values as $meta_value ) {
                // Serialize arrays/objects for storage
                if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
                    $meta_value = maybe_serialize( $meta_value );
                }
                ?>
                <wp:postmeta>
                    <wp:meta_key><?php echo esc_html( $meta_key ); ?></wp:meta_key>
                    <wp:meta_value><?php echo aiad_wxr_cdata( $meta_value ); ?></wp:meta_value>
                </wp:postmeta>
                <?php
            }
        }
        ?>
    </item>
    <?php
    wp_reset_postdata();
}

/**
 * Export taxonomy terms to WXR format.
 *
 * @param string $taxonomy Taxonomy name.
 */
function aiad_export_taxonomy_terms( string $taxonomy ): void {
    $terms = get_terms( array(
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return;
    }

    foreach ( $terms as $term ) {
        ?>
        <wp:term>
            <wp:term_id><?php echo (int) $term->term_id; ?></wp:term_id>
            <wp:term_taxonomy><?php echo esc_html( $taxonomy ); ?></wp:term_taxonomy>
            <wp:term_slug><?php echo esc_html( $term->slug ); ?></wp:term_slug>
            <wp:term_name><?php echo esc_html( $term->name ); ?></wp:term_name>
            <?php if ( ! empty( $term->description ) ) : ?>
                <wp:term_description><?php echo aiad_wxr_cdata( $term->description ); ?></wp:term_description>
            <?php endif; ?>
        </wp:term>
        <?php
    }
}

/**
 * Wrap content in CDATA section for safe XML export.
 *
 * @param string|mixed $str Content to wrap.
 * @return string CDATA-wrapped content.
 */
function aiad_wxr_cdata( $str ): string {
    if ( ! is_string( $str ) ) {
        $str = (string) $str;
    }
    if ( ! seems_utf8( $str ) ) {
        $str = mb_convert_encoding( $str, 'UTF-8', 'ISO-8859-1' ); // utf8_encode() removed in PHP 9
    }
    $str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';
    return $str;
}

/**
 * Render the Import demo resources admin page.
 */
function aiad_render_resource_import_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to access this page.', 'ai-awareness-day' ) );
    }

    $message = '';
    $type    = '';

    if ( isset( $_POST['aiad_resource_import_submit'] ) && check_admin_referer( 'aiad_resource_import', 'aiad_resource_import_nonce' ) ) {
        if ( ! empty( $_FILES['aiad_wxr_file']['tmp_name'] ) ) {
            $file = $_FILES['aiad_wxr_file'];
            if ( ! empty( $file['error'] ) ) {
                $message = esc_html__( 'Upload error. Please try again.', 'ai-awareness-day' );
                $type    = 'error';
            } else {
                $result = aiad_import_resources_from_wxr( $file['tmp_name'], $file['name'] );
                if ( is_wp_error( $result ) ) {
                    /* translators: %s: error message */
                    $message = sprintf( esc_html__( 'Import failed: %s', 'ai-awareness-day' ), $result->get_error_message() );
                    $type    = 'error';
                } else {
                    $res_count   = isset( $result['resource'] ) ? (int) $result['resource'] : 0;
                    $partner_count = isset( $result['featured_resource'] ) ? (int) $result['featured_resource'] : 0;
                    $parts = array();
                    if ( $res_count > 0 ) {
                        $parts[] = sprintf( /* translators: %d: number */ _n( '%d resource', '%d resources', $res_count, 'ai-awareness-day' ), $res_count );
                    }
                    if ( $partner_count > 0 ) {
                        $parts[] = sprintf( /* translators: %d: number */ _n( '%d resource from partner', '%d resources from partners', $partner_count, 'ai-awareness-day' ), $partner_count );
                    }
                    $message = $parts ? sprintf( esc_html__( 'Import completed. %s processed.', 'ai-awareness-day' ), implode( ', ', $parts ) ) : esc_html__( 'Import completed. No matching content in file.', 'ai-awareness-day' );
                    if ( $res_count > 0 ) {
                        $message .= ' <a href="' . esc_url( admin_url( 'edit.php?post_type=resource' ) ) . '">' . esc_html__( 'View resources', 'ai-awareness-day' ) . '</a>';
                    }
                    if ( $partner_count > 0 ) {
                        if ( $res_count > 0 ) {
                            $message .= ' | ';
                        }
                        $message .= ' <a href="' . esc_url( admin_url( 'edit.php?post_type=featured_resource' ) ) . '">' . esc_html__( 'View resources from partners', 'ai-awareness-day' ) . '</a>';
                    }
                    $type = 'updated';
                }
            }
        } else {
            $message = esc_html__( 'Please choose a WXR (.xml) file to upload.', 'ai-awareness-day' );
            $type    = 'error';
        }
    }

    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Import demo resources', 'ai-awareness-day' ); ?></h1>
        <?php
        $bundled_wxr = aiad_bundled_bbc_how_ai_wxr_url();
        if ( $bundled_wxr !== '' ) :
            ?>
            <div class="notice notice-info">
                <p>
                    <strong><?php esc_html_e( 'Live / staging bundle', 'ai-awareness-day' ); ?></strong>
                    —
                    <a href="<?php echo esc_url( $bundled_wxr ); ?>"><?php esc_html_e( 'Download bbc-how-ai-actually-works.wxr.xml', 'ai-awareness-day' ); ?></a>
                    <?php esc_html_e( '(BBC Ideas — How AI actually works, tutor time). Upload it below to add or update that resource by slug on any site running this theme.', 'ai-awareness-day' ); ?>
                </p>
            </div>
        <?php endif; ?>
        <p><?php esc_html_e( 'Upload a WordPress WXR (.xml) file to import Resources and/or Resources from partners. Matching posts are created or updated by slug. You can use the Export tools (below) to generate WXR files from this site.', 'ai-awareness-day' ); ?></p>
        <?php if ( $message ) : ?>
            <div class="<?php echo $type === 'error' ? 'notice notice-error' : 'notice notice-success'; ?> is-dismissible">
                <p><?php echo esc_html( $message ); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field( 'aiad_resource_import', 'aiad_resource_import_nonce' ); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="aiad_wxr_file"><?php esc_html_e( 'WXR file', 'ai-awareness-day' ); ?></label>
                    </th>
                    <td>
                        <input type="file" id="aiad_wxr_file" name="aiad_wxr_file" accept=".xml" class="regular-text" />
                        <p class="description">
                            <?php esc_html_e( 'Accepts WXR files containing Resources and/or Resources from partners (e.g. from this site’s Export demo resources or Export partner resources).', 'ai-awareness-day' ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <?php submit_button( __( 'Import demo resources', 'ai-awareness-day' ), 'primary', 'aiad_resource_import_submit' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Import Resources and/or Resources from partners from a WXR file.
 * First tries WordPress Importer plugin if available, otherwise uses simple XML parser.
 *
 * @param string $file_path Absolute path to the uploaded WXR file.
 * @param string $original_name Original filename (for messages/logging).
 * @return array{resource: int, featured_resource: int}|\WP_Error Counts keyed by post type, or WP_Error on failure.
 */
function aiad_import_resources_from_wxr( string $file_path, string $original_name = '' ) {
    if ( ! file_exists( $file_path ) ) {
        return new WP_Error( 'aiad_import_missing_file', __( 'Import file not found.', 'ai-awareness-day' ) );
    }

    // Try WordPress Importer plugin first if available
    if ( class_exists( 'WP_Import' ) ) {
        require_once ABSPATH . 'wp-admin/includes/import.php';

        $importer = new WP_Import();
        $importer->fetch_attachments = false;

        $counts = array( 'resource' => 0, 'featured_resource' => 0 );

        add_filter(
            'wp_import_post_exists',
            static function ( $post_exists, $post_id, $postdata ) {
                $cpt = isset( $postdata['post_type'] ) ? $postdata['post_type'] : '';
                if ( in_array( $cpt, array( 'resource', 'featured_resource' ), true ) && ! empty( $postdata['post_name'] ) ) {
                    $existing = get_page_by_path( $postdata['post_name'], OBJECT, $cpt );
                    if ( $existing instanceof WP_Post ) {
                        return $existing->ID;
                    }
                }
                return $post_exists;
            },
            10,
            3
        );

        add_action(
            'wp_import_post_data_processed',
            static function ( $postdata ) use ( &$counts ) {
                $cpt = isset( $postdata['post_type'] ) ? $postdata['post_type'] : '';
                if ( isset( $counts[ $cpt ] ) ) {
                    $counts[ $cpt ]++;
                }
            }
        );

        ob_start();
        $importer->import( $file_path );
        ob_end_clean();

        return $counts;
    }

    // Fallback: Simple XML parser for Resource posts only
    return aiad_import_resources_from_wxr_simple( $file_path );
}

/**
 * Supported post types for WXR import (Resources and Resources from partners).
 *
 * @return string[]
 */
function aiad_wxr_import_post_types(): array {
    return array( 'resource', 'featured_resource' );
}

/**
 * Simple WXR import parser for Resource and featured_resource (partner) posts (no plugin required).
 *
 * @param string $file_path Absolute path to the WXR file.
 * @return array{resource: int, featured_resource: int}|\WP_Error Counts keyed by post type, or WP_Error on failure.
 */
function aiad_import_resources_from_wxr_simple( string $file_path ) {
    if ( ! file_exists( $file_path ) ) {
        return new WP_Error( 'aiad_import_missing_file', __( 'Import file not found.', 'ai-awareness-day' ) );
    }

    // Load XML file
    libxml_use_internal_errors( true );
    $xml = simplexml_load_file( $file_path );
    
    if ( $xml === false ) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        return new WP_Error( 'aiad_import_xml_error', __( 'Invalid XML file.', 'ai-awareness-day' ) );
    }

    // Register namespaces
    $xml->registerXPathNamespace( 'wp', 'http://wordpress.org/export/1.2/' );
    $xml->registerXPathNamespace( 'content', 'http://purl.org/rss/1.0/modules/content/' );
    $xml->registerXPathNamespace( 'excerpt', 'http://wordpress.org/export/1.2/excerpt/' );

    $counts = array( 'resource' => 0, 'featured_resource' => 0 );

    // Process each item
    foreach ( $xml->channel->item as $item ) {
        $post_type = (string) $item->children( 'wp', true )->post_type;
        if ( ! in_array( $post_type, aiad_wxr_import_post_types(), true ) ) {
            continue;
        }

        // Check if post already exists by slug
        $post_name = (string) $item->children( 'wp', true )->post_name;
        if ( ! empty( $post_name ) ) {
            $existing = get_page_by_path( $post_name, OBJECT, $post_type );
            if ( $existing instanceof WP_Post ) {
                $post_id = $existing->ID;
            } else {
                $post_id = 0;
            }
        } else {
            $post_id = 0;
        }

        // Prepare post data
        $post_status = (string) $item->children( 'wp', true )->status;
        // Ensure status is valid, default to 'publish'
        if ( ! in_array( $post_status, array( 'publish', 'draft', 'pending', 'private' ), true ) ) {
            $post_status = 'publish';
        }
        
        $post_data = array(
            'post_type'    => $post_type,
            'post_title'   => (string) $item->title,
            'post_content' => (string) $item->children( 'content', true )->encoded,
            'post_excerpt' => (string) $item->children( 'excerpt', true )->encoded,
            'post_status'  => $post_status,
            'post_name'    => $post_name,
            'post_date'    => (string) $item->children( 'wp', true )->post_date,
            'post_date_gmt' => (string) $item->children( 'wp', true )->post_date_gmt,
            'post_author'   => 1,
        );

        // Insert or update post
        if ( $post_id > 0 ) {
            $post_data['ID'] = $post_id;
            $result = wp_update_post( $post_data, true );
        } else {
            $result = wp_insert_post( $post_data, true );
        }

        if ( is_wp_error( $result ) ) {
            continue;
        }

        $post_id = $result;

        // Import taxonomy terms
        $taxonomies_to_assign = array();
        foreach ( $item->category as $category ) {
            $domain = (string) $category['domain'];
            $name   = (string) $category;
            $slug   = (string) $category['nicename'];
            
            if ( in_array( $domain, array( 'resource_principle', 'resource_duration', 'activity_type' ), true ) ) {
                // Try to get term by slug first (more reliable), then by name
                $term = false;
                if ( ! empty( $slug ) ) {
                    $term = get_term_by( 'slug', $slug, $domain );
                }
                if ( ! $term && ! empty( $name ) ) {
                    $term = get_term_by( 'name', $name, $domain );
                }
                
                // Create term if it doesn't exist
                if ( ! $term || is_wp_error( $term ) ) {
                    $term_args = array();
                    if ( ! empty( $slug ) ) {
                        $term_args['slug'] = $slug;
                    }
                    $term_result = wp_insert_term( $name, $domain, $term_args );
                    if ( ! is_wp_error( $term_result ) ) {
                        $term = get_term( $term_result['term_id'], $domain );
                    }
                }
                
                if ( $term && ! is_wp_error( $term ) ) {
                    if ( ! isset( $taxonomies_to_assign[ $domain ] ) ) {
                        $taxonomies_to_assign[ $domain ] = array();
                    }
                    $taxonomies_to_assign[ $domain ][] = (int) $term->term_id;
                }
            }
        }
        
        // Assign all taxonomy terms (replace existing, don't append)
        foreach ( $taxonomies_to_assign as $taxonomy => $term_ids ) {
            wp_set_object_terms( $post_id, $term_ids, $taxonomy, false );
        }

        // Import post meta
        foreach ( $item->children( 'wp', true )->postmeta as $postmeta ) {
            $meta_key   = (string) $postmeta->meta_key;
            $meta_value = (string) $postmeta->meta_value;
            
            // Skip internal WordPress meta
            if ( strpos( $meta_key, '_edit_' ) === 0 || strpos( $meta_key, '_wp_' ) === 0 ) {
                continue;
            }

            // Support PHP-serialized meta (standard WXR) and JSON meta (more readable authoring).
            if ( is_serialized( $meta_value ) ) {
                update_post_meta( $post_id, $meta_key, maybe_unserialize( $meta_value ) );
                continue;
            }

            $trimmed = ltrim( $meta_value );
            if ( $trimmed !== '' && ( $trimmed[0] === '{' || $trimmed[0] === '[' ) ) {
                $decoded = json_decode( $meta_value, true );
                if ( json_last_error() === JSON_ERROR_NONE ) {
                    update_post_meta( $post_id, $meta_key, $decoded );
                    continue;
                }
            }

            update_post_meta( $post_id, $meta_key, $meta_value );
        }

        $counts[ $post_type ]++;
    }

    // Flush rewrite rules to ensure permalinks work
    flush_rewrite_rules( true );

    return $counts;
}
