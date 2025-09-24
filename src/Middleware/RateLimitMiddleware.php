<?php

namespace Ondine\Middleware;

use Ondine\Request;
use Ondine\Response;

class RateLimitMiddleware
{
    protected $limit = 10; // requests
    protected $window = 60; // seconds
    protected $storeDir;

    public function __construct(array $options = [])
    {
        if (isset($options['limit'])) {
            $this->limit = (int)$options['limit'];
        }
        if (isset($options['window'])) {
            $this->window = (int)$options['window'];
        }
        // Default to project data folder inside the public html path: /var/www/html/data/ratelimit
        $defaultDir = __DIR__ . '/../../data/ratelimit';
        $this->storeDir = $options['store_dir'] ?? $defaultDir;

        // Try to create the directory if missing. Suppress warnings and fall back
        // to system temp dir if creation is not permitted.
        if (!is_dir($this->storeDir)) {
            @mkdir($this->storeDir, 0775, true);
        }
        if (!is_dir($this->storeDir) || !is_writable($this->storeDir)) {
            // Fallback to system tmp directory to avoid breaking requests
            $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ondine_ratelimit';
            if (!is_dir($tmp)) {
                @mkdir($tmp, 0700, true);
            }
            $this->storeDir = is_dir($tmp) && is_writable($tmp) ? $tmp : $this->storeDir;
        }
    }

    protected function loadForIp(string $ip)
    {
        $file = $this->storeDir . DIRECTORY_SEPARATOR . preg_replace('/[^a-z0-9_.-]/i', '_', $ip) . '.json';
        if (!is_file($file)) {
            return ['count' => 0, 'ts' => 0];
        }
        $c = @file_get_contents($file);
        $j = $c ? json_decode($c, true) : [];
        if (!is_array($j)) {
            $j = ['count' => 0, 'ts' => 0];
        }
        // if entry expired, reset
        if (time() - ($j['ts'] ?? 0) > $this->window) {
            return ['count' => 0, 'ts' => 0];
        }
        return $j;
    }

    protected function saveForIp(string $ip, array $data)
    {
        $file = $this->storeDir . DIRECTORY_SEPARATOR . preg_replace('/[^a-z0-9_.-]/i', '_', $ip) . '.json';
    // Suppress warnings and ignore failures to avoid breaking the request flow.
        @file_put_contents($file, json_encode($data));
    }

    public function handle(Request $request)
    {
        $path = $request->path ?? ($_SERVER['REQUEST_URI'] ?? '/');

    // only apply to /api/login
        if (strpos($path, '/api/login') === false) {
            return null;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $now = time();
        $entry = $this->loadForIp($ip);
        if (empty($entry['ts'])) {
            $entry['ts'] = $now;
        }
        $entry['count'] = ($entry['count'] ?? 0) + 1;
        $this->saveForIp($ip, $entry);

        if ($entry['count'] > $this->limit) {
            $retryAfter = ($entry['ts'] + $this->window) - $now;
            $resp = new Response(429, ['error' => true, 'message' => 'too many requests']);
            // set Retry-After header and allow caller to send
            $resp->setHeader('Retry-After', max(0, $retryAfter));
            return $resp;
        }

        return null;
    }
}
