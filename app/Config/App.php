<?php

namespace App\Config;

class App
{
    private static ?array $env = null;

    public static function loadEnv(string $baseDir): void
    {
        if (self::$env !== null) {
            return;
        }

        self::$env = [];
        $envFile = $baseDir . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            $line = ltrim($line, "\xEF\xBB\xBF");
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($value !== '' && (
                ($value[0] === '"' && substr($value, -1) === '"') ||
                ($value[0] === "'" && substr($value, -1) === "'")
            )) {
                $value = substr($value, 1, -1);
            }

            $existing = $_ENV[$key] ?? getenv($key);
            if ($existing === false || $existing === null) {
                self::$env[$key] = $value;
                $_ENV[$key] = $value;
                putenv($key . '=' . $value);
            } else {
                self::$env[$key] = (string) $existing;
            }
        }
    }

    public static function env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? getenv($key);
        if ($value === false || $value === null || $value === '') {
            $value = self::$env[$key] ?? null;
        }
        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}
