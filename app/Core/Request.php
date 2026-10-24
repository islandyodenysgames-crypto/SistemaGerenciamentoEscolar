<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public static function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public static function isSecure(): bool
    {
        if (strtolower((string) ($_SERVER['HTTPS'] ?? '')) === 'on' || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443) {
            return true;
        }
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $trusted = Config::get('security.trusted_proxies', []);
        return in_array($remote, is_array($trusted) ? $trusted : [], true)
            && strtolower(trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''))[0])) === 'https';
    }

    public static function clientIp(): string
    {
        $remote = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $trusted = Config::get('security.trusted_proxies', []);
        if (in_array($remote, is_array($trusted) ? $trusted : [], true)) {
            $forwarded = trim(explode(',', (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? ''))[0]);
            if (filter_var($forwarded, FILTER_VALIDATE_IP)) {
                return $forwarded;
            }
        }
        return filter_var($remote, FILTER_VALIDATE_IP) ? $remote : 'unknown';
    }

    public static function expectsJson(): bool
    {
        return str_contains(strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json')
            || strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
    }
}
