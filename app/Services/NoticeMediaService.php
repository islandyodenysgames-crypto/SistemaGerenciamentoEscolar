<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\NoticeAttachmentRepository;
use InvalidArgumentException;

class NoticeMediaService
{
    private const MAX_FILE = 26214400;
    private const MAX_FILES = 10;

    public function __construct(private NoticeAttachmentRepository $repository) {}

    public function attachments(int $noticeId): array
    {
        return $this->repository->forNotice($noticeId);
    }

    public function saveBanner(int $noticeId, string $dataUrl, ?string $oldPath = null): ?string
    {
        $dataUrl = trim($dataUrl);
        if ($dataUrl === '') return $oldPath;
        if (!preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s', $dataUrl, $m)) {
            throw new InvalidArgumentException('A imagem recortada do banner é inválida.');
        }
        $bytes = base64_decode($m[2], true);
        if ($bytes === false || strlen($bytes) > 8 * 1024 * 1024) {
            throw new InvalidArgumentException('O banner deve ter no máximo 8 MB.');
        }
        $ext = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $relativeDir = 'uploads/notices/' . date('Y/m') . '/' . $noticeId;
        $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new InvalidArgumentException('Não foi possível preparar a pasta do banner.');
        }
        $name = 'banner-' . bin2hex(random_bytes(8)) . '.' . $ext;
        if (file_put_contents($absoluteDir . '/' . $name, $bytes) === false) {
            throw new InvalidArgumentException('Não foi possível salvar o banner.');
        }
        $path = $relativeDir . '/' . $name;
        if ($oldPath && $oldPath !== $path) $this->removePublicFile($oldPath);
        return $path;
    }

    public function saveAttachments(int $noticeId, array $files, ?int $userId): void
    {
        $normalized = $this->normalizeFiles($files);
        if (count($normalized) > self::MAX_FILES) throw new InvalidArgumentException('Envie no máximo 10 arquivos por vez.');
        $allowed = ['pdf','jpg','jpeg','png','webp','doc','docx','xls','xlsx','txt','zip','mp3','wav','mp4','webm'];
        foreach ($normalized as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
            if (($file['error'] ?? 1) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('Falha ao enviar um dos anexos.');
            if ((int)$file['size'] > self::MAX_FILE) throw new InvalidArgumentException('Cada anexo deve ter no máximo 25 MB.');
            $original = basename((string)$file['name']);
            $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed, true)) throw new InvalidArgumentException('Tipo de anexo não permitido: ' . $original);
            $relativeDir = 'uploads/notices/' . date('Y/m') . '/' . $noticeId . '/attachments';
            $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;
            if (!is_dir($absoluteDir)) mkdir($absoluteDir, 0775, true);
            $stored = bin2hex(random_bytes(12)) . '.' . $ext;
            if (!move_uploaded_file((string)$file['tmp_name'], $absoluteDir . '/' . $stored)) throw new InvalidArgumentException('Não foi possível salvar o anexo ' . $original . '.');
            $mime = function_exists('mime_content_type') ? (mime_content_type($absoluteDir . '/' . $stored) ?: 'application/octet-stream') : 'application/octet-stream';
            $this->repository->create([
                'notice_id'=>$noticeId,'original_name'=>$original,'stored_name'=>$stored,'relative_path'=>$relativeDir.'/'.$stored,
                'mime_type'=>$mime,'extension'=>$ext,'size_bytes'=>(int)$file['size'],'uploaded_by'=>$userId,
            ]);
        }
    }

    public function deleteAttachment(int $id): ?int
    {
        $item = $this->repository->find($id);
        if (!$item) return null;
        $this->removePublicFile((string)$item['relative_path']);
        $this->repository->delete($id);
        return (int)$item['notice_id'];
    }

    public function removeBanner(?string $path): void { if ($path) $this->removePublicFile($path); }

    private function removePublicFile(string $path): void
    {
        $path = ltrim(str_replace(['..','\\'], ['', '/'], $path), '/');
        $absolute = dirname(__DIR__, 2) . '/public/' . $path;
        if (is_file($absolute)) @unlink($absolute);
    }

    private function normalizeFiles(array $files): array
    {
        if (!isset($files['name'])) return [];
        if (!is_array($files['name'])) return [$files];
        $out=[];
        foreach ($files['name'] as $i=>$name) $out[]=['name'=>$name,'type'=>$files['type'][$i]??'','tmp_name'=>$files['tmp_name'][$i]??'','error'=>$files['error'][$i]??UPLOAD_ERR_NO_FILE,'size'=>$files['size'][$i]??0];
        return $out;
    }
}
