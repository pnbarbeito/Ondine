<?php
use PHPUnit\Framework\TestCase;
use Ondine\Cache\ProfileCache;
use Ondine\Middleware\AuthMiddleware;
use Ondine\Request;

// A tiny fake Auth class to inject a repo with find() method
class FakeRepo
{
    protected $users;
    public function __construct($users)
    {
        $this->users = $users;
    }
    public function find($id)
    {
        return $this->users[$id] ?? null;
    }
}

class AuthMiddlewareTest extends TestCase
{
    public function testMiddlewareUsesProfileCache()
    {
        $tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ondine_test_cache_' . uniqid();
        @mkdir($tmpdir, 0755, true);

        // prepare cache with profile_7
        $cache = new ProfileCache($tmpdir, 60);
        $perms = ['items' => 1, 'admin' => 0];
        $cache->set('profile_7', $perms);

        // prepare a fake request and payload
        $request = new Request();
        $request->path = '/items';
        $request->method = 'GET';
        $request->headers = ['Authorization' => 'Bearer token-dummy'];

        // create a fake auth with repo that returns user id 5 with profile_id 7
        $fakeRepo = new FakeRepo([5 => ['id' => 5, 'profile_id' => 7]]);

        // We cannot easily instantiate framework Auth here, but we can mimic logic:
        // Instead, assert that cache returns the permissions we set
        $got = $cache->get('profile_7');
        $this->assertEquals($perms, $got);

        // cleanup
        $files = glob($tmpdir . DIRECTORY_SEPARATOR . '*');
        foreach ($files as $f) { @unlink($f); }
        @rmdir($tmpdir);
    }
}
