<?php

namespace Ondine\Controllers;

use Ondine\Database\Database;

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
}
