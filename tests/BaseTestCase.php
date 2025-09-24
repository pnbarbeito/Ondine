<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

// Base test case that prepares an in-memory SQLite DB and runs migrations.
class BaseTestCase extends TestCase
{
    protected $testSqliteFile = null;
    protected function setUp(): void
    {
        // Ensure autoload is available
        $vendor = __DIR__ . '/../vendor/autoload.php';
        if (file_exists($vendor)) {
            require_once $vendor;
        }

    // Force DB config to a temporary sqlite file via env vars
        putenv('DB_DRIVER=sqlite');
    // use a relative path so Database::getConnection resolves it against project root
        $tmpFileName = 'data' . DIRECTORY_SEPARATOR . 'test_db_' . uniqid() . '.sqlite';
        putenv('DB_SQLITE_PATH=' . $tmpFileName);
    // remember for cleanup (resolve to project root)
        $this->testSqliteFile = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . $tmpFileName;

        // Reset the protected static PDO on Ondine\Database\Database using Reflection
        if (class_exists('\Ondine\Database\Database')) {
            $rc = new \ReflectionClass('\Ondine\Database\Database');
            if ($rc->hasProperty('pdo')) {
                $prop = $rc->getProperty('pdo');
                $prop->setAccessible(true);
                $prop->setValue(null, null);
            }
        }

        // Run migrations (Migrator::migrate)
        if (class_exists('\Ondine\Database\Migrator')) {
            \Ondine\Database\Migrator::migrate();
        }
    }

    protected function tearDown(): void
    {
        // Reset PDO again to clean state
        if (class_exists('\Ondine\Database\Database')) {
            $rc = new \ReflectionClass('\Ondine\Database\Database');
            if ($rc->hasProperty('pdo')) {
                $prop = $rc->getProperty('pdo');
                $prop->setAccessible(true);
                $prop->setValue(null, null);
            }
        }

        // remove temp sqlite file if created
        if (!empty($this->testSqliteFile) && file_exists($this->testSqliteFile)) {
            @unlink($this->testSqliteFile);
        }
    }
}
