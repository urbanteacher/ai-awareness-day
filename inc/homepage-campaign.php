<?php
/**
 * The homepage's campaign section in blocks: shared helpers for the section template
 * (template-parts/front-page/section-campaign.php), its pattern (patterns/homepage-campaign.php) and the blocks
 * the pattern uses (blocks/partner-marquee, blocks/campaign-embed, blocks/partners).
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The partners for the campaign section's reach grid, in the order the grid shows them (partners with AI resources
 * first, then menu order, then name), and how many the grid shows before "Show More Partners".
 *
 * @return array{partners: array<int, array<string, mixed>>, count: int, initial_show_mobile: int, initial_show_desktop: int}
 */
function aiad_campaign_partners(): array {
	static $cache = null; // The section and its blocks ask more than once per page.
	if ( null !== $cache ) {
		return $cache;
	}
	$partner_posts = new WP_Query(array(
		'post_type'      => 'partner',
		'posts_per_page' => 50,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
		'post_status'    => 'publish',
	));
	$partners = array();
	if ($partner_posts->have_posts()) {
		while ($partner_posts->have_posts()) {
			$partner_posts->the_post();
			$pid         = get_the_ID();
			$partner_url = (string) get_post_meta($pid, '_partner_url', true);
			$ai_url      = (string) get_post_meta($pid, '_partner_ai_resources_url', true);
			$provides_ai = (string) get_post_meta($pid, '_partner_provides_ai_resources', true) === '1';
			$card_href   = '';
			if ($provides_ai) {
				$card_href = $ai_url !== '' ? $ai_url : $partner_url;
			}
			$partner_stats = get_post_meta($pid, '_partner_stats', true);
			$partners[]    = array(
				'id'          => $pid,
				'name'        => get_the_title(),
				'stats'       => $partner_stats ? $partner_stats : '',
				'logo'        => get_the_post_thumbnail_url($pid, 'medium'),
				'menu_order'  => (int) get_post_field('menu_order', $pid),
				'provides_ai' => $provides_ai,
				'card_href'   => $card_href !== '' ? $card_href : '',
			);
		}
		wp_reset_postdata();
	}
	usort(
		$partners,
		static function (array $a, array $b): int {
			$pa = ! empty($a['provides_ai']);
			$pb = ! empty($b['provides_ai']);
			if ($pa !== $pb) {
				return $pb <=> $pa;
			}
			$mo = (int) $a['menu_order'] <=> (int) $b['menu_order'];
			if ($mo !== 0) {
				return $mo;
			}
			return strcasecmp((string) $a['name'], (string) $b['name']);
		}
	);
	$partners_count  = count($partners);
	$ai_partner_count = 0;
	foreach ($partners as $p) {
		if (empty($p['name'])) {
			continue;
		}
		if (!empty($p['provides_ai'])) {
			$ai_partner_count++;
		}
	}
	$initial_show_mobile  = max(8, $ai_partner_count);
	$initial_show_desktop = max(10, $ai_partner_count);

	$cache = array(
		'partners'             => $partners,
		'count'                => $partners_count,
		'initial_show_mobile'  => $initial_show_mobile,
		'initial_show_desktop' => $initial_show_desktop,
	);
	return $cache;
}

/*
 * The campaign pattern's blocks (partner-marquee, campaign-embed, partners-grid) are built blocks in aiad-core (src/blocks).
 */

/**
 * The campaign section in blocks (patterns/homepage-campaign.php): add what the section template works out when it
 * renders, which a core group cannot hold. The section lands on its text rather than the logo strip
 * (data-anchor-target, main.js); with a video it splits text and video (campaign--split), without one the text
 * runs full width (campaign-split--single); and the reach group carries how many partner cards show at first.
 *
 * @param string $block_content The section's HTML.
 * @param array  $block         The parsed block.
 */
function aiad_campaign_section_attributes( string $block_content, array $block ): string {
	if ( 'aiad/homepage-campaign' !== ( $block['attrs']['metadata']['patternName'] ?? '' ) ) {
		return $block_content;
	}
	$has_embed = str_contains( $block_content, 'class="campaign-embed' );
	$html      = new WP_HTML_Tag_Processor( $block_content );
	if ( $html->next_tag( 'section' ) ) {
		if ( $has_embed ) {
			$html->add_class( 'campaign--split' );
		}
		$html->set_attribute( 'data-anchor-target', '.campaign-split' );
	}
	while ( $html->next_tag() ) {
		if ( $html->has_class( 'campaign-split' ) && ! $has_embed ) {
			$html->add_class( 'campaign-split--single' );
		}
		if ( 'reach' === $html->get_attribute( 'id' ) ) {
			$partners = aiad_campaign_partners();
			$html->set_attribute( 'data-initial-show-mobile', (string) $partners['initial_show_mobile'] );
			$html->set_attribute( 'data-initial-show-desktop', (string) $partners['initial_show_desktop'] );
		}
	}
	return $html->get_updated_html();
}
add_filter( 'render_block_core/group', 'aiad_campaign_section_attributes', 10, 2 );
