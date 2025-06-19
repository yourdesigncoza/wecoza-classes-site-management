<?php
/**
 * Search Form Component
 *
 * @package WeCozaSiteManagement\Views\Components
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Extract variables
$search_value = isset($search_value) ? $search_value : '';
$search_param = isset($search_param) ? $search_param : 'search';
$placeholder = isset($placeholder) ? $placeholder : __('Search...', 'wecoza-site-management');
$form_id = isset($form_id) ? $form_id : 'search-form';
$input_id = isset($input_id) ? $input_id : 'search-input';
$show_clear = isset($show_clear) ? $show_clear : true;
$show_refresh = isset($show_refresh) ? $show_refresh : true;
$form_method = isset($form_method) ? $form_method : 'GET';
$form_class = isset($form_class) ? $form_class : 'row g-3';
$input_class = isset($input_class) ? $input_class : 'col-md-8';
$button_class = isset($button_class) ? $button_class : 'col-md-4';
$additional_fields = isset($additional_fields) ? $additional_fields : [];
?>

<div class="search-form-container mb-4">
    <form method="<?php echo esc_attr($form_method); ?>" 
          class="<?php echo esc_attr($form_class); ?>" 
          id="<?php echo esc_attr($form_id); ?>">
        
        <!-- Preserve existing query parameters -->
        <?php foreach ($_GET as $key => $value): ?>
            <?php if ($key !== $search_param && $key !== 'page' && !in_array($key, array_keys($additional_fields))): ?>
                <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>">
            <?php endif; ?>
        <?php endforeach; ?>
        
        <!-- Additional hidden fields -->
        <?php foreach ($additional_fields as $field_name => $field_value): ?>
            <input type="hidden" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($field_value); ?>">
        <?php endforeach; ?>
        
        <!-- Search Input -->
        <div class="<?php echo esc_attr($input_class); ?>">
            <div class="input-group">
                <input type="text" 
                       class="form-control" 
                       name="<?php echo esc_attr($search_param); ?>" 
                       id="<?php echo esc_attr($input_id); ?>"
                       value="<?php echo esc_attr($search_value); ?>" 
                       placeholder="<?php echo esc_attr($placeholder); ?>"
                       autocomplete="off">
                <button class="btn btn-outline-secondary" type="submit">
                    <i class="fas fa-search"></i>
                    <span class="d-none d-sm-inline ms-1"><?php _e('Search', 'wecoza-site-management'); ?></span>
                </button>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="<?php echo esc_attr($button_class); ?>">
            <div class="d-flex gap-2">
                <?php if ($show_clear && !empty($search_value)): ?>
                    <a href="<?php echo esc_url(remove_query_arg([$search_param, 'page'])); ?>" 
                       class="btn btn-outline-secondary"
                       title="<?php esc_attr_e('Clear search', 'wecoza-site-management'); ?>">
                        <i class="fas fa-times"></i>
                        <span class="d-none d-lg-inline ms-1"><?php _e('Clear', 'wecoza-site-management'); ?></span>
                    </a>
                <?php endif; ?>
                
                <?php if ($show_refresh): ?>
                    <button type="button" 
                            class="btn btn-outline-primary refresh-btn"
                            title="<?php esc_attr_e('Refresh results', 'wecoza-site-management'); ?>">
                        <i class="fas fa-sync-alt"></i>
                        <span class="d-none d-lg-inline ms-1"><?php _e('Refresh', 'wecoza-site-management'); ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </form>
    
    <!-- Search Results Info -->
    <?php if (!empty($search_value)): ?>
    <div class="search-info mt-2">
        <small class="text-muted">
            <i class="fas fa-search"></i>
            <?php printf(__('Searching for: "%s"', 'wecoza-site-management'), '<strong>' . esc_html($search_value) . '</strong>'); ?>
        </small>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('<?php echo esc_js($form_id); ?>');
    const searchInput = document.getElementById('<?php echo esc_js($input_id); ?>');
    const refreshBtn = searchForm.querySelector('.refresh-btn');
    
    // Auto-submit on Enter key
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchForm.submit();
            }
        });
        
        // Focus search input on page load if it has a value
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }
    }
    
    // Refresh button functionality
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            // Add loading state
            const originalHTML = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="d-none d-lg-inline ms-1"><?php echo esc_js(__("Refreshing...", "wecoza-site-management")); ?></span>';
            
            // Reload the page
            window.location.reload();
        });
    }
    
    // Live search functionality (optional)
    if (typeof enableLiveSearch !== 'undefined' && enableLiveSearch && searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchValue = this.value.trim();
            
            // Only search if value is at least 2 characters or empty (to clear)
            if (searchValue.length >= 2 || searchValue.length === 0) {
                searchTimeout = setTimeout(function() {
                    // Trigger search via AJAX or form submission
                    if (typeof performLiveSearch === 'function') {
                        performLiveSearch(searchValue);
                    } else {
                        searchForm.submit();
                    }
                }, 500); // 500ms delay
            }
        });
    }
});
</script>
