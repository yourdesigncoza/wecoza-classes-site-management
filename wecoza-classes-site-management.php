<?php
/**
 * Plugin Name: WeCoza Classes Site Management
 * Plugin URI: https://yourdesign.co.za/wecoza-classes-site-management
 * Description: A comprehensive site management system for WeCoza training programs. Handles site creation, management, and client-site relationships with full MVC architecture.
 * Version: 1.0.0
 * Author: Your Design Co
 * Author URI: https://yourdesign.co.za
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wecoza-site-management
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WeCozaSiteManagement
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}


if ( function_exists('wp_cache_get') && wp_using_ext_object_cache() ) {
    error_log('✅ Redis object cache IS active');
} else {
    error_log('❌ Redis object cache NOT active');
}


// wp_cache_flush(); 

// Define plugin constants
// Use datetime for development to prevent caching issues
define('WECOZA_SITE_MANAGEMENT_VERSION', date('YmdHis')); // e.g., 20250619213656
define('WECOZA_SITE_MANAGEMENT_PLUGIN_FILE', __FILE__);
define('WECOZA_SITE_MANAGEMENT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOZA_SITE_MANAGEMENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOZA_SITE_MANAGEMENT_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Define plugin paths
define('WECOZA_SITE_MANAGEMENT_INCLUDES_DIR', WECOZA_SITE_MANAGEMENT_PLUGIN_DIR . 'includes/');
define('WECOZA_SITE_MANAGEMENT_APP_DIR', WECOZA_SITE_MANAGEMENT_PLUGIN_DIR . 'app/');
define('WECOZA_SITE_MANAGEMENT_ASSETS_DIR', WECOZA_SITE_MANAGEMENT_PLUGIN_DIR . 'assets/');
define('WECOZA_SITE_MANAGEMENT_CONFIG_DIR', WECOZA_SITE_MANAGEMENT_PLUGIN_DIR . 'config/');

// Define plugin URLs
define('WECOZA_SITE_MANAGEMENT_ASSETS_URL', WECOZA_SITE_MANAGEMENT_PLUGIN_URL . 'assets/');
// define('WECOZA_SITE_MANAGEMENT_JS_URL', WECOZA_SITE_MANAGEMENT_ASSETS_URL . 'js/');
// define('WECOZA_SITE_MANAGEMENT_CSS_URL', WECOZA_SITE_MANAGEMENT_ASSETS_URL . 'css/');

/**
 * Plugin activation hook
 */
function activate_wecoza_site_management_plugin() {
    require_once WECOZA_SITE_MANAGEMENT_INCLUDES_DIR . 'class-activator.php';
    WeCoza_Site_Management_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_wecoza_site_management_plugin');

/**
 * Plugin deactivation hook
 */
function deactivate_wecoza_site_management_plugin() {
    require_once WECOZA_SITE_MANAGEMENT_INCLUDES_DIR . 'class-deactivator.php';
    WeCoza_Site_Management_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_wecoza_site_management_plugin');

/**
 * Plugin uninstall hook
 */
function uninstall_wecoza_site_management_plugin() {
    require_once WECOZA_SITE_MANAGEMENT_INCLUDES_DIR . 'class-uninstaller.php';
    WeCoza_Site_Management_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_wecoza_site_management_plugin');

/**
 * Initialize the plugin
 */
function run_wecoza_site_management_plugin() {
    // Load the main plugin class
    require_once WECOZA_SITE_MANAGEMENT_INCLUDES_DIR . 'class-wecoza-site-management-plugin.php';
    
    // Initialize the plugin
    $plugin = new WeCoza_Site_Management_Plugin();
    $plugin->run();
}

/**
 * Check if WordPress and required dependencies are loaded
 */
function wecoza_site_management_init() {
    // Check if WordPress is loaded
    if (!function_exists('add_action')) {
        return;
    }
    
    // Check for minimum WordPress version
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        add_action('admin_notices', 'wecoza_site_management_wordpress_version_notice');
        return;
    }
    
    // Check for minimum PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', 'wecoza_site_management_php_version_notice');
        return;
    }
    
    // All checks passed, run the plugin
    run_wecoza_site_management_plugin();
}

/**
 * WordPress version notice
 */
function wecoza_site_management_wordpress_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html('WeCoza Site Management requires WordPress 5.0 or higher. Please update WordPress.');
    echo '</p></div>';
}

/**
 * PHP version notice
 */
function wecoza_site_management_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html('WeCoza Site Management requires PHP 7.4 or higher. Please update PHP.');
    echo '</p></div>';
}

// Initialize the plugin
wecoza_site_management_init();
