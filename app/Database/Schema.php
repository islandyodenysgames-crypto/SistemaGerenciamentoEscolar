<?php

declare(strict_types=1);

namespace App\Database;

class Schema
{
    public static function create(
        string $table,
        callable $callback
    ): void {

        $blueprint = new Table();

        $callback($blueprint);

        $sql = SqlGenerator::createTable($table, $blueprint);

        Connection::getInstance()->exec($sql);
    }
}