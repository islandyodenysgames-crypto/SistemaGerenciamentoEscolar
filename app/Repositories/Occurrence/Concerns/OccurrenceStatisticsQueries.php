<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence\Concerns;

trait OccurrenceStatisticsQueries
{
    /**
     * Total de ocorrências registradas hoje.
     */
    public function countToday(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE occurrence_date = CURDATE()
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Total geral de ocorrências abertas.
     */
    public function countOpen(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE status = 'OPEN'
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Total geral de ocorrências resolvidas.
     */
    public function countResolved(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE status = 'RESOLVED'
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Total de ocorrências do mês atual.
     */
    public function countCurrentMonth(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE YEAR(occurrence_date) =
                YEAR(CURDATE())

              AND MONTH(occurrence_date) =
                MONTH(CURDATE())
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Contagem geral por gravidade.
     */
    public function countBySeverity(): array
    {
        $stmt = $this->db->query("
            SELECT
                severity,
                COUNT(*) AS total

            FROM student_occurrences

            GROUP BY severity
        ");

        return $this->normalizeSeverityCounts(
            $stmt->fetchAll()
        );
    }

    /**
     * Contagem por gravidade no mês atual.
     */
    public function countBySeverityCurrentMonth(): array
    {
        $stmt = $this->db->query("
            SELECT
                severity,
                COUNT(*) AS total

            FROM student_occurrences

            WHERE YEAR(occurrence_date) =
                YEAR(CURDATE())

              AND MONTH(occurrence_date) =
                MONTH(CURDATE())

            GROUP BY severity
        ");

        return $this->normalizeSeverityCounts(
            $stmt->fetchAll()
        );
    }

    /**
     * Quantidade de ocorrências gravíssimas abertas.
     */
    public function countCriticalOpen(): int
    {
        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM student_occurrences

            WHERE severity = 'CRITICAL'
              AND status = 'OPEN'
        ");

        return (int) $stmt->fetchColumn();
    }

    /**
     * Tipos de ocorrência mais frequentes.
     */
    public function mostFrequentTypes(
        int $limit = 6
    ): array {
        $limit = max(
            1,
            min(20, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                type,
                COUNT(*) AS total

            FROM student_occurrences

            GROUP BY type

            ORDER BY
                total DESC,
                type ASC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Gravidades mais frequentes.
     */
    public function mostFrequentSeverities(
        int $limit = 4
    ): array {
        $limit = max(
            1,
            min(4, $limit)
        );

        $stmt = $this->db->query("
            SELECT
                severity,
                COUNT(*) AS total

            FROM student_occurrences

            GROUP BY severity

            ORDER BY
                CASE severity
                    WHEN 'CRITICAL' THEN 4
                    WHEN 'HIGH' THEN 3
                    WHEN 'MEDIUM' THEN 2
                    ELSE 1
                END DESC

            LIMIT {$limit}
        ");

        return $stmt->fetchAll();
    }

    /**
     * Estrutura padrão das contagens por gravidade.
     */
    private function emptySeverityCounts(): array
    {
        return [
            'LOW' => 0,
            'MEDIUM' => 0,
            'HIGH' => 0,
            'CRITICAL' => 0,
        ];
    }

    /**
     * Normaliza o resultado de consultas agrupadas
     * por gravidade.
     */
    private function normalizeSeverityCounts(
        array $rows
    ): array {
        $counts = $this->emptySeverityCounts();

        foreach ($rows as $row) {
            $severity = strtoupper(
                trim(
                    (string) (
                        $row['severity'] ?? 'LOW'
                    )
                )
            );

            if (
                !array_key_exists(
                    $severity,
                    $counts
                )
            ) {
                continue;
            }

            $counts[$severity] = (int) (
                $row['total'] ?? 0
            );
        }

        return $counts;
    }


    /**
     * Dados agregados usados pelos indicadores inteligentes
     * da dashboard de ocorrências.
     */
    public function dashboardIndicators(): array
    {
        $stmt = $this->db->query("
            SELECT
                SUM(
                    CASE
                        WHEN YEAR(occurrence_date) = YEAR(CURDATE())
                         AND MONTH(occurrence_date) = MONTH(CURDATE())
                        THEN 1 ELSE 0
                    END
                ) AS current_month_total,

                SUM(
                    CASE
                        WHEN occurrence_date >= DATE_FORMAT(
                            DATE_SUB(CURDATE(), INTERVAL 1 MONTH),
                            '%Y-%m-01'
                        )
                         AND occurrence_date < DATE_FORMAT(
                            CURDATE(),
                            '%Y-%m-01'
                        )
                        THEN 1 ELSE 0
                    END
                ) AS previous_month_total,

                SUM(
                    CASE
                        WHEN YEAR(occurrence_date) = YEAR(CURDATE())
                         AND MONTH(occurrence_date) = MONTH(CURDATE())
                         AND status = 'RESOLVED'
                        THEN 1 ELSE 0
                    END
                ) AS current_month_resolved,

                SUM(
                    CASE
                        WHEN YEAR(occurrence_date) = YEAR(CURDATE())
                         AND MONTH(occurrence_date) = MONTH(CURDATE())
                         AND severity IN ('HIGH', 'CRITICAL')
                        THEN 1 ELSE 0
                    END
                ) AS high_critical_current_month,

                SUM(
                    CASE
                        WHEN severity = 'CRITICAL'
                         AND status = 'OPEN'
                        THEN 1 ELSE 0
                    END
                ) AS critical_open

            FROM student_occurrences
        ");

        $row = $stmt->fetch() ?: [];

        return [
            'current_month_total' => (int) ($row['current_month_total'] ?? 0),
            'previous_month_total' => (int) ($row['previous_month_total'] ?? 0),
            'current_month_resolved' => (int) ($row['current_month_resolved'] ?? 0),
            'high_critical_current_month' => (int) ($row['high_critical_current_month'] ?? 0),
            'critical_open' => (int) ($row['critical_open'] ?? 0),
        ];
    }

    /**
     * Quantidade de alunos que atingiram o limite de
     * ocorrências dentro dos últimos 30 dias.
     */
    public function countRecurrentStudentsLast30Days(
        int $threshold = 3
    ): int {
        $threshold = max(2, min(20, $threshold));

        $stmt = $this->db->query("
            SELECT COUNT(*)

            FROM (
                SELECT student_id

                FROM student_occurrences

                WHERE occurrence_date >= DATE_SUB(
                    CURDATE(),
                    INTERVAL 29 DAY
                )

                GROUP BY student_id

                HAVING COUNT(*) >= {$threshold}
            ) AS recurrent_students
        ");

        return (int) $stmt->fetchColumn();
    }

}