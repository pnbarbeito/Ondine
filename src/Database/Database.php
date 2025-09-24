<?php

namespace Ondine\Database;

class Database
{
    protected static $pdo;

    public static function getConnection()
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        // load .env if present
        if (file_exists(__DIR__ . '/../../src/Env.php')) {
            require_once __DIR__ . '/../../src/Env.php';
            \Env::load(__DIR__ . '/../../config/.env');
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
            $projectRoot = realpath(__DIR__ . '/../../');
            if ($projectRoot === false) {
                $projectRoot = __DIR__ . '/../../';
            }
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
