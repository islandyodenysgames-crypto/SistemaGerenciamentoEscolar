<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

final class InsightEngine
{
    public function __construct(private InsightGenerator $generator)
    {
    }

    public function school(array $data, int $limit = 6): array
    {
        $insights = $this->generator->school($data);
        usort($insights, static fn(array $a, array $b): int => [$b['priority'], $a['title']] <=> [$a['priority'], $b['title']]);

        return array_slice($insights, 0, max(1, $limit));
    }
}
