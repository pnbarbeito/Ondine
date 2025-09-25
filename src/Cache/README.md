# Ondine - Cache Module

This directory contains caching implementations for the Ondine framework. Caching helps improve performance by storing frequently accessed data in memory or on disk.

## Overview

Ondine provides simple, file-based caching solutions suitable for small to medium applications. The cache implementations are:

- Lightweight and dependency-free
- File-based storage with TTL (Time To Live) support
- Environment variable configuration
- Automatic cache directory creation

## Included Classes

### ProfileCache (`ProfileCache.php`)
A minimal file-based cache specifically designed for profile data storage.

**Key Features:**
- Stores cache entries as JSON files in `data/cache/profile_{key}.json`
- Configurable TTL (Time To Live) in seconds
- Automatic cache invalidation based on TTL
- Environment variable support for TTL configuration
- Safe key sanitization for filesystem compatibility

**Configuration:**
- Cache directory: Defaults to `data/cache` in project root
- TTL: Defaults to 60 seconds, configurable via `PROFILE_CACHE_TTL` environment variable

**Usage Example:**
```php
use Ondine\Cache\ProfileCache;

$cache = new ProfileCache();

// Store profile data
$profileData = ['id' => 1, 'name' => 'John Doe', 'permissions' => ['read', 'write']];
$cache->set('profile_1', $profileData);

// Retrieve profile data
$cachedProfile = $cache->get('profile_1');
if ($cachedProfile) {
    // Use cached data
    echo $cachedProfile['name'];
}

// Clear specific profile cache
$cache->clearProfile(1);

// Or delete by key
$cache->delete('profile_1');
```

## Cache Structure

Cache files are stored in the following format:
```
data/cache/profile_{sanitized_key}.json
```

Each cache file contains:
```json
{
  "ts": 1640995200,
  "value": { /* cached data */ }
}
```

Where:
- `ts`: Unix timestamp when the data was cached
- `value`: The actual cached data

## Environment Variables

- `PROFILE_CACHE_TTL`: Time in seconds for cache expiration (default: 60)

## Best Practices

- Use appropriate TTL values based on data update frequency
- Clear cache when underlying data changes
- Monitor cache directory size for large applications
- Consider Redis or other external cache stores for high-traffic applications

## Future Enhancements

- Add more cache implementations (Redis, Memcached)
- Implement cache warming strategies
- Add cache statistics and monitoring
- Support for cache tags and bulk operations

---

This cache module provides basic caching functionality for Ondine applications. For more advanced caching needs, consider integrating with external cache stores.