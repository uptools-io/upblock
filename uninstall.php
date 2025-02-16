<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Removes all plugin data from the database:
 * - Custom options
 * - Custom database tables
 *
 * @package UpBlock
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Delete all plugin data
 *
 * @since 1.0.0
 * @return void
 */
function upblock_uninstall() {
    global $wpdb;

    // Delete options
    delete_option('upblock_blocked_domains');
    delete_option('upblock_blocked_urls');

    // Drop custom table
    $table_name = $wpdb->prefix . 'upblock_request_logs';
    $wpdb->query($wpdb->prepare("DROP TABLE IF EXISTS %i", $table_name));
}

upblock_uninstall(); 