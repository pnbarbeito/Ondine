<?php

namespace Ondine\Controllers;

use Ondine\Database\Database;
use Ondine\Auth\Auth;
use Ondine\Database\Repository\UserRepository;
use Ondine\Response;

class UserController
{
  protected $pdo;
  protected $auth;

  public function __construct()
  {
    $this->pdo = Database::getConnection();
    $repo = new UserRepository($this->pdo);
    $secret = \Env::get('JWT_SECRET', 'changeme');
    $this->auth = new Auth($repo, $secret);
  }


  public function SetTheme($request, $params)
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
    $userId = $payload['sub'] ?? null;

    $body = $request->parsedBody ?: [];
    $allowed = ['theme'];
    $data = [];
    foreach ($allowed as $f) {
      if (isset($body[$f])) {
        $data[$f] = $body[$f];
      }
    }
    if (empty($data)) {
      return ['updated' => 0];
    }

    $rules = [];
    foreach ($data as $k => $v) {
      $rules[$k] = ['max:32'];
    }
    $errors = \Ondine\Validation::validate($data, $rules);
    
    if (!empty($errors)) {
      return new Response(400, ['error' => true, 'fields' => $errors]);
    }
    $data = \Ondine\Validation::sanitize($data, $rules);

    $sql = 'UPDATE users SET theme = :theme WHERE id = :id';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['theme' => $data['theme'], 'id' => $userId]);

    return ['updated' => (int)$stmt->rowCount()];
  }

  public function updateProfile($request, $params)
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
    $userId = $payload['sub'] ?? null;

    $body = $request->parsedBody ?: [];
    $allowed = ['first_name', 'last_name'];
    $data = [];
    foreach ($allowed as $f) {
      if (isset($body[$f])) {
        $data[$f] = $body[$f];
      }
    }
    if (empty($data)) {
      return ['updated' => 0];
    }

    $rules = [];
    foreach ($data as $k => $v) {
      $rules[$k] = ['required', 'min:2', 'max:64'];
    }
    $errors = \Ondine\Validation::validate($data, $rules);
    
    if (!empty($errors)) {
      return new Response(400, ['error' => true, 'fields' => $errors]);
    }
    $data = \Ondine\Validation::sanitize($data, $rules);

    $fields = [];
    $paramsSql = [':id' => $userId];
    foreach ($data as $k => $v) {
      $fields[] = "$k = :$k";
      $paramsSql[":$k"] = $v;
    }
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($paramsSql);

    return ['updated' => (int)$stmt->rowCount()];
  }

  public function changePassword($request, $params)
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
    $userId = $payload['sub'] ?? null;

    $body = $request->parsedBody ?: [];
    $currentPassword = $body['current_password'] ?? '';
    $newPassword = $body['new_password'] ?? '';

    if ($currentPassword === '' || $newPassword === '') {
      return new Response(400, ['error' => true, 'message' => 'current_password and new_password required']);
    }

    // Get current user password
    $userPassword = $this->auth->getRepo()->findPasswordById($userId);
    if (!$userPassword || !password_verify($currentPassword, $userPassword)) {
      return new Response(401, ['error' => true, 'message' => 'invalid current password']);
    }

    $rules = [
      'new_password' => ['required', 'min:6', 'max:128'],
    ];
    $data = ['new_password' => $newPassword];
    $errors = \Ondine\Validation::validate($data, $rules);
    
    if (!empty($errors)) {
      return new Response(400, ['error' => true, 'fields' => $errors]);
    }
    $data = \Ondine\Validation::sanitize($data, $rules);

    $pw_algo = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
    $hashed = password_hash($data['new_password'], $pw_algo);

    $sql = 'UPDATE users SET password = :password WHERE id = :id';
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['password' => $hashed, 'id' => $userId]);

    return ['updated' => (int)$stmt->rowCount()];
  }


}
