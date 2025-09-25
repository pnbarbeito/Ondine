<?php

namespace Ondine\Controllers;

use Ondine\Database\Database;
use Ondine\Cache\ProfileCache;

class ProfilesController
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function index($request, $params)
    {
        $stmt = $this->pdo->query('SELECT id, name, permissions FROM profiles');
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            if (!empty($r['permissions'])) {
                $decoded = json_decode($r['permissions'], true);
                if ($decoded === null) {
                    $clean = stripslashes($r['permissions']);
                    $decoded = json_decode($clean, true);
                }
                $r['permissions'] = $decoded !== null ? $decoded : null;
            } else {
                $r['permissions'] = null;
            }
        }
        return ['data' => $rows];
    }

    public function show($request, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'id required'];
        }
        $stmt = $this->pdo->prepare('SELECT id, name, permissions FROM profiles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            \Ondine\Response::setStatusCode(404);
            return ['error' => true, 'message' => 'not found'];
        }
        if (!empty($row['permissions'])) {
            $decoded = json_decode($row['permissions'], true);
            if ($decoded === null) {
                $clean = stripslashes($row['permissions']);
                $decoded = json_decode($clean, true);
            }
            $row['permissions'] = $decoded !== null ? $decoded : null;
        } else {
            $row['permissions'] = null;
        }
        return ['data' => $row];
    }

    public function update($request, $params)
    {
        $id = $params['id'] ?? null;
        $body = $request->parsedBody ?: [];
        if (!$id) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'id required'];
        }

        $allowed = ['name','permissions'];
        $data = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $body)) {
                $data[$f] = $body[$f];
            }
        }
        if (empty($data)) {
            return ['updated' => 0];
        }

        // basic sanitize: ensure permissions is JSON if provided
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $data['permissions'] = json_encode($data['permissions']);
        }

        $fields = [];
        $paramsSql = [':id' => $id];
        foreach ($data as $k => $v) {
            $fields[] = "$k = :$k";
            $paramsSql[":$k"] = $v;
        }
        $sql = 'UPDATE profiles SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($paramsSql);
        $count = $stmt->rowCount();

        // invalidate profile cache so changes are immediate
        try {
            $cache = new ProfileCache();
            $cache->clearProfile($id);
        } catch (\Throwable $e) {
            // ignore cache failures
        }

        return ['updated' => (int)$count];
    }

    public function delete($request, $params)
    {
        $id = $params['id'] ?? null;
        if (!$id) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'id required'];
        }
        $stmt = $this->pdo->prepare('DELETE FROM profiles WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $count = $stmt->rowCount();

        // invalidate profile cache
        try {
            $cache = new ProfileCache();
            $cache->clearProfile($id);
        } catch (\Throwable $e) {
            // ignore cache failures
        }

        return ['deleted' => (int)$count];
    }

    public function store($request, $params)
    {
        $body = $request->parsedBody ?: [];
        $name = isset($body['name']) ? trim($body['name']) : '';
        $permissions = $body['permissions'] ?? null;
        if ($name === '') {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'name required'];
        }

        if ($permissions !== null && !is_array($permissions)) {
            \Ondine\Response::setStatusCode(400);
            return ['error' => true, 'message' => 'permissions must be an object/array'];
        }

        $permJson = $permissions !== null ? json_encode($permissions) : null;
        $stmt = $this->pdo->prepare('INSERT INTO profiles (name, permissions) VALUES (:name, :permissions)');
        $stmt->execute([':name' => $name, ':permissions' => $permJson]);
        $id = (int)$this->pdo->lastInsertId();

        // no need to invalidate cache here (no users yet), but keep for symmetry
        try {
            $cache = new ProfileCache();
            $cache->clearProfile($id);
        } catch (\Throwable $e) {
            // ignore
        }

        \Ondine\Response::setStatusCode(201);
        return ['id' => $id];
    }
}
