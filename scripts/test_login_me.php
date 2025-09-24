<?php
// Script de prueba: login y me usando las clases internas (sin servidor HTTP)
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }

use Ondine\Controllers\AuthController;
use Ondine\Request;

$auth = new AuthController();

// Simular request para login
$req = new Request();
$req->parsedBody = ['username' => 'sysadmin', 'password' => 'SysAdmin8590'];
$res = $auth->login($req, []);

echo "Login response:\n";
var_export($res);
echo "\n\n";

if (isset($res['token'])) {
    $token = $res['token'];
    // Simular request para me
    $req2 = new Request();
    $req2->headers['Authorization'] = 'Bearer ' . $token;
    $res2 = $auth->me($req2, []);
    echo "Me response:\n";
    var_export($res2);
    echo "\n";
} else {
    echo "No token returned from login.\n";
}
