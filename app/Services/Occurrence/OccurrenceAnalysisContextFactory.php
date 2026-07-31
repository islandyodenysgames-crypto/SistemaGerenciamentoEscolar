<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\DTOs\Occurrence\AnalysisContextDTO;
use App\Repositories\Occurrence\OccurrenceRepository;
use App\Repositories\StudentRepository;
use DateTimeImmutable;
use InvalidArgumentException;

final class OccurrenceAnalysisContextFactory
{
    public function __construct(
        private readonly StudentRepository $studentRepository,
        private readonly OccurrenceRepository $occurrenceRepository
    ) {
    }

    public function createForStudent(
        int $studentId,
        ?DateTimeImmutable $startDate = null,
        ?DateTimeImmutable $endDate = null
    ): AnalysisContextDTO {
        if ($studentId <= 0) {
            throw new InvalidArgumentException(
                'O aluno informado é inválido.'
            );
        }

        if (
            $startDate !== null
            && $endDate !== null
            && $startDate > $endDate
        ) {
            throw new InvalidArgumentException(
                'A data inicial não pode ser posterior à data final.'
            );
        }

        $student = $this->studentRepository->find($studentId) ?? [];
        $occurrences = $this->occurrenceRepository->byStudent($studentId);
        $occurrences = $this->filterByPeriod(
            $occurrences,
            $startDate,
            $endDate
        );

        return new AnalysisContextDTO(
            studentId: $studentId,
            student: $student,
            occurrences: $occurrences,
            statistics: $this->buildStatistics($occurrences),
            metadata: [
                'generated_at' => (new DateTimeImmutable())->format(
                    'Y-m-d H:i:s'
                ),
                'student_found' => $student !== [],
                'source' => 'student_occurrences',
            ],
            startDate: $startDate,
            endDate: $endDate
        );
    }

    private function filterByPeriod(
        array $occurrences,
        ?DateTimeImmutable $startDate,
        ?DateTimeImmutable $endDate
    ): array {
        if ($startDate === null && $endDate === null) {
            return $occurrences;
        }

        return array_values(
            array_filter(
                $occurrences,
                static function (array $occurrence) use (
                    $startDate,
                    $endDate
                ): bool {
                    $dateValue = trim(
                        (string) ($occurrence['occurrence_date'] ?? '')
                    );

                    if ($dateValue === '') {
                        return false;
                    }

                    try {
                        $date = new DateTimeImmutable($dateValue);
                    } catch (\Throwable) {
                        return false;
                    }

                    if ($startDate !== null && $date < $startDate) {
                        return false;
                    }

                    if ($endDate !== null && $date > $endDate) {
                        return false;
                    }

                    return true;
                }
            )
        );
    }

    private function buildStatistics(array $occurrences): array
    {
        $byType = [];
        $bySeverity = [];
        $open = 0;
        $resolved = 0;
        $last30Days = 0;
        $limitDate = new DateTimeImmutable('-30 days');

        foreach ($occurrences as $occurrence) {
            $type = strtoupper(
                trim((string) ($occurrence['type'] ?? 'OTHER'))
            );
            $severity = strtoupper(
                trim((string) ($occurrence['severity'] ?? 'LOW'))
            );
            $status = strtoupper(
                trim((string) ($occurrence['status'] ?? 'OPEN'))
            );

            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $bySeverity[$severity] = ($bySeverity[$severity] ?? 0) + 1;

            if ($status === 'RESOLVED') {
                $resolved++;
            } else {
                $open++;
            }

            $dateValue = trim(
                (string) ($occurrence['occurrence_date'] ?? '')
            );

            if ($dateValue === '') {
                continue;
            }

            try {
                $date = new DateTimeImmutable($dateValue);
            } catch (\Throwable) {
                continue;
            }

            if ($date >= $limitDate) {
                $last30Days++;
            }
        }

        ksort($byType);
        ksort($bySeverity);

        return [
            'total_occurrences' => count($occurrences),
            'open_occurrences' => $open,
            'resolved_occurrences' => $resolved,
            'occurrences_last_30_days' => $last30Days,
            'occurrences_by_type' => $byType,
            'occurrences_by_severity' => $bySeverity,
        ];
    }
}
