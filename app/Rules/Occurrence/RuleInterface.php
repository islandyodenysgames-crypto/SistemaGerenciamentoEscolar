<?php

declare(strict_types=1);

namespace App\Rules\Occurrence;

use App\DTOs\Occurrence\AnalysisContextDTO;
use App\DTOs\Occurrence\RuleResultDTO;

interface RuleInterface
{
    public function name(): string;

    public function priority(): int;

    public function supports(
        AnalysisContextDTO $context
    ): bool;

    public function evaluate(
        AnalysisContextDTO $context
    ): RuleResultDTO;
}