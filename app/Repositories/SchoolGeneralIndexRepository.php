<?php

declare(strict_types=1);

namespace App\Repositories;

use PDOException;

final class SchoolGeneralIndexRepository extends BaseRepository
{
    public function save(array $data): void
    {
        $sql = "INSERT INTO school_general_index_snapshots (
                    snapshot_date, score, frequency_score, occurrence_score,
                    risk_score, monitoring_score, classes_score, details_json,
                    created_at, updated_at
                ) VALUES (
                    :snapshot_date, :score, :frequency_score, :occurrence_score,
                    :risk_score, :monitoring_score, :classes_score, :details_json,
                    NOW(), NOW()
                ) ON DUPLICATE KEY UPDATE
                    score=VALUES(score), frequency_score=VALUES(frequency_score),
                    occurrence_score=VALUES(occurrence_score), risk_score=VALUES(risk_score),
                    monitoring_score=VALUES(monitoring_score), classes_score=VALUES(classes_score),
                    details_json=VALUES(details_json), updated_at=NOW()";
        $this->db->prepare($sql)->execute($data);
    }

    public function recent(int $days = 365): array
    {
        $days = max(7, min(730, $days));
        try {
            $stmt = $this->db->query("SELECT * FROM school_general_index_snapshots WHERE snapshot_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY) ORDER BY snapshot_date ASC");
            return $stmt->fetchAll() ?: [];
        } catch (PDOException) {
            return [];
        }
    }
}
