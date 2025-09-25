<?php

namespace Tests;

class AuthRefreshFlowTest extends BaseTestCase
{
    public function testLoginRefreshLogoutFlow()
    {
    $pdo = \Ondine\Database\Database::getConnection();
    $sessionRepo = new \Ondine\Auth\SessionRepository($pdo);
    $ctrl = new \Ondine\Controllers\AuthController($pdo, $sessionRepo);

        // create a test user via repository to login
        $pdo = \Ondine\Database\Database::getConnection();
        $userRepo = new \Ondine\Database\Repository\UserRepository($pdo);
        $username = 'flowuser' . uniqid();
        $password = 'secret123';
        $id = $userRepo->create(['first_name' => 'F', 'last_name' => 'L', 'username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)]);

        // login
        $req = new \Ondine\Request();
        $req->parsedBody = ['username' => $username, 'password' => $password];
        $res = $ctrl->login($req, []);
        $this->assertArrayHasKey('token', $res);
        $this->assertArrayHasKey('refresh_token', $res);

        $refresh = $res['refresh_token'];

        // refresh
        $req2 = new \Ondine\Request();
        $req2->parsedBody = ['refresh_token' => $refresh];
        $res2 = $ctrl->refresh($req2, []);
        $this->assertArrayHasKey('token', $res2);

        // logout
        $req3 = new \Ondine\Request();
        $req3->parsedBody = ['refresh_token' => $refresh];
        $res3 = $ctrl->logout($req3, []);
        $this->assertEquals(['ok' => true], $res3);

        // attempting to refresh again should fail
        $req4 = new \Ondine\Request();
        $req4->parsedBody = ['refresh_token' => $refresh];
        $res4 = $ctrl->refresh($req4, []);
        $this->assertArrayHasKey('error', $res4);
    }
}
