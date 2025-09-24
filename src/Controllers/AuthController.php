<?php

namespace Ondine\Controllers;

use Ondine\Response;
use Ondine\Database\Repository\UserRepository;
use Ondine\Auth\Auth;
use Ondine\Auth\SessionRepository;

class AuthController
{
    protected $auth;
    protected $sessionRepo;

    public function __construct()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $repo = new UserRepository($pdo);
        $secret = \Env::get('JWT_SECRET', 'changeme');
        $this->auth = new Auth($repo, $secret);
        $this->sessionRepo = new SessionRepository($pdo);
    }

    public function login($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $rules = [
            'username' => ['required','min:3','max:64'],
            'password' => ['required','min:6','max:128'],
        ];
        $errors = \Ondine\Validation::validate($body, $rules);
        if (!empty($errors)) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'fields' => $errors];
        }
        $body = \Ondine\Validation::sanitize($body, $rules);
        $user = $body['username'];
        $pass = $body['password'];

        // if user exists but blocked, return 403
        if ($this->auth->isUserBlocked($user)) {
            \Ondine\Response::setStatusCode(403);
            return ['error' => true, 'message' => 'user blocked'];
        }

        $token = $this->auth->login($user, $pass);
        if (!$token) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'invalid credentials'];
        }

        // create refresh token and persist
        $refresh = bin2hex(random_bytes(32));
        $this->sessionRepo->create($this->auth->getRepo()->findByUsername($user)['id'], $refresh);

        return ['token' => $token, 'refresh_token' => $refresh];
    }

    public function refresh($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $rt = $body['refresh_token'] ?? null;
        if (!$rt) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'missing refresh_token'];
        }

        $session = $this->sessionRepo->findByToken($rt);
        if (!$session || $session['revoked']) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'invalid refresh_token'];
        }

        if (strtotime($session['expires_at']) < time()) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'refresh_token expired'];
        }

        $userId = $session['user_id'] ?? null;
        $user = $this->auth->getRepo()->findWithProfile($userId);
        if (!$user) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'invalid session user'];
        }

        $newToken = \Ondine\Auth\Jwt::encode(['sub' => $userId], \Env::get('JWT_SECRET', 'changeme'));
        return ['token' => $newToken];
    }

    public function logout($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $rt = $body['refresh_token'] ?? null;
        if (!$rt) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'missing refresh_token'];
        }

        $this->sessionRepo->revoke($rt);
        return ['ok' => true];
    }

    public function me($request, $params)
    {
        $hdr = $request->headers['Authorization'] ?? ($request->headers['authorization'] ?? null);
        if (!$hdr) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'missing token'];
        }

        if (stripos($hdr, 'Bearer ') === 0) {
            $token = trim(substr($hdr, 7));
        } else {
            $token = trim($hdr);
        }

        $payload = $this->auth->verifyToken($token);
        if (!$payload) {
            \Ondine\Response::setStatusCode(401);
            return ['error' => true, 'message' => 'invalid token'];
        }

    // load user with profile
    // use getter to access protected repo property
        $repo = $this->auth->getRepo();
        $user = $repo->findWithProfile($payload['sub']);
        return ['user' => $user, 'token_payload' => $payload];
    }
}
