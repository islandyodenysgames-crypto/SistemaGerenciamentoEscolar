<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Repositories\Intelligence\IntelligenceActionRepository;
use InvalidArgumentException;

final class IntelligenceActionService
{
    public const STATUSES = ['NEW', 'IN_PROGRESS', 'RESOLVED', 'ARCHIVED'];

    public function __construct(private IntelligenceActionRepository $repository)
    {
    }

    public function attach(array $insights): array
    {
        $tracking = $this->repository->byInsightKeys(array_column($insights, 'key'));
        foreach ($insights as &$insight) {
            $action = $tracking[(string) ($insight['key'] ?? '')] ?? null;
            if ($action !== null) {
                $action['history'] = $this->repository->history((int) $action['id']);
            }
            $insight['tracking'] = $action;
        }
        unset($insight);
        return $insights;
    }

    public function save(array $input, array $user): int
    {
        $key = trim((string) ($input['insight_key'] ?? ''));
        $title = trim((string) ($input['title'] ?? ''));
        if ($key === '' || $title === '') {
            throw new InvalidArgumentException('Insight inválido para acompanhamento.');
        }

        $status = strtoupper(trim((string) ($input['status'] ?? 'IN_PROGRESS')));
        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Status de acompanhamento inválido.');
        }

        $existing = $this->repository->findByInsightKey($key);
        $userId = isset($user['id']) ? (int) $user['id'] : null;
        $userName = trim((string) ($user['name'] ?? $user['nome'] ?? 'Usuário'));
        $responsibleName = trim((string) ($input['responsible_name'] ?? '')) ?: $userName;
        $dueDate = trim((string) ($input['due_date'] ?? '')) ?: null;
        $notes = trim((string) ($input['notes'] ?? '')) ?: null;

        if ($existing === null) {
            $id = $this->repository->create([
                'insight_key' => $key,
                'source_type' => 'INSIGHT',
                'status' => $status,
                'priority' => strtoupper((string) ($input['priority'] ?? 'ATTENTION')),
                'title' => mb_substr($title, 0, 220),
                'category' => strtoupper((string) ($input['category'] ?? 'SCHOOL')),
                'responsible_user_id' => $userId,
                'responsible_name' => mb_substr($responsibleName, 0, 150),
                'due_date' => $dueDate,
                'notes' => $notes,
                'created_by' => $userId,
                'created_by_name' => mb_substr($userName, 0, 150),
            ]);
            $this->repository->addHistory([
                'action_id' => $id,
                'event_type' => 'CREATED',
                'status_before' => null,
                'status_after' => $status,
                'description' => $notes ?: 'Acompanhamento iniciado.',
                'created_by' => $userId,
                'created_by_name' => $userName,
            ]);
            return $id;
        }

        $before = (string) $existing['status'];
        $resolvedAt = $status === 'RESOLVED' ? date('Y-m-d H:i:s') : null;
        $this->repository->update((int) $existing['id'], [
            'status' => $status,
            'responsible_user_id' => $userId,
            'responsible_name' => mb_substr($responsibleName, 0, 150),
            'due_date' => $dueDate,
            'notes' => $notes,
            'resolved_at' => $resolvedAt,
        ]);
        $this->repository->addHistory([
            'action_id' => (int) $existing['id'],
            'event_type' => $before === $status ? 'NOTE_UPDATED' : 'STATUS_CHANGED',
            'status_before' => $before,
            'status_after' => $status,
            'description' => $notes ?: 'Acompanhamento atualizado.',
            'created_by' => $userId,
            'created_by_name' => $userName,
        ]);
        return (int) $existing['id'];
    }
}
