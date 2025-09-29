<?php

namespace Tests;

class UsersTest extends BaseTestCase
{
    public function testIndexReturnsUsers()
    {
        // Create a test user
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $userRepo->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser' . uniqid(),
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $res = $ctrl->index($req, []);

        $this->assertArrayHasKey('data', $res);
        $this->assertIsArray($res['data']);
        $this->assertGreaterThanOrEqual(1, count($res['data']));
    }

    public function testShowReturnsUser()
    {
        // Create a test user
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $id = $userRepo->create([
            'first_name' => 'Show',
            'last_name' => 'User',
            'username' => 'showuser' . uniqid(),
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $res = $ctrl->show($req, ['id' => $id]);

        $this->assertArrayHasKey('data', $res);
        $this->assertEquals('Show', $res['data']['first_name']);
    }

    public function testShowReturns404ForInvalidId()
    {
        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $res = $ctrl->show($req, ['id' => 999]);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(404, $res->getStatus());
    }

    public function testStoreCreatesUser()
    {
        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $req->parsedBody = [
            'first_name' => 'New',
            'last_name' => 'User',
            'username' => 'newuser' . uniqid(),
            'password' => 'secret123'
        ];
        $res = $ctrl->store($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(201, $res->getStatus());
        $this->assertArrayHasKey('id', $res->getData());
    }

    public function testStoreReturns400ForInvalidData()
    {
        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $req->parsedBody = [
            'first_name' => 'N',
            'last_name' => 'U',
            'username' => 'nu',
            'password' => '123' // too short
        ];
        $res = $ctrl->store($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(400, $res->getStatus());
        $this->assertArrayHasKey('error', $res->getData());
    }

    public function testUpdateModifiesUser()
    {
        // Create a test user
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $id = $userRepo->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
            'username' => 'olduser' . uniqid(),
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['first_name' => 'Updated'];
        $res = $ctrl->update($req, ['id' => $id]);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);
    }

    public function testDeleteRemovesUser()
    {
        // Create a test user
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $id = $userRepo->create([
            'first_name' => 'Delete',
            'last_name' => 'Me',
            'username' => 'deleteme' . uniqid(),
            'password' => password_hash('secret', PASSWORD_DEFAULT)
        ]);

        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $res = $ctrl->delete($req, ['id' => $id]);

        $this->assertArrayHasKey('deleted', $res);
        $this->assertEquals(1, $res['deleted']);
    }

    public function testChangePasswordUpdatesPassword()
    {
        // Create a test user
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $id = $userRepo->create([
            'first_name' => 'Pass',
            'last_name' => 'Change',
            'username' => 'passuser' . uniqid(),
            'password' => password_hash('oldpass', PASSWORD_DEFAULT)
        ]);

        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['new_password' => 'newpass123'];
        $res = $ctrl->changePassword($req, ['id' => $id]);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);
    }

    public function testChangePasswordReturns400ForInvalidPassword()
    {
        $ctrl = new \Ondine\Controllers\UsersController();
        $req = new \Ondine\Request();
        $req->parsedBody = ['new_password' => '123']; // too short
        $res = $ctrl->changePassword($req, ['id' => 1]);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(400, $res->getStatus());
    }
}