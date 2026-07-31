<?php

declare(strict_types=1);

namespace App\Database;

class SqlGenerator
{
    public static function createTable(
        string $table,
        Table $blueprint
    ): string {
        $table = self::quoteIdentifier($table);

        $columns = [];

        foreach ($blueprint->getColumns() as $column) {
            $columns[] = self::generateColumn($column);
        }

        if (empty($columns)) {
            throw new \RuntimeException(
                'A tabela precisa possuir pelo menos uma coluna.'
            );
        }

        return sprintf(
            "CREATE TABLE %s (\n    %s\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            $table,
            implode(",\n    ", $columns)
        );
    }

    private static function generateColumn(
        Column $column
    ): string {
        $name = self::quoteIdentifier($column->name);
        $type = strtoupper(trim($column->type));

        $sql = "{$name} {$type}";

        if (
            $column->precision !== null
            && $column->scale !== null
        ) {
            $sql .= sprintf(
                '(%d,%d)',
                $column->precision,
                $column->scale
            );
        } elseif ($column->length !== null) {
            $sql .= sprintf(
                '(%d)',
                $column->length
            );
        }

        if ($column->unsigned) {
            $sql .= ' UNSIGNED';
        }

        $sql .= $column->nullable
            ? ' NULL'
            : ' NOT NULL';

        if ($column->autoIncrement) {
            $sql .= ' AUTO_INCREMENT';
        }

        if ($column->unique) {
            $sql .= ' UNIQUE';
        }

        if ($column->primary) {
            $sql .= ' PRIMARY KEY';
        }

        if ($column->hasDefault) {
            $sql .= ' DEFAULT '
                . self::formatDefaultValue($column);
        }

        if ($column->useCurrentOnUpdate) {
            $sql .= ' ON UPDATE CURRENT_TIMESTAMP';
        }

        return $sql;
    }

    private static function formatDefaultValue(
        Column $column
    ): string {
        if ($column->defaultIsExpression) {
            return (string) $column->default;
        }

        if ($column->default === null) {
            return 'NULL';
        }

        if (is_bool($column->default)) {
            return $column->default ? '1' : '0';
        }

        if (
            is_int($column->default)
            || is_float($column->default)
        ) {
            return (string) $column->default;
        }

        $value = str_replace(
            "'",
            "''",
            (string) $column->default
        );

        return "'{$value}'";
    }

    private static function quoteIdentifier(
        string $identifier
    ): string {
        if (!preg_match(
            '/^[A-Za-z_][A-Za-z0-9_]*$/',
            $identifier
        )) {
            throw new \InvalidArgumentException(
                'Identificador de banco de dados inválido: '
                . $identifier
            );
        }

        return "`{$identifier}`";
    }
}