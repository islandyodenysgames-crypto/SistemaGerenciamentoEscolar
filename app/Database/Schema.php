<?php

declare(strict_types=1);

namespace App\Database;

class Schema
{
    public static function create(
        string $table,
        callable $callback
    ): Table {

        $blueprint = new Table();

        $callback($blueprint);

        return $blueprint;
    }
}