<?php

declare(strict_types=1);

if (!function_exists('asset')) {

    function asset(string $path): string
    {
        $url = base_url($path);
        $cleanPath = ltrim((string) parse_url($path, PHP_URL_PATH), '/');
        $publicFile = dirname(__DIR__, 2) . '/public/' . $cleanPath;

        if (is_file($publicFile)) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . 'v=' . (string) filemtime($publicFile);
        }

        return $url;
    }

}

if (!function_exists('page_script')) {

    function page_script(): ?string
    {
        $uri = parse_url(
            $_SERVER['REQUEST_URI'],
            PHP_URL_PATH
        );

        $scripts = [

            '/dashboard'      => 'pages/dashboard.js',

            '/frequencia'     => 'pages/attendance.js',

            '/alunos'         => 'pages/students.js',

            '/turmas'         => 'pages/classes.js',

            '/relatorios'     => 'pages/reports.js',

            '/busca'          => 'pages/search.js',

            '/usuarios'       => 'pages/users.js',

            '/matriculas'     => 'pages/enrollments.js',

            '/configuracoes'  => 'pages/settings.js',

        ];

        foreach ($scripts as $route => $script) {

            if (str_contains($uri, $route)) {
                return $script;
            }

        }

        return null;
    }

}