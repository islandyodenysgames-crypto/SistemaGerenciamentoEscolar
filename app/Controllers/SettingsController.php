<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->view('pages/settings/index', [
            'title' => 'Configurações',
        ]);
    }
}