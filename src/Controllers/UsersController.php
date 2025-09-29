<?php

namespace Ondine\Controllers;

use Ondine\Response;
use Ondine\Database\Repository\UserRepository;

class UsersController
{
    protected $repo;

    public function __construct()
    {
        $pdo = \Ondine\Database\Database::getConnection();
        $this->repo = new UserRepository($pdo);
    }

    public function index($request, $params)
    {
        $rows = $this->repo->all();
        return ['data' => $rows];
    }

    public function show($request, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            return new Response(400, ['error' => true, 'message' => 'id required']);
        }
        $row = $this->repo->find($id);
        if (!$row) {
            return new Response(404, ['error' => true, 'message' => 'not found']);
        }
        return ['data' => $row];
    }

    public function store($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $rules = [
            'first_name' => ['required','min:2','max:64'],
            'last_name' => ['required','min:2','max:64'],
            'username' => ['required','min:3','max:64'],
            'password' => ['required','min:6','max:128'],
            'profile_id' => ['int'],
            'theme' => ['max:32'],
            'state' => ['int'],
        ];
        $errors = \Ondine\Validation::validate($body, $rules);
        if (!empty($errors)) {
            return new Response(400, ['error' => true, 'fields' => $errors]);
        }
        $body = \Ondine\Validation::sanitize($body, $rules);
        $pw_algo = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
        $data = [
            'first_name' => $body['first_name'],
            'last_name' => $body['last_name'],
            'username' => $body['username'],
            'password' => password_hash($body['password'], $pw_algo),
            'profile_id' => isset($body['profile_id']) ? (int)$body['profile_id'] : 1,
            'theme' => $body['theme'] ?? 'dark',
            'state' => isset($body['state']) ? (int)$body['state'] : 1,
        ];
        try {
            $id = $this->repo->create($data);
            return new Response(201, ['id' => $id]);
        } catch (\Ondine\Database\Repository\DuplicateUsernameException $e) {
            return new Response(409, ['error' => true, 'message' => 'username already exists', 'field' => 'username']);
        }
    }

    public function update($request, $params)
    {
        $id = $params['id'] ?? null;
        $body = $request->parsedBody ?: [];
        if (!$id) {
            return new Response(400, ['error' => true, 'message' => 'id required']);
        }
        $allowed = ['first_name','last_name','profile_id','theme','username','password','state'];
        $data = [];
        foreach ($allowed as $f) {
            if (isset($body[$f])) {
                $data[$f] = $body[$f];
            }
        }
        if (empty($data)) {
            return ['updated' => 0];
        }

        // validation rules for fields being updated
        $rules = [];
        foreach ($data as $k => $v) {
            if (in_array($k, ['first_name','last_name'])) {
                $rules[$k] = ['min:2','max:64'];
            }
            if ($k === 'username') {
                $rules[$k] = ['min:3','max:64'];
            }
            if ($k === 'password') {
                $rules[$k] = ['min:6','max:128'];
            }
            if ($k === 'profile_id' || $k === 'state') {
                $rules[$k] = ['int'];
            }
            if ($k === 'theme') {
                $rules[$k] = ['max:32'];
            }
        }
            $errors = \Ondine\Validation::validate($data, $rules);
        if (!empty($errors)) {
            return new Response(400, ['error' => true, 'fields' => $errors]);
        }
            $data = \Ondine\Validation::sanitize($data, $rules);
        if (isset($data['password'])) {
            $pw_algo_upd = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
            $data['password'] = password_hash($data['password'], $pw_algo_upd);
        }

        $old = $this->repo->find($id);
        $count = $this->repo->update($id, $data);

        if ($count && isset($data['state'])) {
            $oldState = isset($old['state']) ? (int)$old['state'] : 1;
            $newState = (int)$data['state'];
            if ($oldState === 1 && $newState === 0) {
                // revoke all sessions for this user
                    $pdo = \Ondine\Database\Database::getConnection();
                    $sessionRepo = new \Ondine\Auth\SessionRepository($pdo);
                $sessionRepo->revokeAllForUser((int)$id);
            }
        }

        return ['updated' => (int)$count];
    }

    public function delete($request, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            return new Response(400, ['error' => true, 'message' => 'id required']);
        }
        $count = $this->repo->delete($id);
        return ['deleted' => (int)$count];
    }

    public function changePassword($request, $params)
    {
        $id = $params['id'] ?? null;
        $body = $request->parsedBody ?: [];
        if (!$id) {
            return new Response(400, ['error' => true, 'message' => 'id required']);
        }
        $newPassword = $body['new_password'] ?? '';
        if ($newPassword === '') {
            return new Response(400, ['error' => true, 'message' => 'new_password required']);
        }

        $rules = ['new_password' => ['required', 'min:6', 'max:128']];
        $data = ['new_password' => $newPassword];
        $errors = \Ondine\Validation::validate($data, $rules);
        if (!empty($errors)) {
            return new Response(400, ['error' => true, 'fields' => $errors]);
        }
        $data = \Ondine\Validation::sanitize($data, $rules);

        $pw_algo = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
        $hashed = password_hash($data['new_password'], $pw_algo);

        $count = $this->repo->update($id, ['password' => $hashed]);

        // revoke all sessions for this user to force re-login
        if ($count) {
            $pdo = \Ondine\Database\Database::getConnection();
            $sessionRepo = new \Ondine\Auth\SessionRepository($pdo);
            $sessionRepo->revokeAllForUser((int)$id);
        }

        return ['updated' => (int)$count];
    }
}
