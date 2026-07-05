<?php

declare(strict_types=1);

namespace App\Components;

abstract class BaseComponent
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function data(): array
    {
        return $this->data;
    }

    abstract public function view(): string;
}