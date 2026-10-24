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
                'environment' => Env::get('APP_ENV', 'local'),
            ],

            'session' => [
                'name' => Env::get('SESSION_NAME', 'sge_session'),
                'lifetime' => (int) Env::get('SESSION_LIFETIME', '1800'),
                'regenerate_interval' => (int) Env::get('SESSION_REGENERATE_INTERVAL', '900'),
                'revalidate_interval' => (int) Env::get('SESSION_REVALIDATE_INTERVAL', '300'),
                'same_site' => Env::get('SESSION_SAME_SITE', 'Lax'),
            ],

            'security' => [
                'trusted_proxies' => array_values(array_filter(array_map('trim', explode(',', (string) Env::get('TRUSTED_PROXIES', ''))))),
                'login_identity_limit' => (int) Env::get('LOGIN_IDENTITY_LIMIT', '5'),
                'login_origin_limit' => (int) Env::get('LOGIN_ORIGIN_LIMIT', '50'),
                'login_window' => (int) Env::get('LOGIN_WINDOW', '900'),
                'login_lockout' => (int) Env::get('LOGIN_LOCKOUT', '900'),
            ],

            'tv' => [
                'access_token' => Env::get('TV_PANEL_ACCESS_TOKEN', ''),
                'cookie_name' => Env::get('TV_PANEL_COOKIE_NAME', 'sge_tv_device'),
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
