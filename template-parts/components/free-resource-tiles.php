<?php
/**
 * The free resources' tiles and "View all" link: the homepage's free resources section and the resource tiles block
 * (blocks/resource-tiles) both print them, for the resources picked in Appearance > Edit Homepage.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$free_resources = $args['query'];
?>
            <div class="resource-tiles">
                <?php
                while ( $free_resources->have_posts() ) :
                    $free_resources->the_post();
                    get_template_part( 'template-parts/components/resource-tile', null, array() );
                endwhile;
                ?>
                <?php
                $resources_archive_url = get_post_type_archive_link( 'resource' );
                if ( ! $resources_archive_url ) {
                    $resources_archive_url = home_url( '/resources/' );
                    if ( get_option( 'permalink_structure' ) === '' ) {
                        $resources_archive_url = add_query_arg( 'post_type', 'resource', home_url( '/' ) );
                    }
                }
                ?>
            </div>
            <a class="resource-tiles__more" href="<?php echo esc_url( $resources_archive_url ); ?>"><?php esc_html_e( 'View all free resources', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></a>
