<?php
/**
 * Admin page: Edit Homepage — simpler than Customizer for non-technical users.
 * Reads and writes the same theme_mod values as the Customizer.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class AIAD_Homepage_Editor
 */
class AIAD_Homepage_Editor {

    const PAGE_SLUG = 'aiad-edit-homepage';
    const NONCE_ACTION = 'aiad_homepage_editor_save';

    /**
     * Hook into admin.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_init', array( $this, 'handle_save' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_bar_menu', array( $this, 'admin_bar_link' ), 99 );
    }

    /**
     * Register admin menu under Appearance.
     */
    public function register_menu(): void {
        add_theme_page(
            __( 'Edit Homepage', 'ai-awareness-day' ),
            __( 'Edit Homepage', 'ai-awareness-day' ),
            'edit_theme_options',
            self::PAGE_SLUG,
            array( $this, 'render_page' )
        );
    }

    /**
     * Enqueue admin CSS and JS (media uploader).
     *
     * @param string $hook_suffix Current admin page hook.
     */
    public function enqueue_assets( string $hook_suffix ): void {
        if ( 'appearance_page_' . self::PAGE_SLUG !== $hook_suffix ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style(
            'aiad-homepage-editor',
            AIAD_URI . '/admin/css/homepage-editor.css',
            array(),
            AIAD_VERSION
        );
        ob_start();
        ?>
        jQuery(function($) {
            $(document).on('click', '.aiad-upload-media', function(e) {
                e.preventDefault();
                var btn = $(this), target = btn.data('target'), store = btn.data('store') || 'image';
                var frame = wp.media({
                    library: { type: 'image' },
                    multiple: false
                });
                frame.on('select', function() {
                    var att = frame.state().get('selection').first().toJSON();
                    var val = store === 'url' ? (att.url || '') : (att.id || '');
                    $('#' + target).val(val);
                    var preview = btn.siblings('.aiad-media-preview');
                    if (att.sizes && att.sizes.medium && att.sizes.medium.url) {
                        preview.html('<img src="' + att.sizes.medium.url + '" alt="" aria-hidden="true" style="max-width:120px;height:auto;vertical-align:middle;" />').show();
                    } else if (att.url) {
                        preview.html('<img src="' + att.url + '" alt="" aria-hidden="true" style="max-width:120px;height:auto;vertical-align:middle;" />').show();
                    }
                });
                frame.open();
            });
        });
        <?php
        wp_add_inline_script( 'jquery', ob_get_clean() );
    }

    /**
     * Add "Edit Homepage" to admin bar when viewing the front page.
     *
     * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
     */
    public function admin_bar_link( WP_Admin_Bar $wp_admin_bar ): void {
        if ( ! is_front_page() || ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }
        $wp_admin_bar->add_node( array(
            'id'    => 'aiad-edit-homepage',
            'title' => __( 'Edit Homepage', 'ai-awareness-day' ),
            'href'  => admin_url( 'themes.php?page=' . self::PAGE_SLUG ),
            'meta'  => array( 'class' => 'aiad-edit-homepage' ),
        ) );
    }

    /**
     * Handle form save (per-tab).
     */
    public function handle_save(): void {
        if ( ! isset( $_GET['page'] ) || self::PAGE_SLUG !== $_GET['page'] ) {
            return;
        }
        if ( ! isset( $_POST['aiad_homepage_editor_nonce'] ) ||
             ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aiad_homepage_editor_nonce'] ) ), self::NONCE_ACTION ) ) {
            return;
        }
        if ( ! current_user_can( 'edit_theme_options' ) ) {
            return;
        }

        $tab = isset( $_POST['aiad_editor_tab'] ) ? sanitize_text_field( wp_unslash( $_POST['aiad_editor_tab'] ) ) : 'hero';

        $updated = 0;
        switch ( $tab ) {
            case 'hero':
                $updated = $this->save_hero();
                break;
            case 'campaign':
                $updated = $this->save_campaign();
                break;
            case 'principles':
                $updated = $this->save_principles();
                break;
            case 'video':
                $updated = $this->save_video();
                break;
            case 'display':
                $updated = $this->save_display_board();
                break;
            case 'contact':
                $updated = $this->save_contact();
                break;
            case 'social':
                $updated = $this->save_social();
                break;
        }

        $redirect = add_query_arg( array(
            'page'    => self::PAGE_SLUG,
            'tab'     => $tab,
            'updated' => $updated ? '1' : '0',
        ), admin_url( 'themes.php' ) );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * Save Hero tab fields.
     *
     * @return int Number of options updated.
     */
    private function save_hero(): int {
        $keys = array(
            'aiad_hero_logo'     => 'absint',
            'aiad_header_logo'   => 'absint',
            'aiad_hero_date'     => 'sanitize_text_field',
            'aiad_hero_title'    => 'sanitize_text_field',
            'aiad_hero_slogan'   => 'sanitize_text_field',
            'aiad_hero_subtitle' => 'sanitize_textarea_field',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save Campaign tab fields.
     *
     * @return int Number of options updated.
     */
    private function save_campaign(): int {
        $keys = array(
            'aiad_campaign_title'  => 'sanitize_text_field',
            'aiad_campaign_text'   => 'wp_kses_post',
            'aiad_campaign_text_2' => 'wp_kses_post',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save Principles & Badges tab (badges are attachment IDs; principle title/desc are text).
     *
     * @return int Number of options updated.
     */
    private function save_principles(): int {
        $n = 0;
        $slugs = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
        foreach ( $slugs as $slug ) {
            $key_badge = 'aiad_badge_' . $slug;
            if ( isset( $_POST[ $key_badge ] ) ) {
                $val = absint( $_POST[ $key_badge ] );
                set_theme_mod( $key_badge, $val );
                $n++;
            }
            $key_title = 'aiad_principle_title_' . $slug;
            if ( isset( $_POST[ $key_title ] ) ) {
                set_theme_mod( $key_title, sanitize_text_field( wp_unslash( $_POST[ $key_title ] ) ) );
                $n++;
            }
            $key_desc = 'aiad_principle_desc_' . $slug;
            if ( isset( $_POST[ $key_desc ] ) ) {
                set_theme_mod( $key_desc, sanitize_textarea_field( wp_unslash( $_POST[ $key_desc ] ) ) );
                $n++;
            }
        }
        return $n;
    }

    /**
     * Save Video tab.
     *
     * @return int Number of options updated.
     */
    private function save_video(): int {
        $keys = array(
            'aiad_youtube_url'  => 'esc_url_raw',
            'aiad_youtube_title' => 'sanitize_text_field',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save Display Board tab (attachment IDs; same as Customizer).
     *
     * @return int Number of options updated.
     */
    private function save_display_board(): int {
        $keys = array(
            'aiad_display_board_image_1' => 'absint',
            'aiad_display_board_image_2' => 'absint',
            'aiad_display_board_image_3' => 'absint',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save Get Involved tab.
     *
     * @return int Number of options updated.
     */
    private function save_contact(): int {
        $keys = array(
            'aiad_contact_title' => 'sanitize_text_field',
            'aiad_contact_desc'  => 'wp_kses_post',
            'aiad_contact_email' => 'sanitize_email',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save Social tab.
     *
     * @return int Number of options updated.
     */
    private function save_social(): int {
        $keys = array(
            'aiad_linkedin'  => 'esc_url_raw',
            'aiad_instagram' => 'esc_url_raw',
        );
        return $this->save_theme_mods( $keys );
    }

    /**
     * Save a set of theme_mod keys from POST.
     *
     * @param array<string, string> $key_sanitize Map of theme_mod key => sanitize callback name.
     * @return int Number of options updated.
     */
    private function save_theme_mods( array $key_sanitize ): int {
        $n = 0;
        foreach ( $key_sanitize as $key => $sanitize ) {
            if ( ! isset( $_POST[ $key ] ) ) {
                continue;
            }
            $raw = wp_unslash( $_POST[ $key ] );
            if ( 'esc_url_raw' === $sanitize ) {
                $val = esc_url_raw( $raw );
            } elseif ( 'sanitize_text_field' === $sanitize ) {
                $val = sanitize_text_field( $raw );
            } elseif ( 'sanitize_textarea_field' === $sanitize ) {
                $val = sanitize_textarea_field( $raw );
            } elseif ( 'sanitize_email' === $sanitize ) {
                $val = sanitize_email( $raw );
            } elseif ( 'wp_kses_post' === $sanitize ) {
                $val = wp_kses_post( $raw );
            } elseif ( 'absint' === $sanitize ) {
                $val = absint( $raw );
            } else {
                $val = sanitize_text_field( $raw );
            }
            set_theme_mod( $key, $val );
            $n++;
        }
        return $n;
    }

    /**
     * Current tab from request.
     *
     * @return string Tab slug.
     */
    private function current_tab(): string {
        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'hero';
        $allowed = array( 'hero', 'campaign', 'principles', 'video', 'display', 'contact', 'social' );
        return in_array( $tab, $allowed, true ) ? $tab : 'hero';
    }

    /**
     * Output the admin page.
     */
    public function render_page(): void {
        $tab = $this->current_tab();
        $updated = isset( $_GET['updated'] ) ? sanitize_text_field( wp_unslash( $_GET['updated'] ) ) : '';

        echo '<div class="wrap aiad-homepage-editor-wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__( 'Edit Homepage', 'ai-awareness-day' ) . '</h1>';
        echo '<p class="description">' . esc_html__( 'Edit the content of your homepage sections. Changes are saved to the same settings as the Customizer.', 'ai-awareness-day' ) . '</p>';

        if ( '1' === $updated ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'ai-awareness-day' ) . '</p></div>';
        } elseif ( '0' === $updated ) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'No changes to save.', 'ai-awareness-day' ) . '</p></div>';
        }

        $tabs = array(
            'hero'       => __( 'Hero', 'ai-awareness-day' ),
            'campaign'   => __( 'Campaign', 'ai-awareness-day' ),
            'principles' => __( 'Principles & Badges', 'ai-awareness-day' ),
            'video'      => __( 'Video', 'ai-awareness-day' ),
            'display'    => __( 'Display Board', 'ai-awareness-day' ),
            'contact'    => __( 'Get Involved', 'ai-awareness-day' ),
            'social'     => __( 'Social', 'ai-awareness-day' ),
        );
        echo '<nav class="nav-tab-wrapper wp-clearfix" aria-label="' . esc_attr__( 'Homepage sections', 'ai-awareness-day' ) . '">';
        foreach ( $tabs as $slug => $label ) {
            $url = add_query_arg( array( 'page' => self::PAGE_SLUG, 'tab' => $slug ), admin_url( 'themes.php' ) );
            echo '<a href="' . esc_url( $url ) . '" class="nav-tab' . ( $tab === $slug ? ' nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>';
        }
        echo '</nav>';

        echo '<form method="post" action="" class="aiad-editor-form">';
        wp_nonce_field( self::NONCE_ACTION, 'aiad_homepage_editor_nonce' );
        echo '<input type="hidden" name="aiad_editor_tab" value="' . esc_attr( $tab ) . '" />';

        switch ( $tab ) {
            case 'hero':
                $this->render_hero_tab();
                break;
            case 'campaign':
                $this->render_campaign_tab();
                break;
            case 'principles':
                $this->render_principles_tab();
                break;
            case 'video':
                $this->render_video_tab();
                break;
            case 'display':
                $this->render_display_tab();
                break;
            case 'contact':
                $this->render_contact_tab();
                break;
            case 'social':
                $this->render_social_tab();
                break;
        }

        echo '<p class="submit"><button type="submit" class="button button-primary">' . esc_html__( 'Save changes', 'ai-awareness-day' ) . '</button></p>';
        echo '</form></div>';
    }

    /**
     * Render Hero tab fields.
     */
    private function render_hero_tab(): void {
        $fields = array(
            'aiad_hero_logo'     => array( 'label' => __( 'Hero Logo', 'ai-awareness-day' ), 'type' => 'image', 'description' => __( 'Image shown above the date.', 'ai-awareness-day' ) ),
            'aiad_header_logo'   => array( 'label' => __( 'Header Logo', 'ai-awareness-day' ), 'type' => 'image', 'description' => __( 'Logo in the site header.', 'ai-awareness-day' ) ),
            'aiad_hero_date'     => array( 'label' => __( 'Event Date Text', 'ai-awareness-day' ), 'type' => 'text', 'default' => 'Thursday 4th June 2026' ),
            'aiad_hero_title'    => array( 'label' => __( 'Hero Title', 'ai-awareness-day' ), 'type' => 'text', 'default' => 'AI Awareness Day' ),
            'aiad_hero_slogan'   => array( 'label' => __( 'Hero Slogan', 'ai-awareness-day' ), 'type' => 'text', 'default' => 'Know it, Question it, Use it Wisely' ),
            'aiad_hero_subtitle' => array( 'label' => __( 'Hero Subtitle', 'ai-awareness-day' ), 'type' => 'textarea', 'default' => 'A nationwide day for schools, students, and parents to explore AI together.' ),
        );
        $this->render_fields( $fields );
    }

    /**
     * Render Campaign tab fields.
     */
    private function render_campaign_tab(): void {
        $fields = array(
            'aiad_campaign_title'  => array( 'label' => __( 'Campaign Title', 'ai-awareness-day' ), 'type' => 'text', 'default' => 'What is AI Awareness Day?' ),
            'aiad_campaign_text'   => array( 'label' => __( 'Campaign Description', 'ai-awareness-day' ), 'type' => 'textarea', 'default' => 'National AI Awareness Day (4th June 2026) is a new nationwide campaign...' ),
            'aiad_campaign_text_2' => array( 'label' => __( 'Campaign Paragraph 2', 'ai-awareness-day' ), 'type' => 'textarea', 'default' => 'Our goal is to create a unified moment...' ),
        );
        $this->render_fields( $fields );
    }

    /**
     * Render Principles & Badges tab.
     */
    private function render_principles_tab(): void {
        $slugs  = array( 'safe', 'smart', 'creative', 'responsible', 'future' );
        $titles = array(
            'safe'        => __( 'Safe', 'ai-awareness-day' ),
            'smart'       => __( 'Smart', 'ai-awareness-day' ),
            'creative'    => __( 'Creative', 'ai-awareness-day' ),
            'responsible' => __( 'Responsible', 'ai-awareness-day' ),
            'future'      => __( 'Future', 'ai-awareness-day' ),
        );
        $descs = array(
            'safe'        => __( 'Ensuring safe and secure interactions with AI technologies.', 'ai-awareness-day' ),
            'smart'       => __( 'Building intelligent understanding of how AI works.', 'ai-awareness-day' ),
            'creative'    => __( 'Harnessing AI as a tool for creativity and innovation.', 'ai-awareness-day' ),
            'responsible' => __( 'Promoting ethical and responsible use of AI.', 'ai-awareness-day' ),
            'future'      => __( 'Preparing for an AI-shaped future with confidence.', 'ai-awareness-day' ),
        );
        echo '<table class="form-table" role="presentation">';
        foreach ( $slugs as $slug ) {
            $badge_id = absint( get_theme_mod( 'aiad_badge_' . $slug, 0 ) );
            $title    = get_theme_mod( 'aiad_principle_title_' . $slug, $titles[ $slug ] );
            $desc     = get_theme_mod( 'aiad_principle_desc_' . $slug, $descs[ $slug ] );
            echo '<tr><th scope="row">' . esc_html( ucfirst( $slug ) ) . '</th><td>';
            echo '<p><label>' . esc_html__( 'Badge image', 'ai-awareness-day' ) . '</label><br>';
            $this->render_media_input( 'aiad_badge_' . $slug, $badge_id, 'image' );
            echo '</p><p><label for="aiad_principle_title_' . esc_attr( $slug ) . '">' . esc_html__( 'Title', 'ai-awareness-day' ) . '</label><br>';
            echo '<input type="text" id="aiad_principle_title_' . esc_attr( $slug ) . '" name="aiad_principle_title_' . esc_attr( $slug ) . '" value="' . esc_attr( $title ) . '" class="regular-text" /></p>';
            echo '<p><label for="aiad_principle_desc_' . esc_attr( $slug ) . '">' . esc_html__( 'Description', 'ai-awareness-day' ) . '</label><br>';
            echo '<textarea id="aiad_principle_desc_' . esc_attr( $slug ) . '" name="aiad_principle_desc_' . esc_attr( $slug ) . '" rows="2" class="large-text">' . esc_textarea( $desc ) . '</textarea></p>';
            echo '</td></tr>';
        }
        echo '</table>';
    }

    /**
     * Render Video tab fields.
     */
    private function render_video_tab(): void {
        $fields = array(
            'aiad_youtube_url'  => array( 'label' => __( 'YouTube URL', 'ai-awareness-day' ), 'type' => 'url', 'default' => '', 'description' => __( 'e.g. https://www.youtube.com/watch?v=VIDEO_ID', 'ai-awareness-day' ) ),
            'aiad_youtube_title' => array( 'label' => __( 'Section title', 'ai-awareness-day' ), 'type' => 'text', 'default' => __( 'Watch', 'ai-awareness-day' ) ),
        );
        $this->render_fields( $fields );
    }

    /**
     * Render Display Board tab (attachment IDs; same as Customizer).
     */
    private function render_display_tab(): void {
        $fields = array(
            'aiad_display_board_image_1' => array( 'label' => __( 'Example display board 1', 'ai-awareness-day' ), 'type' => 'image', 'default' => 0 ),
            'aiad_display_board_image_2' => array( 'label' => __( 'Example display board 2', 'ai-awareness-day' ), 'type' => 'image', 'default' => 0 ),
            'aiad_display_board_image_3' => array( 'label' => __( 'Example display board 3', 'ai-awareness-day' ), 'type' => 'image', 'default' => 0 ),
        );
        echo '<table class="form-table" role="presentation">';
        foreach ( $fields as $key => $config ) {
            $val = get_theme_mod( $key, $config['default'] );
            $id = absint( is_numeric( $val ) ? $val : 0 );
            echo '<tr><th scope="row">' . esc_html( $config['label'] ) . '</th><td>';
            $this->render_media_input( $key, $id, 'image' );
            echo '</td></tr>';
        }
        echo '</table>';
    }

    /**
     * Render Get Involved tab fields.
     */
    private function render_contact_tab(): void {
        $fields = array(
            'aiad_contact_title' => array( 'label' => __( 'Section Title', 'ai-awareness-day' ), 'type' => 'text', 'default' => 'Get Involved' ),
            'aiad_contact_desc'  => array( 'label' => __( 'Description', 'ai-awareness-day' ), 'type' => 'textarea', 'default' => "Whether you're a teacher, school leader..." ),
            'aiad_contact_email' => array( 'label' => __( 'Notification Email', 'ai-awareness-day' ), 'type' => 'email', 'default' => '', 'description' => __( 'Form submissions are sent here.', 'ai-awareness-day' ) ),
        );
        $this->render_fields( $fields );
    }

    /**
     * Render Social tab fields.
     */
    private function render_social_tab(): void {
        $fields = array(
            'aiad_linkedin'  => array( 'label' => __( 'LinkedIn URL', 'ai-awareness-day' ), 'type' => 'url', 'default' => 'https://www.linkedin.com/company/110126438/' ),
            'aiad_instagram' => array( 'label' => __( 'Instagram URL', 'ai-awareness-day' ), 'type' => 'url', 'default' => '#' ),
        );
        $this->render_fields( $fields );
    }

    /**
     * Render a generic set of form fields.
     *
     * @param array<string, array> $fields Field config keyed by theme_mod key.
     */
    private function render_fields( array $fields ): void {
        echo '<table class="form-table" role="presentation">';
        foreach ( $fields as $key => $config ) {
            $val = get_theme_mod( $key, $config['default'] ?? '' );
            $label = $config['label'] ?? $key;
            $type  = $config['type'] ?? 'text';
            $desc  = $config['description'] ?? '';
            echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
            if ( 'image' === $type ) {
                $this->render_media_input( $key, absint( $val ), 'image' );
            } elseif ( 'image_url' === $type ) {
                $this->render_media_input( $key, $val, 'url' );
            } elseif ( 'textarea' === $type ) {
                echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="4" class="large-text">' . esc_textarea( $val ) . '</textarea>';
            } elseif ( 'url' === $type || 'email' === $type ) {
                echo '<input type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" />';
            } else {
                echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" />';
            }
            if ( $desc ) {
                echo '<p class="description">' . esc_html( $desc ) . '</p>';
            }
            echo '</td></tr>';
        }
        echo '</table>';
    }

    /**
     * Output media upload button and hidden input. For image type stores attachment ID; for url type stores image URL.
     *
     * @param string     $key    Theme mod key / input name.
     * @param int|string $value  Current value (attachment ID or URL).
     * @param string     $store  'image' = store ID, 'url' = store URL.
     */
    private function render_media_input( string $key, $value, string $store = 'image' ): void {
        $id = absint( is_numeric( $value ) ? $value : 0 );
        $url = '';
        if ( $id ) {
            $url = wp_get_attachment_image_url( $id, 'medium' );
        } elseif ( is_string( $value ) && $value ) {
            $url = $value;
        }
        echo '<input type="hidden" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . ( 'image' === $store ? esc_attr( (string) $id ) : esc_url( $url ) ) . '" class="aiad-media-id" data-store="' . esc_attr( $store ) . '" />';
        echo ' <button type="button" class="button aiad-upload-media" data-target="' . esc_attr( $key ) . '" data-store="' . esc_attr( $store ) . '">' . esc_html__( 'Select / Upload', 'ai-awareness-day' ) . '</button>';
        if ( $url ) {
            echo ' <span class="aiad-media-preview"><img src="' . esc_url( $url ) . '" alt="" aria-hidden="true" style="max-width:120px;height:auto;vertical-align:middle;" /></span>';
        }
    }
}

new AIAD_Homepage_Editor();
