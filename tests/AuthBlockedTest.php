<?php

namespace Tests;

class AuthBlockedTest extends BaseTestCase
{
    public function testBlockedUserCannotLogin()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $repo = new \Ondine\Database\Repository\UserRepository($pdo);

        // create blocked user
        $username = 'bloqueado' . uniqid();
        $id = $repo->create([
            'first_name' => 'Bloqueado',
            'last_name' => 'User',
            'username' => $username,
            'password' => password_hash('secret123', PASSWORD_ARGON2I),
            'state' => 0,
        ]);

    $authCtrl = new \Ondine\Controllers\AuthController($pdo);

        $req = new \Ondine\Request();
        $req->parsedBody = ['username' => $username, 'password' => 'secret123'];

        $res = $authCtrl->login($req, []);

        // should return error and set HTTP status 403
        $this->assertArrayHasKey('error', $res);
        $this->assertEquals('user blocked', $res['message']);
        $this->assertEquals(403, \Ondine\Response::getLastStatus());
    }
}
