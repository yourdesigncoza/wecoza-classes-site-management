<?php

/**
 * Plugin activation handler
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WeCoza Site Management Activator class
 */
class WeCoza_Site_Management_Activator {

    /**
     * Activate the plugin
     *
     * @since 1.0.0
     */
    public static function activate() {
        // Check for minimum requirements
        self::check_requirements();

        // Create database tables
        self::create_database_tables();

        // Set default options
        self::set_default_options();

        // Create upload directories
        self::create_upload_directories();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Set activation flag
        update_option('wecoza_site_management_plugin_activated', true);
        update_option('wecoza_site_management_plugin_version', WECOZA_SITE_MANAGEMENT_VERSION);

        // Log activation
        error_log('WeCoza Site Management Plugin activated successfully');
    }

    /**
     * Check minimum requirements
     *
     * @since 1.0.0
     */
    private static function check_requirements() {
        // Check WordPress version
        if (version_compare(get_bloginfo('version'), '5.0', '<')) {
            deactivate_plugins(WECOZA_SITE_MANAGEMENT_PLUGIN_BASENAME);
            wp_die(__('WeCoza Site Management requires WordPress 5.0 or higher.', 'wecoza-site-management'));
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.4', '<')) {
            deactivate_plugins(WECOZA_SITE_MANAGEMENT_PLUGIN_BASENAME);
            wp_die(__('WeCoza Site Management requires PHP 7.4 or higher.', 'wecoza-site-management'));
        }
    }

    /**
     * Create database tables
     *
     * @since 1.0.0
     */
    private static function create_database_tables() {
        // Load the bootstrap to access our classes
        require_once WECOZA_SITE_MANAGEMENT_APP_DIR . 'bootstrap.php';

        // Run database migrations
        try {
            $migration_service = new \WeCozaSiteManagement\Services\MigrationService();
            $success = $migration_service->runMigrations();

            if ($success) {
                error_log('WeCoza Site Management: Database migrations completed successfully');
            } else {
                error_log('WeCoza Site Management: Database migrations failed');
            }
        } catch (\Exception $e) {
            error_log('WeCoza Site Management: Migration error: ' . $e->getMessage());
        }

        // Set database version
        update_option('wecoza_site_management_db_version', '1.0.0');
    }

    /**
     * Set default plugin options
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        // Default plugin settings
        $default_options = array(
            'enable_debug' => false,
            'enable_logging' => true,
            'items_per_page' => 20,
            'enable_search' => true,
            'enable_pagination' => true,
            'default_capability' => 'manage_options',
        );

        // Set default options if they don't exist
        foreach ($default_options as $option_name => $default_value) {
            $full_option_name = 'wecoza_site_management_' . $option_name;
            if (get_option($full_option_name) === false) {
                update_option($full_option_name, $default_value);
            }
        }
    }

    /**
     * Create upload directories
     *
     * @since 1.0.0
     */
    private static function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/wecoza-site-management';

        // Create plugin upload directory
        if (!file_exists($plugin_upload_dir)) {
            wp_mkdir_p($plugin_upload_dir);
            
            // Create .htaccess file for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            file_put_contents($plugin_upload_dir . '/.htaccess', $htaccess_content);
            
            // Create index.php file for security
            file_put_contents($plugin_upload_dir . '/index.php', '<?php // Silence is golden');
        }
    }
}
