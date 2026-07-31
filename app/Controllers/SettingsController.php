<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Settings\SettingManager;

class SettingsController extends Controller
{
    public function __construct(private SettingManager $settings) {}

    public function index(): void
    {
        $this->view('pages/settings/index', [
            'title' => 'Configurações',
            'frequencyGoal' => (float) $this->settings->get('school_goals.frequency_goal', 95.0),
        ]);
    }
}