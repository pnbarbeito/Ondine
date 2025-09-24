<?php

namespace Ondine;

class Env
{
    /**
     * Whether we've attempted to auto-load the .env file
     * @var bool
     */
    private static $loaded = false;

    /**
     * Path to the default env file to auto-load when needed
     * @var string|null
     */
    private static $envPath = null;

    /**
     * Configure a custom default .env path used by Env::get auto-load.
     * Call this early (e.g. in bootstrap) if your .env is in a non-standard place.
     *
     * @param string $path
     */
    public static function setDefaultEnvPath(string $path)
    {
        self::$envPath = $path;
    }

    public static function load($path)
    {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            if (!strpos($line, '=')) {
                continue;
            }
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (strlen($val) > 1 && ($val[0] === '"' || $val[0] === "'")) {
                $val = substr($val, 1, -1);
            }
            // If the environment variable is not set or is the empty string,
            // allow the .env file to provide the value. Docker can set
            // variables to an empty string when interpolation fails, so treat
            // empty as unset here.
            $current = getenv($key);
            if ($current === false || $current === '') {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }

    /**
     * Get an environment value giving priority to actual environment variables.
     * If the variable is not set or is empty, returns $default.
     * This centralizes env access so codebase can rely on the same semantics.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        // Check superglobals as fallback
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }
        if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
            return $_SERVER[$key];
        }

        // If we haven't yet auto-loaded a .env file, try to load the default
        // file (config/.env) if it exists. We only attempt this once to avoid
        // repeated filesystem checks.
        if (!self::$loaded) {
            self::$loaded = true;
            $path = self::$envPath ?? __DIR__ . '/../config/.env';
            if (file_exists($path)) {
                self::load($path);

                // After loading, try again to read from environment/superglobals
                $value = getenv($key);
                if ($value !== false && $value !== '') {
                    return $value;
                }
                if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
                    return $_ENV[$key];
                }
                if (array_key_exists($key, $_SERVER) && $_SERVER[$key] !== '') {
                    return $_SERVER[$key];
                }
            }
        }

        return $default;
    }
}

// No global alias here; compat file (`src/compat.php`) provides it and is loaded via composer `autoload.files`.
