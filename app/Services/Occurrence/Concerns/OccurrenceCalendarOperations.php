<?php

declare(strict_types=1);

namespace App\Services\Occurrence\Concerns;

trait OccurrenceCalendarOperations
{
    public function calendarMonth(
            int $year,
            int $month
        ): array {
            return $this->repository
                ->calendarMonth(
                    $year,
                    $month
                );
        }

    public function byDate(
            string $date
        ): array {
            if (!$this->isValidDate($date)) {
                return [];
            }

            $items = $this->repository->byDate(
                $date
            );

            return $this->decorateOccurrences(
                $items
            );
        }

}
