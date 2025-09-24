<?php

namespace Tests;

class SessionRepositoryTest extends BaseTestCase
{
    public function testCreateFindRevokeAndPurge()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $repo = new \Ondine\Auth\SessionRepository($pdo);

        $uid = 1;
        $token = bin2hex(random_bytes(16));
        $repo->create($uid, $token, 1); // 1 second ttl

        $found = $repo->findByToken($token);
        $this->assertNotNull($found);
        $this->assertEquals($uid, (int)$found['user_id']);

        // revoke
        $repo->revoke($token);
        $found2 = $repo->findByToken($token);
        $this->assertEquals(1, (int)$found2['revoked']);

    // create an already-expired token by using a negative TTL (expires in the past)
        $token2 = bin2hex(random_bytes(16));
        $repo->create($uid, $token2, -1800);

        $deleted = $repo->purgeExpired();
        $this->assertGreaterThanOrEqual(1, $deleted);
    }
}
