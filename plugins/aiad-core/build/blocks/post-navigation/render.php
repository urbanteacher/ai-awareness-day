<?php
/**
 * Previous and next posts block: links to the previous and next posts, as single.php printed them.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

the_post_navigation(
	array(
		'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'ai-awareness-day' ) . '</span><span class="nav-title">%title</span>',
		'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'ai-awareness-day' ) . '</span><span class="nav-title">%title</span>',
	)
);
