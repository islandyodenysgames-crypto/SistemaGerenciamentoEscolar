<?php

declare(strict_types=1);

namespace App\Database;

class SqlGenerator
{
    public static function createTable(
        string $table,
        Table $blueprint
    ): string {

        $columns = [];

        foreach ($blueprint->getColumns() as $column) {

            $sql = "{$column->name} {$column->type}";

            if ($column->length !== null) {

                $sql .= "({$column->length})";

            }

            if (!$column->nullable) {

                $sql .= " NOT NULL";

            }

            if ($column->autoIncrement) {

                $sql .= " AUTO_INCREMENT";

            }

            if ($column->unique) {

                $sql .= " UNIQUE";

            }

            if ($column->primary) {

                $sql .= " PRIMARY KEY";

            }

            if ($column->default !== null) {

                $sql .= " DEFAULT '{$column->default}'";

            }

            $columns[] = $sql;

        }

        return sprintf(

            "CREATE TABLE %s (\n\n%s\n\n);",

            $table,

            implode(",\n\n", $columns)

        );
    }
}