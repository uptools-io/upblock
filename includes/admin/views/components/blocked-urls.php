<?php
/**
 * Blocked URLs component
 *
 * @package UpBlock
 * @subpackage Admin\Views\Components
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="upblock-card">
    <div class="upblock-card-header">
        <h2><?php esc_html_e('Blocked URLs', 'upblock'); ?></h2>
        <button type="button" class="upblock-button upblock-button-primary" id="add-url-btn">
            <?php esc_html_e('Add URL', 'upblock'); ?>
        </button>
    </div>
    <div class="upblock-card-content">
        <div class="upblock-urls-list">
            <?php if (empty($blocked_urls)): ?>
                <div class="upblock-url-item">
                    <span style="color: #71717a;"><?php esc_html_e('No blocked URLs.', 'upblock'); ?></span>
                </div>
            <?php else: ?>
                <?php foreach ($blocked_urls as $url): ?>
                    <div class="upblock-url-item">
                        <span class="upblock-url-text"><?php echo esc_html($url); ?></span>
                        <button type="button" class="upblock-button upblock-button-icon upblock-remove-url" data-url="<?php echo esc_attr($url); ?>">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 