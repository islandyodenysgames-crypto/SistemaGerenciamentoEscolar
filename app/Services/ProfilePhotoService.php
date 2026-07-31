<?php

declare(strict_types=1);

namespace App\Services;

use App\Database\Connection;
use InvalidArgumentException;

class ProfilePhotoService
{
    private const MAX_BYTES = 10485760;
    private const ENTITIES = ['student' => 'students', 'user' => 'users'];

    public function apply(string $entity, int $id, string $dataUrl, bool $remove, ?string $currentPath = null): ?string
    {
        if (!isset(self::ENTITIES[$entity]) || $id <= 0) {
            throw new InvalidArgumentException('Destino da foto inválido.');
        }

        if ($remove) {
            $this->removePublicFile($currentPath);
            $this->persist($entity, $id, null);
            return null;
        }

        $dataUrl = trim($dataUrl);
        if ($dataUrl === '') return $currentPath;

        if (!preg_match('#^data:image/(jpeg|png|webp);base64,(.+)$#s', $dataUrl, $matches)) {
            throw new InvalidArgumentException('A foto recortada é inválida.');
        }

        $bytes = base64_decode($matches[2], true);
        if ($bytes === false || strlen($bytes) > self::MAX_BYTES) {
            throw new InvalidArgumentException('A foto deve ter no máximo 10 MB.');
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $folder = $entity === 'student' ? 'students' : 'users';
        $relativeDir = 'uploads/' . $folder . '/' . $id;
        $absoluteDir = public_path($relativeDir);

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0775, true) && !is_dir($absoluteDir)) {
            throw new InvalidArgumentException('Não foi possível preparar a pasta da foto.');
        }

        $filename = 'profile-' . bin2hex(random_bytes(10)) . '.' . $extension;
        $absolutePath = $absoluteDir . DIRECTORY_SEPARATOR . $filename;
        if (file_put_contents($absolutePath, $bytes) === false) {
            throw new InvalidArgumentException('Não foi possível salvar a foto.');
        }

        $newPath = $relativeDir . '/' . $filename;
        if ($currentPath && $currentPath !== $newPath) $this->removePublicFile($currentPath);
        $this->persist($entity, $id, $newPath);
        return $newPath;
    }

    private function persist(string $entity, int $id, ?string $path): void
    {
        $table = self::ENTITIES[$entity];
        $stmt = Connection::getInstance()->prepare("UPDATE {$table} SET photo_path = :path, photo_updated_at = NOW(), updated_at = NOW() WHERE id = :id");
        $stmt->execute(['path' => $path, 'id' => $id]);
    }

    private function removePublicFile(?string $path): void
    {
        if (!$path) return;
        $safe = ltrim(str_replace(['..', '\\'], ['', '/'], $path), '/');
        $absolute = public_path($safe);
        if (is_file($absolute)) @unlink($absolute);
    }
}
