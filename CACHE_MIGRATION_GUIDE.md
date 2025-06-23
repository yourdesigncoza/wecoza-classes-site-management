# Cache Migration Guide: WordPress Transients to Redis Object Caching

## Overview

This document outlines the migration from WordPress transients to Redis object caching with versioning functionality in the WeCoza Classes Site Management plugin.

## Migration Summary

### What Changed

1. **Transient Functions Replaced**:
   - `get_transient()` → `wp_cache_get()` (via CacheHelper)
   - `set_transient()` → `wp_cache_set()` (via CacheHelper)
   - `delete_transient()` → Cache versioning (via CacheHelper)

2. **Cache Versioning Implemented**:
   - Added `wsm_get_cache_version()` and `wsm_bump_cache_version()` helper functions
   - Cache keys now include version numbers for bulk invalidation
   - Version bumping replaces individual cache deletion

3. **Cache Group Standardization**:
   - All plugin cache operations use the `wecoza_sites_debug` cache group
   - Consistent cache key naming conventions maintained

## New Cache Architecture

### Cache Helper Class

The new `WeCozaSiteManagement\CacheHelper` class provides:

- **Versioned Cache Operations**: All cache keys include version numbers
- **Bulk Invalidation**: Version bumping invalidates all cached data at once
- **Redis Compatibility**: Optimized for Redis object caching
- **Backward Compatibility**: Legacy transient cleanup during deactivation

### Key Methods

```php
// Get current cache version
$version = CacheHelper::get_cache_version();

// Bump version to invalidate all cache
$new_version = CacheHelper::bump_cache_version();

// Cache operations with versioning
CacheHelper::set($key, $data, $expiration);
$data = CacheHelper::get($key);
CacheHelper::delete($key);

// Clear all plugin cache
CacheHelper::clear_all();
```

## Files Modified

### Core Cache System
- `includes/class-cache-helper.php` - **NEW**: Cache helper class with versioning
- `includes/class-wecoza-site-management-plugin.php` - Updated cache clearing method

### Model Updates
- `app/Models/SiteModel.php` - Migrated all cache operations to use CacheHelper

### Configuration
- `config/app.php` - Updated cache configuration for Redis object caching

### Cleanup/Deactivation
- `includes/class-deactivator.php` - Updated to use cache versioning
- `includes/class-uninstaller.php` - Updated for complete cache cleanup

## Cache Configuration

### New Settings in `config/app.php`

```php
'cache' => array(
    'enable_caching' => true,
    'cache_type' => 'redis_object_cache',
    'cache_group' => 'wecoza_sites_debug',
    'default_expiration' => DAY_IN_SECONDS, // 24 hours
    'cache_versioning' => true,
    'cache_groups' => array(
        'sites' => DAY_IN_SECONDS,
        'clients' => DAY_IN_SECONDS,
        'debug' => DAY_IN_SECONDS,
        'search_results' => 900, // 15 minutes
    ),
    'cache_keys' => array(
        'sites_debug' => 'wecoza_sites_debug_cache',
        'sites_list' => 'wecoza_sites_list_cache',
        'clients_list' => 'wecoza_clients_list_cache',
    ),
    'legacy_support' => true,
),
```

## Performance Benefits

### Before (Transients)
- Individual cache deletion required multiple database operations
- No bulk invalidation capability
- Limited to database storage (slower)
- Manual cache key management

### After (Redis Object Caching)
- Bulk invalidation via version bumping (single operation)
- Redis in-memory storage (faster)
- Automatic cache key versioning
- Better scalability and performance

## Verification Steps

### 1. Check Redis Availability

```php
$redis_available = CacheHelper::is_external_cache_available();
if ($redis_available) {
    echo "✓ Redis object caching is available";
} else {
    echo "✗ Redis object caching not available - falling back to database";
}
```

### 2. Test Cache Operations

```php
// Test basic cache operations
CacheHelper::set('test_key', 'test_data');
$data = CacheHelper::get('test_key');
echo $data === 'test_data' ? '✓ Cache working' : '✗ Cache failed';
```

### 3. Test Version Bumping

```php
// Set cache data
CacheHelper::set('version_test', 'original_data');

// Bump version (should invalidate cache)
CacheHelper::bump_cache_version();

// Try to retrieve (should return false)
$data = CacheHelper::get('version_test');
echo $data === false ? '✓ Version bumping working' : '✗ Version bumping failed';
```

### 4. Run Automated Tests

Execute the test script:
```php
include 'tests/cache-migration-test.php';
```

## Cache Status Monitoring

### Debug Information

The `SiteModel::getDebugCacheStatus()` method now provides:

```php
$status = SiteModel::getDebugCacheStatus();
// Returns:
// - cache_exists: boolean
// - cache_size_bytes: integer
// - cache_version: integer
// - redis_available: boolean
// - created_at: timestamp
// - expires_at: timestamp
// - sites_in_cache: count
```

### Performance Metrics

Cache operations now include performance tracking:
- Cache hit/miss ratios
- Load times
- Cache size information
- Redis availability status

## Troubleshooting

### Common Issues

1. **Redis Not Available**
   - Plugin falls back to database caching
   - Performance may be slower but functionality maintained

2. **Cache Not Clearing**
   - Check if `CacheHelper::clear_all()` is being called
   - Verify cache version is incrementing

3. **Legacy Cache Issues**
   - Old transients are cleaned up during deactivation
   - Manual cleanup available via uninstaller

### Debug Mode

Enable WordPress debug mode to see cache operation logs:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## Migration Checklist

- [x] Cache helper functions implemented
- [x] SiteModel cache operations updated
- [x] Plugin-wide cache management updated
- [x] Deactivation/cleanup procedures updated
- [x] Cache configuration updated
- [x] Test script created
- [x] Documentation completed

## Backward Compatibility

The migration maintains backward compatibility by:
- Preserving existing cache key naming conventions
- Cleaning up legacy transients during deactivation
- Maintaining the same cache expiration times (24 hours)
- Keeping the same proactive cache invalidation patterns

## Performance Impact

Expected improvements:
- **Faster cache operations** with Redis in-memory storage
- **Reduced database load** from cache operations
- **More efficient bulk invalidation** via version bumping
- **Better scalability** for high-traffic sites

The migration maintains all existing functionality while providing better performance and more efficient cache management.
