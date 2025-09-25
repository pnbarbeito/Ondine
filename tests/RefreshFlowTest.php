<?php
use PHPUnit\Framework\TestCase;

class RefreshFlowTest extends TestCase
{
    protected $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        // run sessions migration (minimal)
        $this->pdo->exec("CREATE TABLE sessions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            refresh_token TEXT,
            issued_at TEXT,
            expires_at TEXT,
            revoked INTEGER DEFAULT 0,
            secret_version INTEGER DEFAULT 1
        )");

        // create dummy user table for repo.findWithProfile usage
        $this->pdo->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT,
            profile_id INTEGER
        )");
        $this->pdo->exec("INSERT INTO users (username, profile_id) VALUES ('alice', 1)");
    }

    public function testRefreshRotatesToken()
    {
        // bootstrap repos and controller
        $repo = new \Ondine\Database\Repository\UserRepository($this->pdo);
        $sessionRepo = new \Ondine\Auth\SessionRepository($this->pdo);

        // create initial refresh token and session
        $refresh = bin2hex(random_bytes(16));
        $uid = 1;
        $sessionRepo->create($uid, $refresh, 60*60);

    // build controller that uses our PDO and sessionRepo (DI)
    $authController = new \Ondine\Controllers\AuthController($this->pdo, $sessionRepo);

        $found = $sessionRepo->findByToken($refresh);
        $this->assertNotNull($found);
        $sid = (int)$found['id'];

        // rotate
        $newRefresh = bin2hex(random_bytes(16));
        $sessionRepo->rotate($sid, $newRefresh, 60*60);

        $this->assertNull($sessionRepo->findByToken($refresh), 'old refresh should not be found after rotate');
        $this->assertNotNull($sessionRepo->findByToken($newRefresh), 'new refresh should be findable');
    }
}
