<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;

class AttendanceAnalyticsService
{
    public function dailyCentral(string $date): array
    {
        $classes = $this->dailyRanking($date);

        $totalClasses = count($classes);
        $doneClasses = 0;
        $pendingClasses = 0;

        foreach ($classes as $class) {
            if ((int) ($class['has_attendance'] ?? 0) === 1) {
                $doneClasses++;
            } else {
                $pendingClasses++;
            }
        }

        $summary = $this->schoolFrequencyToday($date);

        return [
            'totalClasses' => $totalClasses,
            'doneClasses' => $doneClasses,
            'pendingClasses' => $pendingClasses,
            'generalPercentage' => (float) ($summary['percentage'] ?? 0),
            'summary' => $summary,
            'classes' => $classes,
        ];
    }

    public function dailyGeneralPercentage(string $date): float
    {
        $summary = $this->schoolFrequencyToday($date);

        return (float) ($summary['percentage'] ?? 0);
    }

    public function dailyRanking(string $date): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                school_classes.id AS class_id,
                school_classes.name AS class_name,
                school_classes.year,
                school_classes.shift,
                school_classes.photo_path AS class_photo_path,
                school_classes.photo_updated_at AS class_photo_updated_at,
                COUNT(attendance_items.id) AS total_students,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS ranking_absences,
                SUM(CASE WHEN attendance_items.status IN ('FJ', 'AM', 'FO') THEN 1 ELSE 0 END) AS attenuated_absences,
                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,
                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,
                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,
                SUM(CASE WHEN attendance_items.status <> 'P' THEN 1 ELSE 0 END) AS raw_absences,

                CASE
                    WHEN COUNT(attendance_items.id) = 0
                    THEN NULL
                    ELSE ROUND(
                        (
                            SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                            / COUNT(attendance_items.id)
                        ) * 100,
                        1
                    )
                END AS attendance_percentage,

                CASE
                    WHEN COUNT(attendance_items.id) = 0
                    THEN NULL
                    ELSE ROUND(
                        100 -
                        (
                            (
                                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END)
                                / COUNT(attendance_items.id)
                            ) * 100
                        ),
                        2
                    )
                END AS ife_score,

                CASE
                    WHEN attendance.id IS NULL
                    THEN 0
                    ELSE 1
                END AS has_attendance,

                attendance.id AS attendance_id,
                attendance.created_at AS attendance_created_at

            FROM school_classes

            LEFT JOIN attendance
                ON attendance.school_class_id = school_classes.id
               AND attendance.attendance_date = :date

            LEFT JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id

            WHERE school_classes.active = 1

            GROUP BY
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift,
                school_classes.photo_path,
                school_classes.photo_updated_at,
                attendance.id,
                attendance.created_at

            ORDER BY
                has_attendance DESC,
                ife_score DESC,
                attendance_percentage DESC,
                raw_absences ASC,
                school_classes.name ASC
        ");

        $stmt->execute([
            'date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    public function schoolFrequencyToday(string $date): array
    {
        return $this->frequencyByPeriod($date, $date);
    }

    public function schoolFrequencyWeek(): array
    {
        return $this->frequencyBySqlCondition("
            YEARWEEK(attendance.attendance_date, 1) = YEARWEEK(CURDATE(), 1)
        ");
    }

    public function schoolFrequencyMonth(): array
    {
        return $this->frequencyBySqlCondition("
            YEAR(attendance.attendance_date) = YEAR(CURDATE())
            AND MONTH(attendance.attendance_date) = MONTH(CURDATE())
        ");
    }

    public function schoolFrequencyYear(): array
    {
        return $this->frequencyBySqlCondition("
            YEAR(attendance.attendance_date) = YEAR(CURDATE())
        ");
    }

    public function schoolFrequencyByPreset(string $preset): array
    {
        $preset = strtolower(trim($preset));
        $ranges = [
            '7d' => 7,
            '15d' => 15,
            '30d' => 30,
            '60d' => 60,
            '90d' => 90,
        ];

        if (isset($ranges[$preset])) {
            return $this->schoolFrequencyByRange(
                date('Y-m-d', strtotime('-' . ($ranges[$preset] - 1) . ' days')),
                date('Y-m-d')
            );
        }

        return match ($preset) {
            'month' => $this->schoolFrequencyByRange(date('Y-m-01'), date('Y-m-d')),
            'previous_month' => $this->schoolFrequencyByRange(
                date('Y-m-01', strtotime('first day of previous month')),
                date('Y-m-t', strtotime('last day of previous month'))
            ),
            'semester' => $this->schoolFrequencyByRange(
                date('Y') . (date('n') <= 6 ? '-01-01' : '-07-01'),
                date('Y-m-d')
            ),
            'year' => $this->schoolFrequencyByRange(date('Y-01-01'), date('Y-m-d')),
            default => $this->schoolFrequencyByRange(date('Y-m-d', strtotime('-29 days')), date('Y-m-d')),
        };
    }

    public function schoolFrequencyByRange(string $startDate, string $endDate): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare("
            SELECT attendance.attendance_date,
                   ROUND((SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) / COUNT(attendance_items.id)) * 100, 1) AS percentage
            FROM attendance
            INNER JOIN attendance_items ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date BETWEEN :start_date AND :end_date
            GROUP BY attendance.attendance_date
            ORDER BY attendance.attendance_date ASC
        ");
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        return $stmt->fetchAll();
    }

    public function schoolFrequencyHeatmapByPreset(string $preset = 'month'): array
    {
        $preset = strtolower(trim($preset));
        $days = match ($preset) {
            'week' => 7,
            'month' => 30,
            'bimester' => 60,
            'semester' => 180,
            default => 30,
        };

        $data = $this->schoolFrequencyHeatmap($days);
        $labels = [
            'week' => 'Última semana',
            'month' => 'Último mês',
            'bimester' => 'Último bimestre',
            'semester' => 'Último semestre',
        ];
        $data['period'] = array_key_exists($preset, $labels) ? $preset : 'month';
        $data['period_label'] = $labels[$data['period']];
        return $data;
    }

    public function schoolFrequencyHeatmap(int $days = 84): array
    {
        $days = max(28, min(366, $days));
        $startDate = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $endDate = date('Y-m-d');
        $db = Connection::getInstance();
        $stmt = $db->prepare("
            SELECT attendance.attendance_date,
                   COUNT(attendance_items.id) AS total_records,
                   SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presents,
                   ROUND((SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) / NULLIF(COUNT(attendance_items.id), 0)) * 100, 1) AS percentage
            FROM attendance
            INNER JOIN attendance_items ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date BETWEEN :start_date AND :end_date
            GROUP BY attendance.attendance_date
            ORDER BY attendance.attendance_date ASC
        ");
        $stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $indexed = [];
        foreach ($stmt->fetchAll() ?: [] as $row) {
            $indexed[(string) $row['attendance_date']] = $row;
        }
        $items = [];
        $cursor = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);
        while ($cursor <= $end) {
            $date = $cursor->format('Y-m-d');
            $row = $indexed[$date] ?? [];
            $items[] = [
                'date' => $date,
                'weekday' => (int) $cursor->format('N'),
                'percentage' => isset($row['percentage']) ? (float) $row['percentage'] : null,
                'total_records' => (int) ($row['total_records'] ?? 0),
                'presents' => (int) ($row['presents'] ?? 0),
            ];
            $cursor = $cursor->modify('+1 day');
        }
        return ['days' => $days, 'start_date' => $startDate, 'end_date' => $endDate, 'items' => $items];
    }

    public function schoolFrequencyLast30Days(): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                attendance.attendance_date,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY attendance.attendance_date
            ORDER BY attendance.attendance_date ASC
        ");

        return $stmt->fetchAll();
    }

    public function classesWithoutAttendanceToday(string $date): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                school_classes.id,
                school_classes.name,
                school_classes.year,
                school_classes.shift
            FROM school_classes
            WHERE school_classes.active = 1
              AND school_classes.id NOT IN (
                  SELECT attendance.school_class_id
                  FROM attendance
                  WHERE attendance.attendance_date = :date
              )
            ORDER BY
                school_classes.year DESC,
                school_classes.name ASC
        ");

        $stmt->execute([
            'date' => $date,
        ]);

        return $stmt->fetchAll();
    }

    private function frequencyByPeriod(string $startDate, string $endDate): array
    {
        $db = Connection::getInstance();

        $stmt = $db->prepare("
            SELECT
                COUNT(attendance_items.id) AS total_students,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,
                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,
                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE attendance.attendance_date BETWEEN :start_date AND :end_date
        ");

        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        return $this->normalizeFrequencyResult($stmt->fetch());
    }

    private function frequencyBySqlCondition(string $condition): array
    {
        $db = Connection::getInstance();

        $stmt = $db->query("
            SELECT
                COUNT(attendance_items.id) AS total_students,
                SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END) AS presentes,
                SUM(CASE WHEN attendance_items.status = 'F' THEN 1 ELSE 0 END) AS faltas,
                SUM(CASE WHEN attendance_items.status = 'FJ' THEN 1 ELSE 0 END) AS justificadas,
                SUM(CASE WHEN attendance_items.status = 'AM' THEN 1 ELSE 0 END) AS atestados,
                SUM(CASE WHEN attendance_items.status = 'FO' THEN 1 ELSE 0 END) AS onibus,
                ROUND(
                    (
                        SUM(CASE WHEN attendance_items.status = 'P' THEN 1 ELSE 0 END)
                        / COUNT(attendance_items.id)
                    ) * 100,
                    1
                ) AS percentage
            FROM attendance
            INNER JOIN attendance_items
                ON attendance_items.attendance_id = attendance.id
            WHERE {$condition}
        ");

        return $this->normalizeFrequencyResult($stmt->fetch());
    }

    private function normalizeFrequencyResult(array|false $data): array
    {
        if (!$data || (int) ($data['total_students'] ?? 0) === 0) {
            return [
                'total_students' => 0,
                'presentes' => 0,
                'faltas' => 0,
                'justificadas' => 0,
                'atestados' => 0,
                'onibus' => 0,
                'percentage' => 0,
            ];
        }

        return $data;
    }
}