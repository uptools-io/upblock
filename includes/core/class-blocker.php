<?php
/**
 * HTTP Request Blocker functionality
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
 * Class Blocker
 *
 * Handles blocking of HTTP requests based on domain and URL patterns.
 * Provides functionality to manage blocked domains and URLs, and logs request activity.
 *
 * @since 1.0.0
 * @package UpBlock\Core
 */
class Blocker {
    /**
     * Array of blocked domain names.
     *
     * @since 1.0.0
     * @var array<string>
     */
    private $blocked_domains = [];

    /**
     * Array of blocked URL patterns.
     *
     * @since 1.0.0
     * @var array<string>
     */
    private $blocked_urls = [];

    /**
     * Logger instance.
     *
     * @since 1.0.0
     * @var Logger
     */
    private $logger;

    /**
     * Request start times for response time calculation.
     *
     * @since 1.0.0
     * @var array<string,float>
     */
    private $start_times = [];

    /**
     * Last log times for rate limiting.
     *
     * @since 1.0.0
     * @var array<string,float>
     */
    private static $last_log_times = [];

    /**
     * Blocked requests tracking.
     *
     * @since 1.0.0
     * @var array<string,bool>
     */
    private static $blocked_requests = [];

    /**
     * Minimum interval between logs in seconds.
     *
     * @since 1.0.0
     * @var float
     */
    private const MIN_LOG_INTERVAL = 0.02;

    /**
     * Plugin settings.
     *
     * @since 1.0.0
     * @var array{log_retention_days: int, log_blocked_requests: bool}
     */
    private $settings;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->logger = new Logger();
        $this->load_blocked_items();
        $this->settings = [
            'log_retention_days' => (int) get_option('upblock_log_retention_days', 30),
            'log_blocked_requests' => (bool) get_option('upblock_enable_logging', false),
            'enable_auto_cleanup' => (bool) get_option('upblock_enable_auto_cleanup', true)
        ];
        $this->init();
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     * @return void
     */
    private function init() {
        add_filter('pre_http_request', [$this, 'maybe_block_request'], 10, 3);
        add_action('http_api_debug', [$this, 'handle_http_request'], 10, 5);
    }

    /**
     * Load blocked domains and URLs from options.
     *
     * @since 1.0.0
     * @return void
     */
    private function load_blocked_items() {
        $this->blocked_domains = get_option('upblock_blocked_domains', []);
        $this->blocked_urls = get_option('upblock_blocked_urls', []);
    }

    /**
     * Check if domain is whitelisted.
     *
     * @since 1.0.0
     * @param string $domain Domain name to check.
     * @return bool True if domain is whitelisted.
     */
    private function is_whitelisted($domain) {
        return Whitelist::is_whitelisted($domain);
    }

    /**
     * Write log entry with rate limiting.
     *
     * @since 1.0.0
     * @param array $data Log data.
     * @return bool|int False on failure or if skipped, number of rows affected on success.
     */
    private function write_log($data) {
        if ($data['blocked'] && !$this->settings['log_blocked_requests']) {
            return false;
        }

        $url = $data['url'];
        $current_time = microtime(true);

        if (isset(self::$last_log_times[$url])) {
            $time_diff = $current_time - self::$last_log_times[$url];
            if ($time_diff < self::MIN_LOG_INTERVAL) {
                return false;
            }
        }

        self::$last_log_times[$url] = $current_time;

        return $this->logger->log_request($data);
    }

    /**
     * Check if request should be blocked.
     *
     * @since 1.0.0
     * @param mixed         $pre  Whether to preempt an HTTP request's return value. Default false.
     * @param array         $args HTTP request arguments.
     * @param string        $url  The request URL.
     * @return mixed|WP_Error The pre-filtered value or WP_Error if request is blocked.
     */
    public function maybe_block_request($pre, $args, $url) {
        if (!is_admin()) {
            return $pre;
        }

        $domain = parse_url($url, PHP_URL_HOST);
        $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        if ($domain === $site_domain || $this->is_whitelisted($domain)) {
            return $pre;
        }

        $this->start_times[$url] = microtime(true);

        $is_blocked = false;
        $block_reason = '';

        if (in_array($domain, $this->blocked_domains, true)) {
            $is_blocked = true;
            $block_reason = sprintf(
                /* translators: %s: blocked domain name */
                __('Request blocked: Domain %s is in the blocklist', 'upblock'),
                $domain
            );
        }

        if (!$is_blocked) {
            foreach ($this->blocked_urls as $blocked_url) {
                if (strpos($url, $blocked_url) !== false) {
                    $is_blocked = true;
                    $block_reason = sprintf(
                        /* translators: %s: blocked URL pattern */
                        __('Request blocked: URL matches pattern %s', 'upblock'),
                        $blocked_url
                    );
                    break;
                }
            }
        }

        if ($is_blocked) {
            self::$blocked_requests[$url] = true;

            $this->write_log([
                'url' => $url,
                'domain' => $domain,
                'args' => $args,
                'timestamp' => current_time('mysql'),
                'blocked' => true,
                'response_time' => 0
            ]);

            return [
                'headers'  => [],
                'body'     => '',
                'response' => [
                    'code'    => 200,
                    'message' => 'OK'
                ],
                'cookies'  => [],
                'filename' => null
            ];
        }

        return $pre;
    }

    /**
     * Log HTTP request after completion.
     *
     * @since 1.0.0
     * @param array|\WP_Error $response HTTP response or WP_Error object.
     * @param string          $context  Context under which the hook is fired.
     * @param string          $class    HTTP transport used.
     * @param array           $args     HTTP request arguments.
     * @param string          $url      The request URL.
     * @return void
     */
    public function handle_http_request($response, $context, $class, $args, $url) {
        if (!is_admin() || $context !== 'response' || $response instanceof \WP_Error) {
            return;
        }

        if (isset(self::$blocked_requests[$url])) {
            return;
        }

        $domain = parse_url($url, PHP_URL_HOST);
        $site_domain = parse_url(get_site_url(), PHP_URL_HOST);
        
        if ($domain === $site_domain || $this->is_whitelisted($domain)) {
            return;
        }

        $response_time = 0;
        if (isset($this->start_times[$url])) {
            $response_time = (microtime(true) - $this->start_times[$url]) * 1000;
            unset($this->start_times[$url]);
        }

        $this->write_log([
            'url' => $url,
            'domain' => $domain,
            'args' => $args,
            'timestamp' => current_time('mysql'),
            'blocked' => false,
            'response_time' => $response_time
        ]);
    }

    /**
     * Add a domain to the blocklist.
     *
     * @since 1.0.0
     * @param string $domain Domain name to block.
     * @return bool True if domain was added, false if already exists.
     */
    public function add_blocked_domain($domain) {
        if (!in_array($domain, $this->blocked_domains, true)) {
            $this->blocked_domains[] = $domain;
            return update_option('upblock_blocked_domains', $this->blocked_domains);
        }
        return false;
    }

    /**
     * Remove a domain from the blocklist.
     *
     * @since 1.0.0
     * @param string $domain Domain name to unblock.
     * @return bool True if option was updated, false otherwise.
     */
    public function remove_blocked_domain($domain) {
        $this->blocked_domains = array_diff($this->blocked_domains, [$domain]);
        return update_option('upblock_blocked_domains', array_values($this->blocked_domains));
    }

    /**
     * Add a URL pattern to the blocklist.
     *
     * @since 1.0.0
     * @param string $url URL pattern to block.
     * @return bool True if URL was added, false if already exists.
     */
    public function add_blocked_url($url) {
        if (!in_array($url, $this->blocked_urls, true)) {
            $this->blocked_urls[] = $url;
            return update_option('upblock_blocked_urls', $this->blocked_urls);
        }
        return false;
    }

    /**
     * Remove a URL pattern from the blocklist.
     *
     * @since 1.0.0
     * @param string $url URL pattern to unblock.
     * @return bool True if option was updated, false otherwise.
     */
    public function remove_blocked_url($url) {
        $this->blocked_urls = array_diff($this->blocked_urls, [$url]);
        return update_option('upblock_blocked_urls', array_values($this->blocked_urls));
    }

    /**
     * Get all blocked domains.
     *
     * @since 1.0.0
     * @return array<string> Array of blocked domain names.
     */
    public function get_blocked_domains() {
        return $this->blocked_domains;
    }

    /**
     * Get all blocked URL patterns.
     *
     * @since 1.0.0
     * @return array<string> Array of blocked URL patterns.
     */
    public function get_blocked_urls() {
        return $this->blocked_urls;
    }
} 