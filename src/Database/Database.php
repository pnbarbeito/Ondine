<?php

namespace Ondine\Database;

class Database
{
    protected static $pdo;

    /**
     * Detect the project root directory.
     * When installed as a composer dependency, use the current working directory.
     * When developing the framework itself, use the framework directory.
     */
    private static function detectProjectRoot()
    {
        // Prefer the current working directory if it looks like a project root
        $cwd = getcwd();
        if ($cwd) {
            if (file_exists($cwd . '/composer.json') || file_exists($cwd . '/config/config.php')) {
                return $cwd;
            }
            // Walk up from cwd a few levels looking for composer.json
            $p = $cwd;
            for ($i = 0; $i < 6; $i++) {
                if (file_exists($p . '/composer.json') || file_exists($p . '/config/config.php')) {
                    return $p;
                }
                $parent = dirname($p);
                if ($parent === $p) {
                    break;
                }
                $p = $parent;
            }
        }

        // If the package is installed in vendor, derive project root from the vendor segment
        $dir = __DIR__;
        $vendorSeg = DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
        $pos = strpos($dir, $vendorSeg);
        if ($pos !== false) {
            $possibleRoot = substr($dir, 0, $pos);
            if ($possibleRoot && file_exists($possibleRoot . '/composer.json')) {
                return realpath($possibleRoot) ?: $possibleRoot;
            }
        }

        // Fallback to framework directory (useful for developing the library itself)
        $frameworkRoot = realpath(__DIR__ . '/../../');
        if ($frameworkRoot !== false) {
            return $frameworkRoot;
        }

        return __DIR__ . '/../../';
    }

    public static function getConnection()
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        // load .env if present - try project root first, then framework
        if (file_exists(__DIR__ . '/../../src/Env.php')) {
            require_once __DIR__ . '/../../src/Env.php';
            $projectRoot = self::detectProjectRoot();
            $envPath = $projectRoot . '/config/.env';
            if (file_exists($envPath)) {
                \Env::load($envPath);
            } else {
                // Fallback to framework .env
                \Env::load(__DIR__ . '/../../config/.env');
            }
        }

    // Prefer explicit driver from environment, otherwise null
        $driver = \Env::get('DB_DRIVER', null);
        if (!$driver) {
            $config = require __DIR__ . '/../../config/config.php';
            $driver = $config['db']['driver'] ?? 'sqlite';
        }

        if ($driver === 'sqlite') {
            $path = \Env::get('DB_SQLITE_PATH', (require __DIR__ . '/../../config/config.php')['db']['sqlite']['path']);
            // if path is not absolute, resolve against project root for consistent behavior
            // When installed as a composer dependency, use current working directory as project root
            // Otherwise, use the framework directory (for development)
            $projectRoot = self::detectProjectRoot();
            $isAbsolute = preg_match('/^[a-zA-Z]:[\\\\\/]/', $path) === 1;
            if (!$isAbsolute) {
                $path = $projectRoot . DIRECTORY_SEPARATOR . ltrim($path, "./\\");
            }
            $path = str_replace(['\\'], ['\\'], $path);
            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $dsn = 'sqlite:' . $path;
            $pdo = new \PDO($dsn);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            self::$pdo = $pdo;
            return $pdo;
        }

        if ($driver === 'mariadb') {
            $host = \Env::get('MYSQL_HOST', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['host']);
            $port = \Env::get('MYSQL_PORT', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['port']);
            $database = \Env::get('MYSQL_DATABASE', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['database']);
            $user = \Env::get('MYSQL_USER', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['user']);
            $password = \Env::get('MYSQL_PASSWORD', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['password']);
            $charset = \Env::get('MYSQL_CHARSET', (require __DIR__ . '/../../config/config.php')['db']['mariadb']['charset']);

            // First, attempt to connect to the server without specifying a database so
            // we can create the database if it doesn't exist (common for provisioning).
            $serverDsn = sprintf('mysql:host=%s;port=%d;charset=%s', $host, $port, $charset);
            try {
                $serverPdo = new \PDO($serverDsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            } catch (\PDOException $e) {
                // Re-throw with context
                throw new \RuntimeException('Could not connect to MariaDB server: ' . $e->getMessage(), (int)$e->getCode(), $e);
            }

            // Create the database if it does not exist
            try {
                $quotedDb = str_replace('`', '``', $database);
                $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `$quotedDb` CHARACTER SET $charset COLLATE {$charset}_general_ci");
            } catch (\Throwable $e) {
                // If creation fails, throw with context but do not suppress original error
                throw new \RuntimeException('Failed to create database ' . $database . ': ' . $e->getMessage(), (int)($e->getCode() ?: 0), $e);
            }

            // Now connect specifying the database
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $host, $port, $database, $charset);
            $pdo = new \PDO($dsn, $user, $password, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            self::$pdo = $pdo;
            return $pdo;
        }

        throw new \RuntimeException('Unsupported DB driver: ' . $driver);
    }

    public static function beginTransaction()
    {
        $pdo = self::getConnection();
        return $pdo->beginTransaction();
    }

    public static function commit()
    {
        $pdo = self::getConnection();
        return $pdo->commit();
    }

    public static function rollBack()
    {
        $pdo = self::getConnection();
        return $pdo->rollBack();
    }

    public static function execute($sql, $params = [])
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
