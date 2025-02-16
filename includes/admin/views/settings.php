<?php
/**
 * Admin settings page template
 *
 * @package UpBlock
 * @subpackage Admin\Views
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get settings with defaults
$settings = [
    'log_retention_days' => get_option('upblock_log_retention_days', 30),
    'log_blocked_requests' => get_option('upblock_enable_logging', false),
    'enable_auto_cleanup' => get_option('upblock_enable_auto_cleanup', true)
];

// Ensure integer value for retention days
$settings['log_retention_days'] = absint($settings['log_retention_days']);
?>

<div class="wrap upblock-admin">
    <?php settings_errors('upblock-settings'); ?>

    <div id="upblock-notification" class="upblock-notification" style="display: none;">
        <div class="upblock-notification-content">
            <span class="dashicons dashicons-yes-alt"></span>
            <span class="upblock-notification-message"></span>
        </div>
    </div>

    <?php include dirname(__FILE__) . '/components/header.php'; ?>

    <div class="upblock-grid">
        <div class="upblock-card">
            <div class="upblock-card-header">
                <h2><?php esc_html_e('Logging Settings', 'upblock'); ?></h2>
            </div>
            <div class="upblock-card-content">
                <form method="post" action="" id="upblock-settings-form">
                    <?php wp_nonce_field('upblock_nonce'); ?>
                    
                    <div class="upblock-form-group">
                        <div class="upblock-toggle-switch">
                            <div class="upblock-toggle-switch-content">
                                <div class="upblock-toggle-switch-title">
                                    <?php esc_html_e('Log Retention Period', 'upblock'); ?>
                                </div>
                                <p class="upblock-toggle-switch-description">
                                    <?php esc_html_e('Logs older than this many days will be automatically deleted.', 'upblock'); ?>
                                </p>
                            </div>
                            <div class="upblock-number-input">
                                <button type="button" class="upblock-number-button upblock-number-decrease">
                                    <span class="dashicons dashicons-minus"></span>
                                </button>
                                <div class="upblock-number-field">
                                    <input type="number" 
                                           id="log_retention_days" 
                                           name="log_retention_days" 
                                           value="<?php echo esc_attr($settings['log_retention_days']); ?>" 
                                           min="1" 
                                           max="365"
                                           aria-label="<?php esc_attr_e('Log retention period in days', 'upblock'); ?>">
                                    <span class="upblock-number-label"><?php esc_html_e('days', 'upblock'); ?></span>
                                </div>
                                <button type="button" class="upblock-number-button upblock-number-increase">
                                    <span class="dashicons dashicons-plus"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="upblock-form-group">
                        <div class="upblock-toggle-switch">
                            <div class="upblock-toggle-switch-content">
                                <div class="upblock-toggle-switch-title">
                                    <?php esc_html_e('Log Blocked Requests', 'upblock'); ?>
                                </div>
                                <p class="upblock-toggle-switch-description">
                                    <?php esc_html_e('When enabled, blocked requests will be logged and visible in the logs. Disable to save database space.', 'upblock'); ?>
                                </p>
                            </div>
                            <label class="upblock-toggle">
                                <input type="checkbox" 
                                       id="log_blocked_requests"
                                       name="log_blocked_requests" 
                                       value="1"
                                       <?php checked($settings['log_blocked_requests'], true); ?>
                                       aria-label="<?php esc_attr_e('Enable logging of blocked requests', 'upblock'); ?>">
                                <span class="upblock-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="upblock-form-group">
                        <div class="upblock-toggle-switch">
                            <div class="upblock-toggle-switch-content">
                                <div class="upblock-toggle-switch-title">
                                    <?php esc_html_e('Auto Cleanup', 'upblock'); ?>
                                </div>
                                <p class="upblock-toggle-switch-description">
                                    <?php esc_html_e('Automatically clean up old logs based on the retention period.', 'upblock'); ?>
                                </p>
                            </div>
                            <label class="upblock-toggle">
                                <input type="checkbox" 
                                       id="enable_auto_cleanup"
                                       name="enable_auto_cleanup" 
                                       value="1"
                                       <?php checked($settings['enable_auto_cleanup'], true); ?>
                                       aria-label="<?php esc_attr_e('Enable automatic log cleanup', 'upblock'); ?>">
                                <span class="upblock-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="upblock-form-actions">
                        <button type="submit" 
                                name="upblock_save_settings" 
                                class="upblock-button upblock-button-primary">
                            <?php esc_html_e('Save Settings', 'upblock'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 