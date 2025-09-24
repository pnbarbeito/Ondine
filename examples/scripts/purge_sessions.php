<?php
// Prefer Composer autoload if available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
	require_once __DIR__ . '/../../vendor/autoload.php';
} else {
	require_once __DIR__ . '/../../src/autoload.php';
	require_once __DIR__ . '/../../src/Env.php';
	if (class_exists('\\Env', false) || class_exists('Env', false)) {
		\Env::load(__DIR__ . '/../../config/.env');
	}
}
require_once __DIR__ . '/../../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
$repo = new \Ondine\Auth\SessionRepository($pdo);
$deleted = $repo->purgeExpired();
echo "Purged $deleted expired sessions\n";
