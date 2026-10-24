<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\Request;
use App\Core\Session;

final class TvPanelAccessService
{
    public function canView(): bool
    {
        if (Session::has('user')) return true;
        $token = (string) Config::get('tv.access_token', '');
        if (strlen($token) < 32) return Config::get('app.environment', 'local') !== 'production';
        $cookie = (string) ($_COOKIE[(string) Config::get('tv.cookie_name', 'sge_tv_device')] ?? '');
        return $cookie !== '' && hash_equals(hash('sha256', $token), $cookie);
    }

    public function pair(string $providedToken): bool
    {
        $token = (string) Config::get('tv.access_token', '');
        if (strlen($token) < 32 || !hash_equals($token, $providedToken)) return false;
        $basePath = rtrim((string) (parse_url((string) Config::get('app.url', '/'), PHP_URL_PATH) ?: '/'), '/');
        setcookie((string) Config::get('tv.cookie_name', 'sge_tv_device'), hash('sha256', $token), [
            'expires' => time() + 180 * 86400,
            'path' => ($basePath === '' ? '' : $basePath) . '/painel-tv',
            'secure' => Request::isSecure(),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        return true;
    }
}
