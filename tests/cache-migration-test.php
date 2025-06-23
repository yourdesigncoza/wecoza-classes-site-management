<?php

/**
 * Cache Migration Test Script
 *
 * This script tests the migration from WordPress transients to Redis object caching
 * with versioning functionality.
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Test Cache Migration Functionality
 */
class CacheMigrationTest {
    
    /**
     * Run all cache migration tests
     *
     * @return array Test results
     */
    public static function run_tests() {
        $results = [
            'cache_helper_functions' => self::test_cache_helper_functions(),
            'cache_versioning' => self::test_cache_versioning(),
            'site_model_cache' => self::test_site_model_cache(),
            'plugin_cache_management' => self::test_plugin_cache_management(),
            'performance_comparison' => self::test_performance_comparison(),
        ];
        
        return $results;
    }
    
    /**
     * Test cache helper functions
     *
     * @return array Test results
     */
    private static function test_cache_helper_functions() {
        $results = [
            'test_name' => 'Cache Helper Functions',
            'passed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        try {
            // Test 1: Check if CacheHelper class exists
            if (class_exists('WeCozaSiteManagement\\CacheHelper')) {
                $results['passed']++;
                $results['details'][] = '✓ CacheHelper class exists';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ CacheHelper class not found';
            }
            
            // Test 2: Test cache version functions
            $initial_version = \WeCozaSiteManagement\CacheHelper::get_cache_version();
            $new_version = \WeCozaSiteManagement\CacheHelper::bump_cache_version();
            
            if ($new_version > $initial_version) {
                $results['passed']++;
                $results['details'][] = "✓ Cache version bumped from {$initial_version} to {$new_version}";
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache version bump failed';
            }
            
            // Test 3: Test cache operations
            $test_key = 'test_cache_key';
            $test_data = ['test' => 'data', 'timestamp' => time()];
            
            $set_result = \WeCozaSiteManagement\CacheHelper::set($test_key, $test_data, 300);
            $get_result = \WeCozaSiteManagement\CacheHelper::get($test_key);
            
            if ($set_result && $get_result === $test_data) {
                $results['passed']++;
                $results['details'][] = '✓ Cache set/get operations working';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache set/get operations failed';
            }
            
            // Test 4: Test cache deletion
            $delete_result = \WeCozaSiteManagement\CacheHelper::delete($test_key);
            $get_after_delete = \WeCozaSiteManagement\CacheHelper::get($test_key);
            
            if ($delete_result && $get_after_delete === false) {
                $results['passed']++;
                $results['details'][] = '✓ Cache deletion working';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache deletion failed';
            }
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['details'][] = '✗ Exception: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Test cache versioning functionality
     *
     * @return array Test results
     */
    private static function test_cache_versioning() {
        $results = [
            'test_name' => 'Cache Versioning',
            'passed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        try {
            // Test versioned key generation
            $base_key = 'test_versioned_key';
            $versioned_key_1 = \WeCozaSiteManagement\CacheHelper::get_versioned_key($base_key);
            
            // Set some data
            \WeCozaSiteManagement\CacheHelper::set($base_key, 'test_data_1');
            $data_1 = \WeCozaSiteManagement\CacheHelper::get($base_key);
            
            // Bump version
            \WeCozaSiteManagement\CacheHelper::bump_cache_version();
            
            // Check if old data is no longer accessible
            $data_after_bump = \WeCozaSiteManagement\CacheHelper::get($base_key);
            
            if ($data_1 === 'test_data_1' && $data_after_bump === false) {
                $results['passed']++;
                $results['details'][] = '✓ Cache versioning invalidation working';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache versioning invalidation failed';
            }
            
            // Test new versioned key
            $versioned_key_2 = \WeCozaSiteManagement\CacheHelper::get_versioned_key($base_key);
            
            if ($versioned_key_1 !== $versioned_key_2) {
                $results['passed']++;
                $results['details'][] = '✓ Versioned keys are different after version bump';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Versioned keys are the same after version bump';
            }
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['details'][] = '✗ Exception: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Test SiteModel cache operations
     *
     * @return array Test results
     */
    private static function test_site_model_cache() {
        $results = [
            'test_name' => 'SiteModel Cache Operations',
            'passed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        try {
            // Test cache status function
            $cache_status = \WeCozaSiteManagement\Models\SiteModel::getDebugCacheStatus();
            
            if (is_array($cache_status) && isset($cache_status['cache_version'])) {
                $results['passed']++;
                $results['details'][] = '✓ Cache status function working';
                $results['details'][] = "  - Cache version: {$cache_status['cache_version']}";
                $results['details'][] = "  - Redis available: " . ($cache_status['redis_available'] ? 'Yes' : 'No');
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache status function failed';
            }
            
            // Test cache clearing
            $clear_result = \WeCozaSiteManagement\Models\SiteModel::clearDebugCache();
            
            if ($clear_result) {
                $results['passed']++;
                $results['details'][] = '✓ Cache clearing function working';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Cache clearing function failed';
            }
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['details'][] = '✗ Exception: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Test plugin-wide cache management
     *
     * @return array Test results
     */
    private static function test_plugin_cache_management() {
        $results = [
            'test_name' => 'Plugin Cache Management',
            'passed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        try {
            // Test plugin cache clearing
            $clear_result = \WeCoza_Site_Management_Plugin::clear_sites_cache();
            
            if ($clear_result) {
                $results['passed']++;
                $results['details'][] = '✓ Plugin cache clearing working';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Plugin cache clearing failed';
            }
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['details'][] = '✗ Exception: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Test performance comparison
     *
     * @return array Test results
     */
    private static function test_performance_comparison() {
        $results = [
            'test_name' => 'Performance Comparison',
            'passed' => 0,
            'failed' => 0,
            'details' => []
        ];
        
        try {
            // Test cache performance
            $start_time = microtime(true);
            
            // Perform multiple cache operations
            for ($i = 0; $i < 10; $i++) {
                \WeCozaSiteManagement\CacheHelper::set("perf_test_{$i}", "test_data_{$i}");
                \WeCozaSiteManagement\CacheHelper::get("perf_test_{$i}");
            }
            
            $end_time = microtime(true);
            $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
            
            $results['details'][] = "✓ Cache operations completed in {$execution_time}ms";
            
            if ($execution_time < 100) { // Less than 100ms for 20 operations
                $results['passed']++;
                $results['details'][] = '✓ Performance is acceptable';
            } else {
                $results['failed']++;
                $results['details'][] = '✗ Performance may be slow';
            }
            
            // Clean up test data
            for ($i = 0; $i < 10; $i++) {
                \WeCozaSiteManagement\CacheHelper::delete("perf_test_{$i}");
            }
            
        } catch (Exception $e) {
            $results['failed']++;
            $results['details'][] = '✗ Exception: ' . $e->getMessage();
        }
        
        return $results;
    }
    
    /**
     * Display test results
     *
     * @param array $results Test results
     */
    public static function display_results($results) {
        echo "<h2>Cache Migration Test Results</h2>\n";
        
        $total_passed = 0;
        $total_failed = 0;
        
        foreach ($results as $test_result) {
            echo "<h3>{$test_result['test_name']}</h3>\n";
            echo "<p>Passed: {$test_result['passed']} | Failed: {$test_result['failed']}</p>\n";
            
            if (!empty($test_result['details'])) {
                echo "<ul>\n";
                foreach ($test_result['details'] as $detail) {
                    echo "<li>{$detail}</li>\n";
                }
                echo "</ul>\n";
            }
            
            $total_passed += $test_result['passed'];
            $total_failed += $test_result['failed'];
        }
        
        echo "<h3>Overall Results</h3>\n";
        echo "<p><strong>Total Passed: {$total_passed} | Total Failed: {$total_failed}</strong></p>\n";
        
        if ($total_failed === 0) {
            echo "<p style='color: green;'><strong>✓ All tests passed! Cache migration is successful.</strong></p>\n";
        } else {
            echo "<p style='color: red;'><strong>✗ Some tests failed. Please review the migration.</strong></p>\n";
        }
    }
}

// Auto-run tests if this file is accessed directly (for debugging)
if (defined('WECOZA_SITE_MANAGEMENT_VERSION')) {
    // Only run if plugin is loaded
    $test_results = CacheMigrationTest::run_tests();
    CacheMigrationTest::display_results($test_results);
}
