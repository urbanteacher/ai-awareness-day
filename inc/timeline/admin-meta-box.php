<?php
/**
 * Live Timeline — Admin meta box for manual entries.
 *
 * Loaded by inc/timeline.php.
 *
 * @package AI_Awareness_Day
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ──────────────────────────────────────────────
   2. Admin Meta Box (manual entries)
   ────────────────────────────────────────────── */

function aiad_timeline_meta_box(): void
{
    add_meta_box(
        'aiad_timeline_details',
        __('Entry Details', 'ai-awareness-day'),
        'aiad_timeline_meta_box_callback',
        'timeline',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'aiad_timeline_meta_box');

function aiad_timeline_meta_box_callback(WP_Post $post): void
{
    wp_nonce_field('aiad_timeline_meta_save', 'aiad_timeline_meta_nonce');

    $source = get_post_meta($post->ID, '_aiad_timeline_source', true) ?: 'manual';
    $pinned = (bool) get_post_meta($post->ID, '_aiad_timeline_pinned', true);
    $icon = get_post_meta($post->ID, '_aiad_timeline_icon', true) ?: 'announcement';
    $card_type = get_post_meta($post->ID, '_aiad_timeline_card_type', true) ?: 'default';
    $icon_options = aiad_timeline_icon_options();
    $editable = aiad_timeline_editable_meta_config();
    ?>
    <div class="aiad-resource-details">
        <p class="description" style="margin-top:0;">
            <strong><?php esc_html_e('Visual (recommended):', 'ai-awareness-day'); ?></strong><br>
            <?php esc_html_e('Set a Featured Image (school logo, display board, faces, etc.) and/or choose a card type below. The timeline card will show the content prominently.', 'ai-awareness-day'); ?>
        </p>
        <?php
        foreach ($editable as $meta_key => $field) {
            $input_name = str_replace('_aiad_timeline_', 'aiad_timeline_', $meta_key);
            $value = get_post_meta($post->ID, $meta_key, true);
            $input_type = $field['type'];

            // Determine which card types this field should show for
            $show_for = array();
            if ('_aiad_timeline_card_type' === $meta_key) {
                $show_for = array('all'); // Always show
            } elseif ('_aiad_timeline_video_url' === $meta_key) {
                $show_for = array('video', 'default');
            } elseif ('_aiad_timeline_linkedin_url' === $meta_key) {
                $show_for = array('linkedin');
            } elseif ('_aiad_timeline_link_url' === $meta_key || '_aiad_timeline_link_label' === $meta_key) {
                $show_for = array('link', 'default', 'video', 'linkedin');
            } else {
                $show_for = array('all');
            }

            $data_condition = '';
            if (!in_array('all', $show_for, true)) {
                $data_condition = implode(',', $show_for);
            }

            $wrapper_class = 'aiad-timeline-field-wrapper aiad-rd-section';
            if ('_aiad_timeline_card_type' !== $meta_key && $data_condition) {
                $wrapper_class .= ' aiad-timeline-field-conditional';
            }
            ?>
            <div class="<?php echo esc_attr($wrapper_class); ?>" data-show-for="<?php echo esc_attr($data_condition); ?>">
                <label for="<?php echo esc_attr($input_name); ?>"
                    class="aiad-rd-label"><?php echo esc_html($field['label']); ?></label>
                <?php if ('select' === $input_type): ?>
                    <select id="<?php echo esc_attr($input_name); ?>" name="<?php echo esc_attr($input_name); ?>"
                        class="widefat aiad-timeline-card-type-select">
                        <?php foreach ($field['options'] as $opt_value => $opt_label): ?>
                            <option value="<?php echo esc_attr($opt_value); ?>" <?php selected($value, $opt_value); ?>>
                                <?php echo esc_html($opt_label); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="<?php echo esc_attr(('url' === $input_type) ? 'url' : 'text'); ?>"
                        id="<?php echo esc_attr($input_name); ?>" name="<?php echo esc_attr($input_name); ?>"
                        value="<?php echo esc_attr($value); ?>" class="widefat"
                        placeholder="<?php echo esc_attr($field['placeholder'] ?? ''); ?>" />
                <?php endif; ?>
                <?php if (!empty($field['description'])): ?>
                    <p class="description"><?php echo esc_html($field['description']); ?></p>
                <?php endif; ?>
            </div>
            <?php
        }
        ?>
        <div class="aiad-rd-section">
            <strong class="aiad-rd-label"><?php esc_html_e('Source', 'ai-awareness-day'); ?></strong>
            <p class="description" style="margin-top:0;">
                <?php echo esc_html('auto' === $source ? __('Auto-generated', 'ai-awareness-day') : __('Manual', 'ai-awareness-day')); ?>
            </p>
        </div>
        <div class="aiad-rd-section">
            <label for="aiad_timeline_pinned">
                <input type="checkbox" id="aiad_timeline_pinned" name="aiad_timeline_pinned" value="1" <?php checked($pinned); ?> />
                <?php esc_html_e('Pin to top of timeline', 'ai-awareness-day'); ?>
            </label>
        </div>
        <div class="aiad-rd-section">
            <label for="aiad_timeline_icon" class="aiad-rd-label"><?php esc_html_e('Icon', 'ai-awareness-day'); ?></label>
            <select id="aiad_timeline_icon" name="aiad_timeline_icon" class="widefat">
                <?php foreach ($icon_options as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($icon, $value); ?>>
                        <?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php
        $cover_fallback = get_post_meta($post->ID, '_aiad_timeline_cover_fallback', true);
        $cover_options = aiad_timeline_cover_fallback_options();
        ?>
        <div class="aiad-rd-section">
            <label for="aiad_timeline_cover_fallback"
                class="aiad-rd-label"><?php esc_html_e('Cover when no featured image', 'ai-awareness-day'); ?></label>
            <select id="aiad_timeline_cover_fallback" name="aiad_timeline_cover_fallback" class="widefat">
                <?php foreach ($cover_options as $value => $label): ?>
                    <option value="<?php echo esc_attr($value); ?>" <?php selected($cover_fallback, $value); ?>>
                        <?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description">
                <?php esc_html_e('Used if no Featured Image is set. Upload a Featured Image to use your own photo instead.', 'ai-awareness-day'); ?>
            </p>
        </div>
        <?php
        if (function_exists('aiad_render_thumbnail_focal_point_fields')) {
            aiad_render_thumbnail_focal_point_fields($post);
        }
        ?>
    </div>
    <script>
        jQuery(function ($) {
            function toggleTimelineFields() {
                var cardType = $('.aiad-timeline-card-type-select').val();
                $('.aiad-timeline-field-conditional').each(function () {
                    var showFor = $(this).data('show-for');
                    if (showFor) {
                        var types = showFor.toString().split(',');
                        if (types.indexOf(cardType) === -1) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    }
                });
            }
            toggleTimelineFields();
            $('.aiad-timeline-card-type-select').on('change', toggleTimelineFields);
        });
    </script>
    <?php
}

function aiad_save_timeline_meta(int $post_id): void
{
    if (
        !isset($_POST['aiad_timeline_meta_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aiad_timeline_meta_nonce'])), 'aiad_timeline_meta_save')
    ) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_aiad_timeline_pinned', !empty($_POST['aiad_timeline_pinned']));

    if (isset($_POST['aiad_timeline_cover_fallback'])) {
        $cover = sanitize_text_field(wp_unslash($_POST['aiad_timeline_cover_fallback']));
        $valid = array_keys(aiad_timeline_cover_fallback_options());
        if (in_array($cover, $valid, true)) {
            update_post_meta($post_id, '_aiad_timeline_cover_fallback', $cover);
        }
    }

    if (isset($_POST['aiad_timeline_icon'])) {
        $icon = sanitize_text_field(wp_unslash($_POST['aiad_timeline_icon']));
        $valid_icons = array_keys(aiad_timeline_icon_options());
        if (in_array($icon, $valid_icons, true)) {
            update_post_meta($post_id, '_aiad_timeline_icon', $icon);
        }
    }

    foreach (aiad_timeline_editable_meta_config() as $meta_key => $field) {
        $post_key = str_replace('_aiad_timeline_', 'aiad_timeline_', $meta_key);
        if (!isset($_POST[$post_key])) {
            continue;
        }
        $raw = wp_unslash($_POST[$post_key]);
        if ('url' === $field['type']) {
            update_post_meta($post_id, $meta_key, esc_url_raw($raw));
        } elseif ('select' === $field['type'] && isset($field['options'])) {
            // Validate select value against allowed options
            $valid_options = array_keys($field['options']);
            if (in_array($raw, $valid_options, true)) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($raw));
            }
        } else {
            update_post_meta($post_id, $meta_key, sanitize_text_field($raw));
        }
    }

    // Manual entries always have source = 'manual'
    $current_source = get_post_meta($post_id, '_aiad_timeline_source', true);
    if (empty($current_source)) {
        update_post_meta($post_id, '_aiad_timeline_source', 'manual');
    }

    if (function_exists('aiad_save_thumbnail_focal_point_from_post')) {
        aiad_save_thumbnail_focal_point_from_post($post_id);
    }
}
add_action('save_post_timeline', 'aiad_save_timeline_meta');

