<?php

namespace Tests;

class UserTest extends BaseTestCase
{
    private function createUserAndGetToken()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $sessionRepo = new \Ondine\Auth\SessionRepository($pdo);
        $authCtrl = new \Ondine\Controllers\AuthController($pdo, $sessionRepo);

        $username = 'usertest' . uniqid();
        $password = 'secret123';
        $userRepo->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => $username,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ]);

        // Login to get token
        $req = new \Ondine\Request();
        $req->parsedBody = ['username' => $username, 'password' => $password];
        $res = $authCtrl->login($req, []);
        return $res->getData()['token'];
    }

    public function testSetThemeUpdatesUserTheme()
    {
        $token = $this->createUserAndGetToken();

        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer ' . $token;
        $req->parsedBody = ['theme' => 'light'];
        $res = $ctrl->setTheme($req, []);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);
    }

    public function testSetThemeReturns401ForInvalidToken()
    {
        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer invalid';
        $req->parsedBody = ['theme' => 'dark'];
        $res = $ctrl->setTheme($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(401, $res->getStatus());
    }

    public function testUpdateProfileUpdatesUserInfo()
    {
        $token = $this->createUserAndGetToken();

        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer ' . $token;
        $req->parsedBody = ['first_name' => 'Updated', 'last_name' => 'Name'];
        $res = $ctrl->updateProfile($req, []);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);
    }

    public function testUpdateProfileReturns400ForInvalidData()
    {
        $token = $this->createUserAndGetToken();

        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer ' . $token;
        $req->parsedBody = ['first_name' => 'A']; // too short
        $res = $ctrl->updateProfile($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(400, $res->getStatus());
        $this->assertArrayHasKey('error', $res->getData());
    }

    public function testChangePasswordUpdatesPassword()
    {
        $token = $this->createUserAndGetToken();

        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer ' . $token;
        $req->parsedBody = ['current_password' => 'secret123', 'new_password' => 'newsecret456'];
        $res = $ctrl->changePassword($req, []);

        $this->assertArrayHasKey('updated', $res);
        $this->assertEquals(1, $res['updated']);
    }

    public function testChangePasswordReturns401ForWrongCurrentPassword()
    {
        $token = $this->createUserAndGetToken();

        $ctrl = new \Ondine\Controllers\UserController();
        $req = new \Ondine\Request();
        $req->headers['Authorization'] = 'Bearer ' . $token;
        $req->parsedBody = ['current_password' => 'wrongpass', 'new_password' => 'newsecret456'];
        $res = $ctrl->changePassword($req, []);

        $this->assertInstanceOf(\Ondine\Response::class, $res);
        $this->assertEquals(401, $res->getStatus());
    }
}