<?php
return function (\PDO $pdo) {
    $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
    if ($driver === 'sqlite') {
        $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        refresh_token VARCHAR(255) NOT NULL UNIQUE,
        issued_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        revoked INTEGER NOT NULL DEFAULT 0,
        secret_version INTEGER NOT NULL DEFAULT 1
    )");
        return;
    }

    // For MySQL/MariaDB
    $pdo->exec("CREATE TABLE IF NOT EXISTS sessions (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        refresh_token VARCHAR(255) NOT NULL UNIQUE,
        issued_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        revoked TINYINT NOT NULL DEFAULT 0,
        secret_version INT NOT NULL DEFAULT 1,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
};
