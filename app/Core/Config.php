<?php

declare(strict_types=1);

namespace App\Core;

class Config
{
    private static array $config = [];

    public static function load(): void
    {
        self::$config = [
            'app' => [
                'name' => Env::get('APP_NAME', 'Sistema de Frequência Escolar'),
                'url' => Env::get('APP_URL', '/SistemaFrequenciaEscolar/public'),
                'timezone' => Env::get('APP_TIMEZONE', 'America/Fortaleza'),
                'debug' => Env::get('APP_DEBUG', 'true'),
            ],

            'database' => [
                'connection' => Env::get('DB_CONNECTION', 'mysql'),
                'host' => Env::get('DB_HOST', '127.0.0.1'),
                'port' => Env::get('DB_PORT', '3306'),
                'database' => Env::get('DB_DATABASE', 'frequencia_escolar'),
                'username' => Env::get('DB_USERNAME', 'root'),
                'password' => Env::get('DB_PASSWORD', ''),
            ],
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        $value = self::$config;

        foreach ($segments as $segment) {
            if (!array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}