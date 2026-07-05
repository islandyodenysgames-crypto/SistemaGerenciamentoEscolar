<?php

declare(strict_types=1);

namespace App\Components;

class PageHeader extends BaseComponent
{
    public function view(): string
    {
        return 'components/base/page-header';
    }
}