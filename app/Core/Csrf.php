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
        preg_match_all('/<form\b[^>]*>|<\/form\s*>|<input\b[^>]*>/is', $html, $matches, PREG_OFFSET_CAPTURE);
        $stack = [];
        $forms = [];
        foreach ($matches[0] as [$tag, $offset]) {
            if (preg_match('/^<form\b/i', $tag)) {
                $stack[] = [
                    'end' => $offset + strlen($tag),
                    'post' => (bool) preg_match('/\bmethod\s*=\s*(?:"post"|\'post\'|post(?=\s|\/?>|$))/i', $tag),
                    'protected' => false,
                ];
                continue;
            }
            if (preg_match('/^<input\b/i', $tag) && preg_match('/\bname\s*=\s*(?:"_token"|\'_token\'|_token(?=\s|\/?>|$))/i', $tag) && $stack !== []) {
                $stack[array_key_last($stack)]['protected'] = true;
                continue;
            }
            if (preg_match('/^<\/form/i', $tag) && $stack !== []) {
                $forms[] = array_pop($stack);
            }
        }
        $forms = array_merge($forms, $stack);
        usort($forms, static fn (array $a, array $b): int => $b['end'] <=> $a['end']);
        $field = '<input type="hidden" name="_token" value="' . $token . '">';
        foreach ($forms as $form) {
            if ($form['post'] && !$form['protected']) {
                $html = substr_replace($html, $field, $form['end'], 0);
            }
        }
        return $html;
    }
}
