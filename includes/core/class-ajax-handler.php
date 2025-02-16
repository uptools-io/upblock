<?php
/**
 * AJAX Handler functionality
 *
 * @package UpBlock
 * @subpackage Core
 * @since 1.0.0
 */

namespace UpBlock\Core;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Ajax_Handler
 *
 * Handles AJAX requests for managing blocked domains and URLs.
 *
 * @since 1.0.0
 * @package UpBlock\Core
 */
class Ajax_Handler {
    /**
     * Blocker instance.
     *
     * @since 1.0.0
     * @var Blocker
     */
    private $blocker;

    /**
     * Constructor.
     *
     * Initializes the AJAX handler and sets up hooks.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->blocker = new Blocker();
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks.
     *
     * @since 1.0.0
     * @return void
     */
    private function init_hooks() {
        add_action('wp_ajax_upblock_add_blocked_domain', [$this, 'add_blocked_domain']);
        add_action('wp_ajax_upblock_remove_blocked_domain', [$this, 'remove_blocked_domain']);
        add_action('wp_ajax_upblock_add_blocked_url', [$this, 'add_blocked_url']);
        add_action('wp_ajax_upblock_remove_blocked_url', [$this, 'remove_blocked_url']);
    }

    /**
     * Add a domain to the blocklist via AJAX.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_blocked_domain() {
        check_ajax_referer('upblock_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                __('Insufficient permissions to perform this action.', 'upblock'),
                403
            );
        }

        if (!isset($_POST['domain'])) {
            wp_send_json_error(
                __('Domain parameter is missing.', 'upblock'),
                400
            );
        }

        $domain = sanitize_text_field(wp_unslash($_POST['domain']));
        
        if (empty($domain)) {
            wp_send_json_error(
                __('Domain cannot be empty.', 'upblock'),
                400
            );
        }

        $result = $this->blocker->add_blocked_domain($domain);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 500);
        }

        if (!$result) {
            wp_send_json_error(
                __('Domain is already blocked.', 'upblock'),
                409
            );
        }

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: domain name */
                __('Domain %s has been blocked successfully.', 'upblock'),
                $domain
            )
        ]);
    }

    /**
     * Remove a domain from the blocklist via AJAX.
     *
     * @since 1.0.0
     * @return void
     */
    public function remove_blocked_domain() {
        check_ajax_referer('upblock_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                __('Insufficient permissions to perform this action.', 'upblock'),
                403
            );
        }

        if (!isset($_POST['domain'])) {
            wp_send_json_error(
                __('Domain parameter is missing.', 'upblock'),
                400
            );
        }

        $domain = sanitize_text_field(wp_unslash($_POST['domain']));
        
        if (empty($domain)) {
            wp_send_json_error(
                __('Domain cannot be empty.', 'upblock'),
                400
            );
        }

        $result = $this->blocker->remove_blocked_domain($domain);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 500);
        }

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: domain name */
                __('Domain %s has been unblocked successfully.', 'upblock'),
                $domain
            )
        ]);
    }

    /**
     * Add a URL pattern to the blocklist via AJAX.
     *
     * @since 1.0.0
     * @return void
     */
    public function add_blocked_url() {
        check_ajax_referer('upblock_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                __('Insufficient permissions to perform this action.', 'upblock'),
                403
            );
        }

        if (!isset($_POST['url'])) {
            wp_send_json_error(
                __('URL parameter is missing.', 'upblock'),
                400
            );
        }

        $url = esc_url_raw(wp_unslash($_POST['url']));
        
        if (empty($url)) {
            wp_send_json_error(
                __('URL cannot be empty.', 'upblock'),
                400
            );
        }

        $result = $this->blocker->add_blocked_url($url);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 500);
        }

        if (!$result) {
            wp_send_json_error(
                __('URL pattern is already blocked.', 'upblock'),
                409
            );
        }

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: URL pattern */
                __('URL pattern %s has been blocked successfully.', 'upblock'),
                $url
            )
        ]);
    }

    /**
     * Remove a URL pattern from the blocklist via AJAX.
     *
     * @since 1.0.0
     * @return void
     */
    public function remove_blocked_url() {
        check_ajax_referer('upblock_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(
                __('Insufficient permissions to perform this action.', 'upblock'),
                403
            );
        }

        if (!isset($_POST['url'])) {
            wp_send_json_error(
                __('URL parameter is missing.', 'upblock'),
                400
            );
        }

        $url = esc_url_raw(wp_unslash($_POST['url']));
        
        if (empty($url)) {
            wp_send_json_error(
                __('URL cannot be empty.', 'upblock'),
                400
            );
        }

        $result = $this->blocker->remove_blocked_url($url);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 500);
        }

        wp_send_json_success([
            'message' => sprintf(
                /* translators: %s: URL pattern */
                __('URL pattern %s has been unblocked successfully.', 'upblock'),
                $url
            )
        ]);
    }
} 