<?php
/**
 * Top domains component
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
        <h2><?php esc_html_e('Top Domains (Last 7 Days)', 'upblock'); ?></h2>
    </div>
    <div class="upblock-card-content">
        <div class="upblock-top-domains">
            <?php if (empty($top_domains)): ?>
                <div class="upblock-top-domain-item">
                    <span style="color: #71717a;"><?php esc_html_e('No domains recorded in the last 7 days.', 'upblock'); ?></span>
                </div>
            <?php else: ?>
                <?php foreach ($top_domains as $domain): ?>
                    <div class="upblock-top-domain-item">
                        <span class="upblock-domain-name"><?php echo esc_html($domain->domain); ?></span>
                        <span class="upblock-domain-count"><?php echo esc_html($domain->count); ?></span>
                        <button type="button" class="upblock-button upblock-button-small upblock-block-domain" data-domain="<?php echo esc_attr($domain->domain); ?>">
                            <?php esc_html_e('Block', 'upblock'); ?>
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div> 