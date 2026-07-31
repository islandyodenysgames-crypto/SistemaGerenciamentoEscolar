<?php

declare(strict_types=1);

namespace App\Repositories\Occurrence;

use App\Repositories\BaseRepository;

final class NotificationRepository extends BaseRepository
{
    public function allByUser(
        int $userId,
        int $limit = 50
    ): array {
        $limit = max(1, min(200, $limit));

        $stmt = $this->db->prepare("
            SELECT
                id,
                user_id,
                type,
                title,
                message,
                severity,
                reference_type,
                reference_id,
                fingerprint,
                is_read,
                read_at,
                metadata,
                created_at,
                updated_at
            FROM occurrence_notifications
            WHERE user_id = :user_id
            ORDER BY is_read ASC, created_at DESC, id DESC
            LIMIT {$limit}
        ");

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return $stmt->fetchAll();
    }

    public function recentByUser(
        int $userId,
        int $limit = 5
    ): array {
        return array_slice(
            $this->allByUser($userId, $limit),
            0,
            $limit
        );
    }

    public function countUnreadByUser(int $userId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM occurrence_notifications
            WHERE user_id = :user_id
              AND is_read = 0
        ");

        $stmt->execute([
            'user_id' => $userId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function createIfMissing(array $data): ?int
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO occurrence_notifications (
                user_id,
                type,
                title,
                message,
                severity,
                reference_type,
                reference_id,
                fingerprint,
                is_read,
                read_at,
                metadata,
                created_at,
                updated_at
            ) VALUES (
                :user_id,
                :type,
                :title,
                :message,
                :severity,
                :reference_type,
                :reference_id,
                :fingerprint,
                0,
                NULL,
                :metadata,
                NOW(),
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'severity' => $data['severity'],
            'reference_type' => $data['reference_type'],
            'reference_id' => $data['reference_id'],
            'fingerprint' => $data['fingerprint'],
            'metadata' => $data['metadata'],
        ]);

        if ($stmt->rowCount() === 0) {
            return null;
        }

        return (int) $this->db->lastInsertId();
    }

    public function markAsRead(
        int $id,
        int $userId
    ): bool {
        $stmt = $this->db->prepare("
            UPDATE occurrence_notifications
            SET is_read = 1,
                read_at = COALESCE(read_at, NOW()),
                updated_at = NOW()
            WHERE id = :id
              AND user_id = :user_id
        ");

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function markAllAsRead(int $userId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE occurrence_notifications
            SET is_read = 1,
                read_at = COALESCE(read_at, NOW()),
                updated_at = NOW()
            WHERE user_id = :user_id
              AND is_read = 0
        ");

        return $stmt->execute([
            'user_id' => $userId,
        ]);
    }

    public function deleteForUser(
        int $id,
        int $userId
    ): bool {
        $stmt = $this->db->prepare("
            DELETE FROM occurrence_notifications
            WHERE id = :id
              AND user_id = :user_id
        ");

        return $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);
    }

    public function byStudentForUser(
        int $studentId,
        int $userId,
        int $limit = 50
    ): array {
        $limit = max(1, min(200, $limit));

        $stmt = $this->db->prepare("
            SELECT
                id, user_id, type, title, message, severity,
                reference_type, reference_id, is_read, metadata, created_at
            FROM occurrence_notifications
            WHERE user_id = :user_id
              AND reference_type = 'STUDENT'
              AND reference_id = :student_id
            ORDER BY created_at DESC, id DESC
            LIMIT {$limit}
        ");

        $stmt->execute([
            'user_id' => $userId,
            'student_id' => $studentId,
        ]);

        return $stmt->fetchAll();
    }
    public function findForUser(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM occurrence_notifications WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }


}
