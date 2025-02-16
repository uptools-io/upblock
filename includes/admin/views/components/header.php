<?php
/**
 * Admin header component
 *
 * @package UpBlock
 * @subpackage Admin\Views\Components
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="upblock-header">
    <div class="upblock-header-content">
        <div class="upblock-header-top">
            <div class="upblock-branding">
                <div class="upblock-logo">
                    <span class="dashicons dashicons-shield"></span>
                    <span class="upblock-title">upBlock</span>
                </div>
                <span class="upblock-version">v<?php echo UPBLOCK_VERSION; ?></span>
            </div>
            <div class="upblock-header-actions">
                <div class="upblock-nav-menu">
                    <a href="<?php echo esc_url(remove_query_arg('tab')); ?>" class="upblock-nav-item<?php echo !isset($_GET['tab']) ? ' active' : ''; ?>">
                        <span class="dashicons dashicons-chart-bar"></span>
                        <?php esc_html_e('Overview', 'upblock'); ?>
                    </a>
                    <a href="<?php echo esc_url(add_query_arg('tab', 'settings')); ?>" class="upblock-nav-item<?php echo isset($_GET['tab']) && $_GET['tab'] === 'settings' ? ' active' : ''; ?>">
                        <span class="dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e('Settings', 'upblock'); ?>
                    </a>
                </div>
            </div>
        </div>
        <p class="upblock-description">
            <?php 
            if (isset($_GET['tab']) && $_GET['tab'] === 'settings') {
                esc_html_e('Configure plugin settings and logging preferences.', 'upblock');
            } else {
                esc_html_e('Monitor, log and block unwanted HTTP API calls to improve WordPress performance.', 'upblock');
            }
            ?>
        </p>
    </div>
</div> 