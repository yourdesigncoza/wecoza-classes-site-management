# WeCoza Classes Site Management

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![GitHub Issues](https://img.shields.io/github/issues/yourdesigncoza/wecoza-classes-site-management.svg)](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues)
[![GitHub Stars](https://img.shields.io/github/stars/yourdesigncoza/wecoza-classes-site-management.svg)](https://github.com/yourdesigncoza/wecoza-classes-site-management/stargazers)

A comprehensive site management system for WeCoza training programs. This WordPress plugin handles site creation, management, and client-site relationships with full MVC architecture, AJAX functionality, and responsive design.

## 🚀 Quick Start

```bash
# Clone the repository
git clone https://github.com/yourdesigncoza/wecoza-classes-site-management.git

# Navigate to your WordPress plugins directory
cd /path/to/wordpress/wp-content/plugins/

# Copy the plugin
cp -r wecoza-classes-site-management ./

# Activate via WordPress admin or WP-CLI
wp plugin activate wecoza-classes-site-management
```

## Features

- **Complete CRUD Operations**: Create, Read, Update, Delete sites with full validation
- **Client-Site Relationships**: Each client can have multiple sites with proper foreign key relationships
- **Advanced Search & Filtering**: Real-time search with pagination and client filtering
- **Responsive Design**: Bootstrap 5 compatible interface that works on all devices
- **AJAX Functionality**: Smooth user experience with dynamic loading and form submissions
- **Security First**: WordPress nonces, capability checks, SQL injection protection, and data validation
- **MVC Architecture**: Clean, maintainable code structure following WordPress best practices
- **PostgreSQL Integration**: Robust database connectivity with transaction support

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **Database**: PostgreSQL (existing sites and clients tables)
- **Browser**: Modern browsers with JavaScript enabled
- **User Permissions**: Appropriate WordPress capabilities for CRUD operations

## Installation

### Step 1: Upload Plugin Files

1. Download the plugin files
2. Upload the `wecoza-classes-site-management` folder to `/wp-content/plugins/`
3. Ensure all files are properly uploaded and accessible

### Step 2: Database Configuration

The plugin requires access to an existing PostgreSQL database with `sites` and `clients` tables.

#### Configure Database Connection

Add the following to your WordPress `wp-config.php` or set via WordPress admin:

```php
// PostgreSQL connection settings
update_option('wecoza_postgres_host', 'your-postgres-host.com');
update_option('wecoza_postgres_port', '25060');
update_option('wecoza_postgres_dbname', 'your-database-name');
update_option('wecoza_postgres_user', 'your-username');
update_option('wecoza_postgres_password', 'your-password');
```

#### Database Schema

The plugin expects the following table structure:

```sql
-- Sites table
CREATE TABLE sites (
    site_id SERIAL PRIMARY KEY,
    client_id INTEGER NOT NULL,
    site_name VARCHAR(100) NOT NULL,
    address TEXT,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE
);

-- Index for performance
CREATE INDEX idx_sites_client_id ON sites(client_id);

-- Clients table (should already exist)
CREATE TABLE clients (
    client_id SERIAL PRIMARY KEY,
    client_name VARCHAR(255) NOT NULL
);
```

### Step 3: Activate Plugin

1. Go to WordPress Admin → Plugins
2. Find "WeCoza Site Management"
3. Click "Activate"
4. The plugin will automatically verify database connectivity and table structure

### Step 4: Verify Installation

1. Check for any error messages in the WordPress admin
2. Test database connectivity by viewing the plugin pages
3. Verify that sites and clients data is accessible

## Usage

### Shortcodes

The plugin provides three main shortcodes:

#### 1. Sites List
```
[wecoza_sites_list]
```
Displays a searchable, paginated list of all sites.

**Parameters:**
- `per_page` (default: 20) - Number of sites per page
- `show_search` (default: true) - Show search form
- `show_pagination` (default: true) - Show pagination
- `client_id` - Filter by specific client
- `order_by` (default: site_name) - Sort field
- `order` (default: ASC) - Sort direction

#### 2. Site Form
```
[wecoza_site_form]
```
Displays form for creating or editing sites.

**Parameters:**
- `site_id` - ID of site to edit (omit for new site)
- `redirect_url` - Where to redirect after save
- `show_client_selector` (default: true) - Show client dropdown

#### 3. Site Details
```
[wecoza_site_details]
```
Shows detailed information about a single site.

**Parameters:**
- `site_id` - Site ID to display
- `show_edit_link` (default: true) - Show edit button
- `show_delete_link` (default: true) - Show delete button

### Example Implementation

Create WordPress pages with the following content:

#### Sites List Page
```html
<h2>Site Management</h2>
<p>Manage all sites and their client relationships.</p>
[wecoza_sites_list per_page="15"]
```

#### Add Site Page
```html
<h2>Add New Site</h2>
[wecoza_site_form redirect_url="/sites/"]
```

#### Site Details Page
```html
<h2>Site Details</h2>
[wecoza_site_details]
```

## User Permissions

The plugin respects WordPress user roles and capabilities:

| Action | Required Capability | Description |
|--------|-------------------|-------------|
| View Sites | `read` | Can view site lists and details |
| Create Sites | `edit_posts` | Can create new sites |
| Edit Sites | `edit_posts` | Can modify existing sites |
| Delete Sites | `delete_posts` | Can remove sites |

## File Structure

```
wecoza-classes-site-management/
├── wecoza-classes-site-management.php  # Main plugin file
├── includes/                           # Core plugin classes
│   ├── class-activator.php            # Plugin activation
│   ├── class-deactivator.php          # Plugin deactivation
│   ├── class-uninstaller.php          # Plugin uninstall
│   └── class-wecoza-site-management-plugin.php
├── app/                               # MVC application structure
│   ├── Controllers/                   # Business logic
│   │   └── SiteController.php
│   ├── Models/                        # Data models
│   │   ├── SiteModel.php
│   │   └── ClientModel.php
│   ├── Views/                         # Templates
│   │   ├── sites/
│   │   │   ├── list.php
│   │   │   ├── form.php
│   │   │   └── details.php
│   │   └── components/
│   │       ├── pagination.php
│   │       └── search-form.php
│   ├── Services/                      # Shared services
│   │   ├── DatabaseService.php
│   │   └── MigrationService.php
│   ├── ajax-handlers.php              # AJAX endpoints
│   └── bootstrap.php                  # Application bootstrap
├── assets/                            # Frontend assets
│   ├── css/
│   │   └── sites-management.css
│   └── js/
│       └── sites-management.js
├── config/                            # Configuration files
│   └── app.php
├── sites_schema.sql                   # Database schema reference
├── README.md                          # This file
└── SHORTCODE_DOCUMENTATION.md         # Detailed shortcode docs
```

## Security Features

- **WordPress Nonces**: CSRF protection on all forms
- **Capability Checks**: User permission verification
- **SQL Injection Protection**: Prepared statements for all queries
- **XSS Prevention**: Proper output escaping
- **Input Sanitization**: All user input is cleaned
- **Error Logging**: Comprehensive logging for debugging

## Customization

### CSS Styling

The plugin uses Bootstrap 5 compatible classes. Override styles in your theme:

```css
/* Custom site management styles */
.wecoza-sites-list-container {
    /* Your custom styles */
}
```

### Template Override

Copy view files to your theme to customize:
```
your-theme/
└── wecoza-site-management/
    └── sites/
        ├── list.php
        ├── form.php
        └── details.php
```

### Hooks and Filters

```php
// Modify site data before save
add_filter('wecoza_site_before_save', function($site_data) {
    // Your modifications
    return $site_data;
});

// Add custom actions after site creation
add_action('wecoza_site_created', function($site_id) {
    // Your custom logic
});
```

## Troubleshooting

### Common Issues

#### Database Connection Failed
- Verify PostgreSQL credentials
- Check network connectivity
- Ensure database server is running

#### Permission Denied
- Check WordPress user roles
- Verify capability requirements
- Contact site administrator

#### JavaScript Not Working
- Ensure JavaScript is enabled
- Check browser console for errors
- Verify jQuery is loaded

### Debug Mode

Enable WordPress debug mode:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check logs at `/wp-content/debug.log`

## 📖 Documentation

- [Shortcode Documentation](SHORTCODE_DOCUMENTATION.md)
- [Usage Examples](USAGE_EXAMPLES.md)
- [Contributing Guidelines](CONTRIBUTING.md)
- [Security Policy](SECURITY.md)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

### Development Setup

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/amazing-feature`
3. Make your changes
4. Run tests: `composer test`
5. Commit your changes: `git commit -m 'Add amazing feature'`
6. Push to the branch: `git push origin feature/amazing-feature`
7. Open a Pull Request

## 🐛 Bug Reports & Feature Requests

- **Bug Reports**: [Create an issue](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues/new?template=bug_report.md)
- **Feature Requests**: [Request a feature](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues/new?template=feature_request.md)
- **Questions**: [Ask a question](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues/new?template=question.md)

## 📞 Support

For support and bug reports:

1. Check the [documentation](https://github.com/yourdesigncoza/wecoza-classes-site-management/wiki) first
2. Search [existing issues](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues)
3. Review error logs for specific issues
4. [Create a new issue](https://github.com/yourdesigncoza/wecoza-classes-site-management/issues/new) if needed

## Changelog

### Version 1.0.0
- Initial release
- Complete CRUD functionality for sites
- Client-site relationship management
- AJAX search and pagination
- Responsive design
- Security implementation
- MVC architecture
- PostgreSQL integration

## 📊 Project Stats

![GitHub repo size](https://img.shields.io/github/repo-size/yourdesigncoza/wecoza-classes-site-management)
![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/yourdesigncoza/wecoza-classes-site-management)
![GitHub last commit](https://img.shields.io/github/last-commit/yourdesigncoza/wecoza-classes-site-management)

## 🔒 Security

Security is a top priority. Please review our [Security Policy](SECURITY.md) for reporting vulnerabilities.

## 📄 License

This plugin is licensed under the [GPL v2 or later](LICENSE).

```
WeCoza Classes Site Management
Copyright (C) 2025 Your Design Co

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

## 👥 Credits

**Developed by**: [Your Design Co](https://yourdesign.co.za)
**For**: WeCoza training programs
**Maintainer**: [@yourdesigncoza](https://github.com/yourdesigncoza)

## 🌟 Show Your Support

Give a ⭐️ if this project helped you!

---

**Note**: This plugin is specifically designed for WeCoza's training program management system and requires existing PostgreSQL database infrastructure.

## 📋 Table of Contents

- [Quick Start](#-quick-start)
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [User Permissions](#user-permissions)
- [File Structure](#file-structure)
- [Security Features](#security-features)
- [Customization](#customization)
- [Troubleshooting](#troubleshooting)
- [Documentation](#-documentation)
- [Contributing](#-contributing)
- [Support](#-support)
- [License](#-license)
