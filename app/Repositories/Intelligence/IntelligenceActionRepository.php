<?php

declare(strict_types=1);

namespace App\Repositories\Intelligence;

use App\Repositories\BaseRepository;

final class IntelligenceActionRepository extends BaseRepository
{
    public function byInsightKeys(array $keys): array
    {
        $keys = array_values(array_unique(array_filter(array_map('strval', $keys))));
        if ($keys === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->db->prepare("SELECT * FROM intelligence_actions WHERE insight_key IN ($placeholders) ORDER BY updated_at DESC, id DESC");
        $stmt->execute($keys);
        $rows = $stmt->fetchAll();
        $mapped = [];
        foreach ($rows as $row) {
            $mapped[(string) $row['insight_key']] ??= $row;
        }
        return $mapped;
    }

    public function findByInsightKey(string $key): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM intelligence_actions WHERE insight_key = :key ORDER BY id DESC LIMIT 1');
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM intelligence_actions WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('INSERT INTO intelligence_actions (insight_key, source_type, status, priority, title, category, responsible_user_id, responsible_name, due_date, notes, created_by, created_by_name, resolved_at, created_at, updated_at) VALUES (:insight_key, :source_type, :status, :priority, :title, :category, :responsible_user_id, :responsible_name, :due_date, :notes, :created_by, :created_by_name, NULL, NOW(), NOW())');
        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('UPDATE intelligence_actions SET status = :status, responsible_user_id = :responsible_user_id, responsible_name = :responsible_name, due_date = :due_date, notes = :notes, resolved_at = :resolved_at, updated_at = NOW() WHERE id = :id');
        $stmt->execute($data + ['id' => $id]);
    }

    public function addHistory(array $data): void
    {
        $stmt = $this->db->prepare('INSERT INTO intelligence_action_history (action_id, event_type, status_before, status_after, description, created_by, created_by_name, created_at) VALUES (:action_id, :event_type, :status_before, :status_after, :description, :created_by, :created_by_name, NOW())');
        $stmt->execute($data);
    }

    public function history(int $actionId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM intelligence_action_history WHERE action_id = :action_id ORDER BY created_at DESC, id DESC');
        $stmt->execute(['action_id' => $actionId]);
        return $stmt->fetchAll();
    }
}
