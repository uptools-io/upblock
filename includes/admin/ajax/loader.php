<?php
/**
 * AJAX handlers loader
 *
 * @package UpBlock
 * @subpackage Admin\Ajax
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load all AJAX handlers
require_once plugin_dir_path(__FILE__) . 'domains.php';
require_once plugin_dir_path(__FILE__) . 'urls.php';
require_once plugin_dir_path(__FILE__) . 'logs.php';
require_once plugin_dir_path(__FILE__) . 'settings.php'; 