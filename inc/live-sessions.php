<?php
/**
 * Events (live sessions): markup helpers for the schedule — session action link and its icon, audience filter tabs,
 * and the inline filter / calendar script.
 *
 * The live_session post type, its data helpers, admin UI and calendar (ICS) feed live in the aiad-core plugin
 * (plugins/aiad-core/modules/live-sessions.php).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * External-link icon for conference / CPD cards.
 */
function aiad_session_link_icon_svg(): string {
	if ( function_exists( 'aiad_timeline_link_icon_svg' ) ) {
		return aiad_timeline_link_icon_svg();
	}
	return '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
}

/**
 * Primary schedule CTA: “Join” for live sessions, chain-link icon for conferences.
 *
 * @param int    $post_id live_session post ID.
 * @param string $url     Registration / join URL.
 * @param string $block   BEM block prefix (e.g. aiad-schedule-card, aiad-schedule-table).
 */
function aiad_render_session_action_link( int $post_id, string $url, string $block = 'aiad-schedule-card' ): string {
	if ( ! aiad_session_show_join_link( $post_id ) || $url === '' ) {
		return '';
	}

	$is_conference = aiad_session_is_conference( $post_id );
	$label         = aiad_session_cta_label( $post_id );
	$classes       = $block . '__join ' . $block . '__link--action';
	if ( $is_conference ) {
		$classes .= ' ' . $block . '__link--icon';
	}
	if ( $block === 'aiad-schedule-table' ) {
		$classes = 'aiad-schedule-table__cta' . ( $is_conference ? ' aiad-schedule-table__cta--icon' : '' );
	}
	if ( $block === 'session-single' ) {
		$classes = 'session-single__btn session-single__btn--primary' . ( $is_conference ? ' session-single__btn--icon' : '' );
	}

	$inner = $is_conference
		? '<span class="' . esc_attr( $block ) . '__link-icon" aria-hidden="true">' . aiad_session_link_icon_svg() . '</span>'
		: esc_html( $label );

	if ( $is_conference && $block === 'session-single' ) {
		$inner = '<span class="session-single__link-icon" aria-hidden="true">' . aiad_session_link_icon_svg() . '</span>';
	} elseif ( ! $is_conference && $block === 'session-single' ) {
		$inner = esc_html( $label ) . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>';
	}

	return sprintf(
		'<a class="%1$s" href="%2$s" data-session-id="%3$s" target="_blank" rel="noopener"%4$s>%5$s</a>',
		esc_attr( $classes ),
		esc_url( $url ),
		esc_attr( (string) $post_id ),
		$is_conference ? ' aria-label="' . esc_attr( $label ) . '"' : '',
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG from trusted helper.
	);
}

/**
 * Audience filters shared by the front-page schedule block and /schedule/ archive.
 * Markup matches the Live Timeline filter row (timeline-filters / timeline-filter-btn).
 *
 * @param array<string, string> $audience_labels Slug => display name.
 * @param array<string, int>    $audience_counts Retained for API compatibility; not displayed (timeline pills have no counts).
 * @param int                   $session_count   Retained for API compatibility; not displayed.
 */
function aiad_render_schedule_audience_tabs( array $audience_labels, array $audience_counts, int $session_count ): void {
    ?>
    <div class="timeline-filters fade-up" role="group" aria-label="<?php esc_attr_e( 'Filter sessions by audience', 'ai-awareness-day' ); ?>">
        <button type="button" class="timeline-filter-btn timeline-filter-btn--active" data-filter="all">
            <?php esc_html_e( 'All', 'ai-awareness-day' ); ?>
        </button>
        <?php foreach ( $audience_labels as $slug => $name ) : ?>
            <button type="button" class="timeline-filter-btn" data-filter="<?php echo esc_attr( $slug ); ?>">
                <?php echo esc_html( $name ); ?>
            </button>
        <?php endforeach; ?>
        <button type="button" class="timeline-filter-btn" disabled aria-disabled="true">
            <?php esc_html_e( 'Parents', 'ai-awareness-day' ); ?>
            <span class="timeline-filter-btn__suffix" aria-hidden="true"><?php esc_html_e( ' · Soon', 'ai-awareness-day' ); ?></span>
        </button>
    </div>
    <?php
}

/**
 * One inline script per page: schedule archive audience filters + ICS, and homepage spotlight ICS/share.
 */
function aiad_print_schedule_audience_filter_script(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;
    // A file, not an inline tag. It runs once the page is parsed, and may be called after the footer scripts are
    // printed, so it is enqueued in the footer either way.
    $file = AIAD_DIR . '/assets/js/schedule.js';
    wp_enqueue_script(
        'aiad-schedule',
        AIAD_URI . '/assets/js/schedule.js',
        array( 'wp-i18n' ),
        file_exists( $file ) ? filemtime( $file ) : AIAD_VERSION,
        array( 'in_footer' => true )
    );
    wp_set_script_translations( 'aiad-schedule', 'ai-awareness-day' );
}
