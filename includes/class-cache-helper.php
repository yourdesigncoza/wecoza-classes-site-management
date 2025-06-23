<?php

namespace WeCozaSiteManagement;

/**
 * Cache Helper Class for Redis Object Caching
 *
 * Provides cache versioning functionality to enable group-wide cache invalidation
 * when migrating from WordPress transients to wp_cache_* functions with Redis.
 *
 * @package WeCozaSiteManagement
 * @since 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class CacheHelper {
    
    /**
     * Cache group for all plugin cache operations
     */
    const CACHE_GROUP = 'wecoza_sites_debug';
    
    /**
     * Option name for storing cache version
     */
    const CACHE_VERSION_OPTION = 'wecoza_cache_version';
    
    /**
     * Default cache expiration (1 day)
     */
    const DEFAULT_EXPIRATION = DAY_IN_SECONDS;
    
    /**
     * Get current cache version
     *
     * @return int Current cache version number
     */
    public static function get_cache_version() {
        $version = get_option(self::CACHE_VERSION_OPTION, 1);
        return intval($version);
    }
    
    /**
     * Bump cache version to invalidate all cached data
     *
     * @return int New cache version number
     */
    public static function bump_cache_version() {
        $current_version = self::get_cache_version();
        $new_version = $current_version + 1;
        
        update_option(self::CACHE_VERSION_OPTION, $new_version);
        
        // Log cache version bump for debugging
        if (function_exists('WeCozaSiteManagement\\plugin_log')) {
            plugin_log("Cache version bumped from {$current_version} to {$new_version}");
        }
        
        return $new_version;
    }
    
    /**
     * Generate versioned cache key
     *
     * @param string $base_key Base cache key
     * @return string Versioned cache key
     */
    public static function get_versioned_key($base_key) {
        $version = self::get_cache_version();
        return $base_key . '_v' . $version;
    }
    
    /**
     * Get data from cache with versioning
     *
     * @param string $key Cache key (without version)
     * @param string $group Cache group (optional, defaults to plugin group)
     * @return mixed Cached data or false if not found
     */
    public static function get($key, $group = null) {
        if ($group === null) {
            $group = self::CACHE_GROUP;
        }
        
        $versioned_key = self::get_versioned_key($key);
        return wp_cache_get($versioned_key, $group);
    }
    
    /**
     * Set data in cache with versioning
     *
     * @param string $key Cache key (without version)
     * @param mixed $data Data to cache
     * @param int $expiration Cache expiration in seconds (optional)
     * @param string $group Cache group (optional, defaults to plugin group)
     * @return bool True on success, false on failure
     */
    public static function set($key, $data, $expiration = null, $group = null) {
        if ($group === null) {
            $group = self::CACHE_GROUP;
        }
        
        if ($expiration === null) {
            $expiration = self::DEFAULT_EXPIRATION;
        }
        
        $versioned_key = self::get_versioned_key($key);
        return wp_cache_set($versioned_key, $data, $group, $expiration);
    }
    
    /**
     * Delete specific cache entry
     *
     * @param string $key Cache key (without version)
     * @param string $group Cache group (optional, defaults to plugin group)
     * @return bool True on success, false on failure
     */
    public static function delete($key, $group = null) {
        if ($group === null) {
            $group = self::CACHE_GROUP;
        }
        
        $versioned_key = self::get_versioned_key($key);
        return wp_cache_delete($versioned_key, $group);
    }
    
    /**
     * Clear all plugin cache by bumping version
     *
     * @return bool True on success
     */
    public static function clear_all() {
        self::bump_cache_version();
        return true;
    }
    
    /**
     * Get cache statistics and status
     *
     * @return array Cache status information
     */
    public static function get_cache_status() {
        $version = self::get_cache_version();
        
        return [
            'cache_version' => $version,
            'cache_group' => self::CACHE_GROUP,
            'default_expiration' => self::DEFAULT_EXPIRATION,
            'redis_available' => function_exists('wp_cache_get') && wp_using_ext_object_cache(),
        ];
    }
    
    /**
     * Check if external object cache (Redis) is available
     *
     * @return bool True if Redis/external cache is available
     */
    public static function is_external_cache_available() {
        return function_exists('wp_cache_get') && wp_using_ext_object_cache();
    }
    
    /**
     * Get cache size estimation for a specific key
     *
     * @param string $key Cache key (without version)
     * @param string $group Cache group (optional)
     * @return int Size in bytes, 0 if not found
     */
    public static function get_cache_size($key, $group = null) {
        $data = self::get($key, $group);
        if ($data === false) {
            return 0;
        }
        
        return strlen(serialize($data));
    }
}

/**
 * Global helper functions for backward compatibility
 */

/**
 * Get current cache version
 *
 * @return int Current cache version number
 */
function wsm_get_cache_version() {
    return CacheHelper::get_cache_version();
}

/**
 * Bump cache version to invalidate all cached data
 *
 * @return int New cache version number
 */
function wsm_bump_cache_version() {
    return CacheHelper::bump_cache_version();
}

/**
 * Get versioned cache data
 *
 * @param string $key Cache key
 * @param string $group Cache group (optional)
 * @return mixed Cached data or false
 */
function wsm_cache_get($key, $group = null) {
    return CacheHelper::get($key, $group);
}

/**
 * Set versioned cache data
 *
 * @param string $key Cache key
 * @param mixed $data Data to cache
 * @param int $expiration Expiration in seconds (optional)
 * @param string $group Cache group (optional)
 * @return bool Success status
 */
function wsm_cache_set($key, $data, $expiration = null, $group = null) {
    return CacheHelper::set($key, $data, $expiration, $group);
}

/**
 * Delete versioned cache data
 *
 * @param string $key Cache key
 * @param string $group Cache group (optional)
 * @return bool Success status
 */
function wsm_cache_delete($key, $group = null) {
    return CacheHelper::delete($key, $group);
}

/**
 * Clear all plugin cache
 *
 * @return bool Success status
 */
function wsm_cache_clear_all() {
    return CacheHelper::clear_all();
}
