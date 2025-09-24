<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }
require_once __DIR__ . '/../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
$stmt = $pdo->query('SELECT id, name, permissions FROM profiles');
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
var_export($rows);
echo "\n";
