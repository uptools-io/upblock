<?php
/**
 * Log-related AJAX handlers
 *
 * @package UpBlock
 * @subpackage Admin\Ajax
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request to get request logs.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_get_logs() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'upblock_request_logs';
    $logs = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table_name} ORDER BY timestamp DESC LIMIT %d",
            100
        )
    );

    if ($logs === null) {
        wp_send_json_error([
            'message' => __('Error retrieving logs.', 'upblock')
        ], 500);
    }

    wp_send_json_success([
        'logs' => $logs
    ]);
}
add_action('wp_ajax_upblock_get_logs', 'upblock_ajax_get_logs');

/**
 * Handle AJAX request to get top blocked domains.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_get_top_domains() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'upblock_request_logs';
    $domains = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT domain, COUNT(*) as count FROM {$table_name} GROUP BY domain ORDER BY count DESC LIMIT %d",
            10
        )
    );

    if ($domains === null) {
        wp_send_json_error([
            'message' => __('Error retrieving top domains.', 'upblock')
        ], 500);
    }

    wp_send_json_success([
        'domains' => $domains
    ]);
}
add_action('wp_ajax_upblock_get_top_domains', 'upblock_ajax_get_top_domains');

/**
 * Handle AJAX request to clear all logs.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_clear_logs() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'upblock_request_logs';
    $result = $wpdb->query("TRUNCATE TABLE {$table_name}");

    if ($result === false) {
        wp_send_json_error([
            'message' => __('Error clearing logs.', 'upblock')
        ], 500);
    }

    wp_send_json_success([
        'message' => __('Logs cleared successfully.', 'upblock')
    ]);
}
add_action('wp_ajax_upblock_clear_logs', 'upblock_ajax_clear_logs'); 