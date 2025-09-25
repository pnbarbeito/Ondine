<?php

namespace App\Controllers;

use Ondine\Database\Database;

class ExampleController
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
      return ['error' => true, 'message' => 'missing id'];
    }
    return ['item' => ['id' => $id, 'name' => 'Item ' . $id]];
  }
}
