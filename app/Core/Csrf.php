<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    private const SESSION_KEY = '_csrf_token';

    public static function token(): string
    {
        $token = Session::get(self::SESSION_KEY);
        if (!is_string($token) || strlen($token) < 32) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    public static function validate(?string $token): bool
    {
        $stored = Session::get(self::SESSION_KEY);

        return is_string($stored)
            && is_string($token)
            && $token !== ''
            && hash_equals($stored, $token);
    }

    public static function requestToken(): ?string
    {
        $token = Request::post('_token');
        if (is_string($token) && $token !== '') {
            return $token;
        }
        foreach (['HTTP_X_CSRF_TOKEN', 'HTTP_X_XSRF_TOKEN'] as $header) {
            if (isset($_SERVER[$header]) && is_string($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }
        return null;
    }

    public static function rotate(): string
    {
        Session::remove(self::SESSION_KEY);
        return self::token();
    }

    public static function injectIntoHtml(string $html): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return (string) preg_replace_callback('/<form\b([^>]*)>(.*?)<\/form>/is', static function (array $match) use ($token): string {
            if (!preg_match('/\bmethod\s*=\s*(["\']?)post\1/i', $match[1]) || preg_match('/\bname\s*=\s*(["\'])_token\1/i', $match[2])) {
                return $match[0];
            }
            return '<form' . $match[1] . '><input type="hidden" name="_token" value="' . $token . '">' . $match[2] . '</form>';
        }, $html);
    }
}
