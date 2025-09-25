<?php
// Migration: create profiles and users tables (compatible sqlite/mariadb)
return [
  'up' => function ($pdo) {
  $driver = \Env::get('DB_DRIVER', 'sqlite');
    if ($driver === 'sqlite') {
      $sql1 = <<<SQL
        CREATE TABLE IF NOT EXISTS profiles (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          name TEXT NOT NULL,
          permissions TEXT NOT NULL
        );
      SQL;
      $sql2 = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          first_name TEXT NOT NULL,
          last_name TEXT NOT NULL,
          profile_id INTEGER NOT NULL,
          theme TEXT NOT NULL DEFAULT 'dark',
          username TEXT NOT NULL,
          password TEXT NOT NULL,
          state INTEGER NOT NULL DEFAULT 1
        );
      SQL;
      // seed values from environment with sensible defaults
      $env = function ($k, $d = null) {
        return \Env::get($k, $d);
      };
      $seedProfileName = $env('SEED_PROFILE_NAME', 'Administrator');
      $seedProfilePermissions = $env('SEED_PROFILE_PERMISSIONS', json_encode(['admin' => 1, 'profiles' => 1, 'users' => 1]));
      $seedAdminUsername = $env('SEED_ADMIN_USERNAME', 'sysadmin');
      $seedAdminPasswordPlain = $env('SEED_ADMIN_PASSWORD', 'SecureAdmin2025');
      $seedAdminFirst = $env('SEED_ADMIN_FIRSTNAME', 'Sys');
      $seedAdminLast = $env('SEED_ADMIN_LASTNAME', 'Admin');
      $seedAdminState = (int)$env('SEED_ADMIN_STATE', 1);

      $pdo->exec($sql1);
      $pdo->exec($sql2);

      // Insert profile seed
      $stmtP = $pdo->prepare('INSERT INTO profiles (id, name, permissions) VALUES (:id, :name, :permissions)');
      $stmtP->execute([':id' => 1, ':name' => $seedProfileName, ':permissions' => $seedProfilePermissions]);

      // Insert admin user seed (use runtime password_hash)
      $pw_algo = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
      $seedPassword = password_hash($seedAdminPasswordPlain, $pw_algo);
      $stmt = $pdo->prepare('INSERT INTO users (id, first_name, last_name, profile_id, theme, username, password, state) VALUES (:id, :first_name, :last_name, :profile_id, :theme, :username, :password, :state)');
      $stmt->execute([
        ':id' => 1,
        ':first_name' => $seedAdminFirst,
        ':last_name' => $seedAdminLast,
        ':profile_id' => 1,
        ':theme' => 'dark',
        ':username' => $seedAdminUsername,
        ':password' => $seedPassword,
        ':state' => $seedAdminState,
      ]);
      return;
    }

    if ($driver === 'mariadb') {
      //CREATE DATABASE IF NOT EXISTS database_name;USE database_name;
      //#MYSQL_DATABASE=newframe
      $sql1 = <<<SQL
        CREATE TABLE IF NOT EXISTS profiles (
          id INT UNSIGNED NOT NULL AUTO_INCREMENT,
          name VARCHAR(50) NOT NULL,
          permissions VARCHAR(255) NOT NULL,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL;
      $sql2 = <<<SQL
        CREATE TABLE IF NOT EXISTS users (
          id INT UNSIGNED NOT NULL AUTO_INCREMENT,
          first_name VARCHAR(50) NOT NULL,
          last_name VARCHAR(50) NOT NULL,
          profile_id SMALLINT NOT NULL,
          theme VARCHAR(10) NOT NULL DEFAULT 'dark',
          username VARCHAR(64) NOT NULL,
          password VARCHAR(255) NOT NULL,
          state TINYINT NOT NULL DEFAULT 1,
          PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        SQL;

      // env-driven seeds
      $env = function ($k, $d = null) {
        return \Env::get($k, $d);
      };
      $seedProfileName = $env('SEED_PROFILE_NAME', 'Administrator');
      $seedProfilePermissions = $env('SEED_PROFILE_PERMISSIONS', json_encode(['admin' => 1, 'profiles' => 1, 'users' => 1]));
      $seedAdminUsername = $env('SEED_ADMIN_USERNAME', 'sysadmin');
      $seedAdminPasswordPlain = $env('SEED_ADMIN_PASSWORD', 'SysAdmin8590');
      $seedAdminFirst = $env('SEED_ADMIN_FIRSTNAME', 'Sys');
      $seedAdminLast = $env('SEED_ADMIN_LASTNAME', 'Admin');
      $seedAdminState = (int)$env('SEED_ADMIN_STATE', 1);

      $pdo->exec($sql1);
      $pdo->exec($sql2);

      // Insert profile seed
      $stmtP = $pdo->prepare('INSERT INTO profiles (id, name, permissions) VALUES (:id, :name, :permissions)');
      $stmtP->execute([':id' => 1, ':name' => $seedProfileName, ':permissions' => $seedProfilePermissions]);

      // Insert admin user seed
      $pw_algo = defined('PASSWORD_ARGON2I') ? PASSWORD_ARGON2I : PASSWORD_DEFAULT;
      $seedPassword = password_hash($seedAdminPasswordPlain, $pw_algo);
      $stmt = $pdo->prepare('INSERT INTO users (id, first_name, last_name, profile_id, theme, username, password, state) VALUES (:id, :first_name, :last_name, :profile_id, :theme, :username, :password, :state)');
      $stmt->execute([
        ':id' => 1,
        ':first_name' => $seedAdminFirst,
        ':last_name' => $seedAdminLast,
        ':profile_id' => 1,
        ':theme' => 'dark',
        ':username' => $seedAdminUsername,
        ':password' => $seedPassword,
        ':state' => $seedAdminState,
      ]);
      return;
    }
  },
  'down' => function ($pdo) {
    $pdo->exec('DROP TABLE IF EXISTS users');
    $pdo->exec('DROP TABLE IF EXISTS profiles');
  }
];
