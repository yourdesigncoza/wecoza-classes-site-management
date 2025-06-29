<?php
/**
 * Application configuration for WeCoza Site Management Plugin
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

return array(
    /**
     * Plugin Information
     */
    'name' => 'WeCoza Site Management Plugin',
    'version' => WECOZA_SITE_MANAGEMENT_VERSION,
    'description' => 'A comprehensive site management system for WeCoza training programs.',
    'author' => 'Your Design Co',
    'author_uri' => 'https://yourdesign.co.za',
    'text_domain' => 'wecoza-site-management',

    /**
     * Plugin Settings
     */
    'settings' => array(
        'enable_debug' => defined('WP_DEBUG') && WP_DEBUG,
        'enable_logging' => true,
        'cache_duration' => DAY_IN_SECONDS, // 24 hours (matches Redis cache expiration)
        'items_per_page' => 20,
        'max_search_results' => 100,
        'enable_search' => true,
        'enable_pagination' => true,
    ),

    /**
     * Database Configuration
     */
    'database' => array(
        'use_postgresql' => true,
        'charset' => 'utf8mb4',
        'collate' => 'utf8mb4_unicode_ci',
        // PostgreSQL connection settings (can be overridden via WordPress options)
        'postgresql' => array(
            'host' => get_option('wecoza_postgres_host', 'db-wecoza-3-do-user-17263152-0.m.db.ondigitalocean.com'),
            'port' => get_option('wecoza_postgres_port', '25060'),
            'dbname' => get_option('wecoza_postgres_dbname', 'defaultdb'),
            'user' => get_option('wecoza_postgres_user', 'doadmin'),
            'password' => get_option('wecoza_postgres_password', ''),
        ),
    ),

    /**
     * Controllers to initialize
     */
    'controllers' => array(
        'WeCozaSiteManagement\\Controllers\\SiteController',
    ),

    /**
     * Shortcodes configuration
     */
    'shortcodes' => array(
        'wecoza_sites_list' => array(
            'description' => 'Display a list of all sites with search and pagination',
            'attributes' => array(
                'per_page' => 20,
                'show_search' => true,
                'show_pagination' => true,
                'client_id' => null,
                'order_by' => 'site_name',
                'order' => 'ASC',
            ),
        ),
        'wecoza_site_form' => array(
            'description' => 'Display form for creating or editing sites',
            'attributes' => array(
                'site_id' => null,
                'redirect_url' => '',
                'show_client_selector' => true,
            ),
        ),
        'wecoza_site_details' => array(
            'description' => 'Display detailed information for a single site',
            'attributes' => array(
                'site_id' => null,
                'show_edit_link' => true,
                'show_delete_link' => true,
            ),
        ),
    ),

    /**
     * User capabilities
     */
    'capabilities' => array(
        'view_sites' => 'read',
        'create_sites' => 'edit_posts',
        'edit_sites' => 'edit_posts',
        'delete_sites' => 'delete_posts',
        'manage_sites' => 'manage_options',
    ),

    /**
     * Form validation rules
     */
    'validation' => array(
        'site_name' => array(
            'required' => true,
            'max_length' => 100,
            'min_length' => 2,
        ),
        'client_id' => array(
            'required' => true,
            'type' => 'integer',
            'min' => 1,
        ),
        'address' => array(
            'required' => false,
            'max_length' => 1000,
        ),
    ),

    /**
     * AJAX endpoints
     */
    'ajax_endpoints' => array(
        'search_sites' => array(
            'action' => 'wecoza_search_sites',
            'capability' => 'read',
            'nonce_required' => true,
        ),
        'delete_site' => array(
            'action' => 'wecoza_delete_site',
            'capability' => 'delete_posts',
            'nonce_required' => true,
        ),
        'get_site_details' => array(
            'action' => 'wecoza_get_site_details',
            'capability' => 'read',
            'nonce_required' => true,
        ),
        'save_site' => array(
            'action' => 'wecoza_save_site',
            'capability' => 'edit_posts',
            'nonce_required' => true,
        ),
    ),

    /**
     * Cache configuration for Redis object caching
     */
    'cache' => array(
        'enable_caching' => true,
        'cache_type' => 'redis_object_cache', // Redis object caching with versioning
        'cache_group' => 'wecoza_sites_debug', // Main cache group for all plugin operations
        'default_expiration' => DAY_IN_SECONDS, // 24 hours (1 day)
        'cache_versioning' => true, // Enable cache versioning for bulk invalidation
        'cache_groups' => array(
            'sites' => DAY_IN_SECONDS, // 24 hours for sites data
            'clients' => DAY_IN_SECONDS, // 24 hours for clients data
            'debug' => DAY_IN_SECONDS, // 24 hours for debug data
            'search_results' => 900, // 15 minutes for search results
        ),
        'cache_keys' => array(
            'sites_debug' => 'wecoza_sites_debug_cache',
            'sites_list' => 'wecoza_sites_list_cache',
            'clients_list' => 'wecoza_clients_list_cache',
        ),
        'legacy_support' => true, // Support for legacy transient cleanup
    ),

    /**
     * Security settings
     */
    'security' => array(
        'enable_nonces' => true,
        'nonce_lifetime' => 86400, // 24 hours
        'sanitize_inputs' => true,
        'escape_outputs' => true,
        'validate_capabilities' => true,
    ),

    /**
     * UI/UX settings
     */
    'ui' => array(
        'theme' => 'bootstrap5',
        'enable_tooltips' => true,
        'enable_modals' => true,
        'enable_animations' => true,
        'responsive_tables' => true,
    ),

    /**
     * Error handling
     */
    'error_handling' => array(
        'log_errors' => true,
        'display_errors' => defined('WP_DEBUG') && WP_DEBUG,
        'error_page_redirect' => false,
        'custom_error_messages' => array(
            'site_not_found' => 'Site not found.',
            'permission_denied' => 'You do not have permission to perform this action.',
            'validation_failed' => 'Please check your input and try again.',
            'database_error' => 'A database error occurred. Please try again later.',
        ),
    ),
);
