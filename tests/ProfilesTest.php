<?php

namespace Tests;

class ProfilesTest extends BaseTestCase
{
    public function testIndexReturnsProfiles()
    {
        // Create a test profile
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('admin', '{\"read\":true,\"write\":true}')");

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $res = $ctrl->index($req, []);

        $this->assertArrayHasKey('data', $res);
        $this->assertIsArray($res['data']);
        $this->assertGreaterThanOrEqual(1, count($res['data'])); // At least the seed profile
        $this->assertIsArray($res['data'][0]['permissions']);
    }

    public function testShowReturnsProfile()
    {
        // Create a test profile
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('user', '{\"read\":true}')");
        $id = $pdo->lastInsertId();

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $res = $ctrl->show($req, ['id' => $id]);

        $this->assertArrayHasKey('data', $res);
        $this->assertEquals('user', $res['data']['name']);
        $this->assertIsArray($res['data']['permissions']);
    }

    public function testShowReturns404ForInvalidId()
    {
        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $res = $ctrl->show($req, ['id' => 999]);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(404, $res->getStatus());
        $this->assertArrayHasKey('error', $res->getData());
    }

    public function testStoreCreatesProfile()
    {
        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['name' => 'moderator', 'permissions' => ['read' => true, 'moderate' => true]];
        $res = $ctrl->store($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(201, $res->getStatus());
        $this->assertArrayHasKey('id', $res->getData());
    }

    public function testStoreReturns400ForMissingName()
    {
        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['permissions' => ['read' => true]];
        $res = $ctrl->store($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(400, $res->getStatus());
        $this->assertArrayHasKey('error', $res->getData());
    }

    public function testUpdateModifiesProfile()
    {
        // Create a test profile
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('oldname', '{\"old\":true}')");
        $id = $pdo->lastInsertId();

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['name' => 'newname'];
        $res = $ctrl->update($req, ['id' => $id]);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);

        // Verify change
        $stmt = $pdo->prepare('SELECT name FROM profiles WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertEquals('newname', $row['name']);
    }

    public function testUpdateReturns0ForNoChanges()
    {
        // Create a test profile
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('test', '{}')");
        $id = $pdo->lastInsertId();

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $req->parsedBody = []; // empty body
        $res = $ctrl->update($req, ['id' => $id]);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(0, $res['updated']);
    }

    public function testDeleteRemovesProfile()
    {
        // Create a test profile
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('deleteme', '{}')");
        $id = $pdo->lastInsertId();

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $res = $ctrl->delete($req, ['id' => $id]);

        $this->assertArrayHasKey('deleted', $res);
        $this->assertEquals(1, $res['deleted']);

        // Verify deletion
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM profiles WHERE id = ?');
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        $this->assertEquals(0, $count);
    }

    public function testDistinctPermissionsReturnsUniqueKeys()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('p1', '{\"a\":true,\"b\":false}')");
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('p2', '{\"b\":true,\"c\":true}')");
        $pdo->exec("INSERT INTO profiles (name, permissions) VALUES ('p3', '{}')");

        $ctrl = new \Ondine\Controllers\ProfilesController();
        $req = new \Ondine\Request();
        $res = $ctrl->distinctPermissions($req, []);

        $this->assertArrayHasKey('data', $res);
        $this->assertIsArray($res['data']);
        $this->assertContains('a', $res['data']);
        $this->assertContains('b', $res['data']);
        $this->assertContains('c', $res['data']);
        // Note: seed profile may add more, but at least these
        $this->assertGreaterThanOrEqual(3, count($res['data']));
    }
}