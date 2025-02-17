<?php
/**
 * Plugin Name: upBlock
 * Plugin URI: https://uptools.io/plugins/upblock
 * Description: Monitor, log and block unwanted HTTP API calls in WordPress admin to improve performance.
 * Version: 1.0.1
 * Author: upTools
 * Author URI: https://uptools.io
 * Text Domain: upblock
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 8.0
 *
 * @package UpBlock
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('UPBLOCK_VERSION', '1.0.1');
define('UPBLOCK_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('UPBLOCK_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoloader for classes
spl_autoload_register(function ($class) {
    $prefix = 'UpBlock\\';
    $base_dir = UPBLOCK_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    
    // Convert namespace separators to directory separators and make lowercase
    $file = strtolower(str_replace('\\', '/', $relative_class));
    
    // Get the last part of the class name (after the last backslash)
    $class_name = substr($file, strrpos($file, '/') + 1);
    
    // Convert class name to file name (e.g., Ajax_Handler -> class-ajax-handler.php)
    $file_name = 'class-' . str_replace('_', '-', $class_name) . '.php';
    
    // Build the full path
    $file = $base_dir . substr($file, 0, strrpos($file, '/') + 1) . $file_name;

    if (file_exists($file)) {
        require $file;
    }
});

/**
 * Main plugin class
 *
 * @since 1.0.0
 */
class UpBlock {
    /**
     * Instance of this class
     *
     * @since 1.0.0
     * @var self|null
     */
    private static $instance = null;

    /**
     * Logger instance
     *
     * @since 1.0.0
     * @var \UpBlock\Core\Logger
     */
    private $logger;

    /**
     * Blocker instance
     *
     * @since 1.0.0
     * @var \UpBlock\Core\Blocker
     */
    private $blocker;

    /**
     * Ajax handler instance
     *
     * @since 1.0.0
     * @var \UpBlock\Core\Ajax_Handler
     */
    private $ajax_handler;

    /**
     * Get singleton instance
     *
     * @since 1.0.0
     * @return self Instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * Singleton via the `new` operator from outside this class.
     *
     * @since 1.0.0
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize plugin components and hooks
     *
     * @since 1.0.0
     * @return void
     */
    public function init() {
        // Load text domain
        add_action('plugins_loaded', [$this, 'load_textdomain']);

        // Initialize components
        add_action('init', [$this, 'init_components']);

        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'add_admin_menu']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
            
            // Load admin AJAX handlers
            require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/ajax/loader.php';
            
            // Remove unwanted admin notices
            add_action('admin_init', [$this, 'remove_unwanted_notices']);
            
            // Block specific hooks
            $this->block_unwanted_hooks();
        }
    }

    /**
     * Load plugin text domain for translations
     *
     * @since 1.0.0
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'upblock',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Initialize core components of the plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function init_components() {
        $this->logger = new \UpBlock\Core\Logger();
        $this->blocker = new \UpBlock\Core\Blocker();
        $this->ajax_handler = new \UpBlock\Core\Ajax_Handler($this->blocker, $this->logger);

        // Schedule log cleanup
        add_action('upblock_cleanup_logs', [$this->logger, 'cleanup_logs']);
        \UpBlock\Core\Logger::schedule_cleanup();
    }

    /**
     * Remove unwanted admin notices and callbacks
     *
     * @since 1.0.0
     * @return void
     */
    public function remove_unwanted_notices() {
        // Remove all admin notices on our plugin page
        if (isset($_GET['page']) && $_GET['page'] === 'upblock') {
            remove_all_actions('admin_notices');
            remove_all_actions('all_admin_notices');
            
            // Add back only our notices
            add_action('admin_notices', [$this, 'show_our_notices']);
        }
    }

    /**
     * Block specific unwanted hooks
     *
     * @since 1.0.0
     * @return void
     */
    private function block_unwanted_hooks() {
        // Block 'upgrader_pre_download' hook
        remove_all_filters('upgrader_pre_download');
        add_filter('upgrader_pre_download', '__return_false', 0);

        // Block 'site_transient_update_plugins' hook
        remove_all_filters('site_transient_update_plugins');
        
        // Block 'pre_set_site_transient_update_themes' hook
        remove_all_filters('pre_set_site_transient_update_themes');
        
        // Disable WP_Helper_Updater callbacks
        if (class_exists('WC_Helper_Updater')) {
            remove_action('admin_init', ['WC_Helper_Updater', 'block_expired_updates'], 10);
            remove_action('init', ['WC_Helper_Updater', 'transient_update_themes'], 21);
        }
    }

    /**
     * Show only our plugin's notices
     *
     * @since 1.0.0
     * @return void
     */
    public function show_our_notices() {
        // Add any plugin-specific notices here
        if (isset($_GET['upblock-notice']) && $_GET['upblock-notice'] === 'settings-updated') {
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Settings updated successfully.', 'upblock'); ?></p>
            </div>
            <?php
        }
    }

    /**
     * Add admin menu item
     *
     * @since 1.0.0
     * @return void
     */
    public function add_admin_menu() {
        global $menu;
        
        // Check if upTools menu exists
        $uptools_exists = false;
        $uptools_position = 100;
        
        foreach ($menu as $item) {
            if (isset($item[2]) && $item[2] === 'uptools') {
                $uptools_exists = true;
                break;
            }
        }
        
        // Create upTools menu if it doesn't exist
        if (!$uptools_exists) {
            add_menu_page(
                __('upTools', 'upblock'),
                __('upTools', 'upblock'),
                'manage_options',
                'uptools',
                '',
                'dashicons-screenoptions',
                $uptools_position
            );
        }
        
        // Add upBlock as submenu
        add_submenu_page(
            'uptools',
            __('upBlock', 'upblock'),
            __('upBlock', 'upblock'),
            'manage_options',
            'upblock',
            [$this, 'render_admin_page']
        );
        
        // Remove duplicate submenu item if we created the parent menu
        if (!$uptools_exists) {
            remove_submenu_page('uptools', 'uptools');
        }
    }

    /**
     * Enqueue admin assets
     *
     * @since 1.0.0
     * @param string $hook The current admin page hook.
     * @return void
     */
    public function enqueue_admin_assets($hook) {
        if (!isset($_GET['page']) || $_GET['page'] !== 'upblock') {
            return;
        }

        // Use minified versions in production
        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style(
            'upblock-admin',
            UPBLOCK_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css',
            [],
            UPBLOCK_VERSION
        );

        wp_enqueue_script(
            'upblock-admin',
            UPBLOCK_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js',
            ['jquery'],
            UPBLOCK_VERSION,
            true
        );

        wp_localize_script('upblock-admin', 'upblockAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('upblock_nonce'),
            'i18n' => [
                'error' => __('Error', 'upblock'),
                'confirm' => __('Confirm', 'upblock'),
                'enterDomain' => __('Please enter a domain', 'upblock'),
                'enterUrl' => __('Please enter a URL pattern', 'upblock'),
                'errorOccurred' => __('An error occurred while saving settings.', 'upblock'),
                'confirmRemoveDomain' => __('Are you sure you want to remove this domain?', 'upblock'),
                'confirmRemoveUrl' => __('Are you sure you want to remove this URL pattern?', 'upblock'),
                'confirmBlockDomain' => __('Are you sure you want to block this domain?', 'upblock'),
                'confirmBlockUrl' => __('Are you sure you want to block this URL pattern?', 'upblock'),
                'confirmClearLogs' => __('Are you sure you want to clear all logs? This action cannot be undone.', 'upblock')
            ]
        ]);
    }

    /**
     * Render admin page
     *
     * @since 1.0.0
     * @return void
     */
    public function render_admin_page() {
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'main';
        
        if ($tab === 'settings') {
            require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/settings.php';
        } else {
            require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/main.php';
        }
    }

    /**
     * Deactivate plugin
     *
     * @since 1.0.0
     * @return void
     */
    public function deactivate() {
        Logger::unschedule_cleanup();
    }
}

// Initialize the plugin
function upblock_init() {
    return UpBlock::get_instance();
}

// Start the plugin
add_action('plugins_loaded', 'upblock_init');

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'upblock_activate');
register_deactivation_hook(__FILE__, 'upblock_deactivate');

/**
 * Initialize plugin on activation
 *
 * @since 1.0.0
 * @return void
 */
function upblock_activate() {
    // Initialize logger and create table
    \UpBlock\Core\Logger::init();
}

/**
 * Clean up on plugin deactivation
 *
 * @since 1.0.0
 * @return void
 */
function upblock_deactivate() {
    global $wpdb;
    
    // Delete options
    delete_option('upblock_blocked_domains');
    delete_option('upblock_blocked_urls');
    
    // Drop custom table
    $table_name = $wpdb->prefix . 'upblock_request_logs';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");
    
    // Clear scheduled events
    \UpBlock\Core\Logger::unschedule_cleanup();
} 