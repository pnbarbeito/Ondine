<?php

namespace Ondine\Middleware;

use Ondine\Request;
use Ondine\Response;
use Ondine\Auth\Auth;
use Ondine\Database\Database;
use Ondine\Cache\ProfileCache;

class AuthMiddleware
{
    protected $except = ['/login'];
    protected $auth;
    protected $cache;

    public function __construct(array $options = [])
    {
        // allow overriding exceptions
        if (isset($options['except'])) {
            $this->except = $options['except'];
        }

        // build Auth using repo
        $pdo = Database::getConnection();
        $repo = new \Ondine\Database\Repository\UserRepository($pdo);
        $secret = \Env::get('JWT_SECRET', 'changeme');
        $this->auth = new Auth($repo, $secret);

        // lightweight file-based cache for profile lookups (no external deps)
        $this->cache = new ProfileCache();
    }

    public function handle(Request $request)
    {
        $path = trim($request->path, '/');
        // skip exceptions
        foreach ($this->except as $ex) {
            if ($ex === $request->path || $ex === '/' . $path) {
                return;
            }
        }

        // allow unauthenticated access to login and me endpoints (root or under /api)
        if ($path === 'login' || $path === 'me' || $path === 'api/login' || $path === 'api/me') {
            return;
        }

        // Expect Authorization header
        $hdr = $request->headers['Authorization'] ?? ($request->headers['authorization'] ?? null);
        if (!$hdr) {
            return new Response(401, ['error' => true, 'message' => 'missing token']);
        }
        if (stripos($hdr, 'Bearer ') === 0) {
            $token = trim(substr($hdr, 7));
        } else {
            $token = trim($hdr);
        }

        $payload = $this->auth->verifyToken($token);
        if (!$payload) {
            return new Response(401, ['error' => true, 'message' => 'invalid token']);
        }

        // attach to request for controllers
        // We'll cache permissions per profile (key = profile_{id}).
        $uid = $payload['sub'] ?? null;
        if (!$uid) {
            return new Response(401, ['error' => true, 'message' => 'invalid token']);
        }

        // load minimal user info (includes profile_id)
        $user = $this->auth->getRepo()->find($uid);
        if (!$user) {
            return new Response(401, ['error' => true, 'message' => 'unknown user']);
        }

        $profileId = isset($user['profile_id']) ? (int)$user['profile_id'] : null;
        $perms = null;
        if ($profileId) {
            try {
                $perms = $this->cache->get('profile_' . $profileId);
            } catch (\Throwable $e) {
                $perms = null;
            }
        }

        if ($perms === null && $profileId) {
            // fetch permissions from profiles table and cache them
            try {
                $pdo = Database::getConnection();
                $stmt = $pdo->prepare('SELECT permissions FROM profiles WHERE id = :id');
                $stmt->execute([':id' => $profileId]);
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['permissions'])) {
                    $decoded = json_decode($row['permissions'], true);
                    if ($decoded === null) {
                        $clean = stripslashes($row['permissions']);
                        $decoded = json_decode($clean, true);
                    }
                    $perms = $decoded !== null ? $decoded : null;
                } else {
                    $perms = null;
                }
            } catch (\Throwable $e) {
                $perms = null;
            }

            try {
                // cache even null so we avoid repeated DB hits for missing permissions
                $this->cache->set('profile_' . $profileId, $perms);
            } catch (\Throwable $e) {
                // ignore cache write failures
            }
        }

        // Attach profile_permissions to the user array for controllers
        $user['profile_permissions'] = $perms;
        $request->user = $user;
        $request->token_payload = $payload;

    // if requesting /me (root or under /api), allow for any authenticated user (no endpoint permission required)
        if ($path === 'me' || $path === 'api/me') {
            return;
        }

        // determine permission name from path (first segment)
        $segments = explode('/', $path);
        // support /api/<resource> grouping
        $first = $segments[0];
        if ($first === 'api') {
            $resource = $segments[1] ?? '';
        } else {
            $resource = $first;
        }
        // map common aliases
        $permName = $resource;

        // load permissions from profile (findWithProfile returns 'profile_permissions')
        $perms = $request->user['profile_permissions'] ?? null; // decoded array or null

        // If profile explicitly has admin => truthy, bypass permission checks
        if (is_array($perms) && !empty($perms['admin'])) {
            return; // admin has full access
        }

        $hasPerm = null;
        if (is_array($perms) && array_key_exists($permName, $perms)) {
            $hasPerm = $perms[$permName];
        }

        $method = strtoupper($request->method ?? 'GET');

        // Permission rules:
        // - absent => no permission
        // - 0 => read-only (GET allowed)
        // - 1 => read/write (GET, POST, PUT, DELETE allowed)

        if ($hasPerm === null) {
            return new Response(403, ['error' => true, 'message' => 'forbidden: no permission']);
        }

        if ($hasPerm == 0 && $method !== 'GET') {
            return new Response(403, ['error' => true, 'message' => 'forbidden: read-only']);
        }

        // else allowed
        return null;
    }
}
