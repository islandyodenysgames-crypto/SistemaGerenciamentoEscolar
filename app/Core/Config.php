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

                'name' => Env::get('APP_NAME'),

                'url' => Env::get('APP_URL'),

                'timezone' => Env::get('APP_TIMEZONE'),

                'debug' => Env::get('APP_DEBUG'),

            ],

            'database' => [

                'connection' => Env::get('DB_CONNECTION'),

                'host' => Env::get('DB_HOST'),

                'port' => Env::get('DB_PORT'),

                'database' => Env::get('DB_DATABASE'),

                'username' => Env::get('DB_USERNAME'),

                'password' => Env::get('DB_PASSWORD'),

            ],

        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $keys = explode('.', $key);

        $value = self::$config;

        foreach ($keys as $segment) {

            if (!isset($value[$segment])) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}