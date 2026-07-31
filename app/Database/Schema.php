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

        $sql = SqlGenerator::createTable(
            $table,
            $blueprint
        );

        Connection::getInstance()->exec($sql);
    }

    public static function drop(string $table): void
    {
        $table = self::validateIdentifier($table);

        Connection::getInstance()->exec(
            "DROP TABLE `{$table}`"
        );
    }

    public static function dropIfExists(string $table): void
    {
        $table = self::validateIdentifier($table);

        Connection::getInstance()->exec(
            "DROP TABLE IF EXISTS `{$table}`"
        );
    }

    public static function hasTable(string $table): bool
    {
        $table = self::validateIdentifier($table);

        $stmt = Connection::getInstance()->prepare("
            SELECT COUNT(*)
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = :table
        ");

        $stmt->execute([
            'table' => $table,
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private static function validateIdentifier(
        string $identifier
    ): string {
        if (!preg_match(
            '/^[A-Za-z_][A-Za-z0-9_]*$/',
            $identifier
        )) {
            throw new \InvalidArgumentException(
                'Identificador de banco de dados inválido.'
            );
        }

        return $identifier;
    }
}