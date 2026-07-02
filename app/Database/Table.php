<?php

declare(strict_types=1);

namespace App\Database;

class Table
{
    private array $columns = [];

    public function id(): Column
    {
        $column = new Column('BIGINT', 'id');

        $column
            ->primary()
            ->autoIncrement();

        $this->columns[] = $column;

        return $column;
    }

    public function string(
        string $name,
        int $length = 255
    ): Column {

        $column = new Column(
            'VARCHAR',
            $name,
            $length
        );

        $this->columns[] = $column;

        return $column;
    }

    public function integer(string $name): Column
    {
        $column = new Column('INT', $name);

        $this->columns[] = $column;

        return $column;
    }

    public function boolean(string $name): Column
    {
        $column = new Column('BOOLEAN', $name);

        $this->columns[] = $column;

        return $column;
    }

    public function timestamps(): void
    {
        $this->columns[] = new Column(
            'TIMESTAMP',
            'created_at'
        );

        $this->columns[] = new Column(
            'TIMESTAMP',
            'updated_at'
        );
    }

    public function getColumns(): array
    {
        return $this->columns;
    }
}