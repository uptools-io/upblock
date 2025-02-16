<?php
/**
 * Recent logs component
 *
 * @package UpBlock
 * @subpackage Admin\Views\Components
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="upblock-card upblock-card-full">
    <div class="upblock-card-header">
        <h2><?php esc_html_e('Recent HTTP Requests', 'upblock'); ?></h2>
        <button type="button" class="upblock-button upblock-button-danger" id="clear-logs-btn">
            <?php esc_html_e('Clear Logs', 'upblock'); ?>
        </button>
    </div>
    <div class="upblock-card-content">
        <div class="upblock-table-container">
            <table class="upblock-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Time', 'upblock'); ?></th>
                        <th><?php esc_html_e('Domain', 'upblock'); ?></th>
                        <th><?php esc_html_e('URL', 'upblock'); ?></th>
                        <th><?php esc_html_e('Status', 'upblock'); ?></th>
                        <th><?php esc_html_e('Response Time', 'upblock'); ?></th>
                        <th><?php esc_html_e('Actions', 'upblock'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs['items'])): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                <span style="color: #71717a;"><?php esc_html_e('No HTTP requests recorded.', 'upblock'); ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs['items'] as $log): 
                            $is_domain_blocked = in_array($log->domain, $blocked_domains, true);
                            $is_url_blocked = is_url_blocked($log->url, $blocked_urls);
                        ?>
                            <tr>
                                <td><?php echo esc_html(
                                    wp_date(
                                        get_option('date_format') . ' ' . get_option('time_format'),
                                        strtotime($log->timestamp)
                                    )
                                ); ?></td>
                                <td title="<?php echo esc_attr($log->domain); ?>"><?php echo esc_html($log->domain); ?></td>
                                <td class="upblock-url-cell" title="<?php echo esc_attr($log->url); ?>">
                                    <span class="upblock-url-text"><?php echo esc_html($log->url); ?></span>
                                </td>
                                <td>
                                    <span class="upblock-status-badge <?php echo $log->blocked ? 'upblock-status-blocked' : 'upblock-status-allowed'; ?>">
                                        <?php echo $log->blocked ? esc_html__('Blocked', 'upblock') : esc_html__('Allowed', 'upblock'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log->blocked): ?>
                                        <span class="upblock-muted">-</span>
                                    <?php else: ?>
                                        <span class="upblock-response-time"><?php echo esc_html($log->response_time); ?> ms</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$log->blocked): ?>
                                        <button type="button" class="upblock-button upblock-button-small upblock-block-domain" 
                                                data-domain="<?php echo esc_attr($log->domain); ?>"
                                                <?php echo $is_domain_blocked ? 'disabled' : ''; ?>>
                                            <?php esc_html_e('Block Domain', 'upblock'); ?>
                                        </button>
                                        <button type="button" class="upblock-button upblock-button-small upblock-block-url" 
                                                data-url="<?php echo esc_attr($log->url); ?>"
                                                <?php echo $is_url_blocked ? 'disabled' : ''; ?>>
                                            <?php esc_html_e('Block URL', 'upblock'); ?>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($logs['pages'] > 1): ?>
            <div class="upblock-pagination">
                <?php
                $current_page = $logs['current_page'];
                $total_pages = $logs['pages'];
                $range = 2; // How many pages to show on each side of current page
                
                // Previous page
                if ($current_page > 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('page_num', $current_page - 1)); ?>" class="upblock-button">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </a>
                <?php endif;

                // First page
                if ($current_page > $range + 1): ?>
                    <a href="<?php echo esc_url(add_query_arg('page_num', 1)); ?>" class="upblock-button">1</a>
                    <?php if ($current_page > $range + 2): ?>
                        <span class="upblock-pagination-dots">...</span>
                    <?php endif;
                endif;

                // Page numbers
                for ($i = max(1, $current_page - $range); $i <= min($total_pages, $current_page + $range); $i++): 
                    $class = $i === $current_page ? 'upblock-button upblock-button-current' : 'upblock-button';
                ?>
                    <a href="<?php echo esc_url(add_query_arg('page_num', $i)); ?>" class="<?php echo $class; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor;

                // Last page
                if ($current_page < $total_pages - $range): 
                    if ($current_page < $total_pages - $range - 1): ?>
                        <span class="upblock-pagination-dots">...</span>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(add_query_arg('page_num', $total_pages)); ?>" class="upblock-button">
                        <?php echo $total_pages; ?>
                    </a>
                <?php endif;

                // Next page
                if ($current_page < $total_pages): ?>
                    <a href="<?php echo esc_url(add_query_arg('page_num', $current_page + 1)); ?>" class="upblock-button">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div> 