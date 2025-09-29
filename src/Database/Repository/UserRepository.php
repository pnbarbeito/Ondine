<?php

namespace Ondine\Database\Repository;

class UserRepository
{
    protected $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all()
    {
        $stmt = $this->pdo->query('SELECT id, first_name, last_name, profile_id, theme, username, state FROM users');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare('SELECT id, first_name, last_name, profile_id, theme, username, state FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $sql = 'INSERT INTO users (first_name, last_name, profile_id, theme, username, password, state) VALUES (:first_name, :last_name, :profile_id, :theme, :username, :password, :state)';
        $stmt = $this->pdo->prepare($sql);
        try {
            $stmt->execute([
                ':first_name' => $data['first_name'] ?? '',
                ':last_name' => $data['last_name'] ?? '',
                ':profile_id' => $data['profile_id'] ?? 1,
                ':theme' => $data['theme'] ?? 'dark',
                ':username' => $data['username'] ?? '',
                ':password' => $data['password'],
                ':state' => $data['state'] ?? 1,
            ]);
        } catch (\PDOException $ex) {
            // SQLSTATE 23000: integrity constraint violation
            $sqlstate = $ex->getCode();
            $msg = $ex->getMessage();
            if ($sqlstate === '23000' || stripos($msg, 'unique') !== false || stripos($msg, 'duplicate') !== false) {
                throw new DuplicateUsernameException('duplicate_username');
            }
            throw $ex;
        }
        return (int)$this->pdo->lastInsertId();
    }

    public function update($id, $data)
    {
        // Build dynamic update
        $fields = [];
        $params = [':id' => $id];
        $allowed = ['first_name', 'last_name', 'profile_id', 'theme', 'username', 'password', 'state'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }
        if (empty($fields)) {
            return 0;
        }
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount();
    }

    public function findByUsername($username)
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findPasswordById($id)
    {
        $stmt = $this->pdo->prepare('SELECT password FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? $row['password'] : null;
    }

    public function findWithProfile($id)
    {
        $stmt = $this->pdo->prepare('SELECT u.*, p.name AS profile_name, p.permissions AS profile_permissions FROM users u LEFT JOIN profiles p ON u.profile_id = p.id WHERE u.id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }
        if (!empty($row['profile_permissions'])) {
            $decoded = json_decode($row['profile_permissions'], true);
            if ($decoded === null) {
                // try to unescape (handle migrations that inserted escaped JSON)
                $clean = stripslashes($row['profile_permissions']);
                $decoded = json_decode($clean, true);
            }
            $row['profile_permissions'] = $decoded !== null ? $decoded : null;
        } else {
            $row['profile_permissions'] = null;
        }
        return $row;
    }
}
