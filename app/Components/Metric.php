<?php

declare(strict_types=1);

namespace App\Components;

class Metric extends BaseComponent
{
    public function view(): string
    {
        return 'components/base/metric';
    }
}