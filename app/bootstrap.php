<?php

namespace WeCozaSiteManagement;

/**
 * Bootstrap file for WeCoza Site Management Plugin MVC Architecture
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin-specific constants
define('WECOZA_SITE_MANAGEMENT_PATH', dirname(__DIR__));
define('WECOZA_SITE_MANAGEMENT_APP_PATH', WECOZA_SITE_MANAGEMENT_PATH . '/app');
define('WECOZA_SITE_MANAGEMENT_CONFIG_PATH', WECOZA_SITE_MANAGEMENT_PATH . '/config');
define('WECOZA_SITE_MANAGEMENT_VIEWS_PATH', WECOZA_SITE_MANAGEMENT_APP_PATH . '/Views');

/**
 * Autoloader function for plugin classes
 */
spl_autoload_register(function ($class) {
    // Only handle our namespace
    if (strpos($class, 'WeCozaSiteManagement\\') !== 0) {
        return;
    }

    // Convert namespace to path
    $class = str_replace('WeCozaSiteManagement\\', '', $class);
    $class = str_replace('\\', '/', $class);
    $path = WECOZA_SITE_MANAGEMENT_APP_PATH . '/' . $class . '.php';

    if (file_exists($path)) {
        require_once $path;
    }
});

/**
 * Load configuration
 *
 * @param string $config_name Configuration file name
 * @return array Configuration array
 */
function config($config_name) {
    $config_file = WECOZA_SITE_MANAGEMENT_CONFIG_PATH . '/' . $config_name . '.php';
    
    if (file_exists($config_file)) {
        return require $config_file;
    }
    
    return array();
}

/**
 * Render a view template
 *
 * @param string $view View name (e.g., 'sites/list')
 * @param array $data Data to pass to the view
 * @param bool $return Whether to return the output or echo it
 * @return string|void
 */
function view($view, $data = array(), $return = true) {
    $view_file = WECOZA_SITE_MANAGEMENT_VIEWS_PATH . '/' . $view . '.php';
    
    if (!file_exists($view_file)) {
        plugin_log("View file not found: {$view_file}", 'error');
        return $return ? '' : null;
    }
    
    // Extract data to variables
    if (!empty($data)) {
        extract($data, EXTR_SKIP);
    }
    
    if ($return) {
        ob_start();
        include $view_file;
        return ob_get_clean();
    } else {
        include $view_file;
    }
}

/**
 * Load view helpers
 */
function load_view_helpers() {
    // Include the view helpers loader
    $helpers_file = WECOZA_SITE_MANAGEMENT_APP_PATH . '/Helpers/view-helpers-loader.php';
    if (file_exists($helpers_file)) {
        require_once $helpers_file;
    }
}

/**
 * Initialize application
 */
function init() {
    // Load configuration
    $config = config('app');

    // Load view helpers
    load_view_helpers();

    // Initialize controllers
    if (isset($config['controllers']) && is_array($config['controllers'])) {
        foreach ($config['controllers'] as $controller) {
            if (class_exists($controller)) {
                new $controller();
            }
        }
    }

    // Load AJAX handlers
    $ajax_handlers_file = WECOZA_SITE_MANAGEMENT_APP_PATH . '/ajax-handlers.php';
    if (file_exists($ajax_handlers_file)) {
        require_once $ajax_handlers_file;
    }
}

/**
 * Get plugin asset URL
 *
 * @param string $asset Asset path relative to assets directory
 * @return string Full URL to asset
 */
function asset_url($asset) {
    return WECOZA_SITE_MANAGEMENT_ASSETS_URL . ltrim($asset, '/');
}

/**
 * Get plugin directory path
 *
 * @param string $path Path relative to plugin directory
 * @return string Full path
 */
function plugin_path($path = '') {
    return WECOZA_SITE_MANAGEMENT_PATH . '/' . ltrim($path, '/');
}

/**
 * Check if we're in admin area
 *
 * @return bool
 */
function is_admin_area() {
    return is_admin() && !wp_doing_ajax();
}

/**
 * Check if we're doing AJAX
 *
 * @return bool
 */
function is_ajax_request() {
    return wp_doing_ajax();
}

/**
 * Sanitize input data
 *
 * @param mixed $data Data to sanitize
 * @param string $type Type of sanitization
 * @return mixed Sanitized data
 */
function sanitize_input($data, $type = 'text') {
    switch ($type) {
        case 'email':
            return sanitize_email($data);
        case 'url':
            return esc_url_raw($data);
        case 'int':
            return intval($data);
        case 'float':
            return floatval($data);
        case 'textarea':
            return sanitize_textarea_field($data);
        case 'html':
            return wp_kses_post($data);
        case 'text':
        default:
            return sanitize_text_field($data);
    }
}

/**
 * Log plugin messages
 *
 * @param string $message Message to log
 * @param string $level Log level (info, warning, error)
 */
function plugin_log($message, $level = 'info') {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("WeCoza Site Management Plugin [{$level}]: {$message}");
    }
}

/**
 * Initialize the plugin application
 */
add_action('init', function() {
    // Only initialize if not already done
    if (!defined('WECOZA_SITE_MANAGEMENT_INITIALIZED')) {
        init();
        define('WECOZA_SITE_MANAGEMENT_INITIALIZED', true);
    }
}, 10);

// Initialize immediately if called directly
if (!did_action('init')) {
    init();
    if (!defined('WECOZA_SITE_MANAGEMENT_INITIALIZED')) {
        define('WECOZA_SITE_MANAGEMENT_INITIALIZED', true);
    }
}
