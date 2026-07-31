<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Response;
use App\Core\Session;
use App\Services\Settings\SettingService;
use Throwable;

final class SchoolGoalsSettingsController extends BaseController
{
    public function __construct(private SettingService $service) {}

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $data = $this->service->goalsPage();
        $data['title'] = 'Metas da Escola';
        $data['success'] = Session::get('goals_success');
        $data['error'] = Session::get('goals_error');
        Session::remove('goals_success');
        Session::remove('goals_error');
        $this->view('pages/settings/goals/index', $data);
    }

    public function update(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        try {
            $user = Session::get('user', []);
            $this->service->saveGoals($_POST, isset($user['id']) ? (int)$user['id'] : null);
            Session::set('goals_success', 'Meta de frequência atualizada. Os painéis e indicadores já utilizarão o novo valor.');
        } catch (Throwable $e) {
            Session::set('goals_error', $e->getMessage());
        }
        Response::redirect(base_url('configuracoes/metas'));
    }

    public function reset(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $user = Session::get('user', []);
        $this->service->resetGoals(isset($user['id']) ? (int)$user['id'] : null);
        Session::set('goals_success', 'Meta recomendada de 95% restaurada.');
        Response::redirect(base_url('configuracoes/metas'));
    }
}
