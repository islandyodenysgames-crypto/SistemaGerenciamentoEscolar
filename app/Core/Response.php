<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public static function redirect(string $url): never
    {
        header("Location: {$url}");
        exit;
    }

    public static function json(array $data): never
    {
        header('Content-Type: application/json');

        echo json_encode($data);

        exit;
    }
}