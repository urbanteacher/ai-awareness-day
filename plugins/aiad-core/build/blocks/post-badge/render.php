<?php
/**
 * Featured badge block: shows "Featured" above the title of a sticky post.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( is_sticky( (int) ( $block->context['postId'] ?? get_the_ID() ) ) ) {
	echo '<p class="single-post-entry__badge">' . esc_html__( 'Featured', 'ai-awareness-day' ) . '</p>';
}
