<?php
/**
 * Site Form View Template
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
$clients = isset($clients) ? $clients : [];
$is_edit = isset($is_edit) ? $is_edit : false;
$show_client_selector = isset($show_client_selector) ? $show_client_selector : true;
$redirect_url = isset($redirect_url) ? $redirect_url : '';
$nonce = isset($nonce) ? $nonce : '';
$errors = isset($errors) ? $errors : [];
$form_data = isset($form_data) ? $form_data : [];

// Get form values (from site object, form data, or defaults)
$site_id = $site ? $site->getSiteId() : (isset($form_data['site_id']) ? $form_data['site_id'] : '');
$client_id = $site ? $site->getClientId() : (isset($form_data['client_id']) ? $form_data['client_id'] : '');
$site_name = $site ? $site->getSiteName() : (isset($form_data['site_name']) ? $form_data['site_name'] : '');
$address = $site ? $site->getAddress() : (isset($form_data['address']) ? $form_data['address'] : '');
?>

<div class="wecoza-site-form-container">
    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" role="alert">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i>
            <?php _e('Please correct the following errors:', 'wecoza-site-management'); ?>
        </h6>
        <ul class="mb-0">
            <?php foreach ($errors as $field => $error): ?>
                <li><?php echo esc_html($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Site Form -->
    <div class="card">
        <div class="card-header">

                <h4 class="form-title mb-0">
                    <?php if ($is_edit): ?>
                        <i class="fas fa-edit text-primary"></i>
                        <?php _e('Edit Site', 'wecoza-site-management'); ?>
                        <small class="text-muted">#<?php echo esc_html($site_id); ?></small>
                    <?php else: ?>
                        <i class="fas fa-plus text-success"></i>
                        <?php _e('Add New Client Site', 'wecoza-site-management'); ?>
                    <?php endif; ?>
                    
                </h4>

            <p class="mb-2 mb-md-0 mb-lg-2 text-body-tertiary">
                <?php _e('Site Information', 'wecoza-site-management'); ?>
                    </p>
        </div>
        <div class="card-body">
            <form method="POST" id="site-form" class="needs-validation" novalidate>
                <!-- Security Nonce -->
                <?php wp_nonce_field('wecoza_site_management_nonce', 'wecoza_site_nonce'); ?>
                
                <!-- Hidden Fields -->
                <?php if ($is_edit): ?>
                    <input type="hidden" name="site_id" value="<?php echo esc_attr($site_id); ?>">
                <?php endif; ?>
                
                <?php if (!empty($redirect_url)): ?>
                    <input type="hidden" name="redirect_url" value="<?php echo esc_attr($redirect_url); ?>">
                <?php endif; ?>

                <div class="row">
                    <!-- Client Selection -->
                    <?php if ($show_client_selector): ?>
                    <div class="col-md-6 mb-3">
                        <label for="client_id" class="form-label">
                            <?php _e('Client', 'wecoza-site-management'); ?>
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?php echo isset($errors['client_id']) ? 'is-invalid' : ''; ?>" 
                                id="client_id" 
                                name="client_id" 
                                required>
                            <option value="">
                                <?php _e('Select a client...', 'wecoza-site-management'); ?>
                            </option>
                            <?php foreach ($clients as $id => $name): ?>
                                <option value="<?php echo esc_attr($id); ?>" 
                                        <?php selected($client_id, $id); ?>>
                                    <?php echo esc_html($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['client_id'])): ?>
                            <div class="invalid-feedback">
                                <?php echo esc_html($errors['client_id']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            <?php _e('Select the client this site belongs to.', 'wecoza-site-management'); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Site Name -->
                    <div class="<?php echo $show_client_selector ? 'col-md-6' : 'col-md-12'; ?> mb-3">
                        <label for="site_name" class="form-label">
                            <?php _e('Site Name', 'wecoza-site-management'); ?>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control <?php echo isset($errors['site_name']) ? 'is-invalid' : ''; ?>" 
                               id="site_name" 
                               name="site_name" 
                               value="<?php echo esc_attr($site_name); ?>"
                               maxlength="100"
                               required>
                        <?php if (isset($errors['site_name'])): ?>
                            <div class="invalid-feedback">
                                <?php echo esc_html($errors['site_name']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="form-text">
                            <?php _e('Enter a descriptive name for this site (2-100 characters).', 'wecoza-site-management'); ?>
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="address" class="form-label">
                        <?php _e('Address', 'wecoza-site-management'); ?>
                    </label>
                    <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                              id="address" 
                              name="address" 
                              rows="3"
                              maxlength="1000"
                              placeholder="<?php esc_attr_e('Enter the full address of this site...', 'wecoza-site-management'); ?>"><?php echo esc_textarea($address); ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback">
                            <?php echo esc_html($errors['address']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        <?php _e('Optional. Enter the complete address including street, city, postal code, etc.', 'wecoza-site-management'); ?>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <hr class="my-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    <?php _e('Fields marked with * are required.', 'wecoza-site-management'); ?>
                                </span>
                            </div>
                            <div class="btn-group">
                                <a href="<?php echo esc_url(remove_query_arg(['action', 'site_id'])); ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                    <?php _e('Cancel', 'wecoza-site-management'); ?>
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i>
                                    <?php if ($is_edit): ?>
                                        <?php _e('Update Site', 'wecoza-site-management'); ?>
                                    <?php else: ?>
                                        <?php _e('Create Site', 'wecoza-site-management'); ?>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Additional Information -->
    <?php if ($is_edit && $site): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="card-title mb-0">
                <i class="fas fa-info-circle"></i>
                <?php _e('Site Information', 'wecoza-site-management'); ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4"><?php _e('Site ID:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-8"><?php echo esc_html($site->getSiteId()); ?></dd>
                        
                        <dt class="col-sm-4"><?php _e('Created:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-8">
                            <?php if ($site->getCreatedAt()): ?>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($site->getCreatedAt()))); ?>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Unknown', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4"><?php _e('Last Updated:', 'wecoza-site-management'); ?></dt>
                        <dd class="col-sm-8">
                            <?php if ($site->getUpdatedAt()): ?>
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($site->getUpdatedAt()))); ?>
                            <?php else: ?>
                                <span class="text-muted"><?php _e('Never', 'wecoza-site-management'); ?></span>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Loading Overlay -->
<div id="form-loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background: rgba(0,0,0,0.5); z-index: 9999; display: none !important;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status">
            <span class="visually-hidden"><?php _e('Loading...', 'wecoza-site-management'); ?></span>
        </div>
        <p><?php _e('Saving site...', 'wecoza-site-management'); ?></p>
    </div>
</div>

<script>
// Form validation and enhancement
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('site-form');
    const submitBtn = document.getElementById('submit-btn');
    const loadingOverlay = document.getElementById('form-loading-overlay');
    
    // Bootstrap form validation
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        } else {
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?php echo esc_js(__("Saving...", "wecoza-site-management")); ?>';
            loadingOverlay.style.display = 'flex';
        }
        
        form.classList.add('was-validated');
    });
    
    // Character counter for site name
    const siteNameInput = document.getElementById('site_name');
    if (siteNameInput) {
        const maxLength = siteNameInput.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.innerHTML = `<span id="site-name-count">0</span>/${maxLength}`;
        siteNameInput.parentNode.appendChild(counter);
        
        siteNameInput.addEventListener('input', function() {
            document.getElementById('site-name-count').textContent = this.value.length;
        });
        
        // Initialize counter
        document.getElementById('site-name-count').textContent = siteNameInput.value.length;
    }
    
    // Character counter for address
    const addressInput = document.getElementById('address');
    if (addressInput) {
        const maxLength = addressInput.getAttribute('maxlength');
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        counter.innerHTML = `<span id="address-count">0</span>/${maxLength}`;
        addressInput.parentNode.appendChild(counter);
        
        addressInput.addEventListener('input', function() {
            document.getElementById('address-count').textContent = this.value.length;
        });
        
        // Initialize counter
        document.getElementById('address-count').textContent = addressInput.value.length;
    }
});
</script>
