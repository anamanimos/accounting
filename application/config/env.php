<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Env {
    private static $env_file = '.env';
    private static $env_data = [];

    public static function load() {
        $env_path = FCPATH . self::$env_file;

        if (file_exists($env_path)) {
            $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) continue; // Skip comments

                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    // Remove quotes if present
                    if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                        $value = $matches[2];
                    }

                    self::$env_data[$key] = $value;

                    // Set environment variable
                    if (!getenv($key)) {
                        putenv("$key=$value");
                    }
                }
            }
        }
    }

    public static function get($key, $default = null) {
        return self::$env_data[$key] ?? getenv($key) ?: $default;
    }

    public static function set($key, $value) {
        self::$env_data[$key] = $value;
        putenv("$key=$value");
    }
}

// Load environment variables
Env::load();
