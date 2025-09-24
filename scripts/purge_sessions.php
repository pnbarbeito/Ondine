<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }
require_once __DIR__ . '/../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
$repo = new \Ondine\Auth\SessionRepository($pdo);
$deleted = $repo->purgeExpired();
echo "Purged $deleted expired sessions\n";
