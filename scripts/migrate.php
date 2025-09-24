<?php
require_once __DIR__ . '/../src/autoload.php';
require_once __DIR__ . '/../src/Env.php';
\Env::load(__DIR__ . '/../config/.env');

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
