<?php

namespace WeCozaSiteManagement\Models;

use WeCozaSiteManagement\Services\DatabaseService;

/**
 * Site Model for CRUD operations
 *
 * @package WeCozaSiteManagement\Models
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class SiteModel {
    
    /**
     * Site ID
     *
     * @var int
     */
    private $site_id;
    
    /**
     * Client ID
     *
     * @var int
     */
    private $client_id;
    
    /**
     * Site name
     *
     * @var string
     */
    private $site_name;
    
    /**
     * Site address
     *
     * @var string
     */
    private $address;
    
    /**
     * Created at timestamp
     *
     * @var string
     */
    private $created_at;
    
    /**
     * Updated at timestamp
     *
     * @var string
     */
    private $updated_at;
    
    /**
     * Constructor
     *
     * @param array $data Site data
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
        $this->site_id = isset($data['site_id']) ? intval($data['site_id']) : null;
        $this->client_id = isset($data['client_id']) ? intval($data['client_id']) : null;
        $this->site_name = isset($data['site_name']) ? sanitize_text_field($data['site_name']) : '';
        $this->address = isset($data['address']) ? sanitize_textarea_field($data['address']) : '';
        $this->created_at = isset($data['created_at']) ? $data['created_at'] : null;
        $this->updated_at = isset($data['updated_at']) ? $data['updated_at'] : null;
    }
    
    /**
     * Validate site data
     *
     * @return array Array of validation errors
     */
    public function validate() {
        $errors = [];
        $config = \WeCozaSiteManagement\config('app')['validation'];
        
        // Validate site name
        if (empty($this->site_name)) {
            $errors['site_name'] = __('Site name is required.', 'wecoza-site-management');
        } elseif (strlen($this->site_name) < $config['site_name']['min_length']) {
            $errors['site_name'] = sprintf(
                __('Site name must be at least %d characters.', 'wecoza-site-management'),
                $config['site_name']['min_length']
            );
        } elseif (strlen($this->site_name) > $config['site_name']['max_length']) {
            $errors['site_name'] = sprintf(
                __('Site name must not exceed %d characters.', 'wecoza-site-management'),
                $config['site_name']['max_length']
            );
        }
        
        // Validate client ID
        if (empty($this->client_id) || $this->client_id < 1) {
            $errors['client_id'] = __('Please select a valid client.', 'wecoza-site-management');
        }
        
        // Validate address (optional but has max length)
        if (!empty($this->address) && strlen($this->address) > $config['address']['max_length']) {
            $errors['address'] = sprintf(
                __('Address must not exceed %d characters.', 'wecoza-site-management'),
                $config['address']['max_length']
            );
        }
        
        return $errors;
    }
    
    /**
     * Save site (create or update)
     *
     * @return bool|int Returns site ID on success, false on failure
     */
    public function save() {
        $validation_errors = $this->validate();
        if (!empty($validation_errors)) {
            \WeCozaSiteManagement\plugin_log('Site validation failed: ' . print_r($validation_errors, true), 'error');
            return false;
        }
        
        try {
            $db = DatabaseService::getInstance();
            
            if ($this->site_id) {
                return $this->update();
            } else {
                return $this->create();
            }
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error saving site: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Create new site
     *
     * @return int|false Site ID on success, false on failure
     */
    private function create() {
        try {
            $db = DatabaseService::getInstance();
            $this->created_at = current_time('mysql');
            $this->updated_at = current_time('mysql');
            
            $sql = "INSERT INTO sites (client_id, site_name, address, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?) RETURNING site_id";
            
            $stmt = $db->query($sql, [
                $this->client_id,
                $this->site_name,
                $this->address,
                $this->created_at,
                $this->updated_at
            ]);
            
            $result = $stmt->fetch();
            if ($result) {
                $this->site_id = intval($result['site_id']);
                \WeCozaSiteManagement\plugin_log("Site created successfully with ID: {$this->site_id}");
                return $this->site_id;
            }
            
            return false;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error creating site: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Update existing site
     *
     * @return bool
     */
    private function update() {
        try {
            $db = DatabaseService::getInstance();
            $this->updated_at = current_time('mysql');
            
            $sql = "UPDATE sites SET client_id = ?, site_name = ?, address = ?, updated_at = ? 
                    WHERE site_id = ?";
            
            $stmt = $db->query($sql, [
                $this->client_id,
                $this->site_name,
                $this->address,
                $this->updated_at,
                $this->site_id
            ]);
            
            $success = $db->rowCount($stmt) > 0;
            if ($success) {
                \WeCozaSiteManagement\plugin_log("Site updated successfully: {$this->site_id}");
            }
            
            return $success;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error updating site: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Delete site
     *
     * @return bool
     */
    public function delete() {
        if (!$this->site_id) {
            return false;
        }
        
        try {
            $db = DatabaseService::getInstance();
            $sql = "DELETE FROM sites WHERE site_id = ?";
            $stmt = $db->query($sql, [$this->site_id]);
            
            $success = $db->rowCount($stmt) > 0;
            if ($success) {
                \WeCozaSiteManagement\plugin_log("Site deleted successfully: {$this->site_id}");
            }
            
            return $success;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error deleting site: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Find site by ID
     *
     * @param int $site_id
     * @return SiteModel|null
     */
    public static function find($site_id) {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT * FROM sites WHERE site_id = ?";
            $data = $db->fetchRow($sql, [$site_id]);
            
            return $data ? new self($data) : null;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error finding site: ' . $e->getMessage(), 'error');
            return null;
        }
    }
    
    // Getters and Setters
    public function getSiteId() { return $this->site_id; }
    public function setSiteId($site_id) { $this->site_id = intval($site_id); }
    
    public function getClientId() { return $this->client_id; }
    public function setClientId($client_id) { $this->client_id = intval($client_id); }
    
    public function getSiteName() { return $this->site_name; }
    public function setSiteName($site_name) { $this->site_name = sanitize_text_field($site_name); }
    
    public function getAddress() { return $this->address; }
    public function setAddress($address) { $this->address = sanitize_textarea_field($address); }
    
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    
    /**
     * Get all sites with optional filtering and pagination
     *
     * @param array $args Query arguments
     * @return array
     */
    public static function getAll($args = []) {
        $defaults = [
            'per_page' => 20,
            'page' => 1,
            'search' => '',
            'client_id' => null,
            'order_by' => 'site_name',
            'order' => 'ASC',
        ];

        $args = array_merge($defaults, $args);

        try {
            $db = DatabaseService::getInstance();
            $where_conditions = [];
            $params = [];

            // Add search condition
            if (!empty($args['search'])) {
                $search = $db->escapeLike($args['search']);
                $where_conditions[] = "(site_name ILIKE ? OR address ILIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            // Add client filter
            if (!empty($args['client_id'])) {
                $where_conditions[] = "client_id = ?";
                $params[] = intval($args['client_id']);
            }

            // Build WHERE clause
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            // Build ORDER BY clause
            $allowed_order_by = ['site_id', 'site_name', 'client_id', 'created_at', 'updated_at'];
            $order_by = in_array($args['order_by'], $allowed_order_by) ? $args['order_by'] : 'site_name';
            $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';

            // Calculate offset
            $offset = ($args['page'] - 1) * $args['per_page'];

            // Build final query
            $sql = "SELECT * FROM sites {$where_clause} ORDER BY {$order_by} {$order} LIMIT ? OFFSET ?";
            $params[] = intval($args['per_page']);
            $params[] = intval($offset);

            $results = $db->fetchAll($sql, $params);

            // Convert to SiteModel objects
            $sites = [];
            foreach ($results as $row) {
                $sites[] = new self($row);
            }

            return $sites;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error getting sites: ' . $e->getMessage(), 'error');
            return [];
        }
    }

    /**
     * Get total count of sites with optional filtering
     *
     * @param array $args Query arguments
     * @return int
     */
    public static function getCount($args = []) {
        $defaults = [
            'search' => '',
            'client_id' => null,
        ];

        $args = array_merge($defaults, $args);

        try {
            $db = DatabaseService::getInstance();
            $where_conditions = [];
            $params = [];

            // Add search condition
            if (!empty($args['search'])) {
                $search = $db->escapeLike($args['search']);
                $where_conditions[] = "(site_name ILIKE ? OR address ILIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            // Add client filter
            if (!empty($args['client_id'])) {
                $where_conditions[] = "client_id = ?";
                $params[] = intval($args['client_id']);
            }

            // Build WHERE clause
            $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

            $sql = "SELECT COUNT(*) as total FROM sites {$where_clause}";
            $result = $db->fetchRow($sql, $params);

            return intval($result['total']);
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error getting sites count: ' . $e->getMessage(), 'error');
            return 0;
        }
    }

    /**
     * Get sites by client ID
     *
     * @param int $client_id
     * @return array
     */
    public static function getByClientId($client_id) {
        return self::getAll(['client_id' => $client_id, 'per_page' => 1000]);
    }

    /**
     * Check if site name exists for a client (excluding current site)
     *
     * @param string $site_name
     * @param int $client_id
     * @param int $exclude_site_id
     * @return bool
     */
    public static function siteNameExists($site_name, $client_id, $exclude_site_id = null) {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT COUNT(*) as count FROM sites WHERE site_name = ? AND client_id = ?";
            $params = [$site_name, $client_id];

            if ($exclude_site_id) {
                $sql .= " AND site_id != ?";
                $params[] = $exclude_site_id;
            }

            $result = $db->fetchRow($sql, $params);
            return intval($result['count']) > 0;
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error checking site name existence: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray() {
        return [
            'site_id' => $this->site_id,
            'client_id' => $this->client_id,
            'site_name' => $this->site_name,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
