<?php
/**
 * Comments block: the comments and comment form the PHP templates print with comments_template() (comments.php),
 * when comments are open or the post has some.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

if ( comments_open() || get_comments_number() ) {
	comments_template();
}
