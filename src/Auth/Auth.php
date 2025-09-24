<?php

namespace Ondine\Auth;

use Ondine\Database\Repository\UserRepository;

class Auth
{
    protected $repo;
    protected $secret;

    public function __construct(UserRepository $repo, string $secret)
    {
        $this->repo = $repo;
        $this->secret = $secret;
        // Fail-fast in production if secret is not configured
        $env = \Env::get('APP_ENV', \Env::get('ENV', \Env::get('APPLICATION_ENV', 'development')));
        if (strtolower($env) === 'production' && ($this->secret === null || $this->secret === '' || $this->secret === 'changeme')) {
            throw new \RuntimeException('JWT secret is not configured for production environment');
        }
    }

    public function login($username, $password)
    {
        $user = $this->repo->findByUsername($username);
        if (!$user) {
            return null;
        }

        // user must be active (state = 1)
        $state = $user['state'] ?? null;
        if (isset($state) && (int)$state === 0) {
            // blocked user cannot login
            return null;
        }

        if (!password_verify($password, $user['password'])) {
            return null;
        }

        $payload = [
            'sub' => (int)$user['id'],
            'username' => $user['username'] ?? null,
            'first_name' => $user['first_name'] ?? null,
            'last_name' => $user['last_name'] ?? null,
            'profile_id' => $user['profile_id'] ?? null,
        ];

        return Jwt::encode($payload, $this->secret, 3600 * 8);
    }

    public function verifyToken($token)
    {
        $payload = Jwt::decode($token, $this->secret);
        if (!$payload) {
            return null;
        }
        return $payload;
    }

    public function getRepo()
    {
        return $this->repo;
    }

    /**
     * Returns true if the user exists and is blocked (state == 0)
     */
    public function isUserBlocked(string $username): bool
    {
        $user = $this->repo->findByUsername($username);
        if (!$user) {
            return false;
        }
        return isset($user['state']) && (int)$user['state'] === 0;
    }
}
