<?php
/**
 * Entry figure helpers: focal point for cropped featured images.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/components/focal-point-picker/
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Legacy post meta key (migrated to feed key on read).
 */
function aiad_thumbnail_focal_point_meta_key(): string {
	return '_aiad_thumbnail_focal_point';
}

/**
 * Focal meta key for homepage timeline cards (swipe / magazine).
 */
function aiad_thumbnail_focal_point_feed_meta_key(): string {
	return '_aiad_thumbnail_focal_point_feed';
}

/**
 * Focal meta key for single timeline / resource blog-style pages.
 */
function aiad_thumbnail_focal_point_single_meta_key(): string {
	return '_aiad_thumbnail_focal_point_single';
}

/**
 * Meta key for a display context.
 *
 * @param string $context feed|single
 */
function aiad_thumbnail_focal_point_meta_key_for_context( string $context ): string {
	return 'single' === $context
		? aiad_thumbnail_focal_point_single_meta_key()
		: aiad_thumbnail_focal_point_feed_meta_key();
}

/**
 * Default focal point when none is saved (matches entry-figure.css).
 *
 * @param string $context feed|single
 * @return array{x: float, y: float}
 */
function aiad_default_thumbnail_focal_point( string $context = 'feed' ): array {
	if ( 'single' === $context ) {
		return array(
			'x' => 0.5,
			'y' => 0.5,
		);
	}

	return array(
		'x' => 0.5,
		'y' => 0.3,
	);
}

/**
 * Focal contexts available for a post type in admin.
 *
 * @param string $post_type timeline|resource|…
 * @return string[] feed|single
 */
function aiad_thumbnail_focal_point_contexts_for_post_type( string $post_type ): array {
	if ( 'timeline' === $post_type ) {
		return array( 'feed', 'single' );
	}
	if ( 'resource' === $post_type ) {
		return array( 'single' );
	}
	return array();
}

/**
 * Admin label for a focal context.
 *
 * @param string $context feed|single
 */
function aiad_thumbnail_focal_point_context_label( string $context ): string {
	if ( 'single' === $context ) {
		return __( 'Blog post page', 'ai-awareness-day' );
	}
	return __( 'Homepage timeline cards', 'ai-awareness-day' );
}

/**
 * Normalize focal point from meta, JSON, or percent inputs.
 *
 * @param mixed $raw Stored meta or array.
 * @return array{x: float, y: float}|null
 */
function aiad_normalize_focal_point( $raw ): ?array {
	if ( is_string( $raw ) && $raw !== '' ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$raw = $decoded;
		}
	}

	if ( ! is_array( $raw ) ) {
		return null;
	}

	$x = null;
	$y = null;

	if ( isset( $raw['x'], $raw['y'] ) ) {
		$x = (float) $raw['x'];
		$y = (float) $raw['y'];
	} elseif ( isset( $raw['left'], $raw['top'] ) ) {
		$x = (float) $raw['left'];
		$y = (float) $raw['top'];
	}

	if ( null === $x || null === $y ) {
		return null;
	}

	// Accept 0–100 percentages from admin fields.
	if ( $x > 1.0 || $y > 1.0 ) {
		$x /= 100.0;
		$y /= 100.0;
	}

	$x = min( 1.0, max( 0.0, $x ) );
	$y = min( 1.0, max( 0.0, $y ) );

	return array(
		'x' => $x,
		'y' => $y,
	);
}

/**
 * Sanitize focal point for register_post_meta.
 *
 * @param mixed $value Raw value.
 * @return array{x: float, y: float}|null
 */
function aiad_sanitize_focal_point_meta( $value ): ?array {
	return aiad_normalize_focal_point( $value );
}

/**
 * Register focal point meta for timeline and resource posts.
 */
function aiad_register_thumbnail_focal_point_meta(): void {
	$schema = array(
		'type'       => 'object',
		'properties' => array(
			'x' => array( 'type' => 'number' ),
			'y' => array( 'type' => 'number' ),
		),
	);

	$meta_args = array(
		'type'              => 'object',
		'single'            => true,
		'show_in_rest'      => array(
			'schema' => $schema,
		),
		'auth_callback'     => static function () {
			return current_user_can( 'edit_posts' );
		},
		'sanitize_callback' => 'aiad_sanitize_focal_point_meta',
	);

	foreach ( array( 'timeline', 'resource' ) as $post_type ) {
		foreach ( array(
			aiad_thumbnail_focal_point_feed_meta_key(),
			aiad_thumbnail_focal_point_single_meta_key(),
			aiad_thumbnail_focal_point_meta_key(),
		) as $meta_key ) {
			register_post_meta( $post_type, $meta_key, $meta_args );
		}
	}
}
add_action( 'init', 'aiad_register_thumbnail_focal_point_meta' );

/**
 * Focal point for a post's featured image (post meta, then attachment meta).
 *
 * @param int    $post_id Post ID.
 * @param string $context feed|single — homepage cards vs blog post page.
 * @return array{x: float, y: float}|null Null when unset (CSS default applies).
 */
function aiad_get_post_thumbnail_focal_point( int $post_id, string $context = 'feed' ): ?array {
	$keys = array( aiad_thumbnail_focal_point_meta_key_for_context( $context ) );
	if ( 'feed' === $context ) {
		$keys[] = aiad_thumbnail_focal_point_meta_key();
	}
	$keys[] = 'featured_image_focal_point';

	foreach ( $keys as $key ) {
		$point = aiad_normalize_focal_point( get_post_meta( $post_id, $key, true ) );
		if ( $point ) {
			return $point;
		}
	}

	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id > 0 ) {
		foreach ( $keys as $key ) {
			$point = aiad_normalize_focal_point( get_post_meta( $thumb_id, $key, true ) );
			if ( $point ) {
				return $point;
			}
		}
	}

	// Until a blog-specific focal is saved, inherit homepage/feed focal.
	if ( 'single' === $context && ! get_post_meta( $post_id, aiad_thumbnail_focal_point_single_meta_key(), true ) ) {
		return aiad_get_post_thumbnail_focal_point( $post_id, 'feed' );
	}

	return null;
}

/**
 * Inline object-position style for cover-fit images.
 *
 * @param array{x: float, y: float}|null $point Focal point.
 * @return string e.g. "object-position:50% 30%;" or empty.
 */
function aiad_focal_point_inline_style( ?array $point ): string {
	if ( ! $point ) {
		return '';
	}

	$x = round( (float) $point['x'] * 100, 1 );
	$y = round( (float) $point['y'] * 100, 1 );

	return sprintf( 'object-position:%s%% %s%%;', $x, $y );
}

/**
 * style="" attribute for entry-figure images.
 *
 * @param int    $post_id Post whose featured image is shown.
 * @param string $fit     cover|contain — contain skips focal (logos).
 * @param string $context feed|single
 * @return string HTML attribute fragment (leading space + style=) or empty.
 */
function aiad_entry_figure_img_style_attr( int $post_id, string $fit = 'cover', string $context = 'feed' ): string {
	if ( 'contain' === $fit ) {
		return '';
	}

	$point = aiad_get_post_thumbnail_focal_point( $post_id, $context )
		?? aiad_default_thumbnail_focal_point( $context );
	$style = aiad_focal_point_inline_style( $point );
	if ( $style === '' ) {
		return '';
	}

	return ' style="' . esc_attr( $style ) . '"';
}

/**
 * Featured image markup for entry-figure frames.
 *
 * @param int                  $post_id Post ID.
 * @param string               $size    Image size.
 * @param array<string, mixed> $attrs   Extra attributes for wp_get_attachment_image().
 * @return string HTML or empty.
 */
function aiad_entry_figure_thumbnail( int $post_id, string $size = 'large', array $attrs = array() ): string {
	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id <= 0 ) {
		return '';
	}

	$classes = isset( $attrs['class'] ) ? (string) $attrs['class'] : 'resource-activity-figure__img';
	$fit     = isset( $attrs['fit'] ) ? (string) $attrs['fit'] : 'cover';
	$context = isset( $attrs['focal_context'] ) ? (string) $attrs['focal_context'] : 'single';
	unset( $attrs['fit'], $attrs['focal_context'] );

	$img_attrs = array_merge(
		array(
			'class'   => $classes,
			'loading' => 'lazy',
		),
		$attrs
	);
	unset( $img_attrs['class'] );
	$img_attrs['class'] = $classes;

	$focal_style = aiad_focal_point_inline_style(
		'contain' === $fit
			? null
			: ( aiad_get_post_thumbnail_focal_point( $post_id, $context ) ?? aiad_default_thumbnail_focal_point( $context ) )
	);
	if ( $focal_style !== '' ) {
		$existing = isset( $img_attrs['style'] ) ? (string) $img_attrs['style'] : '';
		$img_attrs['style'] = trim( $existing . ' ' . $focal_style );
	}

	return wp_get_attachment_image( $thumb_id, $size, false, $img_attrs );
}

/**
 * Enqueue FocalPointPicker for timeline and resource edit screens.
 *
 * @param string $hook Current admin page hook.
 */
function aiad_enqueue_thumbnail_focal_point_picker_admin( string $hook ): void {
	if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
		return;
	}

	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'timeline', 'resource' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'wp-components' );

	$css_path = AIAD_DIR . '/admin/css/aiad-focal-point-picker.css';
	wp_enqueue_style(
		'aiad-focal-point-picker',
		AIAD_URI . '/admin/css/aiad-focal-point-picker.css',
		array( 'wp-components' ),
		file_exists( $css_path ) ? (string) filemtime( $css_path ) : AIAD_VERSION
	);

	$js_path = AIAD_DIR . '/assets/js/admin-focal-point-picker.js';
	wp_enqueue_script(
		'aiad-admin-focal-point-picker',
		AIAD_URI . '/assets/js/admin-focal-point-picker.js',
		array( 'wp-element', 'wp-components', 'wp-i18n' ),
		file_exists( $js_path ) ? (string) filemtime( $js_path ) : AIAD_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'aiad_enqueue_thumbnail_focal_point_picker_admin' );

/**
 * Admin fields: drag focal point picker + hidden coordinates for save.
 *
 * @param WP_Post $post Post being edited.
 */
function aiad_render_thumbnail_focal_point_picker_block( WP_Post $post, string $context, string $image_url ): void {
	$point   = aiad_get_post_thumbnail_focal_point( $post->ID, $context )
		?? aiad_default_thumbnail_focal_point( $context );
	$x_pct   = round( (float) $point['x'] * 100 );
	$y_pct   = round( (float) $point['y'] * 100 );
	$default = aiad_default_thumbnail_focal_point( $context );
	?>
	<div class="aiad-focal-point-context">
		<strong class="aiad-rd-label"><?php echo esc_html( aiad_thumbnail_focal_point_context_label( $context ) ); ?></strong>
		<?php if ( 'single' === $context ) : ?>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Taller crop on the blog post page — drag to keep faces or headline text in view.', 'ai-awareness-day' ); ?>
			</p>
		<?php else : ?>
			<p class="description" style="margin-top:0;">
				<?php esc_html_e( 'Wide crop on homepage timeline cards (swipe + magazine).', 'ai-awareness-day' ); ?>
			</p>
		<?php endif; ?>
		<div
			class="aiad-focal-point-picker-root"
			data-focal-context="<?php echo esc_attr( $context ); ?>"
			data-image-url="<?php echo esc_url( $image_url ); ?>"
			data-focal-point="<?php echo esc_attr( wp_json_encode( $point ) ); ?>"
			data-default-x="<?php echo esc_attr( (string) $default['x'] ); ?>"
			data-default-y="<?php echo esc_attr( (string) $default['y'] ); ?>"
		>
			<div class="aiad-focal-point-picker-mount"<?php echo $image_url ? '' : ' hidden'; ?>></div>
			<p class="aiad-focal-point-picker-empty description"<?php echo $image_url ? ' hidden' : ''; ?>>
				<?php esc_html_e( 'Set a Featured Image above, then drag to set the focal point.', 'ai-awareness-day' ); ?>
			</p>
			<input type="hidden" name="aiad_thumbnail_focal_<?php echo esc_attr( $context ); ?>_x" value="<?php echo esc_attr( (string) $x_pct ); ?>" />
			<input type="hidden" name="aiad_thumbnail_focal_<?php echo esc_attr( $context ); ?>_y" value="<?php echo esc_attr( (string) $y_pct ); ?>" />
			<p class="aiad-focal-point-picker-coords description">
				<?php
				printf(
					/* translators: 1: horizontal %, 2: vertical % */
					esc_html__( '%1$s%% · %2$s%%', 'ai-awareness-day' ),
					esc_html( (string) $x_pct ),
					esc_html( (string) $y_pct )
				);
				?>
			</p>
		</div>
	</div>
	<?php
}

function aiad_render_thumbnail_focal_point_fields( WP_Post $post ): void {
	$contexts  = aiad_thumbnail_focal_point_contexts_for_post_type( $post->post_type );
	$thumb_id  = (int) get_post_thumbnail_id( $post->ID );
	$image_url = $thumb_id > 0 ? (string) wp_get_attachment_image_url( $thumb_id, 'large' ) : '';

	if ( empty( $contexts ) ) {
		return;
	}
	?>
	<div class="aiad-rd-section aiad-focal-point-fields">
		<strong class="aiad-rd-label"><?php esc_html_e( 'Image focal point', 'ai-awareness-day' ); ?></strong>
		<p class="description" style="margin-top:0;">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: link to Focal Point Picker docs */
					__( 'Separate focus for homepage cards vs the full blog post. Drag using the %s.', 'ai-awareness-day' ),
					'<a href="https://developer.wordpress.org/block-editor/reference-guides/components/focal-point-picker/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Focal Point Picker', 'ai-awareness-day' ) . '</a>'
				)
			);
			?>
		</p>
		<?php
		foreach ( $contexts as $context ) {
			aiad_render_thumbnail_focal_point_picker_block( $post, $context, $image_url );
		}
		?>
		<p class="description"><?php esc_html_e( 'Only applies when the image uses cover cropping (not partner logos).', 'ai-awareness-day' ); ?></p>
	</div>
	<?php
}


/**
 * Save focal point from POST (timeline + resource meta boxes).
 *
 * @param int $post_id Post ID.
 */
function aiad_save_thumbnail_focal_point_from_post( int $post_id ): void {
	$post_type = get_post_type( $post_id );
	if ( ! $post_type ) {
		return;
	}

	foreach ( aiad_thumbnail_focal_point_contexts_for_post_type( $post_type ) as $context ) {
		$x_key = 'aiad_thumbnail_focal_' . $context . '_x';
		$y_key = 'aiad_thumbnail_focal_' . $context . '_y';

		if ( ! isset( $_POST[ $x_key ], $_POST[ $y_key ] ) ) {
			continue;
		}

		$point = aiad_normalize_focal_point(
			array(
				'x' => (int) $_POST[ $x_key ],
				'y' => (int) $_POST[ $y_key ],
			)
		);

		$meta_key = aiad_thumbnail_focal_point_meta_key_for_context( $context );
		if ( ! $point ) {
			delete_post_meta( $post_id, $meta_key );
			continue;
		}

		update_post_meta( $post_id, $meta_key, $point );
	}

	$feed_point = aiad_get_post_thumbnail_focal_point( $post_id, 'feed' );
	if ( $feed_point ) {
		update_post_meta( $post_id, aiad_thumbnail_focal_point_meta_key(), $feed_point );
	} else {
		delete_post_meta( $post_id, aiad_thumbnail_focal_point_meta_key() );
	}
}
