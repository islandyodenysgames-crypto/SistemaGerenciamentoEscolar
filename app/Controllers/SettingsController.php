<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Permissions;
use App\Core\Settings\SettingManager;

class SettingsController extends BaseController
{
    public function __construct(private SettingManager $settings) {}

    public function index(): void
    {
        if (!$this->guard() || !$this->authorize(Permissions::SETTINGS_MANAGE)) return;
        $this->view('pages/settings/index', [
            'title' => 'Configurações',
            'frequencyGoal' => (float) $this->settings->get('school_goals.frequency_goal', 95.0),
        ]);
    }
}
