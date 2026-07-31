<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

final class InsightPriority
{
    public const CRITICAL = 100;
    public const ATTENTION = 70;
    public const POSITIVE = 40;
    public const INFORMATION = 20;

    public static function level(int $priority): string
    {
        return match (true) {
            $priority >= self::CRITICAL => 'CRITICAL',
            $priority >= self::ATTENTION => 'ATTENTION',
            $priority >= self::POSITIVE => 'POSITIVE',
            default => 'INFORMATION',
        };
    }
}
