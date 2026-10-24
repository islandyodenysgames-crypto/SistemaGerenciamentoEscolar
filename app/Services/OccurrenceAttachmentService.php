<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\OccurrenceAttachmentRepository;
use InvalidArgumentException;

class OccurrenceAttachmentService
{
    private const MAX_FILE_SIZE = 25 * 1024 * 1024;
    private const MAX_FILES = 10;
    private const ALLOWED_EXTENSIONS = [
        'pdf','doc','docx','xls','xlsx','jpg','jpeg','png','webp','gif',
        'mp4','webm','mp3','wav','ogg','zip','txt'
    ];

    public function __construct(private OccurrenceAttachmentRepository $repository) {}

    public function byOccurrence(int $occurrenceId): array
    {
        return array_map([$this, 'decorate'], $this->repository->byOccurrence($occurrenceId));
    }

    public function find(int $attachmentId): ?array
    {
        $item = $this->repository->find($attachmentId);
        return $item ? $this->decorate($item) : null;
    }

    public function groupedByOccurrences(array $ids): array
    {
        $grouped = $this->repository->groupedByOccurrences($ids);
        foreach ($grouped as $id => $items) {
            $grouped[$id] = array_map([$this, 'decorate'], $items);
        }
        return $grouped;
    }

    public function uploadMany(int $occurrenceId, array $files, ?int $userId, ?string $userName): array
    {
        $normalized = $this->normalizeFiles($files);
        if (count($normalized) > self::MAX_FILES) {
            throw new InvalidArgumentException('Envie no máximo 10 arquivos por vez.');
        }
        $saved = [];
        foreach ($normalized as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            $inspected = FileSecurityService::inspectUpload($file, self::ALLOWED_EXTENSIONS, self::MAX_FILE_SIZE);
            $size = $inspected['size_bytes']; $original = $inspected['original_name']; $extension = $inspected['extension']; $tmp = $inspected['tmp_name']; $mime = $inspected['mime_type'];
            $relativeDirectory = 'uploads/occurrences/' . date('Y/m') . '/' . $occurrenceId;
            $directory = public_path($relativeDirectory);
            if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                throw new InvalidArgumentException('Não foi possível preparar a pasta de anexos.');
            }
            $stored = FileSecurityService::randomName($extension, 'anexo');
            $target = $directory . DIRECTORY_SEPARATOR . $stored;
            if (!move_uploaded_file($tmp, $target)) {
                throw new InvalidArgumentException('Não foi possível salvar o arquivo ' . $original . '.');
            }
            $relativePath = $relativeDirectory . '/' . $stored;
            $id = $this->repository->create([
                'occurrence_id' => $occurrenceId,
                'original_name' => $original,
                'stored_name' => $stored,
                'relative_path' => $relativePath,
                'mime_type' => $mime,
                'extension' => $extension,
                'size_bytes' => $size,
                'uploaded_by' => $userId,
                'uploaded_by_name' => $userName,
            ]);
            $saved[] = $id;
        }
        return $saved;
    }

    public function remove(int $attachmentId, int $occurrenceId): bool
    {
        $item = $this->repository->find($attachmentId);
        if (!$item || (int) $item['occurrence_id'] !== $occurrenceId) return false;
        FileSecurityService::deleteWithin(public_path(), (string) $item['relative_path']);
        return $this->repository->delete($attachmentId);
    }

    public function removeAll(int $occurrenceId): void
    {
        foreach ($this->repository->byOccurrence($occurrenceId) as $item) {
            FileSecurityService::deleteWithin(public_path(), (string) $item['relative_path']);
        }
        $this->repository->deleteByOccurrence($occurrenceId);
    }

    private function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) return [];
        if (!is_array($files['name'])) return [$files];
        $result = [];
        foreach ($files['name'] as $i => $name) {
            $result[] = [
                'name' => $name,
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
                'size' => $files['size'][$i] ?? 0,
            ];
        }
        return $result;
    }

    private function decorate(array $item): array
    {
        $mime = strtolower((string) ($item['mime_type'] ?? ''));
        $item['url'] = base_url((string) $item['relative_path']);
        $item['is_image'] = str_starts_with($mime, 'image/');
        $item['is_video'] = str_starts_with($mime, 'video/');
        $item['is_audio'] = str_starts_with($mime, 'audio/');
        $item['is_pdf'] = $mime === 'application/pdf' || strtolower((string) ($item['extension'] ?? '')) === 'pdf';
        $item['size_label'] = $this->formatSize((int) ($item['size_bytes'] ?? 0));
        $item['icon'] = $item['is_image'] ? 'image' : ($item['is_video'] ? 'video' : ($item['is_audio'] ? 'audio-lines' : ($item['is_pdf'] ? 'file-text' : 'paperclip')));
        return $item;
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 0, ',', '.') . ' KB';
        return $bytes . ' B';
    }
}
