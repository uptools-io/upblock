<?php
/**
 * Info box component
 *
 * @package UpBlock
 * @subpackage Admin\Views\Components
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="upblock-info-box">
    <div class="upblock-info-box-header">
        <span class="dashicons dashicons-lightbulb"></span>
        <h2><?php esc_html_e('Why is WordPress Admin Sometimes Slow?', 'upblock'); ?></h2>
    </div>
    <div class="upblock-info-box-content">
        <p><?php esc_html_e('WordPress plugins and themes often load various external resources in your admin dashboard, including unwanted advertisements, news feeds, and third-party analytics. While some of these connections are necessary (like security and update checks), many are just:', 'upblock'); ?></p>
        
        <ul>
            <li>
                <span class="dashicons dashicons-clock"></span>
                <?php esc_html_e('Slowing down your admin dashboard with unnecessary background requests', 'upblock'); ?>
            </li>
            <li>
                <span class="dashicons dashicons-warning"></span>
                <?php esc_html_e('Potentially exposing your site to unwanted tracking and security risks', 'upblock'); ?>
            </li>
            <li>
                <span class="dashicons dashicons-chart-line"></span>
                <?php esc_html_e('Consuming server resources with unrequested external content', 'upblock'); ?>
            </li>
        </ul>

        <p><?php esc_html_e('upBlock helps you identify and control these unwanted requests while maintaining essential WordPress functionality like updates and security checks.', 'upblock'); ?></p>
        
        <div class="upblock-info-box-tip">
            <span class="dashicons dashicons-info"></span>
            <span><?php esc_html_e('Tip: Start by monitoring the "Top Domains" section to identify which services are making the most requests.', 'upblock'); ?></span>
        </div>
    </div>
</div> 