<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Response;
use App\Core\Session;
use App\Services\Settings\SettingService;
use Throwable;

final class IntelligenceSettingsController extends BaseController
{
    public function __construct(private SettingService $service)
    {
    }

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) {
            return;
        }
        $data = $this->service->intelligencePage();
        $data['title'] = 'Configurações da Inteligência';
        $data['success'] = Session::get('settings_success');
        $data['error'] = Session::get('settings_error');
        Session::remove('settings_success');
        Session::remove('settings_error');
        $this->view('pages/settings/intelligence/index', $data);
    }

    public function update(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) {
            return;
        }
        try {
            $user = Session::get('user', []);
            $this->service->saveIntelligence($_POST, isset($user['id']) ? (int) $user['id'] : null);
            Session::set('settings_success', 'Configurações da Inteligência atualizadas com sucesso.');
        } catch (Throwable $exception) {
            Session::set('settings_error', $exception->getMessage());
        }
        Response::redirect(base_url('configuracoes/inteligencia'));
    }

    public function reset(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) {
            return;
        }
        $user = Session::get('user', []);
        $this->service->resetIntelligence(isset($user['id']) ? (int) $user['id'] : null);
        Session::set('settings_success', 'Valores recomendados restaurados.');
        Response::redirect(base_url('configuracoes/inteligencia'));
    }
}
