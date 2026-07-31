<?php

declare(strict_types=1);

namespace App\Repositories;

class NoticeAttachmentRepository extends BaseRepository
{
    public function forNotice(int $noticeId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM school_notice_attachments WHERE notice_id = :id ORDER BY created_at DESC, id DESC');
        $stmt->execute(['id' => $noticeId]);
        return $stmt->fetchAll();
    }


    public function forNotices(array $noticeIds): array
    {
        $noticeIds = array_values(array_filter(array_map('intval', $noticeIds), static fn (int $id): bool => $id > 0));
        if ($noticeIds === []) return [];

        $placeholders = implode(',', array_fill(0, count($noticeIds), '?'));
        $stmt = $this->db->prepare("SELECT * FROM school_notice_attachments WHERE notice_id IN ($placeholders) ORDER BY notice_id, created_at DESC, id DESC");
        $stmt->execute($noticeIds);

        $grouped = [];
        foreach ($stmt->fetchAll() as $attachment) {
            $grouped[(int) $attachment['notice_id']][] = $attachment;
        }
        return $grouped;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM school_notice_attachments WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO school_notice_attachments (notice_id, original_name, stored_name, relative_path, mime_type, extension, size_bytes, uploaded_by, created_at, updated_at) VALUES (:notice_id, :original_name, :stored_name, :relative_path, :mime_type, :extension, :size_bytes, :uploaded_by, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM school_notice_attachments WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
