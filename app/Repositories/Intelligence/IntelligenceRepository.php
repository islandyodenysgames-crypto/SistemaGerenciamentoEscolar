<?php

declare(strict_types=1);

namespace App\Repositories\Intelligence;

use App\Repositories\BaseRepository;

class IntelligenceRepository extends BaseRepository
{

    public function lowAttendanceStudents(float $threshold = 85.0): array
    {
        $threshold = max(0.0, min(100.0, $threshold));

        $stmt = $this->db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration,
                school_classes.name AS class_name,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / NULLIF(COUNT(attendance_items.id), 0)
                    ) * 100,
                    1
                ) AS attendance_percentage,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                0 AS total_occurrences,
                0 AS serious_occurrences,
                0 AS open_occurrences
            FROM students
            INNER JOIN attendance_items ON attendance_items.student_id = students.id
            LEFT JOIN school_classes ON school_classes.id = (
                SELECT e.school_class_id
                FROM enrollments e
                WHERE e.student_id = students.id AND e.active = 1
                ORDER BY e.id DESC
                LIMIT 1
            )
            WHERE students.active = 1
            GROUP BY students.id, students.name, students.registration, school_classes.name
            HAVING attendance_percentage < :threshold
            ORDER BY attendance_percentage ASC, students.name ASC
        ");
        $stmt->execute(['threshold' => $threshold]);

        return $stmt->fetchAll();
    }

    public function attendanceRiskStudents(string $from, string $to, int $limit = 10): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->db->prepare("\n            SELECT
                students.id,
                students.name,
                students.registration,
                school_classes.name AS class_name,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                SUM(CASE WHEN attendance_items.status IN ('FJ','AM','FO') THEN 1 ELSE 0 END) AS attenuated_absences,
                COUNT(attendance_items.id) AS attendance_records
            FROM attendance_items
            INNER JOIN attendance ON attendance.id = attendance_items.attendance_id
            INNER JOIN students ON students.id = attendance_items.student_id
            LEFT JOIN school_classes ON school_classes.id = (
                SELECT e.school_class_id
                FROM enrollments e
                WHERE e.student_id = students.id AND e.active = 1
                ORDER BY e.id DESC
                LIMIT 1
            )
            WHERE attendance.attendance_date BETWEEN :date_from AND :date_to
              AND students.active = 1
            GROUP BY students.id, students.name, students.registration, school_classes.name
            HAVING unjustified_absences > 0
            ORDER BY unjustified_absences DESC, attenuated_absences DESC, students.name ASC
            LIMIT {$limit}
        ");
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetchAll();
    }

    public function occurrenceRiskStudents(string $from, string $to, int $limit = 10): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->db->prepare("\n            SELECT
                students.id,
                students.name,
                students.registration,
                school_classes.name AS class_name,
                COUNT(student_occurrences.id) AS total_occurrences,
                SUM(CASE WHEN student_occurrences.severity IN ('HIGH','CRITICAL') THEN 1 ELSE 0 END) AS serious_occurrences,
                SUM(CASE WHEN student_occurrences.status = 'OPEN' THEN 1 ELSE 0 END) AS open_occurrences
            FROM student_occurrences
            INNER JOIN students ON students.id = student_occurrences.student_id
            LEFT JOIN school_classes ON school_classes.id = (
                SELECT e.school_class_id
                FROM enrollments e
                WHERE e.student_id = students.id AND e.active = 1
                ORDER BY e.id DESC
                LIMIT 1
            )
            WHERE student_occurrences.occurrence_date BETWEEN :date_from AND :date_to
              AND students.active = 1
              AND student_occurrences.nature = 'ADVERSE'
            GROUP BY students.id, students.name, students.registration, school_classes.name
            ORDER BY serious_occurrences DESC, total_occurrences DESC, open_occurrences DESC, students.name ASC
            LIMIT {$limit}
        ");
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetchAll();
    }


    public function activeMonitoringSummaries(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn(int $id): bool => $id > 0)));
        if ($studentIds === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->db->prepare("
            SELECT
                sm.student_id,
                sm.id AS monitoring_id,
                COUNT(DISTINCT smu.user_id) AS active_followers,
                GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', ') AS follower_names
            FROM student_monitoring sm
            INNER JOIN student_monitoring_users smu
                ON smu.monitoring_id = sm.id
               AND smu.status = 'ACTIVE'
               AND COALESCE(smu.start_date, sm.start_date) <= CURDATE()
               AND COALESCE(smu.end_date, sm.end_date) >= CURDATE()
            INNER JOIN users u ON u.id = smu.user_id
            WHERE sm.student_id IN ({$placeholders})
              AND sm.status = 'ACTIVE'
              AND sm.start_date <= CURDATE()
              AND sm.end_date >= CURDATE()
            GROUP BY sm.student_id, sm.id
            ORDER BY sm.id DESC
        ");
        $stmt->execute($studentIds);

        $summaries = [];
        foreach ($stmt->fetchAll() as $row) {
            $studentId = (int) ($row['student_id'] ?? 0);
            if ($studentId <= 0 || isset($summaries[$studentId])) {
                continue;
            }
            $summaries[$studentId] = [
                'monitoring_id' => (int) ($row['monitoring_id'] ?? 0),
                'active_followers' => (int) ($row['active_followers'] ?? 0),
                'follower_names' => (string) ($row['follower_names'] ?? ''),
            ];
        }

        return $summaries;
    }


    public function monitoringOperationalSummary(array $priorityStudentIds, int $staleDays = 14): array
    {
        $priorityStudentIds = array_values(array_unique(array_filter(
            array_map('intval', $priorityStudentIds),
            static fn(int $id): bool => $id > 0
        )));
        $staleDays = max(1, min(90, $staleDays));

        $result = [
            'priority_students' => count($priorityStudentIds),
            'active_monitoring' => 0,
            'covered_cases' => 0, // Compatibilidade: representa alunos prioritários cobertos.
            'covered_students' => 0,
            'without_follower' => 0, // Alunos prioritários sem acompanhante ativo.
            'without_recent_action' => 0, // Alunos, não quantidade de casos.
            'pending_recommendations' => 0,
            'coverage_percentage' => 0,
            'by_student' => [],
        ];

        if ($priorityStudentIds === []) {
            return $result;
        }

        $groups = $this->activeMonitoringCases($priorityStudentIds);
        $today = new \DateTimeImmutable('today');

        foreach ($priorityStudentIds as $studentId) {
            $cases = (array) ($groups[$studentId] ?? []);
            $result['active_monitoring'] += count($cases);

            $studentCovered = false;
            $studentStale = false;
            $studentPendingRecommendations = 0;
            $selected = null;

            foreach ($cases as $case) {
                $followers = (int) ($case['active_followers'] ?? 0);
                $pending = (int) ($case['pending_recommendations'] ?? 0);
                $studentPendingRecommendations += $pending;

                $reference = (string) ($case['latest_action_date'] ?? $case['start_date'] ?? '');
                $days = null;
                if ($reference !== '') {
                    $days = max(0, (int) (new \DateTimeImmutable($reference))->diff($today)->format('%r%a'));
                }

                // Só existe acompanhamento coberto quando há participante ativo.
                $stale = $followers > 0 && $days !== null && $days >= $staleDays;
                if ($followers > 0) {
                    $studentCovered = true;
                }
                if ($stale) {
                    $studentStale = true;
                }

                $case['days_without_action'] = $days;
                $case['without_recent_action'] = $stale;

                // Prioriza, para o resumo individual, um caso coberto e atrasado.
                if (
                    $selected === null
                    || ($stale && !($selected['without_recent_action'] ?? false))
                    || ($followers > 0 && (int) ($selected['active_followers'] ?? 0) === 0)
                ) {
                    $selected = $case;
                }
            }

            if ($studentCovered) {
                $result['covered_students']++;
            } else {
                $result['without_follower']++;
            }

            if ($studentStale) {
                $result['without_recent_action']++;
            }

            $result['pending_recommendations'] += $studentPendingRecommendations;

            if ($selected !== null) {
                $result['by_student'][$studentId] = [
                    'monitoring_id' => (int) ($selected['monitoring_id'] ?? 0),
                    'has_active_monitoring' => true,
                    'is_covered' => $studentCovered,
                    'active_case_count' => count($cases),
                    'active_followers' => (int) ($selected['active_followers'] ?? 0),
                    'latest_action_date' => $selected['latest_action_date'] ?? null,
                    'monitoring_start_date' => $selected['start_date'] ?? null,
                    'days_without_action' => $selected['days_without_action'] ?? null,
                    'without_recent_action' => $studentStale,
                    'pending_recommendations' => $studentPendingRecommendations,
                ];
            } else {
                $result['by_student'][$studentId] = [
                    'monitoring_id' => 0,
                    'has_active_monitoring' => false,
                    'is_covered' => false,
                    'active_case_count' => 0,
                    'active_followers' => 0,
                    'latest_action_date' => null,
                    'monitoring_start_date' => null,
                    'days_without_action' => null,
                    'without_recent_action' => false,
                    'pending_recommendations' => 0,
                ];
            }
        }

        // Mantém a chave usada pela view, agora com semântica correta por aluno.
        $result['covered_cases'] = $result['covered_students'];
        $result['coverage_percentage'] = $result['priority_students'] > 0
            ? (int) round(($result['covered_students'] / $result['priority_students']) * 100)
            : 0;

        return $result;
    }

    public function attendanceTotals(string $from, string $to): array
    {
        $stmt = $this->db->prepare("\n            SELECT
                COUNT(attendance_items.id) AS total_records,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                COUNT(DISTINCT CASE WHEN attendance_items.status = 'F' THEN attendance_items.student_id END) AS students_with_unjustified_absence
            FROM attendance_items
            INNER JOIN attendance ON attendance.id = attendance_items.attendance_id
            WHERE attendance.attendance_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetch() ?: [];
    }

    public function occurrenceTotals(string $from, string $to): array
    {
        $stmt = $this->db->prepare("\n            SELECT
                COUNT(*) AS total_occurrences,
                SUM(CASE WHEN severity IN ('HIGH','CRITICAL') THEN 1 ELSE 0 END) AS serious_occurrences,
                SUM(CASE WHEN status = 'OPEN' THEN 1 ELSE 0 END) AS open_occurrences,
                COUNT(DISTINCT student_id) AS students_with_occurrences
            FROM student_occurrences
            WHERE occurrence_date BETWEEN :date_from AND :date_to
              AND nature = 'ADVERSE'
        ");
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetch() ?: [];
    }

    public function classSignals(string $from, string $to, int $limit = 8): array
    {
        $limit = max(1, min(200, $limit));
        $stmt = $this->db->prepare("\n            SELECT
                school_classes.id,
                school_classes.name,
                COUNT(DISTINCT enrollments.student_id) AS active_students,
                COUNT(DISTINCT CASE WHEN attendance_items.status = 'F' THEN attendance_items.id END) AS unjustified_absences,
                COUNT(DISTINCT student_occurrences.id) AS total_occurrences,
                COUNT(DISTINCT CASE WHEN student_occurrences.severity IN ('HIGH','CRITICAL') THEN student_occurrences.id END) AS serious_occurrences
            FROM school_classes
            LEFT JOIN enrollments ON enrollments.school_class_id = school_classes.id AND enrollments.active = 1
            LEFT JOIN attendance ON attendance.school_class_id = school_classes.id AND attendance.attendance_date BETWEEN :att_from AND :att_to
            LEFT JOIN attendance_items ON attendance_items.attendance_id = attendance.id AND attendance_items.student_id = enrollments.student_id
            LEFT JOIN student_occurrences ON student_occurrences.student_id = enrollments.student_id AND student_occurrences.occurrence_date BETWEEN :occ_from AND :occ_to AND student_occurrences.nature = 'ADVERSE'
            WHERE school_classes.active = 1
            GROUP BY school_classes.id, school_classes.name
            HAVING unjustified_absences > 0 OR total_occurrences > 0
            ORDER BY serious_occurrences DESC, unjustified_absences DESC, total_occurrences DESC, school_classes.name ASC
            LIMIT {$limit}
        ");
        $stmt->execute([
            'att_from' => $from,
            'att_to' => $to,
            'occ_from' => $from,
            'occ_to' => $to,
        ]);
        return $stmt->fetchAll();
    }

    public function studentAttendanceTotals(int $studentId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("\n            SELECT
                COUNT(attendance_items.id) AS total_records,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presences,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                SUM(CASE WHEN attendance_items.status IN ('FJ','AM','FO') THEN 1 ELSE 0 END) AS attenuated_absences
            FROM attendance_items
            INNER JOIN attendance ON attendance.id = attendance_items.attendance_id
            WHERE attendance_items.student_id = :student_id
              AND attendance.attendance_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function studentOccurrenceTotals(int $studentId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("\n            SELECT
                COUNT(*) AS total_occurrences,
                SUM(CASE WHEN severity IN ('HIGH','CRITICAL') THEN 1 ELSE 0 END) AS serious_occurrences,
                SUM(CASE WHEN status = 'OPEN' THEN 1 ELSE 0 END) AS open_occurrences,
                SUM(CASE WHEN status IN ('RESOLVED','CLOSED') THEN 1 ELSE 0 END) AS resolved_occurrences
            FROM student_occurrences
            WHERE student_id = :student_id
              AND occurrence_date BETWEEN :date_from AND :date_to
              AND nature = 'ADVERSE'
        ");
        $stmt->execute([
            'student_id' => $studentId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetch() ?: [];
    }


    public function staleCriticalOccurrenceCases(int $days = 7): array
    {
        $days = max(1, min(90, $days));
        $stmt = $this->db->query("
            SELECT
                student_occurrences.id,
                student_occurrences.student_id,
                student_occurrences.title,
                student_occurrences.occurrence_date,
                student_occurrences.severity,
                student_occurrences.status,
                DATEDIFF(CURDATE(), student_occurrences.occurrence_date) AS days_open,
                students.name AS student_name,
                school_classes.name AS class_name
            FROM student_occurrences
            INNER JOIN students ON students.id = student_occurrences.student_id
            LEFT JOIN enrollments ON enrollments.student_id = students.id AND enrollments.active = 1
            LEFT JOIN school_classes ON school_classes.id = enrollments.school_class_id
            WHERE student_occurrences.severity = 'CRITICAL'
              AND student_occurrences.status = 'OPEN'
              AND student_occurrences.occurrence_date <= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            ORDER BY days_open DESC, student_occurrences.occurrence_date ASC
        ");
        return $stmt->fetchAll();
    }

    public function staleCriticalOccurrences(int $days = 7): int
    {
        $days = max(1, min(90, $days));
        $stmt = $this->db->query("\n            SELECT COUNT(*)
            FROM student_occurrences
            WHERE severity = 'CRITICAL'
              AND status = 'OPEN'
              AND occurrence_date <= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
        ");
        return (int) $stmt->fetchColumn();
    }
    public function classAttendanceTotals(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(attendance_items.id) AS total_records,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presences,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                SUM(CASE WHEN attendance_items.status IN ('FJ','AM','FO') THEN 1 ELSE 0 END) AS attenuated_absences,
                COUNT(DISTINCT CASE WHEN attendance_items.status = 'F' THEN attendance_items.student_id END) AS students_with_unjustified_absence
            FROM attendance_items
            INNER JOIN attendance ON attendance.id = attendance_items.attendance_id
            WHERE attendance.school_class_id = :class_id
              AND attendance.attendance_date BETWEEN :date_from AND :date_to
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function classAttendanceResponse(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT CASE
                    WHEN sm.status = 'ACTIVE'
                     AND sm.start_date <= :date_to_case
                     AND sm.end_date >= :date_from_case
                     AND (sm.problem_code = 'FREQUENCY' OR smp.target_metric = 'ATTENDANCE')
                     AND EXISTS (
                         SELECT 1 FROM student_monitoring_users smu
                         WHERE smu.monitoring_id = sm.id
                           AND smu.status = 'ACTIVE'
                           AND smu.start_date <= :date_to_link
                           AND smu.end_date >= :date_from_link
                     )
                    THEN sm.student_id
                END) AS students_with_followup,
                COUNT(DISTINCT CASE
                    WHEN (sma.id IS NOT NULL AND (sm.problem_code = 'FREQUENCY' OR smp.target_metric = 'ATTENDANCE'))
                      OR sor.id IS NOT NULL
                    THEN e.student_id
                END) AS students_with_intervention,
                COUNT(DISTINCT CASE
                    WHEN sm.problem_code = 'FREQUENCY' OR smp.target_metric = 'ATTENDANCE'
                    THEN sma.id
                END) + COUNT(DISTINCT sor.id) AS interventions,
                COUNT(DISTINCT CASE
                    WHEN sm.problem_code = 'FREQUENCY' OR smp.target_metric = 'ATTENDANCE'
                    THEN sma.id
                END) AS monitoring_actions,
                COUNT(DISTINCT sor.id) AS point_responses
            FROM enrollments e
            LEFT JOIN student_monitoring sm ON sm.student_id = e.student_id
            LEFT JOIN student_monitoring_plans smp ON smp.monitoring_id = sm.id AND smp.status = 'ACTIVE'
            LEFT JOIN student_monitoring_actions sma
                ON sma.monitoring_id = sm.id
               AND sma.deleted_at IS NULL
               AND sma.action_date BETWEEN :date_from AND :date_to
            LEFT JOIN student_occurrences sor
                ON sor.student_id = e.student_id
               AND sor.nature = 'RESPONSE'
               AND sor.focus_area = 'ATTENDANCE'
               AND sor.occurrence_date BETWEEN :response_from AND :response_to
            WHERE e.school_class_id = :class_id
              AND e.active = 1
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date_from' => $from,
            'date_to' => $to,
            'date_from_case' => $from,
            'date_to_case' => $to,
            'date_from_link' => $from,
            'date_to_link' => $to,
            'response_from' => $from,
            'response_to' => $to,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function attendancePointResponsesByStudent(array $studentIds, string $from, string $to): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn(int $id): bool => $id > 0)));
        if ($studentIds === []) {
            return [];
        }

        $marks = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->db->prepare("
            SELECT student_id, COUNT(*) AS point_responses, MAX(occurrence_date) AS latest_response_date
            FROM student_occurrences
            WHERE student_id IN ({$marks})
              AND nature = 'RESPONSE'
              AND focus_area = 'ATTENDANCE'
              AND occurrence_date BETWEEN ? AND ?
            GROUP BY student_id
        ");
        $stmt->execute([...$studentIds, $from, $to]);

        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[(int) $row['student_id']] = [
                'point_responses' => (int) ($row['point_responses'] ?? 0),
                'latest_response_date' => $row['latest_response_date'] ?? null,
            ];
        }
        return $result;
    }

    public function classOccurrenceTotals(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT student_occurrences.id) AS total_occurrences,
                SUM(CASE WHEN student_occurrences.severity IN ('HIGH','CRITICAL') THEN 1 ELSE 0 END) AS serious_occurrences,
                SUM(CASE WHEN student_occurrences.status = 'OPEN' THEN 1 ELSE 0 END) AS open_occurrences,
                SUM(CASE WHEN student_occurrences.status IN ('RESOLVED','CLOSED') THEN 1 ELSE 0 END) AS resolved_occurrences,
                COUNT(DISTINCT student_occurrences.student_id) AS students_with_occurrences
            FROM enrollments
            INNER JOIN student_occurrences ON student_occurrences.student_id = enrollments.student_id
            WHERE enrollments.school_class_id = :class_id
              AND enrollments.active = 1
              AND student_occurrences.occurrence_date BETWEEN :date_from AND :date_to
              AND student_occurrences.nature = 'ADVERSE'
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetch() ?: [];
    }

    public function classAttendanceRiskStudents(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration,
                school_classes.name AS class_name,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                SUM(CASE WHEN attendance_items.status IN ('FJ','AM','FO') THEN 1 ELSE 0 END) AS attenuated_absences
            FROM enrollments
            INNER JOIN students ON students.id = enrollments.student_id
            INNER JOIN school_classes ON school_classes.id = enrollments.school_class_id
            LEFT JOIN attendance ON attendance.school_class_id = school_classes.id
                AND attendance.attendance_date BETWEEN :date_from AND :date_to
            LEFT JOIN attendance_items ON attendance_items.attendance_id = attendance.id
                AND attendance_items.student_id = students.id
            WHERE enrollments.school_class_id = :class_id
              AND enrollments.active = 1
              AND students.active = 1
            GROUP BY students.id, students.name, students.registration, school_classes.name
            ORDER BY unjustified_absences DESC, students.name ASC
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetchAll();
    }

    public function classOccurrenceRiskStudents(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                students.id,
                students.name,
                students.registration,
                school_classes.name AS class_name,
                COUNT(DISTINCT student_occurrences.id) AS total_occurrences,
                SUM(CASE WHEN student_occurrences.severity IN ('HIGH','CRITICAL') THEN 1 ELSE 0 END) AS serious_occurrences,
                SUM(CASE WHEN student_occurrences.status = 'OPEN' THEN 1 ELSE 0 END) AS open_occurrences
            FROM enrollments
            INNER JOIN students ON students.id = enrollments.student_id
            INNER JOIN school_classes ON school_classes.id = enrollments.school_class_id
            LEFT JOIN student_occurrences ON student_occurrences.student_id = students.id
                AND student_occurrences.occurrence_date BETWEEN :date_from AND :date_to
                AND student_occurrences.nature = 'ADVERSE'
            WHERE enrollments.school_class_id = :class_id
              AND enrollments.active = 1
              AND students.active = 1
            GROUP BY students.id, students.name, students.registration, school_classes.name
            ORDER BY serious_occurrences DESC, total_occurrences DESC, students.name ASC
        ");
        $stmt->execute([
            'class_id' => $classId,
            'date_from' => $from,
            'date_to' => $to,
        ]);
        return $stmt->fetchAll();
    }


    public function classComparisonSignals(string $from, string $to): array
    {
        $stmt = $this->db->prepare("
            SELECT
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift,
                COUNT(DISTINCT CASE WHEN enrollments.active = 1 AND students.active = 1 THEN students.id END) AS active_students,
                COALESCE(attendance_data.total_records, 0) AS total_records,
                COALESCE(attendance_data.presences, 0) AS presences,
                COALESCE(attendance_data.unjustified_absences, 0) AS unjustified_absences,
                COALESCE(attendance_data.attenuated_absences, 0) AS attenuated_absences,
                COALESCE(attendance_data.students_affected, 0) AS attendance_students_affected,
                COALESCE(occurrence_data.total_occurrences, 0) AS total_occurrences,
                COALESCE(occurrence_data.serious_occurrences, 0) AS serious_occurrences,
                COALESCE(occurrence_data.open_occurrences, 0) AS open_occurrences,
                COALESCE(occurrence_data.students_affected, 0) AS occurrence_students_affected
            FROM school_classes
            LEFT JOIN enrollments ON enrollments.school_class_id = school_classes.id AND enrollments.active = 1
            LEFT JOIN students ON students.id = enrollments.student_id AND students.active = 1
            LEFT JOIN (
                SELECT
                    attendance.school_class_id,
                    COUNT(attendance_items.id) AS total_records,
                    SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presences,
                    SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS unjustified_absences,
                    SUM(CASE WHEN attendance_items.status IN ('FJ','AM','FO') THEN 1 ELSE 0 END) AS attenuated_absences,
                    COUNT(DISTINCT CASE WHEN attendance_items.status = 'F' THEN attendance_items.student_id END) AS students_affected
                FROM attendance
                INNER JOIN attendance_items ON attendance_items.attendance_id = attendance.id
                WHERE attendance.attendance_date BETWEEN :att_from AND :att_to
                GROUP BY attendance.school_class_id
            ) attendance_data ON attendance_data.school_class_id = school_classes.id
            LEFT JOIN (
                SELECT
                    enrollments.school_class_id,
                    COUNT(DISTINCT student_occurrences.id) AS total_occurrences,
                    COUNT(DISTINCT CASE WHEN student_occurrences.severity IN ('HIGH','CRITICAL') THEN student_occurrences.id END) AS serious_occurrences,
                    COUNT(DISTINCT CASE WHEN student_occurrences.status = 'OPEN' THEN student_occurrences.id END) AS open_occurrences,
                    COUNT(DISTINCT student_occurrences.student_id) AS students_affected
                FROM enrollments
                INNER JOIN student_occurrences ON student_occurrences.student_id = enrollments.student_id
                WHERE enrollments.active = 1
                  AND student_occurrences.occurrence_date BETWEEN :occ_from AND :occ_to
                  AND student_occurrences.nature = 'ADVERSE'
                GROUP BY enrollments.school_class_id
            ) occurrence_data ON occurrence_data.school_class_id = school_classes.id
            WHERE school_classes.active = 1
            GROUP BY school_classes.id, school_classes.name, school_classes.year, school_classes.shift,
                     attendance_data.total_records, attendance_data.presences, attendance_data.unjustified_absences,
                     attendance_data.attenuated_absences, attendance_data.students_affected,
                     occurrence_data.total_occurrences, occurrence_data.serious_occurrences,
                     occurrence_data.open_occurrences, occurrence_data.students_affected
            ORDER BY school_classes.name ASC
        ");
        $stmt->execute([
            'att_from' => $from,
            'att_to' => $to,
            'occ_from' => $from,
            'occ_to' => $to,
        ]);
        return $stmt->fetchAll();
    }

    /** Retorna todos os casos ativos, agrupados por aluno, sem multiplicar por participante. */
    public function activeMonitoringCases(array $studentIds): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn(int $id): bool => $id > 0)));
        if ($studentIds === []) return [];
        $marks = implode(',', array_fill(0, count($studentIds), '?'));
        $stmt = $this->db->prepare("SELECT sm.id AS monitoring_id, sm.student_id, sm.case_title, sm.problem_code, sm.problem_details,
                sm.reason, sm.start_date, sm.end_date, sm.status,
                COUNT(DISTINCT CASE WHEN smu.status='ACTIVE' AND smu.start_date<=CURDATE() AND smu.end_date>=CURDATE() THEN smu.user_id END) AS active_followers,
                GROUP_CONCAT(DISTINCT CASE WHEN smu.status='ACTIVE' AND smu.start_date<=CURDATE() AND smu.end_date>=CURDATE() THEN u.name END ORDER BY u.name SEPARATOR ', ') AS follower_names,
                MAX(CASE WHEN sma.deleted_at IS NULL THEN GREATEST(sma.action_date, DATE(sma.created_at), DATE(COALESCE(sma.updated_at,sma.created_at))) END) AS latest_action_date,
                COUNT(DISTINCT CASE WHEN smr.status IN ('PENDING','IN_PROGRESS') THEN smr.id END) AS pending_recommendations
            FROM student_monitoring sm
            LEFT JOIN student_monitoring_users smu ON smu.monitoring_id=sm.id
            LEFT JOIN users u ON u.id=smu.user_id
            LEFT JOIN student_monitoring_actions sma ON sma.monitoring_id=sm.id
            LEFT JOIN student_monitoring_recommendations smr ON smr.monitoring_id=sm.id
            WHERE sm.student_id IN ({$marks}) AND sm.status='ACTIVE' AND sm.start_date<=CURDATE() AND sm.end_date>=CURDATE()
            GROUP BY sm.id, sm.student_id, sm.case_title, sm.problem_code, sm.problem_details, sm.reason, sm.start_date, sm.end_date, sm.status
            ORDER BY sm.student_id, sm.id DESC");
        $stmt->execute($studentIds);
        $grouped=[];
        foreach($stmt->fetchAll() as $row){$grouped[(int)$row['student_id']][]=$row;}
        return $grouped;
    }

    public function linkedCasesForAlert(array $studentIds, string $alertKey): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn(int $id): bool => $id > 0)));
        if ($studentIds === [] || trim($alertKey)==='') return [];
        $marks=implode(',',array_fill(0,count($studentIds),'?'));
        try {
            $stmt=$this->db->prepare("SELECT l.student_id,l.monitoring_id FROM intelligence_alert_case_links l INNER JOIN student_monitoring sm ON sm.id=l.monitoring_id WHERE l.student_id IN ({$marks}) AND l.alert_key=? AND sm.status='ACTIVE' AND sm.end_date>=CURDATE() ORDER BY l.id DESC");
            $stmt->execute([...$studentIds,$alertKey]);
        } catch (\Throwable $e) { return []; }
        $result=[]; foreach($stmt->fetchAll() as $row){$result[(int)$row['student_id']] ??= (int)$row['monitoring_id'];}
        return $result;
    }

    public function linkAlertToCase(int $studentId,int $monitoringId,string $alertKey,string $alertType,?int $linkedBy): void
    {
        if($studentId<=0||$monitoringId<=0||trim($alertKey)==='') return;
        try {
            $stmt=$this->db->prepare("INSERT INTO intelligence_alert_case_links (student_id,monitoring_id,alert_key,alert_type,linked_by,linked_at,created_at,updated_at) VALUES (:student,:monitoring,:alert_key,:alert_type,:linked_by,NOW(),NOW(),NOW()) ON DUPLICATE KEY UPDATE alert_type=VALUES(alert_type),linked_by=VALUES(linked_by),linked_at=NOW(),updated_at=NOW()");
            $stmt->execute(['student'=>$studentId,'monitoring'=>$monitoringId,'alert_key'=>$alertKey,'alert_type'=>$alertType,'linked_by'=>$linkedBy]);
        } catch (\Throwable $e) { }
    }

    /**
     * Consolida respostas pedagógicas, reconhecimentos e ações de acompanhamento
     * por aluno. Os registros adversos permanecem separados e nunca são apagados.
     */
    public function positiveIndicatorsByStudents(array $studentIds, string $from, string $to): array
    {
        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds), static fn (int $id): bool => $id > 0)));
        if ($studentIds === []) return [];

        $marks = implode(',', array_fill(0, count($studentIds), '?'));
        $result = [];

        $stmt = $this->db->prepare("SELECT student_id,
                SUM(nature='POSITIVE') AS recognitions,
                SUM(nature='RESPONSE') AS responses,
                SUM(nature='RESPONSE' AND focus_area='ATTENDANCE') AS attendance_responses,
                SUM(nature='RESPONSE' AND focus_area='BEHAVIOR') AS behavior_responses,
                SUM(nature='RESPONSE' AND focus_area='LEARNING') AS learning_responses,
                SUM(nature='ADVERSE' AND status='RESOLVED') AS resolved_adverse,
                MAX(CASE WHEN nature='POSITIVE' THEN occurrence_date END) AS latest_recognition_date,
                MAX(CASE WHEN nature='RESPONSE' THEN occurrence_date END) AS latest_response_date
            FROM student_occurrences
            WHERE student_id IN ({$marks}) AND occurrence_date BETWEEN ? AND ?
            GROUP BY student_id");
        $stmt->execute([...$studentIds, $from, $to]);
        foreach ($stmt->fetchAll() as $row) $result[(int) $row['student_id']] = $row;

        $actions = $this->db->prepare("SELECT sm.student_id,
                COUNT(DISTINCT sma.id) AS monitoring_actions,
                COUNT(DISTINCT CASE WHEN sm.problem_code='FREQUENCY' OR smp.target_metric='ATTENDANCE' THEN sma.id END) AS frequency_actions,
                COUNT(DISTINCT CASE WHEN sm.problem_code='BEHAVIOR' THEN sma.id END) AS behavior_actions,
                MAX(sma.action_date) AS latest_monitoring_action_date
            FROM student_monitoring sm
            INNER JOIN student_monitoring_actions sma ON sma.monitoring_id=sm.id AND sma.deleted_at IS NULL
            LEFT JOIN student_monitoring_plans smp ON smp.monitoring_id=sm.id
            WHERE sm.student_id IN ({$marks}) AND sma.action_date BETWEEN ? AND ?
            GROUP BY sm.student_id");
        $actions->execute([...$studentIds, $from, $to]);
        foreach ($actions->fetchAll() as $row) {
            $id = (int) $row['student_id'];
            $result[$id] = array_merge($result[$id] ?? ['student_id' => $id], $row);
        }

        foreach ($studentIds as $id) {
            $result[$id] = array_merge([
                'student_id' => $id, 'recognitions' => 0, 'responses' => 0,
                'attendance_responses' => 0, 'behavior_responses' => 0,
                'learning_responses' => 0, 'resolved_adverse' => 0,
                'monitoring_actions' => 0, 'frequency_actions' => 0,
                'behavior_actions' => 0, 'latest_recognition_date' => null,
                'latest_response_date' => null, 'latest_monitoring_action_date' => null,
            ], $result[$id] ?? []);
        }
        return $result;
    }

    public function positiveStudentTimeline(int $studentId, string $from, string $to, int $limit = 8): array
    {
        $limit = max(1, min(30, $limit));
        $stmt = $this->db->prepare("SELECT positive_events.* FROM (
                SELECT so.id, so.occurrence_date AS event_date, so.nature, so.focus_area,
                       so.title, so.description, so.created_at, 'INTERVENTION' AS source_type
                FROM student_occurrences so
                WHERE so.student_id=:occurrence_student_id
                  AND so.nature IN ('RESPONSE','POSITIVE')
                  AND so.occurrence_date BETWEEN :occurrence_from AND :occurrence_to
                UNION ALL
                SELECT sma.id, sma.action_date AS event_date, 'MONITORING_ACTION' AS nature,
                       CASE
                           WHEN sm.problem_code='FREQUENCY' OR smp.target_metric='ATTENDANCE' THEN 'ATTENDANCE'
                           WHEN sm.problem_code='BEHAVIOR' THEN 'BEHAVIOR'
                           WHEN sm.problem_code='LEARNING' THEN 'LEARNING'
                           ELSE sm.problem_code
                       END AS focus_area,
                       sma.action_type_label AS title, sma.description, sma.created_at,
                       'MONITORING_ACTION' AS source_type
                FROM student_monitoring sm
                INNER JOIN student_monitoring_actions sma
                    ON sma.monitoring_id=sm.id AND sma.deleted_at IS NULL
                LEFT JOIN student_monitoring_plans smp ON smp.monitoring_id=sm.id
                WHERE sm.student_id=:monitoring_student_id
                  AND sma.action_date BETWEEN :monitoring_from AND :monitoring_to
            ) positive_events
            ORDER BY event_date DESC, created_at DESC, id DESC LIMIT {$limit}");
        $stmt->execute([
            'occurrence_student_id' => $studentId,
            'occurrence_from' => $from,
            'occurrence_to' => $to,
            'monitoring_student_id' => $studentId,
            'monitoring_from' => $from,
            'monitoring_to' => $to,
        ]);
        return $stmt->fetchAll();
    }

    public function classPositiveIndicators(int $classId, string $from, string $to): array
    {
        $stmt = $this->db->prepare("SELECT
                COUNT(DISTINCT CASE WHEN so.nature='POSITIVE' THEN e.student_id END) AS students_recognized,
                COUNT(DISTINCT CASE WHEN so.nature='RESPONSE' THEN e.student_id END) AS students_with_response,
                COUNT(DISTINCT CASE WHEN so.nature IN ('POSITIVE','RESPONSE') THEN e.student_id END) AS students_with_positive_evidence,
                SUM(so.nature='POSITIVE') AS recognitions,
                SUM(so.nature='RESPONSE') AS responses,
                SUM(so.nature='RESPONSE' AND so.focus_area='ATTENDANCE') AS attendance_responses,
                SUM(so.nature='ADVERSE' AND so.status='RESOLVED') AS resolved_adverse
            FROM enrollments e
            LEFT JOIN student_occurrences so ON so.student_id=e.student_id AND so.occurrence_date BETWEEN :date_from AND :date_to
            WHERE e.school_class_id=:class_id AND e.active=1");
        $stmt->execute(['class_id' => $classId, 'date_from' => $from, 'date_to' => $to]);
        return $stmt->fetch() ?: [];
    }

    public function schoolPositiveIndicators(string $from, string $to): array
    {
        $stmt = $this->db->prepare("SELECT
                COUNT(DISTINCT CASE WHEN nature='POSITIVE' THEN student_id END) AS students_recognized,
                COUNT(DISTINCT CASE WHEN nature='RESPONSE' THEN student_id END) AS students_with_response,
                SUM(nature='POSITIVE') AS recognitions,
                SUM(nature='RESPONSE') AS responses,
                SUM(nature='RESPONSE' AND focus_area='ATTENDANCE') AS attendance_responses,
                SUM(nature='ADVERSE' AND status='RESOLVED') AS resolved_adverse
            FROM student_occurrences WHERE occurrence_date BETWEEN :date_from AND :date_to");
        $stmt->execute(['date_from' => $from, 'date_to' => $to]);
        return $stmt->fetch() ?: [];
    }

    public function positiveStudentCases(string $nature, string $from, string $to): array
    {
        $nature = $nature === 'POSITIVE' ? 'POSITIVE' : 'RESPONSE';
        $stmt = $this->db->prepare("SELECT s.id, s.name, s.registration,
                COALESCE(sc.name,'Sem turma') AS class_name,
                COUNT(so.id) AS positive_records,
                MAX(so.occurrence_date) AS latest_positive_date,
                GROUP_CONCAT(DISTINCT so.focus_area ORDER BY so.focus_area SEPARATOR ',') AS focus_areas
            FROM students s
            INNER JOIN student_occurrences so ON so.student_id=s.id AND so.nature=:nature AND so.occurrence_date BETWEEN :date_from AND :date_to
            LEFT JOIN enrollments e ON e.id=(SELECT e2.id FROM enrollments e2 WHERE e2.student_id=s.id AND e2.active=1 ORDER BY e2.id DESC LIMIT 1)
            LEFT JOIN school_classes sc ON sc.id=e.school_class_id
            WHERE s.active=1
            GROUP BY s.id,s.name,s.registration,sc.name
            ORDER BY positive_records DESC, latest_positive_date DESC, s.name");
        $stmt->execute(['nature' => $nature, 'date_from' => $from, 'date_to' => $to]);
        return $stmt->fetchAll();
    }

}
