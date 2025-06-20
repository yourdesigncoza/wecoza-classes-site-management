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
    echo '<div class="alert alert-subtle-danger">' . __('Site data not available.', 'wecoza-site-management') . '</div>';
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
                        <?php printf(__('Client: %s', 'wecoza-site-management'), esc_html($client->getClientName())); ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-end">
                <div class="btn-group">
                    <a href="<?php echo esc_url(remove_query_arg(['action', 'site_id'])); ?>" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> <?php _e('Back to List', 'wecoza-site-management'); ?>
                    </a>
                    <?php if ($show_edit_link && $can_edit): ?>
                        <a href="<?php echo esc_url(add_query_arg(['action' => 'edit', 'site_id' => $site->getSiteId()])); ?>" 
                           class="btn btn-primary">
                            <i class="fas fa-edit"></i> <?php _e('Edit', 'wecoza-site-management'); ?>
                        </a>
                    <?php endif; ?>
                    <?php if ($show_delete_link && $can_delete): ?>
                        <button type="button" 
                                class="btn btn-danger btn-delete-site" 
                                data-site-id="<?php echo esc_attr($site->getSiteId()); ?>"
                                data-site-name="<?php echo esc_attr($site->getSiteName()); ?>">
                            <i class="fas fa-trash"></i> <?php _e('Delete', 'wecoza-site-management'); ?>
                        </button>
                    <?php endif; ?>
                </div>
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
                        <?php _e('Site Information', 'wecoza-site-management'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3"><?php _e('Site ID:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-secondary"><?php echo esc_html($site->getSiteId()); ?></span>
                        </dd>

                        <dt class="col-sm-3"><?php _e('Site Name:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-9">
                            <strong><?php echo esc_html($site->getSiteName()); ?></strong>
                        </dd>

                        <dt class="col-sm-3"><?php _e('Client:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-9">
                            <?php if ($client): ?>
                                <span class="badge bg-info text-dark">
                                    <?php echo esc_html($client->getClientName()); ?>
                                </span>
                                <small class="text-muted ms-2">ID: <?php echo esc_html($site->getClientId()); ?></small>
                            <?php else: ?>
                                <span class="text-muted">
                                    <?php _e('Unknown Client', 'wecoza-site-management'); ?>
                                    <small>(ID: <?php echo esc_html($site->getClientId()); ?>)</small>
                                </span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-3"><?php _e('Address:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-9">
                            <?php if (!empty($site->getAddress())): ?>
                                <div class="site-address">
                                    <?php echo nl2br(esc_html($site->getAddress())); ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('No address provided', 'wecoza-site-management'); ?></span>
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
                        <?php _e('Timestamps', 'wecoza-site-management'); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-12"><?php _e('Created:', 'wecoza-site-management'); ?></dt>
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
                                <span class="text-muted"><?php _e('Unknown', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-12"><?php _e('Last Updated:', 'wecoza-site-management'); ?></dt>
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
                                    <?php printf(__('%s ago', 'wecoza-site-management'), $time_diff); ?>
                                </small>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Never updated', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSiteModal" tabindex="-1" aria-labelledby="deleteSiteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteSiteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php _e('Confirm Site Deletion', 'wecoza-site-management'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">
                    <?php _e('Are you sure you want to delete this site?', 'wecoza-site-management'); ?>
                </p>
                <div class="alert alert-subtle-warning">
                    <strong><?php _e('Warning:', 'wecoza-site-management'); ?></strong>
                    <?php _e('This action cannot be undone. All data associated with this site will be permanently removed.', 'wecoza-site-management'); ?>
                </div>
                <p class="mb-0">
                    <strong><?php _e('Site to delete:', 'wecoza-site-management'); ?></strong>
                    <span id="delete-site-name" class="text-danger"></span>
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i>
                    <?php _e('Cancel', 'wecoza-site-management'); ?>
                </button>
                <button type="button" class="btn btn-danger" id="confirm-delete-site">
                    <i class="fas fa-trash"></i>
                    <?php _e('Delete Site', 'wecoza-site-management'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Delete confirmation functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.btn-delete-site');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteSiteModal'));
    const confirmDeleteBtn = document.getElementById('confirm-delete-site');
    const deleteSiteNameSpan = document.getElementById('delete-site-name');
    
    let siteToDelete = null;
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            siteToDelete = {
                id: this.getAttribute('data-site-id'),
                name: this.getAttribute('data-site-name')
            };
            
            deleteSiteNameSpan.textContent = siteToDelete.name;
            deleteModal.show();
        });
    });
    
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            if (siteToDelete && typeof wecoza_site_management_ajax !== 'undefined') {
                // Disable button and show loading
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo esc_js(__("Deleting...", "wecoza-site-management")); ?>';
                
                // Perform AJAX delete
                const formData = new FormData();
                formData.append('action', 'wecoza_delete_site');
                formData.append('nonce', wecoza_site_management_ajax.nonce);
                formData.append('site_id', siteToDelete.id);
                
                fetch(wecoza_site_management_ajax.ajax_url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect to list page
                        window.location.href = '<?php echo esc_url(remove_query_arg(["action", "site_id"])); ?>';
                    } else {
                        alert(data.data || '<?php echo esc_js(__("Failed to delete site.", "wecoza-site-management")); ?>');
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-trash"></i> <?php echo esc_js(__("Delete Site", "wecoza-site-management")); ?>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('<?php echo esc_js(__("An error occurred while deleting the site.", "wecoza-site-management")); ?>');
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-trash"></i> <?php echo esc_js(__("Delete Site", "wecoza-site-management")); ?>';
                });
            }
        });
    }
});
</script>
