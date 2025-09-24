<?php
require_once __DIR__ . '/../../../src/autoload.php';
require_once __DIR__ . '/../../../src/Env.php';
\Env::load(__DIR__ . '/../../../config/.env');
require_once __DIR__ . '/../../../src/Database/Database.php';

$pdo = \Ondine\Database\Database::getConnection();
var_dump($pdo instanceof PDO);
