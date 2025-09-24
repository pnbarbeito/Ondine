<?php

// Backwards-compatible global aliases for legacy code that references \Env directly
if (!class_exists('Env') && class_exists(\Ondine\Env::class)) {
    \class_alias(\Ondine\Env::class, 'Env');
}
