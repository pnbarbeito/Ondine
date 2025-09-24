<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }
require_once __DIR__ . '/../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
if ($driver !== 'sqlite') {
    echo "This script is intended for sqlite indexes. Driver=$driver\n";
    exit(1);
}
$indexes = $pdo->query("PRAGMA index_list('users')")->fetchAll(PDO::FETCH_ASSOC);
echo "Indexes on users:\n";
print_r($indexes);
foreach ($indexes as $idx) {
    $name = $idx['name'];
    echo "Index: $name\n";
    $info = $pdo->query("PRAGMA index_info('$name')")->fetchAll(PDO::FETCH_ASSOC);
    print_r($info);
}
