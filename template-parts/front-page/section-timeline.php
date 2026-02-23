<?php
/**
 * Template Part: Live Timeline section (front page).
 *
 * Displays pinned + recent timeline entries with "Load more" via AJAX.
 * Include via get_template_part( 'template-parts/section', 'timeline' )
 * inside front-page.php, gated by aiad_is_section_visible( 'timeline' ).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_alignment_class = aiad_get_text_alignment_class();
$result = aiad_get_timeline_entries( 2 );
$entries = $result['entries'];
$has_more = $result['has_more'];

// Don't render the section if there are no entries at all
if ( empty( $entries ) ) {
    return;
}
?>
<?php
$days_to_go   = function_exists( 'aiad_timeline_days_until_event' ) ? aiad_timeline_days_until_event() : 0;
$schools      = function_exists( 'aiad_timeline_schools_registered_count' ) ? aiad_timeline_schools_registered_count() : 0;
$resources    = 0;
$resource_obj = wp_count_posts( 'resource' );
if ( $resource_obj && isset( $resource_obj->publish ) ) {
    $resources = (int) $resource_obj->publish;
}
$featured_resource_obj = wp_count_posts( 'featured_resource' );
if ( $featured_resource_obj && isset( $featured_resource_obj->publish ) ) {
    $resources += (int) $featured_resource_obj->publish;
}
$days_urgent = $days_to_go > 0 && $days_to_go < 30;
?>
<section class="section <?php echo esc_attr( $text_alignment_class ); ?>" id="timeline">
    <div class="container">
        <div class="fade-up">
            <span class="section-label section-label--live"><?php esc_html_e( 'Live', 'ai-awareness-day' ); ?></span>
            <h2 class="section-title"><?php esc_html_e( 'Campaign Updates', 'ai-awareness-day' ); ?></h2>

            <div class="timeline-stats-bar" role="status" aria-label="<?php esc_attr_e( 'Campaign stats', 'ai-awareness-day' ); ?>">
                <span class="timeline-stats-bar__stat timeline-stats-bar__days <?php echo $days_urgent ? ' timeline-stats-bar__days--urgent' : ''; ?>">
                    <span class="timeline-stats-bar__icon" aria-hidden="true">⏱</span>
                    <span class="timeline-stats-bar__value"><?php echo esc_html( (string) $days_to_go ); ?></span>
                    <span class="timeline-stats-bar__label--full"><?php esc_html_e( 'days to go', 'ai-awareness-day' ); ?></span>
                    <span class="timeline-stats-bar__label--short"><?php esc_html_e( 'days', 'ai-awareness-day' ); ?></span>
                </span>
                <span class="timeline-stats-bar__sep" aria-hidden="true">·</span>
                <span class="timeline-stats-bar__stat timeline-stats-bar__stat--schools">
                    <span class="timeline-stats-bar__value"><?php echo esc_html( (string) $schools ); ?></span>
                    <span class="timeline-stats-bar__label--full"><?php esc_html_e( 'schools registered', 'ai-awareness-day' ); ?></span>
                    <span class="timeline-stats-bar__label--short"><?php esc_html_e( 'schools', 'ai-awareness-day' ); ?></span>
                </span>
                <span class="timeline-stats-bar__sep" aria-hidden="true">·</span>
                <span class="timeline-stats-bar__stat">
                    <span class="timeline-stats-bar__value"><?php echo esc_html( (string) $resources ); ?></span>
                    <span class="timeline-stats-bar__label--full"><?php esc_html_e( 'free resources', 'ai-awareness-day' ); ?></span>
                    <span class="timeline-stats-bar__label--short"><?php esc_html_e( 'resources', 'ai-awareness-day' ); ?></span>
                </span>
            </div>

        </div>

        <div class="timeline" id="timeline-feed" data-offset="<?php echo esc_attr( (string) count( $entries ) ); ?>">
            <div class="timeline__track">
                <?php foreach ( $entries as $entry ) : ?>
                    <?php echo aiad_render_timeline_entry( $entry ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — escaped inside renderer ?>
                <?php endforeach; ?>
            </div>

            <?php if ( $has_more ) : ?>
                <div class="timeline__load-more fade-up">
                    <button type="button" class="btn-action timeline__load-btn" id="timeline-load-more">
                        <?php esc_html_e( 'Load more updates', 'ai-awareness-day' ); ?>
                        <span class="btn-action__icon" aria-hidden="true">+</span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
