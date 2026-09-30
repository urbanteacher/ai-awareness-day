<?php
/**
 * The featured resources' tiles and "View all" link: the homepage's featured resources section and the resource tiles
 * block (blocks/resource-tiles) both print them.
 *
 * @package AI_Awareness_Day
 */
if ( ! defined( 'ABSPATH' ) ) {
    return;
}

$featured_resources = $args['query'];
?>
                    <div class="resource-tiles">
                        <?php
                        while ( $featured_resources->have_posts() ) :
                            $featured_resources->the_post();
                            get_template_part( 'template-parts/components/resource-tile', null, array(
                                'link'     => get_post_meta( get_the_ID(), '_featured_resource_url', true ) ?: get_permalink(),
                                'external' => (bool) get_post_meta( get_the_ID(), '_featured_resource_url', true ),
                                'track_id' => get_the_ID(),
                            ) );
                        endwhile;
                        ?>
                        <?php
                        $featured_archive_url = get_post_type_archive_link('featured_resource');
                        if ( ! $featured_archive_url ) {
                            $featured_archive_url = home_url( '/from-partners/' );
                            if ( get_option( 'permalink_structure' ) === '' ) {
                                $featured_archive_url = add_query_arg( 'post_type', 'featured_resource', home_url( '/' ) );
                            }
                        }
                        ?>
                    </div>
                    <a class="resource-tiles__more" href="<?php echo esc_url( $featured_archive_url ); ?>"><?php esc_html_e( 'View all handpicked resources', 'ai-awareness-day' ); ?> <span aria-hidden="true">&rarr;</span></a>
