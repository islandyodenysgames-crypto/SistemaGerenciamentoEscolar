<?php

declare(strict_types=1);

if (!function_exists('flash')) {

    function flash(string $key): mixed
    {
        if (!isset($_SESSION[$key])) {
            return null;
        }

        $value = $_SESSION[$key];

        unset($_SESSION[$key]);

        return $value;
    }

}