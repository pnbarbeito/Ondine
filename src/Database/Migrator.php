<?php

namespace Ondine\Database;

class Migrator
{
    protected static function migrationsPath()
    {
        return __DIR__ . '/../../migrations';
    }

    public static function migrate()
    {
        $pdo = Database::getConnection();
        $path = self::migrationsPath();
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        // ensure migrations table
        $pdo->exec('CREATE TABLE IF NOT EXISTS migrations (id VARCHAR(100) PRIMARY KEY, ran_at DATETIME NOT NULL)');

        $files = glob($path . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $id = basename($file, '.php');
            $stmt = $pdo->prepare('SELECT 1 FROM migrations WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if ($stmt->fetch()) {
                continue; // already ran
            }

            $migration = require $file;
            $upCallable = null;
            if (is_callable($migration)) {
                $upCallable = $migration;
            } elseif (is_array($migration) && isset($migration['up']) && is_callable($migration['up'])) {
                $upCallable = $migration['up'];
            }

            if ($upCallable) {
                $pdo->beginTransaction();
                try {
                    $upCallable($pdo);
                    $stmt = $pdo->prepare('INSERT INTO migrations (id, ran_at) VALUES (:id, :ran_at)');
                    $stmt->execute([':id' => $id, ':ran_at' => date('Y-m-d H:i:s')]);
                    if ($pdo->inTransaction()) {
                        $pdo->commit();
                    }
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $e;
                }
            }
        }
    }

    public static function rollback($steps = 1)
    {
        $pdo = Database::getConnection();
        $path = self::migrationsPath();
        $files = glob($path . '/*.php');
        usort($files, function ($a, $b) {
            return strcmp($b, $a);
        }); // reverse

        $rolled = 0;
        foreach ($files as $file) {
            if ($rolled >= $steps) {
                break;
            }
            $id = basename($file, '.php');
            $stmt = $pdo->prepare('SELECT 1 FROM migrations WHERE id = :id');
            $stmt->execute([':id' => $id]);
            if (!$stmt->fetch()) {
                continue; // not ran
            }

            $migration = require $file;
            if (is_array($migration) && isset($migration['down']) && is_callable($migration['down'])) {
                $pdo->beginTransaction();
                try {
                    $migration['down']($pdo);
                    $del = $pdo->prepare('DELETE FROM migrations WHERE id = :id');
                    $del->execute([':id' => $id]);
                    if ($pdo->inTransaction()) {
                        $pdo->commit();
                    }
                    $rolled++;
                } catch (\Throwable $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $e;
                }
            }
        }
    }
}
