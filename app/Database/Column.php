<?php

declare(strict_types=1);

namespace App\Database;

class Column
{
    public bool $nullable = false;

    public bool $unique = false;

    public bool $autoIncrement = false;

    public bool $primary = false;

    public bool $unsigned = false;

    public bool $hasDefault = false;

    public mixed $default = null;

    public bool $defaultIsExpression = false;

    public bool $useCurrentOnUpdate = false;

    public ?int $precision = null;

    public ?int $scale = null;

    public function __construct(
        public string $type,
        public string $name,
        public ?int $length = null
    ) {
    }

    public function nullable(bool $value = true): static
    {
        $this->nullable = $value;

        return $this;
    }

    public function unique(bool $value = true): static
    {
        $this->unique = $value;

        return $this;
    }

    public function primary(bool $value = true): static
    {
        $this->primary = $value;

        return $this;
    }

    public function autoIncrement(bool $value = true): static
    {
        $this->autoIncrement = $value;

        return $this;
    }

    public function unsigned(bool $value = true): static
    {
        $this->unsigned = $value;

        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        $this->hasDefault = true;
        $this->defaultIsExpression = false;

        return $this;
    }

    public function defaultExpression(string $expression): static
    {
        $this->default = $expression;
        $this->hasDefault = true;
        $this->defaultIsExpression = true;

        return $this;
    }

    public function useCurrent(): static
    {
        return $this->defaultExpression('CURRENT_TIMESTAMP');
    }

    public function onUpdateCurrentTimestamp(
        bool $value = true
    ): static {
        $this->useCurrentOnUpdate = $value;

        return $this;
    }

    public function precision(
        int $precision,
        int $scale = 0
    ): static {
        $this->precision = max(1, $precision);
        $this->scale = max(0, $scale);

        return $this;
    }
}