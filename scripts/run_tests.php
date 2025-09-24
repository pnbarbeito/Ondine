$<?php
// Simple test runner fallback: runs basic test files that return 0 on success
require_once __DIR__ . '/../src/autoload.php';
echo "Running simple tests...\n";
$testsDir = __DIR__ . '/../tests';
$files = glob($testsDir . '/*.php');
$fail = 0;
foreach ($files as $f) {
    echo "- $f... ";
    $out = null;
    $code = null;
    passthru("php " . escapeshellarg($f), $code);
    if ($code !== 0) { echo "FAILED\n"; $fail++; } else { echo "OK\n"; }
}
if ($fail) { echo "Some tests failed: $fail\n"; exit(1);} echo "All tests passed\n"; exit(0);
