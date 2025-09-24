<?php
// legacy run tests helper
exec('vendor/bin/phpunit --colors=always', $out, $rc);
echo implode("\n", $out) . "\n";
exit($rc);
