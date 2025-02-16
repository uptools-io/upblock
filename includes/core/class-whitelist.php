<?php
/**
 * Whitelist functionality
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
 * Class Whitelist
 *
 * Contains the list of whitelisted domains that should never be blocked or logged.
 * This class provides static methods to check and retrieve whitelisted domains.
 *
 * @since 1.0.0
 * @package UpBlock\Core
 */
class Whitelist {
    /**
     * List of whitelisted domains.
     *
     * These domains will never be blocked or logged by the plugin.
     * The list includes official WordPress, WooCommerce, HelloWP, and UpTools domains.
     *
     * @since 1.0.0
     * @var array<string> Array of whitelisted domain names.
     */
    private static $domains = [
        // WordPress official domains
        'api.wordpress.org',
        'downloads.wordpress.org',
        'wp-json.app',
        'translate.wordpress.com',
        
        // WooCommerce domains
        'woocommerce.com',
        'woo.com',
        
        // HelloWP domains
        'hellowp.io',
        'hellowp.cloud',
        'mailv3.hellowp.cloud',
        'api.hellowp.cloud',
        'cdn.hellowp.cloud',
        'update.hellowp.cloud',
        'license.hellowp.cloud',
        'api.v2.wp-json.app',
        
        // UpTools domains
        'uptools.io',
    ];

    /**
     * Check if a domain is whitelisted.
     *
     * @since 1.0.0
     * @param string $domain The domain name to check.
     * @return bool True if domain is whitelisted, false otherwise.
     */
    public static function is_whitelisted($domain) {
        return in_array($domain, self::$domains, true);
    }

    /**
     * Get all whitelisted domains.
     *
     * @since 1.0.0
     * @return array<string> Array of whitelisted domain names.
     */
    public static function get_domains() {
        return self::$domains;
    }
} 