<?php
require_once __DIR__ . '/../src/autoload.php';
if (file_exists(__DIR__ . '/../config/.env')) { require_once __DIR__ . '/../src/Env.php'; \Env::load(__DIR__ . '/../config/.env'); }

use Ondine\Controllers\AuthController;
use Ondine\Middleware\AuthMiddleware;
use Ondine\Request;
use Ondine\Controllers\UsersController;

$authCtrl = new AuthController();
$reqLogin = new Request();
$reqLogin->parsedBody = ['username' => 'sysadmin', 'password' => 'SysAdmin8590'];
$loginRes = $authCtrl->login($reqLogin, []);
if (!isset($loginRes['token'])) { echo "Login failed\n"; exit(1); }
$token = $loginRes['token'];

$req = new Request();
$req->headers['Authorization'] = 'Bearer ' . $token;
$req->method = 'GET';
$req->path = '/users';

$mw = new AuthMiddleware(['except' => ['/api/login','/api/me']]);
// Will exit and send response on failure
$mw->handle($req);

$uc = new UsersController();
$res = $uc->index($req, []);
var_export($res);
echo "\n";
