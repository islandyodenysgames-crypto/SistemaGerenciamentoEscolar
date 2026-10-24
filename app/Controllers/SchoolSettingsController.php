<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Session;
use App\Repositories\SchoolRepository;
use App\Services\FileUploadService;
use App\Services\SchoolService;

class SchoolSettingsController extends BaseController
{
    private SchoolService $schoolService;

    private FileUploadService $fileUploadService;

    public function __construct()
    {
        $this->schoolService = new SchoolService(
            new SchoolRepository()
        );

        $this->fileUploadService = new FileUploadService();
    }

    public function identity(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $this->view('pages/settings/school/identity', [
            'title' => 'Identidade da Escola',
            'school' => $this->schoolService->current(),
        ]);
    }

    public function update(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $currentSchool = $this->schoolService->current();

        $logoPath = $currentSchool['logo_path'] ?? null;

        if (!empty($_FILES['logo']['tmp_name'])) {
            try {
                $uploadedLogo = $this->fileUploadService->uploadLogo($_FILES['logo']);
            } catch (\InvalidArgumentException $error) {
                Session::set('settings_error', $error->getMessage());
                redirect('configuracoes/identidade');
            }

            if ($uploadedLogo !== null) {
                $logoPath = $uploadedLogo;
            }
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'short_name' => trim($_POST['short_name'] ?? ''),
            'inep_code' => trim($_POST['inep_code'] ?? ''),
            'principal' => trim($_POST['principal'] ?? ''),
            'vice_principal' => trim($_POST['vice_principal'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'state' => strtoupper(trim($_POST['state'] ?? '')),
            'zip_code' => trim($_POST['zip_code'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'website' => trim($_POST['website'] ?? ''),
            'instagram' => trim($_POST['instagram'] ?? ''),
            'facebook' => trim($_POST['facebook'] ?? ''),
            'youtube' => trim($_POST['youtube'] ?? ''),
            'logo_path' => $logoPath,
            'primary_color' => $_POST['primary_color'] ?? '#16a34a',
            'secondary_color' => $_POST['secondary_color'] ?? '#f97316',
        ];

        $this->schoolService->save($data);

        redirect('configuracoes/identidade');
    }
}
