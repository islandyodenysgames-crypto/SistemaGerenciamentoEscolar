<?php

declare(strict_types=1);

namespace App\Services\Occurrence;

use App\Database\Connection;
use App\Repositories\Occurrence\ActionRepository;
use App\Repositories\Occurrence\OccurrenceRepository;
use InvalidArgumentException;
use Throwable;

class ActionService
{
    private const STATUSES = [
        'OPEN',
        'RESOLVED',
    ];

    private const ACTION_TYPES = [
        'ORIENTATION',
        'PHONE_CALL',
        'MEETING',
        'WARNING',
        'FORWARDED',
        'HOME_VISIT',
        'RESOLVED',
        'REOPENED',
        'OTHER',
    ];

    public function __construct(
        private ActionRepository $repository,
        private OccurrenceRepository $occurrenceRepository,
        private ActionAttachmentService $attachmentService
    ) {
    }

    public function byOccurrence(
        int $occurrenceId
    ): array {
        if ($occurrenceId <= 0) {
            return [];
        }

                $actions=$this->repository->byOccurrence($occurrenceId);
        $attachments=$this->attachmentService->groupedByActions(array_column($actions,'id'));
        foreach($actions as &$action){$action['attachments']=$attachments[(int)$action['id']]??[];}
        unset($action);
        return $actions;
    }

    public function groupedByStudent(
        int $studentId
    ): array {
        if ($studentId <= 0) {
            return [];
        }

        $actions = $this->repository->byStudent(
            $studentId
        );
        $attachments=$this->attachmentService->groupedByActions(array_column($actions,'id'));
        foreach($actions as &$action){$action['attachments']=$attachments[(int)$action['id']]??[];}
        unset($action);

        $grouped = [];

        foreach ($actions as $action) {
            $occurrenceId = (int) (
                $action['occurrence_id'] ?? 0
            );

            if ($occurrenceId <= 0) {
                continue;
            }

            $actionType = strtoupper(
                trim(
                    (string) (
                        $action['action_type']
                        ?? 'OTHER'
                    )
                )
            );

            $action['action_type'] = in_array(
                $actionType,
                self::ACTION_TYPES,
                true
            )
                ? $actionType
                : 'OTHER';

            $grouped[$occurrenceId][] = $action;
        }

        return $grouped;
    }

    public function count(
        int $occurrenceId
    ): int {
        if ($occurrenceId <= 0) {
            return 0;
        }

        return $this->repository
            ->countByOccurrence($occurrenceId);
    }

    public function create(array $data): int
    {
        $normalized = $this->normalizeData(
            $data
        );

        $occurrence = $this->occurrenceRepository
            ->find(
                $normalized['occurrence_id']
            );

        if (!$occurrence) {
            throw new InvalidArgumentException(
                'Ocorrência não encontrada.'
            );
        }

        $db = Connection::getInstance();

        try {
            $db->beginTransaction();

            $actionId = $this->repository->create(
                $normalized
            );

            $updated = $this->occurrenceRepository
                ->updateStatus(
                    $normalized['occurrence_id'],
                    $normalized['status_after']
                );

            if (!$updated) {
                throw new InvalidArgumentException(
                    'Não foi possível atualizar a situação da ocorrência.'
                );
            }

            $db->commit();

            return $actionId;
        } catch (Throwable $exception) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw $exception;
        }
    }

    public function statuses(): array
    {
        return [
            'OPEN' =>
                'Manter ocorrência aberta',

            'RESOLVED' =>
                'Marcar ocorrência como resolvida',
        ];
    }

    public function actionTypes(): array
    {
        return [
            'ORIENTATION' =>
                'Orientação',

            'PHONE_CALL' =>
                'Contato telefônico',

            'MEETING' =>
                'Reunião',

            'WARNING' =>
                'Advertência',

            'FORWARDED' =>
                'Encaminhamento',

            'HOME_VISIT' =>
                'Visita domiciliar',

            'RESOLVED' =>
                'Resolução',

            'REOPENED' =>
                'Reabertura',

            'OTHER' =>
                'Outro',
        ];
    }

    public function actionTypeIcons(): array
    {
        return [
            'ORIENTATION' =>
                'message-circle',

            'PHONE_CALL' =>
                'phone',

            'MEETING' =>
                'users',

            'WARNING' =>
                'triangle-alert',

            'FORWARDED' =>
                'send',

            'HOME_VISIT' =>
                'house',

            'RESOLVED' =>
                'circle-check-big',

            'REOPENED' =>
                'rotate-ccw',

            'OTHER' =>
                'file-text',
        ];
    }

    private function normalizeData(
        array $data
    ): array {
        $occurrenceId = (int) (
            $data['occurrence_id'] ?? 0
        );

        $actionType = strtoupper(
            trim(
                (string) (
                    $data['action_type']
                    ?? 'OTHER'
                )
            )
        );

        $description = trim(
            (string) (
                $data['description'] ?? ''
            )
        );

        $statusAfter = strtoupper(
            trim(
                (string) (
                    $data['status_after']
                    ?? 'OPEN'
                )
            )
        );

        $createdBy = (int) (
            $data['created_by'] ?? 0
        );

        $createdByName = trim(
            (string) (
                $data['created_by_name'] ?? ''
            )
        );

        $createdByRole = trim(
            (string) (
                $data['created_by_role'] ?? ''
            )
        );

        if ($occurrenceId <= 0) {
            throw new InvalidArgumentException(
                'A ocorrência informada é inválida.'
            );
        }

        if (
            !in_array(
                $actionType,
                self::ACTION_TYPES,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'O tipo da providência é inválido.'
            );
        }

        if ($description === '') {
            throw new InvalidArgumentException(
                'Informe a providência adotada.'
            );
        }

        if (mb_strlen($description) > 5000) {
            throw new InvalidArgumentException(
                'A providência deve possuir no máximo 5.000 caracteres.'
            );
        }

        if (
            !in_array(
                $statusAfter,
                self::STATUSES,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'A situação informada é inválida.'
            );
        }

        return [
            'occurrence_id' =>
                $occurrenceId,

            'action_type' =>
                $actionType,

            'description' =>
                $description,

            'status_after' =>
                $statusAfter,

            'created_by' =>
                $createdBy > 0
                    ? $createdBy
                    : null,

            'created_by_name' =>
                $createdByName !== ''
                    ? $createdByName
                    : null,

            'created_by_role' =>
                $createdByRole !== ''
                    ? $createdByRole
                    : null,
        ];
    }
}