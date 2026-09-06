<?php
/**
 * Template Part: Live Timeline section (front page).
 *
 * Displays pinned + recent timeline entries (mobile swipe + desktop magazine).
 * Include via get_template_part( 'template-parts/section', 'timeline' )
 * inside front-page.php, gated by aiad_is_section_visible( 'timeline' ).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_alignment_class = aiad_get_text_alignment_class();
$per_page = function_exists( 'aiad_timeline_feed_per_page' ) ? aiad_timeline_feed_per_page() : -1;
$result   = aiad_get_timeline_entries( $per_page );
$entries  = $result['entries'];

// Don't render the section if there are no entries at all
if ( empty( $entries ) ) {
    return;
}

// Get available icon types for filtering
$icon_options = aiad_timeline_icon_options();
$show_filters = ! empty( $entries ) && count( $icon_options ) > 1;
?>
<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="timeline">
    <div class="container">
        <div class="fade-up">
            <span class="section-label"><?php esc_html_e( 'Latest updates', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Campaign Updates', 'ai-awareness-day' ); ?></h2>
        </div>

        <?php if ( $show_filters ) : ?>
        <div class="timeline-filters" role="group" aria-label="<?php esc_attr_e( 'Filter timeline updates', 'ai-awareness-day' ); ?>">
            <button type="button" class="timeline-filter-btn timeline-filter-btn--active" data-filter="all">
                <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
            </button>
            <?php foreach ( $icon_options as $value => $label ) : ?>
                <button type="button" class="timeline-filter-btn" data-filter="<?php echo esc_attr( $value ); ?>">
                    <?php echo esc_html( $label ); ?>
                </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="timeline-feed" id="timeline-feed">
            <?php echo aiad_render_timeline_feed_layouts( $entries ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <?php
        $timeline_archive_url = get_post_type_archive_link( 'timeline' );
        if ( ! $timeline_archive_url ) {
            $timeline_archive_url = home_url( '/timeline/' );
        }
        ?>
        <div class="timeline-section__actions fade-up">
            <a class="timeline-section__cta" href="<?php echo esc_url( $timeline_archive_url ); ?>">
                <?php esc_html_e( 'View all updates →', 'ai-awareness-day' ); ?>
            </a>
        </div>
    </div>
</section>
