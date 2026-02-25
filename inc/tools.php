<?php
/**
 * Free AI Tools: CPT, taxonomy, meta, admin UI, and rendering helpers.
 *
 * CPT slug:      ai_tool
 * Archive slug:  free-tools
 * Taxonomy:      tool_category
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ──────────────────────────────────────────────
   1. CPT Registration
   ────────────────────────────────────────────── */

function aiad_register_ai_tool_post_type(): void {
	register_post_type( 'ai_tool', array(
		'labels' => array(
			'name'               => __( 'AI Tools', 'ai-awareness-day' ),
			'singular_name'      => __( 'AI Tool', 'ai-awareness-day' ),
			'add_new'            => __( 'Add Tool', 'ai-awareness-day' ),
			'add_new_item'       => __( 'Add New AI Tool', 'ai-awareness-day' ),
			'edit_item'          => __( 'Edit AI Tool', 'ai-awareness-day' ),
			'view_item'          => __( 'View AI Tool', 'ai-awareness-day' ),
			'all_items'          => __( 'All AI Tools', 'ai-awareness-day' ),
			'search_items'       => __( 'Search AI Tools', 'ai-awareness-day' ),
		),
		'public'             => true,
		'publicly_queryable' => true,
		'has_archive'        => 'free-tools',
		'rewrite'            => array( 'slug' => 'free-tools' ),
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_icon'          => 'dashicons-laptop',
		'show_in_rest'       => true,
		'supports'           => array( 'title', 'excerpt', 'thumbnail' ),
	) );
}
add_action( 'init', 'aiad_register_ai_tool_post_type', 10 );

/* ──────────────────────────────────────────────
   2. Taxonomy: tool_category
   ────────────────────────────────────────────── */

function aiad_register_tool_category_taxonomy(): void {
	register_taxonomy( 'tool_category', 'ai_tool', array(
		'labels' => array(
			'name'          => __( 'Tool Categories', 'ai-awareness-day' ),
			'singular_name' => __( 'Tool Category', 'ai-awareness-day' ),
			'add_new_item'  => __( 'Add New Category', 'ai-awareness-day' ),
			'edit_item'     => __( 'Edit Category', 'ai-awareness-day' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'tool-category' ),
	) );
}
add_action( 'init', 'aiad_register_tool_category_taxonomy', 10 );

/**
 * Pre-seed default tool categories on first activation.
 */
function aiad_seed_tool_categories(): void {
	$default_categories = array(
		'content-creation'       => __( 'Content Creation', 'ai-awareness-day' ),
		'visual-creative'        => __( 'Visual & Creative', 'ai-awareness-day' ),
		'learning-platforms'     => __( 'Learning Platforms', 'ai-awareness-day' ),
		'subject-specific'       => __( 'Subject-Specific', 'ai-awareness-day' ),
		'interactive-demos'      => __( 'Interactive Demos', 'ai-awareness-day' ),
		'professional-development' => __( 'Professional Development', 'ai-awareness-day' ),
	);

	foreach ( $default_categories as $slug => $name ) {
		if ( ! term_exists( $slug, 'tool_category' ) ) {
			wp_insert_term( $name, 'tool_category', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'aiad_seed_tool_categories', 20 );

/* ──────────────────────────────────────────────
   3. Meta Fields
   ────────────────────────────────────────────── */

function aiad_register_tool_meta(): void {
	$fields = array(
		'_aiad_tool_url'      => 'string',
		'_aiad_tool_status'   => 'string',
		'_aiad_tool_use_case' => 'string',
		'_aiad_tool_features' => 'string',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta( 'ai_tool', $key, array(
			'type'          => $type,
			'single'        => true,
			'default'       => '',
			'show_in_rest'  => true,
			'auth_callback' => function () {
				return current_user_can( 'edit_posts' );
			},
		) );
	}
}
add_action( 'init', 'aiad_register_tool_meta', 15 );

/* ──────────────────────────────────────────────
   4. Admin Meta Box
   ────────────────────────────────────────────── */

function aiad_add_tool_meta_box(): void {
	add_meta_box(
		'aiad_tool_details',
		__( 'Tool Details', 'ai-awareness-day' ),
		'aiad_render_tool_meta_box',
		'ai_tool',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'aiad_add_tool_meta_box' );

function aiad_render_tool_meta_box( WP_Post $post ): void {
	wp_nonce_field( 'aiad_tool_meta_save', 'aiad_tool_meta_nonce' );

	$url      = get_post_meta( $post->ID, '_aiad_tool_url', true );
	$status   = get_post_meta( $post->ID, '_aiad_tool_status', true ) ?: 'available';
	$use_case = get_post_meta( $post->ID, '_aiad_tool_use_case', true );
	$features = get_post_meta( $post->ID, '_aiad_tool_features', true );
	?>
	<table class="form-table" style="margin-top:0">
		<tr>
			<th><label for="aiad_tool_url"><?php esc_html_e( 'Website URL', 'ai-awareness-day' ); ?></label></th>
			<td><input type="url" id="aiad_tool_url" name="aiad_tool_url" value="<?php echo esc_attr( $url ); ?>" class="large-text" placeholder="https://example.com" /></td>
		</tr>
		<tr>
			<th><label for="aiad_tool_status"><?php esc_html_e( 'Status', 'ai-awareness-day' ); ?></label></th>
			<td>
				<select id="aiad_tool_status" name="aiad_tool_status">
					<option value="available" <?php selected( $status, 'available' ); ?>><?php esc_html_e( 'Available', 'ai-awareness-day' ); ?></option>
					<option value="coming_soon" <?php selected( $status, 'coming_soon' ); ?>><?php esc_html_e( 'Coming Soon', 'ai-awareness-day' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="aiad_tool_use_case"><?php esc_html_e( 'Use Case', 'ai-awareness-day' ); ?></label></th>
			<td>
				<input type="text" id="aiad_tool_use_case" name="aiad_tool_use_case" value="<?php echo esc_attr( $use_case ); ?>" class="large-text" placeholder="<?php esc_attr_e( 'e.g. Lesson planning, differentiation, feedback', 'ai-awareness-day' ); ?>" />
				<p class="description"><?php esc_html_e( 'Short description shown on the card.', 'ai-awareness-day' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="aiad_tool_features"><?php esc_html_e( 'Key Features', 'ai-awareness-day' ); ?></label></th>
			<td>
				<textarea id="aiad_tool_features" name="aiad_tool_features" class="large-text" rows="5" placeholder="<?php esc_attr_e( 'One feature per line', 'ai-awareness-day' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One feature per line. First 3 are shown on cards; the rest appear as "+N more".', 'ai-awareness-day' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function aiad_save_tool_meta( int $post_id ): void {
	if (
		! isset( $_POST['aiad_tool_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_tool_meta_nonce'] ) ), 'aiad_tool_meta_save' ) ||
		( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ||
		! current_user_can( 'edit_post', $post_id )
	) {
		return;
	}

	if ( isset( $_POST['aiad_tool_url'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_url', esc_url_raw( wp_unslash( $_POST['aiad_tool_url'] ) ) );
	}
	if ( isset( $_POST['aiad_tool_status'] ) ) {
		$status = sanitize_text_field( wp_unslash( $_POST['aiad_tool_status'] ) );
		update_post_meta( $post_id, '_aiad_tool_status', in_array( $status, array( 'available', 'coming_soon' ), true ) ? $status : 'available' );
	}
	if ( isset( $_POST['aiad_tool_use_case'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_use_case', sanitize_text_field( wp_unslash( $_POST['aiad_tool_use_case'] ) ) );
	}
	if ( isset( $_POST['aiad_tool_features'] ) ) {
		update_post_meta( $post_id, '_aiad_tool_features', sanitize_textarea_field( wp_unslash( $_POST['aiad_tool_features'] ) ) );
	}
}
add_action( 'save_post_ai_tool', 'aiad_save_tool_meta' );

/* ──────────────────────────────────────────────
   5. Rendering Helper
   ────────────────────────────────────────────── */

/**
 * Render a single tool card.
 *
 * @param WP_Post $tool The ai_tool post object.
 * @return string HTML markup.
 */
function aiad_render_tool_card( WP_Post $tool ): string {
	$url      = get_post_meta( $tool->ID, '_aiad_tool_url', true );
	$status   = get_post_meta( $tool->ID, '_aiad_tool_status', true ) ?: 'available';
	$use_case = get_post_meta( $tool->ID, '_aiad_tool_use_case', true );
	$features_raw = get_post_meta( $tool->ID, '_aiad_tool_features', true );

	$features     = $features_raw ? array_filter( array_map( 'trim', explode( "\n", $features_raw ) ) ) : array();
	$features     = array_values( $features );
	$shown        = array_slice( $features, 0, 3 );
	$extra        = count( $features ) - count( $shown );

	$terms        = get_the_terms( $tool->ID, 'tool_category' );
	$category     = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';

	$is_available = 'available' === $status;
	$status_label = $is_available ? __( 'Available', 'ai-awareness-day' ) : __( 'Coming Soon', 'ai-awareness-day' );

	ob_start();
	?>
	<div class="tool-card">
		<div class="tool-card__top">
			<?php if ( $category ) : ?>
				<span class="tool-card__category"><?php echo esc_html( $category ); ?></span>
			<?php endif; ?>
			<span class="tool-card__status tool-card__status--<?php echo esc_attr( $status ); ?>">
				<span class="tool-card__status-dot" aria-hidden="true"></span>
				<?php echo esc_html( $status_label ); ?>
			</span>
		</div>

		<h3 class="tool-card__title"><?php echo esc_html( get_the_title( $tool ) ); ?></h3>

		<?php if ( $use_case ) : ?>
			<p class="tool-card__use-case"><?php echo esc_html( $use_case ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $shown ) ) : ?>
			<ul class="tool-card__features">
				<?php foreach ( $shown as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
				<?php if ( $extra > 0 ) : ?>
					<li class="tool-card__features-more">
						<?php
						/* translators: %d: number of extra features */
						printf( esc_html( _n( '+%d more feature', '+%d more features', $extra, 'ai-awareness-day' ) ), (int) $extra );
						?>
					</li>
				<?php endif; ?>
			</ul>
		<?php endif; ?>

		<?php if ( $url ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" class="tool-card__link" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Visit Website', 'ai-awareness-day' ); ?>
				<span aria-hidden="true">→</span>
			</a>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}
