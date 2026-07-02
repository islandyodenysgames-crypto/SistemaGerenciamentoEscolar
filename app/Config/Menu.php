<?php

declare(strict_types=1);

namespace App\Config;

class Menu
{
    public static function items(): array
    {
        return [

            [
                'title' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'url' => base_url(),
                'active' => true,
            ],

            [
                'title' => 'Alunos',
                'icon' => 'users',
                'url' => '#',
                'active' => false,
            ],

            [
                'title' => 'Turmas',
                'icon' => 'school',
                'url' => '#',
                'active' => false,
            ],

            [
                'title' => 'Frequência',
                'icon' => 'clipboard-check',
                'url' => '#',
                'active' => false,
            ],

            [
                'title' => 'Relatórios',
                'icon' => 'chart-column',
                'url' => '#',
                'active' => false,
            ],

            [
                'title' => 'Configurações',
                'icon' => 'settings',
                'url' => '#',
                'active' => false,
            ],

        ];
    }
}