<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }
require_once __DIR__ . '/../src/Database/Database.php';
require_once __DIR__ . '/../src/Controllers/UsersController.php';
require_once __DIR__ . '/../src/Request.php';

$pdo = \Ondine\Database\Database::getConnection();
$req = new \Ondine\Request();
$req->parsedBody = ['first_name' => 'Test', 'last_name' => 'User', 'username' => 'testdup', 'password' => 'secret12'];
$ctrl = new \Ondine\Controllers\UsersController();
$res = $ctrl->store($req, []);
var_export($res);
echo "\n";
