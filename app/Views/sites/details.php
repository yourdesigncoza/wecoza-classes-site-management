<?php
/**
 * Site Details View Template
 *
 * @package WeCozaSiteManagement\Views
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$site = isset($site) ? $site : null;
$client = isset($client) ? $client : null;
$show_edit_link = isset($show_edit_link) ? $show_edit_link : false;
$show_delete_link = isset($show_delete_link) ? $show_delete_link : false;
$can_edit = isset($can_edit) ? $can_edit : false;
$can_delete = isset($can_delete) ? $can_delete : false;

if (!$site) {
    echo '<div class="alert alert-subtle-danger">' . 'Site data not available.' . '</div>';
    return;
}
?>

<div class="wecoza-site-details-container">
    <!-- Header -->
    <div class="site-details-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3 class="details-title mb-0">
                    <i class="fas fa-building text-primary"></i>
                    <?php echo esc_html($site->getSiteName()); ?>
                    <small class="text-muted">#<?php echo esc_html($site->getSiteId()); ?></small>
                </h3>
                <?php if ($client): ?>
                    <p class="text-muted mb-0">
                        <i class="fas fa-user"></i>
                        <?php printf('Client: %s', esc_html($client->getClientName())); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <!--
                <div class="btn-group">
                    <a href="<?php echo esc_url(remove_query_arg(['action', 'site_id'])); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    <?php if ($show_edit_link && $can_edit): ?>
                        <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'site_id' => $site->getSiteId()])); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                    <?php if ($show_delete_link && $can_delete): ?>
                        <button type="button" 
                                class="btn btn-danger btn-delete-site" 
                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                data-site-name="<?php echo esc_attr($site->getSiteName()); ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    <?php endif; ?>
                </div>
                -->
                    <?php if ($show_delete_link && $can_delete): ?>
                        <button type="button" 
                                class="btn btn-danger btn-delete-site" 
                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                data-site-name="<?php echo esc_attr($site->getSiteName()); ?>">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    <?php endif; ?>


            </div>
        </div>
    </div>

    <!-- Site Information Cards -->
    <div class="row">
        <!-- Basic Information -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i>
                        Site Information
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Site ID:</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-secondary"><?php echo esc_html($site->getSiteId()); ?></span>
                        </dd>

                        <dt class="col-sm-3">Site Name:</dt>
                        <dd class="col-sm-9">
                            <strong><?php echo esc_html($site->getSiteName()); ?></strong>
                        </dd>

                        <dt class="col-sm-3">Client:</dt>
                        <dd class="col-sm-9">
                            <?php if ($client): ?>
                                <span class="badge badge-phoenix badge-phoenix-primary">
                                    <?php echo esc_html($client->getClientName()); ?>
                                </span>
                                <small class="text-muted ms-2">ID: <?php echo esc_html($site->getClientId()); ?></small>
                            <?php else: ?>
                                <span class="text-muted">
                                    Unknown Client
                                    <small>(ID: <?php echo esc_html($site->getClientId()); ?>)</small>
                                </span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-3">Address:</dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($site->getAddress())): ?>
                                <div class="site-address">
                                    <?php echo nl2br(esc_html($site->getAddress())); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted">No address provided</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Metadata -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock"></i>
                        Timestamps
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-12">Created:</dt>
                        <dd class="col-12 mb-3">
                            <?php if ($site->getCreatedAt()): ?>
                                <div>
                                    <i class="fas fa-calendar-plus text-success"></i>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($site->getCreatedAt()))); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($site->getCreatedAt()))); ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Unknown</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-12">Last Updated:</dt>
                        <dd class="col-12">
                            <?php if ($site->getUpdatedAt()): ?>
                                <div>
                                    <i class="fas fa-calendar-edit text-warning"></i>
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($site->getUpdatedAt()))); ?>
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($site->getUpdatedAt()))); ?>
                                </small>
                                <?php
                                $time_diff = human_time_diff(strtotime($site->getUpdatedAt()), current_time('timestamp'));
                                ?>
                                <br>
                                <small class="text-muted">
                                    <?php printf('%s ago', $time_diff); ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted">Never updated</span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Delete confirmation functionality - Single confirmation pattern
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete-site');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const siteId = this.getAttribute('data-site-id');
            const siteName = this.getAttribute('data-site-name');

            // Single confirmation dialog
            const confirmMessage = `<?php echo esc_js('Are you sure you want to delete this site?'); ?>\n\n<?php echo esc_js('Site:'); ?> ${siteName}\n\n<?php echo esc_js('Warning: This action cannot be undone. All data associated with this site will be permanently removed.'); ?>`;

            if (confirm(confirmMessage)) {
                deleteSite(siteId, this);
            }
        });
    });

    /**
     * Delete a site via AJAX
     */
    function deleteSite(siteId, button) {
        if (!siteId || typeof wecoza_site_management_ajax === 'undefined') {
            showError('Unable to delete site. Missing required data.');
            return;
        }

        // Store original button state
        const originalHTML = button.innerHTML;
        const originalDisabled = button.disabled;

        // Show loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';

        // Prepare AJAX data
        const formData = new FormData();
        formData.append('action', 'wecoza_delete_site');
        formData.append('nonce', wecoza_site_management_ajax.nonce);
        formData.append('site_id', siteId);

        // Perform AJAX delete
        fetch(wecoza_site_management_ajax.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                showSuccess('Site deleted successfully!');

                // Redirect to list page after a short delay
                setTimeout(function() {
                    window.location.href = '<?php echo esc_url(remove_query_arg(["action", "site_id"])); ?>';
                }, 1500);
            } else {
                // Show error message
                const errorMessage = data.data || 'Failed to delete site.';
                showError(errorMessage);

                // Restore button state
                button.disabled = originalDisabled;
                button.innerHTML = originalHTML;
            }
        })
        .catch(error => {
            console.error('Delete site error:', error);

            // Show error message
            showError('An error occurred while deleting the site. Please try again.');

            // Restore button state
            button.disabled = originalDisabled;
            button.innerHTML = originalHTML;
        });
    }

    /**
     * Show success message
     */
    function showSuccess(message) {
        showNotification(message, 'success');
    }

    /**
     * Show error message
     */
    function showError(message) {
        showNotification(message, 'danger');
    }

    /**
     * Show notification message
     */
    function showNotification(message, type) {
        // Remove any existing notifications
        const existingAlerts = document.querySelectorAll('.wecoza-notification-alert');
        existingAlerts.forEach(alert => alert.remove());

        // Create notification HTML
        const alertHTML = `
            <div class="alert alert-subtle-${type} alert-dismissible fade show wecoza-notification-alert" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i>
                ${escapeHtml(message)}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;

        // Insert at top of container
        const container = document.querySelector('.wecoza-site-details-container');
        if (container) {
            container.insertAdjacentHTML('afterbegin', alertHTML);

            // Auto-dismiss after 5 seconds for success messages
            if (type === 'success') {
                setTimeout(function() {
                    const alert = document.querySelector('.wecoza-notification-alert');
                    if (alert) {
                        alert.classList.remove('show');
                        setTimeout(() => alert.remove(), 150);
                    }
                }, 5000);
            }
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, function(m) { return map[m]; }) : '';
    }
});
</script>
