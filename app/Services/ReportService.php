<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Settings\SettingManager;
use App\Database\Connection;
use PDO;

class ReportService
{
    public function __construct(
        private AttendanceAnalyticsService $analyticsService,
        private SettingManager $settings
    ) {
    }

    public function daily(string $date): array
    {
        $ranking = $this->analyticsService->dailyRanking($date);
        $pending = $this->analyticsService->classesWithoutAttendanceToday($date);
        $completedClasses = count(array_filter(
            $ranking,
            static fn(array $row): bool => (int) ($row['has_attendance'] ?? 0) === 1
        ));
        $totalClasses = count($ranking);

        return [
            'summary' => $this->analyticsService->schoolFrequencyToday($date),
            'ranking' => $ranking,
            'classesWithoutAttendance' => $pending,
            'goal' => $this->goal(),
            'totalClasses' => $totalClasses,
            'completedClasses' => $completedClasses,
            'pendingClasses' => max(0, $totalClasses - $completedClasses),
            'coveragePercentage' => $totalClasses > 0
                ? round(($completedClasses / $totalClasses) * 100, 1)
                : 0.0,
            'generatedAt' => date('d/m/Y H:i'),
        ];
    }

    public function overview(): array
    {
        $db = Connection::getInstance();
        $goal = $this->goal();
        $start = date('Y-m-d', strtotime('-29 days'));
        $end = date('Y-m-d');

        return [
            'goal' => $goal,
            'totalStudents' => (int) $db->query("SELECT COUNT(*) FROM students WHERE active = 1")->fetchColumn(),
            'totalClasses' => (int) $db->query("SELECT COUNT(*) FROM school_classes WHERE active = 1")->fetchColumn(),
            'frequency' => $this->periodSummary($start, $end, null),
            'occurrences' => $this->occurrenceSummary($start, $end, null),
            'studentsBelowGoal' => count($this->studentPerformance($start, $end, null, $goal, true)),
            'periodStart' => $start,
            'periodEnd' => $end,
        ];
    }

    public function consolidated(string $startDate, string $endDate, ?int $classId = null): array
    {
        [$startDate, $endDate] = $this->normalizeDates($startDate, $endDate);
        $goal = $this->goal();
        $classes = $this->classes();
        $selectedClass = null;
        foreach ($classes as $class) {
            if ((int) $class['id'] === (int) $classId) {
                $selectedClass = $class;
                break;
            }
        }

        $summary = $this->periodSummary($startDate, $endDate, $classId);
        $classPerformance = $this->classPerformance($startDate, $endDate, $classId);
        $students = $this->studentPerformance($startDate, $endDate, $classId, $goal, false);
        $alerts = array_values(array_filter($students, static fn(array $row): bool => (int) ($row['total_records'] ?? 0) > 0 && (float) $row['percentage'] < $goal));

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'goal' => $goal,
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'summary' => $summary,
            'classPerformance' => $classPerformance,
            'studentPerformance' => $students,
            'studentsBelowGoal' => $alerts,
            'occurrences' => $this->occurrenceSummary($startDate, $endDate, $classId),
            'occurrencesByType' => $this->labelOccurrenceTypes($this->occurrencesByType($startDate, $endDate, $classId)),
            'trend' => $this->dailyTrend($startDate, $endDate, $classId),
            'generatedAt' => date('d/m/Y H:i'),
        ];
    }


    public function occurrenceReport(string $startDate, string $endDate, ?int $classId = null, ?string $type = null, ?string $severity = null, ?string $status = null): array
    {
        [$startDate, $endDate] = $this->normalizeDates($startDate, $endDate);
        $classes = $this->classes();
        $selectedClass = null;
        foreach ($classes as $class) {
            if ((int) $class['id'] === (int) $classId) { $selectedClass = $class; break; }
        }
        $filters = $this->normalizeOccurrenceFilters($type, $severity, $status);
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'selectedType' => $filters['type'],
            'selectedSeverity' => $filters['severity'],
            'selectedStatus' => $filters['status'],
            'occurrenceTypes' => $this->occurrenceTypeLabels(),
            'severityLabels' => $this->severityLabels(),
            'statusLabels' => $this->statusLabels(),
            'summary' => $this->occurrenceSummaryFiltered($startDate, $endDate, $classId, $filters),
            'byType' => $this->labelOccurrenceTypes($this->occurrenceGrouping($startDate, $endDate, $classId, $filters, 'o.type', 'type')),
            'bySeverity' => $this->labelRows($this->occurrenceGrouping($startDate, $endDate, $classId, $filters, 'o.severity', 'severity'), 'severity', $this->severityLabels()),
            'byStatus' => $this->labelRows($this->occurrenceGrouping($startDate, $endDate, $classId, $filters, 'o.status', 'status'), 'status', $this->statusLabels()),
            'bySubject' => $this->occurrenceGrouping($startDate, $endDate, $classId, $filters, "COALESCE(sub.name, 'Geral da escola')", 'label', ' LEFT JOIN subjects sub ON sub.id=o.subject_id'),
            'records' => $this->occurrenceRecords($startDate, $endDate, $classId, $filters),
            'generatedAt' => date('d/m/Y H:i'),
        ];
    }

    public function occurrenceTypeLabels(): array
    {
        return [
            'OBSERVATION' => 'Observação',
            'WARNING' => 'Advertência',
            'SUSPENSION' => 'Suspensão',
            'REFERRAL' => 'Encaminhamento',
            'PRAISE' => 'Elogio',
            'OTHER' => 'Outro',
        ];
    }

    private function severityLabels(): array
    {
        return ['LOW'=>'Baixa','MEDIUM'=>'Média','HIGH'=>'Alta','CRITICAL'=>'Crítica'];
    }

    private function statusLabels(): array
    {
        return ['OPEN'=>'Aberta','IN_PROGRESS'=>'Em acompanhamento','RESOLVED'=>'Resolvida','CLOSED'=>'Encerrada'];
    }

    private function labelOccurrenceTypes(array $rows): array
    {
        return $this->labelRows($rows, 'type', $this->occurrenceTypeLabels());
    }

    private function labelRows(array $rows, string $key, array $labels): array
    {
        foreach ($rows as &$row) {
            $code = strtoupper((string)($row[$key] ?? ''));
            $row['code'] = $code;
            $row['label'] = $labels[$code] ?? ($code !== '' ? ucfirst(strtolower(str_replace('_', ' ', $code))) : 'Não informado');
        }
        unset($row);
        return $rows;
    }

    private function normalizeOccurrenceFilters(?string $type, ?string $severity, ?string $status): array
    {
        $type = strtoupper(trim((string)$type));
        $severity = strtoupper(trim((string)$severity));
        $status = strtoupper(trim((string)$status));
        return [
            'type' => array_key_exists($type, $this->occurrenceTypeLabels()) ? $type : null,
            'severity' => array_key_exists($severity, $this->severityLabels()) ? $severity : null,
            'status' => array_key_exists($status, $this->statusLabels()) ? $status : null,
        ];
    }

    private function occurrenceWhere(string $startDate, string $endDate, ?int $classId, array $filters): array
    {
        $joins = ' LEFT JOIN enrollments e ON e.student_id=o.student_id AND e.active=1 LEFT JOIN school_classes sc ON sc.id=e.school_class_id';
        $where = ' WHERE o.occurrence_date BETWEEN :start_date AND :end_date';
        $params = ['start_date'=>$startDate,'end_date'=>$endDate];
        if ($classId) { $where .= ' AND sc.id=:class_id'; $params['class_id']=$classId; }
        foreach (['type','severity','status'] as $field) {
            if (!empty($filters[$field])) { $where .= " AND o.{$field}=:{$field}"; $params[$field]=$filters[$field]; }
        }
        return [$joins, $where, $params];
    }

    private function occurrenceSummaryFiltered(string $startDate, string $endDate, ?int $classId, array $filters): array
    {
        [$joins,$where,$params] = $this->occurrenceWhere($startDate,$endDate,$classId,$filters);
        $stmt=Connection::getInstance()->prepare("SELECT COUNT(DISTINCT o.id) total, SUM(o.status='OPEN') open_count, SUM(o.status IN ('RESOLVED','CLOSED')) resolved_count, SUM(o.severity IN ('HIGH','CRITICAL')) serious_count, COUNT(DISTINCT o.student_id) affected_students, COUNT(DISTINCT sc.id) affected_classes FROM student_occurrences o {$joins} {$where}");
        $stmt->execute($params); $row=$stmt->fetch(PDO::FETCH_ASSOC)?:[];
        foreach(['total','open_count','resolved_count','serious_count','affected_students','affected_classes'] as $k) $row[$k]=(int)($row[$k]??0);
        return $row;
    }

    private function occurrenceGrouping(string $startDate, string $endDate, ?int $classId, array $filters, string $expression, string $alias, string $extraJoin=''): array
    {
        [$joins,$where,$params] = $this->occurrenceWhere($startDate,$endDate,$classId,$filters);
        $stmt=Connection::getInstance()->prepare("SELECT {$expression} {$alias}, COUNT(DISTINCT o.id) total FROM student_occurrences o {$joins} {$extraJoin} {$where} GROUP BY {$expression} ORDER BY total DESC LIMIT 12");
        $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC)?:[];
    }

    private function occurrenceRecords(string $startDate, string $endDate, ?int $classId, array $filters): array
    {
        [$joins,$where,$params] = $this->occurrenceWhere($startDate,$endDate,$classId,$filters);
        $stmt=Connection::getInstance()->prepare("SELECT o.id,o.occurrence_date,o.type,o.severity,o.status,o.title,s.name student_name,sc.name class_name,COALESCE(sub.name,'Geral da escola') subject_name,COALESCE(u.name,'Não informado') author_name FROM student_occurrences o INNER JOIN students s ON s.id=o.student_id {$joins} LEFT JOIN subjects sub ON sub.id=o.subject_id LEFT JOIN users u ON u.id=o.created_by {$where} ORDER BY o.occurrence_date DESC,o.id DESC LIMIT 500");
        $stmt->execute($params); $rows=$stmt->fetchAll(PDO::FETCH_ASSOC)?:[];
        $types=$this->occurrenceTypeLabels(); $severities=$this->severityLabels(); $statuses=$this->statusLabels();
        foreach($rows as &$row){$row['type_label']=$types[strtoupper((string)$row['type'])]??'Outro';$row['severity_label']=$severities[strtoupper((string)$row['severity'])]??'Não informada';$row['status_label']=$statuses[strtoupper((string)$row['status'])]??'Não informado';} unset($row);
        return $rows;
    }

    public function classes(): array
    {
        $stmt = Connection::getInstance()->query("SELECT id, name, year, shift FROM school_classes WHERE active = 1 ORDER BY year, name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function goal(): float
    {
        return max(0.0, min(100.0, (float) $this->settings->get('school_goals.frequency_goal', 95.0)));
    }

    private function normalizeDates(string $startDate, string $endDate): array
    {
        $start = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate) ?: new \DateTimeImmutable('-29 days');
        $end = \DateTimeImmutable::createFromFormat('Y-m-d', $endDate) ?: new \DateTimeImmutable('today');
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }
        return [$start->format('Y-m-d'), $end->format('Y-m-d')];
    }

    private function classCondition(?int $classId, string $alias = 'a'): array
    {
        if ($classId !== null && $classId > 0) {
            return [" AND {$alias}.school_class_id = :class_id", ['class_id' => $classId]];
        }
        return ['', []];
    }

    private function periodSummary(string $startDate, string $endDate, ?int $classId): array
    {
        [$classSql, $params] = $this->classCondition($classId);
        $stmt = Connection::getInstance()->prepare("SELECT COUNT(ai.id) total_records,
            SUM(ai.status='P') presentes, SUM(ai.status='F') faltas,
            SUM(ai.status='FJ') justificadas, SUM(ai.status='AM') atestados,
            SUM(ai.status='FO') onibus,
            ROUND(100 * SUM(ai.status='P') / NULLIF(COUNT(ai.id),0),1) percentage,
            COUNT(DISTINCT a.attendance_date) school_days,
            COUNT(DISTINCT a.school_class_id) classes_with_attendance
            FROM attendance a INNER JOIN attendance_items ai ON ai.attendance_id=a.id
            WHERE a.attendance_date BETWEEN :start_date AND :end_date {$classSql}");
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate] + $params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach (['total_records','presentes','faltas','justificadas','atestados','onibus','school_days','classes_with_attendance'] as $key) {
            $row[$key] = (int) ($row[$key] ?? 0);
        }
        $row['percentage'] = (float) ($row['percentage'] ?? 0);
        return $row;
    }

    private function classPerformance(string $startDate, string $endDate, ?int $classId): array
    {
        $extra = $classId ? ' AND sc.id = :class_id' : '';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($classId) $params['class_id'] = $classId;
        $stmt = Connection::getInstance()->prepare("SELECT sc.id class_id, sc.name class_name, sc.year, sc.shift,
            COUNT(ai.id) total_records, SUM(ai.status='P') presentes, SUM(ai.status='F') faltas,
            SUM(ai.status IN ('FJ','AM','FO')) justificadas,
            ROUND(100 * SUM(ai.status='P') / NULLIF(COUNT(ai.id),0),1) percentage,
            COUNT(DISTINCT a.attendance_date) school_days
            FROM school_classes sc
            LEFT JOIN attendance a ON a.school_class_id=sc.id AND a.attendance_date BETWEEN :start_date AND :end_date
            LEFT JOIN attendance_items ai ON ai.attendance_id=a.id
            WHERE sc.active=1 {$extra}
            GROUP BY sc.id, sc.name, sc.year, sc.shift
            ORDER BY percentage DESC, sc.year, sc.name");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function studentPerformance(string $startDate, string $endDate, ?int $classId, float $goal, bool $onlyBelow): array
    {
        $extra = $classId ? ' AND sc.id = :class_id' : '';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($classId) $params['class_id'] = $classId;
        $having = $onlyBelow ? ' HAVING COUNT(ai.id) > 0 AND percentage < :goal' : '';
        if ($onlyBelow) $params['goal'] = $goal;
        $stmt = Connection::getInstance()->prepare("SELECT s.id student_id, s.name student_name, s.registration,
            sc.id class_id, sc.name class_name, sc.year, sc.shift,
            COUNT(ai.id) total_records,
            COALESCE(SUM(ai.status='P'), 0) presentes,
            COALESCE(SUM(ai.status='F'), 0) faltas,
            COALESCE(SUM(ai.status='FJ'), 0) faltas_justificadas,
            COALESCE(SUM(ai.status='AM'), 0) atestados,
            COALESCE(SUM(ai.status='FO'), 0) faltas_onibus,
            COALESCE(SUM(ai.status IN ('FJ','AM','FO')), 0) justificadas,
            COALESCE(ROUND(100 * SUM(ai.status='P') / NULLIF(COUNT(ai.id),0),1), 0) percentage
            FROM students s
            INNER JOIN enrollments e ON e.student_id=s.id AND e.active=1
            INNER JOIN school_classes sc ON sc.id=e.school_class_id
            LEFT JOIN attendance a
                ON a.school_class_id=sc.id
               AND a.attendance_date BETWEEN :start_date AND :end_date
            LEFT JOIN attendance_items ai
                ON ai.attendance_id=a.id
               AND ai.student_id=s.id
            WHERE s.active=1 {$extra}
            GROUP BY s.id, s.name, s.registration, sc.id, sc.name, sc.year, sc.shift
            {$having}
            ORDER BY CASE WHEN COUNT(ai.id)=0 THEN 1 ELSE 0 END, percentage ASC, s.name ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function occurrenceSummary(string $startDate, string $endDate, ?int $classId): array
    {
        $joins = '';
        $extra = '';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($classId) {
            $joins = ' INNER JOIN enrollments e ON e.student_id=o.student_id AND e.active=1';
            $extra = ' AND e.school_class_id=:class_id';
            $params['class_id'] = $classId;
        }
        $stmt = Connection::getInstance()->prepare("SELECT COUNT(*) total,
            SUM(o.status='OPEN') open_count,
            SUM(o.status<>'OPEN') resolved_count,
            SUM(o.severity IN ('HIGH','CRITICAL')) serious_count,
            COUNT(DISTINCT o.student_id) affected_students
            FROM student_occurrences o {$joins}
            WHERE o.occurrence_date BETWEEN :start_date AND :end_date {$extra}");
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        foreach (['total','open_count','resolved_count','serious_count','affected_students'] as $key) $row[$key]=(int)($row[$key]??0);
        return $row;
    }

    private function occurrencesByType(string $startDate, string $endDate, ?int $classId): array
    {
        $joins = '';
        $extra = '';
        $params = ['start_date' => $startDate, 'end_date' => $endDate];
        if ($classId) {
            $joins = ' INNER JOIN enrollments e ON e.student_id=o.student_id AND e.active=1';
            $extra = ' AND e.school_class_id=:class_id';
            $params['class_id'] = $classId;
        }
        $stmt = Connection::getInstance()->prepare("SELECT o.type, COUNT(*) total FROM student_occurrences o {$joins}
            WHERE o.occurrence_date BETWEEN :start_date AND :end_date {$extra}
            GROUP BY o.type ORDER BY total DESC LIMIT 10");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function dailyTrend(string $startDate, string $endDate, ?int $classId): array
    {
        [$classSql, $params] = $this->classCondition($classId);
        $stmt = Connection::getInstance()->prepare("SELECT a.attendance_date date,
            ROUND(100 * SUM(ai.status='P') / NULLIF(COUNT(ai.id),0),1) percentage,
            COUNT(ai.id) total_records
            FROM attendance a INNER JOIN attendance_items ai ON ai.attendance_id=a.id
            WHERE a.attendance_date BETWEEN :start_date AND :end_date {$classSql}
            GROUP BY a.attendance_date ORDER BY a.attendance_date");
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate] + $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
