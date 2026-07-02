<?php

declare(strict_types=1);

namespace App\Config;

class Menu
{
    public static function items(): array
    {
        return [

            [
                'title'  => 'Dashboard',
                'icon'   => 'layout-dashboard',
                'url'    => base_url(),
                'active' => true,
            ],

            [
                'title'  => 'Alunos',
                'icon'   => 'users',
                'url'    => base_url('alunos'),
                'active' => false,
            ],

            [
                'title'  => 'Turmas',
                'icon'   => 'school',
                'url'    => base_url('turmas'),
                'active' => false,
            ],

            [
                'title'  => 'Frequência',
                'icon'   => 'clipboard-check',
                'url'    => base_url('frequencia'),
                'active' => false,
            ],

            [
                'title'  => 'Usuários',
                'icon'   => 'user-cog',
                'url'    => base_url('usuarios'),
                'active' => false,
            ],

            [
                'title'  => 'Relatórios',
                'icon'   => 'chart-column',
                'url'    => base_url('relatorios'),
                'active' => false,
            ],

            [
                'title'  => 'Configurações',
                'icon'   => 'settings',
                'url'    => base_url('configuracoes'),
                'active' => false,
            ],

        ];
    }
}