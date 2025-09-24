<?php

namespace Tests;

use Ondine\Middleware\RateLimitMiddleware;
use Ondine\Request;

class RateLimitTest extends BaseTestCase
{
    public function testRateLimitBlocksAfterLimit()
    {
        $tmpStore = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ratelimit_' . uniqid();
        if (!is_dir($tmpStore)) {
            mkdir($tmpStore, 0777, true);
        }
        $mw = new RateLimitMiddleware(['limit' => 3, 'window' => 60, 'store_dir' => $tmpStore]);

        // simulate three allowed requests
        for ($i = 0; $i < 3; $i++) {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            $req = new Request();
            $req->path = '/api/login';
            $res = $mw->handle($req);
            $this->assertNull($res, "Request $i should be allowed");
        }

        // fourth should be blocked
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $req = new Request();
        $req->path = '/api/login';
        $res = $mw->handle($req);
        $this->assertNotNull($res);
        $this->assertInstanceOf(\Ondine\Response::class, $res);

        // send would produce 429
        $this->assertEquals(429, (new \ReflectionClass($res))->getProperty('status')->getValue($res));
        // Retry-After header must exist
        $rHeaders = (new \ReflectionClass($res))->getProperty('headers')->getValue($res);
        $this->assertArrayHasKey('Retry-After', $rHeaders);
    }
}
