<?php

declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_name((string) Config::get('session.name', 'sge_session'));
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => self::cookiePath(),
                'secure' => Request::isSecure(),
                'httponly' => true,
                'samesite' => (string) Config::get('session.same_site', 'Lax'),
            ]);
            session_start();

            $now = time();
            $last = (int) ($_SESSION['_last_activity'] ?? $now);
            $lifetime = max(60, (int) Config::get('session.lifetime', 1800));
            if (isset($_SESSION['user']) && self::isExpired($last, $now, $lifetime)) {
                $_SESSION = ['login_error' => 'Sua sessão expirou por inatividade. Entre novamente.'];
                session_regenerate_id(true);
            }
            $regenerated = (int) ($_SESSION['_regenerated_at'] ?? 0);
            if ($regenerated === 0 || ($now - $regenerated) >= max(60, (int) Config::get('session.regenerate_interval', 900))) {
                session_regenerate_id(true);
                $_SESSION['_regenerated_at'] = $now;
            }
            $_SESSION['_last_activity'] = $now;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $parameters = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $parameters['path'],
                'domain' => $parameters['domain'],
                'secure' => $parameters['secure'],
                'httponly' => true,
                'samesite' => $parameters['samesite'] ?? 'Lax',
            ]);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public static function authenticate(array $user): void
    {
        session_regenerate_id(true);
        self::set('user', $user);
        self::set('_last_activity', time());
        self::set('_regenerated_at', time());
        self::set('_user_revalidated_at', time());
    }

    public static function isExpired(int $lastActivity, int $now, int $lifetime): bool
    {
        return $lastActivity > 0 && ($now - $lastActivity) > max(60, $lifetime);
    }

    private static function cookiePath(): string
    {
        $path = parse_url((string) Config::get('app.url', '/'), PHP_URL_PATH) ?: '/';
        return rtrim(str_replace('\\', '/', $path), '/') ?: '/';
    }
}
