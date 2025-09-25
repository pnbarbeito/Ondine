<?php

namespace Ondine\Cache;

/**
 * Minimal file-based cache for profile data.
 * - Stores entries under data/cache/profile_{key}.json
 * - Uses JSON serialization and a TTL (seconds)
 * - No external dependencies, suitable for minimal frameworks or single-host setups
 */
class ProfileCache
{
    protected $dir;
    protected $ttl;

    public function __construct(string $dir = null, int $ttl = null)
    {
        // default cache directory under project data/cache
        $projectRoot = getcwd();
        $this->dir = $dir ?: $projectRoot . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'cache';
        if (!is_dir($this->dir)) {
            @mkdir($this->dir, 0755, true);
        }

        $envTtl = null;
        if (function_exists('Env') || class_exists('\Env')) {
            try {
                $envTtl = \Env::get('PROFILE_CACHE_TTL', null);
            } catch (\Throwable $e) {
                $envTtl = null;
            }
        }

        $this->ttl = $ttl ?? (is_numeric($envTtl) ? (int)$envTtl : 60);
    }

    protected function filename(string $key): string
    {
        $safe = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->dir . DIRECTORY_SEPARATOR . 'profile_' . $safe . '.json';
    }

    public function get(string $key)
    {
        $file = $this->filename($key);
        if (!is_file($file)) {
            return null;
        }

        $data = @file_get_contents($file);
        if ($data === false) {
            return null;
        }

        $obj = @json_decode($data, true);
        if (!is_array($obj) || !isset($obj['ts']) || !isset($obj['value'])) {
            return null;
        }

        if (time() - $obj['ts'] > $this->ttl) {
            @unlink($file);
            return null;
        }

        return $obj['value'];
    }

    public function set(string $key, $value): bool
    {
        $file = $this->filename($key);
        $obj = ['ts' => time(), 'value' => $value];
        $json = @json_encode($obj);
        if ($json === false) {
            return false;
        }
        return (bool)@file_put_contents($file, $json, LOCK_EX);
    }

    public function delete(string $key): bool
    {
        $file = $this->filename($key);
        if (is_file($file)) {
            return (bool)@unlink($file);
        }
        return true;
    }

    /**
     * Convenience: clear cache entry for a profile id key = profile_{id}
     */
    public function clearProfile($profileId): bool
    {
        $key = 'profile_' . (string)$profileId;
        return $this->delete($key);
    }
}
