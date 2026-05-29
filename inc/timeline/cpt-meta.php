<?php
/**
 * Live Timeline — CPT, taxonomy & meta registration.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   1. CPT & Meta Registration
   ────────────────────────────────────────────── */

/**
 * Register the timeline CPT.
 * Hooked at priority 10 (runs inside aiad_register_post_types or separately).
 */
function aiad_register_timeline_post_type(): void
{
    register_post_type('timeline', array(
        'labels' => array(
            'name' => __('Timeline', 'ai-awareness-day'),
            'singular_name' => __('Timeline Entry', 'ai-awareness-day'),
            'add_new' => __('Add Update', 'ai-awareness-day'),
            'add_new_item' => __('Add Timeline Entry', 'ai-awareness-day'),
            'edit_item' => __('Edit Timeline Entry', 'ai-awareness-day'),
            'view_item' => __('View Entry', 'ai-awareness-day'),
            'all_items' => __('All Entries', 'ai-awareness-day'),
            'search_items' => __('Search Timeline', 'ai-awareness-day'),
        ),
        'public' => true,
        'publicly_queryable' => true,
        'has_archive' => 'timeline',
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-backup',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'author', 'custom-fields'),
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'timeline', 'with_front' => false),
    ));
}
add_action('init', 'aiad_register_timeline_post_type');

/**
 * Register Category taxonomy for timeline entries (shown with the title on the front).
 */
function aiad_register_timeline_category_taxonomy(): void
{
    register_taxonomy('timeline_category', 'timeline', array(
        'labels' => array(
            'name' => __('Categories', 'ai-awareness-day'),
            'singular_name' => __('Category', 'ai-awareness-day'),
            'add_new_item' => __('Add New Category', 'ai-awareness-day'),
            'edit_item' => __('Edit Category', 'ai-awareness-day'),
        ),
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
    ));
}
add_action('init', 'aiad_register_timeline_category_taxonomy', 11);

/**
 * Register post meta for timeline entries.
 */
function aiad_register_timeline_meta(): void
{
    $meta_fields = array(
        // 'auto' or 'manual'
        '_aiad_timeline_source' => array('type' => 'string', 'default' => 'manual'),
        // Auto-generated type: 'resource', 'partner', 'submission', 'milestone'
        '_aiad_timeline_auto_type' => array('type' => 'string', 'default' => ''),
        // Related post ID (resource, partner, etc.) — 0 if none
        '_aiad_timeline_related_id' => array('type' => 'integer', 'default' => 0),
        // Whether this entry is pinned to the top
        '_aiad_timeline_pinned' => array('type' => 'boolean', 'default' => false),
        // Icon key for rendering (e.g. 'resource', 'partner', 'announcement', 'milestone')
        '_aiad_timeline_icon' => array('type' => 'string', 'default' => 'announcement'),
        // Card type: 'default', 'video', 'link', 'linkedin'
        '_aiad_timeline_card_type' => array('type' => 'string', 'default' => 'default'),
        // Optional CTA link URL
        '_aiad_timeline_link_url' => array('type' => 'string', 'default' => ''),
        // Optional CTA link label
        '_aiad_timeline_link_label' => array('type' => 'string', 'default' => ''),
        // Optional video URL (YouTube, Vimeo, LinkedIn video, etc.) — shown as embed in the timeline card
        '_aiad_timeline_video_url' => array('type' => 'string', 'default' => ''),
        // Optional LinkedIn post URL for embedded posts
        '_aiad_timeline_linkedin_url' => array('type' => 'string', 'default' => ''),
        // Like count (incremented via front-end AJAX)
        '_aiad_timeline_like_count' => array('type' => 'integer', 'default' => 0),
        // Cover when no featured image: '' (auto), 'gradient', 'tech'
        '_aiad_timeline_cover_fallback' => array('type' => 'string', 'default' => ''),
    );

    foreach ($meta_fields as $key => $args) {
        register_post_meta('timeline', $key, array(
            'type' => $args['type'],
            'single' => true,
            'default' => $args['default'],
            'show_in_rest' => true,
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            },
        ));
    }
}
add_action('init', 'aiad_register_timeline_meta', 15);

/**
 * Migrate existing posts from the old CPT slug (aiad_timeline) to the current slug (timeline).
 *
 * The CPT was renamed in v1.3.4. Any posts created before that rename are stored with
 * post_type = 'aiad_timeline' in the database. Since that slug is no longer registered,
 * those entries load without block editor support — no slug panel, no excerpt, no template.
 * This one-time migration updates them so they behave like normal 'timeline' posts.
 *
 * @since 1.3.4
 */
function aiad_migrate_timeline_post_type(): void
{
    if (get_option('aiad_timeline_cpt_migrated')) {
        return;
    }
    global $wpdb;
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s",
        'aiad_timeline'
    ));
    if ($ids) {
        $wpdb->update(
            $wpdb->posts,
            array('post_type' => 'timeline'),
            array('post_type' => 'aiad_timeline'),
            array('%s'),
            array('%s')
        );
        foreach ($ids as $id) {
            clean_post_cache((int) $id);
        }
    }
    update_option('aiad_timeline_cpt_migrated', true);
}
add_action('admin_init', 'aiad_migrate_timeline_post_type');

/**
 * One-time flush of rewrite rules after the aiad_timeline → timeline CPT rename.
 * Runs separately from the post migration so it fires even if migration already completed.
 */
function aiad_flush_timeline_rewrite_rules(): void
{
    if (get_option('aiad_timeline_rewrite_flushed')) {
        return;
    }
    flush_rewrite_rules(false);
    update_option('aiad_timeline_rewrite_flushed', true);
}
add_action('admin_init', 'aiad_flush_timeline_rewrite_rules');

/**
 * One-time: remove Key Stage / curriculum assessment timeline entries (not needed on the feed).
 */
function aiad_trash_keystage_timeline_entries(): void
{
    if (get_option('aiad_keystage_timeline_trashed') === 'yes') {
        return;
    }

    $shortcode_needles = array(
        '[aiad_curriculum_quiz',
        '[aiad_ict_curriculum',
    );

    $slug_needles = array(
        'cross-curricular-ai-curriculum-quiz',
        'ict-curriculum',
        'ks2',
        'ks3',
        'ks4',
        'ks5',
        'key-stage',
    );

    $posts = get_posts(
        array(
            'post_type'      => 'timeline',
            'post_status'    => array('publish', 'draft', 'pending', 'private', 'future'),
            'posts_per_page' => -1,
            'fields'         => 'ids',
        )
    );

    foreach ($posts as $post_id) {
        $post_id = (int) $post_id;
        $post    = get_post($post_id);
        if (!$post instanceof WP_Post) {
            continue;
        }

        $trash = false;

        foreach ($shortcode_needles as $needle) {
            if (false !== strpos($post->post_content, $needle)) {
                $trash = true;
                break;
            }
        }

        if (!$trash) {
            $slug = $post->post_name;
            foreach ($slug_needles as $fragment) {
                if ($slug !== '' && false !== strpos($slug, $fragment)) {
                    $trash = true;
                    break;
                }
            }
        }

        if (!$trash) {
            $title = strtolower($post->post_title);
            if (
                false !== strpos($title, 'key stage')
                || preg_match('/\bks[2-5]\b/', $title)
                || false !== strpos($title, 'curriculum assessment')
                || false !== strpos($title, 'computing assessment across')
            ) {
                $trash = true;
            }
        }

        if ($trash) {
            wp_trash_post($post_id);
        }
    }

    update_option('aiad_keystage_timeline_trashed', 'yes');
}
add_action('init', 'aiad_trash_keystage_timeline_entries', 35);

/**
 * Config for editable timeline meta fields. Add a new entry here to get admin UI and save automatically;
 * then use the meta key in timeline-layout renderers if you want it on the front.
 *
 * To add a new feature: 1) Add the meta key to $meta_fields in aiad_register_timeline_meta() above.
 * 2) Add an entry here (type: 'text' or 'url', label, optional placeholder/description).
 * 3) In inc/timeline-layouts.php renderers, get_post_meta( $entry->ID, $meta_key, true ) and output.
 *
 * @return array<string, array{ type: string, label: string, placeholder?: string, description?: string }>
 */
function aiad_timeline_editable_meta_config(): array
{
    return array(
        '_aiad_timeline_card_type' => array(
            'type' => 'select',
            'label' => __('Card Type', 'ai-awareness-day'),
            'description' => __('Choose what type of content this card displays.', 'ai-awareness-day'),
            'options' => array(
                'default' => __('Standard (text + optional image)', 'ai-awareness-day'),
                'video' => __('YouTube/Vimeo Video', 'ai-awareness-day'),
                'linkedin' => __('LinkedIn Post', 'ai-awareness-day'),
                'link' => __('External Link Card', 'ai-awareness-day'),
            ),
        ),
        '_aiad_timeline_video_url' => array(
            'type' => 'url',
            'label' => __('Video URL', 'ai-awareness-day'),
            'placeholder' => 'https://www.youtube.com/watch?v=...',
            'description' => __('YouTube or Vimeo link — will be embedded in the card.', 'ai-awareness-day'),
        ),
        '_aiad_timeline_linkedin_url' => array(
            'type' => 'url',
            'label' => __('LinkedIn Post URL', 'ai-awareness-day'),
            'placeholder' => 'https://www.linkedin.com/embed/feed/update/urn:li:ugcPost:...',
            'description' => __('Paste the LinkedIn embed URL (from "Embed this post" option on LinkedIn). Format: linkedin.com/embed/feed/update/...', 'ai-awareness-day'),
        ),
        '_aiad_timeline_link_url' => array(
            'type' => 'url',
            'label' => __('Link URL', 'ai-awareness-day'),
            'placeholder' => 'https://...',
            'description' => __('External link for "Learn more" button.', 'ai-awareness-day'),
        ),
        '_aiad_timeline_link_label' => array(
            'type' => 'text',
            'label' => __('Link Label', 'ai-awareness-day'),
            'placeholder' => __('View resource →', 'ai-awareness-day'),
        ),
    );
}

