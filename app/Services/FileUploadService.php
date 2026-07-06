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
        if (
            empty($file['tmp_name']) ||
            !is_uploaded_file($file['tmp_name'])
        ) {
            return null;
        }

        $extension = strtolower(
            pathinfo(
                $file['name'],
                PATHINFO_EXTENSION
            )
        );

        $allowed = [
            'png',
            'jpg',
            'jpeg',
            'svg',
            'webp',
        ];

        if (!in_array($extension, $allowed, true)) {
            return null;
        }

        if (!is_dir($this->destination)) {
            mkdir(
                $this->destination,
                0775,
                true
            );
        }

        $filename = 'school-logo.' . $extension;

        $target = $this->destination . DIRECTORY_SEPARATOR . $filename;

        move_uploaded_file(
            $file['tmp_name'],
            $target
        );

        return 'uploads/schools/' . $filename;
    }
}