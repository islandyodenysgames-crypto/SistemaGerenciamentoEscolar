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

        return $this->addColumn($column);
    }

    public function string(
        string $name,
        int $length = 255
    ): Column {
        return $this->addColumn(
            new Column('VARCHAR', $name, $length)
        );
    }

    public function char(
        string $name,
        int $length = 1
    ): Column {
        return $this->addColumn(
            new Column('CHAR', $name, $length)
        );
    }

    public function email(string $name = 'email'): Column
    {
        return $this->string($name, 180);
    }

    public function password(string $name = 'password'): Column
    {
        return $this->string($name, 255);
    }

    public function integer(string $name): Column
    {
        return $this->addColumn(
            new Column('INT', $name)
        );
    }

    public function unsignedInteger(string $name): Column
    {
        return $this->integer($name)->unsigned();
    }

    public function bigInteger(string $name): Column
    {
        return $this->addColumn(
            new Column('BIGINT', $name)
        );
    }

    public function unsignedBigInteger(string $name): Column
    {
        return $this->bigInteger($name)->unsigned();
    }

    public function smallInteger(string $name): Column
    {
        return $this->addColumn(
            new Column('SMALLINT', $name)
        );
    }

    public function tinyInteger(string $name): Column
    {
        return $this->addColumn(
            new Column('TINYINT', $name)
        );
    }

    public function boolean(string $name): Column
    {
        return $this->addColumn(
            new Column('BOOLEAN', $name)
        );
    }

    public function decimal(
        string $name,
        int $precision = 10,
        int $scale = 2
    ): Column {
        $column = new Column('DECIMAL', $name);

        $column->precision($precision, $scale);

        return $this->addColumn($column);
    }

    public function float(string $name): Column
    {
        return $this->addColumn(
            new Column('FLOAT', $name)
        );
    }

    public function double(string $name): Column
    {
        return $this->addColumn(
            new Column('DOUBLE', $name)
        );
    }

    public function date(string $name): Column
    {
        return $this->addColumn(
            new Column('DATE', $name)
        );
    }

    public function dateTime(string $name): Column
    {
        return $this->addColumn(
            new Column('DATETIME', $name)
        );
    }

    public function timestamp(string $name): Column
    {
        return $this->addColumn(
            new Column('TIMESTAMP', $name)
        );
    }

    public function time(string $name): Column
    {
        return $this->addColumn(
            new Column('TIME', $name)
        );
    }

    public function text(string $name): Column
    {
        return $this->addColumn(
            new Column('TEXT', $name)
        );
    }

    public function mediumText(string $name): Column
    {
        return $this->addColumn(
            new Column('MEDIUMTEXT', $name)
        );
    }

    public function longText(string $name): Column
    {
        return $this->addColumn(
            new Column('LONGTEXT', $name)
        );
    }

    public function json(string $name): Column
    {
        return $this->addColumn(
            new Column('JSON', $name)
        );
    }

    public function createdAt(): Column
    {
        return $this->timestamp('created_at')
            ->useCurrent();
    }

    public function updatedAt(): Column
    {
        return $this->timestamp('updated_at')
            ->useCurrent()
            ->onUpdateCurrentTimestamp();
    }

    public function timestamps(): void
    {
        $this->createdAt();
        $this->updatedAt();
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    private function addColumn(Column $column): Column
    {
        $this->columns[] = $column;

        return $column;
    }
}