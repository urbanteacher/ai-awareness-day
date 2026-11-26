<?php
/**
 * One-time: publish "From primary to university, England’s AI rules depend on where you study", Mark
 * Martin's close reading of ten institutions' published AI policies (24 November 2026), as a campaign update (the timeline post
 * type, shown at /timeline/). The text is inc/articles/england-ai-policy-compared.html, block markup made from the Markdown draft.
 * Revised 25 November 2026 after an editorial review: sections reordered, facts checked against the policies, and a closing
 * "six questions" checklist added, so it no longer follows the Markdown draft line for line.
 * Its two figures (assets/images/articles/ai-policy/: policy-grid, which replaced the draft's comparison table, and learner-journey)
 * are imported into the media library in place of their IMG:{name} placeholders. The text-free cover
 * (assets/images/articles/ai-policy-card.jpg, one illustrated AI policy document with colour-coded clauses, ticks
 * and an approval seal; the earlier five-sheet cover is kept in archive/design-kit/ai-policy-dropped/) is made by archive/design-kit/ai-policy.html with render-ai-policy.mjs. Runs once on the first request
 * after a deploy; the post can then be edited, retitled or trashed in the editor without this putting it back.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The post's title and excerpt (its standfirst).
 *
 * @return array{title: string, excerpt: string}
 */
function aiad_article_ai_policy_fields(): array {
	return array(
		'title'   => 'From primary to university, England’s AI rules depend on where you study',
		'excerpt' => 'From plagiarism and deepfakes to cyber attacks on school systems, the risks of AI are more real than ever. Yet no Act of Parliament requires an AI policy. Working from DfE guidance, schools, trusts, colleges and universities have taken on the task themselves, so every AI policy is different. We read ten, from a Sheffield primary to three universities, to see how far apart they are.',
	);
}

/**
 * Publish the post once, with its excerpt, topics (Policy & regulation, Research & insight) and cover.
 */
function aiad_migrate_article_ai_policy(): void {
	if ( get_option( 'aiad_article_ai_policy_added' ) === '1' || ! post_type_exists( 'timeline' ) || ! taxonomy_exists( 'timeline_category' ) ) {
		return;
	}
	$slug = 'england-ai-policy-schools-colleges-universities';
	if ( get_page_by_path( $slug, OBJECT, 'timeline' ) ) {
		update_option( 'aiad_article_ai_policy_added', '1' );
		return;
	}
	$file = AIAD_DIR . '/inc/articles/england-ai-policy-compared.html';
	if ( ! is_readable( $file ) ) {
		return;
	}
	$content = str_replace( array( 'THEME_URI', 'SITE_URI' ), array( untrailingslashit( AIAD_URI ), untrailingslashit( home_url() ) ), (string) file_get_contents( $file ) );
	$fields  = aiad_article_ai_policy_fields();

	kses_remove_filters();
	$id = wp_insert_post(
		array(
			'post_type'    => 'timeline',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $fields['title'],
			'post_excerpt' => $fields['excerpt'],
			'post_content' => '',
		)
	);
	kses_init_filters();
	if ( ! $id || is_wp_error( $id ) ) {
		return;
	}

	// The two figures: imported into the media library, their IMG:{name} placeholders replaced with the addresses.
	$dir = AIAD_DIR . '/assets/images/articles/ai-policy';
	preg_match_all( '/IMG:([a-z0-9-]+)/', $content, $found );
	foreach ( array_unique( $found[1] ) as $name ) {
		$attachment = aiad_article_import_image( (int) $id, $dir . '/' . $name . '.png', 'ai-policy-' . $name . '.png', '' );
		$url        = $attachment ? (string) wp_get_attachment_url( $attachment ) : untrailingslashit( AIAD_URI ) . '/assets/images/articles/ai-policy/' . $name . '.png';
		$content    = str_replace( 'IMG:' . $name . '"', $url . '"', $content );
	}
	global $wpdb;
	$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	clean_post_cache( $id );

	update_post_meta( $id, '_aiad_timeline_source', 'manual' );
	update_post_meta( $id, '_aiad_timeline_icon', 'announcement' );

	$cover = aiad_article_import_image( (int) $id, AIAD_DIR . '/assets/images/articles/ai-policy-card.jpg', 'england-ai-policy-schools-colleges-universities.jpg', 'An illustrated AI policy document on a dark background: a cream sheet headed AI Policy with a sparkle badge, five colour-coded clauses each marked with a tick, half-circle or cross, a signature and a pink approval seal' );
	if ( $cover ) {
		set_post_thumbnail( $id, $cover );
	}

	$terms = array();
	foreach ( array( 'policy-regulation', 'research-insight' ) as $topic ) {
		$term = get_term_by( 'slug', $topic, 'timeline_category' );
		if ( $term ) {
			$terms[] = (int) $term->term_id;
		}
	}
	if ( $terms ) {
		wp_set_object_terms( $id, $terms, 'timeline_category', false );
	}

	update_option( 'aiad_article_ai_policy_added', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_ai_policy_added', 'aiad_migrate_article_ai_policy' ); }, 60 );

/**
 * One-time (26 Nov 2026): bring a post published before the Glenthorne reading-list figure was dropped into line with the
 * article file. Removes that figure and its media item, and swaps in the reworded paragraph and borrowed-text line. Each
 * swap only applies if the old text is still there, so later edits in the editor are left alone.
 */
function aiad_fix_article_ai_policy_residue(): void {
	$post = get_page_by_path( 'england-ai-policy-schools-colleges-universities', OBJECT, 'timeline' );
	if ( ! $post ) {
		update_option( 'aiad_article_ai_policy_residue_fixed', '1' );
		return;
	}
	$content = (string) $post->post_content;
	$before  = $content;
	$content = (string) preg_replace( '/\n?<!-- wp:image [^\n]*-->\n<figure[^\n]*reading-list-residue[^\n]*\n<!-- \/wp:image -->\n?/', "\n", $content );
	$swaps   = array(
		'<p>One detail stands out. The policy’s reading list contains links tagged “utm_source=chatgpt.com” and citation fragments such as “gov.uk+15gov.uk+15”, the marks left when references are copied from a ChatGPT answer. A policy that rules ChatGPT out for staff appears to have gathered some of its own references with it.</p>' =>
			'<p>The policy also shows how quietly AI has entered routine work. Its reading list carries links tagged “utm_source=chatgpt.com” and citation fragments such as “gov.uk+15gov.uk+15”, the marks left when references are gathered from a ChatGPT answer. That is no failing of the author, and many staff now research this way. It is a reason for policies to say how staff may use AI for research and drafting, not only which tools are approved.</p>',
		'as Ecclesall’s GCSE exam rules, Leicester’s outdated Bard and Glenthorne’s ChatGPT-tagged reading list show.' =>
			'as Ecclesall’s GCSE exam rules and Leicester’s outdated Bard show.',
	);
	$content = str_replace( array_keys( $swaps ), array_values( $swaps ), $content );
	if ( $content !== $before ) {
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_content' => $content ), array( 'ID' => $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		clean_post_cache( $post->ID );
	}
	foreach ( get_attached_media( 'image', $post->ID ) as $media ) {
		if ( str_contains( (string) get_attached_file( $media->ID ), 'reading-list-residue' ) ) {
			wp_delete_attachment( $media->ID, true );
		}
	}
	update_option( 'aiad_article_ai_policy_residue_fixed', '1' );
}
add_action( 'init', static function (): void { aiad_article_run_once( 'aiad_article_ai_policy_residue_fixed', 'aiad_fix_article_ai_policy_residue' ); }, 61 );
