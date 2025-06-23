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
    // DISABLED FOR DEBUGGING - Search functionality temporarily disabled
    wp_send_json_error('Search functionality temporarily disabled for debugging.');
}

/**
 * AJAX handler for getting client sites
 */
function ajax_get_client_sites() {
    // DISABLED FOR DEBUGGING - Client sites loading temporarily disabled
    wp_send_json_error('Client sites loading temporarily disabled for debugging.');
}

/**
 * AJAX handler for bulk operations
 */
function ajax_bulk_site_operations() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error('Security check failed.');
    }
    
    // Check capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied.');
    }
    
    try {
        $operation = isset($_POST['operation']) ? sanitize_text_field($_POST['operation']) : '';
        $site_ids = isset($_POST['site_ids']) ? array_map('intval', $_POST['site_ids']) : [];
        
        if (empty($site_ids)) {
            wp_send_json_error('No sites selected.');
        }
        
        $results = [];
        $success_count = 0;
        $error_count = 0;
        
        switch ($operation) {
            case 'delete':
                if (!current_user_can('delete_posts')) {
                    wp_send_json_error('Permission denied for delete operation.');
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
                wp_send_json_error('Invalid operation.');
        }
        
        $message = sprintf(
            '%d sites processed successfully, %d errors.',
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
        wp_send_json_error('Bulk operation failed. Please try again.');
    }
}

/**
 * AJAX handler for site validation
 */
function ajax_validate_site() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wecoza_site_management_nonce')) {
        wp_send_json_error('Security check failed.');
    }
    
    // Check capabilities
    if (!current_user_can('edit_posts')) {
        wp_send_json_error('Permission denied.');
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
                $validation_errors['site_name'] = 'A site with this name already exists for this client.';
            }
        }
        
        if (empty($validation_errors)) {
            wp_send_json_success('Validation passed.');
        } else {
            wp_send_json_error([
                'message' => 'Validation failed.',
                'errors' => $validation_errors
            ]);
        }
        
    } catch (\Exception $e) {
        plugin_log('AJAX validation error: ' . $e->getMessage(), 'error');
        wp_send_json_error('Validation failed. Please try again.');
    }
}

/**
 * AJAX handler for getting site statistics
 */
function ajax_get_site_statistics() {
    // DISABLED FOR DEBUGGING - Statistics loading temporarily disabled
    wp_send_json_error('Statistics loading temporarily disabled for debugging.');
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
