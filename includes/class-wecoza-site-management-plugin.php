<?php

/**
 * Main plugin class
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main WeCoza Site Management Plugin class
 */
class WeCoza_Site_Management_Plugin {

    /**
     * Plugin version
     *
     * @var string
     */
    protected $version;

    /**
     * Plugin name
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * Constructor
     */
    public function __construct() {
        $this->version = WECOZA_SITE_MANAGEMENT_VERSION;
        $this->plugin_name = 'wecoza-site-management';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load required dependencies
     */
    private function load_dependencies() {
        // Load the autoloader and bootstrap
        require_once WECOZA_SITE_MANAGEMENT_APP_DIR . 'bootstrap.php';
    }

    /**
     * Define admin hooks
     */
    private function define_admin_hooks() {
        // Admin-specific hooks can be added here if needed in the future
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Define public hooks
     */
    private function define_public_hooks() {
        // Initialize shortcodes and AJAX handlers
        add_action('init', array($this, 'init_plugin_features'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }

    /**
     * Initialize plugin features
     */
    public function init_plugin_features() {
        // This will be called by the bootstrap to initialize controllers
        // Controllers will register their own shortcodes and AJAX handlers
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on relevant admin pages
        if (strpos($hook, 'wecoza-site-management') !== false) {
            wp_enqueue_style(
                'wecoza-site-management-admin',
                WECOZA_SITE_MANAGEMENT_CSS_URL . 'admin.css',
                array(),
                $this->version
            );

            wp_enqueue_script(
                'wecoza-site-management-admin',
                WECOZA_SITE_MANAGEMENT_JS_URL . 'admin.js',
                array('jquery'),
                $this->version,
                true
            );
        }
    }

    /**
     * Enqueue public assets
     */
    public function enqueue_public_assets() {
        // Only enqueue on pages that use our shortcodes
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'wecoza_sites_list') ||
            has_shortcode($post->post_content, 'wecoza_site_form') ||
            has_shortcode($post->post_content, 'wecoza_site_details')) {
            
            wp_enqueue_style(
                'wecoza-site-management',
                WECOZA_SITE_MANAGEMENT_CSS_URL . 'sites-management.css',
                array(),
                $this->version
            );

            wp_enqueue_script(
                'wecoza-site-management',
                WECOZA_SITE_MANAGEMENT_JS_URL . 'sites-management.js',
                array('jquery'),
                $this->version,
                true
            );

            // Localize script for AJAX
            wp_localize_script(
                'wecoza-site-management',
                'wecoza_site_management_ajax',
                array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wecoza_site_management_nonce'),
                    'messages' => array(
                        'confirm_delete' => __('Are you sure you want to delete this site?', 'wecoza-site-management'),
                        'error_occurred' => __('An error occurred. Please try again.', 'wecoza-site-management'),
                        'success_saved' => __('Site saved successfully!', 'wecoza-site-management'),
                        'success_deleted' => __('Site deleted successfully!', 'wecoza-site-management'),
                    )
                )
            );
        }
    }

    /**
     * Run the plugin
     */
    public function run() {
        // Plugin is now running
        do_action('wecoza_site_management_plugin_loaded');
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Get plugin name
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }
}
