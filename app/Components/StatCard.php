<?php

declare(strict_types=1);

namespace App\Components;

class StatCard extends BaseComponent
{
    public function view(): string
    {
        return 'components/base/stat-card';
    }
}