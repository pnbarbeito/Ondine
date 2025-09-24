<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }
require_once __DIR__ . '/../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver === 'sqlite') {
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_ASSOC);
} else {
    // MariaDB / MySQL
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);
}
echo "Tables:\n";
print_r($tables);
