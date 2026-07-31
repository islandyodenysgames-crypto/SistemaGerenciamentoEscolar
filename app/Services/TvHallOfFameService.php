<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use PDO;

/**
 * Produz os reconhecimentos históricos do Painel TV sem depender do ranking do dia.
 */
final class TvHallOfFameService
{
    public function build(?string $referenceDate = null): array
    {
        $referenceDate = $this->validDate($referenceDate) ? (string)$referenceDate : date('Y-m-d');

        $weekStart = date('Y-m-d', strtotime('monday this week', strtotime($referenceDate)));
        $monthStart = date('Y-m-01', strtotime($referenceDate));
        $previousWeekStart = date('Y-m-d', strtotime('-7 days', strtotime($weekStart)));
        $previousWeekEnd = date('Y-m-d', strtotime('-1 day', strtotime($weekStart)));

        return [
            'generated_at' => date(DATE_ATOM),
            'reference_date' => $referenceDate,
            'day' => $this->bestInPeriod($referenceDate, $referenceDate),
            'week' => $this->bestInPeriod($weekStart, $referenceDate),
            'evolution' => $this->bestEvolution($previousWeekStart, $previousWeekEnd, $weekStart, $referenceDate),
            'month' => $this->bestInPeriod($monthStart, $referenceDate),
        ];
    }

    private function bestInPeriod(string $startDate, string $endDate): ?array
    {
        $sql = "
            SELECT
                sc.id AS class_id,
                sc.name AS class_name,
                sc.year,
                sc.shift,
                sc.photo_path AS class_photo_path,
                sc.photo_updated_at AS class_photo_updated_at,
                COUNT(DISTINCT a.attendance_date) AS attendance_days,
                COUNT(ai.id) AS total_records,
                SUM(CASE WHEN ai.status = 'P' THEN 1 ELSE 0 END) AS present_records,
                ROUND(
                    100 * SUM(CASE WHEN ai.status = 'P' THEN 1 ELSE 0 END)
                    / NULLIF(COUNT(ai.id), 0),
                    1
                ) AS attendance_percentage
            FROM school_classes sc
            INNER JOIN attendance a
                ON a.school_class_id = sc.id
               AND a.attendance_date BETWEEN :start_date AND :end_date
            INNER JOIN attendance_items ai
                ON ai.attendance_id = a.id
            WHERE sc.active = 1
            GROUP BY sc.id, sc.name, sc.year, sc.shift, sc.photo_path, sc.photo_updated_at
            HAVING COUNT(ai.id) > 0
            ORDER BY attendance_percentage DESC,
                     present_records DESC,
                     total_records DESC,
                     sc.name ASC
            LIMIT 1
        ";

        $statement = Connection::getInstance()->prepare($sql);
        $statement->execute(['start_date' => $startDate, 'end_date' => $endDate]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['period_start'] = $startDate;
        $row['period_end'] = $endDate;
        $row['attendance_percentage'] = (float)($row['attendance_percentage'] ?? 0);
        $row['attendance_days'] = (int)($row['attendance_days'] ?? 0);
        $row['total_records'] = (int)($row['total_records'] ?? 0);
        $row['present_records'] = (int)($row['present_records'] ?? 0);

        return $row;
    }

    private function bestEvolution(
        string $previousStart,
        string $previousEnd,
        string $currentStart,
        string $currentEnd
    ): ?array {
        $sql = "
            SELECT
                sc.id AS class_id,
                sc.name AS class_name,
                sc.year,
                sc.shift,
                sc.photo_path AS class_photo_path,
                sc.photo_updated_at AS class_photo_updated_at,
                previous_period.percentage AS previous_percentage,
                current_period.percentage AS current_percentage,
                ROUND(current_period.percentage - previous_period.percentage, 1) AS evolution_percentage,
                previous_period.attendance_days AS previous_days,
                current_period.attendance_days AS current_days
            FROM school_classes sc
            INNER JOIN (
                SELECT
                    a.school_class_id,
                    COUNT(DISTINCT a.attendance_date) AS attendance_days,
                    100 * SUM(CASE WHEN ai.status = 'P' THEN 1 ELSE 0 END)
                        / NULLIF(COUNT(ai.id), 0) AS percentage
                FROM attendance a
                INNER JOIN attendance_items ai ON ai.attendance_id = a.id
                WHERE a.attendance_date BETWEEN :previous_start AND :previous_end
                GROUP BY a.school_class_id
            ) previous_period ON previous_period.school_class_id = sc.id
            INNER JOIN (
                SELECT
                    a.school_class_id,
                    COUNT(DISTINCT a.attendance_date) AS attendance_days,
                    100 * SUM(CASE WHEN ai.status = 'P' THEN 1 ELSE 0 END)
                        / NULLIF(COUNT(ai.id), 0) AS percentage
                FROM attendance a
                INNER JOIN attendance_items ai ON ai.attendance_id = a.id
                WHERE a.attendance_date BETWEEN :current_start AND :current_end
                GROUP BY a.school_class_id
            ) current_period ON current_period.school_class_id = sc.id
            WHERE sc.active = 1
              AND current_period.percentage > previous_period.percentage
            ORDER BY evolution_percentage DESC,
                     current_period.percentage DESC,
                     sc.name ASC
            LIMIT 1
        ";

        $statement = Connection::getInstance()->prepare($sql);
        $statement->execute([
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'current_start' => $currentStart,
            'current_end' => $currentEnd,
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        $row['previous_percentage'] = round((float)($row['previous_percentage'] ?? 0), 1);
        $row['current_percentage'] = round((float)($row['current_percentage'] ?? 0), 1);
        $row['evolution_percentage'] = round((float)($row['evolution_percentage'] ?? 0), 1);
        $row['previous_days'] = (int)($row['previous_days'] ?? 0);
        $row['current_days'] = (int)($row['current_days'] ?? 0);
        $row['previous_period_start'] = $previousStart;
        $row['previous_period_end'] = $previousEnd;
        $row['current_period_start'] = $currentStart;
        $row['current_period_end'] = $currentEnd;

        return $row;
    }

    private function validDate(?string $date): bool
    {
        if ($date === null || $date === '') {
            return false;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
