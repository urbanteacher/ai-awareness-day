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
 * Handle sorting by email and role meta keys in the admin list table
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
    } elseif ( $orderby === 'role' ) {
        $query->set( 'meta_key', '_submission_involved_as' );
        $query->set( 'orderby', 'meta_value' );
    }
}
add_action( 'pre_get_posts', 'aiad_form_submission_orderby' );

/**
 * Display submission details in admin edit screen
 */
function aiad_form_submission_meta_box(): void {
    global $post;

    if ( $post->post_type !== 'form_submission' ) {
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
        <h3><?php esc_html_e( 'Submission Details', 'ai-awareness-day' ); ?></h3>
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
add_action( 'edit_form_after_title', 'aiad_form_submission_meta_box' );

/**
 * Add Downloads column to resource list table
 *
 * @param array $columns List table columns.
 * @return array
 */
function aiad_resource_admin_columns( array $columns ): array {
    $columns['downloads'] = __( 'Downloads', 'ai-awareness-day' );
    return $columns;
}
add_filter( 'manage_resource_posts_columns', 'aiad_resource_admin_columns' );

/**
 * Output Downloads column content
 *
 * @param string $column  Column name.
 * @param int    $post_id Post ID.
 */
function aiad_resource_admin_column_content( string $column, int $post_id ): void {
    if ( 'downloads' === $column ) {
        $count = absint( get_post_meta( $post_id, '_aiad_download_count', true ) );
        echo esc_html( number_format_i18n( $count ) );
    }
}
add_action( 'manage_resource_posts_custom_column', 'aiad_resource_admin_column_content', 10, 2 );

/**
 * Make Downloads column sortable
 *
 * @param array $columns Sortable columns.
 * @return array
 */
function aiad_resource_sortable_columns( array $columns ): array {
    $columns['downloads'] = 'downloads';
    return $columns;
}
add_filter( 'manage_edit-resource_sortable_columns', 'aiad_resource_sortable_columns' );

/**
 * Order by downloads in admin when requested
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
    if ( 'downloads' !== ( $query->get( 'orderby' ) ?? '' ) ) {
        return;
    }
    $query->set( 'meta_key', '_aiad_download_count' );
    $query->set( 'orderby', 'meta_value_num' );
}
add_action( 'pre_get_posts', 'aiad_resource_admin_order_by_downloads' );
