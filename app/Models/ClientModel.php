<?php

namespace WeCozaSiteManagement\Models;

use WeCozaSiteManagement\Services\DatabaseService;

/**
 * Client Model for handling client data and relationships
 *
 * @package WeCozaSiteManagement\Models
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class ClientModel {
    
    /**
     * Client ID
     *
     * @var int
     */
    private $client_id;
    
    /**
     * Client name
     *
     * @var string
     */
    private $client_name;
    
    /**
     * Constructor
     *
     * @param array $data Client data
     */
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->fill($data);
        }
    }
    
    /**
     * Fill model with data
     *
     * @param array $data
     */
    public function fill($data) {
        $this->client_id = isset($data['client_id']) ? intval($data['client_id']) : null;
        $this->client_name = isset($data['client_name']) ? sanitize_text_field($data['client_name']) : '';
    }
    
    /**
     * Find client by ID
     *
     * @param int $client_id
     * @return ClientModel|null
     */
    public static function find($client_id) {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT * FROM clients WHERE client_id = ?";
            $data = $db->fetchRow($sql, [$client_id]);
            
            return $data ? new self($data) : null;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error finding client: ' . $e->getMessage(), 'error');
            return null;
        }
    }
    
    /**
     * Get all clients
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function getAll($args = []) {
        $defaults = [
            'order_by' => 'client_name',
            'order' => 'ASC',
            'search' => '',
        ];
        
        $args = array_merge($defaults, $args);
        
        try {
            $db = DatabaseService::getInstance();
            $where_conditions = [];
            $params = [];
            
            // Add search condition
            if (!empty($args['search'])) {
                $search = $db->escapeLike($args['search']);
                $where_conditions[] = "client_name ILIKE ?";
                $params[] = "%{$search}%";
            }
            
            // Build WHERE clause
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
            
            // Build ORDER BY clause
            $allowed_order_by = ['client_id', 'client_name'];
            $order_by = in_array($args['order_by'], $allowed_order_by) ? $args['order_by'] : 'client_name';
            $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
            
            $sql = "SELECT * FROM clients {$where_clause} ORDER BY {$order_by} {$order}";
            $results = $db->fetchAll($sql, $params);
            
            // Convert to ClientModel objects
            $clients = [];
            foreach ($results as $row) {
                $clients[] = new self($row);
            }
            
            return $clients;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error getting clients: ' . $e->getMessage(), 'error');
            return [];
        }
    }
    
    /**
     * Get clients as options array for select dropdowns
     *
     * @return array
     */
    public static function getAsOptions() {
        $clients = self::getAll();
        $options = [];
        
        foreach ($clients as $client) {
            $options[$client->getClientId()] = $client->getClientName();
        }
        
        return $options;
    }
    
    /**
     * Get client with their sites
     *
     * @param int $client_id
     * @return array|null
     */
    public static function getWithSites($client_id) {
        $client = self::find($client_id);
        if (!$client) {
            return null;
        }
        
        $sites = SiteModel::getByClientId($client_id);
        
        return [
            'client' => $client,
            'sites' => $sites,
            'site_count' => count($sites)
        ];
    }
    
    /**
     * Check if client exists
     *
     * @param int $client_id
     * @return bool
     */
    public static function exists($client_id) {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT COUNT(*) as count FROM clients WHERE client_id = ?";
            $result = $db->fetchRow($sql, [$client_id]);
            
            return intval($result['count']) > 0;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error checking client existence: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Get client statistics
     *
     * @param int $client_id
     * @return array
     */
    public static function getStatistics($client_id) {
        try {
            $db = DatabaseService::getInstance();
            
            // Get site count
            $sql = "SELECT COUNT(*) as site_count FROM sites WHERE client_id = ?";
            $result = $db->fetchRow($sql, [$client_id]);
            $site_count = intval($result['site_count']);
            
            // Get most recent site
            $sql = "SELECT site_name, created_at FROM sites WHERE client_id = ? ORDER BY created_at DESC LIMIT 1";
            $recent_site = $db->fetchRow($sql, [$client_id]);
            
            return [
                'site_count' => $site_count,
                'most_recent_site' => $recent_site ? $recent_site['site_name'] : null,
                'most_recent_site_date' => $recent_site ? $recent_site['created_at'] : null,
            ];
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error getting client statistics: ' . $e->getMessage(), 'error');
            return [
                'site_count' => 0,
                'most_recent_site' => null,
                'most_recent_site_date' => null,
            ];
        }
    }
    
    // Getters
    public function getClientId() { 
        return $this->client_id; 
    }
    
    public function getClientName() { 
        return $this->client_name; 
    }
    
    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray() {
        return [
            'client_id' => $this->client_id,
            'client_name' => $this->client_name,
        ];
    }
}
