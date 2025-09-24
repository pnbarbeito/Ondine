<?php
return [
    'up' => function ($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS ux_users_username ON users(username);");
        } else {
            // MySQL / MariaDB
            $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX ux_users_username (username);");
        }
    },
    'down' => function ($pdo) {
        $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver === 'sqlite') {
            // SQLite has limited DROP INDEX support
            $pdo->exec("DROP INDEX IF EXISTS ux_users_username;");
        } else {
            $pdo->exec("ALTER TABLE users DROP INDEX ux_users_username;");
        }
    }
];
