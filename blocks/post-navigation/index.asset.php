<?php
// Dependencies of index.js, in the format @wordpress/scripts writes (this block has no build step).
return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor' ),
	'version'      => (string) filemtime( __DIR__ . '/index.js' ),
);
