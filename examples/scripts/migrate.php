<?php
// Prefer Composer autoload if available
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../src/autoload.php';
    require_once __DIR__ . '/../../src/Env.php';
    if (class_exists('\\Env', false) || class_exists('Env', false)) {
        \Env::load(__DIR__ . '/../../config/.env');
    }
}

use Ondine\Database\Migrator;

$cmd = $argv[1] ?? 'migrate';
try {
    if ($cmd === 'migrate') {
        Migrator::migrate();
        echo "Migrations ran successfully\n";
    } elseif ($cmd === 'rollback') {
        $steps = isset($argv[2]) ? (int)$argv[2] : 1;
        Migrator::rollback($steps);
        echo "Rollback completed (steps={$steps})\n";
    } else {
        echo "Unknown command: $cmd\n";
        exit(1);
    }
} catch (Throwable $e) {
    echo "Migration error: " . $e->getMessage() . "\n";
    exit(1);
}
