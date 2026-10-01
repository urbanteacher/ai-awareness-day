<?php
/**
 * Partner profile: what the single partner template's blocks read.
 *
 * templates/single-partner.html lays the page out in core blocks and four small blocks of ours (aiad/partner-logo,
 * -intro, -links and -actions). Each one prints a part of the profile only when the partner has it, which is why they
 * are blocks and not block bindings: a binding fills an attribute and cannot leave the block out. Core's post meta
 * binding could not read these fields in any case, as it refuses every key that starts with an underscore.
 *
 * @package AIAD_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The partner a profile block is showing.
 *
 * On the site it is the page's own post (the block's postId context). In the editor's preview of a block, which has no
 * post, it is the newest published partner, so the block shows something.
 *
 * @param WP_Block|null $block The block being rendered.
 */
function aiad_partner_profile_post_id( $block = null ): int {
	$post_id = $block instanceof WP_Block ? (int) ( $block->context['postId'] ?? 0 ) : 0;
	if ( ! $post_id && is_singular( 'partner' ) ) {
		$post_id = (int) get_queried_object_id();
	}
	if ( ! $post_id && defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$ids     = get_posts(
			array(
				'post_type'      => 'partner',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		$post_id = (int) ( $ids[0] ?? 0 );
	}
	return 'partner' === get_post_type( $post_id ) ? $post_id : 0;
}

/**
 * The five strands in the order a profile lists them: slug => label.
 *
 * @return array<string, string>
 */
function aiad_partner_profile_theme_labels(): array {
	return array(
		'safe'        => __( 'Safe', 'ai-awareness-day' ),
		'smart'       => __( 'Smart', 'ai-awareness-day' ),
		'creative'    => __( 'Creative', 'ai-awareness-day' ),
		'responsible' => __( 'Responsible', 'ai-awareness-day' ),
		'future'      => __( 'Future', 'ai-awareness-day' ),
	);
}

/**
 * A partner's links grouped by strand, in the strands' order. A row needs an address and a title; a row with an
 * unknown strand goes under Smart.
 *
 * @return array<string, array<int, array{title: string, duration: string, url: string}>> Strand slug => links; strands without links are left out.
 */
function aiad_partner_profile_links( int $post_id ): array {
	$links = get_post_meta( $post_id, '_partner_links', true );
	$links = is_array( $links ) ? $links : array();
	$names = aiad_partner_profile_theme_labels();

	$grouped = array();
	foreach ( $links as $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$theme = isset( $item['theme'] ) ? (string) $item['theme'] : '';
		$url   = isset( $item['url'] ) ? (string) $item['url'] : '';
		$title = isset( $item['title'] ) ? (string) $item['title'] : '';
		if ( '' === $url || '' === $title ) {
			continue;
		}
		if ( ! isset( $names[ $theme ] ) ) {
			$theme = 'smart';
		}
		$grouped[ $theme ][] = array(
			'title'    => $title,
			'duration' => isset( $item['duration'] ) ? (string) $item['duration'] : '',
			'url'      => $url,
		);
	}

	$ordered = array();
	foreach ( array_keys( $names ) as $slug ) {
		if ( ! empty( $grouped[ $slug ] ) ) {
			$ordered[ $slug ] = $grouped[ $slug ];
		}
	}
	return $ordered;
}
