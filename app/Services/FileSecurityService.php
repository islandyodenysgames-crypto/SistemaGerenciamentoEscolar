<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final class FileSecurityService
{
    private const MIME_BY_EXTENSION = [
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'], 'webp' => ['image/webp'], 'gif' => ['image/gif'],
        'pdf' => ['application/pdf'], 'txt' => ['text/plain'],
        'doc' => ['application/msword', 'application/x-ole-storage', 'application/CDFV2'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
        'xls' => ['application/vnd.ms-excel', 'application/x-ole-storage', 'application/CDFV2'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'mp3' => ['audio/mpeg'], 'wav' => ['audio/wav', 'audio/x-wav'], 'ogg' => ['audio/ogg'],
        'mp4' => ['video/mp4'], 'webm' => ['video/webm'],
    ];

    public static function inspectUpload(array $file, array $allowedExtensions, int $maxBytes, bool $requireHttpUpload = true): array
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException(self::uploadErrorMessage($error));
        }
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_file($tmp) || ($requireHttpUpload && !is_uploaded_file($tmp))) {
            throw new InvalidArgumentException('O arquivo temporário do upload é inválido.');
        }
        $actualSize = filesize($tmp);
        if ($actualSize === false || $actualSize <= 0 || $actualSize > $maxBytes) {
            throw new InvalidArgumentException('O arquivo está vazio ou excede o limite permitido.');
        }
        $original = trim(basename(str_replace('\\', '/', (string) ($file['name'] ?? 'arquivo'))));
        if ($original === '' || preg_match('/[\x00-\x1F\x7F]/', $original)) {
            throw new InvalidArgumentException('O nome do arquivo é inválido.');
        }
        $extension = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        $allowedExtensions = array_map('strtolower', $allowedExtensions);
        if ($extension === '' || !in_array($extension, $allowedExtensions, true) || !isset(self::MIME_BY_EXTENSION[$extension])) {
            throw new InvalidArgumentException('O tipo de arquivo não é permitido.');
        }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: 'application/octet-stream';
        if (!in_array($mime, self::MIME_BY_EXTENSION[$extension], true)) {
            throw new InvalidArgumentException('A extensão do arquivo não corresponde ao conteúdo enviado.');
        }
        if (str_starts_with($mime, 'image/')) {
            $image = @getimagesize($tmp);
            if ($image === false || !self::imageTypeMatches($extension, (int) ($image[2] ?? 0))) {
                throw new InvalidArgumentException('A imagem enviada é inválida ou está corrompida.');
            }
        }
        return ['original_name' => $original, 'extension' => $extension, 'mime_type' => $mime, 'size_bytes' => (int) $actualSize, 'tmp_name' => $tmp];
    }

    public static function inspectImageBytes(string $bytes, array $allowedExtensions, int $maxBytes): array
    {
        if ($bytes === '' || strlen($bytes) > $maxBytes) throw new InvalidArgumentException('A imagem está vazia ou excede o limite permitido.');
        $image = @getimagesizefromstring($bytes);
        $map = [IMAGETYPE_JPEG => ['jpg', 'image/jpeg'], IMAGETYPE_PNG => ['png', 'image/png'], IMAGETYPE_WEBP => ['webp', 'image/webp'], IMAGETYPE_GIF => ['gif', 'image/gif']];
        if ($image === false || !isset($map[(int) ($image[2] ?? 0)]) || !in_array($map[(int) $image[2]][0], $allowedExtensions, true)) {
            throw new InvalidArgumentException('A imagem enviada é inválida ou está corrompida.');
        }
        return ['extension' => $map[(int) $image[2]][0], 'mime_type' => $map[(int) $image[2]][1], 'width' => (int) $image[0], 'height' => (int) $image[1], 'size_bytes' => strlen($bytes)];
    }

    public static function safePath(string $baseDirectory, string $relativePath): ?string
    {
        $relativePath = str_replace('\\', '/', trim($relativePath));
        if ($relativePath === '' || str_contains($relativePath, "\0") || str_starts_with($relativePath, '/') || preg_match('#^[A-Za-z]:/#', $relativePath)) return null;
        $segments = explode('/', $relativePath);
        if (in_array('..', $segments, true) || in_array('', $segments, true)) return null;
        $base = realpath($baseDirectory);
        if ($base === false) return null;
        $candidate = $base . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $segments);
        $parent = realpath(dirname($candidate));
        if ($parent === false || ($parent !== $base && !str_starts_with($parent, $base . DIRECTORY_SEPARATOR))) return null;
        return $candidate;
    }

    public static function deleteWithin(string $baseDirectory, string $relativePath): bool
    {
        $path = self::safePath($baseDirectory, $relativePath);
        return $path !== null && is_file($path) ? unlink($path) : false;
    }

    public static function randomName(string $extension, string $prefix = 'file'): string
    {
        return preg_replace('/[^a-z0-9-]/i', '-', $prefix) . '-' . bin2hex(random_bytes(16)) . '.' . strtolower($extension);
    }

    private static function imageTypeMatches(string $extension, int $type): bool
    {
        return match ($type) { IMAGETYPE_JPEG => in_array($extension, ['jpg', 'jpeg'], true), IMAGETYPE_PNG => $extension === 'png', IMAGETYPE_WEBP => $extension === 'webp', IMAGETYPE_GIF => $extension === 'gif', default => false };
    }

    private static function uploadErrorMessage(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'O arquivo excede o limite permitido.',
            UPLOAD_ERR_PARTIAL => 'O envio do arquivo foi interrompido. Tente novamente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi enviado.',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE, UPLOAD_ERR_EXTENSION => 'O servidor não conseguiu receber o arquivo.',
            default => 'Não foi possível receber o arquivo.',
        };
    }
}
