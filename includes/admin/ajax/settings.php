<?php
/**
 * Settings-related AJAX handlers
 *
 * @package UpBlock
 * @subpackage Admin\Ajax
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request to save settings.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_save_settings() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['settings'])) {
        wp_send_json_error([
            'message' => __('Settings parameter is missing.', 'upblock')
        ], 400);
    }

    $settings = json_decode(wp_unslash($_POST['settings']), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        wp_send_json_error([
            'message' => __('Invalid settings format.', 'upblock')
        ], 400);
    }

    // Debug log
    error_log('Received settings: ' . print_r($settings, true));

    $valid_settings = [
        'log_retention_days' => [
            'filter' => FILTER_VALIDATE_INT,
            'options' => ['min_range' => 1, 'max_range' => 365],
            'option_name' => 'upblock_log_retention_days'
        ],
        'enable_logging' => [
            'filter' => FILTER_VALIDATE_BOOLEAN,
            'option_name' => 'upblock_enable_logging'
        ],
        'enable_auto_cleanup' => [
            'filter' => FILTER_VALIDATE_BOOLEAN,
            'option_name' => 'upblock_enable_auto_cleanup'
        ]
    ];

    $sanitized_settings = [];
    foreach ($valid_settings as $key => $config) {
        if (isset($settings[$key])) {
            $filter = $config['filter'];
            $options = isset($config['options']) ? $config['options'] : null;
            
            $value = filter_var($settings[$key], $filter, ['options' => $options]);
            
            // Debug log
            error_log("Processing setting {$key}: Input = " . print_r($settings[$key], true) . ", Filtered = " . print_r($value, true));
            
            if ($value !== false || ($config['filter'] === FILTER_VALIDATE_BOOLEAN && $value === false)) {
                $sanitized_settings[$key] = [
                    'value' => $value,
                    'option_name' => $config['option_name']
                ];
            }
        }
    }

    if (empty($sanitized_settings)) {
        wp_send_json_error([
            'message' => __('No valid settings provided.', 'upblock')
        ], 400);
    }

    // Debug log
    error_log('Sanitized settings: ' . print_r($sanitized_settings, true));

    $success = true;
    $failed_options = [];

    foreach ($sanitized_settings as $key => $setting) {
        // Delete the option first to ensure clean update
        delete_option($setting['option_name']);
        
        // Try to add the option
        if (!add_option($setting['option_name'], $setting['value'])) {
            // If add failed, try to update
            if (!update_option($setting['option_name'], $setting['value'])) {
                $success = false;
                $failed_options[] = $setting['option_name'];
                error_log("Failed to update option: {$setting['option_name']} with value: " . print_r($setting['value'], true));
            } else {
                error_log("Successfully updated option: {$setting['option_name']} with value: " . print_r($setting['value'], true));
            }
        } else {
            error_log("Successfully added option: {$setting['option_name']} with value: " . print_r($setting['value'], true));
        }
    }

    if ($success) {
        wp_send_json_success([
            'message' => __('Settings saved successfully.', 'upblock'),
            'settings' => array_map(function($setting) {
                return $setting['value'];
            }, $sanitized_settings)
        ]);
    }

    wp_send_json_error([
        'message' => sprintf(
            /* translators: %s: comma-separated list of failed options */
            __('Error saving settings. Failed options: %s', 'upblock'),
            implode(', ', $failed_options)
        ),
        'failed_options' => $failed_options
    ], 500);
}
add_action('wp_ajax_upblock_save_settings', 'upblock_ajax_save_settings');

/**
 * Handle AJAX request to get settings.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_get_settings() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    $settings = [
        'log_retention_days' => (int) get_option('upblock_log_retention_days', 30),
        'enable_logging' => (bool) get_option('upblock_enable_logging', true),
        'enable_auto_cleanup' => (bool) get_option('upblock_enable_auto_cleanup', true)
    ];

    wp_send_json_success([
        'settings' => $settings
    ]);
}
add_action('wp_ajax_upblock_get_settings', 'upblock_ajax_get_settings'); 