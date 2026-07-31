<?php

declare(strict_types=1);

namespace App\Services\Content\Contracts;

interface ContentProviderInterface
{
    public function name(): string;

    /** @param array<string,mixed> $context */
    public function generate(string $type, array $context, string $style, string $referenceDate): string;
}
