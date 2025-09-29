<?php

namespace Tests;

class UsersDuplicateTest extends BaseTestCase
{
    public function testDuplicateUserReturns409()
    {
        // autoload provided by BaseTestCase
        $uniq = 'testdup' . uniqid();
        $req1 = new \Ondine\Request();
        $req1->parsedBody = ['first_name' => 'Test', 'last_name' => 'User', 'username' => $uniq, 'password' => 'secret12'];
        $ctrl = new \Ondine\Controllers\UsersController();
        $res1 = $ctrl->store($req1, []);
        $this->assertArrayHasKey('id', $res1->getData());

        // attempt duplicate
        $req2 = new \Ondine\Request();
        $req2->parsedBody = ['first_name' => 'Test', 'last_name' => 'User2', 'username' => $uniq, 'password' => 'secret12'];
        $res2 = $ctrl->store($req2, []);
        $this->assertArrayHasKey('error', $res2->getData());
        $this->assertEquals(true, $res2->getData()['error']);
        $this->assertEquals('username already exists', $res2->getData()['message']);
    }
}
