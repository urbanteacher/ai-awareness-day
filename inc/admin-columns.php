<?php
/**
 * Admin list table columns: form submissions and resources (Downloads).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add admin columns for form submissions
 */
function aiad_form_submission_columns( $columns ): array {
    $new_columns = array();
    $new_columns['cb'] = $columns['cb'];
    $new_columns['title'] = __( 'Name', 'ai-awareness-day' );
    $new_columns['email'] = __( 'Email', 'ai-awareness-day' );
    $new_columns['role'] = __( 'Role', 'ai-awareness-day' );
    $new_columns['date'] = $columns['date'];
    return $new_columns;
}
add_filter( 'manage_form_submission_posts_columns', 'aiad_form_submission_columns' );

/**
 * Populate admin columns for form submissions
 */
function aiad_form_submission_column_content( $column, $post_id ): void {
    switch ( $column ) {
        case 'email':
            $email = get_post_meta( $post_id, '_submission_email', true );
            if ( $email ) {
                echo '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>';
            }
            break;
        case 'role':
            $involved_as = get_post_meta( $post_id, '_submission_involved_as', true );
            $role_labels = array(
                'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
                'parent'        => __( 'Parent', 'ai-awareness-day' ),
                'school_leader' => __( 'School leader', 'ai-awareness-day' ),
                'organisation' => __( 'Organisation', 'ai-awareness-day' ),
            );
            $role_display = isset( $role_labels[ $involved_as ] ) ? $role_labels[ $involved_as ] : $involved_as;
            echo esc_html( $role_display );
            break;
    }
}
add_action( 'manage_form_submission_posts_custom_column', 'aiad_form_submission_column_content', 10, 2 );

/**
 * Make email and role columns sortable
 */
function aiad_form_submission_sortable_columns( $columns ): array {
    $columns['email'] = 'email';
    $columns['role']  = 'role';
    return $columns;
}
add_filter( 'manage_edit-form_submission_sortable_columns', 'aiad_form_submission_sortable_columns' );

/**
 * Handle sorting by email and role meta keys in the admin list table.
 * meta_query with EXISTS ensures posts without the meta key still appear in results.
 */
function aiad_form_submission_orderby( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( $query->get( 'post_type' ) !== 'form_submission' ) {
        return;
    }
    $orderby = $query->get( 'orderby' );
    if ( $orderby === 'email' ) {
        $query->set( 'meta_key', '_submission_email' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'meta_query', array(
            array(
                'key'     => '_submission_email',
                'compare' => 'EXISTS',
            ),
        ) );
    } elseif ( $orderby === 'role' ) {
        $query->set( 'meta_key', '_submission_involved_as' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'meta_query', array(
            array(
                'key'     => '_submission_involved_as',
                'compare' => 'EXISTS',
            ),
        ) );
    }
}
add_action( 'pre_get_posts', 'aiad_form_submission_orderby' );

/**
 * Display submission details in admin edit screen.
 * Registered via add_meta_box so it only runs on the correct screen.
 *
 * @param WP_Post $post     Current post (add_meta_box passes this).
 * @param array   $metabox  Meta box args (unused).
 */
function aiad_form_submission_meta_box( $post, $metabox = null ): void {
    if ( ! $post || $post->post_type !== 'form_submission' ) {
        return;
    }

    $first_name = get_post_meta( $post->ID, '_submission_first_name', true );
    $last_name = get_post_meta( $post->ID, '_submission_last_name', true );
    $email = get_post_meta( $post->ID, '_submission_email', true );
    $involved_as = get_post_meta( $post->ID, '_submission_involved_as', true );
    $message = get_post_meta( $post->ID, '_submission_message', true );
    $school_name = get_post_meta( $post->ID, '_submission_school_name', true );
    $subject = get_post_meta( $post->ID, '_submission_subject', true );
    $child_school = get_post_meta( $post->ID, '_submission_child_school', true );
    $role_title = get_post_meta( $post->ID, '_submission_role_title', true );
    $organisation   = get_post_meta( $post->ID, '_submission_organisation', true );
    $org_type       = get_post_meta( $post->ID, '_submission_org_type', true );
    $checklist_keys = (array) get_post_meta( $post->ID, '_submission_checklist', true );

    $role_labels = array(
        'teacher'       => __( 'Teacher', 'ai-awareness-day' ),
        'parent'        => __( 'Parent', 'ai-awareness-day' ),
        'school_leader' => __( 'School leader', 'ai-awareness-day' ),
        'organisation' => __( 'Organisation', 'ai-awareness-day' ),
    );
    $role_display = isset( $role_labels[ $involved_as ] ) ? $role_labels[ $involved_as ] : $involved_as;

    ?>
    <div class="form-submission-details" style="padding: 20px;">
        <table class="form-table">
            <tr>
                <th><?php esc_html_e( 'Name', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $first_name . ' ' . $last_name ); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Email', 'ai-awareness-day' ); ?></th>
                <td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
            </tr>
            <tr>
                <th><?php esc_html_e( 'Getting involved as', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $role_display ); ?></td>
            </tr>
            <?php if ( $school_name ) : ?>
            <tr>
                <th><?php esc_html_e( 'School', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $school_name ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $subject ) : ?>
            <tr>
                <th><?php esc_html_e( 'Subject / Area', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $subject ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $child_school ) : ?>
            <tr>
                <th><?php esc_html_e( 'Child\'s School', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $child_school ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $role_title ) : ?>
            <tr>
                <th><?php esc_html_e( 'Role Title', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $role_title ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $organisation ) : ?>
            <tr>
                <th><?php esc_html_e( 'Organisation', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $organisation ); ?></td>
            </tr>
            <?php endif; ?>
            <?php if ( $org_type ) : ?>
            <?php
            $org_type_display = $org_type;
            if ( function_exists( 'aiad_get_organisation_type_options' ) ) {
                $org_types = aiad_get_organisation_type_options();
                if ( isset( $org_types[ $org_type ] ) ) {
                    $org_type_display = $org_types[ $org_type ];
                }
            }
            ?>
            <tr>
                <th><?php esc_html_e( 'Organisation Type', 'ai-awareness-day' ); ?></th>
                <td><?php echo esc_html( $org_type_display ); ?></td>
            </tr>
            <?php endif; ?>
            <?php
            if ( ! empty( $checklist_keys ) && function_exists( 'aiad_get_contact_checklist_labels' ) ) {
                $checklist_labels = aiad_get_contact_checklist_labels();
                $checklist_display = array();
                foreach ( $checklist_keys as $key ) {
                    if ( isset( $checklist_labels[ $key ] ) ) {
                        $checklist_display[] = $checklist_labels[ $key ];
                    }
                }
                if ( ! empty( $checklist_display ) ) :
                    ?>
            <tr>
                <th><?php esc_html_e( 'Interested in', 'ai-awareness-day' ); ?></th>
                <td><ul style="margin:0; padding-left:1.25rem;"><?php foreach ( $checklist_display as $label ) : ?><li><?php echo esc_html( $label ); ?></li><?php endforeach; ?></ul></td>
            </tr>
            <?php
                endif;
            }
            ?>
            <?php if ( $message ) : ?>
            <tr>
                <th><?php esc_html_e( 'Message', 'ai-awareness-day' ); ?></th>
                <td><?php echo nl2br( esc_html( $message ) ); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
    <?php
}
function aiad_register_form_submission_meta_box(): void {
    add_meta_box(
        'aiad_form_submission_details',
        __( 'Submission Details', 'ai-awareness-day' ),
        'aiad_form_submission_meta_box',
        'form_submission',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'aiad_register_form_submission_meta_box' );

/**
 * Add Position column to Partners list table.
 */
function aiad_partner_admin_columns( array $columns ): array {
    $new = array();
    foreach ( $columns as $key => $label ) {
        $new[ $key ] = $label;
        if ( 'title' === $key ) {
            $new['position']    = __( 'Position', 'ai-awareness-day' );
            $new['ai_resources'] = __( 'AI resources', 'ai-awareness-day' );
        }
    }
    return $new;
}
add_filter( 'manage_partner_posts_columns', 'aiad_partner_admin_columns' );

/**
 * Output Position column content.
 */
function aiad_partner_admin_column_content( string $column, int $post_id ): void {
    if ( 'position' === $column ) {
        $order = get_post_field( 'menu_order', $post_id );
        echo '<span class="aiad-partner-order" data-order="' . esc_attr( $order ) . '">' . esc_html( $order ) . '</span>';
    }
    if ( 'ai_resources' === $column ) {
        $flag = (string) get_post_meta( $post_id, '_partner_provides_ai_resources', true ) === '1';
        $url  = (string) get_post_meta( $post_id, '_partner_ai_resources_url', true );
        $home = (string) get_post_meta( $post_id, '_partner_url', true );
        $href = $url !== '' ? $url : $home;
        if ( $flag && $href !== '' ) {
            echo '<span aria-hidden="true">✓</span> <a href="' . esc_url( $href ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Link', 'ai-awareness-day' ) . '</a>';
        } elseif ( $flag ) {
            echo '<span style="color:#b45309" title="' . esc_attr__( 'Checked — add Website URL or AI resources URL', 'ai-awareness-day' ) . '">!</span>';
        } else {
            echo '—';
        }
    }
}
add_action( 'manage_partner_posts_custom_column', 'aiad_partner_admin_column_content', 10, 2 );

/**
 * Make Position column sortable.
 */
function aiad_partner_sortable_columns( array $columns ): array {
    $columns['position'] = 'menu_order';
    return $columns;
}
add_filter( 'manage_edit-partner_sortable_columns', 'aiad_partner_sortable_columns' );

/**
 * Default Partners list to sort by position ascending.
 */
function aiad_partner_default_orderby( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'partner' !== $query->get( 'post_type' ) ) {
        return;
    }
    if ( ! $query->get( 'orderby' ) ) {
        $query->set( 'orderby', 'menu_order' );
        $query->set( 'order', 'ASC' );
    }
}
add_action( 'pre_get_posts', 'aiad_partner_default_orderby' );

/**
 * Add Position field to Quick Edit panel.
 */
function aiad_partner_quick_edit_field( string $column_name, string $post_type ): void {
    if ( 'position' !== $column_name || 'partner' !== $post_type ) {
        return;
    }
    ?>
    <fieldset class="inline-edit-col-right">
        <div class="inline-edit-col">
            <?php wp_nonce_field( 'aiad_partner_quick_edit', 'aiad_partner_quick_edit_nonce' ); ?>
            <label>
                <span class="title"><?php esc_html_e( 'Position', 'ai-awareness-day' ); ?></span>
                <input type="number" name="aiad_partner_menu_order" class="aiad-partner-menu-order" value="0" min="0" step="1" style="width:60px;">
            </label>
        </div>
    </fieldset>
    <?php
}
add_action( 'quick_edit_custom_box', 'aiad_partner_quick_edit_field', 10, 2 );

/**
 * Save Position from Quick Edit.
 */
function aiad_partner_save_menu_order( int $post_id ): void {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['aiad_partner_menu_order'] ) ) {
        $nonce = isset( $_POST['aiad_partner_quick_edit_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_partner_quick_edit_nonce'] ) ) : '';
        if ( ! wp_verify_nonce( $nonce, 'aiad_partner_quick_edit' ) ) {
            return;
        }
        wp_update_post(
            array(
                'ID'         => $post_id,
                'menu_order' => absint( wp_unslash( $_POST['aiad_partner_menu_order'] ) ),
            )
        );
    }
}
add_action( 'save_post_partner', 'aiad_partner_save_menu_order' );

/**
 * Enqueue JS to populate Quick Edit Position field from the row value.
 */
function aiad_partner_quick_edit_js( string $hook ): void {
    if ( 'edit.php' !== $hook ) {
        return;
    }
    $screen = get_current_screen();
    if ( ! $screen || 'partner' !== $screen->post_type ) {
        return;
    }
    ?>
    <script>
    jQuery( function( $ ) {
        var $wp_inline_edit = inlineEditPost.edit;
        inlineEditPost.edit = function( id ) {
            $wp_inline_edit.apply( this, arguments );
            var postId = ( typeof id === 'object' ) ? parseInt( this.getId( id ) ) : id;
            var $row   = $( '#post-' + postId );
            var order  = $row.find( '.aiad-partner-order' ).data( 'order' );
            $( '#edit-' + postId ).find( '.aiad-partner-menu-order' ).val( order );
        };
    } );
    </script>
    <?php
}
add_action( 'admin_footer', 'aiad_partner_quick_edit_js' );

/**
 * Add Downloads column to resource list table
 *
 * @param array $columns List table columns.
 * @return array
 */
function aiad_resource_admin_columns( array $columns ): array {
    $columns['downloads'] = __( 'Downloads', 'ai-awareness-day' );
    $columns['views']     = __( 'Views', 'ai-awareness-day' );
    return $columns;
}
add_filter( 'manage_resource_posts_columns', 'aiad_resource_admin_columns' );

/**
 * Output Downloads and Previews column content
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function aiad_resource_admin_column_content( string $column, int $post_id ): void {
    if ( 'downloads' === $column ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        echo esc_html( number_format_i18n( $count ) );
    }
    if ( 'views' === $column ) {
        $count = absint( get_post_meta( $post_id, '_aiad_view_count', true ) );
        echo esc_html( number_format_i18n( $count ) );
    }
}
add_action( 'manage_resource_posts_custom_column', 'aiad_resource_admin_column_content', 10, 2 );

/**
 * Make Downloads and Previews columns sortable
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function aiad_resource_sortable_columns( array $columns ): array {
    $columns['downloads'] = 'downloads';
    $columns['views']     = 'views';
    return $columns;
}
add_filter( 'manage_edit-resource_sortable_columns', 'aiad_resource_sortable_columns' );

/**
 * Order by downloads or previews in admin when requested
 *
 * @param WP_Query $query Main query.
 */
function aiad_resource_admin_order_by_downloads( WP_Query $query ): void {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    if ( 'resource' !== ( $query->get( 'post_type' ) ?? '' ) ) {
        return;
    }
    $orderby = $query->get( 'orderby' ) ?? '';
    if ( 'downloads' === $orderby ) {
        $query->set( 'meta_key', '_aiad_download_count' );
        $query->set( 'orderby', 'meta_value_num' );
    } elseif ( 'views' === $orderby ) {
        $query->set( 'meta_key', '_aiad_view_count' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}
add_action( 'pre_get_posts', 'aiad_resource_admin_order_by_downloads' );
