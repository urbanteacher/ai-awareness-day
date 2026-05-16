<?php
/**
 * Archive template: Campaign updates (timeline CPT).
 *
 * @package AI_Awareness_Day
 */

get_header();

$archive_url = get_post_type_archive_link( 'timeline' );
if ( ! $archive_url ) {
    $archive_url = home_url( '/timeline/' );
}

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

$filter = isset( $_GET['timeline_icon'] ) ? sanitize_text_field( wp_unslash( $_GET['timeline_icon'] ) ) : 'all';
$icon_options = function_exists( 'aiad_timeline_icon_options' ) ? aiad_timeline_icon_options() : array();
if ( 'all' !== $filter && ! isset( $icon_options[ $filter ] ) ) {
    $filter = 'all';
}

$result   = function_exists( 'aiad_get_timeline_archive_entries' )
    ? aiad_get_timeline_archive_entries( $paged, $filter )
    : array( 'entries' => array(), 'max_pages' => 0, 'found' => 0 );
$entries  = $result['entries'];
$max_pages = $result['max_pages'];

$show_filters = ! empty( $icon_options );
?>
<main id="main" role="main" class="container-width-standard timeline-archive">
    <section class="section timeline-archive__root">
        <div class="container">
            <span class="section-label section-label--live"><?php esc_html_e( 'Live', 'ai-awareness-day' ); ?></span>
            <h1 class="section-title timeline-archive__title"><?php esc_html_e( 'Campaign updates', 'ai-awareness-day' ); ?></h1>
            <p class="section-desc"><?php esc_html_e( 'News, partners, milestones and stories from AI Awareness Day.', 'ai-awareness-day' ); ?></p>

            <?php if ( $show_filters ) : ?>
                <div class="timeline-filters" role="group" aria-label="<?php esc_attr_e( 'Filter timeline updates', 'ai-awareness-day' ); ?>">
                    <button type="button" class="timeline-filter-btn<?php echo 'all' === $filter ? ' timeline-filter-btn--active' : ''; ?>" data-filter="all">
                        <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
                    </button>
                    <?php foreach ( $icon_options as $value => $label ) : ?>
                        <button type="button" class="timeline-filter-btn<?php echo $filter === $value ? ' timeline-filter-btn--active' : ''; ?>" data-filter="<?php echo esc_attr( $value ); ?>">
                            <?php echo esc_html( $label ); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="timeline-feed" id="timeline-feed" data-timeline-archive="1">
                <?php
                if ( function_exists( 'aiad_render_timeline_archive_feed' ) ) {
                    echo aiad_render_timeline_archive_feed( $entries ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
                ?>
            </div>

            <?php if ( $max_pages > 1 ) : ?>
                <nav class="timeline-archive__pagination" aria-label="<?php esc_attr_e( 'Updates pagination', 'ai-awareness-day' ); ?>">
                    <?php
                    $link_base = $archive_url;
                    if ( 'all' !== $filter ) {
                        $link_base = add_query_arg( 'timeline_icon', $filter, $link_base );
                    }
                    $big = 999999999;
                    echo wp_kses_post(
                        paginate_links(
                            array(
                                'base'      => str_replace( $big, '%#%', esc_url( add_query_arg( 'paged', $big, $link_base ) ) ),
                                'format'    => '',
                                'total'     => $max_pages,
                                'current'   => $paged,
                                'prev_text' => __( '← Newer', 'ai-awareness-day' ),
                                'next_text' => __( 'Older →', 'ai-awareness-day' ),
                            )
                        )
                    );
                    ?>
                </nav>
            <?php endif; ?>

            <p class="timeline-archive__back">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>#timeline"><?php esc_html_e( '← Back to homepage', 'ai-awareness-day' ); ?></a>
            </p>
        </div>
    </section>
</main>
<?php
get_footer();
