<?php

namespace Ondine\Auth;

class SessionRepository
{
    protected $pdo;
    protected $secret;
    protected $currentSecretVersion = 1;
    protected $secrets = [];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->secret = \Env::get('REFRESH_TOKEN_SECRET', \Env::get('JWT_SECRET', 'changeme'));
        $this->currentSecretVersion = (int)\Env::get('REFRESH_TOKEN_SECRET_VERSION', 1);
    // load map of secrets if provided (JSON or comma-separated)
        $map = \Env::get('REFRESH_TOKEN_SECRETS', '');
        $this->secrets = [];
        if ($map) {
            // try JSON
            $decoded = json_decode($map, true);
            if (is_array($decoded)) {
                $this->secrets = $decoded;
            } else {
                // comma separated list -> versions starting at 1
                $parts = array_map('trim', explode(',', $map));
                foreach ($parts as $i => $val) {
                    $this->secrets[$i + 1] = $val;
                }
            }
        }
        // ensure current version exists in map
        if (!isset($this->secrets[$this->currentSecretVersion])) {
            $this->secrets[$this->currentSecretVersion] = $this->secret;
        }

        // Fail-fast in production if refresh secret is not configured
        $env = \Env::get('APP_ENV', \Env::get('ENV', \Env::get('APPLICATION_ENV', 'development')));
        if (strtolower($env) === 'production' && ($this->secret === null || $this->secret === '' || $this->secret === 'changeme')) {
            throw new \RuntimeException('Refresh token secret is not configured for production environment');
        }
    }

    public function create(int $userId, string $refreshToken, int $ttlSeconds = 60 * 60 * 24 * 30)
    {
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + $ttlSeconds);
        $hash = $this->hashTokenWithVersion($refreshToken, $this->currentSecretVersion);
        $stmt = $this->pdo->prepare('INSERT INTO sessions (user_id, refresh_token, issued_at, expires_at, revoked, secret_version) VALUES (:uid, :rt, :is, :ex, 0, :sv)');
        $stmt->execute([':uid' => $userId, ':rt' => $hash, ':is' => $now, ':ex' => $expires, ':sv' => $this->currentSecretVersion]);
        return $this->pdo->lastInsertId();
    }

    public function findByToken(string $refreshToken)
    {
        // try with current secret version first
        $hash = $this->hashTokenWithVersion($refreshToken, $this->currentSecretVersion);
        $stmt = $this->pdo->prepare('SELECT * FROM sessions WHERE refresh_token = :rt LIMIT 1');
        $stmt->execute([':rt' => $hash]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($row) {
            return $row;
        }

        // fallback: try other secrets if provided
        foreach ($this->secrets as $ver => $sec) {
            if ($ver === $this->currentSecretVersion) {
                continue;
            }
            $h = hash_hmac('sha256', $refreshToken, $sec);
            $stmt = $this->pdo->prepare('SELECT * FROM sessions WHERE refresh_token = :rt LIMIT 1');
            $stmt->execute([':rt' => $h]);
            $r = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($r) {
                return $r;
            }
        }
        return null;
    }

    public function revoke(string $refreshToken)
    {
        // try to revoke by trying all known secrets (should match the stored hash)
        $count = 0;
        foreach ($this->secrets as $ver => $sec) {
            $h = hash_hmac('sha256', $refreshToken, $sec);
            $stmt = $this->pdo->prepare('UPDATE sessions SET revoked = 1 WHERE refresh_token = :rt');
            $stmt->execute([':rt' => $h]);
            $count += $stmt->rowCount();
            if ($count) {
                break; // once revoked stop
            }
        }
        return $count;
    }

    /**
     * Revoke all sessions for a given user id.
     * Returns number of rows affected.
     */
    public function revokeAllForUser(int $userId)
    {
        $stmt = $this->pdo->prepare('UPDATE sessions SET revoked = 1 WHERE user_id = :uid AND revoked = 0');
        $stmt->execute([':uid' => $userId]);
        return $stmt->rowCount();
    }

    protected function hashToken(string $token)
    {
        return hash_hmac('sha256', $token, $this->secret);
    }

    protected function hashTokenWithVersion(string $token, int $version)
    {
        $sec = $this->secrets[$version] ?? $this->secret;
        return hash_hmac('sha256', $token, $sec);
    }

    public function purgeExpired()
    {
        $stmt = $this->pdo->prepare('DELETE FROM sessions WHERE expires_at < :now');
        $stmt->execute([':now' => date('Y-m-d H:i:s')]);
        return $stmt->rowCount();
    }

    /**
     * Rotate the refresh token for a given session id.
     * Replaces the stored hashed refresh token and updates timestamps.
     */
    public function rotate(int $sessionId, string $newRefreshToken, ?int $ttlSeconds = null)
    {
        $ttl = $ttlSeconds ?? (60 * 60 * 24 * 30);
        $now = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + $ttl);
        $hash = $this->hashTokenWithVersion($newRefreshToken, $this->currentSecretVersion);
        $stmt = $this->pdo->prepare('UPDATE sessions SET refresh_token = :rt, issued_at = :is, expires_at = :ex, revoked = 0, secret_version = :sv WHERE id = :id');
        $stmt->execute([':rt' => $hash, ':is' => $now, ':ex' => $expires, ':sv' => $this->currentSecretVersion, ':id' => $sessionId]);
        return $stmt->rowCount();
    }
}
