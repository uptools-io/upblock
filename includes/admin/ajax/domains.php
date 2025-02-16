<?php
/**
 * Domain-related AJAX handlers
 *
 * @package UpBlock
 * @subpackage Admin\Ajax
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handle AJAX request to add domain to blocklist.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_add_domain() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['domain'])) {
        wp_send_json_error([
            'message' => __('Domain parameter is missing.', 'upblock')
        ], 400);
    }

    $domain = sanitize_text_field(wp_unslash($_POST['domain']));
    if (empty($domain)) {
        wp_send_json_error([
            'message' => __('Domain cannot be empty.', 'upblock')
        ], 400);
    }

    $blocked_domains = get_option('upblock_blocked_domains', []);

    if (in_array($domain, $blocked_domains, true)) {
        wp_send_json_error([
            'message' => __('Domain is already blocked.', 'upblock')
        ], 409);
    }

    $blocked_domains[] = $domain;
    if (update_option('upblock_blocked_domains', $blocked_domains)) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: domain name */
                __('Domain %s added successfully.', 'upblock'),
                $domain
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error adding domain.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_add_domain', 'upblock_ajax_add_domain');

/**
 * Handle AJAX request to remove domain from blocklist.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_remove_domain() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['domain'])) {
        wp_send_json_error([
            'message' => __('Domain parameter is missing.', 'upblock')
        ], 400);
    }

    $domain = sanitize_text_field(wp_unslash($_POST['domain']));
    if (empty($domain)) {
        wp_send_json_error([
            'message' => __('Domain cannot be empty.', 'upblock')
        ], 400);
    }

    $blocked_domains = get_option('upblock_blocked_domains', []);
    $key = array_search($domain, $blocked_domains, true);

    if ($key === false) {
        wp_send_json_error([
            'message' => __('Domain not found in blocklist.', 'upblock')
        ], 404);
    }

    unset($blocked_domains[$key]);
    if (update_option('upblock_blocked_domains', array_values($blocked_domains))) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: domain name */
                __('Domain %s removed successfully.', 'upblock'),
                $domain
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error removing domain.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_remove_domain', 'upblock_ajax_remove_domain');

/**
 * Handle AJAX request to block domain from logs.
 *
 * @since 1.0.0
 * @return void
 */
function upblock_ajax_block_domain() {
    check_ajax_referer('upblock_nonce', '_wpnonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error([
            'message' => __('You do not have permission to perform this action.', 'upblock')
        ], 403);
    }

    if (!isset($_POST['domain'])) {
        wp_send_json_error([
            'message' => __('Domain parameter is missing.', 'upblock')
        ], 400);
    }

    $domain = sanitize_text_field(wp_unslash($_POST['domain']));
    if (empty($domain)) {
        wp_send_json_error([
            'message' => __('Domain cannot be empty.', 'upblock')
        ], 400);
    }

    $blocked_domains = get_option('upblock_blocked_domains', []);

    if (in_array($domain, $blocked_domains, true)) {
        wp_send_json_error([
            'message' => __('Domain is already blocked.', 'upblock')
        ], 409);
    }

    $blocked_domains[] = $domain;
    if (update_option('upblock_blocked_domains', $blocked_domains)) {
        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: domain name */
                __('Domain %s blocked successfully.', 'upblock'),
                $domain
            )
        ]);
    }

    wp_send_json_error([
        'message' => __('Error blocking domain.', 'upblock')
    ], 500);
}
add_action('wp_ajax_upblock_block_domain', 'upblock_ajax_block_domain'); 