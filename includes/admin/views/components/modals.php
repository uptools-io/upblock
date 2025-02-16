<?php
/**
 * Modal components
 *
 * @package UpBlock
 * @subpackage Admin\Views\Components
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Add Domain Modal -->
<div class="upblock-modal" id="add-domain-modal">
    <div class="upblock-modal-content">
        <div class="upblock-modal-header">
            <h3><?php esc_html_e('Add Domain to Blocklist', 'upblock'); ?></h3>
            <button type="button" class="upblock-modal-close">×</button>
        </div>
        <div class="upblock-modal-body">
            <div class="upblock-form-group">
                <label for="new-domain"><?php esc_html_e('Domain Name', 'upblock'); ?></label>
                <input type="text" id="new-domain" class="upblock-input" placeholder="example.com">
            </div>
        </div>
        <div class="upblock-modal-footer">
            <button type="button" class="upblock-button" id="cancel-add-domain"><?php esc_html_e('Cancel', 'upblock'); ?></button>
            <button type="button" class="upblock-button upblock-button-primary" id="save-domain"><?php esc_html_e('Add Domain', 'upblock'); ?></button>
        </div>
    </div>
</div>

<!-- Add URL Modal -->
<div class="upblock-modal" id="add-url-modal">
    <div class="upblock-modal-content">
        <div class="upblock-modal-header">
            <h3><?php esc_html_e('Add URL Pattern to Blocklist', 'upblock'); ?></h3>
            <button type="button" class="upblock-modal-close">×</button>
        </div>
        <div class="upblock-modal-body">
            <div class="upblock-form-group">
                <label for="new-url"><?php esc_html_e('URL Pattern', 'upblock'); ?></label>
                <input type="text" id="new-url" class="upblock-input" placeholder="https://example.com/api/">
                <p class="upblock-help-text"><?php esc_html_e('Any URL containing this pattern will be blocked.', 'upblock'); ?></p>
            </div>
        </div>
        <div class="upblock-modal-footer">
            <button type="button" class="upblock-button" id="cancel-add-url"><?php esc_html_e('Cancel', 'upblock'); ?></button>
            <button type="button" class="upblock-button upblock-button-primary" id="save-url"><?php esc_html_e('Add URL', 'upblock'); ?></button>
        </div>
    </div>
</div>

<!-- Confirm Modal Template -->
<div class="upblock-modal" id="confirm-modal">
    <div class="upblock-modal-content">
        <div class="upblock-modal-header">
            <h3 id="confirm-title"></h3>
            <button type="button" class="upblock-modal-close">×</button>
        </div>
        <div class="upblock-modal-body">
            <p id="confirm-message"></p>
        </div>
        <div class="upblock-modal-footer">
            <button type="button" class="upblock-button" id="confirm-cancel"><?php esc_html_e('Cancel', 'upblock'); ?></button>
            <button type="button" class="upblock-button upblock-button-primary" id="confirm-ok"><?php esc_html_e('Confirm', 'upblock'); ?></button>
        </div>
    </div>
</div>

<!-- Alert Modal Template -->
<div class="upblock-modal" id="alert-modal">
    <div class="upblock-modal-content">
        <div class="upblock-modal-header">
            <h3 id="alert-title"></h3>
            <button type="button" class="upblock-modal-close">×</button>
        </div>
        <div class="upblock-modal-body">
            <p id="alert-message"></p>
        </div>
        <div class="upblock-modal-footer">
            <button type="button" class="upblock-button upblock-button-primary" id="alert-ok"><?php esc_html_e('OK', 'upblock'); ?></button>
        </div>
    </div>
</div> 