<?php
use PHPUnit\Framework\TestCase;
use Ondine\Cache\ProfileCache;

class CacheTest extends TestCase
{
    protected $tmpdir;

    protected function setUp(): void
    {
        $this->tmpdir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ondine_test_cache_' . uniqid();
        @mkdir($this->tmpdir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tmpdir)) {
            $files = glob($this->tmpdir . DIRECTORY_SEPARATOR . '*');
            if ($files) {
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
            @rmdir($this->tmpdir);
        }
    }

    public function testSetGetDelete()
    {
        $cache = new ProfileCache($this->tmpdir, 5);
        $key = 'profile_42';
        $value = ['read' => 1, 'write' => 0];

        $this->assertTrue($cache->set($key, $value));
        $got = $cache->get($key);
        $this->assertEquals($value, $got);

        $this->assertTrue($cache->delete($key));
        $this->assertNull($cache->get($key));
    }

    public function testTtlExpires()
    {
        $cache = new ProfileCache($this->tmpdir, 1);
        $key = 'profile_43';
        $value = ['foo' => 'bar'];
        $cache->set($key, $value);
        $this->assertEquals($value, $cache->get($key));
        // sleep to expire
        sleep(2);
        $this->assertNull($cache->get($key));
    }
}
