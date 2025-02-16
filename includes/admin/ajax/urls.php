<?php
/**
 * URL-related AJAX handlers
 *
 * @package UpBlock
 * @subpackage Admin\Ajax
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request to add URL to blocklist.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_add_url() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['url'])) {
        wp_send_json_error([
            'message' => __('URL parameter is missing.', 'upblock')
        ], 400);
    }

    $url = sanitize_text_field(wp_unslash($_POST['url']));
    if (empty($url)) {
        wp_send_json_error([
            'message' => __('URL cannot be empty.', 'upblock')
        ], 400);
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        wp_send_json_error([
            'message' => __('Invalid URL format.', 'upblock')
        ], 400);
    }

    $blocked_urls = get_option('upblock_blocked_urls', []);

    if (in_array($url, $blocked_urls, true)) {
        wp_send_json_error([
            'message' => __('URL is already blocked.', 'upblock')
        ], 409);
    }

    $blocked_urls[] = $url;
    if (update_option('upblock_blocked_urls', $blocked_urls)) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: URL */
                __('URL %s added successfully.', 'upblock'),
                $url
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error adding URL.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_add_url', 'upblock_ajax_add_url');

/**
 * Handle AJAX request to remove URL from blocklist.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_remove_url() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['url'])) {
        wp_send_json_error([
            'message' => __('URL parameter is missing.', 'upblock')
        ], 400);
    }

    $url = sanitize_text_field(wp_unslash($_POST['url']));
    if (empty($url)) {
        wp_send_json_error([
            'message' => __('URL cannot be empty.', 'upblock')
        ], 400);
    }

    $blocked_urls = get_option('upblock_blocked_urls', []);
    $key = array_search($url, $blocked_urls, true);

    if ($key === false) {
        wp_send_json_error([
            'message' => __('URL not found in blocklist.', 'upblock')
        ], 404);
    }

    unset($blocked_urls[$key]);
    if (update_option('upblock_blocked_urls', array_values($blocked_urls))) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: URL */
                __('URL %s removed successfully.', 'upblock'),
                $url
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error removing URL.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_remove_url', 'upblock_ajax_remove_url');

/**
 * Handle AJAX request to block URL from logs.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_block_url() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['url'])) {
        wp_send_json_error([
            'message' => __('URL parameter is missing.', 'upblock')
        ], 400);
    }

    $url = sanitize_text_field(wp_unslash($_POST['url']));
    if (empty($url)) {
        wp_send_json_error([
            'message' => __('URL cannot be empty.', 'upblock')
        ], 400);
    }

    $blocked_urls = get_option('upblock_blocked_urls', []);

    if (in_array($url, $blocked_urls, true)) {
        wp_send_json_error([
            'message' => __('URL is already blocked.', 'upblock')
        ], 409);
    }

    $blocked_urls[] = $url;
    if (update_option('upblock_blocked_urls', $blocked_urls)) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: URL */
                __('URL %s blocked successfully.', 'upblock'),
                $url
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error blocking URL.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_block_url', 'upblock_ajax_block_url'); 