<?php

namespace WeCozaSiteManagement\Controllers;

use WeCozaSiteManagement\Models\SiteModel;
use WeCozaSiteManagement\Models\ClientModel;

/**
 * Site Controller for handling site-related operations
 *
 * @package WeCozaSiteManagement\Controllers
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SiteController {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register WordPress hooks
        add_action('init', [$this, 'registerShortcodes']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
        
        // Register AJAX handlers
        add_action('wp_ajax_wecoza_search_sites', [$this, 'ajaxSearchSites']);
        add_action('wp_ajax_nopriv_wecoza_search_sites', [$this, 'ajaxSearchSites']);
        add_action('wp_ajax_wecoza_delete_site', [$this, 'ajaxDeleteSite']);
        add_action('wp_ajax_wecoza_get_site_details', [$this, 'ajaxGetSiteDetails']);
        add_action('wp_ajax_wecoza_save_site', [$this, 'ajaxSaveSite']);
    }
    
    /**
     * Register all site-related shortcodes
     */
    public function registerShortcodes() {
        add_shortcode('wecoza_sites_list', [$this, 'sitesListShortcode']);
        add_shortcode('wecoza_site_form', [$this, 'siteFormShortcode']);
        add_shortcode('wecoza_site_details', [$this, 'siteDetailsShortcode']);
    }
    
    /**
     * Enqueue assets for site management
     */
    public function enqueueAssets() {
        global $post;
        
        // Only enqueue on pages that use our shortcodes
        if (is_a($post, 'WP_Post') && (
            has_shortcode($post->post_content, 'wecoza_sites_list') ||
            has_shortcode($post->post_content, 'wecoza_site_form') ||
            has_shortcode($post->post_content, 'wecoza_site_details')
        )) {
            wp_enqueue_style(
                'wecoza-site-management',
                \WeCozaSiteManagement\asset_url('css/sites-management.css'),
                [],
                WECOZA_SITE_MANAGEMENT_VERSION
            );
            
            wp_enqueue_script(
                'wecoza-site-management',
                \WeCozaSiteManagement\asset_url('js/sites-management.js'),
                ['jquery'],
                WECOZA_SITE_MANAGEMENT_VERSION,
                true
            );

            // Enqueue AJAX pagination script for sites list
            wp_enqueue_script(
                'wecoza-sites-pagination',
                \WeCozaSiteManagement\asset_url('js/sites-table-pagination.js'),
                ['jquery', 'wecoza-site-management'],
                WECOZA_SITE_MANAGEMENT_VERSION,
                true
            );
            
            // Localize script for AJAX
            wp_localize_script(
                'wecoza-site-management',
                'wecoza_site_management_ajax',
                [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('wecoza_site_management_nonce'),
                    'messages' => [
                        'confirm_delete' => __('Are you sure you want to delete this site?', 'wecoza-site-management'),
                        'error_occurred' => __('An error occurred. Please try again.', 'wecoza-site-management'),
                        'success_saved' => __('Site saved successfully!', 'wecoza-site-management'),
                        'success_deleted' => __('Site deleted successfully!', 'wecoza-site-management'),
                        'loading' => __('Loading...', 'wecoza-site-management'),
                        'no_results' => __('No sites found.', 'wecoza-site-management'),
                    ]
                ]
            );
        }
    }
    
    /**
     * Sites list shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function sitesListShortcode($atts) {
        // Check user capabilities
        if (!current_user_can('read')) {
            return '<div class="alert alert-subtle-warning">' . 
                   __('You do not have permission to view sites.', 'wecoza-site-management') . 
                   '</div>';
        }
        
        // Process shortcode attributes
        $atts = shortcode_atts([
            'per_page' => 20,
            'show_search' => true,
            'show_pagination' => true,
            'client_id' => null,
            'order_by' => 'site_name',
            'order' => 'ASC',
        ], $atts);
        
        // Get current page
        $current_page = isset($_GET['sites_page']) ? max(1, intval($_GET['sites_page'])) : 1;
        
        // Get search term
        $search = isset($_GET['sites_search']) ? sanitize_text_field($_GET['sites_search']) : '';
        
        // Prepare query arguments
        $query_args = [
            'per_page' => intval($atts['per_page']),
            'page' => $current_page,
            'search' => $search,
            'client_id' => $atts['client_id'] ? intval($atts['client_id']) : null,
            'order_by' => $atts['order_by'],
            'order' => $atts['order'],
        ];
        
        try {
            // Get sites and total count
            $sites = SiteModel::getAll($query_args);
            $total_sites = SiteModel::getCount($query_args);

            // Get unique client count from entire filtered dataset
            $unique_clients = SiteModel::getUniqueClientCount($query_args);

            // Calculate pagination
            $total_pages = ceil($total_sites / $query_args['per_page']);
            
            // Get clients for display
            $clients = [];
            if (!empty($sites)) {
                $client_ids = array_unique(array_map(function($site) {
                    return $site->getClientId();
                }, $sites));
                
                foreach ($client_ids as $client_id) {
                    $client = ClientModel::find($client_id);
                    if ($client) {
                        $clients[$client_id] = $client;
                    }
                }
            }
            
            // Prepare view data
            $view_data = [
                'sites' => $sites,
                'clients' => $clients,
                'total_sites' => $total_sites,
                'unique_clients' => $unique_clients,
                'current_page' => $current_page,
                'total_pages' => $total_pages,
                'per_page' => $query_args['per_page'],
                'search' => $search,
                'show_search' => filter_var($atts['show_search'], FILTER_VALIDATE_BOOLEAN),
                'show_pagination' => filter_var($atts['show_pagination'], FILTER_VALIDATE_BOOLEAN),
                'can_edit' => current_user_can('edit_posts'),
                'can_delete' => current_user_can('delete_posts'),
            ];
            
            // Render the view
            return \WeCozaSiteManagement\view('sites/list', $view_data);
            
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error in sitesListShortcode: ' . $e->getMessage(), 'error');
            return '<div class="alert alert-subtle-danger">' . 
                   __('Error loading sites. Please try again later.', 'wecoza-site-management') . 
                   '</div>';
        }
    }
    
    /**
     * Site form shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function siteFormShortcode($atts) {
        // Check user capabilities
        if (!current_user_can('edit_posts')) {
            return '<div class="alert alert-subtle-warning">' . 
                   __('You do not have permission to create or edit sites.', 'wecoza-site-management') . 
                   '</div>';
        }
        
        // Process shortcode attributes
        $atts = shortcode_atts([
            'site_id' => null,
            'redirect_url' => '',
            'show_client_selector' => true,
        ], $atts);
        
        // Get site ID from URL parameter if not provided in shortcode
        $site_id = $atts['site_id'] ? intval($atts['site_id']) : 
                   (isset($_GET['site_id']) ? intval($_GET['site_id']) : null);
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wecoza_site_nonce'])) {
            return $this->handleFormSubmission($atts);
        }
        
        try {
            // Load site data if editing
            $site = null;
            if ($site_id) {
                $site = SiteModel::find($site_id);
                if (!$site) {
                    return '<div class="alert alert-subtle-danger">' . 
                           __('Site not found.', 'wecoza-site-management') . 
                           '</div>';
                }
            }
            
            // Get clients for dropdown
            $clients = ClientModel::getAsOptions();
            
            // Prepare view data
            $view_data = [
                'site' => $site,
                'clients' => $clients,
                'is_edit' => !empty($site),
                'show_client_selector' => filter_var($atts['show_client_selector'], FILTER_VALIDATE_BOOLEAN),
                'redirect_url' => $atts['redirect_url'],
                'nonce' => wp_create_nonce('wecoza_site_management_nonce'),
                'errors' => [],
                'form_data' => [],
            ];
            
            // Render the view
            return \WeCozaSiteManagement\view('sites/form', $view_data);
            
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error in siteFormShortcode: ' . $e->getMessage(), 'error');
            return '<div class="alert alert-subtle-danger">' . 
                   __('Error loading form. Please try again later.', 'wecoza-site-management') . 
                   '</div>';
        }
    }
    
    /**
     * Site details shortcode handler
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function siteDetailsShortcode($atts) {
        // Check user capabilities
        if (!current_user_can('read')) {
            return '<div class="alert alert-subtle-warning">' . 
                   __('You do not have permission to view site details.', 'wecoza-site-management') . 
                   '</div>';
        }
        
        // Process shortcode attributes
        $atts = shortcode_atts([
            'site_id' => null,
            'show_edit_link' => true,
            'show_delete_link' => true,
        ], $atts);
        
        // Get site ID from URL parameter if not provided in shortcode
        $site_id = $atts['site_id'] ? intval($atts['site_id']) : 
                   (isset($_GET['site_id']) ? intval($_GET['site_id']) : null);
        
        if (!$site_id) {
            return '<div class="alert alert-subtle-warning">' . 
                   __('No site ID provided.', 'wecoza-site-management') . 
                   '</div>';
        }
        
        try {
            // Load site data
            $site = SiteModel::find($site_id);
            if (!$site) {
                return '<div class="alert alert-subtle-danger">' . 
                       __('Site not found.', 'wecoza-site-management') . 
                       '</div>';
            }
            
            // Load client data
            $client = ClientModel::find($site->getClientId());
            
            // Prepare view data
            $view_data = [
                'site' => $site,
                'client' => $client,
                'show_edit_link' => filter_var($atts['show_edit_link'], FILTER_VALIDATE_BOOLEAN) && current_user_can('edit_posts'),
                'show_delete_link' => filter_var($atts['show_delete_link'], FILTER_VALIDATE_BOOLEAN) && current_user_can('delete_posts'),
                'can_edit' => current_user_can('edit_posts'),
                'can_delete' => current_user_can('delete_posts'),
            ];
            
            // Render the view
            return \WeCozaSiteManagement\view('sites/details', $view_data);
            
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error in siteDetailsShortcode: ' . $e->getMessage(), 'error');
            return '<div class="alert alert-subtle-danger">' .
                   __('Error loading site details. Please try again later.', 'wecoza-site-management') .
                   '</div>';
        }
    }

    /**
     * Handle form submission
     *
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    private function handleFormSubmission($atts) {
        // Verify nonce
        if (!wp_verify_nonce($_POST['wecoza_site_nonce'], 'wecoza_site_management_nonce')) {
            return '<div class="alert alert-subtle-danger">' .
                   __('Security check failed. Please try again.', 'wecoza-site-management') .
                   '</div>';
        }

        // Get form data
        $form_data = [
            'site_id' => isset($_POST['site_id']) ? intval($_POST['site_id']) : null,
            'client_id' => isset($_POST['client_id']) ? intval($_POST['client_id']) : null,
            'site_name' => isset($_POST['site_name']) ? sanitize_text_field($_POST['site_name']) : '',
            'address' => isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '',
        ];

        try {
            // Create or load site model
            $site = $form_data['site_id'] ? SiteModel::find($form_data['site_id']) : new SiteModel();

            if ($form_data['site_id'] && !$site) {
                return '<div class="alert alert-subtle-danger">' .
                       __('Site not found.', 'wecoza-site-management') .
                       '</div>';
            }

            // Fill site with form data
            $site->fill($form_data);

            // Validate and save
            $validation_errors = $site->validate();
            if (!empty($validation_errors)) {
                // Re-render form with errors
                $clients = ClientModel::getAsOptions();
                $view_data = [
                    'site' => $site,
                    'clients' => $clients,
                    'is_edit' => !empty($form_data['site_id']),
                    'show_client_selector' => filter_var($atts['show_client_selector'], FILTER_VALIDATE_BOOLEAN),
                    'redirect_url' => $atts['redirect_url'],
                    'nonce' => wp_create_nonce('wecoza_site_management_nonce'),
                    'errors' => $validation_errors,
                    'form_data' => $form_data,
                ];

                return \WeCozaSiteManagement\view('sites/form', $view_data);
            }

            // Save site
            $result = $site->save();
            if ($result) {
                $success_message = $form_data['site_id'] ?
                    __('Site updated successfully!', 'wecoza-site-management') :
                    __('Site created successfully!', 'wecoza-site-management');

                // Redirect if URL provided
                if (!empty($atts['redirect_url'])) {
                    wp_redirect($atts['redirect_url']);
                    exit;
                }

                return '<div class="alert alert-subtle-success">' . $success_message . '</div>';
            } else {
                return '<div class="alert alert-subtle-danger">' .
                       __('Failed to save site. Please try again.', 'wecoza-site-management') .
                       '</div>';
            }

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error handling form submission: ' . $e->getMessage(), 'error');
            return '<div class="alert alert-subtle-danger">' .
                   __('An error occurred while saving. Please try again.', 'wecoza-site-management') .
                   '</div>';
        }
    }

    /**
     * AJAX handler for searching sites
     */
    public function ajaxSearchSites() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
            wp_die(__('Security check failed.', 'wecoza-site-management'));
        }

        // Check capabilities
        if (!current_user_can('read')) {
            wp_die(__('Permission denied.', 'wecoza-site-management'));
        }

        try {
            $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
            $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
            $per_page = isset($_POST['per_page']) ? max(1, intval($_POST['per_page'])) : 20;
            $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : null;

            $query_args = [
                'search' => $search,
                'page' => $page,
                'per_page' => $per_page,
                'client_id' => $client_id,
            ];

            $sites = SiteModel::getAll($query_args);
            $total_sites = SiteModel::getCount($query_args);
            $unique_clients = SiteModel::getUniqueClientCount($query_args);

            // Get clients for display
            $clients = [];
            if (!empty($sites)) {
                $client_ids = array_unique(array_map(function($site) {
                    return $site->getClientId();
                }, $sites));

                foreach ($client_ids as $client_id) {
                    $client = ClientModel::find($client_id);
                    if ($client) {
                        $clients[$client_id] = $client;
                    }
                }
            }

            $response = [
                'success' => true,
                'data' => [
                    'sites' => array_map(function($site) { return $site->toArray(); }, $sites),
                    'clients' => array_map(function($client) { return $client->toArray(); }, $clients),
                    'total' => $total_sites,
                    'unique_clients' => $unique_clients,
                    'page' => $page,
                    'per_page' => $per_page,
                    'total_pages' => ceil($total_sites / $per_page),
                ]
            ];

            wp_send_json($response);

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('AJAX search error: ' . $e->getMessage(), 'error');
            wp_send_json_error(__('Search failed. Please try again.', 'wecoza-site-management'));
        }
    }

    /**
     * AJAX handler for deleting sites
     */
    public function ajaxDeleteSite() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
            wp_die(__('Security check failed.', 'wecoza-site-management'));
        }

        // Check capabilities
        if (!current_user_can('delete_posts')) {
            wp_die(__('Permission denied.', 'wecoza-site-management'));
        }

        try {
            $site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;

            if (!$site_id) {
                wp_send_json_error(__('Invalid site ID.', 'wecoza-site-management'));
            }

            $site = SiteModel::find($site_id);
            if (!$site) {
                wp_send_json_error(__('Site not found.', 'wecoza-site-management'));
            }

            $success = $site->delete();

            if ($success) {
                wp_send_json_success(__('Site deleted successfully.', 'wecoza-site-management'));
            } else {
                wp_send_json_error(__('Failed to delete site.', 'wecoza-site-management'));
            }

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('AJAX delete error: ' . $e->getMessage(), 'error');
            wp_send_json_error(__('Delete failed. Please try again.', 'wecoza-site-management'));
        }
    }

    /**
     * AJAX handler for getting site details
     */
    public function ajaxGetSiteDetails() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
            wp_die(__('Security check failed.', 'wecoza-site-management'));
        }

        // Check capabilities
        if (!current_user_can('read')) {
            wp_die(__('Permission denied.', 'wecoza-site-management'));
        }

        try {
            $site_id = isset($_POST['site_id']) ? intval($_POST['site_id']) : 0;

            if (!$site_id) {
                wp_send_json_error(__('Invalid site ID.', 'wecoza-site-management'));
            }

            $site = SiteModel::find($site_id);
            if (!$site) {
                wp_send_json_error(__('Site not found.', 'wecoza-site-management'));
            }

            $client = ClientModel::find($site->getClientId());

            $response = [
                'site' => $site->toArray(),
                'client' => $client ? $client->toArray() : null,
            ];

            wp_send_json_success($response);

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('AJAX get details error: ' . $e->getMessage(), 'error');
            wp_send_json_error(__('Failed to load site details.', 'wecoza-site-management'));
        }
    }

    /**
     * AJAX handler for saving sites
     */
    public function ajaxSaveSite() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
            wp_die(__('Security check failed.', 'wecoza-site-management'));
        }

        // Check capabilities
        if (!current_user_can('edit_posts')) {
            wp_die(__('Permission denied.', 'wecoza-site-management'));
        }

        try {
            $form_data = [
                'site_id' => isset($_POST['site_id']) ? intval($_POST['site_id']) : null,
                'client_id' => isset($_POST['client_id']) ? intval($_POST['client_id']) : null,
                'site_name' => isset($_POST['site_name']) ? sanitize_text_field($_POST['site_name']) : '',
                'address' => isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '',
            ];

            // Create or load site model
            $site = $form_data['site_id'] ? SiteModel::find($form_data['site_id']) : new SiteModel();

            if ($form_data['site_id'] && !$site) {
                wp_send_json_error(__('Site not found.', 'wecoza-site-management'));
            }

            // Fill site with form data
            $site->fill($form_data);

            // Validate
            $validation_errors = $site->validate();
            if (!empty($validation_errors)) {
                wp_send_json_error([
                    'message' => __('Validation failed.', 'wecoza-site-management'),
                    'errors' => $validation_errors
                ]);
            }

            // Save site
            $result = $site->save();
            if ($result) {
                $message = $form_data['site_id'] ?
                    __('Site updated successfully!', 'wecoza-site-management') :
                    __('Site created successfully!', 'wecoza-site-management');

                wp_send_json_success([
                    'message' => $message,
                    'site' => $site->toArray()
                ]);
            } else {
                wp_send_json_error(__('Failed to save site.', 'wecoza-site-management'));
            }

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('AJAX save error: ' . $e->getMessage(), 'error');
            wp_send_json_error(__('Save failed. Please try again.', 'wecoza-site-management'));
        }
    }
}
