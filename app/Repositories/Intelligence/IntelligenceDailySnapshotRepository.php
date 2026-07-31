<?php

declare(strict_types=1);

namespace App\Repositories\Intelligence;

use App\Repositories\BaseRepository;
use PDO;

final class IntelligenceDailySnapshotRepository extends BaseRepository
{
    public function activeStudents(): array
    {
        $sql = "
            SELECT
                s.id AS student_id,
                sc.id AS class_id,
                sc.name AS class_name
            FROM students s
            LEFT JOIN enrollments e
              ON e.id = (
                    SELECT e2.id
                    FROM enrollments e2
                    WHERE e2.student_id = s.id
                      AND e2.active = 1
                    ORDER BY e2.id DESC
                    LIMIT 1
              )
            LEFT JOIN school_classes sc ON sc.id = e.school_class_id
            WHERE s.active = 1
            ORDER BY s.id ASC
        ";

        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function hasSchoolSnapshot(string $date): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM school_intelligence_daily_snapshots WHERE snapshot_date = :snapshot_date LIMIT 1');
        $stmt->execute(['snapshot_date' => $date]);
        return (bool) $stmt->fetchColumn();
    }

    public function saveStudent(array $data): void
    {
        $sql = "INSERT INTO student_intelligence_daily_snapshots (
                    snapshot_date, student_id, class_id, class_name, analysis_window_days,
                    risk_level, risk_score, attendance_records, presences, attendance_percentage,
                    unjustified_absences, attenuated_absences, total_occurrences, serious_occurrences,
                    open_occurrences, has_active_monitoring, active_followers, pending_recommendations,
                    latest_action_date, days_without_action, created_at, updated_at
                ) VALUES (
                    :snapshot_date, :student_id, :class_id, :class_name, :analysis_window_days,
                    :risk_level, :risk_score, :attendance_records, :presences, :attendance_percentage,
                    :unjustified_absences, :attenuated_absences, :total_occurrences, :serious_occurrences,
                    :open_occurrences, :has_active_monitoring, :active_followers, :pending_recommendations,
                    :latest_action_date, :days_without_action, NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    class_id = VALUES(class_id),
                    class_name = VALUES(class_name),
                    analysis_window_days = VALUES(analysis_window_days),
                    risk_level = VALUES(risk_level),
                    risk_score = VALUES(risk_score),
                    attendance_records = VALUES(attendance_records),
                    presences = VALUES(presences),
                    attendance_percentage = VALUES(attendance_percentage),
                    unjustified_absences = VALUES(unjustified_absences),
                    attenuated_absences = VALUES(attenuated_absences),
                    total_occurrences = VALUES(total_occurrences),
                    serious_occurrences = VALUES(serious_occurrences),
                    open_occurrences = VALUES(open_occurrences),
                    has_active_monitoring = VALUES(has_active_monitoring),
                    active_followers = VALUES(active_followers),
                    pending_recommendations = VALUES(pending_recommendations),
                    latest_action_date = VALUES(latest_action_date),
                    days_without_action = VALUES(days_without_action),
                    updated_at = NOW()";

        $this->db->prepare($sql)->execute($data);
    }

    public function saveSchool(array $data): void
    {
        $columns = [
            'snapshot_date', 'analysis_window_days', 'active_students', 'priority_students',
            'critical_students', 'high_risk_students', 'moderate_risk_students', 'attention_classes',
            'combined_risk_students', 'stale_critical_occurrences', 'unjustified_absences',
            'students_with_unjustified_absence', 'total_occurrences', 'serious_occurrences',
            'open_occurrences', 'monitoring_coverage_percentage', 'covered_cases', 'without_follower',
            'without_recent_action', 'pending_recommendations'
        ];
        $insertColumns = implode(', ', $columns);
        $placeholders = implode(', ', array_map(static fn(string $column): string => ':' . $column, $columns));
        $updates = implode(', ', array_map(static fn(string $column): string => $column . '=VALUES(' . $column . ')', array_slice($columns, 1)));

        $sql = "INSERT INTO school_intelligence_daily_snapshots ({$insertColumns}, created_at, updated_at)
                VALUES ({$placeholders}, NOW(), NOW())
                ON DUPLICATE KEY UPDATE {$updates}, updated_at=NOW()";
        $this->db->prepare($sql)->execute($data);
    }

    public function recentStudentSnapshots(int $studentId, int $limit = 90): array
    {
        $limit = max(1, min(365, $limit));
        $stmt = $this->db->prepare("SELECT * FROM student_intelligence_daily_snapshots WHERE student_id=:student_id ORDER BY snapshot_date DESC LIMIT {$limit}");
        $stmt->execute(['student_id' => $studentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recentClassSnapshots(int $classId, int $days = 90): array
    {
        $days = max(2, min(365, $days));
        $stmt = $this->db->prepare("
            SELECT
                snapshot_date,
                class_id,
                MAX(class_name) AS class_name,
                COUNT(*) AS active_students,
                ROUND(AVG(attendance_percentage), 2) AS attendance_percentage,
                SUM(unjustified_absences) AS unjustified_absences,
                SUM(total_occurrences) AS total_occurrences,
                SUM(serious_occurrences) AS serious_occurrences,
                SUM(open_occurrences) AS open_occurrences,
                ROUND(AVG(risk_score), 2) AS average_risk_score,
                SUM(CASE WHEN risk_level IN ('MODERATE','HIGH','CRITICAL') THEN 1 ELSE 0 END) AS priority_students,
                SUM(CASE WHEN risk_level = 'CRITICAL' THEN 1 ELSE 0 END) AS critical_students,
                SUM(CASE WHEN has_active_monitoring = 1 AND active_followers > 0 THEN 1 ELSE 0 END) AS covered_students,
                SUM(pending_recommendations) AS pending_recommendations
            FROM student_intelligence_daily_snapshots
            WHERE class_id = :class_id
              AND snapshot_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
            GROUP BY snapshot_date, class_id
            ORDER BY snapshot_date ASC
        ");
        $stmt->execute(['class_id' => $classId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function recentClassesSnapshots(array $classIds, int $days = 90): array
    {
        $result = [];
        foreach (array_unique(array_map('intval', $classIds)) as $classId) {
            if ($classId > 0) {
                $result[$classId] = $this->recentClassSnapshots($classId, $days);
            }
        }
        return $result;
    }

    public function recentSchoolSnapshots(int $limit = 90): array
    {
        $limit = max(1, min(365, $limit));
        return $this->db->query("SELECT * FROM school_intelligence_daily_snapshots ORDER BY snapshot_date DESC LIMIT {$limit}")->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
