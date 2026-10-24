<?php

declare(strict_types=1);

namespace App\Services;

class FileUploadService
{
    private string $destination;

    public function __construct(
        string $destination = 'uploads/schools'
    ) {
        $this->destination = public_path($destination);
    }

    public function uploadLogo(array $file): ?string
    {
        if ((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        $inspected = FileSecurityService::inspectUpload($file, ['png', 'jpg', 'jpeg', 'webp'], 5 * 1024 * 1024);

        if (!is_dir($this->destination)) {
            mkdir(
                $this->destination,
                0775,
                true
            );
        }

        $filename = FileSecurityService::randomName($inspected['extension'], 'school-logo');

        $target = $this->destination . DIRECTORY_SEPARATOR . $filename;

        if (!move_uploaded_file($inspected['tmp_name'], $target)) {
            throw new \InvalidArgumentException('Não foi possível salvar o logotipo da escola.');
        }

        return 'uploads/schools/' . $filename;
    }
}
