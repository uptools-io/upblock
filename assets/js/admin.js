/**
 * Admin JavaScript functionality
 *
 * @package UpBlock
 * @since 1.0.0
 */

'use strict';

jQuery(function($) {
    // Cache DOM elements
    const $settingsForm = $('#upblock-settings-form');
    const $logRetentionInput = $('#log_retention_days');
    const $notification = $('#upblock-notification');

    /**
     * Show notification message
     *
     * @param {string} message Message to display
     */
    const showNotification = (message) => {
        $notification.removeClass('upblock-notification-hide').show();
        $('.upblock-notification-message').text(message);
        
        setTimeout(() => {
            $notification.addClass('upblock-notification-hide');
            setTimeout(() => $notification.hide(), 300);
        }, 3000);
    };

    /**
     * Show alert modal
     *
     * @param {string} title Modal title
     * @param {string} message Modal message
     * @param {Function} callback Optional callback function
     */
    const showAlert = (title, message, callback) => {
        $('#alert-title').text(title);
        $('#alert-message').text(message);
        
        $('#alert-ok').off('click').on('click', () => {
            closeModal('alert-modal');
            if (callback) callback();
        });
        
        openModal('alert-modal');
    };

    /**
     * Show confirm modal
     *
     * @param {string} title Modal title
     * @param {string} message Modal message
     * @param {Function} callback Callback function
     */
    const showConfirm = (title, message, callback) => {
        $('#confirm-title').text(title);
        $('#confirm-message').text(message);
        
        $('#confirm-ok').off('click').on('click', () => {
            closeModal('confirm-modal');
            callback(true);
        });
        
        $('#confirm-cancel').off('click').on('click', () => {
            closeModal('confirm-modal');
            callback(false);
        });
        
        openModal('confirm-modal');
    };

    /**
     * Open modal
     *
     * @param {string} modalId Modal element ID
     */
    const openModal = (modalId) => {
        $('#' + modalId).addClass('is-active');
    };

    /**
     * Close modal
     *
     * @param {string} modalId Modal element ID
     */
    const closeModal = (modalId) => {
        $('#' + modalId).removeClass('is-active');
    };

    /**
     * Close all modals
     */
    const closeAllModals = () => {
        $('.upblock-modal').removeClass('is-active');
    };

    /**
     * Handle AJAX request
     *
     * @param {Object} options Request options
     */
    const handleAjaxRequest = (options) => {
        const defaults = {
            url: upblockAdmin.ajaxUrl,
            type: 'POST',
            data: {
                _wpnonce: upblockAdmin.nonce
            }
        };

        const settings = { ...defaults, ...options };

        // Merge data properly
        settings.data = { ...defaults.data, ...options.data };

        return $.ajax(settings)
            .fail((jqXHR) => {
                let errorMessage = upblockAdmin.i18n.errorOccurred;
                if (jqXHR.responseJSON && jqXHR.responseJSON.data && jqXHR.responseJSON.data.message) {
                    errorMessage = jqXHR.responseJSON.data.message;
                }
                showAlert(
                    upblockAdmin.i18n.error,
                    errorMessage
                );
            });
    };

    // Settings page handlers
    if ($settingsForm.length) {
        // Number input handlers
        $('.upblock-number-button.upblock-number-decrease').on('click', () => {
            const value = parseInt($logRetentionInput.val());
            if (value > 1) {
                $logRetentionInput.val(value - 1);
            }
        });

        $('.upblock-number-button.upblock-number-increase').on('click', () => {
            const value = parseInt($logRetentionInput.val());
            if (value < 365) {
                $logRetentionInput.val(value + 1);
            }
        });

        // Direct input validation
        $logRetentionInput.on('change', function() {
            let value = parseInt($(this).val());
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > 365) {
                value = 365;
            }
            $(this).val(value);
        });

        // Settings form submission
        $settingsForm.on('submit', function(e) {
            e.preventDefault();

            const settings = {
                log_retention_days: parseInt($('#log_retention_days').val(), 10),
                enable_logging: $('input[name="log_blocked_requests"]').is(':checked'),
                enable_auto_cleanup: $('input[name="enable_auto_cleanup"]').is(':checked')
            };

            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'upblock_save_settings',
                    _wpnonce: upblockAdmin.nonce,
                    settings: JSON.stringify(settings)
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    let errorMessage = upblockAdmin.i18n.errorOccurred;
                    
                    if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMessage = xhr.responseJSON.data.message;
                        console.error('Failed options:', xhr.responseJSON.data.failed_options);
                    }
                    
                    showNotification(errorMessage, 'error');
                }
            });
        });
    }

    // Main page handlers
    if ($('.upblock-domains-list').length) {
        // Clear logs
        $('#clear-logs-btn').on('click', () => {
            showConfirm(
                upblockAdmin.i18n.confirm,
                upblockAdmin.i18n.confirmClearLogs,
                (confirmed) => {
                    if (!confirmed) return;

                    handleAjaxRequest({
                        data: {
                            action: 'upblock_clear_logs'
                        },
                        success: (response) => {
                            if (response.success) {
                                // Clear all lists
                                $('.upblock-domains-list, .upblock-urls-list').each(function() {
                                    if ($(this).children().length === 0) {
                                        $(this).html(
                                            `<div class="${$(this).hasClass('upblock-domains-list') ? 'upblock-domain-item' : 'upblock-url-item'}">
                                                <span style="color: #71717a;">${$(this).hasClass('upblock-domains-list') ? 'No blocked domains.' : 'No blocked URLs.'}</span>
                                            </div>`
                                        );
                                    }
                                });

                                // Clear top domains
                                $('.upblock-top-domains').html(
                                    '<div class="upblock-top-domain-item">' +
                                    '<span style="color: #71717a;">No domains recorded in the last 7 days.</span>' +
                                    '</div>'
                                );

                                // Clear logs table
                                $('.upblock-table tbody').html(
                                    '<tr><td colspan="6" style="text-align: center; padding: 2rem;">' + 
                                    '<span style="color: #71717a;">No HTTP requests recorded.</span>' +
                                    '</td></tr>'
                                );

                                showNotification(response.data.message || 'Logs cleared successfully.');
                            } else {
                                showAlert(
                                    upblockAdmin.i18n.error,
                                    response.data.message
                                );
                            }
                        }
                    });
                }
            );
        });

        // Block domain from logs
        $('.upblock-block-domain').on('click', function() {
            const domain = $(this).data('domain');
            if (!domain) return;

            showConfirm(
                upblockAdmin.i18n.confirm,
                upblockAdmin.i18n.confirmBlockDomain,
                (confirmed) => {
                    if (!confirmed) return;

                    handleAjaxRequest({
                        data: {
                            action: 'upblock_block_domain',
                            domain: domain
                        },
                        success: (response) => {
                            if (response.success) {
                                location.reload();
                            } else {
                                showAlert(
                                    upblockAdmin.i18n.error,
                                    response.data.message
                                );
                            }
                        }
                    });
                }
            );
        });

        // Block URL from logs
        $('.upblock-block-url').on('click', function() {
            const url = $(this).data('url');
            if (!url) return;

            showConfirm(
                upblockAdmin.i18n.confirm,
                upblockAdmin.i18n.confirmBlockUrl,
                (confirmed) => {
                    if (!confirmed) return;

                    handleAjaxRequest({
                        data: {
                            action: 'upblock_block_url',
                            url: url
                        },
                        success: (response) => {
                            if (response.success) {
                                location.reload();
                            } else {
                                showAlert(
                                    upblockAdmin.i18n.error,
                                    response.data.message
                                );
                            }
                        }
                    });
                }
            );
        });
    }

    // Modal handlers
    $('#add-domain-btn, #add-url-btn').on('click', function() {
        openModal($(this).attr('id').replace('-btn', '-modal'));
    });

    $('.upblock-modal-close, #cancel-add-domain, #cancel-add-url').on('click', closeAllModals);

    $('.upblock-modal').on('click', function(e) {
        if (e.target === this) {
            closeAllModals();
        }
    });

    // Add domain
    $('#save-domain').on('click', () => {
        const domain = $('#new-domain').val().trim();
        if (!domain) {
            showAlert(
                upblockAdmin.i18n.error,
                upblockAdmin.i18n.enterDomain
            );
            return;
        }

        handleAjaxRequest({
            data: {
                action: 'upblock_add_domain',
                domain: domain
            },
            success: (response) => {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert(
                        upblockAdmin.i18n.error,
                        response.data.message
                    );
                }
            }
        });
    });

    // Add URL
    $('#save-url').on('click', () => {
        const url = $('#new-url').val().trim();
        if (!url) {
            showAlert(
                upblockAdmin.i18n.error,
                upblockAdmin.i18n.enterUrl
            );
            return;
        }

        handleAjaxRequest({
            data: {
                action: 'upblock_add_url',
                url: url
            },
            success: (response) => {
                if (response.success) {
                    location.reload();
                } else {
                    showAlert(
                        upblockAdmin.i18n.error,
                        response.data.message
                    );
                }
            }
        });
    });

    // Remove domain
    $('.upblock-remove-domain').on('click', function() {
        const domain = $(this).data('domain');
        showConfirm(
            upblockAdmin.i18n.confirm,
            upblockAdmin.i18n.confirmRemoveDomain,
            (confirmed) => {
                if (!confirmed) return;

                handleAjaxRequest({
                    data: {
                        action: 'upblock_remove_domain',
                        domain: domain
                    },
                    success: (response) => {
                        if (response.success) {
                            location.reload();
                        } else {
                            showAlert(
                                upblockAdmin.i18n.error,
                                response.data.message
                            );
                        }
                    }
                });
            }
        );
    });

    // Remove URL
    $('.upblock-remove-url').on('click', function() {
        const url = $(this).data('url');
        showConfirm(
            upblockAdmin.i18n.confirm,
            upblockAdmin.i18n.confirmRemoveUrl,
            (confirmed) => {
                if (!confirmed) return;

                handleAjaxRequest({
                    data: {
                        action: 'upblock_remove_url',
                        url: url
                    },
                    success: (response) => {
                        if (response.success) {
                            location.reload();
                        } else {
                            showAlert(
                                upblockAdmin.i18n.error,
                                response.data.message
                            );
                        }
                    }
                });
            }
        );
    });
}); 