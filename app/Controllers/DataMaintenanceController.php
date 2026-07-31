<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Response;
use App\Core\Session;
use App\Services\SchoolDataMaintenanceService;
use Throwable;

final class DataMaintenanceController extends BaseController
{
    public function __construct(private SchoolDataMaintenanceService $service) {}

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $this->view('pages/settings/data-maintenance', [
            'title' => 'Backup e dados escolares',
            'summary' => $this->service->summary(),
            'success' => flash('data_maintenance_success'),
            'error' => flash('data_maintenance_error'),
        ]);
    }

    public function export(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        try {
            $path = $this->service->createBackup();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($path) . '"');
            header('Content-Length: ' . filesize($path));
            header('X-Content-Type-Options: nosniff');
            readfile($path);
            exit;
        } catch (Throwable $e) {
            Session::set('data_maintenance_error', $e->getMessage());
            Response::redirect(base_url('configuracoes/dados'));
        }
    }

    public function restore(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        if (trim((string)($_POST['confirmation'] ?? '')) !== 'RESTAURAR DADOS') {
            Session::set('data_maintenance_error', 'Digite RESTAURAR DADOS para confirmar a operação.');
            Response::redirect(base_url('configuracoes/dados'));
        }
        try {
            $this->service->restoreBackup($_FILES['backup_file'] ?? []);
            Session::set('data_maintenance_success', 'Backup restaurado com sucesso. Entre novamente caso a sessão tenha sido alterada.');
        } catch (Throwable $e) {
            Session::set('data_maintenance_error', $e->getMessage());
        }
        Response::redirect(base_url('configuracoes/dados'));
    }

    public function clear(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        if (trim((string)($_POST['confirmation'] ?? '')) !== 'LIMPAR DADOS') {
            Session::set('data_maintenance_error', 'Digite LIMPAR DADOS para confirmar a operação.');
            Response::redirect(base_url('configuracoes/dados'));
        }
        try {
            $result = $this->service->clearSchoolData($_POST);
            $kept = [];
            if ($result['kept_classes']) $kept[] = 'turmas';
            if ($result['kept_students']) $kept[] = 'alunos e matrículas';
            if ($result['kept_class_photos']) $kept[] = 'fotos das turmas';
            if ($result['kept_student_photos']) $kept[] = 'fotos dos alunos';
            $suffix = $kept ? ' Foram mantidos: ' . implode(', ', $kept) . '.' : '';
            Session::set('data_maintenance_success', 'Limpeza concluída com segurança.' . $suffix);
        } catch (Throwable $e) {
            Session::set('data_maintenance_error', $e->getMessage());
        }
        Response::redirect(base_url('configuracoes/dados'));
    }
}
