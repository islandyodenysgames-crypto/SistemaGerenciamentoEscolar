<?php

declare(strict_types=1);

if (!function_exists('public_path')) {

    function public_path(string $path = ''): string
    {
        $public = dirname(__DIR__, 2)
            . DIRECTORY_SEPARATOR
            . 'public';

        return $path !== ''
            ? $public
                . DIRECTORY_SEPARATOR
                . ltrim(
                    str_replace(
                        ['/', '\\'],
                        DIRECTORY_SEPARATOR,
                        $path
                    ),
                    DIRECTORY_SEPARATOR
                )
            : $public;
    }

}

if (!function_exists('uploads_path')) {

    function uploads_path(string $path = ''): string
    {
        return public_path(
            'uploads'
            . ($path !== '' ? '/' . ltrim($path, '/') : '')
        );
    }

}

if (!function_exists('temp_path')) {

    function temp_path(string $path = ''): string
    {
        return public_path(
            'temp'
            . ($path !== '' ? '/' . ltrim($path, '/') : '')
        );
    }

}

if (!function_exists('cache_path')) {

    function cache_path(string $path = ''): string
    {
        return public_path(
            'cache'
            . ($path !== '' ? '/' . ltrim($path, '/') : '')
        );
    }

}