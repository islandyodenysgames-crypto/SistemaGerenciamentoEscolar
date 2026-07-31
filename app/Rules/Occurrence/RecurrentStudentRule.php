<?php

declare(strict_types=1);

namespace App\Rules\Occurrence;

use App\DTOs\Occurrence\AnalysisContextDTO;
use App\DTOs\Occurrence\RuleResultDTO;

final class RecurrentStudentRule extends AbstractRule
{
    private const TYPE = 'RECURRENT_STUDENT';

    public function __construct(
        private readonly int $minimumOccurrences = 2,
        private readonly int $periodDays = 30,
        private readonly int $score = 25
    ) {
    }

    public function name(): string { return 'recurrent_student'; }
    public function priority(): int { return 10; }

    public function supports(AnalysisContextDTO $context): bool
    {
        return $context->studentId() > 0;
    }

    protected function analyze(AnalysisContextDTO $context): RuleResultDTO
    {
        $groups = $this->recurrentGroups($context->occurrences());
        if ($groups === []) {
            return $this->fail(
                type: self::TYPE,
                title: 'Sem reincidência',
                message: sprintf(
                    'Não há repetição do mesmo título ou da mesma disciplina nos últimos %d dias.',
                    $this->periodDays
                ),
                metadata: [
                    'student_id' => $context->studentId(),
                    'minimum_occurrences' => $this->minimumOccurrences,
                    'period_days' => $this->periodDays,
                    'criterion' => 'same_title_or_subject',
                ]
            );
        }

        usort($groups, static fn(array $a, array $b): int => $b['total'] <=> $a['total']);
        $main = $groups[0];
        $total = (int) $main['total'];

        return $this->success(
            type: self::TYPE,
            title: 'Aluno reincidente',
            message: sprintf(
                '%d registros recorrentes por %s: %s.',
                $total,
                $main['criterion'] === 'TITLE' ? 'mesmo título' : 'mesma disciplina',
                $main['label']
            ),
            severity: $total >= $this->minimumOccurrences * 3 ? 'DANGER' : 'WARNING',
            score: $this->score,
            factors: [sprintf('%d registros com %s', $total, $main['label'])],
            metadata: [
                'student_id' => $context->studentId(),
                'total_occurrences' => $total,
                'minimum_occurrences' => $this->minimumOccurrences,
                'period_days' => $this->periodDays,
                'criterion' => $main['criterion'],
                'criterion_label' => $main['label'],
                'recurrent_groups' => $groups,
            ]
        );
    }

    private function recurrentGroups(array $occurrences): array
    {
        $limitDate = new \DateTimeImmutable(sprintf('-%d days', $this->periodDays));
        $titles = [];
        $subjects = [];

        foreach ($occurrences as $occurrence) {
            if (!is_array($occurrence)) continue;
            $dateValue = $occurrence['occurred_at'] ?? $occurrence['occurrence_date'] ?? $occurrence['date'] ?? $occurrence['created_at'] ?? null;
            if (!is_string($dateValue) || trim($dateValue) === '') continue;
            try { $date = new \DateTimeImmutable($dateValue); } catch (\Throwable) { continue; }
            if ($date < $limitDate) continue;

            $title = $this->normalize((string) ($occurrence['title'] ?? ''));
            if ($title !== '') {
                $titles[$title]['label'] = trim((string) ($occurrence['title'] ?? ''));
                $titles[$title]['total'] = (int) ($titles[$title]['total'] ?? 0) + 1;
            }

            $subjectKey = (string) ($occurrence['subject_id'] ?? $occurrence['discipline_id'] ?? $occurrence['subject_name'] ?? $occurrence['discipline'] ?? '');
            if ($subjectKey !== '') {
                $subjects[$subjectKey]['label'] = (string) ($occurrence['subject_name'] ?? $occurrence['discipline'] ?? 'Disciplina recorrente');
                $subjects[$subjectKey]['total'] = (int) ($subjects[$subjectKey]['total'] ?? 0) + 1;
            }
        }

        $groups = [];
        foreach ($titles as $item) {
            if (($item['total'] ?? 0) >= $this->minimumOccurrences) $groups[] = ['criterion' => 'TITLE'] + $item;
        }
        foreach ($subjects as $item) {
            if (($item['total'] ?? 0) >= $this->minimumOccurrences) $groups[] = ['criterion' => 'SUBJECT'] + $item;
        }
        return $groups;
    }

    private function normalize(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}
