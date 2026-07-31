<?php

declare(strict_types=1);

namespace App\Repositories\Intelligence;

use App\Repositories\BaseRepository;

final class StudentIntelligenceSnapshotRepository extends BaseRepository
{
    public function find(int $userId, int $studentId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_intelligence_snapshots WHERE user_id=:user_id AND student_id=:student_id LIMIT 1");
        $stmt->execute(['user_id' => $userId, 'student_id' => $studentId]);
        return $stmt->fetch() ?: null;
    }

    public function save(int $userId, int $studentId, string $riskLevel, int $riskScore, ?string $latestActionDate, ?string $lastStaleAlertAt = null): void
    {
        $stmt = $this->db->prepare("INSERT INTO student_intelligence_snapshots
            (user_id,student_id,risk_level,risk_score,latest_action_date,last_stale_alert_at,evaluated_at,created_at,updated_at)
            VALUES (:user_id,:student_id,:risk_level,:risk_score,:latest_action_date,:last_stale_alert_at,NOW(),NOW(),NOW())
            ON DUPLICATE KEY UPDATE
                risk_level=VALUES(risk_level),
                risk_score=VALUES(risk_score),
                latest_action_date=VALUES(latest_action_date),
                last_stale_alert_at=COALESCE(VALUES(last_stale_alert_at),last_stale_alert_at),
                evaluated_at=NOW(),
                updated_at=NOW()");
        $stmt->execute([
            'user_id' => $userId,
            'student_id' => $studentId,
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'latest_action_date' => $latestActionDate,
            'last_stale_alert_at' => $lastStaleAlertAt,
        ]);
    }
}
