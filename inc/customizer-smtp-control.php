<?php
/**
 * Custom Customizer Control: SMTP setup instructions (Get Involved section).
 * Loaded only when registering Customizer controls to avoid fatal on front-end.
 *
 * @package AI_Awareness_Day
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_Customize_Control' ) ) {
    return;
}

/**
 * Custom Customizer Control for SMTP Information
 */
class AIAD_SMTP_Info_Control extends WP_Customize_Control {

    /** @var string Control type. */
    public $type = 'aiad_smtp_info';

    /**
     * Render the control's content.
     */
    public function render_content(): void {
        ?>
        <div class="aiad-smtp-info-control" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 15px 0;">
            <h4 style="margin-top: 0; margin-bottom: 10px; color: #856404;">
                📧 <?php esc_html_e( 'Email Delivery Setup Required', 'ai-awareness-day' ); ?>
            </h4>
            <p style="margin-bottom: 12px; color: #856404;">
                <strong><?php esc_html_e( 'Important:', 'ai-awareness-day' ); ?></strong>
                <?php esc_html_e( 'For reliable email delivery, you need to install and configure the WP Mail SMTP plugin.', 'ai-awareness-day' ); ?>
            </p>
            <ol style="margin: 0; padding-left: 20px; color: #856404;">
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Go to', 'ai-awareness-day' ); ?>
                    <strong><?php esc_html_e( 'Plugins → Add New', 'ai-awareness-day' ); ?></strong>
                </li>
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Search for', 'ai-awareness-day' ); ?>
                    <strong>"WP Mail SMTP"</strong>
                </li>
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Click', 'ai-awareness-day' ); ?>
                    <strong><?php esc_html_e( 'Install Now', 'ai-awareness-day' ); ?></strong>
                    <?php esc_html_e( 'and', 'ai-awareness-day' ); ?>
                    <strong><?php esc_html_e( 'Activate', 'ai-awareness-day' ); ?></strong>
                </li>
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Go to', 'ai-awareness-day' ); ?>
                    <strong><?php esc_html_e( 'WP Mail SMTP → Settings', 'ai-awareness-day' ); ?></strong>
                </li>
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Configure with your hosting provider\'s SMTP settings (or use Gmail/SendGrid)', 'ai-awareness-day' ); ?>
                </li>
                <li style="margin-bottom: 8px;">
                    <?php esc_html_e( 'Send a test email to verify it works', 'ai-awareness-day' ); ?>
                </li>
            </ol>
            <p style="margin-top: 12px; margin-bottom: 0; color: #856404; font-size: 13px;">
                <em>
                    <?php esc_html_e( 'Note: Without SMTP, emails may not be delivered or may go to spam. Your form will still save submissions to the database, but email notifications require SMTP configuration.', 'ai-awareness-day' ); ?>
                </em>
            </p>
        </div>
        <?php
    }
}
