<?php
/**
 * Main admin page template
 *
 * @package UpBlock
 * @subpackage Admin\Views
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$logs = $this->logger->get_logs([
    'per_page' => 10,
    'page' => isset($_GET['page_num']) ? absint($_GET['page_num']) : 1
]);

$top_domains = $this->logger->get_top_domains(5);
$blocked_domains = $this->blocker->get_blocked_domains();
$blocked_urls = $this->blocker->get_blocked_urls();

// Helper function to check if URL matches any blocked pattern
function is_url_blocked($url, $blocked_urls) {
    foreach ($blocked_urls as $blocked_url) {
        if (strpos($url, $blocked_url) !== false) {
            return true;
        }
    }
    return false;
}
?>

<div class="wrap upblock-admin">
    <?php 
    // Load header component
    require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/header.php';
    ?>

    <div class="upblock-grid">
        <?php
        // Load blocked domains component
        require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/blocked-domains.php';

        // Load blocked URLs component
        require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/blocked-urls.php';

        // Load top domains component
        require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/top-domains.php';

        // Load recent logs component
        require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/recent-logs.php';
        ?>
    </div>

    <?php
    // Load info box component
    require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/info-box.php';

    // Load modals component
    require_once UPBLOCK_PLUGIN_DIR . 'includes/admin/views/components/modals.php';
    ?>
</div> 