<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

final class RecurrenceIntelligenceService
{
    public function dashboard(?int $periodDays = null, ?int $minimum = null): array
    {
        $periodDays = $periodDays ?? (int) $this->setting('analysis_window_days', 30);
        $minimum = $minimum ?? (int) $this->setting('recurrence_limit', 2);
        $periodDays = max(7, min(365, $periodDays));
        $minimum = max(2, $minimum);
        $rows = $this->occurrencesSince(date('Y-m-d', strtotime('-' . ($periodDays - 1) . ' days')));
        $cases = $this->buildCases($rows, $minimum);

        usort($cases, static fn(array $a, array $b): int =>
            ((int) $b['total_occurrences'] <=> (int) $a['total_occurrences'])
            ?: strcmp((string) $b['last_occurrence'], (string) $a['last_occurrence'])
        );

        $studentIds = array_values(array_unique(array_map('intval', array_column($cases, 'student_id'))));
        $newThisWeek = count(array_filter($cases, static fn(array $case): bool =>
            !empty($case['last_occurrence']) && strtotime((string) $case['last_occurrence']) >= strtotime('-7 days')
        ));

        return [
            'active_students' => count($studentIds),
            'active_cases' => count($cases),
            'critical_cases' => count(array_filter($cases, static fn(array $case): bool => ($case['risk_level'] ?? '') === 'CRITICAL')),
            'new_this_week' => $newThisWeek,
            'period_days' => $periodDays,
            'minimum_occurrences' => $minimum,
            'criterion_description' => 'Mesmo título ou mesma disciplina',
            'cases' => array_slice($cases, 0, 12),
        ];
    }

    /**
     * Analisa a ocorrência recém-criada pelos critérios reais de reincidência:
     * mesmo título OU mesma disciplina.
     */
    public function analyzeOccurrence(
        int $studentId,
        string $title,
        int $subjectId,
        ?int $periodDays = null,
        ?int $minimum = null
    ): array {
        $periodDays = $periodDays ?? (int) $this->setting('analysis_window_days', 30);
        $minimum = $minimum ?? (int) $this->setting('recurrence_limit', 2);
        $periodDays = max(7, min(365, $periodDays));
        $minimum = max(2, $minimum);
        $rows = $this->occurrencesSince(
            date('Y-m-d', strtotime('-' . ($periodDays - 1) . ' days')),
            $studentId
        );
        $cases = $this->buildCases($rows, $minimum);
        $normalizedTitle = $this->normalize($title);

        $matching = array_values(array_filter($cases, static function (array $case) use ($normalizedTitle, $subjectId): bool {
            return (($case['criterion'] ?? '') === 'TITLE' && ($case['criterion_key'] ?? '') === $normalizedTitle)
                || (($case['criterion'] ?? '') === 'SUBJECT' && (int) ($case['subject_id'] ?? 0) === $subjectId);
        }));

        usort($matching, static fn(array $a, array $b): int => (int) $b['total_occurrences'] <=> (int) $a['total_occurrences']);
        $case = $matching[0] ?? null;

        return [
            'is_recurrent' => $case !== null,
            'total' => (int) ($case['total_occurrences'] ?? 0),
            'period_days' => $periodDays,
            'severity' => (string) ($case['risk_level'] ?? 'ATTENTION'),
            'criterion' => (string) ($case['criterion'] ?? ''),
            'criterion_label' => (string) ($case['group_label'] ?? ''),
            'case' => $case,
        ];
    }

    /** Compatibilidade com chamadas antigas. */
    public function analyzeStudentType(int $studentId, string $type, ?int $periodDays = null, ?int $minimum = null): array
    {
        $periodDays = $periodDays ?? (int) $this->setting('analysis_window_days', 30);
        $minimum = $minimum ?? (int) $this->setting('recurrence_limit', 2);
        $periodDays = max(7, min(365, $periodDays));
        $minimum = max(2, $minimum);
        $rows = $this->occurrencesSince(date('Y-m-d', strtotime('-' . ($periodDays - 1) . ' days')), $studentId);
        $filtered = array_values(array_filter($rows, static fn(array $row): bool => strtoupper((string) ($row['type'] ?? '')) === strtoupper($type)));
        $total = count($filtered);

        return [
            'is_recurrent' => $total >= max(2, $minimum),
            'total' => $total,
            'type' => $type,
            'period_days' => $periodDays,
            'severity' => $total >= 6 ? 'CRITICAL' : ($total >= 4 ? 'HIGH' : 'ATTENTION'),
        ];
    }

    public function studentDashboard(int $studentId, ?int $periodDays = null, ?int $minimum = null): array
    {
        $periodDays = $periodDays ?? (int) $this->setting('analysis_window_days', 30);
        $minimum = $minimum ?? (int) $this->setting('recurrence_limit', 2);
        $periodDays = max(7, min(365, $periodDays));
        $minimum = max(2, $minimum);
        $currentStart = date('Y-m-d', strtotime('-' . ($periodDays - 1) . ' days'));
        $previousStart = date('Y-m-d', strtotime('-' . (($periodDays * 2) - 1) . ' days'));
        $previousEnd = date('Y-m-d', strtotime('-' . $periodDays . ' days'));

        $currentCases = $this->buildCases($this->occurrencesBetween($previousEnd < $currentStart ? $currentStart : $currentStart, date('Y-m-d'), $studentId), $minimum);
        $previousCases = $this->buildCases($this->occurrencesBetween($previousStart, $previousEnd, $studentId), $minimum);
        $previousMap = [];
        foreach ($previousCases as $case) {
            $previousMap[$case['group_key']] = (int) $case['total_occurrences'];
        }

        $total = 0;
        $critical = 0;
        foreach ($currentCases as &$case) {
            $current = (int) ($case['total_occurrences'] ?? 0);
            $previous = (int) ($previousMap[$case['group_key']] ?? 0);
            $difference = $current - $previous;
            $case['previous_occurrences'] = $previous;
            $case['difference'] = $difference;
            $case['trend'] = $difference > 0 ? 'WORSENING' : ($difference < 0 ? 'IMPROVING' : 'STABLE');
            $total += $current;
            if (($case['risk_level'] ?? '') === 'CRITICAL') $critical++;
        }
        unset($case);

        usort($currentCases, static fn(array $a, array $b): int => (int) $b['total_occurrences'] <=> (int) $a['total_occurrences']);

        return [
            'has_recurrence' => $currentCases !== [],
            'active_cases' => count($currentCases),
            'total_occurrences' => $total,
            'critical_cases' => $critical,
            'period_days' => $periodDays,
            'minimum_occurrences' => $minimum,
            'criterion_description' => 'Mesmo título ou mesma disciplina',
            'cases' => $currentCases,
        ];
    }

    private function occurrencesSince(string $startDate, ?int $studentId = null): array
    {
        return $this->occurrencesBetween($startDate, date('Y-m-d'), $studentId);
    }

    private function occurrencesBetween(string $startDate, string $endDate, ?int $studentId = null): array
    {
        $db = Connection::getInstance();
        $sql = "
            SELECT so.id, so.student_id, so.subject_id, so.occurrence_date, so.type,
                   so.title, so.description, so.severity, so.status,
                   s.name AS student_name,
                   subj.name AS subject_name,
                   sc.name AS class_name
            FROM student_occurrences so
            INNER JOIN students s ON s.id = so.student_id
            LEFT JOIN subjects subj ON subj.id = so.subject_id
            LEFT JOIN enrollments e ON e.student_id = s.id AND e.active = 1
            LEFT JOIN school_classes sc ON sc.id = e.school_class_id
            WHERE so.occurrence_date BETWEEN :start_date AND :end_date
        ";
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($studentId !== null) {
            $sql .= ' AND so.student_id = :student_id';
            $params['student_id'] = $studentId;
        }
        $sql .= ' ORDER BY so.occurrence_date DESC, so.id DESC';
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    private function buildCases(array $rows, int $minimum): array
    {
        $titleGroups = [];
        $subjectGroups = [];

        foreach ($rows as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if ($studentId <= 0) continue;

            $titleKey = $this->normalize((string) ($row['title'] ?? ''));
            if ($titleKey !== '') {
                $key = $studentId . '|TITLE|' . $titleKey;
                $titleGroups[$key][] = $row;
            }

            $subjectId = (int) ($row['subject_id'] ?? 0);
            if ($subjectId > 0) {
                $key = $studentId . '|SUBJECT|' . $subjectId;
                $subjectGroups[$key][] = $row;
            }
        }

        $cases = [];
        $qualifyingTitleRecordSets = [];
        foreach ($titleGroups as $key => $groupRows) {
            if (count($groupRows) < $minimum) continue;
            $case = $this->decorateCase($key, 'TITLE', $groupRows, $minimum);
            $cases[] = $case;
            $qualifyingTitleRecordSets[(int) $case['student_id']][] = array_map('intval', array_column($groupRows, 'id'));
        }

        foreach ($subjectGroups as $key => $groupRows) {
            if (count($groupRows) < $minimum) continue;
            $recordIds = array_map('intval', array_column($groupRows, 'id'));
            sort($recordIds);
            $duplicate = false;
            foreach ($qualifyingTitleRecordSets[(int) ($groupRows[0]['student_id'] ?? 0)] ?? [] as $titleIds) {
                sort($titleIds);
                if ($recordIds === $titleIds) {
                    $duplicate = true;
                    break;
                }
            }
            if (!$duplicate) $cases[] = $this->decorateCase($key, 'SUBJECT', $groupRows, $minimum);
        }

        return $cases;
    }

    private function decorateCase(string $key, string $criterion, array $rows, int $minimum): array
    {
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $b['occurrence_date'], (string) $a['occurrence_date']));
        $first = end($rows) ?: $rows[0];
        reset($rows);
        $latest = $rows[0];
        $total = count($rows);
        $risk = $total >= ($minimum * 3) ? 'CRITICAL' : ($total >= ($minimum * 2) ? 'HIGH' : 'ATTENTION');
        $label = $criterion === 'TITLE'
            ? (string) ($latest['title'] ?? 'Título não informado')
            : (string) ($latest['subject_name'] ?? 'Disciplina não informada');

        return [
            'group_key' => $key,
            'criterion_key' => $criterion === 'TITLE' ? $this->normalize((string) ($latest['title'] ?? '')) : (string) ((int) ($latest['subject_id'] ?? 0)),
            'criterion' => $criterion,
            'criterion_label' => $criterion === 'TITLE' ? 'Mesmo título' : 'Mesma disciplina',
            'group_label' => $label,
            'student_id' => (int) ($latest['student_id'] ?? 0),
            'student_name' => (string) ($latest['student_name'] ?? 'Aluno não identificado'),
            'class_name' => (string) ($latest['class_name'] ?? 'Sem turma'),
            'subject_id' => (int) ($latest['subject_id'] ?? 0),
            'subject_name' => (string) ($latest['subject_name'] ?? 'Disciplina não informada'),
            'title' => (string) ($latest['title'] ?? ''),
            'total_occurrences' => $total,
            'first_occurrence' => (string) ($first['occurrence_date'] ?? ''),
            'last_occurrence' => (string) ($latest['occurrence_date'] ?? ''),
            'days_since_last' => !empty($latest['occurrence_date']) ? max(0, (int) floor((time() - strtotime((string) $latest['occurrence_date'])) / 86400)) : null,
            'risk_level' => $risk,
            'records' => array_slice($rows, 0, 12),
        ];
    }

    private function setting(string $key, mixed $default): mixed
    {
        try {
            $stmt = Connection::getInstance()->prepare("SELECT value, type FROM system_settings WHERE setting_key = :setting_key LIMIT 1");
            $stmt->execute(['setting_key' => 'intelligence.' . $key]);
            $row = $stmt->fetch();
            if (!$row) return $default;
            return match ((string)($row['type'] ?? 'string')) {
                'integer' => (int)$row['value'],
                'float' => (float)$row['value'],
                'boolean' => in_array(strtolower((string)$row['value']), ['1','true','yes','on'], true),
                default => $row['value'],
            };
        } catch (\Throwable) {
            return $default;
        }
    }

    private function normalize(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }
}
