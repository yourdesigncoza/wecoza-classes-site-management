# WeCoza Site Management Plugin - Shortcode Documentation

## Overview

The WeCoza Site Management Plugin provides three powerful shortcodes for managing sites and their relationships with clients. Each shortcode is designed to be flexible, secure, and user-friendly.

## Table of Contents

1. [wecoza_sites_list](#wecoza_sites_list)
2. [wecoza_site_form](#wecoza_site_form)
3. [wecoza_site_details](#wecoza_site_details)
4. [Security & Permissions](#security--permissions)
5. [Styling & Customization](#styling--customization)
6. [Troubleshooting](#troubleshooting)

---

## wecoza_sites_list

Displays a comprehensive list of all sites with search, pagination, and filtering capabilities.

### Syntax
```
[wecoza_sites_list per_page="20" show_search="true" show_pagination="true" client_id="" order_by="site_name" order="ASC"]
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | integer | 20 | Number of sites to display per page (1-100) |
| `show_search` | boolean | true | Whether to display the search form |
| `show_pagination` | boolean | true | Whether to display pagination controls |
| `client_id` | integer | null | Filter sites by specific client ID |
| `order_by` | string | site_name | Sort field: `site_id`, `site_name`, `client_id`, `created_at`, `updated_at` |
| `order` | string | ASC | Sort direction: `ASC` or `DESC` |

### Usage Examples

#### Basic Usage
```
[wecoza_sites_list]
```
Displays all sites with default settings (20 per page, search enabled, pagination enabled).

#### Custom Page Size
```
[wecoza_sites_list per_page="10"]
```
Shows 10 sites per page instead of the default 20.

#### Filter by Client
```
[wecoza_sites_list client_id="5"]
```
Shows only sites belonging to client with ID 5.

#### Disable Search and Pagination
```
[wecoza_sites_list show_search="false" show_pagination="false"]
```
Shows all sites without search or pagination controls.

#### Custom Sorting
```
[wecoza_sites_list order_by="created_at" order="DESC"]
```
Shows sites sorted by creation date, newest first.

### Features

- **Live Search**: Real-time filtering as users type
- **Responsive Design**: Works on all device sizes
- **AJAX Pagination**: Smooth page transitions without full reload
- **Bulk Operations**: Select multiple sites for batch actions
- **Export Options**: Download site lists in various formats
- **Action Buttons**: Quick access to view, edit, and delete operations

### Required Permissions

- **View**: `read` capability
- **Edit Actions**: `edit_posts` capability
- **Delete Actions**: `delete_posts` capability

---

## wecoza_site_form

Displays a form for creating new sites or editing existing ones with comprehensive validation.

### Syntax
```
[wecoza_site_form site_id="" redirect_url="" show_client_selector="true"]
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `site_id` | integer | null | ID of site to edit (omit for new site creation) |
| `redirect_url` | string | "" | URL to redirect to after successful save |
| `show_client_selector` | boolean | true | Whether to show the client selection dropdown |

### Usage Examples

#### Create New Site
```
[wecoza_site_form]
```
Displays a form for creating a new site.

#### Edit Existing Site
```
[wecoza_site_form site_id="123"]
```
Displays a form pre-populated with data from site ID 123.

#### Custom Redirect
```
[wecoza_site_form redirect_url="/sites-list/"]
```
Redirects to `/sites-list/` after successful save.

#### Hide Client Selector
```
[wecoza_site_form show_client_selector="false"]
```
Useful when the client is predetermined or set via other means.

#### Edit with Redirect
```
[wecoza_site_form site_id="123" redirect_url="/site-details/?site_id=123"]
```
Edit site 123 and redirect to its details page after saving.

### Form Fields

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| Client | Select | Yes | Must be valid client ID |
| Site Name | Text | Yes | 2-100 characters, unique per client |
| Address | Textarea | No | Maximum 1000 characters |

### Features

- **Real-time Validation**: Instant feedback as users type
- **Character Counters**: Shows remaining characters for text fields
- **Duplicate Detection**: Prevents duplicate site names per client
- **Auto-save**: Saves form data locally to prevent loss
- **Rich Text Editor**: Enhanced address input with formatting
- **File Uploads**: Attach documents or images to sites

### Required Permissions

- **Create/Edit**: `edit_posts` capability

---

## wecoza_site_details

Displays comprehensive information about a single site with optional edit and delete actions.

### Syntax
```
[wecoza_site_details site_id="" show_edit_link="true" show_delete_link="true"]
```

### Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `site_id` | integer | null | ID of site to display (can also come from URL parameter) |
| `show_edit_link` | boolean | true | Whether to show the edit button |
| `show_delete_link` | boolean | true | Whether to show the delete button |

### Usage Examples

#### Basic Usage
```
[wecoza_site_details site_id="123"]
```
Shows details for site ID 123 with all action buttons.

#### Read-only View
```
[wecoza_site_details site_id="123" show_edit_link="false" show_delete_link="false"]
```
Shows site details without any action buttons.

#### Dynamic Site ID from URL
```
[wecoza_site_details]
```
Gets site ID from URL parameter `?site_id=123`.

#### Hide Delete Button Only
```
[wecoza_site_details site_id="123" show_delete_link="false"]
```
Shows edit button but hides delete button for safety.

### Displayed Information

- **Basic Details**: Site ID, name, client information
- **Address**: Full address with formatting
- **Timestamps**: Creation and last update dates
- **Client Relationship**: Link to client details
- **Statistics**: Usage metrics and related data
- **Action History**: Recent changes and modifications

### Features

- **Responsive Layout**: Adapts to different screen sizes
- **Print-friendly**: Optimized for printing
- **Social Sharing**: Share site information
- **QR Code**: Generate QR code for site location
- **Map Integration**: Show site location on map
- **Export Options**: Download site information

### Required Permissions

- **View**: `read` capability
- **Edit Actions**: `edit_posts` capability
- **Delete Actions**: `delete_posts` capability

---

## Security & Permissions

### WordPress Capabilities

The plugin respects WordPress user roles and capabilities:

| Action | Required Capability | Description |
|--------|-------------------|-------------|
| View Sites | `read` | Basic viewing permission |
| Create Sites | `edit_posts` | Can create new sites |
| Edit Sites | `edit_posts` | Can modify existing sites |
| Delete Sites | `delete_posts` | Can remove sites |
| Manage Settings | `manage_options` | Admin-level access |

### Security Features

- **Nonce Protection**: All forms include WordPress nonces
- **CSRF Prevention**: Cross-site request forgery protection
- **SQL Injection Protection**: All queries use prepared statements
- **XSS Prevention**: All output is properly escaped
- **Input Sanitization**: All user input is sanitized
- **Capability Checks**: Every action verifies user permissions

---

## Styling & Customization

### CSS Classes

The plugin uses Bootstrap 5 compatible classes and provides custom CSS classes for styling:

```css
/* Main containers */
.wecoza-sites-list-container
.wecoza-site-form-container
.wecoza-site-details-container

/* Tables */
.sites-table
.sites-table-container

/* Forms */
.site-form-container
.search-form-container

/* Buttons */
.btn-view-site
.btn-delete-site
.btn-edit-site
```

### Customization Options

1. **Override CSS**: Add custom styles to your theme
2. **Template Override**: Copy view files to your theme
3. **Hooks & Filters**: Use WordPress hooks for customization
4. **JavaScript Events**: Listen for plugin events

---

## Troubleshooting

### Common Issues

#### Shortcode Not Displaying
- Check if plugin is activated
- Verify user has required permissions
- Check for PHP errors in debug log

#### Search Not Working
- Ensure JavaScript is enabled
- Check for JavaScript console errors
- Verify AJAX URL is correct

#### Form Validation Errors
- Check database connection
- Verify client exists for site creation
- Ensure all required fields are filled

#### Permission Denied Errors
- Check user role and capabilities
- Verify WordPress user permissions
- Contact administrator for access

### Debug Mode

Enable debug mode by adding to wp-config.php:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

### Support

For additional support:
1. Check the plugin documentation
2. Review error logs
3. Contact plugin support team
4. Submit bug reports on GitHub

---

## Advanced Usage

### Combining Shortcodes

Create a complete site management interface:

```html
<!-- Site List Page -->
<h2>All Sites</h2>
[wecoza_sites_list per_page="15"]

<!-- Create Site Page -->
<h2>Add New Site</h2>
[wecoza_site_form redirect_url="/sites/"]

<!-- Site Details Page -->
<h2>Site Details</h2>
[wecoza_site_details]
```

### URL Parameters

The shortcodes respond to URL parameters:

- `?site_id=123` - Auto-selects site for details/edit
- `?client_id=456` - Filters list by client
- `?search=office` - Pre-fills search term
- `?page=2` - Shows specific page

### Integration Examples

#### With Contact Form 7
```
[wecoza_site_details site_id="123"]
[contact-form-7 id="456" title="Site Inquiry"]
```

#### With Custom Post Types
```php
// In your theme's functions.php
add_action('wp_head', function() {
    if (is_singular('property')) {
        $site_id = get_post_meta(get_the_ID(), 'site_id', true);
        if ($site_id) {
            echo do_shortcode("[wecoza_site_details site_id='{$site_id}']");
        }
    }
});
```
