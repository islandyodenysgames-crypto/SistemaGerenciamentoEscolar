<?php

declare(strict_types=1);

if (!function_exists('base_url')) {

    function base_url(string $path = ''): string
    {
        $base = '/SistemaFrequenciaEscolar/public';

        return $base . (
            $path !== ''
                ? '/' . ltrim($path, '/')
                : ''
        );
    }

}

if (!function_exists('redirect')) {

    function redirect(string $path): never
    {
        header('Location: ' . base_url($path));
        exit;
    }

}