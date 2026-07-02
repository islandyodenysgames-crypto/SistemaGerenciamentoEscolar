<?php

declare(strict_types=1);

namespace App\Database;

class Column
{
    public function __construct(

        public string $type,

        public string $name,

        public ?int $length = null

    ) {

    }

    public bool $nullable = false;

    public bool $unique = false;

    public bool $autoIncrement = false;

    public bool $primary = false;

    public mixed $default = null;

    public function nullable(): static
    {
        $this->nullable = true;

        return $this;
    }

    public function unique(): static
    {
        $this->unique = true;

        return $this;
    }

    public function primary(): static
    {
        $this->primary = true;

        return $this;
    }

    public function autoIncrement(): static
    {
        $this->autoIncrement = true;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;

        return $this;
    }
}