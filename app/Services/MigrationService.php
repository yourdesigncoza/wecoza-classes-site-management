<?php

namespace WeCozaSiteManagement\Services;

use WeCozaSiteManagement\Services\DatabaseService;

/**
 * Database Migration Service
 *
 * @package WeCozaSiteManagement\Services
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class MigrationService {
    
    /**
     * Database service instance
     *
     * @var DatabaseService
     */
    private $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = DatabaseService::getInstance();
    }
    
    /**
     * Run all migrations
     *
     * @return bool
     */
    public function runMigrations() {
        try {
            \WeCozaSiteManagement\plugin_log('Starting database migrations');
            
            // Check if sites table exists and has correct structure
            $this->verifySitesTable();
            
            // Check if clients table exists (referenced by foreign key)
            $this->verifyClientsTable();
            
            // Verify indexes
            $this->verifyIndexes();
            
            // Update migration version
            update_option('wecoza_site_management_migration_version', '1.0.0');
            
            \WeCozaSiteManagement\plugin_log('Database migrations completed successfully');
            return true;
            
        } catch (\Exception $e) {
            \WeCozaSiteManagement\plugin_log('Migration failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }
    
    /**
     * Verify sites table exists and has correct structure
     *
     * @throws \Exception
     */
    private function verifySitesTable() {
        // Check if table exists
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'sites'
        )";
        
        $result = $this->db->fetchRow($sql);
        
        if (!$result['exists']) {
            throw new \Exception('Sites table does not exist in the database');
        }
        
        // Verify table structure
        $sql = "SELECT column_name, data_type, is_nullable, column_default
                FROM information_schema.columns 
                WHERE table_schema = 'public' 
                AND table_name = 'sites'
                ORDER BY ordinal_position";
        
        $columns = $this->db->fetchAll($sql);
        
        $expected_columns = [
            'site_id' => ['data_type' => 'integer', 'is_nullable' => 'NO'],
            'client_id' => ['data_type' => 'integer', 'is_nullable' => 'NO'],
            'site_name' => ['data_type' => 'character varying', 'is_nullable' => 'NO'],
            'address' => ['data_type' => 'text', 'is_nullable' => 'YES'],
            'created_at' => ['data_type' => 'timestamp without time zone', 'is_nullable' => 'YES'],
            'updated_at' => ['data_type' => 'timestamp without time zone', 'is_nullable' => 'YES'],
        ];
        
        $found_columns = [];
        foreach ($columns as $column) {
            $found_columns[$column['column_name']] = [
                'data_type' => $column['data_type'],
                'is_nullable' => $column['is_nullable']
            ];
        }
        
        foreach ($expected_columns as $column_name => $expected) {
            if (!isset($found_columns[$column_name])) {
                throw new \Exception("Missing column: {$column_name} in sites table");
            }
            
            if ($found_columns[$column_name]['data_type'] !== $expected['data_type']) {
                \WeCozaSiteManagement\plugin_log(
                    "Column {$column_name} has type {$found_columns[$column_name]['data_type']}, expected {$expected['data_type']}",
                    'warning'
                );
            }
        }
        
        \WeCozaSiteManagement\plugin_log('Sites table structure verified successfully');
    }
    
    /**
     * Verify clients table exists (for foreign key reference)
     *
     * @throws \Exception
     */
    private function verifyClientsTable() {
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'clients'
        )";
        
        $result = $this->db->fetchRow($sql);
        
        if (!$result['exists']) {
            \WeCozaSiteManagement\plugin_log('Warning: Clients table does not exist. Foreign key constraint may fail.', 'warning');
        } else {
            \WeCozaSiteManagement\plugin_log('Clients table verified successfully');
        }
    }
    
    /**
     * Verify required indexes exist
     */
    private function verifyIndexes() {
        // Check for primary key on site_id
        $sql = "SELECT EXISTS (
            SELECT FROM information_schema.table_constraints 
            WHERE table_schema = 'public' 
            AND table_name = 'sites' 
            AND constraint_type = 'PRIMARY KEY'
        )";
        
        $result = $this->db->fetchRow($sql);
        if (!$result['exists']) {
            \WeCozaSiteManagement\plugin_log('Warning: Primary key constraint missing on sites table', 'warning');
        }
        
        // Check for index on client_id
        $sql = "SELECT EXISTS (
            SELECT FROM pg_indexes 
            WHERE schemaname = 'public' 
            AND tablename = 'sites' 
            AND indexname = 'idx_sites_client_id'
        )";
        
        $result = $this->db->fetchRow($sql);
        if (!$result['exists']) {
            \WeCozaSiteManagement\plugin_log('Warning: Index on client_id missing from sites table', 'warning');
        }
        
        \WeCozaSiteManagement\plugin_log('Database indexes verified');
    }
    
    /**
     * Get current migration version
     *
     * @return string
     */
    public function getCurrentVersion() {
        return get_option('wecoza_site_management_migration_version', '0.0.0');
    }
    
    /**
     * Check if migrations are needed
     *
     * @return bool
     */
    public function needsMigration() {
        $current_version = $this->getCurrentVersion();
        $target_version = '1.0.0';
        
        return version_compare($current_version, $target_version, '<');
    }
    
    /**
     * Test database connectivity and basic operations
     *
     * @return array Test results
     */
    public function testDatabase() {
        $results = [
            'connection' => false,
            'sites_table' => false,
            'clients_table' => false,
            'can_read' => false,
            'can_write' => false,
            'errors' => []
        ];
        
        try {
            // Test connection
            $results['connection'] = $this->db->testConnection();
            
            if ($results['connection']) {
                // Test sites table
                try {
                    $this->db->query("SELECT 1 FROM sites LIMIT 1");
                    $results['sites_table'] = true;
                } catch (\Exception $e) {
                    $results['errors'][] = 'Sites table access failed: ' . $e->getMessage();
                }
                
                // Test clients table
                try {
                    $this->db->query("SELECT 1 FROM clients LIMIT 1");
                    $results['clients_table'] = true;
                } catch (\Exception $e) {
                    $results['errors'][] = 'Clients table access failed: ' . $e->getMessage();
                }
                
                // Test read capability
                try {
                    $this->db->fetchAll("SELECT site_id, site_name FROM sites LIMIT 5");
                    $results['can_read'] = true;
                } catch (\Exception $e) {
                    $results['errors'][] = 'Read test failed: ' . $e->getMessage();
                }
                
                // Test write capability (with rollback)
                try {
                    $this->db->beginTransaction();
                    $this->db->query(
                        "INSERT INTO sites (client_id, site_name, address) VALUES (?, ?, ?)",
                        [1, 'TEST_SITE_' . time(), 'Test Address']
                    );
                    $this->db->rollback();
                    $results['can_write'] = true;
                } catch (\Exception $e) {
                    $this->db->rollback();
                    $results['errors'][] = 'Write test failed: ' . $e->getMessage();
                }
            } else {
                $results['errors'][] = 'Database connection failed';
            }
            
        } catch (\Exception $e) {
            $results['errors'][] = 'Database test failed: ' . $e->getMessage();
        }
        
        return $results;
    }
}
