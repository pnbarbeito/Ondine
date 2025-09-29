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

    /**
     * Allow dependency injection for easier testing. If dependencies are not
     * provided, the constructor falls back to the previous behavior (get
     * connection from Database::getConnection()).
     *
     * @param \PDO|null $pdo
     * @param \Ondine\Auth\SessionRepository|null $sessionRepo
     * @param \Ondine\Auth\Auth|null $auth
     */
    public function __construct($pdo = null, $sessionRepo = null, $auth = null)
    {
        if ($pdo === null) {
            $pdo = \Ondine\Database\Database::getConnection();
        }

        if ($auth !== null) {
            $this->auth = $auth;
        } else {
            $repo = new UserRepository($pdo);
            $secret = \Env::get('JWT_SECRET', 'changeme');
            $this->auth = new Auth($repo, $secret);
        }

        if ($sessionRepo !== null) {
            $this->sessionRepo = $sessionRepo;
        } else {
            $this->sessionRepo = new SessionRepository($pdo);
        }
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
            return new Response(400, ['error' => true, 'fields' => $errors]);
        }
        $body = \Ondine\Validation::sanitize($body, $rules);
        $user = $body['username'];
        $pass = $body['password'];

        // if user exists but blocked, return 403
        if ($this->auth->isUserBlocked($user)) {
            return new Response(403, ['error' => true, 'message' => 'user blocked']);
        }

        $token = $this->auth->login($user, $pass);
        if (!$token) {
            return new Response(401, ['error' => true, 'message' => 'invalid credentials']);
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
            return new Response(400, ['error' => true, 'message' => 'missing refresh_token']);
        }

        $session = $this->sessionRepo->findByToken($rt);
        if (!$session || $session['revoked']) {
            return new Response(401, ['error' => true, 'message' => 'invalid refresh_token']);
        }

        if (strtotime($session['expires_at']) < time()) {
            return new Response(401, ['error' => true, 'message' => 'refresh_token expired']);
        }

        $userId = $session['user_id'] ?? null;
        $user = $this->auth->getRepo()->findWithProfile($userId);
        if (!$user) {
            return new Response(401, ['error' => true, 'message' => 'invalid session user']);
        }

        // rotate refresh token for this session to mitigate replay attacks
        $newRefresh = bin2hex(random_bytes(32));
        // $session contains 'id'
        $sid = isset($session['id']) ? (int)$session['id'] : null;
        if ($sid) {
            $this->sessionRepo->rotate($sid, $newRefresh);
        }

        $newToken = \Ondine\Auth\Jwt::encode(['sub' => $userId], \Env::get('JWT_SECRET', 'changeme'));
        return ['token' => $newToken, 'refresh_token' => $newRefresh];
    }

    public function logout($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $rt = $body['refresh_token'] ?? null;
        if (!$rt) {
            return new Response(400, ['error' => true, 'message' => 'missing refresh_token']);
        }

        $this->sessionRepo->revoke($rt);
        return new Response(200, ['ok' => true]);
    }

    public function me($request, $params)
    {
        $hdr = $request->headers['Authorization'] ?? ($request->headers['authorization'] ?? null);
        if (!$hdr) {
            return new Response(401, ['error' => true, 'message' => 'missing token']);
        }

        if (stripos($hdr, 'Bearer ') === 0) {
            $token = trim(substr($hdr, 7));
        } else {
            $token = trim($hdr);
        }

        $payload = $this->auth->verifyToken($token);
        if (!$payload) {
            return new Response(401, ['error' => true, 'message' => 'invalid token']);
        }

    // load user with profile
    // use getter to access protected repo property
        $repo = $this->auth->getRepo();
        $user = $repo->findWithProfile($payload['sub']);
        return ['user' => $user, 'token_payload' => $payload];
    }
}
