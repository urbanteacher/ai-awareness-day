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
 * Post meta key for thumbnail focal point ({ x, y } in 0–1).
 */
function aiad_thumbnail_focal_point_meta_key(): string {
	return '_aiad_thumbnail_focal_point';
}

/**
 * Default focal point when none is saved (matches entry-figure.css).
 *
 * @return array{x: float, y: float}
 */
function aiad_default_thumbnail_focal_point(): array {
	return array(
		'x' => 0.5,
		'y' => 0.3,
	);
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

	foreach ( array( 'timeline', 'resource' ) as $post_type ) {
		register_post_meta(
			$post_type,
			aiad_thumbnail_focal_point_meta_key(),
			array(
				'type'              => 'object',
				'single'            => true,
				'show_in_rest'      => array(
					'schema' => $schema,
				),
				'auth_callback'     => static function () {
					return current_user_can( 'edit_posts' );
				},
				'sanitize_callback' => 'aiad_sanitize_focal_point_meta',
			)
		);
	}
}
add_action( 'init', 'aiad_register_thumbnail_focal_point_meta' );

/**
 * Focal point for a post's featured image (post meta, then attachment meta).
 *
 * @param int $post_id Post ID.
 * @return array{x: float, y: float}|null Null when unset (CSS default applies).
 */
function aiad_get_post_thumbnail_focal_point( int $post_id ): ?array {
	$meta_key = aiad_thumbnail_focal_point_meta_key();

	foreach ( array( $meta_key, 'featured_image_focal_point' ) as $key ) {
		$point = aiad_normalize_focal_point( get_post_meta( $post_id, $key, true ) );
		if ( $point ) {
			return $point;
		}
	}

	$thumb_id = (int) get_post_thumbnail_id( $post_id );
	if ( $thumb_id > 0 ) {
		foreach ( array( $meta_key, 'featured_image_focal_point' ) as $key ) {
			$point = aiad_normalize_focal_point( get_post_meta( $thumb_id, $key, true ) );
			if ( $point ) {
				return $point;
			}
		}
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
 * @return string HTML attribute fragment (leading space + style=) or empty.
 */
function aiad_entry_figure_img_style_attr( int $post_id, string $fit = 'cover' ): string {
	if ( 'contain' === $fit ) {
		return '';
	}

	$style = aiad_focal_point_inline_style( aiad_get_post_thumbnail_focal_point( $post_id ) );
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
	unset( $attrs['fit'] );

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
		'contain' === $fit ? null : aiad_get_post_thumbnail_focal_point( $post_id )
	);
	if ( $focal_style !== '' ) {
		$existing = isset( $img_attrs['style'] ) ? (string) $img_attrs['style'] : '';
		$img_attrs['style'] = trim( $existing . ' ' . $focal_style );
	}

	return wp_get_attachment_image( $thumb_id, $size, false, $img_attrs );
}

/**
 * Admin fields: horizontal / vertical focal point (%).
 *
 * @param WP_Post $post Post being edited.
 */
function aiad_render_thumbnail_focal_point_fields( WP_Post $post ): void {
	$point   = aiad_get_post_thumbnail_focal_point( $post->ID ) ?? aiad_default_thumbnail_focal_point();
	$x_pct   = round( (float) $point['x'] * 100 );
	$y_pct   = round( (float) $point['y'] * 100 );
	?>
	<div class="aiad-rd-section aiad-focal-point-fields">
		<strong class="aiad-rd-label"><?php esc_html_e( 'Image focal point', 'ai-awareness-day' ); ?></strong>
		<p class="description" style="margin-top:0;">
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: link to Focal Point Picker docs */
					__( 'Sets which part of the Featured Image stays visible when cropped (object-position). Same idea as the block editor %s.', 'ai-awareness-day' ),
					'<a href="https://developer.wordpress.org/block-editor/reference-guides/components/focal-point-picker/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Focal Point Picker', 'ai-awareness-day' ) . '</a>'
				)
			);
			?>
		</p>
		<p style="display:flex;gap:0.75rem;flex-wrap:wrap;margin:0.5rem 0 0;">
			<label style="flex:1;min-width:6rem;">
				<?php esc_html_e( 'Horizontal %', 'ai-awareness-day' ); ?><br>
				<input type="number" name="aiad_thumbnail_focal_x" class="small-text" min="0" max="100" step="1" value="<?php echo esc_attr( (string) $x_pct ); ?>" />
			</label>
			<label style="flex:1;min-width:6rem;">
				<?php esc_html_e( 'Vertical %', 'ai-awareness-day' ); ?><br>
				<input type="number" name="aiad_thumbnail_focal_y" class="small-text" min="0" max="100" step="1" value="<?php echo esc_attr( (string) $y_pct ); ?>" />
			</label>
		</p>
		<p class="description"><?php esc_html_e( 'Default is 50% / 30% (center, slightly above middle). Only applies when the image uses cover cropping.', 'ai-awareness-day' ); ?></p>
	</div>
	<?php
}

/**
 * Save focal point from POST (timeline + resource meta boxes).
 *
 * @param int $post_id Post ID.
 */
function aiad_save_thumbnail_focal_point_from_post( int $post_id ): void {
	if ( ! isset( $_POST['aiad_thumbnail_focal_x'], $_POST['aiad_thumbnail_focal_y'] ) ) {
		return;
	}

	$x = (int) $_POST['aiad_thumbnail_focal_x'];
	$y = (int) $_POST['aiad_thumbnail_focal_y'];

	$point = aiad_normalize_focal_point(
		array(
			'x' => $x,
			'y' => $y,
		)
	);

	if ( ! $point ) {
		delete_post_meta( $post_id, aiad_thumbnail_focal_point_meta_key() );
		return;
	}

	update_post_meta( $post_id, aiad_thumbnail_focal_point_meta_key(), $point );
}
