<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Content\InstitutionalContentService;

/** Fachada mantida para compatibilidade com o Painel TV. */
final class TvIntelligentContentService
{
    public function __construct(private InstitutionalContentService $content) {}

    public function build(array $dashboard,array $hallOfFame,array $config,?string $referenceDate=null): array
    {
        return $this->content->build($dashboard,$hallOfFame,$config,$referenceDate);
    }
}
