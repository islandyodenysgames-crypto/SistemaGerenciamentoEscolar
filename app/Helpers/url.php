<?php

declare(strict_types=1);

if (!function_exists('base_url')) {

    function base_url(string $path = ''): string
    {
        $base = rtrim((string) \App\Core\Config::get('app.url', '/'), '/');
        if ($base === '') {
            $base = '/';
        }

        if ($path === '') {
            return $base;
        }

        $normalizedPath = str_replace('\\', '/', ltrim($path, '/'));
        if (is_private_upload_path($normalizedPath)) {
            $path = 'arquivos/visualizar?path=' . rawurlencode($normalizedPath);
        }

        return ($base === '/' ? '' : rtrim($base, '/')) . '/' . ltrim($path, '/');
    }

}

if (!function_exists('is_private_upload_path')) {
    function is_private_upload_path(string $path): bool
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if (preg_match('#^uploads/(students|users|occurrences|occurrence-actions|monitoring-actions)/#', $path)) return true;
        return (bool) preg_match('#^uploads/notices/[0-9]{4}/[0-9]{2}/[0-9]+/attachments/#', $path);
    }
}

if (!function_exists('redirect')) {

    function redirect(string $path): never
    {
        header('Location: ' . base_url($path));
        exit;
    }

}
