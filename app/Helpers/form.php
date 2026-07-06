<?php

declare(strict_types=1);

if (!function_exists('old')) {

    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }

}

if (!function_exists('selected')) {

    function selected(mixed $value, mixed $current): string
    {
        return $value == $current ? 'selected' : '';
    }

}

if (!function_exists('checked')) {

    function checked(mixed $value, mixed $current): string
    {
        return $value == $current ? 'checked' : '';
    }

}