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
    <div class="alert alert-subtle-danger" role="alert">
        <h6 class="alert-heading">
            <i class="fas fa-exclamation-triangle"></i>
            Please correct the following errors:
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
                        Edit Site
                        <small class="text-muted">#<?php echo esc_html($site_id); ?></small>
                    <?php else: ?>
                        <i class="fas fa-plus text-success"></i>
                        Add New Client Site
                    <?php endif; ?>
                    
                </h4>

            <p class="mb-2 mb-md-0 mb-lg-2 text-body-tertiary">
                Site Information
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
                            Client
                            <span class="text-danger">*</span>
                        </label>
                        <select class="form-select <?php echo isset($errors['client_id']) ? 'is-invalid' : ''; ?>" 
                                id="client_id" 
                                name="client_id" 
                                required>
                            <option value="">
                                Select a client...
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
                            Select the client this site belongs to.
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Site Name -->
                    <div class="<?php echo $show_client_selector ? 'col-md-6' : 'col-md-12'; ?> mb-3">
                        <label for="site_name" class="form-label">
                            Site Name
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
                            Enter a descriptive name for this site (2-100 characters).
                        </div>
                    </div>
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="address" class="form-label">
                        Address
                    </label>
                    <textarea class="form-control <?php echo isset($errors['address']) ? 'is-invalid' : ''; ?>" 
                              id="address" 
                              name="address" 
                              rows="3"
                              maxlength="1000"
                              placeholder="Enter the full address of this site..."><?php echo esc_textarea($address); ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <div class="invalid-feedback">
                            <?php echo esc_html($errors['address']); ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        Optional. Enter the complete address including street, city, postal code, etc.
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
                                    Fields marked with * are required.
                                </span>
                            </div>
                            <div class="btn-group">
                                <a href="<?php echo esc_url(remove_query_arg(['action', 'site_id'])); ?>" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i>
                                    <?php if ($is_edit): ?>
                                        Update Site
                                    <?php else: ?>
                                        Create Site
                                    <?php endif; ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


</div>

<!-- Loading Overlay -->
<div id="form-loading-overlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" 
     style="background: rgba(0,0,0,0.5); z-index: 9999; display: none !important;">
    <div class="text-center text-white">
        <div class="spinner-border mb-3" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p>Saving site...</p>
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
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
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
