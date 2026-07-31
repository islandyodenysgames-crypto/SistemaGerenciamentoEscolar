<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NoticeRepository;
use App\Repositories\NoticeAttachmentRepository;
use App\Services\Occurrence\NotificationService;
use DateTime;
use InvalidArgumentException;

class NoticeService
{
    private const PRIORITIES = ['INFO','IMPORTANT','HIGH','URGENT'];
    private const CATEGORIES = ['GENERAL','PEDAGOGICAL','EVENTS','CALENDAR','MEETINGS','MANAGEMENT'];

    private const TARGETS = [
        'ALL',
        'TEACHERS',
        'COORDINATION',
        'SECRETARY',
        'ADMINISTRATION',
        'CLASS',
        'TV_PANEL',
    ];

    public function __construct(
        private NoticeRepository $repository,
        private NoticeAttachmentRepository $attachmentRepository,
        private NotificationService $notificationService
    ) {
    }

    public function all(): array
    {
        $notices = $this->repository->all();
        if ($notices === []) {
            return [];
        }

        $attachments = $this->attachmentRepository->forNotices(
            array_column($notices, 'id')
        );

        foreach ($notices as &$notice) {
            $notice['attachments'] = $attachments[(int) $notice['id']] ?? [];
        }
        unset($notice);

        return $notices;
    }

    public function activeForDashboard(?int $limit = null): array
    {
        $notices = $this->repository->activeForDashboard($limit);
        if ($notices === []) return [];

        $attachments = $this->attachmentRepository->forNotices(array_column($notices, 'id'));
        foreach ($notices as &$notice) {
            $notice['attachments'] = $attachments[(int) $notice['id']] ?? [];
        }
        unset($notice);
        return $notices;
    }

    public function activeForTvPanel(?int $limit = null): array
    {
        $notices = $this->repository->activeForTarget('TV_PANEL', 100);
        $notices = array_values(array_filter($notices, static fn(array $notice): bool => trim((string)($notice['banner_path'] ?? '')) !== ''));
        if ($limit !== null) $notices = array_slice($notices, 0, max(1, $limit));
        if ($notices === []) return [];

        $attachments = $this->attachmentRepository->forNotices(array_column($notices, 'id'));
        foreach ($notices as &$notice) {
            $notice['attachments'] = $attachments[(int) $notice['id']] ?? [];
        }
        unset($notice);
        return $notices;
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $data): int
    {
        $normalized = $this->normalizeData($data, true);
        $id = $this->repository->create($normalized);
        $this->notificationService->notifyNotice([
            ...$normalized,
            'id' => $id,
        ]);
        return $id;
    }

    public function update(
        int $id,
        array $data
    ): bool {
        return $this->repository->update(
            $id,
            $this->normalizeData($data, false)
        );
    }

    public function activate(int $id): bool
    {
        return $this->repository
            ->updateActive($id, true);
    }

    public function archive(int $id): bool
    {
        return $this->repository
            ->updateActive($id, false);
    }

    public function pin(int $id): bool
    {
        return $this->repository
            ->updatePinned($id, true);
    }

    public function unpin(int $id): bool
    {
        return $this->repository
            ->updatePinned($id, false);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    public function countActive(): int
    {
        return $this->repository->countActive();
    }

    public function countScheduled(): int
    {
        return $this->repository->countScheduled();
    }

    public function countExpired(): int
    {
        return $this->repository->countExpired();
    }

    public function priorities(): array
    {
        return [
            'INFO' => 'Informativo',
            'IMPORTANT' => 'Importante',
            'HIGH' => 'Alta',
            'URGENT' => 'Urgente',
        ];
    }


    public function categories(): array
    {
        return [
            'GENERAL' => 'Geral', 'PEDAGOGICAL' => 'Pedagógico', 'EVENTS' => 'Eventos',
            'CALENDAR' => 'Calendário', 'MEETINGS' => 'Reuniões', 'MANAGEMENT' => 'Gestão',
        ];
    }

    public function targets(): array
    {
        return [
            'ALL' => 'Todos',
            'TEACHERS' => 'Professores',
            'COORDINATION' => 'Coordenação',
            'SECRETARY' => 'Secretaria',
            'ADMINISTRATION' => 'Direção',
            'CLASS' => 'Turma específica',
            'TV_PANEL' => 'Painel-TV',
        ];
    }

    public function highestActivePriority(): string
    {
        return $this->repository
            ->highestActivePriority();
    }

    private function normalizeData(
        array $data,
        bool $creating
    ): array {
        $title = trim(
            (string) ($data['title'] ?? '')
        );

        $summary = trim((string)($data['summary'] ?? ''));
        $content = trim((string) ($data['content'] ?? ''));
        $category = strtoupper(trim((string)($data['category'] ?? 'GENERAL')));

        // Compatibilidade com avisos antigos: "Urgente" deixou de ser categoria
        // e permanece apenas como nível de prioridade.
        if ($category === 'URGENT') {
            $category = 'GENERAL';
        }
        $bannerPath = trim((string)($data['banner_path'] ?? ''));
        $youtubeUrl = trim((string)($data['youtube_url'] ?? ''));
        $featured = !empty($data['featured']) ? 1 : 0;

        $priority = strtoupper(
            trim(
                (string) (
                    $data['priority'] ?? 'INFO'
                )
            )
        );

        $target = strtoupper(
            trim(
                (string) (
                    $data['target'] ?? 'ALL'
                )
            )
        );

        $targetClassId = (int) (
            $data['target_class_id'] ?? 0
        );

        $publishedAt = $this->normalizeDateTime(
            $data['published_at'] ?? null
        );

        $expiresAt = $this->normalizeDateTime(
            $data['expires_at'] ?? null
        );

        $pinned = !empty($data['pinned'])
            ? 1
            : 0;

        $active = !empty($data['active'])
            ? 1
            : 0;

        if ($title === '') {
            throw new InvalidArgumentException(
                'Informe o título do aviso.'
            );
        }

        if (mb_strlen($title) > 180) {
            throw new InvalidArgumentException(
                'O título deve possuir no máximo 180 caracteres.'
            );
        }

        if ($summary !== '' && mb_strlen($summary) > 320) throw new InvalidArgumentException('O resumo deve possuir no máximo 320 caracteres.');
        if (!in_array($category, self::CATEGORIES, true)) throw new InvalidArgumentException('A categoria informada é inválida.');
        if ($youtubeUrl !== '' && filter_var($youtubeUrl, FILTER_VALIDATE_URL) === false) throw new InvalidArgumentException('Informe um link válido do YouTube.');

        if ($content === '') {
            throw new InvalidArgumentException(
                'Informe o conteúdo do aviso.'
            );
        }

        if (
            !in_array(
                $priority,
                self::PRIORITIES,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'A prioridade informada é inválida.'
            );
        }

        if (
            !in_array(
                $target,
                self::TARGETS,
                true
            )
        ) {
            throw new InvalidArgumentException(
                'O público-alvo informado é inválido.'
            );
        }

        if (
            $target === 'CLASS'
            && $targetClassId <= 0
        ) {
            throw new InvalidArgumentException(
                'Selecione a turma que receberá o aviso.'
            );
        }

        if ($target !== 'CLASS') {
            $targetClassId = 0;
        }

        if (
            $publishedAt !== null
            && $expiresAt !== null
            && strtotime($expiresAt)
                <= strtotime($publishedAt)
        ) {
            throw new InvalidArgumentException(
                'A data de expiração deve ser posterior à publicação.'
            );
        }

        $createdBy = null;
        $createdByName = null;
        $createdByRole = null;

        if ($creating) {
            $createdByValue = (int) (
                $data['created_by'] ?? 0
            );

            $createdByNameValue = trim(
                (string) (
                    $data['created_by_name'] ?? ''
                )
            );

            $createdByRoleValue = trim(
                (string) (
                    $data['created_by_role'] ?? ''
                )
            );

            $createdBy = $createdByValue > 0
                ? $createdByValue
                : null;

            $createdByName = $createdByNameValue !== ''
                ? $createdByNameValue
                : null;

            $createdByRole = $createdByRoleValue !== ''
                ? $createdByRoleValue
                : null;
        }

        return [
            'title' => $title,

            'summary' => $summary !== '' ? $summary : null,
            'content' => $content,
            'category' => $category,
            'banner_path' => $bannerPath !== '' ? $bannerPath : null,
            'youtube_url' => $youtubeUrl !== '' ? $youtubeUrl : null,
            'featured' => $featured,

            'priority' => $priority,

            'pinned' => $pinned,

            'active' => $active,

            'target' => $target,

            'target_class_id' => $targetClassId > 0
                ? $targetClassId
                : null,

            'published_at' => $publishedAt,

            'expires_at' => $expiresAt,

            'created_by' => $createdBy,

            'created_by_name' => $createdByName,

            'created_by_role' => $createdByRole,
        ];
    }

    private function normalizeDateTime(
        mixed $value
    ): ?string {
        $value = trim(
            (string) ($value ?? '')
        );

        if ($value === '') {
            return null;
        }

        $formats = [
            'Y-m-d\TH:i',
            'Y-m-d H:i:s',
            'Y-m-d H:i',
        ];

        foreach ($formats as $format) {
            $date = DateTime::createFromFormat(
                $format,
                $value
            );

            if ($date !== false) {
                return $date->format(
                    'Y-m-d H:i:s'
                );
            }
        }

        throw new InvalidArgumentException(
            'Uma das datas informadas é inválida.'
        );
    }
}