#!/usr/bin/env php
<?php
require __DIR__ . '/../../vendor/autoload.php';

// Simple wrapper to call the library migrator
if (!class_exists('\Ondine\\Database\\Migrator')) {
    echo "Migrator not available. Run composer install.\n";
    exit(1);
}

\Ondine\Database\Migrator::migrate();

echo "Migrations executed.\n";
