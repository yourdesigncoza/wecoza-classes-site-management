<?php

namespace WeCozaSiteManagement;

use WeCozaSiteManagement\Models\SiteModel;
use WeCozaSiteManagement\Models\ClientModel;

/**
 * AJAX Handlers for Site Management
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX handler for live search functionality
 */
function ajax_live_search_sites() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error(__('Security check failed.', 'wecoza-site-management'));
    }
    
    // Check capabilities
    if (!current_user_can('read')) {
        wp_send_json_error(__('Permission denied.', 'wecoza-site-management'));
    }
    
    try {
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $per_page = isset($_POST['per_page']) ? max(1, min(100, intval($_POST['per_page']))) : 20;
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : null;
        
        $query_args = [
            'search' => $search,
            'page' => $page,
            'per_page' => $per_page,
            'client_id' => $client_id,
        ];
        
        $sites = SiteModel::getAll($query_args);
        $total_sites = SiteModel::getCount($query_args);
        
        // Get clients for display
        $clients = [];
        if (!empty($sites)) {
            $client_ids = array_unique(array_map(function($site) {
                return $site->getClientId();
            }, $sites));
            
            foreach ($client_ids as $client_id) {
                $client = ClientModel::find($client_id);
                if ($client) {
                    $clients[$client_id] = $client->toArray();
                }
            }
        }
        
        // Prepare response data
        $sites_data = array_map(function($site) {
            return $site->toArray();
        }, $sites);
        
        $response = [
            'sites' => $sites_data,
            'clients' => $clients,
            'total' => $total_sites,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total_sites / $per_page),
            'search' => $search,
        ];
        
        wp_send_json_success($response);
        
    } catch (\Exception $e) {
        plugin_log('AJAX live search error: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Search failed. Please try again.', 'wecoza-site-management'));
    }
}

/**
 * AJAX handler for getting client sites
 */
function ajax_get_client_sites() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error(__('Security check failed.', 'wecoza-site-management'));
    }
    
    // Check capabilities
    if (!current_user_can('read')) {
        wp_send_json_error(__('Permission denied.', 'wecoza-site-management'));
    }
    
    try {
        $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
        
        if (!$client_id) {
            wp_send_json_error(__('Invalid client ID.', 'wecoza-site-management'));
        }
        
        $sites = SiteModel::getByClientId($client_id);
        $client = ClientModel::find($client_id);
        
        $sites_data = array_map(function($site) {
            return $site->toArray();
        }, $sites);
        
        $response = [
            'client' => $client ? $client->toArray() : null,
            'sites' => $sites_data,
            'count' => count($sites_data),
        ];
        
        wp_send_json_success($response);
        
    } catch (\Exception $e) {
        plugin_log('AJAX get client sites error: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Failed to load client sites.', 'wecoza-site-management'));
    }
}

/**
 * AJAX handler for bulk operations
 */
function ajax_bulk_site_operations() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error(__('Security check failed.', 'wecoza-site-management'));
    }
    
    // Check capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission denied.', 'wecoza-site-management'));
    }
    
    try {
        $operation = isset($_POST['operation']) ? sanitize_text_field($_POST['operation']) : '';
        $site_ids = isset($_POST['site_ids']) ? array_map('intval', $_POST['site_ids']) : [];
        
        if (empty($site_ids)) {
            wp_send_json_error(__('No sites selected.', 'wecoza-site-management'));
        }
        
        $results = [];
        $success_count = 0;
        $error_count = 0;
        
        switch ($operation) {
            case 'delete':
                if (!current_user_can('delete_posts')) {
                    wp_send_json_error(__('Permission denied for delete operation.', 'wecoza-site-management'));
                }
                
                foreach ($site_ids as $site_id) {
                    $site = SiteModel::find($site_id);
                    if ($site) {
                        if ($site->delete()) {
                            $success_count++;
                            $results[$site_id] = 'success';
                        } else {
                            $error_count++;
                            $results[$site_id] = 'error';
                        }
                    } else {
                        $error_count++;
                        $results[$site_id] = 'not_found';
                    }
                }
                break;
                
            default:
                wp_send_json_error(__('Invalid operation.', 'wecoza-site-management'));
        }
        
        $message = sprintf(
            __('%d sites processed successfully, %d errors.', 'wecoza-site-management'),
            $success_count,
            $error_count
        );
        
        wp_send_json_success([
            'message' => $message,
            'success_count' => $success_count,
            'error_count' => $error_count,
            'results' => $results,
        ]);
        
    } catch (\Exception $e) {
        plugin_log('AJAX bulk operations error: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Bulk operation failed. Please try again.', 'wecoza-site-management'));
    }
}

/**
 * AJAX handler for site validation
 */
function ajax_validate_site() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error(__('Security check failed.', 'wecoza-site-management'));
    }
    
    // Check capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(__('Permission denied.', 'wecoza-site-management'));
    }
    
    try {
        $form_data = [
            'site_id' => isset($_POST['site_id']) ? intval($_POST['site_id']) : null,
            'client_id' => isset($_POST['client_id']) ? intval($_POST['client_id']) : null,
            'site_name' => isset($_POST['site_name']) ? sanitize_text_field($_POST['site_name']) : '',
            'address' => isset($_POST['address']) ? sanitize_textarea_field($_POST['address']) : '',
        ];
        
        // Create temporary site model for validation
        $site = new SiteModel();
        $site->fill($form_data);
        
        // Validate
        $validation_errors = $site->validate();
        
        // Check for duplicate site name within client
        if (empty($validation_errors['site_name']) && !empty($form_data['site_name']) && !empty($form_data['client_id'])) {
            if (SiteModel::siteNameExists($form_data['site_name'], $form_data['client_id'], $form_data['site_id'])) {
                $validation_errors['site_name'] = __('A site with this name already exists for this client.', 'wecoza-site-management');
            }
        }
        
        if (empty($validation_errors)) {
            wp_send_json_success(__('Validation passed.', 'wecoza-site-management'));
        } else {
            wp_send_json_error([
                'message' => __('Validation failed.', 'wecoza-site-management'),
                'errors' => $validation_errors
            ]);
        }
        
    } catch (\Exception $e) {
        plugin_log('AJAX validation error: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Validation failed. Please try again.', 'wecoza-site-management'));
    }
}

/**
 * AJAX handler for getting site statistics
 */
function ajax_get_site_statistics() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error(__('Security check failed.', 'wecoza-site-management'));
    }
    
    // Check capabilities
    if (!current_user_can('read')) {
        wp_send_json_error(__('Permission denied.', 'wecoza-site-management'));
    }
    
    try {
        // Get overall statistics
        $total_sites = SiteModel::getCount();
        $total_clients = count(ClientModel::getAll());
        
        // Get recent sites
        $recent_sites = SiteModel::getAll(['per_page' => 5, 'order_by' => 'created_at', 'order' => 'DESC']);
        
        $recent_sites_data = array_map(function($site) {
            return $site->toArray();
        }, $recent_sites);
        
        $response = [
            'total_sites' => $total_sites,
            'total_clients' => $total_clients,
            'recent_sites' => $recent_sites_data,
            'generated_at' => current_time('mysql'),
        ];
        
        wp_send_json_success($response);
        
    } catch (\Exception $e) {
        plugin_log('AJAX statistics error: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Failed to load statistics.', 'wecoza-site-management'));
    }
}

// Register AJAX handlers
add_action('wp_ajax_wecoza_live_search_sites', __NAMESPACE__ . '\\ajax_live_search_sites');
add_action('wp_ajax_nopriv_wecoza_live_search_sites', __NAMESPACE__ . '\\ajax_live_search_sites');

add_action('wp_ajax_wecoza_get_client_sites', __NAMESPACE__ . '\\ajax_get_client_sites');
add_action('wp_ajax_nopriv_wecoza_get_client_sites', __NAMESPACE__ . '\\ajax_get_client_sites');

add_action('wp_ajax_wecoza_bulk_site_operations', __NAMESPACE__ . '\\ajax_bulk_site_operations');

add_action('wp_ajax_wecoza_validate_site', __NAMESPACE__ . '\\ajax_validate_site');

add_action('wp_ajax_wecoza_get_site_statistics', __NAMESPACE__ . '\\ajax_get_site_statistics');
add_action('wp_ajax_nopriv_wecoza_get_site_statistics', __NAMESPACE__ . '\\ajax_get_site_statistics');
