# WeCoza Site Management - Usage Examples

This document provides practical examples of how to implement the WeCoza Site Management plugin shortcodes in your WordPress pages and posts.

## Table of Contents

1. [Basic Implementation](#basic-implementation)
2. [Advanced Configurations](#advanced-configurations)
3. [Complete Site Management System](#complete-site-management-system)
4. [Integration Examples](#integration-examples)
5. [Custom Styling Examples](#custom-styling-examples)
6. [PHP Integration](#php-integration)

---

## Basic Implementation

### Simple Site List Page

Create a new WordPress page titled "Sites" and add:

```html
<div class="site-management-intro">
    <h2>Our Training Sites</h2>
    <p>Browse all our training locations and facilities.</p>
</div>

[wecoza_sites_list]
```

### Basic Site Creation Form

Create a page titled "Add New Site":

```html
<div class="form-intro">
    <h2>Add New Training Site</h2>
    <p>Please fill in all required information for the new site.</p>
</div>

[wecoza_site_form]

<div class="form-help">
    <h4>Need Help?</h4>
    <ul>
        <li>Site names should be descriptive and unique</li>
        <li>Include full address for better location tracking</li>
        <li>Contact support if you need assistance</li>
    </ul>
</div>
```

### Simple Site Details Page

Create a page titled "Site Details":

```html
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/sites/">Sites</a></li>
        <li class="breadcrumb-item active">Site Details</li>
    </ol>
</nav>

[wecoza_site_details]
```

---

## Advanced Configurations

### Customized Site List with Filtering

```html
<div class="sites-dashboard">
    <h2>Site Management Dashboard</h2>
    
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Total Sites</h5>
                    <p class="card-text display-4" id="total-sites">-</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Active Clients</h5>
                    <p class="card-text display-4" id="total-clients">-</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5>Recent Sites</h5>
                    <p class="card-text display-4" id="recent-count">-</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sites List -->
    [wecoza_sites_list per_page="25" order_by="updated_at" order="DESC"]
</div>

<script>
// Load statistics
jQuery(document).ready(function($) {
    $.post(wecoza_site_management_ajax.ajax_url, {
        action: 'wecoza_get_site_statistics',
        nonce: wecoza_site_management_ajax.nonce
    }, function(response) {
        if (response.success) {
            $('#total-sites').text(response.data.total_sites);
            $('#total-clients').text(response.data.total_clients);
            $('#recent-count').text(response.data.recent_sites.length);
        }
    });
});
</script>
```

### Multi-Step Site Creation

Create a page with tabbed interface:

```html
<div class="site-creation-wizard">
    <h2>Site Creation Wizard</h2>
    
    <ul class="nav nav-tabs" id="siteWizardTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#step1">Step 1: Basic Info</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#step2">Step 2: Location</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#step3">Step 3: Review</a>
        </li>
    </ul>
    
    <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="step1">
            <h4>Basic Site Information</h4>
            [wecoza_site_form show_client_selector="true"]
        </div>
        <div class="tab-pane fade" id="step2">
            <h4>Location Details</h4>
            <p>Additional location configuration would go here.</p>
        </div>
        <div class="tab-pane fade" id="step3">
            <h4>Review and Submit</h4>
            <p>Review all information before submitting.</p>
        </div>
    </div>
</div>
```

### Client-Specific Site Management

```html
<div class="client-site-manager">
    <h2>Client Site Management</h2>
    
    <!-- Client Selection -->
    <div class="client-selector mb-4">
        <label for="client-filter">Select Client:</label>
        <select id="client-filter" class="form-select">
            <option value="">All Clients</option>
            <!-- Options populated via AJAX -->
        </select>
    </div>
    
    <!-- Dynamic Sites List -->
    <div id="client-sites-container">
        [wecoza_sites_list per_page="15"]
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#client-filter').on('change', function() {
        const clientId = $(this).val();
        if (clientId) {
            // Reload page with client filter
            window.location.href = '?client_id=' + clientId;
        } else {
            window.location.href = window.location.pathname;
        }
    });
});
</script>
```

---

## Complete Site Management System

### Main Sites Management Page

```html
<div class="sites-management-system">
    <!-- Header -->
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1>Site Management System</h1>
                <p class="lead">Manage all training sites and their client relationships</p>
            </div>
            <div class="col-md-4 text-end">
                <a href="/add-site/" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus"></i> Add New Site
                </a>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions mb-4">
        <div class="row">
            <div class="col-md-3">
                <a href="/sites/" class="btn btn-outline-primary w-100">
                    <i class="fas fa-list"></i><br>View All Sites
                </a>
            </div>
            <div class="col-md-3">
                <a href="/add-site/" class="btn btn-outline-success w-100">
                    <i class="fas fa-plus"></i><br>Add New Site
                </a>
            </div>
            <div class="col-md-3">
                <a href="/clients/" class="btn btn-outline-info w-100">
                    <i class="fas fa-users"></i><br>Manage Clients
                </a>
            </div>
            <div class="col-md-3">
                <a href="/reports/" class="btn btn-outline-warning w-100">
                    <i class="fas fa-chart-bar"></i><br>View Reports
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Content -->
    [wecoza_sites_list per_page="20" show_search="true"]
</div>
```

### Site Details with Related Information

```html
<div class="comprehensive-site-details">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/sites/">Sites</a></li>
            <li class="breadcrumb-item active">Site Details</li>
        </ol>
    </nav>
    
    <!-- Main Site Details -->
    [wecoza_site_details show_edit_link="true" show_delete_link="true"]
    
    <!-- Additional Information Tabs -->
    <div class="mt-4">
        <ul class="nav nav-tabs" id="siteDetailsTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#classes">Classes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#equipment">Equipment</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#history">History</a>
            </li>
        </ul>
        
        <div class="tab-content mt-3">
            <div class="tab-pane fade show active" id="classes">
                <h4>Classes at This Site</h4>
                <!-- Classes shortcode would go here -->
                <p>Integration with classes plugin...</p>
            </div>
            <div class="tab-pane fade" id="equipment">
                <h4>Equipment & Facilities</h4>
                <p>Equipment management integration...</p>
            </div>
            <div class="tab-pane fade" id="history">
                <h4>Site History</h4>
                <p>Historical data and changes...</p>
            </div>
        </div>
    </div>
</div>
```

---

## Integration Examples

### With Contact Form 7

```html
<div class="site-inquiry-page">
    <h2>Site Information & Inquiry</h2>
    
    <!-- Site Details -->
    <div class="row">
        <div class="col-md-8">
            [wecoza_site_details show_edit_link="false" show_delete_link="false"]
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Inquire About This Site</h5>
                </div>
                <div class="card-body">
                    [contact-form-7 id="123" title="Site Inquiry Form"]
                </div>
            </div>
        </div>
    </div>
</div>
```

### With WooCommerce

```html
<div class="training-booking">
    <h2>Book Training at This Location</h2>
    
    <!-- Site Information -->
    [wecoza_site_details show_edit_link="false" show_delete_link="false"]
    
    <!-- Booking Form -->
    <div class="booking-section mt-4">
        <h3>Available Training Programs</h3>
        [products category="training" site_id="123"]
    </div>
</div>
```

### With Custom Post Types

```php
// In your theme's functions.php
function display_site_with_properties() {
    if (is_singular('property')) {
        $site_id = get_post_meta(get_the_ID(), 'associated_site_id', true);
        if ($site_id) {
            echo '<div class="property-site-info">';
            echo '<h3>Training Site Information</h3>';
            echo do_shortcode("[wecoza_site_details site_id='{$site_id}' show_edit_link='false' show_delete_link='false']");
            echo '</div>';
        }
    }
}
add_action('wp_footer', 'display_site_with_properties');
```

---

## Custom Styling Examples

### Dark Theme Implementation

```css
/* Add to your theme's style.css */
.dark-theme .wecoza-sites-list-container,
.dark-theme .wecoza-site-form-container,
.dark-theme .wecoza-site-details-container {
    background: #2c3e50;
    color: #ecf0f1;
}

.dark-theme .sites-table {
    background: #34495e;
    color: #ecf0f1;
}

.dark-theme .sites-table th {
    background: #1a252f;
}

.dark-theme .card {
    background: #34495e;
    border-color: #4a5f7a;
}
```

### Mobile-First Responsive Design

```css
/* Mobile-first approach */
.sites-mobile-optimized {
    padding: 1rem;
}

@media (min-width: 768px) {
    .sites-mobile-optimized {
        padding: 2rem;
    }
    
    .sites-table .btn-group {
        display: flex;
    }
}

@media (max-width: 767px) {
    .sites-table .btn-group .btn {
        display: block;
        width: 100%;
        margin-bottom: 0.25rem;
    }
}
```

---

## PHP Integration

### Custom Template Integration

```php
<?php
// In your theme's page-sites.php template

get_header(); ?>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <?php
            // Check if user can manage sites
            if (current_user_can('edit_posts')) {
                echo '<div class="admin-toolbar mb-3">';
                echo '<a href="/add-site/" class="btn btn-primary">Add New Site</a>';
                echo '</div>';
            }
            
            // Display sites list
            echo do_shortcode('[wecoza_sites_list per_page="20"]');
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
```

### Dynamic Shortcode Parameters

```php
<?php
// Dynamic client filtering based on user role
function get_user_client_sites() {
    $current_user = wp_get_current_user();
    $user_client_id = get_user_meta($current_user->ID, 'assigned_client_id', true);
    
    if ($user_client_id && !current_user_can('manage_options')) {
        // Show only user's assigned client sites
        return do_shortcode("[wecoza_sites_list client_id='{$user_client_id}']");
    } else {
        // Show all sites for admins
        return do_shortcode('[wecoza_sites_list]');
    }
}

// Use in template: echo get_user_client_sites();
?>
```

### AJAX Integration

```javascript
// Custom AJAX functionality
function loadClientSites(clientId) {
    jQuery.post(wecoza_site_management_ajax.ajax_url, {
        action: 'wecoza_get_client_sites',
        nonce: wecoza_site_management_ajax.nonce,
        client_id: clientId
    }, function(response) {
        if (response.success) {
            updateSitesDisplay(response.data.sites);
        }
    });
}

function updateSitesDisplay(sites) {
    let html = '<div class="sites-grid row">';
    sites.forEach(function(site) {
        html += `
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${site.site_name}</h5>
                        <p class="card-text">${site.address || 'No address'}</p>
                        <a href="/site-details/?site_id=${site.site_id}" class="btn btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    jQuery('#sites-container').html(html);
}
```

---

These examples demonstrate the flexibility and power of the WeCoza Site Management plugin. You can combine and customize these examples to create the perfect site management solution for your specific needs.
