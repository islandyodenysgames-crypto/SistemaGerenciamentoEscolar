<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceCalendarQueries
{
    /**
     * Resumo mensal para o calendário
     * do painel de ocorrências.
     */
    public function calendarMonth(
        int $year,
        int $month
    ): array {
        $startDate = sprintf(
            '%04d-%02d-01',
            $year,
            $month
        );

        $endDate = date(
            'Y-m-t',
            strtotime($startDate)
        );

        $stmt = $this->db->prepare("
            SELECT
                occurrence_date,

                COUNT(*) AS total,

                SUM(
                    CASE
                        WHEN status = 'OPEN'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_open,

                SUM(
                    CASE
                        WHEN status = 'RESOLVED'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_resolved,

                SUM(
                    CASE
                        WHEN severity = 'LOW'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_low,

                SUM(
                    CASE
                        WHEN severity = 'MEDIUM'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_medium,

                SUM(
                    CASE
                        WHEN severity = 'HIGH'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_high,

                SUM(
                    CASE
                        WHEN severity = 'CRITICAL'
                        THEN 1
                        ELSE 0
                    END
                ) AS total_critical

            FROM student_occurrences

            WHERE occurrence_date
                BETWEEN :start_date AND :end_date

            GROUP BY occurrence_date

            ORDER BY occurrence_date ASC
        ");

        $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $calendar = [];

        foreach ($stmt->fetchAll() as $row) {
            $date = (string) (
                $row['occurrence_date'] ?? ''
            );

            if ($date === '') {
                continue;
            }

            $calendar[$date] = [
                'total' => (int) (
                    $row['total'] ?? 0
                ),

                'open' => (int) (
                    $row['total_open'] ?? 0
                ),

                'resolved' => (int) (
                    $row['total_resolved'] ?? 0
                ),

                'low' => (int) (
                    $row['total_low'] ?? 0
                ),

                'medium' => (int) (
                    $row['total_medium'] ?? 0
                ),

                'high' => (int) (
                    $row['total_high'] ?? 0
                ),

                'critical' => (int) (
                    $row['total_critical'] ?? 0
                ),
            ];
        }

        return $calendar;
    }
}