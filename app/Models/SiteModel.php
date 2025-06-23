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
            $errors['site_name'] = 'Site name is required.';
        } elseif (strlen($this->site_name) < $config['site_name']['min_length']) {
            $errors['site_name'] = sprintf(
                'Site name must be at least %d characters.',
                $config['site_name']['min_length']
            );
        } elseif (strlen($this->site_name) > $config['site_name']['max_length']) {
            $errors['site_name'] = sprintf(
                'Site name must not exceed %d characters.',
                $config['site_name']['max_length']
            );
        }
        
        // Validate client ID
        if (empty($this->client_id) || $this->client_id < 1) {
            $errors['client_id'] = 'Please select a valid client.';
        }
        
        // Validate address (optional but has max length)
        if (!empty($this->address) && strlen($this->address) > $config['address']['max_length']) {
            $errors['address'] = sprintf(
                'Address must not exceed %d characters.',
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

                // Clear cache after successful creation
                \WeCoza_Site_Management_Plugin::clear_sites_cache();

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

                // Clear cache after successful update
                \WeCoza_Site_Management_Plugin::clear_sites_cache();
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

                // Clear cache after successful deletion
                \WeCoza_Site_Management_Plugin::clear_sites_cache();
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
     * Get unique client count with optional filtering
     *
     * @param array $args Query arguments
     * @return int
     */
    public static function getUniqueClientCount($args = []) {
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

            $sql = "SELECT COUNT(DISTINCT client_id) as unique_clients FROM sites {$where_clause}";
            $result = $db->fetchRow($sql, $params);

            return intval($result['unique_clients']);
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error getting unique client count: ' . $e->getMessage(), 'error');
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

    /**
     * Get all sites with client data in a single optimized query for debugging
     * Eliminates N+1 query problem by using LEFT JOIN
     *
     * @param int $limit Maximum number of sites to retrieve (default: 1000)
     * @return array Contains sites_with_clients, statistics, and performance metrics
     */
    public static function getAllSitesWithClientsForDebug($limit = 1000) {
        try {
            $db = DatabaseService::getInstance();
            $start_time = microtime(true);

            // Single optimized query with LEFT JOIN and aggregate statistics
            $sql = "
                WITH site_data AS (
                    SELECT
                        s.site_id,
                        s.client_id,
                        s.site_name,
                        s.address,
                        s.created_at,
                        s.updated_at,
                        c.client_name
                    FROM sites s
                    LEFT JOIN clients c ON s.client_id = c.client_id
                    ORDER BY s.site_name ASC
                    LIMIT ?
                ),
                stats AS (
                    SELECT
                        COUNT(*) as total_sites,
                        COUNT(DISTINCT client_id) as unique_clients
                    FROM sites
                )
                SELECT
                    sd.*,
                    st.total_sites,
                    st.unique_clients
                FROM site_data sd
                CROSS JOIN stats st
            ";

            $results = $db->fetchAll($sql, [$limit]);
            $end_time = microtime(true);
            $load_time_ms = round(($end_time - $start_time) * 1000, 2);

            // Process results
            $sites_with_clients = [];
            $total_sites = 0;
            $unique_clients = 0;

            foreach ($results as $row) {
                // Extract statistics from first row
                if (empty($sites_with_clients)) {
                    $total_sites = intval($row['total_sites']);
                    $unique_clients = intval($row['unique_clients']);
                }

                // Build site data with embedded client info
                $site_data = [
                    'site_id' => intval($row['site_id']),
                    'client_id' => intval($row['client_id']),
                    'site_name' => $row['site_name'],
                    'address' => $row['address'],
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'client_name' => $row['client_name'] // Embedded client data
                ];

                $sites_with_clients[] = $site_data;
            }

            return [
                'sites_with_clients' => $sites_with_clients,
                'statistics' => [
                    'total_sites' => $total_sites,
                    'unique_clients' => $unique_clients,
                    'sites_loaded' => count($sites_with_clients),
                    'load_time_ms' => $load_time_ms,
                    'query_count' => 1 // Single query instead of N+1
                ],
                'performance' => [
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'load_time_ms' => $load_time_ms,
                    'memory_usage' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ]
            ];

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error in getAllSitesWithClientsForDebug: ' . $e->getMessage(), 'error');
            return [
                'sites_with_clients' => [],
                'statistics' => [
                    'total_sites' => 0,
                    'unique_clients' => 0,
                    'sites_loaded' => 0,
                    'load_time_ms' => 0,
                    'query_count' => 0
                ],
                'performance' => [
                    'start_time' => 0,
                    'end_time' => 0,
                    'load_time_ms' => 0,
                    'memory_usage' => 0,
                    'peak_memory' => 0
                ],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get cached sites with clients data for debugging with intelligent cache invalidation
     * Uses WordPress transients for performance optimization
     *
     * @param int $limit Maximum number of sites to retrieve (default: 1000)
     * @param bool $force_refresh Force cache refresh (default: false)
     * @return array Contains sites_with_clients, statistics, performance metrics, and cache info
     */
    public static function getCachedSitesWithClientsForDebug($limit = 1000, $force_refresh = false) {
        $cache_key = 'wecoza_sites_debug_cache_v1';
        $cache_expiration = DAY_IN_SECONDS; // 24 hours
        $start_time = microtime(true);

        try {
            $cache_status = [
                'cache_hit' => false,
                'cache_miss_reason' => '',
                'cache_created_at' => null,
                'cache_expires_at' => null,
                'cache_load_time_ms' => 0
            ];

            // Step 1: Check if we should use cache or refresh (simplified approach)
            $should_refresh = $force_refresh;
            $cached_data = null;

            if (!$should_refresh) {
                // Get cached data
                $cached_data = get_transient($cache_key);

                if ($cached_data === false) {
                    $should_refresh = true;
                    $cache_status['cache_miss_reason'] = 'Cache expired or not found';
                } elseif (!is_array($cached_data) || !isset($cached_data['sites_with_clients'])) {
                    $should_refresh = true;
                    $cache_status['cache_miss_reason'] = 'Cache data corrupted or invalid structure';
                }
            } else {
                $cache_status['cache_miss_reason'] = 'Force refresh requested';
            }

            // Step 2: Load data from cache or database
            if ($should_refresh) {
                // Cache miss - load from database
                $fresh_data = self::getAllSitesWithClientsForDebug($limit);

                if (!isset($fresh_data['error'])) {
                    // Store in cache with metadata
                    $cache_data = $fresh_data;
                    $cache_data['cache_metadata'] = [
                        'created_at' => current_time('mysql'),
                        'created_timestamp' => time(),
                        'expires_at' => date('Y-m-d H:i:s', time() + $cache_expiration),
                        'cache_version' => 'v1'
                    ];

                    // Set transient
                    set_transient($cache_key, $cache_data, $cache_expiration);

                    $cache_status['cache_created_at'] = $cache_data['cache_metadata']['created_at'];
                    $cache_status['cache_expires_at'] = $cache_data['cache_metadata']['expires_at'];

                    // \WeCozaSiteManagement\plugin_log('Debug cache refreshed: ' . count($fresh_data['sites_with_clients']) . ' sites cached');
                }

                $result_data = $fresh_data;
            } else {
                // Cache hit - load from cache
                $cache_load_start = microtime(true);
                $result_data = $cached_data;
                $cache_load_end = microtime(true);

                $cache_status['cache_hit'] = true;
                $cache_status['cache_load_time_ms'] = round(($cache_load_end - $cache_load_start) * 1000, 2);

                if (isset($cached_data['cache_metadata'])) {
                    $cache_status['cache_created_at'] = $cached_data['cache_metadata']['created_at'];
                    $cache_status['cache_expires_at'] = $cached_data['cache_metadata']['expires_at'];
                }
            }

            // Step 4: Add cache information to result
            $end_time = microtime(true);
            $total_time_ms = round(($end_time - $start_time) * 1000, 2);

            // Update performance metrics
            if (isset($result_data['performance'])) {
                $result_data['performance']['total_time_with_cache_ms'] = $total_time_ms;
            }

            // Add cache status
            $result_data['cache_info'] = $cache_status;
            $result_data['cache_info']['total_operation_time_ms'] = $total_time_ms;

            return $result_data;

        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Error in getCachedSitesWithClientsForDebug: ' . $e->getMessage(), 'error');

            // Fallback to direct database query
            $fallback_data = self::getAllSitesWithClientsForDebug($limit);
            $fallback_data['cache_info'] = [
                'cache_hit' => false,
                'cache_miss_reason' => 'Cache system error: ' . $e->getMessage(),
                'fallback_used' => true,
                'error' => $e->getMessage()
            ];

            return $fallback_data;
        }
    }

    /**
     * Clear debug cache manually
     * Useful for debugging or when data changes outside normal operations
     *
     * @return bool Success status
     */
    public static function clearDebugCache() {
        $cache_key = 'wecoza_sites_debug_cache_v1';

        $result = delete_transient($cache_key);

        \WeCozaSiteManagement\plugin_log('Debug cache manually cleared');

        return $result;
    }

    /**
     * Get cache status information
     *
     * @return array Cache status details
     */
    public static function getDebugCacheStatus() {
        $cache_key = 'wecoza_sites_debug_cache_v1';

        $cached_data = get_transient($cache_key);

        $status = [
            'cache_exists' => $cached_data !== false,
            'cache_size_bytes' => $cached_data !== false ? strlen(serialize($cached_data)) : 0,
        ];

        if ($cached_data !== false && isset($cached_data['cache_metadata'])) {
            $status['created_at'] = $cached_data['cache_metadata']['created_at'];
            $status['expires_at'] = $cached_data['cache_metadata']['expires_at'];
            $status['sites_in_cache'] = count($cached_data['sites_with_clients']);
        }

        return $status;
    }
}
