<?php
/**
 * Blocked domains component
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
        <h2><?php esc_html_e('Blocked Domains', 'upblock'); ?></h2>
        <button type="button" class="upblock-button upblock-button-primary" id="add-domain-btn">
            <?php esc_html_e('Add Domain', 'upblock'); ?>
        </button>
    </div>
    <div class="upblock-card-content">
        <div class="upblock-domains-list">
            <?php if (empty($blocked_domains)): ?>
                <div class="upblock-domain-item">
                    <span style="color: #71717a;"><?php esc_html_e('No blocked domains.', 'upblock'); ?></span>
                </div>
            <?php else: ?>
                <?php foreach ($blocked_domains as $domain): ?>
                    <div class="upblock-domain-item">
                        <span class="upblock-domain-text"><?php echo esc_html($domain); ?></span>
                        <button type="button" class="upblock-button upblock-button-icon upblock-remove-domain" data-domain="<?php echo esc_attr($domain); ?>">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 