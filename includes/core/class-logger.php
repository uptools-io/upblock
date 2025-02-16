<?php
/**
 * Logger functionality
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
 * Class Logger
 *
 * Handles logging of HTTP requests and provides methods for log management.
 *
 * @since 1.0.0
 * @package UpBlock\Core
 */
class Logger {
    /**
     * Database table name for storing logs.
     *
     * @since 1.0.0
     * @var string
     */
    const TABLE_NAME = 'upblock_request_logs';

    /**
     * Initialize logger and create required database table.
     *
     * @since 1.0.0
     * @return void
     */
    public static function init() {
        self::create_table();
    }

    /**
     * Create or update the logs table in the database.
     *
     * @since 1.0.0
     * @return void
     */
    private static function create_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(1) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table_name
            )
        );
        
        if ($table_exists) {
            return;
        }
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url text NOT NULL,
            domain varchar(255) NOT NULL,
            args longtext,
            timestamp datetime NOT NULL,
            blocked tinyint(1) NOT NULL DEFAULT 0,
            response_time float NULL,
            PRIMARY KEY  (id),
            KEY domain (domain),
            KEY timestamp (timestamp)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Log a request to the database.
     *
     * @since 1.0.0
     * @param array $data {
     *     Request data to log.
     *
     *     @type string  $url           The URL of the request.
     *     @type string  $domain        The domain of the request.
     *     @type array   $args          Optional. Request arguments.
     *     @type string  $timestamp     Request timestamp.
     *     @type bool    $blocked       Whether the request was blocked.
     *     @type float   $response_time Optional. Request response time.
     * }
     * @return bool|int False on failure, number of rows affected on success.
     */
    public function log_request($data) {
        global $wpdb;
        
        $enable_logging = (bool) get_option('upblock_enable_logging', false);

        if (!empty($data['blocked']) && !$enable_logging) {
            return true;
        }
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->insert(
            $table_name,
            [
                'url' => $data['url'],
                'domain' => $data['domain'],
                'args' => isset($data['args']) ? wp_json_encode($data['args']) : '',
                'timestamp' => $data['timestamp'],
                'blocked' => !empty($data['blocked']),
                'response_time' => isset($data['response_time']) ? $data['response_time'] : null
            ],
            [
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%f'
            ]
        );
    }

    /**
     * Get logs with filtering and pagination.
     *
     * @since 1.0.0
     * @param array $args {
     *     Optional. Arguments for filtering and pagination.
     *
     *     @type int    $per_page  Number of items per page. Default 20.
     *     @type int    $page      Current page number. Default 1.
     *     @type string $orderby   Column to sort by. Default 'timestamp'.
     *     @type string $order     Sort direction. Default 'DESC'.
     *     @type string $search    Search term. Default empty.
     *     @type string $domain    Filter by domain. Default empty.
     *     @type string $status    Filter by status. Default empty.
     *     @type string $date_from Start date filter. Default empty.
     *     @type string $date_to   End date filter. Default empty.
     * }
     * @return array {
     *     @type array $items        Array of log items.
     *     @type int   $total        Total number of items.
     *     @type int   $pages        Total number of pages.
     *     @type int   $current_page Current page number.
     *     @type int   $per_page     Items per page.
     * }
     */
    public function get_logs($args = []) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;

        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'orderby' => 'timestamp',
            'order' => 'DESC',
            'search' => '',
            'domain' => '',
            'status' => '',
            'date_from' => '',
            'date_to' => ''
        ];

        $args = wp_parse_args($args, $defaults);
        
        $where = [];
        $values = [];

        if (!empty($args['search'])) {
            $where[] = '(url LIKE %s OR domain LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $values[] = $search_term;
            $values[] = $search_term;
        }

        if (!empty($args['domain'])) {
            $where[] = 'domain = %s';
            $values[] = $args['domain'];
        }

        if ($args['status'] === 'blocked') {
            $where[] = 'blocked = 1';
        } elseif ($args['status'] === 'allowed') {
            $where[] = 'blocked = 0';
        }

        if (!empty($args['date_from'])) {
            $where[] = 'timestamp >= %s';
            $values[] = $args['date_from'] . ' 00:00:00';
        }
        if (!empty($args['date_to'])) {
            $where[] = 'timestamp <= %s';
            $values[] = $args['date_to'] . ' 23:59:59';
        }

        $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $offset = ($args['page'] - 1) * $args['per_page'];

        $count_query = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        $total = !empty($values) ? $wpdb->get_var($wpdb->prepare($count_query, $values)) : $wpdb->get_var($count_query);

        $orderby = in_array($args['orderby'], ['timestamp', 'domain', 'url'], true) ? $args['orderby'] : 'timestamp';
        $order = $args['order'] === 'ASC' ? 'ASC' : 'DESC';
        
        $query = "SELECT * FROM {$table_name} {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        
        $items = !empty($values) 
            ? $wpdb->get_results($wpdb->prepare($query, array_merge($values, [$args['per_page'], $offset])))
            : $wpdb->get_results($wpdb->prepare($query, [$args['per_page'], $offset]));

        return [
            'items' => $items,
            'total' => (int) $total,
            'pages' => ceil($total / $args['per_page']),
            'current_page' => $args['page'],
            'per_page' => $args['per_page']
        ];
    }

    /**
     * Get top domains by request count.
     *
     * @since 1.0.0
     * @param int $limit Maximum number of domains to return. Default 10.
     * @param int $days  Number of days to look back. Default 7.
     * @return array Array of objects with domain and count properties.
     */
    public function get_top_domains($limit = 10, $days = 7) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT domain, COUNT(*) as count
            FROM {$table_name}
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL %d DAY)
            GROUP BY domain
            ORDER BY count DESC
            LIMIT %d",
            $days,
            $limit
        ));
    }

    /**
     * Clear all logs from the database.
     *
     * @since 1.0.0
     * @return int|false Number of rows affected, or false on error.
     */
    public function clear_all_logs() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        return $wpdb->query("TRUNCATE TABLE {$table_name}");
    }

    /**
     * Clean up old logs based on retention settings.
     *
     * @since 1.0.0
     * @return int|false Number of rows deleted, or false on error.
     */
    public function cleanup_logs() {
        global $wpdb;
        
        $retention_days = (int) get_option('upblock_log_retention_days', 30);
        
        $table_name = $wpdb->prefix . self::TABLE_NAME;
        
        return $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table_name} WHERE timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)",
            $retention_days
        ));
    }

    /**
     * Schedule daily cleanup cron job.
     *
     * @since 1.0.0
     * @return void
     */
    public static function schedule_cleanup() {
        if (!wp_next_scheduled('upblock_cleanup_logs')) {
            wp_schedule_event(time(), 'daily', 'upblock_cleanup_logs');
        }
    }

    /**
     * Unschedule cleanup cron job.
     *
     * @since 1.0.0
     * @return void
     */
    public static function unschedule_cleanup() {
        wp_clear_scheduled_hook('upblock_cleanup_logs');
    }
} 