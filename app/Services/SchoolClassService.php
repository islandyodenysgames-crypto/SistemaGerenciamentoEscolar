<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SchoolClassRepository;

class SchoolClassService
{
    private const PHOTO_MAX_BYTES = 8 * 1024 * 1024;
    private const PHOTO_MIN_WIDTH = 900;
    private const PHOTO_MIN_HEIGHT = 500;
    public function __construct(
        private SchoolClassRepository $repository
    ) {
    }

    public function all(): array
    {
        return $this->repository->all();
    }

    public function countActive(): int
    {
        return $this->repository->countActive();
    }

    public function find(int $id): ?array
    {
        return $this->repository->find($id);
    }

    public function create(array $data): void
    {
        $this->repository->create($data);
    }

    public function update(int $id, array $data): void
    {
        $this->repository->update($id, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }


    public function savePhoto(array $file, string $oldPath = ''): string
    {
        $inspected = FileSecurityService::inspectUpload($file, ['jpg', 'jpeg', 'png', 'webp'], self::PHOTO_MAX_BYTES);
        $tmp = $inspected['tmp_name'];
        $info = $tmp !== '' ? @getimagesize($tmp) : false;
        $allowed = [
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
        ];

        if (!$info || !isset($allowed[$info[2]])) {
            throw new \InvalidArgumentException('Envie uma foto JPG, PNG ou WebP para a turma.');
        }

        if ((int)$info[0] < self::PHOTO_MIN_WIDTH || (int)$info[1] < self::PHOTO_MIN_HEIGHT) {
            throw new \InvalidArgumentException('A foto da turma deve possuir pelo menos 900 × 500 px.');
        }

        $relativeDir = 'uploads/classes';
        $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \InvalidArgumentException('Não foi possível preparar a pasta de fotos das turmas.');
        }

        $name = FileSecurityService::randomName($allowed[$info[2]], 'turma');
        $target = $absoluteDir . '/' . $name;

        if (!move_uploaded_file($tmp, $target)) {
            throw new \InvalidArgumentException('Não foi possível salvar a foto da turma.');
        }

        $path = $relativeDir . '/' . $name;
        if ($oldPath !== '' && $oldPath !== $path) {
            $this->removePhoto($oldPath);
        }

        return $path;
    }


    public function saveCroppedPhoto(string $dataUri, string $oldPath = ''): string
    {
        $dataUri = trim($dataUri);
        if ($dataUri === '' || !preg_match('#^data:image/(jpeg|jpg);base64,([A-Za-z0-9+/=\r\n]+)$#', $dataUri, $matches)) {
            throw new \InvalidArgumentException('O recorte da foto da turma é inválido. Selecione e envie a imagem novamente.');
        }

        $binary = base64_decode(preg_replace('/\s+/', '', $matches[2]), true);
        if ($binary === false || $binary === '') {
            throw new \InvalidArgumentException('Não foi possível processar o recorte da foto da turma.');
        }

        if (strlen($binary) > self::PHOTO_MAX_BYTES) {
            throw new \InvalidArgumentException('A foto recortada da turma deve possuir no máximo 8 MB.');
        }

        $validated = FileSecurityService::inspectImageBytes($binary, ['jpg'], self::PHOTO_MAX_BYTES);
        $info = @getimagesizefromstring($binary);
        if (!$info || (int)($info[2] ?? 0) !== IMAGETYPE_JPEG) {
            throw new \InvalidArgumentException('O recorte enviado não é uma imagem JPEG válida.');
        }

        if ((int)$info[0] !== 1600 || (int)$info[1] !== 900) {
            throw new \InvalidArgumentException('O recorte da foto da turma deve possuir exatamente 1600 × 900 px.');
        }

        $relativeDir = 'uploads/classes';
        $absoluteDir = dirname(__DIR__, 2) . '/public/' . $relativeDir;
        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new \InvalidArgumentException('Não foi possível preparar a pasta de fotos das turmas.');
        }

        $name = FileSecurityService::randomName($validated['extension'], 'turma');
        $target = $absoluteDir . '/' . $name;
        if (@file_put_contents($target, $binary, LOCK_EX) === false) {
            throw new \InvalidArgumentException('Não foi possível salvar a foto recortada da turma.');
        }

        $path = $relativeDir . '/' . $name;
        if ($oldPath !== '' && $oldPath !== $path) {
            $this->removePhoto($oldPath);
        }

        return $path;
    }

    public function removePhoto(string $path): void
    {
        $path = str_replace('\\', '/', ltrim($path, '/'));
        if ($path === '' || !str_starts_with($path, 'uploads/classes/')) {
            return;
        }

        FileSecurityService::deleteWithin(dirname(__DIR__, 2) . '/public', $path);
    }

    public function exists(
        string $name,
        int $year,
        ?int $ignoreId = null
    ): bool {
        return $this->repository->exists(
            $name,
            $year,
            $ignoreId
        );
    }
}
