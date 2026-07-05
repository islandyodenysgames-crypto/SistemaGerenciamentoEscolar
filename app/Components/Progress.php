<?php

declare(strict_types=1);

namespace App\Components;

class Progress extends BaseComponent
{
    public function view(): string
    {
        return 'components/base/progress';
    }
}